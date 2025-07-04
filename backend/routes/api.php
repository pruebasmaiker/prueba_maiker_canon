<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PersonajeController;
use App\Http\Controllers\EpisodioController;
use App\Http\Controllers\LocacionController;
use App\Http\Controllers\ReporteController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Listar personajes con búsqueda por nombre
Route::get('/personajes', [PersonajeController::class, 'apiIndex']);
Route::get('/personajes/estados', [PersonajeController::class, 'getEstadosDisponibles']);
// Detalle de personaje (con episodios y locaciones)
Route::get('/personajes/{id}', [PersonajeController::class, 'apiShow']);
// Listar episodios
Route::get('/episodios', [EpisodioController::class, 'apiIndex']);
// Listar locaciones
Route::get('/locaciones', [LocacionController::class, 'apiIndex']);

// Reportes fijos
Route::get('/reportes/personajes-por-fecha', [PersonajeController::class, 'reportePersonajesPorFecha']);
Route::get('/reportes/personajes-por-episodio', [EpisodioController::class, 'reportePersonajesPorEpisodio']);
Route::get('/reportes/locaciones-con-personajes', [LocacionController::class, 'reporteLocacionesConPersonajes']);

// Reporteador flexible
Route::get('/reportes', [ReporteController::class, 'index']);

Route::post('/importar-personaje', [PersonajeController::class, 'importarDesdeFrontend']);
