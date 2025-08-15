<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <title>@yield('title') - DIDADPOL</title>

  @vite([
    'resources/css/app.css',
    'resources/css/dashboard.css',
    'resources/css/register.css',
    'resources/css/roles.css',
    'resources/css/home.css',
    'resources/css/planilla.css',
    'resources/css/vacaciones.css',
    'resources/css/calendario.css',
    'resources/css/backups.css',
    'resources/css/oficinas.css',
    'resources/css/permisos.css',
    'resources/css/tipos_empleados.css',
    'resources/css/titulos.css',
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

@yield('vendorjs')

</head>
<body>

<!-- Navbar superior -->
<nav class="navbar">
    <div class="navbar-left">
        <span class="navbar-brand">DIDADPOL</span>
    </div>

    <div class="navbar-right">
        <div class="user-menu">
            @php
                $nombreCompleto = Auth::user()->name ?? 'Usuario';
                $partes = explode(' ', $nombreCompleto);
                $primerNombre = $partes[0] ?? '';
                $primerApellido = $partes[1] ?? '';
            @endphp

            <div class="user-info-box">
                <span class="user-name">{{ $primerNombre }} {{ $primerApellido }}</span>
                <span class="user-role">{{ session('nombre_rol') ?? 'SIN ROL' }}</span>
            </div>
        </div>
    </div>
</nav>

<!-- Menú lateral y contenido -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

<div class="dashboard-container">
    <aside class="sidebar">
        <ul class="menu">
            <li><a href="{{ route('home') }}"><i class="fas fa-home"></i> Inicio</a></li>

            @if(in_array('CALENDARIO', $modulosPermitidos))
            <li><a href="{{ route('calendario') }}"><i class="fas fa-calendar"></i> Calendario</a></li>
            @endif

            @if(in_array('ASISTENCIA', $modulosPermitidos))
            <li><a href="{{ route('asistencia.index') }}"><i class="fas fa-user-check"></i> Asistencia</a></li>
            @endif

            @if(in_array('CONTROL DE ASISTENCIA', $modulosPermitidos))
            <li><a href="{{ route('control_asistencia.admin') }}"><i class="fas fa-calendar-check"></i> Control de Asistencia</a></li>
            @endif

            @if(in_array('RECURSOS HUMANOS', $modulosPermitidos))
            <li class="has-submenu">
                <input type="checkbox" id="submenu-rh" class="submenu-toggle" />
                <label for="submenu-rh"><i class="fas fa-users-cog"></i> Recursos Humanos</label>
                <ul class="submenu">
                    <li><a href="{{ route('datos_empresa.index') }}">Datos de la Empresa</a></li>
                    <li><a href="{{ route('personas.index') }}">Personas</a></li>
                     <li><a href="{{ route('vacaciones.index') }}">Vacaciones</a></li>

                </ul>
            </li>
            @endif

            @if(in_array('EMPLEADOS', $modulosPermitidos))
            <li class="has-submenu">
                <input type="checkbox" id="submenu-empleados" class="submenu-toggle" />
                <label for="submenu-empleados"><i class="fas fa-user-tie"></i> Empleados</label>
                <ul class="submenu">
                    <li><a href="{{ route('empleados.index') }}">Gestión Empleados</a></li>
                    <li><a href="{{ route('horarios.index') }}">Horarios Laborales</a></li>
                    <li><a href="{{ route('niveles.index') }}">Niveles Educativos</a></li>
                    <li><a href="{{ route('oficinas.index') }}">Oficinas</a></li>
                    <li><a href="{{ route('puestos.index') }}">Puestos</a></li>
                    <li><a href="{{ route('tipos.index') }}">Tipos de Empleado</a></li>
                    <li><a href="{{ route('titulos.index') }}">Títulos Académicos</a></li>
                </ul>
            </li>
            @endif

            @if(in_array('USUARIO', $modulosPermitidos))
            <li class="has-submenu">
                <input type="checkbox" id="submenu-usuario" class="submenu-toggle" />
                <label for="submenu-usuario"><i class="fas fa-users-cog"></i> Usuario</label>
                <ul class="submenu">
                    <li><a href="{{ route('usuario.create') }}">Registrar Usuario</a></li>
                    <li><a href="{{ route('roles.index') }}">Roles</a></li>
                    <li><a href="{{ route('usuarios_roles.index') }}">Usuarios Roles</a></li>
                    <li><a href="{{ route('permisos.index') }}">Permisos</a></li>
                </ul>
            </li>
            @endif

            @if(in_array('PLANILLA', $modulosPermitidos))
            <li class="has-submenu">
                <input type="checkbox" id="submenu-planilla" class="submenu-toggle" />
                <label for="submenu-planilla"><i class="fas fa-user-tie"></i> Planilla</label>
                <ul class="submenu">
                    <li><a href="{{ route('planilla') }}">Cálculo de Planilla</a></li>
                </ul>
            </li>
            @endif

             <li><a href="{{ route('backups.index') }}"><i class="fas fa-database"></i> Backup</a></li>

        </ul>

            


        <!-- Botón de cerrar sesión -->
        <form id="logout-form-sidebar" action="{{ route('logout') }}" method="POST" class="logout-form-sidebar">
            @csrf
            <button type="submit" class="logout-sidebar-button">
                <i class="fas fa-sign-out-alt"></i>
                Cerrar sesión
            </button>
        </form>
    </aside>

    <main class="main-content">
        @yield('content')
    </main>
</div>


<!-- Script de inactividad -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        let inactividadTimer;
        let cuentaRegresiva;
        let alertaActiva = false;

        function mostrarSweetAlertaInactividad() {
            let segundos = 3;
            alertaActiva = true;

            Swal.fire({
                title: 'Sesión finalizada por inactividad',
                html: `<p>Serás redirigido en <strong id="swal-contador">${segundos}</strong> segundos...</p>`,
                icon: 'warning',
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    const contador = Swal.getHtmlContainer().querySelector('#swal-contador');
                    cuentaRegresiva = setInterval(() => {
                        segundos--;
                        contador.textContent = segundos;
                        if (segundos <= 0) {
                            clearInterval(cuentaRegresiva);
                            window.location.href = "{{ route('login') }}?inactivo=1";
                        }
                    }, 1000);
                }
            });
        }

        function reiniciarInactividad() {
            if (alertaActiva) return;
            clearTimeout(inactividadTimer);
            clearInterval(cuentaRegresiva);
            inactividadTimer = setTimeout(mostrarSweetAlertaInactividad, 900000); // 15 minutos
        }

        reiniciarInactividad();
        document.addEventListener('mousemove', reiniciarInactividad);
        document.addEventListener('keypress', reiniciarInactividad);
    });
</script>

<!-- Script para verificar estado de usuario y rol en tiempo real -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        setInterval(() => {
            fetch('/verificar-estado', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.estado !== 'ACTIVO' || data.estado_rol !== 'ACTIVO') {
                    let mensaje = data.estado !== 'ACTIVO'
                        ? 'Tu cuenta fue desactivada por el administrador.'
                        : 'Tu rol ha sido desactivado. Contacta al administrador.';

                    Swal.fire({
                        icon: 'warning',
                        title: 'Sesión cerrada',
                        text: mensaje,
                        allowOutsideClick: false,
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '{{ route("logout") }}';
                        form.style.display = 'none';

                        const csrf = document.createElement('input');
                        csrf.type = 'hidden';
                        csrf.name = '_token';
                        csrf.value = '{{ csrf_token() }}';
                        form.appendChild(csrf);

                        document.body.appendChild(form);
                        form.submit();
                    });
                }
            });
        }, 10000); // cada 10 segundos
    });
</script>

@yield('scripts')
</body>
</html>
