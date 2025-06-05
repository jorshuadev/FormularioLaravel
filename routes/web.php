<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PersonaController;
use Illuminate\Routing\Route as RoutingRoute;

Route::get('//personas/crear', [PersonaController::class, 'create'])->name('persona.create');
Route::post('/personas', [PersonaController::class, 'store'])->name('persona.store');
Route::get('/personas/ver', [PersonaController::class, 'index'])->name('persona.registros');

Route::get('/formulario', function () {
    // Crear una persona de prueba
    $persona = new \App\Models\Persona([
        'nombre' => 'Juan',
        'apellido' => 'Pérez',
        'tipo_documento' => 'cedula',
        'nro_documento' => '12345678',
        'correo_electronico' => 'juan@example.com',
        'telefono' => '61234567',
        'notificacion_via_correo' => true,
        'notificacion_via_sms' => false,
        'created_at' => now()
    ]);
    
    return view('emails.formulario-confirmacion', compact('persona'));
});