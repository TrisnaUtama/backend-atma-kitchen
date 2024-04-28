<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PegawaiController extends Controller
{

    public function registerPegawai(Request $request){
        $validator = Validator::make(request()->all(), [
            'id_role' => 'required',
            'nama' => 'required',
            'email' => 'required|email|unique:pegawai',
            'no_telpn' => 'required',
            'tanggal_lahir' => 'required',
            'alamat' => 'required',
            'password' => 'required',
            'gender' => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        $user = Pegawai::create([
            'id_role' => $request->id_role,
            'nama' => $request->nama,
            'email' => $request->email,
            'no_telpn' => $request->no_telpn,
            'tanggal_lahir' => $request->tanggal_lahir,
            'alamat' => $request->alamat,
            'password' => Hash::make($request->password),
            'gender' => $request->gender,
        ]);

        if($user){
            return response()->json(['message' => 'Successfully register', 'data' => $user]);
        }else{
            return response()->json(['message' => 'Error While Register']);
        }
    }

    public function tambahPegawai(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_role' => 'required',
            'nama' => 'required',
            'email' => 'required|email|unique:pegawai',
            'no_telpn' => 'required',
            'tanggal_lahir' => 'required',
            'alamat' => 'required',
            'password' => 'required',
            'gender' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $pegawai = Pegawai::create([
            'id_role' => $request->id_role,
            'nama' => $request->nama,
            'email' => $request->email,
            'no_telpn' => $request->no_telpn,
            'tanggal_lahir' => $request->tanggal_lahir,
            'alamat' => $request->alamat,
            'password' => Hash::make($request->password),
            'gender' => $request->gender,
        ]);

        if ($pegawai) {
            return response()->json(['message' => 'Pegawai successfully added', 'data' => $pegawai]);
        } else {
            return response()->json(['message' => 'Error while adding Pegawai']);
        }
    }

    // Mengubah pegawai
    public function ubahPegawai(Request $request, $id)
    {
        $pegawai = Pegawai::find($id);

        if (!$pegawai) {
            return response()->json(['message' => 'Pegawai not found'], 404);
        }

        $updateData = $request->all();

        $pegawai->update($updateData);


        return response()->json(['message' => 'Pegawai successfully updated', 'data' => $pegawai]);
    }

    // Menghapus pegawai
    public function hapusPegawai($id)
    {
        $pegawai = Pegawai::find($id);

        if (!$pegawai) {
            return response()->json(['message' => 'Pegawai not found'], 404);
        }

        $pegawai->delete();

        return response()->json(['message' => 'Pegawai successfully deleted']);
    }

    // Menampilkan pegawai
    public function tampilkanPegawai()
    {
        $pegawai = Pegawai::all();

        return response()->json(['data' => $pegawai]);
    }

    // Mencari pegawai
    public function cariPegawai($query)
    {
        $pegawai = Pegawai::where('nama', 'like', '%' . $query . '%')
            ->orWhere('email', 'like', '%' . $query . '%')
            ->orWhere('no_telpn', 'like', '%' . $query . '%')
            ->get();

        return response()->json(['data' => $pegawai]);
    }

}
