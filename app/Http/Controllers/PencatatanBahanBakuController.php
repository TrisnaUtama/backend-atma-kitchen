<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BahanBaku;
use App\Models\Pencatatan_Bahan_Baku;
use App\Models\Pemesanan;

class PencatatanBahanBakuController extends Controller
{
    public function getBahanBakuUsage(){
        try {
            $pencatatan = Pencatatan_Bahan_Baku::with('bahan_baku')->get();
        if(empty($pencatatan)){
            return response()->json([
                'status' => false,
                'message' => 'data is empty',
            ], 404);
        }

        return response()->json([
            'status' => false,
            'message' => 'success retreive all bahan baku usage',
            'data' => $pencatatan,
        ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function addBahanBakuToPencatatan()
    {
        try {
            $bahanBakus = BahanBaku::all();

            foreach ($bahanBakus as $bahanBaku) {
                Pencatatan_Bahan_Baku::create([
                    'id_bahan_baku' => $bahanBaku->id,
                    'jumlah_terpakai' => 0,
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Successfully added all Bahan Baku to Pencatatan Bahan Baku.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to add Bahan Baku to Pencatatan Bahan Baku: ' . $e->getMessage(),
            ], 500);
        }
    }

    
}
