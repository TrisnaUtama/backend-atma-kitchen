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
    
}
