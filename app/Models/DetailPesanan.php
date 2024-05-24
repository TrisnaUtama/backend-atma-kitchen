<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPesanan extends Model
{
    use HasFactory;
    protected $table = 'detail_pemesanan';

    protected $fillable = [
        'id_produk',
        'id_hampers',
        'id_pemesanan',
        'subtotal',
        'jumlah',
    ];

    public function Produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id');
    }

    public function hampers()
    {
        return $this->belongsTo(Hampers::class, 'id_hampers', 'id');
    }

    public function Pemesanan()
    {
        return $this->belongsTo(Pesanan::class, 'id_pemesanan', 'id_pemesanan');
    }
}
