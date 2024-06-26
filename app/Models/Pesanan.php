<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Pesanan extends Model
{
    use HasFactory;
    protected $table = 'pemesanan';
    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'id_customer',
        'id_penitip',
        'tanggal_pemesanan',
        'tanggal_pembayaran',
        'tanggal_diambil',
        'jarak_delivery',
        'ongkir',
        'poin_pesanan',
        'potongan_poin',
        'status_pesanan',
        'uang_customer',
        'bukti_pembayaran',
        'tip',
    ];

    public function Customer(){
        return $this->belongsTo(Customer::class, 'id_customer', 'id_customer');
    }

    public function Produk(){
        return $this->belongsTo(Produk::class, 'id_produk', 'id');
    }

    public function detail_pemesanan()
    {
        return $this->hasMany(DetailPesanan::class, 'id_pemesanan', 'id');
    }

    public function Saldo(){
        return $this->belongsTo(Saldo::class, 'id_saldo', 'id');
    }
}
