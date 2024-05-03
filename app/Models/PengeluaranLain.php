<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengeluaranLain extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'pengeluaran_operasional'; 
    protected $primaryKey = 'id'; 
    protected $fillable = [
        'nama_pengeluaran',
        'total_pengeluaran',
        'tanggal_pembelian',
    ];
}
