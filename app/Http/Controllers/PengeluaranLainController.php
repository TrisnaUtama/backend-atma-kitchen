<?php

namespace App\Http\Controllers;

use App\Models\PengeluaranLain;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PengeluaranLainController extends Controller
{
    public function getAllPengeluaran(){
        try{
            $pengeluaran=PengeluaranLain::all();
            if(count($pengeluaran)<=0)
                return response()->json([
                    'status' => false,
                    'message' => 'Pengeluaran is empty!',
                    'data' => $pengeluaran
                ],401);

            
            return response()->json([
                'status' => true,
                'message' => 'success retreive all data pengeluaran',
                'data' => $pengeluaran
            ],200);
        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function addPengeluaran(Request $request){
        try{
            $dataPengeluaran = $request->all();
            $validator = Validator::make($dataPengeluaran, [
                'nama_pengeluaran' => 'required',
                'total_pengeluaran' => 'required',
                'tanggal_pembelian' => 'required|date'
            ]);

            if($validator->fails()){
                return response()->json(['status' => false, 'message' => $validator->errors()], 400);
            }
    
            $dataPengeluaran['nama_pengeluaran'] = $request->nama_pengeluaran;
            $dataPengeluaran['total_pengeluaran'] = $request->total_pengeluaran;
            $dataPengeluaran['tanggal_pembelian'] = $request->tanggal_pembelian;
    
            $pengeluaran = PengeluaranLain::create($dataPengeluaran);
            return response()->json([
                'status' => 'success',
                'message' => 'Success add pengeluaran',
                'data' => $pengeluaran
            ],200);

        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function updatePengeluaran(Request $request, String $id){
        try{
            $pengeluaran = PengeluaranLain::find($id);
            if(is_null($pengeluaran)){
                return response([
                    'message' => 'Pengeluaran not found!',
                    'data' => null,
                ],400);

            }
            $updateData = $request->all();
    
            $pengeluaran->update($updateData);


            return response([
                'message' => 'Success update Pengeluaran',
                'data' => $pengeluaran,
            ], 200);
        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function deletePengeluaran(String $id){
        try{
            $pengeluaran = PengeluaranLain::find($id);


            if(is_null($pengeluaran))
                return response([
                    'message' => 'Pengeluaran not found',
                    'data' => null,
                ], 404);
                
            if($pengeluaran->delete())
                return response([
                    'message' => 'delete Pengeluaran success',
                    'data' => $pengeluaran
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
