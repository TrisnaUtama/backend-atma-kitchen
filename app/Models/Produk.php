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
        'status',
    ];

    public function penitip(){
        return $this->belongsTo(Penitip::class, 'id_penitip', 'id');
    }

    public function resep(){
        return $this->belongsTo(Resep::class, 'id_resep', 'id');
    }

    public function limit(){
        return $this->hasMany(Limit_Produk::class, 'id_produk', 'id');
    }

    public function komposisi()
    {
        return $this->hasManyThrough(Komposisi::class, Resep::class, 'id', 'id_resep', 'id_resep', 'id');
    }
}

