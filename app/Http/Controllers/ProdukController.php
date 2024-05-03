<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Limit_Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;


class ProdukController extends Controller
{
    public function getAllProduk()
    {
        try {
            $products = Produk::where('status', 1)->get();  
            if ($products->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No products found with status 1',
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Successfully retrieved products with status 1',
                'data' => $products
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function addingProduct(Request $request)
    {
        try {
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
                $uploadFolder = 'produk';
                $image = $request->file('gambar');
                $fileName = $request->nama_produk . '.' . $image->getClientOriginalExtension();
                $image_uploaded_path = $image->storeAs($uploadFolder, $fileName, 'public');
                $productsData['gambar'] = $fileName;
            }

            if ($validate->fails()) {
                return response()->json(['status' => false, 'message' => $validate->errors()], 400);
            }

            $productsData['id_penitip'] = $request->id_penitip;
            $productsData['id_resep'] = $request->id_resep;
            $productsData['nama_produk'] = $request->nama_produk;
            $productsData['deskripsi'] = $request->deskripsi;
            $productsData['kategori'] = $request->kategori;
            if($request->has('stok')){
                $productsData['stok'] = $request->stok;
            }else{
                $productsData['stok'] = 0;
            }
            $productsData['harga'] = $request->harga;
            $productsData['status'] = 1;
            $productsData['tanggal_penitipan'] = $request->tanggal;
            $today = Carbon::today();

            $product = Produk::create($productsData);
            $limit_produk = Limit_Produk::create([
                'id_produk' => $product->id,
                'limit' => 0,
                'tanggal_limit' => $today,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Success adding data products',
                'data' => $product
            ], 200);

        } catch (Exception $e) {
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

        $productsData = [];

        if ($request->has('id_penitip')) {
            $productsData['id_penitip'] = $request->id_penitip;
        }

        if ($request->has('id_resep')) {
            $productsData['id_resep'] = $request->id_resep;
        }

        if ($request->has('nama_produk')) {
            $productsData['nama_produk'] = $request->nama_produk;
        }

        if ($request->has('deskripsi')) {
            $productsData['deskripsi'] = $request->deskripsi;
        }

        if ($request->has('kategori')) {
            $productsData['kategori'] = $request->kategori;
        }

        if($request['kategori'] != "Titipan"){
            $productsData['id_penitip'] = null;
            $productsData['tanggal_penitipan']  = null;
        }

        if ($request->has('stok')) {
            $productsData['stok'] = $request->stok;
        }

        if ($request->has('harga')) {
            $productsData['harga'] = $request->harga;
        }

        if ($request->has('tanggal')) {
            $productsData['tanggal_penitipan'] = $request->tanggal;
        }

        if ($request->hasFile('gambar')) {
            $uploadFolder = 'produk';
            $image = $request->file('gambar');
            $fileName = $request->nama_produk . '.' . $image->getClientOriginalExtension();
            $image_uploaded_path = $image->storeAs($uploadFolder, $fileName, 'public');
            $productsData['gambar'] = $fileName;
        }

        $product->update($productsData);

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


    public function deleteProductById(string $id)
    {
        try{
            $product = Produk::find($id);

            if(is_null($product))
                return response([
                    'message' => 'Product not found',
                    'data' => null,
                ], 404);

            $product['status'] = 0;
            $product->save();

            if(!$product->status)
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

    public function getProductById(string $id){
        try{
            $productName = Produk::find($id);
            if(!$productName) {
                return response()->json([
                    'status' => false,
                    'message' => 'Product name parameter is empty',
                    'data' => null,
                ], 400);
            }

            
            return response()->json([
                'status' => true,
                'message' => 'Success retrieve product',
                'data' => $productName
            ], 200);

        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

}
