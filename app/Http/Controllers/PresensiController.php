<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Presensi;
use Carbon\Carbon;

class PresensiController extends Controller
{
    public function addPresensi(Request $request)
    {
        $date = Carbon::now();
        // DD($date);
        // Validasi input
        $request->validate([
            'id_pegawai' => 'required',
            'status_presensi' => 'required|in:hadir,tidak hadir',
        ]);

        // Membuat presensi baru
        $presensi = new Presensi();
        $presensi->id_pegawai = $request->id_pegawai;
        $presensi->tanggal_presensi = $date;
        $presensi->status_presensi = $request->status_presensi;
        $presensi->save();

        // Mengembalikan response yang sesuai
        return response()->json(['message' => 'Presensi berhasil ditambahkan'], 201);
    }
    public function getAllPresensi()
    {
        try {
            $presensi = Presensi::all();
            if (count($presensi) <= 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Presensi is empty',
                    'data' => $presensi,
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Success retrieve all data Presensi',
                'data' => $presensi
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function updatePresensi(Request $request, $id)
    {
        try {
            $presensi = Presensi::find($id);
            if (is_null($presensi)) {
                return response([
                    'message' => 'Presensi not found',
                    'data' => null,
                ], 404);
            }
            $presensi['status_presensi'] = $request->status_presensi;


            $presensi->save();

            return response([
                'message' => 'Success update Prensensi',
                'data' => $presensi,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
