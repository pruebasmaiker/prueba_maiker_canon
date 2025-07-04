<?php

namespace App\Http\Controllers;

use App\Models\Episodio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class EpisodioController extends Controller
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
    public function show(Episodio $episodio)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Episodio $episodio)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Episodio $episodio)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Episodio $episodio)
    {
        //
    }

    /**
     * Importar episodios desde la API de Rick and Morty
     */
    public function importFromApi()
    {
        $url = 'https://rickandmortyapi.com/api/episode';
        $importados = 0;
        do {
            $response = Http::get($url);
            if ($response->failed()) break;
            $data = $response->json();
            foreach ($data['results'] as $item) {
                Episodio::updateOrCreate(
                    ['id' => $item['id']],
                    [
                        'nombre' => $item['name'],
                        'fecha_emision' => $item['air_date'] ? date('Y-m-d', strtotime($item['air_date'])) : null,
                        'codigo' => $item['episode'],
                        'url' => $item['url'],
                    ]
                );
                $importados++;
            }
            $url = $data['info']['next'];
        } while ($url);
        return response()->json(['importados' => $importados]);
    }

    /**
     * Listar episodios (API)
     */
    public function apiIndex()
    {
        return response()->json(\App\Models\Episodio::all(['id', 'nombre', 'codigo', 'fecha_emision', 'url']));
    }

    /**
     * Reporte: Cantidad de personajes por episodio
     */
    public function reportePersonajesPorEpisodio()
    {
        $episodios = \App\Models\Episodio::withCount('personajes')->get();
        $result = $episodios->map(function($e) {
            return [
                'episodio' => $e->nombre,
                'codigo' => $e->codigo,
                'cant_personajes' => $e->personajes_count
            ];
        })->sortByDesc('cant_personajes')->values();
        return response()->json($result);
    }
}
