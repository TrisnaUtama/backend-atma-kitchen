<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pemesanan;
use App\Models\DetailPemesanan;
use App\Models\Produk;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Exception;
use App\Models\Pesanan;
use Psy\Readline\Hoa\Console;

class PemesananController extends Controller{
    public function addPemesanan(Request $req){
        try {
            $validator = Validator::make($req->all(), [
                'items' => 'required|array',
                'items.*.id_produk' => 'nullable|integer',
                'items.*.id_hampers' => 'nullable|integer',
                'items.*.jumlah' => 'required|integer',
                'items.*.harga' => 'required|integer',
                'items.*.tanggal_diambil' => 'required|date',
                'items.*.status' => 'required|string',
                'items.*.id_alamat' => 'nullable|integer',
                'items.*.potongan_poin' => 'nullable|numeric',
                'items.*.deliveryType' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors(),
                ], 400);
            }

            $data = $validator->validated();
            $user = Auth::user();
            $totalPembayaran = 0;

            foreach ($data['items'] as $item) {
                $totalPembayaran += $item['harga'] * $item['jumlah'];
            }
            $poin = 0;
            while ($totalPembayaran > 0) {
                if ($totalPembayaran >= 1000000) {
                    $poin += 200;
                    $totalPembayaran -= 1000000;
                } elseif ($totalPembayaran >= 500000) {
                    $poin += 75;
                    $totalPembayaran -= 500000;
                } elseif ($totalPembayaran >= 100000) {
                    $poin += 15;
                    $totalPembayaran -= 100000;
                } elseif ($totalPembayaran >= 10000) {
                    $poin += 1;
                    $totalPembayaran -= 10000;
                }else {
                    $totalPembayaran = 0;
                }
            }

            $lastTwoDigitsOfYear = Carbon::now()->format('y');
            $month = Carbon::now()->format('m');

            $lastOrderNumber = cache('last_order_number', 100);
            $nextOrderNumber = $lastOrderNumber + 1;
            cache(['last_order_number' => $nextOrderNumber]);

            $idPemesanan = sprintf("%s.%s.%03d", $lastTwoDigitsOfYear, $month, $nextOrderNumber);
            $tanggal_pembayaran = null;

            $customer = Customer::find($user->id);
            $tanggalLahir = $customer->tanggal_lahir;

            $today = Carbon::now();
            $birthday = Carbon::createFromFormat('Y-m-d', $tanggalLahir);
            $todayMonth = $today->format('m');
            $todayTanggal = $today->format('d');
            $birthdayMonth = $birthday->format('m');
            $birthdayTanggal = $birthday->format('d');

            $isBirthdayInRange = $todayMonth == $birthdayMonth && $todayTanggal >= ($birthdayTanggal - 3) && $todayTanggal <= ($birthdayTanggal + 3);

            if ($isBirthdayInRange) {
                $poin *= 2;
            }

            $potonganPoin = 0;
            if($data['items'][0]['potongan_poin'] != 0){
                $potonganPoin = $data['items'][0]['potongan_poin'];
            }

            $status_pesanan = ($data['items'][0]['deliveryType'] === 'pickup') ? 'sudah di bayar' : 'dikonfirmasi admin';


            $pemesanan = Pemesanan::create([
                'no_nota' => $idPemesanan,
                'id_customer' => $user->id,
                'id_alamat' => $data['items'][0]['id_alamat'],
                'tanggal_pemesanan' => Carbon::now('Asia/Jakarta'),
                'tanggal_diambil' => Carbon::parse($data['items'][0]['tanggal_diambil'], 'Asia/Jakarta'),
                'status_pesanan' => $status_pesanan,
                'poin_pesanan' => $poin,
                'tanggal_pembayaran' => $tanggal_pembayaran,
                'potongan_poin' => $data['items'][0]['potongan_poin'],
            ]);

            $user = Auth::user();
            $customer = Customer::find($user->id);
            $customer->poin -= $potonganPoin;
            $customer->save();

            foreach ($data['items'] as $item) {
                DetailPemesanan::create([
                    'id_produk' => $item['id_produk'] ?? null,
                    'id_hampers' => $item['id_hampers'] ?? null,
                    'id_pemesanan' => $pemesanan->id,
                    'jumlah' => $item['jumlah'],
                    'subtotal' => $item['harga'] * $item['jumlah'],
                    'status' => $item['status'],
                ]);

                if ($item['status'] === 'Pre-Order') {
                    $produk = Produk::find($item['id_produk']);
                    $limit = $produk->limit()->first();
                    if ($limit) {
                        $limit->limit -= $item['jumlah'];
                        $limit->save();
                    }
                } elseif ($item['status'] === 'Ready Stok') {
                    $produk = Produk::find($item['id_produk']);
                    if ($produk) {
                        $produk->stok -= $item['jumlah'];
                        $produk->save();
                    }
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Pemesanan created successfully',
                'pemesanan' => $pemesanan,
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getPemesananById($id){
        try{
            $pemesanan = Pemesanan::where('id_customer', $id)->get();
            return response()->json([
                'status' => true,
                'data' => $pemesanan,
                'message' => `successfuly retreive all data pemesanan by id $id`
            ]);
        }catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function getTotalPemesananById($id){
        try {
            $pemesanan = Pemesanan::with('detailPemesanan')->find($id);

            if ($pemesanan) {
                $totalAmount = 0;
                foreach ($pemesanan->detailPemesanan as $detail) {
                    $totalAmount += $detail->subtotal;
                }
                if ($pemesanan->ongkir) {
                    $totalAmount += $pemesanan->ongkir;
                }
                return response()->json([
                    'status' => true,
                    'pemesanan' => $pemesanan,
                    'total_amount' => $totalAmount,
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Pemesanan not found',
                ], 404);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

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
