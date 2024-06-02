<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pemesanan;
use App\Models\DetailPemesanan;
use App\Models\Produk;
use App\Models\Penitip;



class LaporanPenitipController extends Controller
{
    public function showPenitipData($penitipId)
    {
        $result = Pemesanan::with(['detailPemesanan.produk.penitip'])
            ->where('status_pesanan', 'selesai')
            ->get()
            ->flatMap(function ($pemesanan) use ($penitipId) {
                return $pemesanan->detailPemesanan->filter(function ($detail) use ($penitipId) {
                    return $detail->produk->penitip->id == $penitipId;
                })->map(function ($detail) {
                    $produk = $detail->produk;
                    $penitip = $produk->penitip;
                    $total = $produk->harga * $detail->jumlah;
                    $komisi = $total * 0.20;
                    $diterima = $total - $komisi;
                    
                    return [
                        'nama_produk' => $produk->nama_produk,
                        'banyaknya_terjual' => $detail->jumlah,
                        'harga' => $produk->harga,
                        'total' => $total,
                        'komisi' => $komisi,
                        'total_diterima' => $diterima,
                        'penitip_id' => $penitip->id ?? null,
                        'penitip_nama' => $penitip->nama ?? null,
                        
                    ];
                });
            });
            $totalDiterima = $result->sum('total_diterima');

            $penitip = Penitip::find($penitipId);

            return response()->json([
                'data' => $result,
                'total_uang' => $totalDiterima,
                'penitip' => $penitip,
            ]);
    }

    public function getAllPenitip()
    {
        try{
            $penitip = Penitip::all();
            if($penitip->isEmpty()){
                return response()->json([
                    'status' => false,
                    'message' => 'No Penitip Data found ',
                ], 404);
            }
            return response()->json([
                'status' => true,
                'message' => 'Successfully retrieved penitip',
                'data' => $penitip
            ], 200);
        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}

