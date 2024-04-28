<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['auth:sanctum','admin'])->prefix('produk')->group(function(){
    Route::get('/getAll', 'App\Http\Controllers\ProdukController@getAllProduk');
    Route::post('/', 'App\Http\Controllers\ProdukController@addingProduct');
    Route::patch('/{id}', 'App\Http\Controllers\ProdukController@updateProduct');
    Route::delete('/{id}', 'App\Http\Controllers\ProdukController@deleteProductById');
    Route::get('/getById', 'App\Http\Controllers\ProdukController@getProductById');
});

Route::middleware(['auth:sanctum','admin'])->prefix('resep')->group(function() {
    Route::get('/getAll', 'App\Http\Controllers\ResepController@getAllResep');
    Route::post('/', 'App\Http\Controllers\ResepController@addResep');
    Route::patch('/{id}', 'App\Http\Controllers\ResepController@updateResep');
    Route::delete('/{id}', 'App\Http\Controllers\ResepController@deleteResepById');
});

#routeBahanBaku
Route::middleware(['auth:sanctum','admin'])->prefix('bahanbaku')->group(function(){
    Route::get('/getAllBahanBaku', 'App\Http\Controllers\BahanBakuController@getAllBahanBaku');
    Route::post('/add', 'App\Http\Controllers\BahanBakuController@addBahanBaku');
    Route::patch('/{id}', 'App\Http\Controllers\BahanBakuController@updateBahanBaku');
    Route::delete('/{id}', 'App\Http\Controllers\BahanBakuController@deleteBahanBakuById');
    Route::get('/getBahanById', 'App\Http\Controllers\BahanBakuController@getBahanBakuById');
});


#route Penitip
Route::middleware(['auth:sanctum','mo'])->prefix('penitip')->group(function(){
    Route::get('/getAllPenitip', 'App\Http\Controllers\PenitipController@getAllPenitip');
    Route::post('/addPenitip', 'App\Http\Controllers\PenitipController@addPenitip');
    Route::patch('/{id}', 'App\Http\Controllers\PenitipController@updatePenitip');
    Route::delete('/{id}', 'App\Http\Controllers\PenitipController@deletePenitipById');
    Route::get('/getPenitipById', 'App\Http\Controllers\PenitipController@getPenitipById');
});



Route::group(['prefix' => 'auth'], function () {
    Route::post('register', 'App\Http\Controllers\AuthController@register');
    Route::post('registerPegawai', 'App\Http\Controllers\PegawaiController@registerPegawai');
    Route::post('login', 'App\Http\Controllers\AuthController@login')->name('login');
});

Route::middleware(['auth:sanctum'])->prefix('auth')->group(function() {
    Route::post('logout', 'App\Http\Controllers\AuthController@logout');
});
