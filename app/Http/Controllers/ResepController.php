<?php

namespace App\Http\Controllers;

use App\Models\Resep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class ResepController extends Controller
{
    public function getAllResep()
    {
        try {
            $reseps = Resep::all();
            if (count($reseps) <= 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Resep is empty',
                    'data' => $reseps,
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Success retrieve all data resep',
                'data' => $reseps
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function addResep(Request $request)
    {
        try {
            $resepsData = $request->all();
            $validate = Validator::make($resepsData, [
                'nama_resep' => 'required',
            ]);

            if ($validate->fails()) {
                return response()->json(['status' => false, 'message' => $validate->errors()], 400);
            }

            $resepsData['nama_resep'] = $request->nama_resep;

            $resep = Resep::create($resepsData);
            return response()->json([
                'status' => true,
                'message' => 'Success adding data resep',
                'data' => $resep
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateResep(Request $request, string $id)
    {
        try {
            $resep = Resep::find($id);
            if (is_null($resep)) {
                return response([
                    'message' => 'Resep not found',
                    'data' => null,
                ], 404);
            }
            $updateData = $request->all();

            $resep->update($updateData);

            return response([
                'message' => 'Success update resep',
                'data' => $resep,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteResepById(string $id)
    {
        try {
            $resep = Resep::find($id);

            if (is_null($resep))
                return response([
                    'message' => 'Resep not found',
                    'data' => null,
                ], 404);

            if ($resep->delete())
                return response([
                    'message' => 'Delete resep success',
                    'data' => $resep,
                ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
