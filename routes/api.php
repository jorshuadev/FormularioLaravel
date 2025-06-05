<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PersonaController;
use App\Http\Middleware\ValidateApiToken;

// Ruta de debug temporal para ver errores detallados
Route::post('/personas-debug', function(Request $request) {
    try {
        return app(PersonaController::class)($request);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error interno del servidor',
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->middleware(ValidateApiToken::class);

// Tu ruta original
Route::post('/personas', PersonaController::class)->middleware(ValidateApiToken::class);
Route::post('/persona', PersonaController::class);