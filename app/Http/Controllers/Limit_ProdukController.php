<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\Limit_Produk;
use Illuminate\Support\Facades\Validator;

class Limit_ProdukController extends Controller
{
    public function getLimitAllProduk(){
        try{
            $limit = Limit_Produk::all();
            if($limit->isEmpty()){
                return response()->json([
                    'status' => false,
                    'message' => 'No Limit Produk Data found ',
                ], 404);
            }
            return response()->json([
                'status' => true,
                'message' => 'Successfully retrieved Limit Produk',
                'data' => $limit
            ], 200);
        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function addLimitProduk(Request $request){
        try{
            $limitData = $request->all();

            $validate = Validator::make($limitData, [
                'id_produk' => 'required',
                'limit' => 'required',
                'tanggal' => 'required'
            ]);

            if($validate->fails()){
                return response()->json(['status' => false, 'message' => $validate->errors()], 400);
            }

            $limitData['id_produk'] = $request->id_produk;
            $limitData['limit'] = $request->limit;
            $limitData['jumlah'] = $request->jumlah;
            $limit = Limit_Produk::create($limitData);

            return response()->json([
                'status' => true,
                'message' => 'Success adding data Limit Produk',
                'data' => $limit
            ], 200);

        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function editLimitProduk(Request $request,string $id){
        try{
            $specificLimit = Limit_Produk::where('id_produk', $id)->first();
            $limitData = [];
            if(!$specificLimit){
                return response([
                    'message' => 'Produk Limit not found',
                    'data' => null,
                ], 404);
            }
            $limitData['limit'] = $request->limit;
            $limitData['tanggal_limit'] = $request->tanggal_limit;
            $specificLimit->update($limitData);
            
            return response([
                'message' => 'Success update product',
                'data' => $specificLimit,
            ], 200);

        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteLimitProduk(string $id){
        try{
            $specificLimit = Limit_Produk::find($id);
            $specificLimitDate = $specificLimit['tanggal'];
            if(!$specificLimit){
                return response([
                    'message' => 'Produk Limit not found',
                    'data' => null,
                ], 404);
            }

            $specificLimit->delete();
            return response([
                'message' => "Delete Limit in ".$specificLimitDate,
                'data' => $specificLimit
                ,
            ], 200);

        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}