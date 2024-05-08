<?php

namespace App\Http\Controllers;

use App\Models\PembelianBahanBaku;
use App\Models\BahanBaku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PembelianBahanBakuController extends Controller
{
    public function getAll(Request $request)
    {
        try {
            $pembelianData = PembelianBahanBaku::all();
            if (!$pembelianData) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data not found'
                ], 500);
            }

            return response()->json([
                'status' => true,
                'message' => 'success retrieve data bahan baku',
                'data' => $pembelianData
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getSpecificPemebelian(Request $request, string $id)
    {
        try {
            $pembelian = PembelianBahanBaku::find($id);

            if (!$pembelian) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data not found'
                ], 500);
            }

            return response()->json([
                'status' => true,
                'message' => 'success retrieve data bahan baku',
                'data' => $pembelian
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function addPembelianBahanBaku(Request $request)
    {
        try {
            $pembelianData = $request->all();
            $validate = Validator::make($pembelianData, [
                'id_bahan_baku' => 'required',
                'harga' => 'required',
                'jumlah' => 'required',
            ]);

            if ($validate->fails()) {
                return response()->json(['status' => false, 'message' => $validate->errors()], 400);
            }


            $bahan_baku = BahanBaku::where('id', $request->id_bahan_baku)->first();
            $new_jumlah = $bahan_baku->stok + $request->jumlah;

            $bahan_baku->stok = $new_jumlah;
            $bahan_baku->save();

            $pembelianData['id_bahan_baku'] = $request->id_bahan_baku;
            $pembelianData['jumlah'] = $request->jumlah;
            $pembelianData['harga'] = $request->harga;
            $pembelianData['nama'] = $bahan_baku->nama_bahan_baku;

            $createPembelian = PembelianBahanBaku::create($pembelianData);
            return response()->json([
                'status' => true,
                'message' => 'Success adding data products',
                'data' => $createPembelian
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function updatePembelianBahanBaku(Request $request, string $id)
    {
        try {
            $pembelian = PembelianBahanBaku::find($id);
            if (!$pembelian) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data not found'
                ], 500);
            }
            $bahan_baku = BahanBaku::where('id', $request->id_bahan_baku)->first();

            $updatePembelian = [];
            if ($request->has('id_bahan_baku')) {
                $updatePembelian['id_bahan_baku'] = $request->id_bahan_baku;
                $updatePembelian['nama'] = $bahan_baku->nama_bahan_baku;
            }
            if ($request->has('jumlah')) {
                $new_jumlah = $bahan_baku->stok + $request->jumlah;
                $bahan_baku->stok = $new_jumlah;
                $bahan_baku->save();
                $updatePembelian['jumlah'] = $request->jumlah;
            }
            if ($request->has('harga')) {
                $updatePembelian['harga'] = $request->harga;
            }


            $pembelian->update($updatePembelian);
            return response([
                'message' => 'Success update product',
                'data' => $pembelian,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function deletePmebelianBahanBaku(string $id)
    {
        try {
            $pembelian = PembelianBahanBaku::find($id);
            if (!$pembelian) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data not found'
                ], 500);
            }

            if ($pembelian->delete()) {
                return response([
                    'message' => 'delete Penitip success',
                    'data' => $pembelian
                    ,
                ], 200);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
