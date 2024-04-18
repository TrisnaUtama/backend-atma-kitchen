<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Saldo extends Model
{
    use HasFactory;
    
    public $timestamps = false;
    protected $table = 'saldo'; 
    protected $primaryKey = 'id'; 
    protected $fillable = [
        'jumlah_saldo',
        'tanggal_masuk',
        'tanggal_keluar',
    ];
}
