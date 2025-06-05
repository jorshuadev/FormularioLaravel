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
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        .error-message {
            color: red;
            margin-top: 10px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-title">Registro de Persona</div>
        <form id="personaForm" action="{{ route('persona.store') }}" method="POST" onsubmit="return validarFormulario()">
            @csrf

            <label for="nombre">Nombre Completo:</label>
            <input type="text" id="nombre" name="nombre" required />

            <label for="tipo_documento">Tipo de documento:</label>
            <select id="tipo_documento" name="tipo_documento" required>
                <option value="" disabled selected>Seleccione...</option>
                <option value="cedula">Cédula</option>
                <option value="pasaporte">Pasaporte</option>
                <option value="otros">Otros</option>
            </select>

            <label for="numero_documento">Número de documento:</label>
            <input type="text" id="numero_documento" name="numero_documento" required />

            <label for="correo">Correo electrónico:</label>
            <input type="email" id="correo" name="correo" required />

            <label for="telefono">Teléfono:</label>
            <input type="text" id="telefono" name="telefono" required />

            <div class="checkbox-group">
                <label>
                    <input type="checkbox" name="notificar_correo" value="1" />
                    Notificar por correo
                </label>
                <label>
                    <input type="checkbox" name="notificar_sms" value="1" />
                    Notificar por SMS
                </label>
            </div>

            <input type="hidden" id="timezone" name="timezone" />
            <input type="hidden" name="registro_via" value="web" />

            <div class="error-message" id="errorMensaje"></div>

            <div style="display:block; align-items: center; text-align: center;">
                <button type="submit" class="btn">Registrar</button>
            </div>
        </form>

        <a href="{{ route('persona.registros') }}" class="btn btn-secondary">Ver registros</a>
    </div>

    <script>
        document.getElementById('timezone').value = Intl.DateTimeFormat().resolvedOptions().timeZone;

        function validarFormulario() {
            const correo = document.getElementById('correo').value.trim();
            const telefono = document.getElementById('telefono').value.trim();
            const numeroDocumentoInput = document.getElementById('numero_documento');
            const numeroDocumento = numeroDocumentoInput.value.trim().replace(/-/g, '');
            const errorMensaje = document.getElementById('errorMensaje');

            // Limpiar errores anteriores
            errorMensaje.textContent = '';

            // Reemplazar guiones automáticamente
            numeroDocumentoInput.value = numeroDocumento;

            // Validar correo
            const correoRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!correoRegex.test(correo)) {
                errorMensaje.textContent = 'Ingrese un correo válido.';
                return false;
            }

            // Validar teléfono (solo números, 8 dígitos, empieza con 6)
            const telefonoRegex = /^6\d{7}$/;
            if (!telefonoRegex.test(telefono)) {
                errorMensaje.textContent = 'El teléfono debe comenzar con 6 y tener 8 dígitos.';
                return false;
            }

            return true; // si todo está bien
        }
    </script>
</body>
</html>
