<?php
// app/Http/Controllers/Api/PersonaController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Persona;
use App\Mail\FormularioSubmitted;
use App\Services\SmsService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;

class PersonaController extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            // Validar token (esto idealmente va en middleware)
            if ($request->header('Authorization') !== config('app.token_api')) {
                return response()->json(['success' => false, 'message' => 'Token inválido'], 401);
            }

            // Validaciones
            $validator = Validator::make($request->all(), [
                'nombre_completo' => 'required|string|min:3',
                'tipo_documento' => 'required|in:cedula,pasaporte,otros',
                'nro_documento' => 'required|string|unique:personas,nro_documento',
                'correo_electronico' => 'required|email|unique:personas,correo_electronico',
                'telefono' => ['required', 'regex:/^(\+507)?6\d{7}$/'],
                'timezone' => 'required|string',
                'notificar_por_correo' => 'boolean',
                'notificar_por_sms' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Separar nombre y apellido (simple split)
            $nombreCompleto = trim($request->nombre_completo);
            $partes = explode(' ', $nombreCompleto, 2);
            $nombre = $partes[0] ?? '';
            $apellido = $partes[1] ?? '';

            // Limpiar caracteres no numéricos de nro_documento (quitar guiones u otros)
            $nroDocumentoLimpio = preg_replace('/[^0-9]/', '', $request->nro_documento);

            // Preparar datos para insertar
            $datosPersona = [
                'nombre' => $nombre,
                'apellido' => $apellido,
                'tipo_documento' => $request->tipo_documento,
                'nro_documento' => $nroDocumentoLimpio,
                'correo_electronico' => $request->correo_electronico,
                'telefono' => $request->telefono,
                'ip' => $request->ip(),
                'timezone' => $request->timezone,
                'registro_via' => 'mobile',
                'notificacion_via_correo' => $request->boolean('notificar_por_correo'),
                'notificacion_via_sms' => $request->boolean('notificar_por_sms'),
            ];

            // Crear la persona
            $persona = Persona::create($datosPersona);

            // ✅ LÓGICA DE ENVÍO DE EMAIL Y SMS CONDICIONAL
            $notificaciones = [];
            
            // Solo enviar email si el checkbox está marcado
            if ($persona->notificacion_via_correo) {
                try {
                    Mail::to($persona->correo_electronico)->send(new FormularioSubmitted($persona));
                    $notificaciones[] = 'Email de confirmación enviado.';
                } catch (\Exception $e) {
                    $notificaciones[] = 'No se pudo enviar el email de confirmación.';
                }
            }

            // Solo enviar SMS si el checkbox está marcado
            if ($persona->notificacion_via_sms) {
                $smsService = new SmsService();
                $mensajeSms = "Hola {$persona->nombre}, tu registro ha sido completado exitosamente.";
                $resultadoSms = $smsService->sendSms($persona->telefono, $mensajeSms);
                
                if ($resultadoSms['success']) {
                    $notificaciones[] = 'SMS de confirmación enviado.';
                } else {
                    $notificaciones[] = 'No se pudo enviar el SMS de confirmación.';
                }
            }

            $mensajeFinal = 'Persona registrada exitosamente.';
            if (!empty($notificaciones)) {
                $mensajeFinal .= ' ' . implode(' ', $notificaciones);
            }

            return response()->json([
                'success' => true,
                'data' => $persona,
                'message' => $mensajeFinal
            ], 201);

        } catch (QueryException $e) {
            // Error específico para duplicados
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'nro_documento' => ['Ya existe una persona con esa cédula.']
                    ]
                ], 422);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error de base de datos: ' . $e->getMessage()
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }
}