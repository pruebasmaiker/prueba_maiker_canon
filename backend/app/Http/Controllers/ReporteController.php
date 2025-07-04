<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Personaje;
use App\Models\Episodio;
use App\Models\Locacion;
use Illuminate\Support\Carbon;

class ReporteController extends Controller
{
    public function index(Request $request)
    {
        $estado = $request->query('estado');
        $locacion = $request->query('locacion');
        $personaje_nombre = $request->query('personaje_nombre');
        $episodio_nombre = $request->query('episodio_nombre');
        $fecha_inicio = $request->query('fecha_inicio');
        $fecha_fin = $request->query('fecha_fin');

        $resultados = [];

        if ($estado) {
            $resultados['personajes_por_estado'] = Personaje::where('estado', $estado)->get();
        }

        if ($locacion) {
            $resultados['personajes_por_locacion'] = Personaje::whereHas('locaciones', function($q) use ($locacion) {
                $q->where('nombre', $locacion);
            })->get();
        }

        if ($personaje_nombre) {
            $personaje = Personaje::with('episodios')->where('nombre', 'LIKE', "%{$personaje_nombre}%")->first();
            $resultados['episodios_de_personaje'] = $personaje ? $personaje->episodios : [];
        }

        if ($episodio_nombre) {
            $episodio = Episodio::with('personajes')->where('nombre', 'LIKE', "%{$episodio_nombre}%")->first();
            $resultados['personajes_de_episodio'] = $episodio ? $episodio->personajes : [];
        }

        if ($fecha_inicio && $fecha_fin) {
            $resultados['episodios_por_fecha'] = Episodio::whereBetween('fecha_emision', [$fecha_inicio, $fecha_fin])->get();
        }

        return response()->json($resultados);
    }
} 