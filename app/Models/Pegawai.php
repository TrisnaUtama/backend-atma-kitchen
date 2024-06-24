<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Pegawai extends Authenticatable
{
    use HasFactory, HasApiTokens;

    public $timestamps = false;
    protected $table = 'pegawai'; 
    protected $primaryKey = 'id'; 
    protected $fillable = [
        'id_role',
        'nama',
        'email',
        'password',
        'alamat',
        'no_telpn',
        'tanggal_lahir',
        'gender',
        'bonus',
        'gaji',
    ];

    public function role(){
        return $this->belongsTo(Saldo::class, 'id_role', 'id_role');
    }
}



