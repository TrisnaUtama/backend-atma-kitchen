<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hampers;
use App\Models\Detail_Hampers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class HampersController extends Controller
{
    public function getAllHampers(){
        try{
            $hampers = Hampers::all();
            if($hampers->isEmpty()){
                return response()->json([
                    'status' => false,
                    'message' => 'No hampers founded',
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Successfully retrieved hampers ',
                'data' => $hampers
            ], 200);

        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);    
        }
    }

    public function getSpecificHampers(string $id){
        try{
            $hampers = Hampers::find($id);
            if($hampers->isEmpty()){
                return response()->json([
                    'status' => false,
                    'message' => 'No hampers founded',
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Successfully retrieved hampers ',
                'data' => $hampers
            ], 200);

        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);    
        }
    }

    public function addHampers(Request $request){
        try{
            $hampersData = $request->all();
            $validate = Validator::make($hampersData, [
                'nama_hampers' => 'required',
                'gambar' => 'required',
                'harga' => 'required',
                'deskripsi' => 'required',
            ]);

            if ($request->hasFile('gambar')) {
                $uploadFolder = 'hampers';
                $image = $request->file('gambar');
                $fileName = $request->nama_hampers . '.' . $image->getClientOriginalExtension();
                $image_uploaded_path = $image->storeAs($uploadFolder, $fileName, 'public');
                $hampersData['gambar'] = $fileName;
            }

            $hampersData['deskripsi'] = $request->deskripsi;
            $hampersData['harga'] = $request->harga;
            $hampersData['nama_hampers'] = $request->nama_hampers;
            
            $hampers = Hampers::create($hampersData);
            $detail = $request->input();
            foreach ($detail['id_bahan_baku'] as $key => $value) {
                if (isset($value)) { 
                    $detailData = new Detail_Hampers;
                    $detailData['id_hampers'] = $hampers->id;
                    $detailData['id_bahan_baku'] = $value; 
                    $detailData->save(); 
                }
            }
        
            foreach ($detail['id_produk'] as $key => $value) {
                if (isset($value)) { 
                    $detailData = new Detail_Hampers;
                    $detailData['id_hampers'] = $hampers->id;
                    $detailData['id_produk'] = $value;
                    $detailData->save(); 
                } 
            }

            return response()->json([
                'status' => true,
                'message' => 'Success adding data products',
                'data' => $hampers
            ], 200);
        }catch(Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateHampers(Request $request, string $id)
{
    try {
        $hampers = Hampers::find($id);
        if (!$hampers) {
            return response()->json([
                'status' => false,
                'message' => 'Hampers not found',
            ], 404);
        }

        $hampersData = [];

        if ($request->has('nama_hampers')) {
            $hampersData['nama_hampers'] = $request->nama_hampers;
        }
        if ($request->has('harga')) {
            $hampersData['harga'] = $request->harga;
        }
        if ($request->has('deskripsi')) {
            $hampersData['deskripsi'] = $request->deskripsi;
        }
        if ($request->hasFile('gambar')) {
            $uploadFolder = 'hampers';
            $image = $request->file('gambar');
            $fileName = $request->nama_hampers . '.' . $image->getClientOriginalExtension();
            $image_uploaded_path = $image->storeAs($uploadFolder, $fileName, 'public');
            $hampersData['gambar'] = $fileName;
        }

        if ($request->has('id_bahan_baku') && $request->has('id_produk')) {
            $hampers->detailHampers()->delete();
            
            foreach ($request->id_bahan_baku as $bahan_baku_id) {
                $detailData = new Detail_Hampers;
                $detailData->id_hampers = $hampers->id;
                $detailData->id_bahan_baku = $bahan_baku_id;
                $detailData->save();
            }

            foreach ($request->id_produk as $produk_id) {
                $detailData = new Detail_Hampers;
                $detailData->id_hampers = $hampers->id;
                $detailData->id_produk = $produk_id;
                $detailData->save();
            }
        }
        $hampers->detailHampers()->delete();

        return response()->json([
            'message' => 'Success update product',
            'data' => $hampers,
        ], 200);

    } catch (Exception $e) {
        return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
}

    

    public function deleteHampersById(string $id){
        try{
            $hampers = Hampers::find($id);

            if(!$hampers){
                return response()->json([
                    'status' => false,
                    'message' => 'No hampers founded',
                ], 404);
            }

            if($hampers->delete())
            return response([
                'message' => 'delete Penitip success',
                'data' => $hampers
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
