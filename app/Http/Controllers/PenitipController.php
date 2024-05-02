<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penitip;
use Illuminate\Support\Facades\Validator;

class PenitipController extends Controller
{
    public function getAllPenitip()
    {
        try{
            $penitip = Penitip::all();
            if(count($penitip) <= 0)
                return response()->json([
                    'status' => false,
                    'message' => 'penitip is empty',
                    'data' => $penitip,
                ], 401);
            
            return response()->json([
                'status' => true,
                'message' => 'success retreive all data penitip',
                'data' => $penitip
            ], 200);

        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        } 
    }

    public function addPenitip(Request $request)
    {
        try{
            $penitipData = $request->all();
            $validate = Validator::make($penitipData, [
                'nama' => 'required',
                'no_telpn' => 'required',
                'email' => 'required',
            ]);

            if($validate->fails()){
                return response()->json(['status' => false, 'message' => $validate->errors()], 400);
            }
           
            $penitipData['nama'] = $request->nama;
            $penitipData['no_telpn'] = $request->no_telpn;
            $penitipData['email'] = $request->email;
            $penitipData['profit'] = $request->profit;
    

            $penitip = Penitip::create($penitipData);
            return response()->json([
                'status' => true,
                'message' => 'success adding data penitips',
                'data' => $penitip
            ], 200);
        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function updatePenitip(Request $request, string $id)
    {
        try {
            $penitip = Penitip::find($id);
            if(is_null($penitip)){
                return response([
                    'message' => 'Penitip not found',
                    'data' => null,
                ], 404); 
            }
            $updateData = $request->all();
    
            $penitip->update($updateData);
    
            return response([
                'message' => 'Success update Penitip',
                'data' => $penitip,
            ], 200);
    
        } catch(Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function deletePenitipById( string $id)
    {
        try{
            $penitip = Penitip::find($id);

            if(is_null($penitip))
                return response([
                    'message' => 'Penitip not found',
                    'data' => null,
                ], 404);
                
            if($penitip->delete())
                return response([
                    'message' => 'delete Penitip success',
                    'data' => $penitip
                    ,
                ], 200);
        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getPenitipById(Request $request){
        try{
            $penitipName = $request->all();
            dd($penitipName);
            if(is_null($penitipName)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Penitip name parameter is empty',
                    'data' => null,
                ], 400); 
            }
            $penitip = Penitip ::where('nama', 'like', "%$penitipName%")->first();
            if(!$penitip)
                return response()->json([
                    'status' => false,
                    'message' => 'Penitip not found',
                    'data' => null,
                ], 404);
            
            return response()->json([
                'status' => true,
                'message' => 'Success retrieve penitip: ' . $penitip->nama,
                'data' => $penitip
            ], 200);
            
        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
}
