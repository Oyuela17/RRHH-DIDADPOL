<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Autenticación')</title>
    
    {{-- Fuente y diseño limpio --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

   {{-- Estilos compilados con Vite --}}
@vite([
    'resources/css/app.css',
    'resources/css/login.css',               
    'resources/css/register.css',
    'resources/css/roles.css',
    'resources/css/oficinas.css',
    'resources/css/tipos_empleados.css',
    'resources/css/titulos.css',
    'resources/css/vacaciones.css',
    'resources/css/permisos.css',
    'resources/css/puestos.css',
    'resources/css/personas.css',
    'resources/css/asistencia.css',
    'resources/css/asistencia_admin.css',
    'resources/css/datos_empresa.css',
    'resources/css/niveles_educativos.css',
    'resources/css/usuarioroles.css',
    'resources/css/horarios_laborales.css',
    'resources/css/empleado.css',
    'resources/js/app.js'
])

    <style>
        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
            background-color: #ffffff;
            height: 100%;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .auth-container {
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        @yield('content')
    </div>
</body>
</html>
