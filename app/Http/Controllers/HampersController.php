<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hampers;
use App\Models\Detail_Hampers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class HampersController extends Controller
{
    public function getAllHampers()
    {
        try {
            $hampers = Hampers::all();
            if (!$hampers) {
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

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getSpecificHampers(string $id)
    {
        try {
            $hampers = Hampers::find($id);
            if (!$hampers) {
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

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function addHampers(Request $request)
    {
        try {
            $requestData = $request->all();

            $validator = Validator::make($requestData, [
                'nama_hampers' => 'required',
                'gambar' => 'required|image',
                'harga' => 'required',
                'deskripsi' => 'required',
                'id_bahan_baku.*' => 'exists:bahan_baku,id',
                'id_produk.*' => 'exists:produk,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first(),
                ], 400);
            }

            if ($request->hasFile('gambar')) {
                $image = $request->file('gambar');
                $fileName = $request->nama_hampers . '.' . $image->getClientOriginalExtension();
                $image_uploaded_path = $image->storeAs('hampers', $fileName, 'public');
                $requestData['gambar'] = $fileName;
            }

            $hampers = Hampers::create($requestData);

            $detailData = [];
            if (isset($requestData['id_bahan_baku'])) {
                foreach ($requestData['id_bahan_baku'] as $value) {
                    $detailData[] = new Detail_Hampers([
                        'id_hampers' => $hampers->id,
                        'id_bahan_baku' => $value,
                    ]);
                }
            }

            if (isset($requestData['id_produk'])) {
                foreach ($requestData['id_produk'] as $value) {
                    $detailData[] = new Detail_Hampers([
                        'id_hampers' => $hampers->id,
                        'id_produk' => $value,
                    ]);
                }
            }

            $hampers->detailHampers()->saveMany($detailData);

            return response()->json([
                'status' => true,
                'message' => 'Success adding data products',
                'data' => $hampers
            ], 200);
        } catch (\Exception $e) {
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

            $hampers->update($hampersData);

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

            // $hampers->detailHampers()->delete();

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



    public function deleteHampersById(string $id)
    {
        try {
            $hampers = Hampers::find($id);

            if (!$hampers) {
                return response()->json([
                    'status' => false,
                    'message' => 'No hampers founded',
                ], 404);
            }

            if ($hampers->delete())
                return response([
                    'message' => 'delete Penitip success',
                    'data' => $hampers
                    ,
                ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
