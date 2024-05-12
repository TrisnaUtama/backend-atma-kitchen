<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presensi extends Model
{
    public $timestamps = false;
    protected $table = 'presensi';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id_pegawai',
        'tanggal_presensi',
        'status',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'id_pegawai', 'id_pegawai');
    }

    use HasFactory;
}
