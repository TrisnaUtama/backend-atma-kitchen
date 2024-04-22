<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

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
