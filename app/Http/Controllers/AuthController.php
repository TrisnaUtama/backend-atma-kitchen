<?php

namespace App\Http\Controllers;

use App\Models\Saldo;
use App\Models\Pegawai;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Illuminate\Auth\SessionGuard;

class AuthController extends Controller
{
    public function register(Request $request){
        $validator = Validator::make(request()->all(), [
            'nama' => 'required',
            'no_telpn' => 'required',
            'tanggal_lahir' => 'required',
            'email' => 'required|email|unique:customer',
            'password' => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        $saldo = Saldo::create();
        $user = Customer::create([
            'id_saldo' => $saldo->id,
            'nama' => $request->nama,
            'email' => $request->email,
            'no_telpn' => $request->no_telpn,
            'tanggal_lahir' => $request->tanggal_lahir,
            'password' => Hash::make($request->password),
        ]);

        if($user){
            return response()->json(['message' => 'Successfully register', 'data' => $user]);
        }else{
            return response()->json(['message' => 'Error While Register']);
        }
    }

    public function login(Request $request){
        $credentials = $request->only('email', 'password');

        $pegawai = Pegawai::where('email', $credentials['email'])->first();
        if ($pegawai && Hash::check($credentials['password'], $pegawai->password)) {
            $token = $pegawai->createToken('authToken')->plainTextToken;
            return $this->respondWithToken($token, $pegawai);
        }

        $customer = Customer::where('email', $credentials['email'])->first();
        if ($customer && Hash::check($credentials['password'], $customer->password)) {
            $token = $customer->createToken('authToken')->plainTextToken;
            return $this->respondWithToken($token, $customer);
        }

        return response()->json(['error' => 'Invalid credentials'], 401);
    }
    
    protected function respondWithToken($token, $credentials){
        return response()->json([
            'status' => 'success',
            'data' => $credentials,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expiration' => 525600,
        ], 200);
    }
        
    
    public function logout(){
        $removeToken = Auth::user();
    
        if($removeToken) 
            $removeToken->currentAccessToken()->delete();
            return response()->json(['success' => true, 'message' => 'Successfully logged out', 'data' => $removeToken]);
        
        return response()->json(['success' => false, 'message' => 'no token provided', 'data' => $removeToken]);
    }
}