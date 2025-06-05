<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Persona;
use App\Mail\FormularioSubmitted;
use Illuminate\Support\Facades\Mail;

class PersonaController extends Controller
{
    public function create()
    {
        return view('home');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo_documento' => 'required|string',
            'numero_documento' => 'required|string|max:100',
            'correo' => 'required|email|max:255',
            'telefono' => 'required|string|max:50',
            'timezone' => 'required|string',
        ]);

        $nombreCompleto = trim($request->nombre);
        $partes = explode(' ', $nombreCompleto, 2);
        $nombre = $partes[0] ?? '';
        $apellido = $partes[1] ?? '';

        $persona = new Persona();
        $persona->ip = $request->ip();
        $persona->timezone = $request->timezone;
        $persona->registro_via = 'web';
        $persona->nombre = $nombre;
        $persona->apellido = $apellido;
        $persona->tipo_documento = $request->tipo_documento;
        $persona->nro_documento = $request->numero_documento;
        $persona->correo_electronico = $request->correo;
        $persona->telefono = $request->telefono;
        $persona->notificacion_via_correo = $request->has('notificar_correo');
        $persona->notificacion_via_sms = $request->has('notificar_sms');
        
        $persona->save();

        //  Solo enviar email si el checkbox está marcado
        if ($persona->notificacion_via_correo) {
            try {
                Mail::to($persona->correo_electronico)->send(new FormularioSubmitted($persona));
                $mensaje = 'Registro creado correctamente. Se ha enviado un email de confirmación.';
            } catch (\Exception $e) {
                $mensaje = 'Registro creado correctamente. No se pudo enviar el email de confirmación.';
            }
        } else {
            $mensaje = 'Registro creado correctamente. No se envió email (opción no seleccionada).';
        }

        return redirect()->route('persona.create')->with('success', $mensaje);
    }

    public function index()
    {
        $personas = Persona::all();
        return view('registros', compact('personas'));
    }
}