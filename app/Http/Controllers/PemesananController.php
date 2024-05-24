<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Psy\Readline\Hoa\Console;


class PemesananController extends Controller
{
    public function getconfirmPesanan()
    {
        $orders = Pesanan::where('status_pesanan', 'menunggu pembayaran')
            ->with('customer')
            ->get();
        return response()->json($orders);
    }

    public function confirmPesanan(Request $request, $id)
    {
        $order = Pesanan::where('status_pesanan', 'menunggu pembayaran')->find($id);
        if ($order) {
            $order->status_pesanan = 'diproses';
            $order->save();
            return response()->json([
                'status' => 'pesanan sudah di konfirmasi dan sedang di proses',
                'order' => $order,
            ]);
        }
        return response()->json([
            'message' => 'Pesanan tidak ditemukan atau sudah dikonfirmasi.'
        ], 404);
    }

    public function payPesanan($id, Request $request)
    {
        $customer = $request->user();
        try {
            $payment = Pesanan::with([
                'detail_pemesanan' => function ($query) {
                    $query->select('id_pemesanan', 'id_produk', 'jumlah', 'subtotal')
                        ->with([
                            'Produk' => function ($query) {
                                $query->select('id', 'nama_produk', 'harga');
                            }
                        ]);
                }
            ])
            ->where('id_customer',$customer->id)
            ->where('status_pesanan', 'menunggu pembayaran')
            ->get();

            if(count($payment)==0){
                throw new Exception();
            }
            return response()->json([
                'status' =>true,
                'message' => 'berhasil mendapatkan pesanan yang belum dibayar',
                'data' => $payment,
            ]);
        }catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'daftar pesanan tidak ditemukan!',
                'error' => $th->getMessage(),
                'data' => []
            ], 404);
        }
    }

    public function buktiBayar($id, Request $request){
        try{
            $validator= Validator::make($request->all(),[
                'bukti_pembayaran' => 'required'
            ],
            [
                'bukti_pembayaran.required' => 'bukti pembayaran harus di isi!'
            ],
        ); 
        if($validator->fails()){
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()
                ],400);
            }

            $pesanan = Pesanan::where('id', $id)->where('status_pesanan', 'menunggu pembayaran')->first();
            if($pesanan == null){
                throw new Exception();
            }
            $idUser = $request->user()->id;
            $hash = md5($idUser. $pesanan->id_pemesanan);
            $extension = $request->file('bukti_pembayaran')->guessExtension();
            $path = $request->file('bukti_pembayaran')->storeAs('buktiBayar', $hash . '.' . $extension, 'public');
            
            $pesanan->update([
                'status_pesanan' => 'sudah di bayar',
                'tanggal_pembayaran' => now(),
                'bukti_pembayaran' => $hash . '.' . $extension,
            ]);
            // $pesanan->status_pesanan = 'sudah di bayar';
            // $pesanan->tanggal_pembayaran = now();
            // $pesanan->bukti_pembayaran = $hash . '.' . $extension;
            // $pesanan->save();

            return response()->json([
                'status' => true,
                'message' => 'pesanan berhasil diupdate'
            ],200);
        }catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'daftar pesanan tidak ditemukan!',
                'error' => $th->getMessage(),
                'data' => []
            ], 404);
        }
    }
}
