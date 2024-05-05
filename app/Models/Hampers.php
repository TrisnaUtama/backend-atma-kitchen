<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hampers extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'hampers'; 
    protected $primaryKey = 'id'; 
    protected $fillable = [
        'gambar',
        'harga',
        'deskripsi',
        'nama_hampers',
    ];

    public function detailHampers()
    {
        return $this->hasMany(Detail_Hampers::class, 'id_hampers');
    }
}