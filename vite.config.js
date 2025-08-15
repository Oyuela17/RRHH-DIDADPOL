import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/dashboard.css',
                'resources/css/register.css',
                'resources/css/roles.css',
                'resources/css/oficinas.css',
                'resources/css/vacaciones.css',
                'resources/css/planilla.css',
                'resources/css/permisos.css',
                'resources/css/backups.css',
                'resources/css/puestos.css',
                'resources/css/calendario.css',
                'resources/css/tipos_empleados.css',
                'resources/css/titulos.css',
                'resources/css/personas.css',
                'resources/css/asistencia.css',
                'resources/css/asistencia_admin.css',
                'resources/css/datos_empresa.css',
                'resources/css/niveles_educativos.css',
                'resources/css/usuarioroles.css',
                'resources/css/horarios_laborales.css',
                'resources/css/empleado.css',
                'resources/css/login.css',
                'resources/css/home.css',
                'resources/css/app.css',
                'resources/js/app.js'
            ],
            refresh: true,
        }),
    ],
    build: {
        outDir: 'public/build',
    },
});
