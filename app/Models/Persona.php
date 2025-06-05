<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    use HasFactory;

    protected $table = 'personas';

    // Campos básicos que seguramente existen
    protected $fillable = [
        'nombre',
        'apellido',
        'tipo_documento',
        'nro_documento',
        'correo_electronico',
        'telefono',
        'registro_via',
        // Campos opcionales
        'ip',
        'timezone',
        'notificacion_via_correo',
        'notificacion_via_sms'
    ];

    protected $casts = [
        'notificacion_via_correo' => 'boolean',
        'notificacion_via_sms' => 'boolean',
    ];
}