<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\Pemesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BahanBakuController extends Controller
{

    public function getBahanBakuUsage()
    {
        try {
            $processedOrders = Pemesanan::with([
                'detailPemesanan.produk.resep.komposisi.bahanBaku',
                'detailPemesanan.hampers.detailHampers.produk.resep.komposisi.bahanBaku'
            ])->where('status_pesanan', 'diproses')->get();

            if ($processedOrders->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data is empty',
                    'data' => $processedOrders,
                ], 404);
            }

            $bahanBakuUsage = [];

            foreach ($processedOrders as $order) {
                foreach ($order->detailPemesanan as $detail) {
                    $produk = $detail->produk;
                    if ($produk) {
                        $this->accumulateBahanBakuUsage($produk, $detail->jumlah, $bahanBakuUsage);
                    }

                    $hampers = $detail->hampers;
                    if ($hampers) {
                        foreach ($hampers->detailHampers as $detailHampers) {
                            $produkInHampers = $detailHampers->produk;
                            if ($produkInHampers) {
                                $this->accumulateBahanBakuUsage($produkInHampers, $detail->jumlah, $bahanBakuUsage);
                            }
                        }
                    }
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Successfully retrieved bahan baku usage data',
                'data' => $bahanBakuUsage,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function accumulateBahanBakuUsage($produk, $orderQuantity, &$bahanBakuUsage){
        $resep = $produk->resep;
        if ($resep) {
            foreach ($resep->komposisi as $komposisi) {
                $bahanBaku = $komposisi->bahanBaku;
                if ($bahanBaku) {
                    $bahanBakuId = $komposisi->id_bahan_baku;
                    $bahanBakuName = $bahanBaku->nama_bahan_baku; 
                    $jumlah = $komposisi->jumlah * $orderQuantity;

                    if (isset($bahanBakuUsage[$bahanBakuId])) {
                        $bahanBakuUsage[$bahanBakuId]['jumlah'] += $jumlah;
                    } else {
                        $bahanBakuUsage[$bahanBakuId] = [
                            'id' => $bahanBakuId,
                            'name' => $bahanBakuName,
                            'jumlah' => $jumlah,
                        ];
                    }
                }
            }
        }
    }

    public function getAllBahanBaku()
    {
        try{
            $bahan_bakus = BahanBaku::all();
            if(count($bahan_bakus) <= 0)
                return response()->json([
                    'status' => false,
                    'message' => 'bahan_bakus is empty',
                    'data' => $bahan_bakus,
                ], 401);

            return response()->json([
                'status' => true,
                'message' => 'success retreive all data bahan_bakus',
                'data' => $bahan_bakus
            ], 200);

        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function addBahanBaku(Request $request)
    {
        try{
            $bahan_bakusData = $request->all();
            $validate = Validator::make($bahan_bakusData, [
                'nama_bahan_baku' => 'required',
                // 'stok' => 'required',
                'satuan' => 'required',
            ]);

            if($validate->fails()){
                return response()->json(['status' => false, 'message' => $validate->errors()], 400);
            }

            $bahanBaku = BahanBaku::where('nama_bahan_baku', $request->nama_bahan_baku)->first();

            if($bahanBaku){
                return response()->json(['status' => false, 'message' => 'bahan baku already exist'], 400);
            }else{
            $bahan_bakusData['nama_bahan_baku'] = $request->nama_bahan_baku;
            // $bahan_bakusData['stok'] = $request->stok;
            $bahan_bakusData['satuan'] = $request->satuan;


            $bahan_bakus = BahanBaku::create($bahan_bakusData);
            return response()->json([
                'status' => true,
                'message' => 'success adding data products',
                'data' => $bahan_bakus
            ], 200);
        }
        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateBahanBaku(Request $request, string $id)
    {
        try {
            $bahan_bakus = BahanBaku::find($id);
            if(is_null($bahan_bakus)){
                return response([
                    'message' => 'Bahan Baku not found',
                    'data' => null,
                ], 404);
            }
            $updateData = $request->all();

            $bahan_bakus->update($updateData);

            return response([
                'message' => 'Success update Bahan Baku',
                'data' => $bahan_bakus,
            ], 200);

        } catch(Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteBahanBakuById( string $id)
    {
        try{
            $bahan_bakus= BahanBaku::find($id);

            if(is_null($bahan_bakus))
                return response([
                    'message' => 'Bahan Baku not found',
                    'data' => null,
                ], 404);

            if($bahan_bakus->delete())
                return response([
                    'message' => 'delete Bahan Baku success',
                    'data' => $bahan_bakus
                    ,
                ], 200);
        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getBahanBakuById(String $id){
        try{
            $bahan_bakus = BahanBaku::find($id);
            if(!$bahan_bakus)
                return response()->json([
                    'status' => false,
                    'message' => 'Bahan Baku not found',
                    'data' => null,
                ], 404);

            return response()->json([
                'status' => true,
                'message' => 'Success retrieve bahan baku: ' . $bahan_bakus->nama_bahan_baku,
                'data' => $bahan_bakus
            ], 200);

        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

}
