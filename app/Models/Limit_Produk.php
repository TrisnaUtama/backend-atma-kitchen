<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Limit_Produk extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'limit_produk'; 
    protected $primaryKey = 'id'; 
    protected $fillable = [
        'id_produk',
        'limit',
        'tanggal',
    ];

    public function produk(){
        return $this->belongsTo(Produk::class, 'id_produk', 'id_produk');
    }
}
