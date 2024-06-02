<?php

namespace App\Http\Controllers;

use App\Models\Alamat;
use App\Models\Komposisi;
use App\Models\Customer;
use App\Models\detailPemesanan;
use App\Models\Hampers;
use App\Models\Pemesanan;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class DetailPemesananController extends Controller
{
    public function index()
    {
        // $user_data = Customer::where('id_customer', Auth::user()->id_customer)->first()->load('user_credential');
        $orders = Pemesanan::where('id_customer', Auth::user()->id)->with('detailPemesanan', 'detailPemesanan.produk')->get()->sortByDesc('id');
        // return $orders;


        return response()->json([
            'status' => true,
            'message' => 'Success retrieve all data pemesanan',
            'data' => $orders
        ], 200);
    }

    public function getAllJarakNull()
    {
        $orders = Pemesanan::where('status_pesanan', 'dikonfirmasi admin')
            ->with('detailPemesanan', 'detailPemesanan.produk')
            ->get()
            ->sortByDesc('id');

        $namaCustomer = [];
        foreach ($orders as $order) {
            $namaCustomer = Customer::where('id', $order->id_customer)->first();
            $order->nama = $namaCustomer;
        }
        $alamat = [];
        foreach ($orders as $order) {
            $alamat = Alamat::where('id', $order->id_alamat)->get();
            $order->alamat = $alamat;

        }

        return response()->json([
            'status' => true,
            'message' => 'Success retrieve all data pemesanan',
            'data' => $orders
        ], 200);
    }


    public function addJarakDelivery(Request $request, $id)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'jarak_delivery' => 'required|numeric|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 400);
            }

            // Find the order
            $order = Pemesanan::find($id);

            if (!$order) {
                return response()->json([
                    'status' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            // Calculate shipping cost based on delivery distance
            $jarak_delivery = $request->jarak_delivery;
            if ($jarak_delivery <= 5) {
                $order->ongkir = 10000;
            } else if ($jarak_delivery > 5 && $jarak_delivery <= 10) {
                $order->ongkir = 15000;
            } else if ($jarak_delivery > 10 && $jarak_delivery <= 15) {
                $order->ongkir = 20000;
            } else if ($jarak_delivery > 15) {
                $order->ongkir = 25000;
            }

            $order->jarak_delivery = $jarak_delivery;
            $subtotal = $order->ongkir;

            // Fetch the order details and calculate subtotal
            $detailPemesanan = DetailPemesanan::where('id_pemesanan', $id)->get();
            foreach ($detailPemesanan as $detail) {
                $hargaProduk = Produk::find($detail->id_produk);
                $hargaHampers = Hampers::find($detail->id_hampers);

                if ($hargaProduk) {
                    $subtotal += $hargaProduk->harga * $detail->jumlah;
                } else if ($hargaHampers) {
                    $subtotal += $hargaHampers->harga * $detail->jumlah;
                } else {
                    // If neither product nor hampers found, skip this detail item
                    continue;
                }
            }

            // Update each detail order's subtotal
            foreach ($detailPemesanan as $detail) {
                $detail->subtotal = $subtotal;
                $detail->save();
            }

            // Update the order status and save the order
            $order->status_pesanan = 'menunggu pembayaran';
            $order->save();

            return response()->json([
                'status' => true,
                'message' => 'Jarak delivery added successfully',
                'data' => $order
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function getStatus()
    {
        $orders = Pemesanan::where('status_pesanan', 'sudah di bayar')
            ->with('detailPemesanan', 'detailPemesanan.produk')
            ->get()
            ->sortByDesc('id');
        $namaCustomer = [];
        foreach ($orders as $order) {
            $namaCustomer = Customer::where('id', $order->id_customer)->first();
            $order->nama = $namaCustomer;
        }
        $alamat = [];
        foreach ($orders as $order) {
            $alamat = Alamat::where('id', $order->id_alamat)->get();
            $order->alamat = $alamat;
        }
        return response()->json([
            'status' => true,
            'message' => 'Success retrieve all data pemesanan with status_pesanan "sudah di bayar"',
            'data' => $orders
        ], 200);
    }

    public function addPembayaran(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'uang_customer' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 400);
        }

        $order = Pemesanan::find($id);

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found'
            ], 404);
        }

        $subtotal = $order->ongkir;
        $detailPemesanan = DetailPemesanan::where('id_pemesanan', $id)->get();
        foreach ($detailPemesanan as $detail) {
            $hargaProduk = Produk::find($detail->id_produk);
            $hargaHampers = Hampers::find($detail->id_hampers);
            if ($hargaProduk) {
                $subtotal += $hargaProduk->harga * $detail->jumlah;
            } else if ($hargaHampers) {
                $subtotal += $hargaHampers->harga * $detail->jumlah;

            }
        }

        $uang_customer = $request->uang_customer;

        if ($uang_customer >= $subtotal) {
            $tip = $uang_customer - $subtotal;
            $order->uang_customer = $uang_customer;
            $order->tip = $tip;
            $order->status_pesanan = 'pembayaran valid';
            $order->save();
        } else {
            return response()->json([
                'status' => false,
                'message' => 'The payment is insufficient'
            ], 400);
        }

        return response()->json([
            'status' => true,
            'message' => 'Payment added successfully',
            'data' => $order
        ], 200);
    }

    public function getStatusPesanan()
    {
        $orders = Pemesanan::where('status_pesanan', 'pembayaran valid')
            ->with('detailPemesanan', 'detailPemesanan.produk')
            ->get()
            ->sortByDesc('id');
        $namaCustomer = [];
        foreach ($orders as $order) {
            $namaCustomer = Customer::where('id', $order->id_customer)->first();
            $order->nama = $namaCustomer;
        }
        $alamat = [];
        foreach ($orders as $order) {
            $alamat = Alamat::where('id', $order->id_alamat)->get();
            $order->alamat = $alamat;
        }
        return response()->json([
            'status' => true,
            'message' => 'Success retrieve all data pemesanan with status_pembayaran "pembayaran valid"',
            'data' => $orders
        ], 200);
    }

    public function getStatusDiproses()
    {
        $orders = Pemesanan::where('status_pesanan', 'diproses')
            ->with('detailPemesanan', 'detailPemesanan.produk')
            ->get()
            ->sortByDesc('id');

        $namaCustomer = [];
        foreach ($orders as $order) {
            $namaCustomer = Customer::where('id', $order->id_customer)->first();
            $order->nama = $namaCustomer;
        }
        $alamat = [];
        foreach ($orders as $order) {
            $alamat = Alamat::where('id', $order->id_alamat)->get();
            $order->alamat = $alamat;

        }

        return response()->json([
            'status' => true,
            'message' => 'Success retrieve all data pemesanan',
            'data' => $orders
        ], 200);
    }

    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status_pesanan' => 'required|string|in:siap di-pickup,sedang dikirim',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 400);
        }

        $order = Pemesanan::find($id);

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found'
            ], 404);
        }

        // Check jarak_delivery and status_pesanan
        if (is_null($order->jarak_delivery) && $request->status_pesanan === 'siap di-pickup') {
            $order->status_pesanan = $request->status_pesanan;
            $order->status_pesanan = 'selesai';

        } elseif (!is_null($order->jarak_delivery) && $request->status_pesanan === 'sedang dikirim') {
            $order->status_pesanan = $request->status_pesanan;
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Invalid status update for the current delivery situation'
            ], 400);
        }

        $order->save();

        return response()->json([
            'status' => true,
            'message' => 'Order status updated successfully',
            'data' => $order
        ], 200);
    }

    public function getShippedOrPickedUpOrders()
    {
        $orders = Pemesanan::whereIn('status_pesanan', ['sedang dikirim', 'siap di-pickup'])
            ->with('detailPemesanan', 'detailPemesanan.produk')
            ->get()
            ->sortByDesc('id');

        $namaCustomer = [];
        foreach ($orders as $order) {
            $namaCustomer = Customer::where('id', $order->id_customer)->first();
            $order->nama = $namaCustomer;
        }
        $alamat = [];
        foreach ($orders as $order) {
            $alamat = Alamat::where('id', $order->id_alamat)->get();
            $order->alamat = $alamat;
        }

        return response()->json([
            'status' => true,
            'message' => 'Success retrieve all shipped or picked up orders',
            'data' => $orders
        ], 200);
    }

    public function getShippedOrPickedUpOrdersByCustomer()
    {
        try {
            $customerId = Auth::user()->id;

            $orders = Pemesanan::where('id_customer', $customerId)
                ->whereIn('status_pesanan', ['sedang dikirim', 'siap di-pickup'])
                ->with('detailPemesanan', 'detailPemesanan.produk')
                ->get()
                ->sortByDesc('id');

            // Fetch customer and address details for each order
            foreach ($orders as $order) {
                $order->nama = Customer::where('id', $order->id_customer)->first();
                $order->alamat = Alamat::where('id', $order->id_alamat)->get();
            }

            return response()->json([
                'status' => true,
                'message' => 'Success retrieve all shipped or picked up orders for the logged-in customer',
                'data' => $orders
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateStatusToSelesai(Request $request, $id)
    {
        try {
            // Get the authenticated customer's ID
            $customerId = Auth::user()->id;

            // Find the order
            $order = Pemesanan::where('id', $id)
                ->where('id_customer', $customerId)
                ->whereIn('status_pesanan', ['sedang dikirim', 'siap di-pickup'])
                ->first();

            if (!$order) {
                return response()->json([
                    'status' => false,
                    'message' => 'Order not found or status not valid for update'
                ], 404);
            }

            // Update the order status to "selesai"
            $order->status_pesanan = 'selesai';
            $order->save();

            return response()->json([
                'status' => true,
                'message' => 'Order status updated to selesai successfully',
                'data' => $order
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getTelatBayar()
    {
        try {
            $today = Carbon::today()->toDateString();

            // dd($today);
            $orders = Pemesanan::whereIn('status_pesanan', ['menunggu pembayaran', 'sudah di bayar'])
                ->whereRaw('DATE_SUB(DATE(tanggal_diambil), INTERVAL 1 DAY) < ?', [$today])
                ->get();
            $namaCustomer = [];
            foreach ($orders as $order) {
                $namaCustomer = Customer::where('id', $order->id_customer)->first();
                $order->nama = $namaCustomer;
            }
            if ($orders->isEmpty()) {
                return response()->json([
                    'status' => true,
                    'message' => 'No late payment orders found',
                    'data' => []
                ], 200);
            }

            return response()->json([
                'status' => true,
                'message' => 'Success retrieve late payment orders',
                'data' => $orders
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function cancelLatePaymentOrder($id)
    {
        try {
            // Find the order
            $order = Pemesanan::find($id);

            if (!$order) {
                return response()->json([
                    'status' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            if ($order->status_pesanan != 'menunggu pembayaran' && $order->status_pesanan != 'sudah di bayar') {
                return response()->json([
                    'status' => false,
                    'message' => 'Order status not valid for cancellation'
                ], 400);
            }

            // Fetch the order details and restore stock or quota
            $detailPemesanan = DetailPemesanan::where('id_pemesanan', $id)->get();
            foreach ($detailPemesanan as $detail) {
                if ($detail->id_produk) {
                    $produk = Produk::find($detail->id_produk);
                    if ($produk) {
                        $produk->stok += $detail->jumlah;
                        $produk->save();
                    }
                } elseif ($detail->id_hampers) {
                    $hampers = Hampers::find($detail->id_hampers);
                    if ($hampers) {
                        $hampers->kuota += $detail->jumlah;
                        $hampers->save();
                    }
                }
                $detail->delete();
            }

            // Update the order status to "dibatalkan"
            $order->status_pesanan = 'dibatalkan';
            $order->save();

            return response()->json([
                'status' => true,
                'message' => 'Order cancelled successfully and stock/quota restored',
                'data' => $order
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function getLaporanProdukBulanan(Request $request)
    {
        try {
            $report = [];
            $totalPerMonth = []; // New array to store total amount and total transactions per month
            $totalAmountAllMonths = 0; // Initialize total amount for all months
            $totalTransactionsAllMonths = 0; // Initialize total transactions for all months

            // Loop through each month from January to December
            for ($month = 1; $month <= 12; $month++) {
                $formattedDate = Carbon::create(null, $month, 1)->format('Y-m');
                $laporan = Pemesanan::with('detailPemesanan.produk', 'detailPemesanan.hampers', 'costumer')
                    ->whereIn('status_pesanan', ['diproses', 'siap di-pickup', 'sudah di-pickup', 'sedang dikirim', 'selesai'])
                    ->whereRaw("DATE_FORMAT(tanggal_diambil, '%Y-%m') = ?", [$formattedDate])
                    ->get();

                // Initialize variables to store total amount and total transactions for the current month
                $totalAmount = 0;
                $transactionsCount = 0;

                // Initialize arrays to store products and hampers for the current month
                $productsThisMonth = [];
                $hampersThisMonth = [];

                foreach ($laporan as $pemesanan) {
                    $transactionsCount++; // Increment transaction count for each order
                    $customerName = $pemesanan->costumer->nama;

                    foreach ($pemesanan->detailPemesanan as $detail) {
                        if ($detail->id_produk) {
                            $produk = $detail->produk;
                            $totalAmount += $detail->jumlah * $produk->harga;
                            $productsThisMonth[] = [
                                'nama customer' => $customerName,
                                'nama' => $produk->nama_produk,
                                'harga' => $produk->harga
                            ];
                            ;
                        }

                        if ($detail->id_hampers) {
                            $hampers = $detail->hampers;
                            $totalAmount += $detail->jumlah * $hampers->harga;
                            $hampersThisMonth[] = [
                                'nama customer' => $customerName,
                                'nama' => $hampers->nama_hampers,
                                'harga' => $hampers->harga
                            ];
                        }
                    }
                }

                $totalPerMonth[$month] = [
                    'totalAmount' => $totalAmount,
                    'totalTransactions' => $transactionsCount,
                    'products' => $productsThisMonth,
                    'hampers' => $hampersThisMonth,

                ];

                $totalAmountAllMonths += $totalAmount;
                $totalTransactionsAllMonths += $transactionsCount;
            }

            $report = array_values($report);

            return response()->json([
                'status' => true,
                'data' => $report,
                'totalPerMonth' => $totalPerMonth, // Return total amount, total transactions, products, and hampers per month
                'totalAmountAllMonths' => $totalAmountAllMonths,
                'totalTransactionsAllMonths' => $totalTransactionsAllMonths,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function getLaporanBahanBakuPeriode(Request $request)
    {
        try {
            $startDate = $request->input('startDate');
            $endDate = $request->input('endDate');

            // Fetch pemesanan within the specified period with related details
            $laporan = Pemesanan::with('detailPemesanan.produk.komposisi.bahanBaku', 'detailPemesanan.hampers.komposisi.bahanBaku')
                ->whereIn('status_pesanan', ['diproses', 'siap di-pickup', 'sudah di-pickup', 'sedang dikirim', 'selesai'])
                ->whereBetween(DB::raw("DATE_FORMAT(tanggal_diambil, '%Y-%m-%d')"), [$startDate, $endDate])
                ->get();

            $totalUsage = [];

            // Iterate through each order
            foreach ($laporan as $order) {
                foreach ($order->detailPemesanan as $detail) {
                    if ($detail->produk) {
                        $items = $detail->produk->komposisi;
                    } elseif ($detail->hampers) {
                        $items = $detail->hampers->komposisi;
                    } else {
                        continue;
                    }

                    foreach ($items as $item) {
                        $bahanBakuName = $item->bahanBaku->nama_bahan_baku;
                        $satuan = $item->bahanBaku->satuan;

                        if (!isset($totalUsage[$bahanBakuName])) {
                            $totalUsage[$bahanBakuName] = [
                                'nama_bahan_baku' => $bahanBakuName,
                                'satuan' => $satuan,
                                'total_usage' => 0,
                            ];
                        }

                        $totalUsage[$bahanBakuName]['total_usage'] += $item->jumlah * $detail->jumlah;
                    }
                }
            }

            $formattedTotalUsage = array_values($totalUsage); // Re-index the array keys

            return response()->json([
                'status' => true,
                'totalUsage' => $formattedTotalUsage,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }






}
