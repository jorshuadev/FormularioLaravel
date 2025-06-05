<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Registros</title>
    <style>
        table {
            border-collapse: collapse;
            width: 90%;
            margin: 20px auto;
            font-family: Arial, sans-serif;
        }
        th, td {
            border: 1px solid black;
            padding: 8px 12px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
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
        a.btn-back {
            display: block;
            width: 90px;
            margin: 20px auto;
            padding: 8px 10px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            text-align: center;
            border-radius: 4px;
        }
        a.btn-back:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

@if(session('success'))
    <div class="success-msg">
        {{ session('success') }}
    </div>
@endif

<table>
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>Tipo Documento</th>
            <th>Número Documento</th>
            <th>Correo</th>
            <th>Teléfono</th>
            <th>IP</th>
            <th>Zona Horaria</th>
            <th>Notificar por Correo</th>
            <th>Notificar por SMS</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($personas as $persona)
            <tr>
                <td>{{ $persona->nombre }}</td>
                <td>{{ $persona->apellido }}</td>
                <td>{{ ucfirst($persona->tipo_documento) }}</td>
                <td>{{ $persona->nro_documento }}</td>
                <td>{{ $persona->correo_electronico }}</td>
                <td>{{ $persona->telefono }}</td>
                <td>{{ $persona->ip }}</td>
                <td>{{ $persona->timezone }}</td>
                <td>{{ $persona->notificacion_via_correo ? 'Sí' : 'No' }}</td>
                <td>{{ $persona->notificacion_via_sms ? 'Sí' : 'No' }}</td>
            </tr>
        @empty
            <tr><td colspan="10">No hay registros aún.</td></tr>
        @endforelse
    </tbody>
</table>

<a href="{{ route('persona.create') }}" class="btn-back">Volver</a>

</body>
</html>
