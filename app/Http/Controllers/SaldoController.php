<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\detailSaldo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Exception;

class SaldoController extends Controller
{
    public function getSaldoById($id)
    {
        try {

            $saldo = Customer::with([
                'saldo' => function ($query) {
                    $query->select('id', 'jumlah_saldo');
                }
            ])
                ->where('id', $id)
                ->first();

            if ($saldo) {
                return response()->json([
                    'status' => true,
                    'data' => $saldo,
                    'message' => "Successfully retrieved saldo for customer ID $id"
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "Customer ID $id not found"
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



    public function penarikanSaldo(Request $request)
    {
        try {
            $customer = Auth::user();

            $request->validate([
                'jumlah_saldo' => 'required|numeric'
            ], [
                'jumlah_saldo.required' => 'Jumlah saldo wajib diisi.',
                'jumlah_saldo.numeric' => 'Jumlah saldo harus berupa angka.'
            ]);


            if ($customer->saldo->jumlah_saldo < $request->jumlah_saldo) {
                return response()->json([
                    'status' => false,
                    'message' => 'jumlah saldo tidak boleh kurang',
                ], 400);
            }

            $detailSaldo = detailSaldo::create([
                'id_customer' => $customer->id,
                'jumlah_saldo' => $request->jumlah_saldo,
                'tanggal_penarikan' => now(),
                'status' => 'pending'
            ]);
            return response()->json(['message' => 'Penarikan saldo berhasil diajukan', 'data' => $detailSaldo], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function historySaldo()
    {

        $customerId = Auth::id();
        $history = detailSaldo::where('id_customer', $customerId)
            ->where('status', 'confirmed')
            ->get();

        if ($history->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Tidak ada history penarikan saldo yang dikonfirmasi'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'History penarikan saldo yang dikonfirmasi ditemukan',
            'data' => $history
        ], 200);
    }

}
