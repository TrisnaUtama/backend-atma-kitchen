<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BahanBaku extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'bahan_baku'; 
    protected $primaryKey = 'id'; 

    protected $fillable = [
        'nama_bahan_baku',
        'stok',
        'satuan',
    ];
}
