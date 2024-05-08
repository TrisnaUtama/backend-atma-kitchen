<?php

namespace App\Http\Controllers;

use App\Models\Resep;
use App\Models\Komposisi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\DB;


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
                'komposisi' => 'array'
            ]);

            if ($validate->fails()) {
                return response()->json(['status' => false, 'message' => $validate->errors()], 400);
            }

            $resepsData['nama_resep'] = $request->nama_resep;

            $resep = Resep::create($resepsData);

            if ($request->has('komposisi')) {
                foreach ($resepsData['komposisi'] as $data) {
                    Komposisi::create([
                        "id_resep" => $resep->id,
                        "id_bahan_baku" => $data['id_bahan_baku'],
                        "jumlah" => $data["jumlah"]
                    ]);
                }
            }
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
                return response()->json([
                    'status' => false,
                    'message' => 'Resep not found',
                ], 404);
            }

            $updateData = $request->all();

            // Validasi data yang diperbarui
            $validate = Validator::make($updateData, [
                'komposisi' => 'array'
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validate->errors(),
                ], 400);
            }

            // Update nama resep
            $resep->update(['nama_resep' => $updateData['nama_resep']]);

            // Hapus komposisi lama
            Komposisi::where('id_resep', $id)->delete();

            // Tambahkan komposisi baru
            if (isset($updateData['komposisi'])) {
                foreach ($updateData['komposisi'] as $data) {
                    Komposisi::create([
                        "id_resep" => $id,
                        "id_bahan_baku" => $data['id_bahan_baku'],
                        "jumlah" => $data["jumlah"]
                    ]);
                }
            }

            return response()->json([
                'status' => true,
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
            // Mulai transaksi
            DB::beginTransaction();

            $resep = Resep::find($id);

            if (is_null($resep))
                return response([
                    'message' => 'Resep not found',
                    'data' => null,
                ], 404);

            // Hapus terlebih dahulu komposisi terkait
            Komposisi::where('id_resep', $id)->delete();

            // Hapus resep
            if ($resep->delete()) {
                // Commit transaksi jika berhasil
                DB::commit();
                return response([
                    'message' => 'Delete resep success',
                    'data' => $resep,
                ], 200);
            }

            // Rollback transaksi jika ada masalah saat menghapus resep
            DB::rollBack();

        } catch (Exception $e) {
            // Rollback transaksi jika terjadi kesalahan
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function getResepById(string $id)
    {
        try {
            $resep = Resep::find($id);
            $komposisi = Komposisi::where('id_resep', $id)->get();

            if (is_null($resep)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Resep not found',
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Success retrieve resep by id',
                'data' => $resep,
                'komposisi' => $komposisi
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

}
