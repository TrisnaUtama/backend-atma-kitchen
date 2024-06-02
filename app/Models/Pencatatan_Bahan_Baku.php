<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pencatatan_Bahan_Baku extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'pencatatan_bahan_baku'; 
    protected $primaryKey = 'id'; 

    protected $fillable = [
        'id_bahan_baku',
        'jumlah_terpakai',
    ];

    public function bahan_baku()
    {
        return $this->belongsTo(BahanBaku::class, 'id_bahan_baku', 'id');
    }
}
