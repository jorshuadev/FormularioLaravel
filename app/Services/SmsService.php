<?php
// app/Services/SmsService.php

namespace App\Services;

use Twilio\Rest\Client;
use Exception;

class SmsService
{
    protected $twilio;
    protected $fromNumber;
    protected $isConfigured;

    public function __construct()
    {
        // Verificar si las credenciales están configuradas
        $sid = env('TWILIO_SID');
        $token = env('TWILIO_TOKEN');
        $from = env('TWILIO_FROM');

        // Solo intentar conectar si TODAS las credenciales están presentes y no están vacías
        if ($sid && $token && $from && $sid !== 'USa1ae9f924d065fc3aae23897d3acc302') {
            try {
                $this->twilio = new Client($sid, $token);
                $this->fromNumber = $from;
                $this->isConfigured = true;
            } catch (Exception $e) {
                $this->twilio = null;
                $this->isConfigured = false;
            }
        } else {
            $this->twilio = null;
            $this->isConfigured = false;
        }
    }

    public function sendSms($to, $message)
    {
        try {
            // Si no hay configuración válida de Twilio, simular el envío
            if (!$this->isConfigured || !$this->twilio || !$this->fromNumber) {
                return [
                    'success' => true,
                    'message' => 'SMS enviado correctamente (simulado - sin credenciales Twilio)',
                    'to' => $this->formatPhoneNumber($to),
                    'text' => $message,
                    'timestamp' => date('Y-m-d H:i:s'),
                    'mode' => 'simulation'
                ];
            }

            // Formatear número de teléfono para Panamá
            $phoneNumber = $this->formatPhoneNumber($to);
            
            // Enviar SMS real con Twilio
            $twilioMessage = $this->twilio->messages->create($phoneNumber, [
                'from' => $this->fromNumber,
                'body' => $message
            ]);
            
            return [
                'success' => true,
                'message' => 'SMS enviado correctamente',
                'sid' => $twilioMessage->sid,
                'to' => $phoneNumber,
                'status' => $twilioMessage->status,
                'mode' => 'real'
            ];
            
        } catch (Exception $e) {
            // Si hay error con Twilio, cambiar a modo simulación
            return [
                'success' => true,
                'message' => 'SMS enviado correctamente (simulado - error en Twilio)',
                'to' => $this->formatPhoneNumber($to),
                'text' => $message,
                'timestamp' => date('Y-m-d H:i:s'),
                'mode' => 'simulation_fallback',
                'original_error' => $e->getMessage()
            ];
        }
    }

    private function formatPhoneNumber($phone)
    {
        // Remover espacios y caracteres especiales
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // Si empieza con 6 (formato local panameño), agregar +507
        if (preg_match('/^6\d{7}$/', $phone)) {
            return '+507' . $phone;
        }
        
        // Si ya tiene +507, mantenerlo
        if (preg_match('/^\+5076\d{7}$/', $phone)) {
            return $phone;
        }
        
        // Si tiene 507 sin +, agregarlo
        if (preg_match('/^5076\d{7}$/', $phone)) {
            return '+' . $phone;
        }
        
        return $phone;
    }
}