<?php

namespace App\Http\Controllers;


use App\Models\Limit_Produk;
use App\Models\Pesanan;
use Illuminate\Http\Request;
use App\Models\Pemesanan;
use App\Models\DetailPemesanan;
use App\Models\Pencatatan_Bahan_Baku;
use App\Models\Produk;
use App\Models\Customer;
use App\Models\BahanBaku;
use Illuminate\Support\Facades\Auth;
use App\Models\Saldo;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Exception;

class PemesananController extends Controller{


    public function processOrder(Request $request)
    {
        $orderIds = $request->input('selectedOrderIds');

        if (empty($orderIds)) {
            return response()->json([
                'status' => false,
                'message' => 'No orders selected.',
            ], 400);
        }

        $totalRequiredQuantities = [];

        foreach ($orderIds as $orderId) {
            $order = Pemesanan::find($orderId);

            if (!$order) {
                return response()->json([
                    'status' => false,
                    'message' => 'Order not found.',
                ], 404);
            }

            $orderDetails = $order->detailPemesanan;

            foreach ($orderDetails as $orderDetail) {
                $product = $orderDetail->produk;
                $komposisi = $product->komposisi;

                foreach ($komposisi as $ingredient) {
                    $requiredQuantity = $orderDetail->jumlah * $ingredient->jumlah;
                    $ingredientId = $ingredient->id_bahan_baku;

                    if (isset($totalRequiredQuantities[$ingredientId])) {
                        $totalRequiredQuantities[$ingredientId] += $requiredQuantity;
                    } else {
                        $totalRequiredQuantities[$ingredientId] = $requiredQuantity;
                    }
                }
            }
        }

        foreach ($totalRequiredQuantities as $ingredientId => $totalRequiredQuantity) {
            $bahanBaku = BahanBaku::find($ingredientId);

            if (!$bahanBaku || $bahanBaku->stok < $totalRequiredQuantity) {
                return response()->json([
                    'status' => false,
                    'message' => 'Insufficient stock for one or more ingredients.',
                    'ingredient' => $bahanBaku ? $bahanBaku->nama_bahan_baku : 'Unknown Ingredient',
                    'required_quantity' => $totalRequiredQuantity,
                    'available_stock' => $bahanBaku ? $bahanBaku->stok : 0,
                ], 400);
            }
        }

        foreach ($orderIds as $orderId) {
            $order = Pemesanan::find($orderId);
            $orderDetails = $order->detailPemesanan;

            foreach ($orderDetails as $orderDetail) {
                $product = $orderDetail->produk;
                $komposisi = $product->komposisi;

                foreach ($komposisi as $ingredient) {
                    $requiredQuantity = $orderDetail->jumlah * $ingredient->jumlah;
                    $bahanBaku = BahanBaku::find($ingredient->id_bahan_baku);

                    $bahanBaku->stok -= $requiredQuantity;
                    $bahanBaku->save();

                    $pencatatan = Pencatatan_Bahan_Baku::where('id_bahan_baku', $ingredient->id_bahan_baku)->first();

                    if ($pencatatan) {
                        $pencatatan->jumlah_terpakai += $requiredQuantity;
                        $pencatatan->save();
                    } else {
                        Pencatatan_Bahan_Baku::create([
                            'id_bahan_baku' => $ingredient->id_bahan_baku,
                            'jumlah_terpakai' => $requiredQuantity
                        ]);
                    }
                }
            }

            $order->status_pesanan = 'diproses';
            $order->save();
        }

        return response()->json([
            'status' => true,
            'message' => 'Orders processed successfully.',
        ]);
    }

