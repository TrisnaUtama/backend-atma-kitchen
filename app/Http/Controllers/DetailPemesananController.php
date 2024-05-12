<?php

namespace App\Http\Controllers;

use App\Models\detailPemesanan;
use App\Models\Pemesanan;
use Illuminate\Http\Request;
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
            'message' => 'Success retrieve all data resep',
            'data' => $orders
        ], 200);
    }
}
