<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Registro de Persona</title>
    <style>
        .form-container {
            border: 2px solid black;
            width: 400px;
            padding: 20px;
            margin: 40px auto;
            position: relative;
            font-family: Arial, sans-serif;
        }
        .form-title {
            position: absolute;
            top: -14px;
            left: 50%;
            transform: translateX(-50%);
            background-color: white;
            padding: 0 10px;
            font-weight: bold;
            font-size: 1.2rem;
        }
        label {
            display: block;
            margin-top: 15px;
            font-weight: 600;
        }
        input[type="text"],
        input[type="email"],
        select {
            width: 100%;
            padding: 6px;
            margin-top: 5px;
            box-sizing: border-box;
        }
        
        /* 🔥 ESTILOS PARA CAMPOS CON ERROR */
        .error-field {
            border: 2px solid #dc3545 !important;
            background-color: #f8d7da;
        }
        
        .checkbox-group {
            margin-top: 15px;
        }
        .checkbox-group label {
            display: inline-block;
            margin-right: 20px;
            font-weight: normal;
        }
        .btn {
            margin-top: 25px;
            padding: 10px 20px;
            background-color: #ebebeb;
            color: rgb(37, 37, 37);
            border: none;
            cursor: pointer;
            font-size: 1rem;
            border-radius: 4px;
            border: 1px solid black;
        }
        .btn:hover {
            background-color: #7a7a7a;
        }
        .btn-secondary {
            margin-top: 15px;
            display: block;
            background-color: #6c757d;
            text-align: center;
            text-decoration: none;
            border-radius: 4px;
            color: aliceblue;
            padding: 10px 20px;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        
        /* 🔥 ESTILOS PARA MENSAJES DE ERROR */
        .error-message {
            color: #dc3545;
            margin-top: 10px;
            font-size: 0.9rem;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 4px;
            display: none;
        }
        
        .error-message.show {
            display: block;
        }
        
        .field-error {
            color: #dc3545;
            font-size: 0.8rem;
            margin-top: 2px;
            display: block;
        }
        
        .success-msg {
            width: 90%;
            margin: 10px auto;
            padding: 10px;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            font-weight: bold;
            border-radius: 4px;
            text-align: center;
        }
        
        /* 🔥 MENSAJE DE ERROR GENERAL */
        .error-msg {
            width: 90%;
            margin: 10px auto;
            padding: 10px;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            font-weight: bold;
            border-radius: 4px;
            text-align: center;
        }

        /* 🔥 PLACEHOLDER PERSONALIZADO */
        input::placeholder {
            color: #999;
            font-style: italic;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    
{{-- 🔥 MENSAJE DE ÉXITO --}}
@if(session('success'))
    <div class="success-msg">
        {{ session('success') }}
    </div>
@endif

{{-- 🔥 MENSAJE DE ERROR GENERAL --}}
@if(session('error'))
    <div class="error-msg">
        {{ session('error') }}
    </div>
@endif

{{-- 🔥 MOSTRAR ERRORES DE VALIDACIÓN --}}
@if($errors->any())
    <div class="error-msg">
        <strong>Se encontraron los siguientes errores:</strong>
        <ul style="margin: 10px 0 0 0; padding-left: 20px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

    <div class="form-container">
        <div class="form-title">Registro de Persona</div>
        <form id="personaForm" action="{{ route('persona.store') }}" method="POST" onsubmit="return validarFormulario()">
            @csrf

            <label for="nombre">Nombre Completo:</label>
            <input type="text" 
                   id="nombre" 
                   name="nombre" 
                   value="{{ old('nombre') }}"
                   placeholder="Ej: Juan Pérez González"
                   class="{{ $errors->has('nombre') ? 'error-field' : '' }}"
                   required />
            @if($errors->has('nombre'))
                <span class="field-error">{{ $errors->first('nombre') }}</span>
            @endif

            <label for="tipo_documento">Tipo de documento:</label>
            <select id="tipo_documento" 
                    name="tipo_documento" 
                    class="{{ $errors->has('tipo_documento') ? 'error-field' : '' }}"
                    required>
                <option value="" disabled {{ old('tipo_documento') ? '' : 'selected' }}>Seleccione...</option>
                <option value="cedula" {{ old('tipo_documento') == 'cedula' ? 'selected' : '' }}>Cédula</option>
                <option value="pasaporte" {{ old('tipo_documento') == 'pasaporte' ? 'selected' : '' }}>Pasaporte</option>
                <option value="otros" {{ old('tipo_documento') == 'otros' ? 'selected' : '' }}>Otros</option>
            </select>
            @if($errors->has('tipo_documento'))
                <span class="field-error">{{ $errors->first('tipo_documento') }}</span>
            @endif

            <label for="numero_documento">Número de documento:</label>
            <input type="text" 
                   id="numero_documento" 
                   name="numero_documento" 
                   value="{{ old('numero_documento') }}"
                   placeholder="Cédula: 8-123-4567 o 8-1026-2297 | Pasaporte: AB123456"
                   class="{{ $errors->has('numero_documento') ? 'error-field' : '' }}"
                   required />
            @if($errors->has('numero_documento'))
                <span class="field-error">{{ $errors->first('numero_documento') }}</span>
            @endif

            <label for="correo">Correo electrónico:</label>
            <input type="email" 
                   id="correo" 
                   name="correo" 
                   value="{{ old('correo') }}"
                   placeholder="ejemplo@correo.com"
                   class="{{ $errors->has('correo') ? 'error-field' : '' }}"
                   required />
            @if($errors->has('correo'))
                <span class="field-error">{{ $errors->first('correo') }}</span>
            @endif

            <label for="telefono">Teléfono:</label>
            <input type="text" 
                   id="telefono" 
                   name="telefono" 
                   value="{{ old('telefono') }}"
                   placeholder="Ej: 64848240, 2345-6789, 6123-4567"
                   class="{{ $errors->has('telefono') ? 'error-field' : '' }}"
                   required />
            @if($errors->has('telefono'))
                <span class="field-error">{{ $errors->first('telefono') }}</span>
            @endif

            <div class="checkbox-group">
                <label>
                    <input type="checkbox" 
                           name="notificar_correo" 
                           value="1" 
                           {{ old('notificar_correo') ? 'checked' : '' }} />
                    Notificar por correo
                </label>
                <label>
                    <input type="checkbox" 
                           name="notificar_sms" 
                           value="1" 
                           {{ old('notificar_sms') ? 'checked' : '' }} />
                    Notificar por SMS
                </label>
            </div>

            <input type="hidden" id="timezone" name="timezone" value="{{ old('timezone') }}" />
            <input type="hidden" name="registro_via" value="web" />

            <div class="error-message" id="errorMensaje"></div>

            <div style="display:block; align-items: center; text-align: center;">
                <button type="submit" class="btn">Registrar</button>
            </div>
        </form>

        {{-- 🔥 BOTÓN PARA VER REGISTROS --}}
        <div style="text-align: center;">
            <a href="{{ route('persona.registros') }}" class="btn btn-secondary">Ver registros</a>
        </div>
    </div>

    <script>
        // Detectar zona horaria automáticamente
        document.getElementById('timezone').value = Intl.DateTimeFormat().resolvedOptions().timeZone;

        // 🔥 ACTUALIZAR PLACEHOLDER DINÁMICAMENTE SEGÚN TIPO DE DOCUMENTO
        document.addEventListener('DOMContentLoaded', function() {
            const tipoDocumentoSelect = document.getElementById('tipo_documento');
            const numeroDocumentoInput = document.getElementById('numero_documento');
            
            // Función para actualizar placeholder
            function actualizarPlaceholder() {
                const tipoSeleccionado = tipoDocumentoSelect.value;
                
                switch(tipoSeleccionado) {
                    case 'cedula':
                        numeroDocumentoInput.placeholder = 'Ej: 8-123-4567, 8-1026-2297, 10-123-4567';
                        break;
                    case 'pasaporte':
                        numeroDocumentoInput.placeholder = 'Ej: AB123456, CD789012, XY987654';
                        break;
                    case 'otros':
                        numeroDocumentoInput.placeholder = 'Ej: DUI-12345, NIE-X1234567, ID-ABC123';
                        break;
                    default:
                        numeroDocumentoInput.placeholder = 'Cédula: 8-123-4567 | Pasaporte: AB123456 | Otros: DUI-12345';
                }
            }
            
            // Actualizar al cambiar tipo de documento
            tipoDocumentoSelect.addEventListener('change', actualizarPlaceholder);
            
            // Actualizar al cargar la página si hay valor previo
            if (tipoDocumentoSelect.value) {
                actualizarPlaceholder();
            }
        });

        function validarFormulario() {
            const correo = document.getElementById('correo').value.trim();
            const telefono = document.getElementById('telefono').value.trim();
            const numeroDocumentoInput = document.getElementById('numero_documento');
            const numeroDocumento = numeroDocumentoInput.value.trim();
            const errorMensaje = document.getElementById('errorMensaje');

            // Limpiar errores anteriores
            errorMensaje.textContent = '';
            errorMensaje.classList.remove('show');

            // Validar correo
            const correoRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!correoRegex.test(correo)) {
                errorMensaje.textContent = 'Ingrese un correo válido.';
                errorMensaje.classList.add('show');
                return false;
            }

            // 🔥 ACTUALIZADO: Validar teléfono panameño (7-8 dígitos, empieza con 2-9)
            const telefonoLimpio = telefono.replace(/[\s\-$$$$]/g, '');
            const telefonoRegex = /^[2-9]\d{6,7}$/;
            if (!telefonoRegex.test(telefonoLimpio)) {
                errorMensaje.textContent = 'El teléfono debe comenzar con 2-9 y tener 7-8 dígitos (ej: 64848240, 2345-6789).';
                errorMensaje.classList.add('show');
                return false;
            }

            return true; // si todo está bien
        }

        // 🔥 LIMPIAR ESTILOS DE ERROR AL ESCRIBIR
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('input, select');
            
            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    // Remover clase de error al escribir
                    this.classList.remove('error-field');
                    
                    // Ocultar mensaje de error del campo
                    const errorSpan = this.parentNode.querySelector('.field-error');
                    if (errorSpan) {
                        errorSpan.style.display = 'none';
                    }
                });
            });

            // 🔥 FORMATEO AUTOMÁTICO SOLO PARA TELÉFONO (NO PARA DOCUMENTO)
            const telefonoInput = document.getElementById('telefono');
            telefonoInput.addEventListener('input', function() {
                let valor = this.value.replace(/[^\d]/g, ''); // Solo números
                
                // Formatear automáticamente
                if (valor.length === 8) {
                    if (valor.startsWith('6')) {
                        // Móvil: 6484-8240
                        this.value = valor.substring(0, 4) + '-' + valor.substring(4);
                    } else if (valor.startsWith('2') || valor.startsWith('3') || valor.startsWith('4') || valor.startsWith('5') || valor.startsWith('7') || valor.startsWith('8') || valor.startsWith('9')) {
                        // Fijo: 2345-6789
                        this.value = valor.substring(0, 4) + '-' + valor.substring(4);
                    }
                } else if (valor.length === 7) {
                    // 7 dígitos: 234-5678
                    this.value = valor.substring(0, 3) + '-' + valor.substring(3);
                } else {
                    this.value = valor;
                }
            });

            // 🔥 REMOVIDO: Ya no hay formateo automático para número de documento
            // El usuario puede escribir libremente: 8-123-4567, 81234567, 8 123 4567, etc.
            // El sistema limpiará y validará en el backend
        });
    </script>
</body>
</html>
