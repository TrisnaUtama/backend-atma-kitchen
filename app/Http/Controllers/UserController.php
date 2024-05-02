<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailVerification;
use App\Mail\MailSend;
use Illuminate\Support\Facades\DB;
use Psy\Readline\Hoa\Console;


class UserController extends Controller
{
    public function creatToken(Request $request){
        $validator = Validator::make($request->all(),[
            'email'=> 'required|email'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 404);
        }

        $user = User::where('email', $request->email)->first();
        if($user == null){
            return response()->json([
                'status' => 'error',
                'message' => 'Email gagal registrasi'
            ], 404);
        }
        try{
            $tokenNew = Str::random(150);
            $active=DB::table('password_reset_tokens')->where('email', $user->email)->first();

            if($active){
                DB::table('password_reset_tokens')->where('email', $user->email)->update([
                    'token' => $tokenNew,
                    'created_at'=>now()
                ]);
            }else{
                DB::table('password_reset_tokens')->insert([
                    'email' => $user->email,
                    'token' => $tokenNew,
                    'created_at'=>now()
                ]);
            }

            $data=[
                'name'=>$user->name,
                'url'=> request()->ip().':'.request()->getPort().'/api/v1/active/'.$tokenNew,
            ];

            Mail::to($user->email)->send(new MailSend($data));

            return response()->json([
                'status' => 'success',
                'message' => 'verifikasi untuk reset password telah dikirim ke email anda.'
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'status' => 'error',
                'message' => 'Error : '.$e->getMessage()
            ],400);
        }

    }
    public function activeToken(String $token){
        $verifikasi_token = DB::table('password_reset_tokens')->where('token', $token)->first();
        if(!$verifikasi_token || $verifikasi_token->token != $token){
            return view('verfikasi Gagal !!!');
        }
        DB::table('password_reset_tokens')->where('token',$token)->update([
            'is_verified' => true
        ]);
        $link = 'http://127.0.0.1:3000/auth/forgot-password/change-password?token='.$token.'&email='.$verifikasi_token->email;  
        return view('mail.verifikasiPage', compact('link'));
    }

    public function validateToken(string $token){
        $verifikasi_token = DB::table('password_reset_tokens')->where('token', $token)->first();
        if(!$verifikasi_token){
            return response()->json([
                'status' => 'error',
                'message' => 'token tidak ditemukan'
            ],404);
        }else if(!$verifikasi_token->is_verified){
            return response()->json([
                'status' => 'error',
                'message' => 'token tidak aktif'
            ],400);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil mendapatkan token!'
        ],200);
    }
    public function resetPass(Request $request, string $token){
        $validator = Validator::make($request->all(),[
            'password'=> 'required|min:8',
            'confirmasi_pass' => 'required|same:password',
        ]);
        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ],400);
        }

        $tokenTabel = DB::table('password_reset_tokens')->where('token', $token)->first();
        if($tokenTabel == null){
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Token'
            ],404);
        }else if(!$tokenTabel->is_verified){
            return response()->json([
                'status' => 'error',
                'message' => 'token not verificated'
            ],400);
        }
      
        $user = User::where('email', $tokenTabel->email)->first();
        if($user == null){
            return response()->json([
                'status' => 'error',
                'message' => 'user not found'
            ],404);
        }

        $user->update([
            'password'=> Hash::make($request->password)
        ]);

        DB::table('password_reset_tokens')->where('email',$user->email)->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Password berhasil di perbarui!!'
        ],200);
    }


}
