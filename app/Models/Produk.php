<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;
    
    public $timestamps = false;
    protected $table = 'produk'; 
    protected $primaryKey = 'id'; 
    protected $fillable = [
        'id_penitip',
        'id_resep',
        'tanggal_penitipan',
        'nama_produk',
        'gambar',
        'deskripsi',
        'kategori',
        'harga',
        'stok',
    ];

    public function penitip(){
        return $this->belongsTo(Penitip::class, 'id_penitip', 'id_penitip');
    }

    public function resep(){
        return $this->belongsTo(Resep::class, 'id_resep', 'id_resep');
    }
}

