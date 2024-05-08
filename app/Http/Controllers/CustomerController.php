<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailVerification;
use App\Mail\MailSend;
use Illuminate\Support\Facades\DB;
use Psy\Readline\Hoa\Console;
use App\Models\Customer;
use App\Models\Pesanan;


class CustomerController extends Controller
{
    public function creatToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }
        try {

            $user = Customer::where('email', $request->email)->first();

            if ($user == null) {
                throw new \Exception('');
            }
            $tokenNew = Str::random(150);
            $active = DB::table('password_reset_tokens')->where('email', $user->email)->first();

            if ($active) {
                DB::table('password_reset_tokens')->where('email', $user->email)->update([
                    'token' => $tokenNew,
                    'created_at' => now()
                ]);
            } else {
                DB::table('password_reset_tokens')->insert([
                    'email' => $user->email,
                    'token' => $tokenNew,
                    'created_at' => now()
                ]);
            }

            $data = [
                'name' => $user->name,
                'url' => request()->ip() . ':' . request()->getPort() . '/api/v1/active/' . $tokenNew,
            ];

            Mail::to($user->email)->send(new MailSend($data));

            return response()->json([
                'status' => 'success',
                'message' => 'verifikasi untuk reset password telah dikirim ke email anda.',
                'token' => $tokenNew,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error : ' . $e->getMessage()
            ], 400);
        }

    }
    public function activeToken(string $token)
    {
        $verifikasi_token = DB::table('password_reset_tokens')->where('token', $token)->first();
        if (!$verifikasi_token || $verifikasi_token->token != $token) {
            return view('verfikasi Gagal !!!');
        }
        DB::table('password_reset_tokens')->where('token', $token)->update([
            'is_verified' => true
        ]);
        $link = 'http://127.0.0.1:3000/forgot-password/change-password?token=' . $token;
        return view('mail.verifikasiPage', compact('link'));
    }

    public function validateToken(string $token)
    {
        $verifikasi_token = DB::table('password_reset_tokens')->where('token', $token)->first();
        if (!$verifikasi_token) {
            return response()->json([
                'status' => 'error',
                'message' => 'token tidak ditemukan'
            ], 404);
        } else if (!$verifikasi_token->is_verified) {
            return response()->json([
                'status' => 'error',
                'message' => 'token tidak aktif'
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil mendapatkan token!'
        ], 200);
    }
    public function resetPass(Request $request, string $token)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|min:8',
            'confirmasi_pass' => 'required|same:password',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        $tokenTabel = DB::table('password_reset_tokens')->where('token', $token)->first();
        if ($tokenTabel == null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Token'
            ], 404);
        } else if (!$tokenTabel->is_verified) {
            return response()->json([
                'status' => 'error',
                'message' => 'token not verificated'
            ], 400);
        }

        $user = Customer::where('email', $tokenTabel->email)->first();
        if ($user == null) {
            return response()->json([
                'status' => 'error',
                'message' => 'user not found'
            ], 404);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        DB::table('password_reset_tokens')->where('email', $user->email)->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Password berhasil di perbarui!!'
        ], 200);
    }

    public function getHistoryPesanana($id)
    {
        try {
            $history = Pesanan::with([
                'detail_pemesanan' => function ($query) {
                    $query->select('id_pemesanan', 'id_produk', 'subtotal', 'jumlah')
                        ->with([
                            'Produk' => function ($query) {
                                $query->select('id', 'nama_produk', 'harga');
                            }
                        ]);
                }
            ])
                ->where('id_customer', $id)
                ->where(function ($query){
                    $query->where('status_pesanan', 'selesai')
                    ->orWhere('status_pesanan', 'dibatalkan');
                })
                ->get();

            if (count($history) == 0) {
                throw new \Exception();
            }
            return response()->json([
                'status' => true,
                'message' => 'Success mendapatkan history pesanan',
                'data' => $history
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Pesanan tidak ditemukan!',
                'error' => $th->getMessage(),
                'data' => []
            ], 404);
        }
    }

    public function cariCustomer(Request $request){
        try{
            $searchKey = $request->query('query');
            $customer = Customer::where('nama','like','%'. $searchKey . '%')->select('id','nama','email','no_telpn')->get();
            if(count($customer)==0){
                throw new \Exception();
            }
            return response()->json([
                'status' => true,
                'message' => 'Success mendapatkan data customer',
                'data' => $customer
            ]);
        }catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Customer tidak ditemukan!',
                'error' => $th->getMessage(),
                'data' => []
            ], 404);
        }
    }
}
