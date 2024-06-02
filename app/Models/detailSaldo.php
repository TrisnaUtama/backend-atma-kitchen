<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class detailSaldo extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'detail_saldo';
    protected $primaryKey = 'id';

    protected $fillable= [
        'id_customer',
        'jumlah_saldo',
        'tanggal_penarikan',
        'tanggal_konfirmasi',
        'status',
    ];

    public function Customer(){
        return $this->belongsTo(Customer::class,'id_customer', 'id');
    }

}
