<?php

namespace App\Http\Controllers;

use App\Models\detailPemesanan;
use App\Models\Pemesanan;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

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
        $orders = Pemesanan::whereNull('jarak_delivery')
            ->with('detailPemesanan', 'detailPemesanan.produk')
            ->get()
            ->sortByDesc('id');

        return response()->json([
            'status' => true,
            'message' => 'Success retrieve all data pemesanan',
            'data' => $orders
        ], 200);
    }


    public function addJarakDelivery(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'jarak_delivery' => 'required|numeric|min:0',
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

        $jarak_delivery = $request->jarak_delivery;
        if ($jarak_delivery <= 5) {
            $order->ongkir = 10000;
            $order->jarak_delivery = $request->jarak_delivery;

        } else if ($jarak_delivery > 5 && $jarak_delivery <= 10) {
            $order->ongkir = 15000;
            $order->jarak_delivery = $request->jarak_delivery;

        } else if ($jarak_delivery > 10 && $jarak_delivery <= 15) {
            $order->ongkir = 20000;
            $order->jarak_delivery = $request->jarak_delivery;

        } else if ($jarak_delivery > 15) {
            $order->ongkir = 25000;
            $order->jarak_delivery = $request->jarak_delivery;

        }

        // Hitung ulang subtotal dan update setiap detail pemesanan
        $subtotal = $order->ongkir;
        $detailPemesanan = DetailPemesanan::where('id_pemesanan', $id)->get();
        foreach ($detailPemesanan as $detail) {
            $hargaProduk = Produk::find($detail->id_produk);
            $subtotal += $hargaProduk->harga * $detail->jumlah;
        }

        foreach ($detailPemesanan as $detail) {
            $detail->subtotal = $subtotal;
            $detail->save();
        }

        $order->save();

        return response()->json([
            'status' => true,
            'message' => 'Jarak delivery added successfully',
            'data' => $order
        ], 200);
    }

    public function getStatus()
    {
        $orders = Pemesanan::where('status_pesanan', 'menunggu pembayaran')
            ->with('detailPemesanan', 'detailPemesanan.produk')
            ->get()
            ->sortByDesc('id');

        return response()->json([
            'status' => true,
            'message' => 'Success retrieve all data pemesanan with status_pembayaran "menunggu pembayaran"',
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
            $subtotal += $hargaProduk->harga * $detail->jumlah;
        }

        $uang_customer = $request->uang_customer;

        if ($uang_customer >= $subtotal) {
            $tip = $uang_customer - $subtotal;
            $order->uang_customer = $uang_customer;
            $order->tip = $tip;
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




}
