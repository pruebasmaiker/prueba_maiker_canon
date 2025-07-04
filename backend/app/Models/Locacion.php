<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Locacion extends Model
{
    use HasFactory;

    protected $table = 'locaciones';

    protected $fillable = [
        'id',
        'nombre',
        'tipo',
        'dimension',
    ];

    public function personajes()
    {
        return $this->belongsToMany(Personaje::class, 'personaje_locacion');
    }
}
