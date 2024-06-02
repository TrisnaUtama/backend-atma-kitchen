<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presensi extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'presensi';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id_pegawai',
        'tanggal_presensi',
        'status',
    ];

    // Definisi relasi dengan model Pegawai
    public function pegawai()
    {
        // Asumsikan bahwa kunci asing pada model Presensi adalah id_pegawai
        return $this->belongsTo(Pegawai::class, 'id_pegawai', 'id');
    }
}
