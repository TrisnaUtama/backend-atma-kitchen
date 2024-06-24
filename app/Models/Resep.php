<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resep extends Model
{
    public $timestamps = false;
    protected $table = 'resep';
    protected $primaryKey = 'id';
    protected $fillable = [
        'nama_resep'
    ];

    public function komposisi()
    {
        return $this->hasMany(Komposisi::class, 'id_resep', 'id');
    }
}
