<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProdukController extends Controller
{
    public function getAllProduk()
    {
        try{
            $products = Produk::all();
            if(count($products) <= 0)
                return response()->json([
                    'status' => false,
                    'message' => 'products is empty',
                    'data' => $products,
                ], 401);
            
            return response()->json([
                'status' => true,
                'message' => 'success retreive all data products',
                'data' => $products
            ], 200);

        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        } 
    }

    public function addingProduct(Request $request)
    {
        try{
            $productsData = $request->all();
            $validate = Validator::make($productsData, [
                'id_resep' => 'required',
                'nama_produk' => 'required',
                'gambar' => 'required',
                'deskripsi' => 'required',
                'kategori' => 'required',
                'harga' => 'required',
            ]);

            if ($request->hasFile('gambar')) {
                $image = $request->file('gambar');
                $imageName = $image->getClientOriginalName();
                $image->move(public_path('gambar_produk'), $imageName);
                $registrationData['gambar'] = $imageName;
            }

            if($validate->fails()){
                return response()->json(['status' => false, 'message' => $validate->errors()], 400);
            }

            $productsData['id_resep'] = $request->id_resep;
            $productsData['nama_produk'] = $request->nama_produk;
            $productsData['deskripsi'] = $request->deskripsi;
            $productsData['kategori'] = $request->kategori;
            $productsData['gambar'] = $request->gambar;
            $productsData['harga'] = $request->harga;

            $product = Produk::create($productsData);
            return response()->json([
                'status' => true,
                'message' => 'success adding data products',
                'data' => $product
            ], 200);

        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateProduct(Request $request, string $id)
    {
        try {
            $product = Produk::find($id);
            if(is_null($product)){
                return response([
                    'message' => 'Product not found',
                    'data' => null,
                ], 404); 
            }
            $updateData = $request->all();
    
            $product->update($updateData);
    
            return response([
                'message' => 'Success update product',
                'data' => $product,
            ], 200);
    
        } catch(Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
    public function deleteProductById( string $id)
    {
        try{
            $product = Produk::find($id);

            if(is_null($product))
                return response([
                    'message' => 'Product not found',
                    'data' => null,
                ], 404);
                
            if($product->delete())
                return response([
                    'message' => 'delete product success',
                    'data' => $product,
                ], 200);
        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getProductById(Request $request){
        try{
            $productName = $request->all();
            dd($productName);
            if(is_null($productName)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Product name parameter is empty',
                    'data' => null,
                ], 400); 
            }
            $product = Produk::where('nama_produk', 'like', "%$productName%")->first();
            if(!$product)
                return response()->json([
                    'status' => false,
                    'message' => 'Product not found',
                    'data' => null,
                ], 404);
            
            return response()->json([
                'status' => true,
                'message' => 'Success retrieve product: ' . $product->nama_produk,
                'data' => $product
            ], 200);
            
        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
}
