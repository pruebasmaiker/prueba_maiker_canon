<?php

namespace App\Http\Controllers;

use App\Models\Personaje;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Episodio;
use App\Models\Locacion;
use Illuminate\Support\Facades\DB;

class PersonajeController extends Controller
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
    public function show(Personaje $personaje)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Personaje $personaje)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Personaje $personaje)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Personaje $personaje)
    {
        //
    }

    /**
     * Importar personajes desde la API de Rick and Morty
     */
    public function importFromApi()
    {
        $url = 'https://rickandmortyapi.com/api/character';
        $importados = 0;
        do {
            $response = Http::get($url);
            if ($response->failed()) break;
            $data = $response->json();
            foreach ($data['results'] as $item) {
                $personaje = Personaje::updateOrCreate(
                    ['id' => $item['id']],
                    [
                        'nombre' => $item['name'],
                        'estado' => $item['status'],
                        'especie' => $item['species'],
                        'tipo' => $item['type'],
                        'genero' => $item['gender'],
                        'imagen' => $item['image'],
                    ]
                );
                // Relacionar episodios
                $episodioIds = [];
                foreach ($item['episode'] as $epUrl) {
                    $epId = intval(basename($epUrl));
                    if ($epId) $episodioIds[] = $epId;
                }
                if ($episodioIds) {
                    $personaje->episodios()->syncWithoutDetaching($episodioIds);
                }
                // Relacionar locaciones (origen y residencia)
                $locaciones = [];
                if (!empty($item['origin']['url'])) {
                    $locId = intval(basename($item['origin']['url']));
                    if ($locId) $locaciones[$locId] = ['tipo' => 'origen'];
                }
                if (!empty($item['location']['url'])) {
                    $locId = intval(basename($item['location']['url']));
                    if ($locId) $locaciones[$locId] = ['tipo' => 'residencia'];
                }
                if ($locaciones) {
                    foreach ($locaciones as $locId => $pivot) {
                        DB::table('personaje_locacion')->updateOrInsert(
                            [
                                'personaje_id' => $personaje->id,
                                'locacion_id' => $locId,
                                'tipo' => $pivot['tipo']
                            ]
                        );
                    }
                }
                $importados++;
            }
            $url = $data['info']['next'];
        } while ($url);
        return response()->json(['importados' => $importados]);
    }

    /**
     * Listar personajes con búsqueda por nombre (API)
     */
    public function apiIndex(Request $request)
    {
        $query = Personaje::query();
        if ($request->has('nombre')) {
            $query->where('nombre', 'like', '%' . $request->nombre . '%');
        }
        return response()->json($query->get(['id', 'nombre', 'imagen']));
    }

    /**
     * Detalle de personaje con episodios y locaciones (API)
     */
    public function apiShow($id)
    {
        $personaje = Personaje::with(['episodios', 'locaciones'])->findOrFail($id);
        return response()->json($personaje);
    }

    /**
     * Reporte: Personajes en orden ascendente por fecha de emisión de su primer episodio
     */
    public function reportePersonajesPorFecha()
    {
        $personajes = \App\Models\Personaje::with(['episodios' => function($q) {
            $q->orderBy('fecha_emision', 'asc');
        }])->get();
        $result = $personajes->map(function($p) {
            $primerEpisodio = $p->episodios->sortBy('fecha_emision')->first();
            return [
                'personaje' => $p->nombre,
                'primer_episodio' => $primerEpisodio ? $primerEpisodio->nombre : null,
                'fecha_emision' => $primerEpisodio ? \Carbon\Carbon::parse($primerEpisodio->fecha_emision)->translatedFormat('F d, Y') : null,
                'antiguedad_dias' => $primerEpisodio ? \Carbon\Carbon::parse($primerEpisodio->fecha_emision)->diffInDays(now()) : null,
            ];
        })->sortBy('fecha_emision')->values();
        return response()->json($result);
    }

    /**
     * Importar personaje, locación y episodios desde el frontend
     */
    public function importarDesdeFrontend(Request $request)
    {
        \Log::info('Datos recibidos en importarDesdeFrontend:', $request->all());
        
        try {
            // Validar datos básicos
            $data = $request->validate([
                'personaje.id' => 'required|integer',
                'personaje.nombre' => 'required|string',
                'personaje.estado' => 'nullable|string',
                'personaje.especie' => 'nullable|string',
                'personaje.tipo' => 'nullable|string',
                'personaje.genero' => 'nullable|string',
                'personaje.imagen' => 'nullable|string',
                'locacion.id' => 'required|integer',
                'locacion.nombre' => 'required|string',
                'locacion.tipo' => 'nullable|string',
                'locacion.dimension' => 'nullable|string',
                'episodios' => 'required|array|min:1',
                'episodios.*.id' => 'required|integer',
                'episodios.*.nombre' => 'required|string',
                'episodios.*.fecha_emision' => 'required|string',
                'episodios.*.codigo' => 'required|string',
                'episodios.*.url' => 'required|string',
            ]);
            
            \Log::info('Validación exitosa, procesando datos...');
            
            // 1. Guardar o actualizar locación
            $locacion = Locacion::updateOrCreate(
                ['id' => $data['locacion']['id']],
                [
                    'nombre' => $data['locacion']['nombre'],
                    'tipo' => $data['locacion']['tipo'] ?? 'Unknown',
                    'dimension' => $data['locacion']['dimension'] ?? 'Unknown',
                ]
            );
            \Log::info('Locación guardada:', ['id' => $locacion->id, 'nombre' => $locacion->nombre]);

            // 2. Guardar o actualizar episodios
            $episodioIds = [];
            foreach ($data['episodios'] as $ep) {
                $episodio = Episodio::updateOrCreate(
                    ['id' => $ep['id']],
                    [
                        'nombre' => $ep['nombre'],
                        'fecha_emision' => $ep['fecha_emision'],
                        'codigo' => $ep['codigo'],
                        'url' => $ep['url'],
                    ]
                );
                $episodioIds[] = $episodio->id;
            }
            \Log::info('Episodios guardados:', ['count' => count($episodioIds), 'ids' => $episodioIds]);

            // 3. Guardar o actualizar personaje
            $personaje = Personaje::updateOrCreate(
                ['id' => $data['personaje']['id']],
                [
                    'nombre' => $data['personaje']['nombre'],
                    'estado' => $data['personaje']['estado'] ?? 'Unknown',
                    'especie' => $data['personaje']['especie'] ?? 'Unknown',
                    'tipo' => $data['personaje']['tipo'] ?? '',
                    'genero' => $data['personaje']['genero'] ?? 'Unknown',
                    'imagen' => $data['personaje']['imagen'] ?? '',
                ]
            );
            \Log::info('Personaje guardado:', ['id' => $personaje->id, 'nombre' => $personaje->nombre]);

            // 4. Relacionar personaje con locación (tipo: origen)
            $personaje->locaciones()->syncWithoutDetaching([$locacion->id => ['tipo' => 'origen']]);
            
            // 5. Relacionar personaje con episodios
            $personaje->episodios()->syncWithoutDetaching($episodioIds);

            \Log::info('Importación completada exitosamente');
            return response()->json([
                'success' => true, 
                'message' => 'Importación exitosa',
                'personaje_id' => $personaje->id,
                'locacion_id' => $locacion->id,
                'episodios_count' => count($episodioIds)
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Error de validación:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error general en importación:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getEstadosDisponibles()
    {
        $estados = Personaje::distinct()->pluck('estado')->filter()->values();
        return response()->json($estados);
    }
}
