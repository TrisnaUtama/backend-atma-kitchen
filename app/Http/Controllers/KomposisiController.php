<?php

namespace App\Http\Controllers;

use App\Models\Komposisi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KomposisiController extends Controller
{
    public function getAllKomposisi()
    {
        try {
            $komposisi = Komposisi::all();

            if (count($komposisi) <= 0)
                return response()->json([
                    'status' => false,
                    'message' => 'Komposisi is empty',
                    'data' => $komposisi,
                ], 401);

            return response()->json([
                'status' => true,
                'message' => 'Success retrieve all data komposisi',
                'data' => $komposisi
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function addKomposisi(Request $request)
    {
        try {
            $komposisiData = $request->all();
            $validate = Validator::make($komposisiData, [
                'id_resep' => 'required',
                'id_bahan_baku' => 'required',
                'jumlah' => 'required',
            ]);

            if ($validate->fails()) {
                return response()->json(['status' => false, 'message' => $validate->errors()], 400);
            }

            $komposisi = Komposisi::create($komposisiData);

            return response()->json([
                'status' => true,
                'message' => 'Success adding data komposisi',
                'data' => $komposisi
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateKomposisi(Request $request, string $id)
    {
        try {
            $komposisi = Komposisi::find($id);

            if (is_null($komposisi)) {
                return response([
                    'message' => 'Komposisi not found',
                    'data' => null,
                ], 404);
            }

            $updateData = $request->all();
            $komposisi->update($updateData);

            return response([
                'message' => 'Success update komposisi',
                'data' => $komposisi,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteKomposisiById(string $id)
    {
        try {
            $komposisi = Komposisi::find($id);

            if (is_null($komposisi))
                return response([
                    'message' => 'Komposisi not found',
                    'data' => null,
                ], 404);

            if ($komposisi->delete())
                return response([
                    'message' => 'Delete komposisi success',
                    'data' => $komposisi,
                ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
