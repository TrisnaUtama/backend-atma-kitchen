<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Komposisi extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'komposisi';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id_resep',
        'id_bahan_baku',
        'jumlah'
    ];

    public function resep()
    {
        return $this->belongsTo(Resep::class, 'id_resep', 'id_resep');
    }
    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class, 'id_bahan_baku', 'id_bahan_baku');
    }
}
