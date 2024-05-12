<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class GajiBonusController extends Controller
{

    public function ubahGaji(Request $request, $id)
    {
        $pegawai = Pegawai::find($id);

        if (!$pegawai) {
            return response()->json(['message' => 'Pegawai not found'], 404);
        }

        if ($request->has('gaji') && $request->gaji !== null) {
            $pegawai->gaji = $request->gaji;
        }

        // Memeriksa apakah bidang 'gender' ada dalam permintaan dan bukan null
        if ($request->has('bonus') && $request->bonus !== null) {
            $pegawai->bonus = $request->bonus;
        }
        if ($pegawai->save()) {
            return response()->json(['message' => 'Profile updated successfully', 'data' => $pegawai]);
        } else {
            return response()->json(['message' => 'Failed to update profile']);
        }


        return response()->json(['message' => 'Pegawai successfully updated', 'data' => $pegawai]);
    }
}
