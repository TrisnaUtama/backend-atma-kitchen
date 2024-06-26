<?php

use App\Http\Controllers\CustomerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::middleware(['auth:sanctum'])->prefix('user')->group(function () {
    Route::patch('update', 'App\Http\Controllers\AuthController@updateProfile');
});

Route::get('/getAllHampers', 'App\Http\Controllers\HampersController@getAllHampers');
Route::get('/getAllProduk', 'App\Http\Controllers\ProdukController@getAllProduk');
#routeProduk
Route::middleware(['auth:sanctum', 'admin'])->prefix('produk')->group(function () {
    Route::get('/getAll', 'App\Http\Controllers\ProdukController@getAllProduk');
    Route::post('/addProduk', 'App\Http\Controllers\ProdukController@addingProduct');
    Route::patch('/{id}', 'App\Http\Controllers\ProdukController@updateProduct');
    Route::delete('/{id}', 'App\Http\Controllers\ProdukController@deleteProductById');
    Route::get('/{id}', 'App\Http\Controllers\ProdukController@getProductById');
});

// pemesanan
Route::middleware(['auth:sanctum', 'customer'])->prefix('pemesanan')->group(function () {
    Route::post('/', 'App\Http\Controllers\PemesananController@addPemesanan');
    Route::get('/{id}', 'App\Http\Controllers\PemesananController@getPemesananById');
    Route::get('/getTotal/{id}', 'App\Http\Controllers\PemesananController@getTotalPemesananById');
});

Route::middleware(['auth:sanctum', 'mo'])->prefix('pemesanan-to-proses')->group(function () {
    Route::get('/', 'App\Http\Controllers\PemesananController@getPemesananToProses');
    Route::post('/process-orders', 'App\Http\Controllers\PemesananController@processOrder');
});

Route::middleware(['auth:sanctum', 'mo'])->prefix('bahan-baku-usage')->group(function () {
    Route::get('/', 'App\Http\Controllers\PencatatanBahanBakuController@getBahanBakuUsage');
    Route::post('/', 'App\Http\Controllers\PencatatanBahanBakuController@addBahanBakuToPencatatan');
});

Route::middleware(['auth:sanctum', 'report'])->prefix('laporan')->group(function () {
    Route::get('/', 'App\Http\Controllers\PemesananController@getLaporanProduk');
});


Route::middleware(['auth:sanctum', 'customer'])->prefix('saldo')->group(function () {
    Route::get('/{id}', 'App\Http\Controllers\SaldoController@getSaldoById');
});

// alamat
Route::middleware(['auth:sanctum', 'customer'])->prefix('alamat')->group(function () {
    Route::post('/', 'App\Http\Controllers\AlamatController@addAlamat');
    Route::get('/{id}', 'App\Http\Controllers\AlamatController@getAlamatById');
});

Route::middleware(['auth:sanctum', 'customer'])->prefix('produk-customer')->group(function () {
    Route::get('/getAll', 'App\Http\Controllers\ProdukController@getAllProduk');
    Route::get('/{id}', 'App\Http\Controllers\ProdukController@getProductById');
    Route::get('/hampers/{id}', 'App\Http\Controllers\HampersController@getSpecificHampers');
});

