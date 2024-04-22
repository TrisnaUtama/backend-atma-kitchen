<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penitip extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'penitip'; 
    protected $primaryKey = 'id'; 

    protected $fillable = [
        'nama',
        'no_telpn',
        'email',
        'profit',
    ];
}
