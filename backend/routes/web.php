<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PersonajeController;
use App\Http\Controllers\LocacionController;
use App\Http\Controllers\EpisodioController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/importar-personajes', [PersonajeController::class, 'importFromApi']);
Route::get('/importar-locaciones', [LocacionController::class, 'importFromApi']);
Route::get('/importar-episodios', [EpisodioController::class, 'importFromApi']);
