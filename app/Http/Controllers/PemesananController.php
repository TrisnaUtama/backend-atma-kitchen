<?php

namespace App\Http\Controllers;

use App\Models\Hampers;
use App\Models\Pesanan;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Customer;
use App\Models\Produk;
use App\Models\Saldo;
use App\Models\detailPemesanan;
use Psy\Readline\Hoa\Console;


class PemesananController extends Controller
{
    public function getconfirmPesanan()
{
    $orders = Pesanan::with([
        'detail_pemesanan.Produk' => function ($query) {
            $query->select('id', 'nama_produk', 'harga');
        },
        'detail_pemesanan.Hampers' => function ($query) {
            $query->select('id', 'nama_hampers', 'harga');
        }
    ])
    ->where('status_pesanan', 'pembayaran valid')
    ->get();

    // Mengembalikan hasil (opsional, tergantung pada apa yang Anda inginkan)
    return response()->json($orders);
}


    public function confirmPesanan(Request $request, $id)
    {

        $order = Pesanan::where('status_pesanan', 'pembayaran valid')->find($id);
        

        if ($order) {
            if ($request->has('reject') && $request->reject) {
                $order->status_pesanan = 'ditolak';

                $details = DetailPemesanan::where('id_pemesanan', $order->id)->get();
                foreach ($details as $detail) {
                    $product = Produk::find($detail->id_produk);
                    if ($product) {
                        $product->stok += $detail->jumlah;
                        $product->save();
                    }
                }
                $jumlahSaldo = Saldo::where('id_customer', $order->id_customer)->first();
                if ($jumlahSaldo) {
                    $jumlahSaldo->jumlah_saldo += $order->uang_customer;
                    $jumlahSaldo->save();
                } else {
                    $saldo = new Saldo();
                    $saldo->id_customer = $order->id_customer;
                    $saldo->jumlah_saldo = $order->uang_customer;
                    $saldo->save();
                }

                $order->uang_customer = 0;
            } else {
                $order->status_pesanan = 'diterima';
                $poinPesanan = $order->poin_pesanan;
                $poinCustomer = Customer::where('id', $order->id_customer)->first();
                if ($poinCustomer) {
                    $poinCustomer->poin += $poinPesanan;
                    $poinCustomer->save();
                    $order->status_pesanan = 'diproses';
                    $order->save();

                    return response()->json([
                        'status' => 'pesanan sudah dikonfirmasi dan sedang diproses',
                        'order' => $order,
                    ]);
                }
            }

            $order->save();

            return response()->json([
                'status' => $request->reject ? 'pesanan ditolak dan stok dikembalikan serta uang customer masuk ke saldo' : 'pesanan sudah dikonfirmasi dan sedang diproses',
                'order' => $order,
            ]);
        } else {
            return response()->json([
                'message' => 'Pesanan tidak ditemukan atau sudah dikonfirmasi.'
            ], 404);
        }
    }


    public function payPesanan($id, Request $request)
{
    $customer = $request->user();
    try {
        $payment = Pesanan::with([
            'detail_pemesanan.Produk' => function ($query) {
                $query->select('id', 'nama_produk', 'harga');
            },
            'detail_pemesanan.Hampers' => function ($query) {
                $query->select('id', 'nama_hampers', 'harga');
            }
        ])
        ->where('id_customer', $customer->id)
        ->where('status_pesanan', 'dikonfirmasi admin')
        ->get();

        if (count($payment) == 0) {
            throw new Exception();
        }
        return response()->json([
            'status' => true,
            'message' => 'Berhasil mendapatkan pesanan yang belum dibayar',
            'data' => $payment,
        ]);
    } catch (\Throwable $th) {
        return response()->json([
            'status' => false,
            'message' => 'Daftar pesanan tidak ditemukan!',
            'error' => $th->getMessage(),
            'data' => []
        ], 404);
    }
}


    public function buktiBayar($id, Request $request)
    {
        try {
            print ($id);
            print ($request);
            $validator = Validator::make(
                $request->all(),
                [
                    'bukti_pembayaran' => 'required'
                ],
                [
                    'bukti_pembayaran.required' => 'bukti pembayaran harus di isi!'
                ],
            );
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()
                ], 400);
            }

            $pesanan = Pesanan::where('id', $id)->where('status_pesanan', 'dikonfirmasi admin')->first();
            if ($pesanan == null) {
                throw new Exception();
            }
            $idUser = $request->user()->id;
            $hash = md5($idUser . $pesanan->id_pemesanan);
            $extension = $request->file('bukti_pembayaran')->guessExtension();
            $path = $request->file('bukti_pembayaran')->storeAs('buktiBayar', $hash . '.' . $extension, 'public');

            $pesanan->update([
                'status_pesanan' => 'sudah di bayar',
                'tanggal_pembayaran' => now(),
                'bukti_pembayaran' => $hash . '.' . $extension,
            ]);
            return response()->json([
                'status' => true,
                'message' => 'pesanan berhasil diupdate'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'daftar pesanan tidak ditemukan!',
                'error' => $th->getMessage(),
                'data' => []
            ], 404);
        }
    }
}
