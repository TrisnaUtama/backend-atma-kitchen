<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembelianBahanBaku extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'pembelian_bahan_baku'; 
    protected $primaryKey = 'id'; 
    protected $fillable = [
        'id_bahan_baku',
        'harga',
        'jumlah',
        'nama',
    ];

    public function bahan_baku(){
        return $this->belongsTo(Saldo::class, 'id_bahan_baku', 'id_bahan_baku');
    }
}
