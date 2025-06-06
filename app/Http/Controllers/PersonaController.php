<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Persona;
use App\Mail\FormularioSubmitted;
use App\Services\SmsService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

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
        // 🔥 GUARDAR DATOS ORIGINALES ANTES DE LIMPIAR
        $datosOriginales = $request->all();
        
        // Limpiar y preparar datos antes de validación
        $this->prepareDataForValidation($request);

        // Crear validador personalizado
        $validator = Validator::make($request->all(), $this->getValidationRules($request), $this->getValidationMessages());

        // Si la validación falla, regresar con errores Y MANTENER LOS DATOS ORIGINALES
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($datosOriginales) // 🔥 USAR DATOS ORIGINALES, NO LOS LIMPIADOS
                ->with('error', 'Por favor, corrija los errores en el formulario.');
        }

        try {
            // Separar nombre completo en nombre y apellido
            $nombreCompleto = trim($request->nombre);
            $partesNombre = explode(' ', $nombreCompleto, 2);
            $nombre = $partesNombre[0] ?? '';
            $apellido = $partesNombre[1] ?? '';

            // Crear la persona
            $persona = new Persona();
            $persona->ip = $request->ip();
            $persona->timezone = $request->timezone;
            $persona->registro_via = 'web';
            $persona->nombre = $nombre;
            $persona->apellido = $apellido;
            $persona->tipo_documento = $request->tipo_documento;
            $persona->nro_documento = $request->numero_documento;
            $persona->correo_electronico = $request->correo;
            $persona->telefono = $this->formatPhoneForStorage($request->telefono);
            $persona->notificacion_via_correo = $request->has('notificar_correo');
            $persona->notificacion_via_sms = $request->has('notificar_sms');
            
            $persona->save();

            $mensajes = ['Registro creado correctamente.'];

            // Enviar email si está activado
            if ($persona->notificacion_via_correo) {
                try {
                    Mail::to($persona->correo_electronico)->send(new FormularioSubmitted($persona));
                    $mensajes[] = 'Email de confirmación enviado.';
                } catch (\Exception $e) {
                    Log::error('Error enviando email: ' . $e->getMessage());
                    $mensajes[] = 'No se pudo enviar el email de confirmación.';
                }
            }

            // Enviar SMS si está activado
            if ($persona->notificacion_via_sms) {
                try {
                    $mensajeSms = "Hola {$persona->nombre}, tu registro ha sido completado exitosamente. Gracias por registrarte con nosotros.";
                    $resultadoSms = $this->smsService->sendSms($persona->telefono, $mensajeSms);
                    
                    if ($resultadoSms['success']) {
                        $mensajes[] = 'SMS de confirmación enviado.';
                    } else {
                        $mensajes[] = 'No se pudo enviar el SMS de confirmación: ' . $resultadoSms['message'];
                    }
                } catch (\Exception $e) {
                    Log::error('Error enviando SMS: ' . $e->getMessage());
                    $mensajes[] = 'No se pudo enviar el SMS de confirmación.';
                }
            }

            // 🔥 LIMPIAR EL FORMULARIO después del éxito (sin withInput)
            return redirect()->route('persona.create')->with('success', implode(' ', $mensajes));

        } catch (\Exception $e) {
            Log::error('Error registrando persona: ' . $e->getMessage());
            return redirect()->back()
                ->withInput($datosOriginales) // 🔥 MANTENER DATOS ORIGINALES en caso de error del servidor
                ->with('error', 'Error interno del servidor. Por favor, inténtelo nuevamente.');
        }
    }

    public function index()
    {
        $personas = Persona::orderBy('created_at', 'desc')->paginate(15);
        return view('registros', compact('personas'));
    }

    /**
     * Preparar y limpiar datos antes de la validación
     */
    private function prepareDataForValidation(Request $request)
    {
        // 🔥 AUTO-DETECTAR TIMEZONE SI NO SE PROPORCIONA
        if (!$request->has('timezone') || empty($request->timezone)) {
            $timezone = $this->detectTimezone($request);
            $request->merge(['timezone' => $timezone]);
        }

        // Limpiar número de documento
        if ($request->has('numero_documento')) {
            $numeroDocumento = $this->cleanDocumentNumber($request->numero_documento, $request->tipo_documento);
            $request->merge(['numero_documento' => $numeroDocumento]);
        }

        // Limpiar teléfono
        if ($request->has('telefono')) {
            $telefono = $this->cleanPhoneNumber($request->telefono);
            $request->merge(['telefono' => $telefono]);
        }

        // Limpiar correo
        if ($request->has('correo')) {
            $correo = strtolower(trim($request->correo));
            $request->merge(['correo' => $correo]);
        }

        // Limpiar nombre
        if ($request->has('nombre')) {
            $nombre = $this->cleanName($request->nombre);
            $request->merge(['nombre' => $nombre]);
        }
    }

    /**
     * Obtener reglas de validación
     */
    private function getValidationRules(Request $request)
    {
        $tipoDocumento = $request->input('tipo_documento');
        
        return [
            'nombre' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/' // Solo letras y espacios
            ],
            'tipo_documento' => [
                'required',
                'in:cedula,pasaporte,otros'
            ],
            'numero_documento' => [
                'required',
                'string',
                'unique:personas,nro_documento',
                function ($attribute, $value, $fail) use ($tipoDocumento) {
                    if ($tipoDocumento === 'cedula') {
                        // Validar el formato original (con guiones) antes de limpiar
                        if (!$this->validarCedulaPanama($value)) {
                            $fail('El número de cédula no tiene un formato válido para Panamá. Debe seguir el formato: P-PPP-PPPP o PP-PPPP-PPPP (ej: 8-123-4567, 8-1026-2297).');
                        }
                    } elseif ($tipoDocumento === 'pasaporte') {
                        // Validar formato de pasaporte (alfanumérico, 6-12 caracteres)
                        if (!preg_match('/^[A-Z0-9]{6,12}$/i', $value)) {
                            $fail('El número de pasaporte debe contener entre 6 y 12 caracteres alfanuméricos.');
                        }
                    } elseif ($tipoDocumento === 'otros') {
                        // Validar otros documentos (alfanumérico, 5-20 caracteres)
                        if (!preg_match('/^[A-Z0-9\-]{5,20}$/i', $value)) {
                            $fail('El número de documento debe contener entre 5 y 20 caracteres alfanuméricos.');
                        }
                    }
                }
            ],
            'correo' => [
                'required',
                'email:rfc,dns', // Validación más estricta de email
                'max:255',
                'unique:personas,correo_electronico'
            ],
            'telefono' => [
                'required',
                'string',
                'regex:/^[2-9]\d{6,7}$/', // 🔥 ACTUALIZADO: 7-8 dígitos, empieza con 2-9
                'unique:personas,telefono'
            ],
            'timezone' => [
                'nullable', // 🔥 CAMBIADO: Ya no es required porque se auto-detecta
                function ($attribute, $value, $fail) {
                    // Solo validar si se proporciona un valor
                    if ($value && !$this->validarTimezone($value)) {
                        $fail('La zona horaria seleccionada no es válida.');
                    }
                }
            ],
            'notificar_correo' => 'boolean',
            'notificar_sms' => 'boolean'
        ];
    }

    /**
     * Obtener mensajes de error personalizados
     */
    private function getValidationMessages()
    {
        return [
            // Nombre
            'nombre.required' => 'El nombre completo es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.max' => 'El nombre no puede exceder 255 caracteres.',
            'nombre.regex' => 'El nombre solo puede contener letras y espacios.',
            
            // Tipo de documento
            'tipo_documento.required' => 'Debe seleccionar un tipo de documento.',
            'tipo_documento.in' => 'El tipo de documento seleccionado no es válido.',
            
            // Número de documento
            'numero_documento.required' => 'El número de documento es obligatorio.',
            'numero_documento.unique' => 'Ya existe una persona registrada con este número de documento.',
            
            // Correo electrónico
            'correo.required' => 'El correo electrónico es obligatorio.',
            'correo.email' => 'El formato del correo electrónico no es válido.',
            'correo.unique' => 'Ya existe una persona registrada con este correo electrónico.',
            'correo.max' => 'El correo electrónico no puede exceder 255 caracteres.',
            
            // Teléfono
            'telefono.required' => 'El número de teléfono es obligatorio.',
            'telefono.regex' => 'El número de teléfono debe ser válido para Panamá (7-8 dígitos, ej: 64848240, 2345-6789).',
            'telefono.unique' => 'Ya existe una persona registrada con este número de teléfono.',
            
            // Timezone
            // 'timezone.required' => 'La zona horaria es obligatoria.', // 🔥 REMOVIDO
            
            // Notificaciones
            'notificar_correo.boolean' => 'El campo de notificación por correo debe ser verdadero o falso.',
            'notificar_sms.boolean' => 'El campo de notificación por SMS debe ser verdadero o falso.',
        ];
    }

    /**
     * 🔥 VALIDAR CÉDULA PANAMEÑA - CONFIRMANDO SOPORTE PARA 8-1026-2297 y 810262297
     */
    private function validarCedulaPanama($cedula)
    {
        // Verificar primero el formato con guiones
        if ($this->validarFormatoCedulaConGuiones($cedula)) {
            return true;
        }
        
        // Si no tiene el formato con guiones, verificar el formato sin guiones
        return $this->validarFormatoCedulaSinGuiones($cedula);
    }
    
    /**
     * Validar cédula con formato de guiones (8-123-4567, 8-1026-2297)
     */
    private function validarFormatoCedulaConGuiones($cedula)
    {
        // Formato 1: P-PPP-PPPP (ej: 8-123-4567)
        if (preg_match('/^(\d{1})-(\d{3})-(\d{4})$/', $cedula, $matches)) {
            $provincia = (int)$matches[1];
            $tomo = (int)$matches[2];
            $asiento = (int)$matches[3];
            return $this->validarComponentesCedula($provincia, $tomo, $asiento);
        }
        
        // Formato 2: PP-PPP-PPPP (ej: 10-123-4567)
        if (preg_match('/^(\d{2})-(\d{3})-(\d{4})$/', $cedula, $matches)) {
            $provincia = (int)$matches[1];
            $tomo = (int)$matches[2];
            $asiento = (int)$matches[3];
            return $this->validarComponentesCedula($provincia, $tomo, $asiento);
        }
        
        // Formato 3: P-PPPP-PPPP (ej: 8-1026-2297)
        if (preg_match('/^(\d{1})-(\d{4})-(\d{4})$/', $cedula, $matches)) {
            $provincia = (int)$matches[1];
            $tomo = (int)$matches[2];
            $asiento = (int)$matches[3];
            return $this->validarComponentesCedula($provincia, $tomo, $asiento);
        }
        
        // Formato 4: PP-PPPP-PPPP (ej: 13-1026-2297)
        if (preg_match('/^(\d{2})-(\d{4})-(\d{4})$/', $cedula, $matches)) {
            $provincia = (int)$matches[1];
            $tomo = (int)$matches[2];
            $asiento = (int)$matches[3];
            return $this->validarComponentesCedula($provincia, $tomo, $asiento);
        }
        
        return false;
    }
    
    /**
     * Validar cédula sin guiones (81234567, 810262297)
     */
    private function validarFormatoCedulaSinGuiones($cedula)
    {
        // Limpiar el valor de espacios y guiones
        $cedulaLimpia = preg_replace('/[\s\-]/', '', $cedula);
        
        // Verificar que solo contenga números
        if (!preg_match('/^\d+$/', $cedulaLimpia)) {
            return false;
        }
        
        // Verificar longitud: 8-10 dígitos para cédulas panameñas
        if (strlen($cedulaLimpia) < 8 || strlen($cedulaLimpia) > 10) {
            return false;
        }
        
        $formatoValido = false;
        $provincia = 0;
        $tomo = 0;
        $asiento = 0;
        
        // Formato 1: P-PPP-PPPP (8 dígitos) - ej: 8-123-4567 → 81234567
        if (preg_match('/^(\d{1})(\d{3})(\d{4})$/', $cedulaLimpia, $matches)) {
            $provincia = (int)$matches[1];
            $tomo = (int)$matches[2];
            $asiento = (int)$matches[3];
            $formatoValido = true;
        }
        // Formato 2: PP-PPP-PPPP (9 dígitos) - ej: 10-123-4567 → 101234567
        elseif (preg_match('/^(\d{2})(\d{3})(\d{4})$/', $cedulaLimpia, $matches)) {
            $provincia = (int)$matches[1];
            $tomo = (int)$matches[2];
            $asiento = (int)$matches[3];
            $formatoValido = true;
        }
        // Formato 3: P-PPPP-PPPP (9 dígitos) - ej: 8-1026-2297 → 810262297
        elseif (preg_match('/^(\d{1})(\d{4})(\d{4})$/', $cedulaLimpia, $matches)) {
            $provincia = (int)$matches[1];
            $tomo = (int)$matches[2];
            $asiento = (int)$matches[3];
            $formatoValido = true;
        }
        // Formato 4: PP-PPPP-PPPP (10 dígitos) - ej: 13-1026-2297 → 13102622297
        elseif (preg_match('/^(\d{2})(\d{4})(\d{4})$/', $cedulaLimpia, $matches)) {
            $provincia = (int)$matches[1];
            $tomo = (int)$matches[2];
            $asiento = (int)$matches[3];
            $formatoValido = true;
        }
        
        if (!$formatoValido) {
            return false;
        }
        
        return $this->validarComponentesCedula($provincia, $tomo, $asiento);
    }
    
    /**
     * Validar los componentes de la cédula (provincia, tomo, asiento)
     */
    private function validarComponentesCedula($provincia, $tomo, $asiento)
    {
        // Validar provincia: 1-13 para provincias panameñas + comarcas
        if ($provincia < 1 || $provincia > 13) {
            return false;
        }
        
        // Validar que tomo y asiento no sean 0
        if ($tomo === 0 || $asiento === 0) {
            return false;
        }
        
        return true;
    }

    /**
     * Validar timezone
     */
    private function validarTimezone($timezone)
    {
        return in_array($timezone, timezone_identifiers_list());
    }

    /**
     * Limpiar número de documento
     */
    private function cleanDocumentNumber($document, $tipoDocumento)
    {
        if (!$document) return '';
        
        // Para cédulas, mantener los guiones para la validación
        // pero eliminarlos para el almacenamiento
        if ($tipoDocumento === 'cedula') {
            return preg_replace('/[^\d]/', '', $document);
        }
        
        // Para pasaportes y otros, limpiar espacios extra
        return strtoupper(trim(preg_replace('/\s+/', '', $document)));
    }

    /**
     * 🔥 ACTUALIZADO: Limpiar número de teléfono para Panamá
     */
    private function cleanPhoneNumber($phone)
    {
        if (!$phone) return '';
        
        // Remover espacios, guiones y paréntesis
        $cleaned = preg_replace('/[\s\-()]/', '', $phone);
        
        // Si empieza con +507, removerlo para validación local
        if (strpos($cleaned, '+507') === 0) {
            return substr($cleaned, 4);
        }
        
        // Si empieza con 507, removerlo
        if (strpos($cleaned, '507') === 0 && strlen($cleaned) === 10) {
            return substr($cleaned, 3);
        }
        
        return $cleaned;
    }

    /**
     * Limpiar nombre
     */
    private function cleanName($name)
    {
        if (!$name) return '';
        
        // Limpiar espacios extra y capitalizar
        $cleaned = trim(preg_replace('/\s+/', ' ', $name));
        
        // Capitalizar cada palabra
        return mb_convert_case($cleaned, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Formatear teléfono para almacenamiento
     */
    private function formatPhoneForStorage($phone)
    {
        // Remover +507 si existe para almacenar solo el número local
        $cleaned = preg_replace('/^\+507/', '', $phone);
        return preg_replace('/[\s\-()]/', '', $cleaned);
    }

    /**
     * 🔥 DETECTAR TIMEZONE AUTOMÁTICAMENTE
     */
    private function detectTimezone(Request $request)
    {
        try {
            // Método 1: Detectar por IP usando servicio gratuito
            $timezone = $this->detectTimezoneByIP($request->ip());
            if ($timezone) {
                Log::info("Timezone detectado por IP: {$timezone} para IP: " . $request->ip());
                return $timezone;
            }

            // Método 2: Detectar por headers del navegador
            $timezone = $this->detectTimezoneByHeaders($request);
            if ($timezone) {
                Log::info("Timezone detectado por headers: {$timezone}");
                return $timezone;
            }

            // Método 3: Detectar por código de país del teléfono
            if ($request->has('telefono')) {
                $timezone = $this->detectTimezoneByPhone($request->telefono);
                if ($timezone) {
                    Log::info("Timezone detectado por teléfono: {$timezone}");
                    return $timezone;
                }
            }

        } catch (\Exception $e) {
            Log::warning("Error detectando timezone: " . $e->getMessage());
        }

        // Fallback: Timezone por defecto para Panamá
        Log::info("Usando timezone por defecto: America/Panama");
        return 'America/Panama';
    }

    /**
     * Detectar timezone por IP usando servicio gratuito
     */
    private function detectTimezoneByIP($ip)
    {
        try {
            // No detectar para IPs locales
            if ($ip === '127.0.0.1' || $ip === '::1' || strpos($ip, '192.168.') === 0 || strpos($ip, '10.') === 0) {
                return null;
            }

            // Usar servicio gratuito de geolocalización
            $url = "http://ip-api.com/json/{$ip}?fields=timezone,status";
            
            $context = stream_context_create([
                'http' => [
                    'timeout' => 3, // 3 segundos timeout
                    'user_agent' => 'Laravel App Timezone Detection'
                ]
            ]);

            $response = @file_get_contents($url, false, $context);
            
            if ($response) {
                $data = json_decode($response, true);
                
                if ($data && $data['status'] === 'success' && isset($data['timezone'])) {
                    $timezone = $data['timezone'];
                    
                    // Verificar que el timezone sea válido
                    if (in_array($timezone, timezone_identifiers_list())) {
                        return $timezone;
                    }
                }
            }

        } catch (\Exception $e) {
            Log::warning("Error en detección por IP: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Detectar timezone por headers del navegador
     */
    private function detectTimezoneByHeaders(Request $request)
    {
        try {
            // Buscar header personalizado de timezone (si el frontend lo envía)
            $timezone = $request->header('X-Timezone');
            if ($timezone && in_array($timezone, timezone_identifiers_list())) {
                return $timezone;
            }

            // Detectar por Accept-Language header
            $acceptLanguage = $request->header('Accept-Language');
            if ($acceptLanguage) {
                // Mapeo básico de idiomas a timezones comunes
                $languageTimezoneMap = [
                    'es-PA' => 'America/Panama',
                    'es-CR' => 'America/Costa_Rica',
                    'es-GT' => 'America/Guatemala',
                    'es-HN' => 'America/Tegucigalpa',
                    'es-NI' => 'America/Managua',
                    'es-SV' => 'America/El_Salvador',
                    'es-BZ' => 'America/Belize',
                    'es-MX' => 'America/Mexico_City',
                    'es-CO' => 'America/Bogota',
                    'es-VE' => 'America/Caracas',
                    'es-US' => 'America/New_York',
                    'en-US' => 'America/New_York',
                    'en-CA' => 'America/Toronto',
                ];

                foreach ($languageTimezoneMap as $lang => $tz) {
                    if (strpos($acceptLanguage, $lang) !== false) {
                        return $tz;
                    }
                }

                // Fallback para español genérico
                if (strpos($acceptLanguage, 'es') !== false) {
                    return 'America/Panama';
                }
            }

        } catch (\Exception $e) {
            Log::warning("Error en detección por headers: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Detectar timezone por número de teléfono
     */
    private function detectTimezoneByPhone($phone)
    {
        try {
            $cleanPhone = preg_replace('/[^\d+]/', '', $phone);

            // Mapeo de códigos de país a timezones
            $phoneTimezoneMap = [
                '+507' => 'America/Panama',
                '507' => 'America/Panama',
                '+506' => 'America/Costa_Rica',
                '506' => 'America/Costa_Rica',
                '+502' => 'America/Guatemala',
                '502' => 'America/Guatemala',
                '+504' => 'America/Tegucigalpa',
                '504' => 'America/Tegucigalpa',
                '+505' => 'America/Managua',
                '505' => 'America/Managua',
                '+503' => 'America/El_Salvador',
                '503' => 'America/El_Salvador',
                '+501' => 'America/Belize',
                '501' => 'America/Belize',
                '+52' => 'America/Mexico_City',
                '52' => 'America/Mexico_City',
                '+57' => 'America/Bogota',
                '57' => 'America/Bogota',
                '+58' => 'America/Caracas',
                '58' => 'America/Caracas',
                '+1' => 'America/New_York',
                '1' => 'America/New_York',
            ];

            foreach ($phoneTimezoneMap as $code => $timezone) {
                if (strpos($cleanPhone, $code) === 0) {
                    return $timezone;
                }
            }

            // Si el teléfono no tiene código de país pero parece panameño (7-8 dígitos empezando con 2-9)
            if (preg_match('/^[2-9]\d{6,7}$/', $cleanPhone)) {
                return 'America/Panama';
            }

        } catch (\Exception $e) {
            Log::warning("Error en detección por teléfono: " . $e->getMessage());
        }

        return null;
    }
}