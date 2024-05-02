<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

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
            $productsData['nama_produk'] = $request->nama_produk;
            $productsData['deskripsi'] = $request->deskripsi;
            $productsData['kategori'] = $request->kategori;
            $productsData['stok'] = $request->stok;
            $productsData['harga'] = $request->harga;
            $productsData['tanggal_penitipan'] = $request->tanggal;

            $product = Produk::create($productsData);
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
            $productsData;
            if(is_null($product)){
                return response([
                    'message' => 'Product not found',
                    'data' => null,
                ], 404);
            }

            if ($request->hasFile('gambar')) {
                $uploadFolder = 'produk';
                $image = $request->file('gambar');
                $fileName = $request->nama_produk . '.' . $image->getClientOriginalExtension();
                $image_uploaded_path = $image->storeAs($uploadFolder, $fileName, 'public');
                $productsData['gambar'] = $fileName;
            }

            $productsData['id_penitip'] = $request->id_penitip;
            $productsData['nama_produk'] = $request->nama_produk;
            $productsData['deskripsi'] = $request->deskripsi;
            $productsData['kategori'] = $request->kategori;
            $productsData['stok'] = $request->stok;
            $productsData['harga'] = $request->harga;
            $productsData['tanggal_penitipan'] = $request->tanggal;

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
