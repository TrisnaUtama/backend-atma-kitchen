<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

    Route::prefix('produk')->group(function(){
        Route::get('/getAll', 'App\Http\Controllers\ProdukController@getAllProduk');
        Route::post('/', 'App\Http\Controllers\ProdukController@addingProduct');
        Route::patch('/{id}', 'App\Http\Controllers\ProdukController@updateProduct');
        Route::delete('/{id}', 'App\Http\Controllers\ProdukController@deleteProductById');
        Route::get('/', 'App\Http\Controllers\ProdukController@getProductById');
    });