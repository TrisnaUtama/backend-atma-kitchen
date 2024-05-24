<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pemesanan extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'pemesanan';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id_customer',
        'tanggal_pemesanan',
        'tanggal_pembayaran',
        'tanggal_diambil',
        'jarak_delivery',
        'ongkir',
        'poin_pesanan',
        'status_pesanan',
        'id_alamat',
        'uang_customer',
        'tip',
    ];

    public function detailPemesanan()
    {
        return $this->hasMany(DetailPemesanan::class, 'id_pemesanan', 'id');
    }

    public function costumer()
    {
        return $this->belongsTo(Customer::class, 'id_customer', 'id');
    }

    public function alamat()
    {
        return $this->belongsTo(Alamat::class, 'id_alamat', 'id');
    }
}
