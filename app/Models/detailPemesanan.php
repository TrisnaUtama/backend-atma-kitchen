<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class detailPemesanan extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'detail_pemesanan';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id_produk',
        'id_hampers',
        'id_pemesanan',
        'subtotal',
        'jumlah',
        'status',
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id');
    }
    public function hampers()
    {
        return $this->belongsTo(Hampers::class, 'id_hampers', 'id');
    }
    public function pemesanan()
    {
        return $this->belongsTo(Pemesanan::class, 'id_pemesanan', 'id');
    }
}