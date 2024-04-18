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

Route::group(['prefix' => 'auth'], function () {
    Route::post('register', 'App\Http\Controllers\AuthController@register');
    Route::post('registerPegawai', 'App\Http\Controllers\PegawaiController@registerPegawai');
    Route::post('login', 'App\Http\Controllers\AuthController@login')->name('login');
});

Route::middleware(['auth:sanctum'])->prefix('auth')->group(function() {
    Route::post('logout', 'App\Http\Controllers\AuthController@logout');
});
