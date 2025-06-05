<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PersonaController;

Route::get('//personas/crear', [PersonaController::class, 'create'])->name('persona.create');
Route::post('/personas', [PersonaController::class, 'store'])->name('persona.store');
Route::get('/personas/ver', [PersonaController::class, 'index'])->name('persona.registros');
