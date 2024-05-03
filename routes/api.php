<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


#routeProduk
Route::middleware(['auth:sanctum','admin'])->prefix('produk')->group(function(){
    Route::get('/getAll', 'App\Http\Controllers\ProdukController@getAllProduk');
    Route::post('/addProduk', 'App\Http\Controllers\ProdukController@addingProduct');
    Route::patch('/{id}', 'App\Http\Controllers\ProdukController@updateProduct');
    Route::delete('/{id}', 'App\Http\Controllers\ProdukController@deleteProductById');
    Route::get('/{id}', 'App\Http\Controllers\ProdukController@getProductById');
});

#routeResep
Route::middleware(['auth:sanctum','admin'])->prefix('resep')->group(function() {
    Route::get('/getAll', 'App\Http\Controllers\ResepController@getAllResep');
    Route::post('/', 'App\Http\Controllers\ResepController@addResep');
    Route::patch('/{id}', 'App\Http\Controllers\ResepController@updateResep');
    Route::delete('/{id}', 'App\Http\Controllers\ResepController@deleteResepById');
});

#routepegawai
Route::middleware(['auth:sanctum', 'mo'])->prefix('pegawai')->group(function () {
    Route::get('/getAll', 'App\Http\Controllers\PegawaiController@tampilkanPegawai');
    Route::post('/add', 'App\Http\Controllers\PegawaiController@tambahPegawai');
    Route::patch('/{id}', 'App\Http\Controllers\PegawaiController@ubahPegawai');
    Route::delete('/{id}', 'App\Http\Controllers\PegawaiController@hapusPegawai');
    Route::get('/search/{id}', 'App\Http\Controllers\PegawaiController@cariPegawai');
});

#routeGantiPassword
Route::post('/lupaPassword/create',[UserController::class,'creatToken' ]);
Route::get('/active/{token}',[UserController::class,'activeToken' ]);
Route::post('reset/{token}',[UserController::class,'resetPass']);
Route::get('validate/{token}',[UserController::class,'validateToken']);


#routePengeluaranLain
Route::middleware(['auth:sanctum', 'mo'])->prefix('pengeluaranLain')->group(function () {
    Route::get('/getAll', 'App\Http\Controllers\PengeluaranLainController@getAllPengeluaran');
    Route::post('/add', 'App\Http\Controllers\PengeluaranLainController@addPengeluaran');
    Route::patch('/update/{id}', 'App\Http\Controllers\PengeluaranLainController@updatePengeluaran');
    Route::delete('delete/{id}', 'App\Http\Controllers\PengeluaranLainController@deletePengeluaran');
});


#routeBahanBaku
Route::middleware(['auth:sanctum','admin'])->prefix('bahanbaku')->group(function(){
    Route::get('/getAllBahanBaku', 'App\Http\Controllers\BahanBakuController@getAllBahanBaku');
    Route::post('/add', 'App\Http\Controllers\BahanBakuController@addBahanBaku');
    Route::patch('/{id}', 'App\Http\Controllers\BahanBakuController@updateBahanBaku');
    Route::delete('/{id}', 'App\Http\Controllers\BahanBakuController@deleteBahanBakuById');
    Route::get('/getBahanById', 'App\Http\Controllers\BahanBakuController@getBahanBakuById');
});


#routeLimit
Route::middleware(['auth:sanctum','admin'])->prefix('limit')->group(function(){
    Route::get('/getAll', 'App\Http\Controllers\Limit_ProdukController@getLimitAllProduk');
    Route::post('/add', 'App\Http\Controllers\Limit_ProdukController@addLimitProduk');
    Route::patch('/{id}', 'App\Http\Controllers\Limit_ProdukController@editLimitProduk');
    Route::delete('/{id}', 'App\Http\Controllers\Limit_ProdukController@deleteLimitProduk');
});


#route Penitip
Route::middleware(['auth:sanctum','mo'])->prefix('penitip')->group(function(){
    Route::get('/getAllPenitip', 'App\Http\Controllers\PenitipController@getAllPenitip');
    Route::post('/addPenitip', 'App\Http\Controllers\PenitipController@addPenitip');
    Route::patch('/{id}', 'App\Http\Controllers\PenitipController@updatePenitip');
    Route::delete('/{id}', 'App\Http\Controllers\PenitipController@deletePenitipById');
    Route::get('/getPenitipById', 'App\Http\Controllers\PenitipController@getPenitipById');
});

#routePenitipAdmin
Route::middleware(['auth:sanctum','admin'])->prefix('penitipAdmin')->group(function(){
    Route::get('/getAllPenitip', 'App\Http\Controllers\PenitipController@getAllPenitip');
    Route::get('/{id}', 'App\Http\Controllers\PenitipController@getPenitipById');
});

Route::group(['prefix' => 'auth'], function () {
    Route::post('register', 'App\Http\Controllers\AuthController@register');
    Route::post('registerPegawai', 'App\Http\Controllers\PegawaiController@registerPegawai');
    Route::post('login', 'App\Http\Controllers\AuthController@login')->name('login');
});

Route::middleware(['auth:sanctum'])->prefix('auth')->group(function() {
    Route::post('logout', 'App\Http\Controllers\AuthController@logout');
});
