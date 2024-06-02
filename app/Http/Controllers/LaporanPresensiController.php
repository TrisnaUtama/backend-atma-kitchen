<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Presensi;
use App\Models\Pegawai;

class LaporanPresensiController extends Controller
{
    public function laporanPegawai(Request $request)
    {
        $query = $request->query('date');
        $yearMonth = substr($query, 0, 7);

        $presensi = Presensi::where('tanggal_presensi', 'like', $yearMonth . '%')
            ->with(['pegawai' => function ($query) {
                $query->select('id', 'nama', 'bonus', 'gaji');
            }])
            ->get();

    
        $presensiGrouped = $presensi->groupBy('id_pegawai');
        $laporan = collect();
        $totalGajiPegawai = 0; 

        foreach ($presensiGrouped as $pegawaiId => $presensiItems) {
            $jumlahHadir = $presensiItems->where('status_presensi', 'hadir')->count();
            $jumlahBolos = $presensiItems->where('status_presensi', 'tidak hadir')->count();

            $pegawai = $presensiItems->first()->pegawai;
            $pegawai->jumlah_hadir = $jumlahHadir;
            $pegawai->jumlah_bolos = $jumlahBolos;

            
            $totalGaji = $pegawai->gaji + $pegawai->bonus;
           

            $pegawai->total_gaji = $totalGaji;

            
            $totalGajiPegawai += $totalGaji;
            
            $laporan->push([
                'nama' => $pegawai->nama,
                'jumlah_hadir' => $jumlahHadir,
                'jumlah_bolos' => $jumlahBolos,
                'honor_harian' => $pegawai->gaji,
                'bonus_rajin' => $pegawai->bonus,
                'total' => $totalGaji,
            ]);
        }

        return response()->json([
            'laporan' => $laporan,
            'total_gaji_pegawai' => $totalGajiPegawai,
        ]);
    }
}
