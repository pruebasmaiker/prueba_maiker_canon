<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Personaje extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'nombre',
        'estado',
        'especie',
        'tipo',
        'genero',
        'imagen',
    ];

    public function episodios()
    {
        return $this->belongsToMany(Episodio::class, 'personaje_episodio');
    }

    public function locaciones()
    {
        return $this->belongsToMany(Locacion::class, 'personaje_locacion');
    }
}