#routeHampers
Route::middleware(['auth:sanctum', 'admin'])->prefix('hampers')->group(function () {
    Route::get('/getAll', 'App\Http\Controllers\HampersController@getAllHampers');
    Route::post('/add', 'App\Http\Controllers\HampersController@addHampers');
    Route::patch('/{id}', 'App\Http\Controllers\HampersController@updateHampers');
    Route::delete('/{id}', 'App\Http\Controllers\HampersController@deleteHampersById');
    Route::get('/{id}', 'App\Http\Controllers\HampersController@getSpecificHampers');
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('detail_hampers')->group(function () {
    Route::get('/getAll', 'App\Http\Controllers\DetailHampersController@getAllDetail');
    Route::post('/add', 'App\Http\Controllers\DetailHampersController@addDetail');
    Route::patch('/{id}', 'App\Http\Controllers\DetailHampersController@updateDetail');
    Route::delete('/{id}', 'App\Http\Controllers\DetailHampersController@deleteDetail');
});

//komposisi
Route::middleware(['auth:sanctum', 'admin'])->prefix('komposisi')->group(function () {
    Route::get('/getAll', 'App\Http\Controllers\KomposisiController@getAllKomposisi');
    Route::post('/add', 'App\Http\Controllers\KomposisiController@addKomposisi');
    Route::patch('/{id}', 'App\Http\Controllers\KomposisiController@updateKomposisi');
    Route::delete('/{id}', 'App\Http\Controllers\KomposisiController@deleteKomposisiById');
});

#routeResep
Route::middleware(['auth:sanctum', 'admin'])->prefix('resep')->group(function () {
    Route::get('/getAll', 'App\Http\Controllers\ResepController@getAllResep');
    Route::post('/add', 'App\Http\Controllers\ResepController@addResep');
    Route::patch('/{id}', 'App\Http\Controllers\ResepController@updateResep');
    Route::delete('/{id}', 'App\Http\Controllers\ResepController@deleteResepById');
    Route::get('/{id}', 'App\Http\Controllers\ResepController@getResepById');
});

#routepegawai
Route::middleware(['auth:sanctum', 'mo'])->prefix('pegawai')->group(function () {
    Route::get('/getAll', 'App\Http\Controllers\PegawaiController@tampilkanPegawai');
    Route::post('/add', 'App\Http\Controllers\PegawaiController@tambahPegawai');
    Route::patch('/{id}', 'App\Http\Controllers\PegawaiController@ubahPegawai');
    Route::delete('/{id}', 'App\Http\Controllers\PegawaiController@hapusPegawai');
    Route::get('/search/{id}', 'App\Http\Controllers\PegawaiController@cariPegawai');
    Route::get('/{id}', 'App\Http\Controllers\PegawaiController@getPegawaiById');
});

#laporan pesanan


#konfrimasiPesananMo
Route::middleware(['auth:sanctum', 'mo'])->prefix('konfirmasi')->group(function () {
    Route::get('/getPemesanan', 'App\Http\Controllers\PemesananController@getconfirmPesanan');
    Route::post('/confirmPesanan/{id}', 'App\Http\Controllers\PemesananController@confirmPesanan');
});

#Owner
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

#routePembelianBahanBaku
Route::middleware(['auth:sanctum', 'mo'])->prefix('pembelianBahanBaku')->group(function () {
    Route::get('/getAll', 'App\Http\Controllers\PembelianBahanBakuController@getAll');
    Route::get('/{id}', 'App\Http\Controllers\PembelianBahanBakuController@getSpecificPemebelian');
    Route::post('/add', 'App\Http\Controllers\PembelianBahanBakuController@addPembelianBahanBaku');
    Route::patch('/{id}', 'App\Http\Controllers\PembelianBahanBakuController@updatePembelianBahanBaku');
    Route::delete('/{id}', 'App\Http\Controllers\PembelianBahanBakuController@deletePmebelianBahanBaku');
});

#routeGantiPassword

Route::post('/lupaPassword/create', [CustomerController::class, 'creatToken']);
Route::get('/active/{token}', [CustomerController::class, 'activeToken']);
Route::post('reset/{token}', [CustomerController::class, 'resetPass']);
Route::get('validate/{token}', [CustomerController::class, 'validateToken']);

#routeGetHistory
Route::get('/cariData', [CustomerController::class, 'cariCustomer']);
Route::get('/getHistory/{id}', [CustomerController::class, 'getHistoryPesanana']);
Route::get('/cariData', [CustomerController::class, 'cariCustomer']);
Route::get('/getHistory/{id}', [CustomerController::class, 'getHistoryPesanana']);

#routePaymentCustomer
Route::middleware(['auth:sanctum', 'customer'])->prefix('bayar')->group(function () {
    Route::get('/daftarPesanan/{id}', 'App\Http\Controllers\PemesananController@payPesanan');
    Route::post('/buktiBayar/{id}', 'App\Http\Controllers\PemesananController@buktiBayar');
});


#routePengeluaranLain
Route::middleware(['auth:sanctum', 'mo'])->prefix('pengeluaranLain')->group(function () {
    Route::get('/getAll', 'App\Http\Controllers\PengeluaranLainController@getAllPengeluaran');
    Route::post('/add', 'App\Http\Controllers\PengeluaranLainController@addPengeluaran');
    Route::patch('/{id}', 'App\Http\Controllers\PengeluaranLainController@updatePengeluaran');
    Route::delete('/{id}', 'App\Http\Controllers\PengeluaranLainController@deletePengeluaran');
    Route::get('/{id}', 'App\Http\Controllers\PengeluaranLainController@getPengeluaranById');
});


#routeBahanBaku
Route::middleware(['auth:sanctum', 'admin'])->prefix('bahanbaku')->group(function () {
    Route::get('/getAllBahanBaku', 'App\Http\Controllers\BahanBakuController@getAllBahanBaku');
    Route::post('/add', 'App\Http\Controllers\BahanBakuController@addBahanBaku');
    Route::patch('/{id}', 'App\Http\Controllers\BahanBakuController@updateBahanBaku');
    Route::delete('/{id}', 'App\Http\Controllers\BahanBakuController@deleteBahanBakuById');
    Route::get('/{id}', 'App\Http\Controllers\BahanBakuController@getBahanBakuById');
});

Route::middleware(['auth:sanctum', 'mo'])->prefix('bahanbakuMO')->group(function () {
    Route::get('/getAllBahanBaku', 'App\Http\Controllers\BahanBakuController@getAllBahanBaku');
});


Route::middleware(['auth:sanctum', 'mo'])->prefix('penitip')->group(function () {
    Route::get('/laporanPenitip/{id}', 'App\Http\Controllers\LaporanPenitipController@showPenitipData');
    Route::get('/getAllPenitip', 'App\Http\Controllers\LaporanPenitipController@getAllPenitip');

});
Route::middleware(['auth:sanctum', 'mo'])->prefix('laporan')->group(function () {
    Route::get('/pemasukanPengeluaran', 'App\Http\Controllers\LaporanPemasukandanPengeluaran@laporanBulanan');
});
Route::middleware(['auth:sanctum', 'mo'])->prefix('laporanPegawai')->group(function () {
    Route::get('/presensi', 'App\Http\Controllers\LaporanPresensiController@laporanPegawai');
});


Route::middleware(['auth:sanctum', 'report'])->prefix('reportBahanBaku')->group(function () {
    Route::get('/getAllBahanBaku', 'App\Http\Controllers\BahanBakuController@getAllBahanBaku');
});




#routeLimit
Route::middleware(['auth:sanctum', 'admin'])->prefix('limit')->group(function () {
    Route::get('/getAll', 'App\Http\Controllers\Limit_ProdukController@getLimitAllProduk');
    Route::post('/add', 'App\Http\Controllers\Limit_ProdukController@addLimitProduk');
    Route::patch('/{id}', 'App\Http\Controllers\Limit_ProdukController@editLimitProduk');
    Route::delete('/{id}', 'App\Http\Controllers\Limit_ProdukController@deleteLimitProduk');
    Route::get('/getToday', 'App\Http\Controllers\Limit_ProdukController@getLimitProdukToday');
});

//limitRouteCustomer
Route::middleware(['auth:sanctum', 'customer'])->prefix('limit-customer')->group(function () {
    Route::get('/getToday', 'App\Http\Controllers\Limit_ProdukController@getLimitProdukToday');
});

Route::prefix('limit')->group(function () {
    Route::get('/getToday', 'App\Http\Controllers\Limit_ProdukController@getLimitProdukToday');
});

//SaldoCustomer
Route::middleware(['auth:sanctum','customer'])->prefix('saldoCustomer')->group(function(){
    Route::get('/getSaldo/{id}', 'App\Http\Controllers\SaldoController@getSaldoById');
    Route::post('/tarikSaldo', 'App\Http\Controllers\SaldoController@penarikanSaldo');
    Route::get('/history', 'App\Http\Controllers\SaldoController@historySaldo');
});

//SaldoAdmin
Route::middleware(['auth:sanctum','admin'])->prefix('saldoAdmin')->group(function(){
    Route::get('/listSaldo', 'App\Http\Controllers\DetailSaldoController@listPenarikanSaldo');
    Route::post('/confirmSaldo/{id}', 'App\Http\Controllers\DetailSaldoController@confirmSaldo');
});


#route Penitip
Route::middleware(['auth:sanctum', 'mo'])->prefix('penitip')->group(function () {
    Route::get('/getAllPenitip', 'App\Http\Controllers\PenitipController@getAllPenitip');
    Route::post('/addPenitip', 'App\Http\Controllers\PenitipController@addPenitip');
    Route::patch('/{id}', 'App\Http\Controllers\PenitipController@updatePenitip');
    Route::delete('/{id}', 'App\Http\Controllers\PenitipController@deletePenitipById');
    Route::get('/{id}', 'App\Http\Controllers\PenitipController@getPenitipById');
});


Route::middleware(['auth:sanctum', 'mo'])->prefix('role')->group(function () {
    Route::get('/getAllRole', 'App\Http\Controllers\RoleController@getAllRole');
});

#routePenitipAdmin
Route::middleware(['auth:sanctum', 'admin'])->prefix('penitipAdmin')->group(function () {
    Route::get('/getAllPenitip', 'App\Http\Controllers\PenitipController@getAllPenitip');
    Route::get('/{id}', 'App\Http\Controllers\PenitipController@getPenitipById');
});

Route::group(['prefix' => 'auth'], function () {
    Route::post('register', 'App\Http\Controllers\AuthController@register');
    Route::post('registerPegawai', 'App\Http\Controllers\PegawaiController@registerPegawai');
    Route::post('login', 'App\Http\Controllers\AuthController@login')->name('login');
});

Route::middleware(['auth:sanctum', 'customer'])->prefix('detailPemesanan')->group(function () {
    Route::get('/getHistory', 'App\Http\Controllers\DetailPemesananController@index');
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('detailPemesanan')->group(function () {
    Route::get('/getJarak', 'App\Http\Controllers\DetailPemesananController@getAllJarakNull');
    Route::get('/getStatus', 'App\Http\Controllers\DetailPemesananController@getStatus');
    Route::get('/getStatusDiproses', 'App\Http\Controllers\DetailPemesananController@getStatusDiproses');
    Route::get('/getStatusPesanan', 'App\Http\Controllers\DetailPemesananController@getStatusPesanan');
    Route::post('/addJarakDelivery/{id}', 'App\Http\Controllers\DetailPemesananController@addJarakDelivery');
    Route::post('/addPembayaran/{id}', 'App\Http\Controllers\DetailPemesananController@addPembayaran');
    Route::post('/updateStatus/{id}', 'App\Http\Controllers\DetailPemesananController@updateStatus');
    Route::get('/getTelatBayar', 'App\Http\Controllers\DetailPemesananController@getTelatBayar');
    Route::post('/cancelLatePaymentOrder/{id}', 'App\Http\Controllers\DetailPemesananController@cancelLatePaymentOrder'); // New route
});

Route::middleware(['auth:sanctum', 'report'])->prefix('detailPemesanan')->group(
    function () {
        Route::get('/getLaporanProdukBulanan', 'App\Http\Controllers\DetailPemesananController@getLaporanProdukBulanan');
        Route::get('/getLaporanBahanBakuPeriode', 'App\Http\Controllers\DetailPemesananController@getLaporanBahanBakuPeriode');
    }
);

Route::middleware(['auth:sanctum', 'customer'])->prefix('detailPemesanan')->group(function () {
    Route::get('/getShippedOrPickedUpOrders', 'App\Http\Controllers\DetailPemesananController@getShippedOrPickedUpOrders');
    Route::get('/getShippedOrPickedUpOrdersByCustomer', 'App\Http\Controllers\DetailPemesananController@getShippedOrPickedUpOrdersByCustomer');
    Route::post('/updateStatusToSelesai/{id}', 'App\Http\Controllers\DetailPemesananController@updateStatusToSelesai'); // New route
});



Route::prefix('presensi')->group(function () {
    Route::post('/add', 'App\Http\Controllers\PresensiController@addPresensi');
    Route::get('/getAllPresensi', 'App\Http\Controllers\PresensiController@getAllPresensi');
    Route::patch('/{id}', 'App\Http\Controllers\PresensiController@updatePresensi');
});

Route::middleware(['auth:sanctum'])->prefix('auth')->group(function () {
    Route::post('logout', 'App\Http\Controllers\AuthController@logout');
});
