<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Detail_Hampers extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'detail_hampers';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id_produk',
        'id_bahan_baku',
        'id_hampers',
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id');
    }

    public function bahan_baku()
    {
        return $this->belongsTo(BahanBaku::class, 'id_bahan_baku', 'id_bahan_baku');
    }

    public function hampers()
    {
        return $this->belongsTo(Hampers::class, 'id_hampers', 'id_hampers');
    }
}
