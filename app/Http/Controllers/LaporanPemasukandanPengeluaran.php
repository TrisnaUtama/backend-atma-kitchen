<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\detailPemesanan;
use App\Models\Pesanan;
use App\Models\PengeluaranLain;

class LaporanPemasukandanPengeluaran extends Controller
{
    public function laporanBulanan(Request $request)
    {
        $query = $request->query('date');
        $yearMonth = substr($query, 0, 7);
        $pesanan = Pesanan::where('status_pesanan', 'selesai')
            ->where(fn($query) => $query->where('tanggal_diambil', 'like', $yearMonth . '%'))
            ->get();

            $pengeluaran = PengeluaranLain::select('nama_pengeluaran', 'total_pengeluaran')
            ->where('tanggal_pembelian', 'like', $yearMonth . '%')
            ->get();
        


        $detailPemesanan = $pesanan->flatMap(function ($pesanan) {
            return $pesanan->detail_pemesanan;
        })->pluck('subtotal');

        $penjualan = $detailPemesanan->sum();

        $tip = $pesanan->pluck('tip')->sum();
        $total = $penjualan + $tip;
        $totalpengeluaran = $pengeluaran->pluck('total_pengeluaran')->sum();

        return response()->json([
            'penjualan' => $penjualan,
            'tip' => $tip,
            'total' => $total,
            'pengeluaran' => $pengeluaran,
            'jumlah_pengeluaran' => $totalpengeluaran,
        ]);
    }

}
