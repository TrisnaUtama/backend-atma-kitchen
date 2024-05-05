<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alamat extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'alamat';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id_customer',
        'nama_alamat'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'id_customer', 'id_customer');
    }
}
