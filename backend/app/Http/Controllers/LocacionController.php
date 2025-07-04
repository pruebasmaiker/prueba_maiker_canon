<?php

namespace App\Http\Controllers;

use App\Models\Locacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LocacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Locacion $locacion)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Locacion $locacion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Locacion $locacion)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Locacion $locacion)
    {
        //
    }

    /**
     * Importar locaciones desde la API de Rick and Morty
     */
    public function importFromApi()
    {
        $url = 'https://rickandmortyapi.com/api/location';
        $importados = 0;
        do {
            $response = Http::get($url);
            if ($response->failed()) break;
            $data = $response->json();
            foreach ($data['results'] as $item) {
                Locacion::updateOrCreate(
                    ['id' => $item['id']],
                    [
                        'nombre' => $item['name'],
                        'tipo' => $item['type'],
                        'dimension' => $item['dimension'],
                    ]
                );
                $importados++;
            }
            $url = $data['info']['next'];
        } while ($url);
        return response()->json(['importados' => $importados]);
    }

    /**
     * Listar locaciones (API)
     */
    public function apiIndex()
    {
        return response()->json(\App\Models\Locacion::all(['id', 'nombre', 'tipo', 'dimension']));
    }

    /**
     * Reporte: Listar todas las locaciones y los personajes de cada una
     */
    public function reporteLocacionesConPersonajes()
    {
        $locaciones = \App\Models\Locacion::with('personajes')->get();
        $result = $locaciones->map(function($l) {
            return [
                'locacion' => $l->nombre,
                'tipo' => $l->tipo,
                'dimension' => $l->dimension,
                'personajes' => $l->personajes->pluck('nombre')->values()
            ];
        });
        return response()->json($result);
    }
}
