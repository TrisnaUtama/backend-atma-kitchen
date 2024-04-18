<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Customer extends Authenticatable
{
    use HasFactory, HasApiTokens;

    public $timestamps = false;
    protected $table = 'customer'; 
    protected $primaryKey = 'id'; 
    protected $fillable = [
        'id_saldo',
        'nama',
        'password',
        'email',
        'no_telpn',
        'tanggal_lahir',
        'gender',
        'poin',
    ];

    public function saldo(){
        return $this->belongsTo(Saldo::class, 'id_saldo', 'id_saldo');
    }
}



