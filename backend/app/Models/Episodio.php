<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Episodio extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'nombre',
        'fecha_emision',
        'codigo',
        'url',
    ];

    public function personajes()
    {
        return $this->belongsToMany(Personaje::class, 'personaje_episodio');
    }
}
