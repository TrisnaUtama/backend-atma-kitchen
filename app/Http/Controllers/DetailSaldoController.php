<?php

namespace App\Http\Controllers;

use App\Models\detailSaldo;
use App\Models\Customer;
use Exception;

class DetailSaldoController extends Controller
{

    public function listPenarikanSaldo()
    {
        try {
            $penarikanSaldo = detailSaldo::with('customer')
            ->get();
            return response()->json([
                'status' => true,
                'message' => 'berhasil ambil data penarikan saldo',
                'data' => $penarikanSaldo,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function confirmSaldo($id)
    {
        $detailSaldo = detailSaldo::where('status','pending')->find($id);

        if (!$detailSaldo) {
            return response()->json([
                'status' => false,
                'message' => 'Tidak ada pengajuan penarikan saldo'
            ], 404);
        }

        if ($detailSaldo->status != 'pending') {
            return response()->json([
                'status' => false,
                'message' => 'Penarikan saldo sudah di konfirmasi'
            ], 400);
        }

        $customer = Customer::find($detailSaldo->id_customer);
        if (!$customer) {
            return response()->json([
                'status' => false,
                'message' => 'Customer tidak ditemukan'
            ], 404);
        }


        if ($customer->saldo->jumlah_saldo < $detailSaldo->jumlah_saldo) {
            return response()->json([
                'status' => false,
                'message' => 'Jumlah saldo tidak cukup'
            ], 400);
        }

        $customer->saldo->jumlah_saldo -= $detailSaldo->jumlah_saldo;
        $customer->saldo->save();

        $detailSaldo->status = 'confirmed';
        $detailSaldo->tanggal_konfirmasi = now();
        $detailSaldo->save();

        return response()->json([
            'status' => true,
            'message' => 'Penarikan saldo berhasil dikonfirmasi',
            'data' => $detailSaldo
        ], 200);
    }


}
