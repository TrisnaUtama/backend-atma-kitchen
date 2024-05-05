<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['auth:sanctum'])->prefix('user')->group(function () {
    Route::patch('update', 'App\Http\Controllers\AuthController@updateProfile');
});


Route::middleware(['auth:sanctum', 'admin'])->prefix('produk')->group(function () {
    Route::get('/getAll', 'App\Http\Controllers\ProdukController@getAllProduk');
    Route::post('/', 'App\Http\Controllers\ProdukController@addingProduct');
    Route::patch('/{id}', 'App\Http\Controllers\ProdukController@updateProduct');
    Route::delete('/{id}', 'App\Http\Controllers\ProdukController@deleteProductById');
    Route::get('/getById', 'App\Http\Controllers\ProdukController@getProductById');
});

//komposisi
Route::middleware(['auth:sanctum', 'admin'])->prefix('komposisi')->group(function () {
    Route::get('/getAll', 'App\Http\Controllers\KomposisiController@getAllKomposisi');
    Route::post('/add', 'App\Http\Controllers\KomposisiController@addKomposisi');
    Route::patch('/{id}', 'App\Http\Controllers\KomposisiController@updateKomposisi');
    Route::delete('/{id}', 'App\Http\Controllers\KomposisiController@deleteKomposisiById');
});

//resep
Route::middleware(['auth:sanctum', 'admin'])->prefix('resep')->group(function () {
    Route::get('/getAll', 'App\Http\Controllers\ResepController@getAllResep');
    Route::post('/add', 'App\Http\Controllers\ResepController@addResep');
    Route::patch('/{id}', 'App\Http\Controllers\ResepController@updateResep');
    Route::delete('/{id}', 'App\Http\Controllers\ResepController@deleteResepById');
    Route::get('/{id}', 'App\Http\Controllers\ResepController@getResepById');

});

#route pegawai
Route::middleware(['auth:sanctum', 'mo'])->prefix('pegawai')->group(function () {
    Route::get('/getAll', 'App\Http\Controllers\PegawaiController@tampilkanPegawai');
    Route::post('/add', 'App\Http\Controllers\PegawaiController@tambahPegawai');
    Route::patch('/{id}', 'App\Http\Controllers\PegawaiController@ubahPegawai');
    Route::delete('/{id}', 'App\Http\Controllers\PegawaiController@hapusPegawai');
    Route::get('/search/{id}', 'App\Http\Controllers\PegawaiController@cariPegawai');
    Route::get('/{id}', 'App\Http\Controllers\PegawaiController@getPegawaiById');
});

Route::middleware(['auth:sanctum', 'owner'])->prefix('pegawaiOwner')->group(function () {
    Route::get('/getAll', 'App\Http\Controllers\PegawaiController@tampilkanPegawai');
    Route::get('/{id}', 'App\Http\Controllers\PegawaiController@getPegawaiById');
    Route::patch('/{id}', 'App\Http\Controllers\GajiBonusController@ubahGaji');

});

//alamat
Route::middleware(['auth:sanctum', 'customer'])->prefix('alamat')->group(function () {
    Route::get('/getAllAlamat', 'App\Http\Controllers\AlamatController@getAllAlamat');
    Route::post('/addAlamat', 'App\Http\Controllers\AlamatController@addAlamat');
    Route::patch('/{id}', 'App\Http\Controllers\AlamatController@updateAlamat');
    Route::delete('/{id}', 'App\Http\Controllers\AlamatController@deleteAlamatById');
    Route::get('/getAlamatById', 'App\Http\Controllers\AlamatController@getAlamatById');
});

#routeBahanBaku
Route::middleware(['auth:sanctum', 'admin'])->prefix('bahanbaku')->group(function () {
    Route::get('/getAllBahanBaku', 'App\Http\Controllers\BahanBakuController@getAllBahanBaku');
    Route::post('/add', 'App\Http\Controllers\BahanBakuController@addBahanBaku');
    Route::patch('/{id}', 'App\Http\Controllers\BahanBakuController@updateBahanBaku');
    Route::delete('/{id}', 'App\Http\Controllers\BahanBakuController@deleteBahanBakuById');
    Route::get('/getBahanById', 'App\Http\Controllers\BahanBakuController@getBahanBakuById');
});


#route Penitip
Route::middleware(['auth:sanctum', 'mo'])->prefix('penitip')->group(function () {
    Route::get('/getAllPenitip', 'App\Http\Controllers\PenitipController@getAllPenitip');
    Route::post('/addPenitip', 'App\Http\Controllers\PenitipController@addPenitip');
    Route::patch('/{id}', 'App\Http\Controllers\PenitipController@updatePenitip');
    Route::delete('/{id}', 'App\Http\Controllers\PenitipController@deletePenitipById');
    Route::get('/getPenitipById', 'App\Http\Controllers\PenitipController@getPenitipById');
});


Route::middleware(['auth:sanctum', 'mo'])->prefix('role')->group(function () {
    Route::get('/getAllRole', 'App\Http\Controllers\RoleController@getAllRole');
});

Route::group(['prefix' => 'auth'], function () {
    Route::post('register', 'App\Http\Controllers\AuthController@register');
    Route::post('registerPegawai', 'App\Http\Controllers\PegawaiController@registerPegawai');
    Route::post('login', 'App\Http\Controllers\AuthController@login')->name('login');
});

Route::middleware(['auth:sanctum'])->prefix('auth')->group(function () {
    Route::post('logout', 'App\Http\Controllers\AuthController@logout');
});
