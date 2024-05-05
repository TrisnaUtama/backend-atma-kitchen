<?php

namespace App\Http\Controllers;

use App\Models\Alamat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;


class AlamatController extends Controller
{
    public function getAllAlamat()
    {
        try {
            $alamats = Alamat::all();
            if (count($alamats) <= 0)
                return response()->json([
                    'status' => false,
                    'message' => 'Alamat is empty',
                    'data' => $alamats,
                ], 401);

            return response()->json([
                'status' => true,
                'message' => 'Success retrieve all data alamat',
                'data' => $alamats
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function addAlamat(Request $request)
    {
        try {
            $alamatData = $request->all();
            $user = Auth::user();
            // dd($user->id);
            $validate = Validator::make($alamatData, [
                // 'id_customer' => 'required',
                'nama_alamat' => 'required',
            ]);

            if ($validate->fails()) {
                return response()->json(['status' => false, 'message' => $validate->errors()], 400);
            }

            $alamat = Alamat::create([
                'id_customer' => $user->id,
                'nama_alamat' => $request->nama_alamat
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Success adding data alamat',
                'data' => $alamat
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateAlamat(Request $request, string $id)
    {
        try {
            $alamat = Alamat::find($id);
            if (is_null($alamat)) {
                return response([
                    'message' => 'Alamat not found',
                    'data' => null,
                ], 404);
            }
            $updateData = $request->all();

            $alamat->update($updateData);

            return response([
                'message' => 'Success update alamat',
                'data' => $alamat,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteAlamatById(string $id)
    {
        try {
            $alamat = Alamat::find($id);

            if (is_null($alamat))
                return response([
                    'message' => 'Alamat not found',
                    'data' => null,
                ], 404);

            if ($alamat->delete())
                return response([
                    'message' => 'Delete alamat success',
                    'data' => $alamat,
                ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAlamatById(Request $request)
    {
        try {
            $user = Auth::user();


            if (!$user->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Customer ID parameter is empty',
                    'data' => null,
                ], 400);
            }

            $alamat = Alamat::where('id_customer', $user->id)->get();

            if (!$alamat->isEmpty()) {
                return response()->json([
                    'status' => true,
                    'message' => 'Success retrieve alamat for customer ID: ' . $user->id,
                    'data' => $alamat
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Alamat not found for customer ID: ' . $user->id,
                    'data' => null,
                ], 404);
            }

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}

