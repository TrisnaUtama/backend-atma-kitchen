<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Detail_Hampers;

class DetailHampersController extends Controller
{
    public function getAllDetail(Request $request){
        try{
            $detail = Detail_Hampers::all();

            if(!$detail){
                return response()->json([
                    'status' => false,
                    'message' => 'detail hampers is empty'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'successfuly reterieve detail hampers',
                'data' => $detail
            ], 200);

        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function addDetail(Request $request){
        try{
            $detail = $request->all();
            $validate = Validator::make($detail, [
                'id_produk' => 'required',
                'id_hampers' => 'required',
                'id_hampers' => 'required',
            ]);

            if ($validate->fails()) {
                return response()->json(['status' => false, 'message' => $validate->errors()], 400);
            }

            $detail['id_produk'] = $request->id_produk;
            $detail['id_bahan_baku'] = $request->id_bahan_baku;
            $detail['id_hampers'] = $request->id_hampers;

            $createDetail = Detail_Hampers::create($detail);
            return response()->json([
                'status' => true,
                'message' => 'Success adding data products',
                'data' => $createDetail
            ], 200);

        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateDetail(Request $request, string $id){
        try{
            $detail = Detail_Hampers::find($id);
            if(!$detail)
                return response()->json([
                    'status' => false,
                    'message' => 'detail hampers is empty'
                ], 404);

            $dataDetail = [];

            if ($request->has('id_produk')) {
                $dataDetail['id_produk'] = $request->id_produk;
            }
            if ($request->has('id_hampers')) {
                $dataDetail['id_hampers'] = $request->id_hampers;
            }
            if ($request->has('id_bahan_baku')) {
                $dataDetail['id_bahan_baku'] = $request->id_bahan_baku;
            }

            $detail->update($dataDetail);

            return response([
                'message' => 'Success update product',
                'data' => $detail,
            ], 200);
        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteDetail(stirng $id){
        try{
            $detail = Detail_Hampers::find($id);
            if(is_null($detail)){
                return response([
                    'message' => 'Data not found',
                    'data' => null,
                ], 404);
            }

            if($detail->delete())
            return response([
                'message' => 'delete Penitip success',
                'data' => $detail
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
