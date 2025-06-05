<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Persona;
use App\Mail\FormularioSubmitted;
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
                'registro_via' => 'mobile', // ✅ Cambiado de 'api' a 'mobile'
                'notificacion_via_correo' => $request->boolean('notificar_por_correo'),
                'notificacion_via_sms' => $request->boolean('notificar_por_sms'),
            ];

            // Log para debug usando helper function
            logger('Datos a insertar:', $datosPersona);

            // Crear la persona
            $persona = Persona::create($datosPersona);

            logger('Persona creada exitosamente', ['id' => $persona->id]);

            // ✅ LÓGICA DE ENVÍO DE EMAIL CONDICIONAL
            $emailStatus = '';
            
            // Solo enviar email si el checkbox está marcado
            if ($persona->notificacion_via_correo) {
                try {
                    Mail::to($persona->correo_electronico)->send(new FormularioSubmitted($persona));
                    $emailStatus = 'Email de confirmación enviado.';
                    logger('Email enviado exitosamente', ['email' => $persona->correo_electronico]);
                } catch (\Exception $e) {
                    $emailStatus = 'No se pudo enviar el email de confirmación.';
                    logger('Error enviando email', [
                        'email' => $persona->correo_electronico,
                        'error' => $e->getMessage()
                    ]);
                }
            } else {
                $emailStatus = 'No se envió email (opción no seleccionada).';
                logger('Email no enviado - opción desactivada', ['email' => $persona->correo_electronico]);
            }

            return response()->json([
                'success' => true,
                'data' => $persona,
                'message' => 'Persona registrada exitosamente. ' . $emailStatus
            ], 201);

        } catch (QueryException $e) {
            logger()->error('Error de base de datos:', [
                'message' => $e->getMessage(),
                'sql' => $e->getSql() ?? 'N/A'
            ]);

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
            logger()->error('Error general:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }
}