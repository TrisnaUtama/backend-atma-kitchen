<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Produk;
use App\Models\Limit_Produk;
use App\Models\Hampers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class Limit_ProdukController extends Controller{

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

    public function getLimitProdukToday() {
        try {
            $today = Carbon::today()->toDateString();
            
            $produk = Produk::whereHas('limit', function($query) use ($today) {
                    $query->where('tanggal_limit', $today);
                })
                ->orWhere('stok', '>', 0)
                ->with(['limit' => function($query) use ($today) {
                    $query->where('tanggal_limit', $today);
                }])
                ->get();
            
            $produkWithSum = [];
            foreach ($produk as $item) {
                $stok = $item->stok ?? 0;
                $limit = $item->limit->first()->limit ?? 0;
                $sum = $stok + $limit;
                $produkWithSum[$item->id] = $sum;
            }
            
            $detailhampers = Hampers::with('detailHampers.produk')->get();
            $hampers = Hampers::all();
            
            foreach ($detailhampers as $hampersItem) {
                $productStocks = [];
                foreach ($hampersItem->detailHampers as $detail) {
                    $productId = $detail->id_produk;
                    if (isset($produkWithSum[$productId])) {
                        $productStocks[] = $produkWithSum[$productId];
                    }
                }
                
                if (!empty($productStocks)) {
                    $minStock = min($productStocks);
                    $hampersItem->update(['stok' => $minStock]);
                }
            }
            
            if ($produk->isEmpty() && $hampers->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'produk is empty' 
                ], 500);
            }
    
            return response()->json([
                'status' => true,
                'data' => [
                    'produk' => $produk,
                    'hampers' => $hampers
                ]
            ], 200);
    
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage() 
            ], 500);
        }
    }
    
    
    

    public function getLimitHampers(){
        try{

        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
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
