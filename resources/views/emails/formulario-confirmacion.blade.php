<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Registro</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            background: #007bff;
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px;
        }
        .info-row {
            margin: 15px 0;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .label {
            font-weight: bold;
            color: #495057;
            display: inline-block;
            width: 150px;
        }
        .value {
            color: #333;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
        }
        .success-badge {
            background: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            display: inline-block;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>¡Registro Completado!</h1>
            <p>Gracias por registrarte con nosotros</p>
            <div class="success-badge">✓ Confirmado</div>
        </div>
        
        <div class="content">
            <h2>Hola {{ $persona->nombre }} {{ $persona->apellido }},</h2>
            
            <p>Tu registro ha sido completado exitosamente. Aquí están los detalles de tu información:</p>
            
            <div class="info-row">
                <span class="label">Nombre Completo:</span>
                <span class="value">{{ $persona->nombre }} {{ $persona->apellido }}</span>
            </div>
            
            <div class="info-row">
                <span class="label">Tipo de Documento:</span>
                <span class="value">{{ ucfirst($persona->tipo_documento) }}</span>
            </div>
            
            <div class="info-row">
                <span class="label">Número de Documento:</span>
                <span class="value">{{ $persona->nro_documento }}</span>
            </div>
            
            <div class="info-row">
                <span class="label">Correo Electrónico:</span>
                <span class="value">{{ $persona->correo_electronico }}</span>
            </div>
            
            <div class="info-row">
                <span class="label">Teléfono:</span>
                <span class="value">{{ $persona->telefono }}</span>
            </div>
            
            <div class="info-row">
                <span class="label">Fecha de Registro:</span>
                <span class="value">{{ $persona->created_at->format('d/m/Y H:i:s') }}</span>
            </div>
            
            <div class="info-row">
                <span class="label">Notificaciones Email:</span>
                <span class="value">{{ $persona->notificacion_via_correo ? '✓ Activadas' : '✗ Desactivadas' }}</span>
            </div>
            
            <div class="info-row">
                <span class="label">Notificaciones SMS:</span>
                <span class="value">{{ $persona->notificacion_via_sms ? '✓ Activadas' : '✗ Desactivadas' }}</span>
            </div>
            
            <hr style="margin: 30px 0; border: none; border-top: 2px solid #eee;">
            
            <h3>¿Qué sigue?</h3>
            <p>Nos pondremos en contacto contigo pronto a través de los medios que seleccionaste.</p>
            
            <p>Si tienes alguna pregunta o necesitas modificar tu información, no dudes en contactarnos.</p>
            
            <p><strong>¡Gracias por confiar en nosotros!</strong></p>
        </div>
        
        <div class="footer">
            <p>Este email fue enviado automáticamente desde nuestro sistema de registro.</p>
            <p>Por favor no respondas directamente a este mensaje.</p>
            <p>&copy; {{ date('Y') }} Tu Empresa. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>