<?php
// app/Http/Controllers/PersonaController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Persona;
use App\Mail\FormularioSubmitted;
use App\Services\SmsService;
use Illuminate\Support\Facades\Mail;

class PersonaController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

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

        $mensajes = [];

        // ✅ Enviar email si está activado
        if ($persona->notificacion_via_correo) {
            try {
                Mail::to($persona->correo_electronico)->send(new FormularioSubmitted($persona));
                $mensajes[] = 'Email de confirmación enviado.';
            } catch (\Exception $e) {
                $mensajes[] = 'No se pudo enviar el email de confirmación.';
            }
        }

        // ✅ Enviar SMS si está activado
        if ($persona->notificacion_via_sms) {
            $mensajeSms = "Hola {$persona->nombre}, tu registro ha sido completado exitosamente. Gracias por registrarte con nosotros.";
            $resultadoSms = $this->smsService->sendSms($persona->telefono, $mensajeSms);
            
            if ($resultadoSms['success']) {
                $mensajes[] = 'SMS de confirmación enviado.';
            } else {
                $mensajes[] = 'No se pudo enviar el SMS de confirmación.';
            }
        }

        $mensajeFinal = 'Registro creado correctamente.';
        if (!empty($mensajes)) {
            $mensajeFinal .= ' ' . implode(' ', $mensajes);
        }

        return redirect()->route('persona.create')->with('success', $mensajeFinal);
    }

    public function index()
    {
        $personas = Persona::all();
        return view('registros', compact('personas'));
    }
}