    public function getLaporanProduk(Request $request){
        try {
            $userInputDate = $request->input('userInputDate'); 
            if (!$userInputDate) {
                return response()->json([
                    'status' => false,
                    'message' => 'User input date is missing',
                ], 400);
            }
    
            $formattedDate = Carbon::createFromFormat('Y-m-d', $userInputDate)->format('Y-m');
            $laporan = Pemesanan::with('detailPemesanan.produk', 'detailPemesanan.hampers')
                ->whereIn('status_pesanan', ['diproses', 'siap di-pickup', 'sudah di-pickup', 'sedang dikirim', 'selesai'])
                ->whereRaw("DATE_FORMAT(tanggal_diambil, '%Y-%m') = ?", [$formattedDate])
                ->get();
    
            $report = [];
    
            foreach ($laporan as $pemesanan) {
                foreach ($pemesanan->detailPemesanan as $detail) {
                    if ($detail->id_produk) {
                        $produk = $detail->produk;
                        $productKey = $produk->id;
                        if (array_key_exists($productKey, $report)) {
                            $report[$productKey]['quantity'] += $detail->jumlah;
                            $report[$productKey]['subtotal'] += ($detail->jumlah * $produk->harga);
                        } else {
                            $report[$productKey] = [
                                'name' => $produk->nama_produk,
                                'price' => $produk->harga,
                                'quantity' => $detail->jumlah,
                                'subtotal' => $detail->jumlah * $produk->harga,
                                'type' => 'product',
                            ];
                        }
                    }

                    if ($detail->id_hampers) {
                        $hampers = $detail->hampers;
                        $hampersKey = 'hampers_'. $hampers->id;
                        if (array_key_exists($hampersKey, $report)) {
                            $report[$hampersKey]['quantity'] += $detail->jumlah;
                            $report[$hampersKey]['subtotal'] += ($detail->jumlah * $hampers->harga);
                        } else {
                            $report[$hampersKey] = [
                                'name' => $hampers->nama_hampers,
                                'price' => $hampers->harga,
                                'quantity' => $detail->jumlah,
                                'subtotal' => $detail->jumlah * $hampers->harga,
                                'type' => 'hampers',
                            ];
                        }
                    }
                }
            }
    
            $report = array_values($report);
    
            return response()->json([
                'status' => true,
                'data' => $report,
            ], 200);
    
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    
    

    public function getPemesananToProses(){
        try {

            $today = Carbon::today()->toDateString();
            $pemesanan = Pemesanan::with('detailPemesanan')
                ->where('status_pesanan', 'diterima')
                ->whereRaw('DATE_SUB(DATE(tanggal_diambil), INTERVAL 1 DAY) = ?', [$today])
                ->get();
                
    
            if ($pemesanan->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'There is no data pemesanan to process',
                ], 404);
            }
    
            return response()->json([
                'status' => true,
                'message' => 'Successfully retrieved data pemesanan to process',
                'data' => $pemesanan
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function addPemesanan(Request $req)
    {
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
                } else {
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
            if ($data['items'][0]['potongan_poin'] != 0) {
                $potonganPoin = $data['items'][0]['potongan_poin'];
            }

            $status_pesanan = ($data['items'][0]['deliveryType'] === 'pickup') ? 'menunggu pembayaran' : 'dikonfirmasi admin';

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

    public function getPemesananById($id)
    {
        try {
            $pemesanan = Pemesanan::where('id_customer', $id)->get();
            return response()->json([
                'status' => true,
                'data' => $pemesanan,
                'message' => `successfuly retreive all data pemesanan by id $id`
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function getTotalPemesananById($id)
    {
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
                    $limit = Limit_Produk::find($detail->id_produk);
                    if ($detail->status == 'Ready Stok') {
                        if ($product) {
                            $product->stok += $detail->jumlah;
                            $product->save();
                        }
                    } else {
                        $limit->limit += $detail->jumlah;
                        $limit->save();
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
                $order->tip = 0;
            } else {
                $order->status_pesanan = 'diterima';
                $order->tanggal_diterima = Carbon::now();
                $poinPesanan = $order->poin_pesanan;
                $poinCustomer = Customer::where('id', $order->id_customer)->first();
                if ($poinCustomer) {
                    $poinCustomer->poin += $poinPesanan;
                    $poinCustomer->save();
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
                ->where('status_pesanan', 'menunggu pembayaran')
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

            $pesanan = Pesanan::where('id', $id)->where('status_pesanan', 'menunggu pembayaran')->first();
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
