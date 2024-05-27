<?php

namespace App\Http\Controllers;
use App\Models\Saldo;
use App\Models\Customer;
use Illuminate\Http\Request;

class SaldoController extends Controller
{
    public function getSaldoById($id)
    {
        try {
            $saldo = Customer::with([
                'saldo' => function ($query) {
                    $query->select('id', 'jumlah_saldo');
                }
            ]) ->first();
            return response()->json([
                'status' => true,
                'data' => $saldo,
                'message' => `successfuly retreive all data pemesanan by id $id`
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
