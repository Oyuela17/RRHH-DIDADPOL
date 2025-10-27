<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;

use App\Http\Controllers\BackupsUiController;
use App\Http\Controllers\BitacoraController;
use App\Http\Controllers\CalendarioController;
use App\Http\Controllers\ControlAsistenciaAdminController;
use App\Http\Controllers\ControlAsistenciaController;
use App\Http\Controllers\DatosEmpresaController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\EstadoUsuarioController;
use App\Http\Controllers\NivelEducativoController;
use App\Http\Controllers\OficinaController;
use App\Http\Controllers\PermisosController;
use App\Http\Controllers\PersonaSController; // si tu clase es PersonasController corrige el use
use App\Http\Controllers\PuestoController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TipoEmpleadoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\UserRoleController;

/*
|--------------------------------------------------------------------------
| Rutas públicas
|--------------------------------------------------------------------------
*/

// Portada
Route::get('/', fn () => view('welcome'));

// Auth scaffold de Laravel
Auth::routes();

// Login/Logout personalizados
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// Recuperación de contraseña (vista)
Route::get('/password/reset', fn () => view('auth.passwords.reset'))->name('password.request');

// Rutas públicas adicionales (si realmente deben ser públicas)
Route::get('/definir-contrasena', fn () => view('usuarios.definir-contrasena'));
Route::get('/usuarios/crear', [UsuarioController::class, 'create'])->name('usuario.create');
Route::post('/usuarios',        [UsuarioController::class, 'store'])->name('usuario.store');

// Endpoints informativos (si no requieren auth)
Route::get('/api/empleados/total', [HomeController::class, 'obtenerTotalEmpleados']);
Route::get('/api/usuarios/total',  [HomeController::class, 'obtenerTotalUsuarios']);

/*
|--------------------------------------------------------------------------
| Rutas protegidas (requieren login) + auditoría (pg.audit)
|--------------------------------------------------------------------------
|
| IMPORTANTE: 'pg.audit' debe ejecutarse DESPUÉS de 'auth' para que exista Auth::id()
|
*/
Route::middleware(['auth', 'pg.audit'])->group(function () {

    // Dashboard
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // Bitácora (lectura)
    Route::get('/bitacora', [BitacoraController::class, 'index'])->name('bitacora.index');

    // Planilla
    Route::match(['get', 'post'], '/planilla', [App\Http\Controllers\PlanillaController::class, 'index'])
        ->name('planilla');

    // Calendario
    Route::get('/calendario',                 [CalendarioController::class, 'index'])->name('calendario.index');
    Route::get('/calendario/obtener-eventos', [CalendarioController::class, 'obtenerEventos']);
    Route::post('/calendario/guardar-evento', [CalendarioController::class, 'guardarEvento']);
    Route::put('/calendario/actualizar-evento/{id}', [CalendarioController::class, 'actualizarEvento']);
    Route::delete('/calendario/eliminar-evento/{id}', [CalendarioController::class, 'eliminarEvento']);

    // Control de asistencia (admin)
    Route::get('/control-asistencia/admin',        [ControlAsistenciaAdminController::class, 'index'])->name('control_asistencia.admin');
    Route::get('/control-asistencia/export/pdf',   [ControlAsistenciaAdminController::class, 'exportarPDF'])->name('asistencia.export.pdf');
    Route::get('/control-asistencia/export/excel', [ControlAsistenciaAdminController::class, 'exportarExcel'])->name('asistencia.export.excel');

    // Asistencia (usuario)
    Route::get('/asistencia',            [ControlAsistenciaController::class, 'index'])->name('asistencia.index');
    Route::post('/asistencia/punch',     [ControlAsistenciaController::class, 'registrar'])->name('asistencia.punch');

    // Backups (UI)
    Route::get('/backups',                 [BackupsUiController::class, 'index'])->name('backups.index');
    Route::post('/backups/run',            [BackupsUiController::class, 'run'])->name('backups.run');
    Route::get('/backups/{id}/download',   [BackupsUiController::class, 'download'])->name('backups.download');
    Route::delete('/backups/{id}',         [BackupsUiController::class, 'destroy'])->name('backups.destroy');
    Route::post('/backups/restore',        [BackupsUiController::class, 'restoreUpload'])->name('backups.restore');
    Route::post('/backups/{id}/restore',   [BackupsUiController::class, 'restoreUse'])->name('backups.restore.use');
    Route::post('/backups/schedule/save',  [BackupsUiController::class, 'saveSchedule'])->name('backups.schedule.save');
    Route::get('/backups/schedule/test',   [BackupsUiController::class, 'testSchedule'])->name('backups.schedule.test');

    // Roles
    Route::get('/roles',              [RoleController::class, 'index'])->name('roles.index');
    Route::get('/roles/create',       [RoleController::class, 'create'])->name('roles.create');
    Route::post('/roles',             [RoleController::class, 'store'])->name('roles.store');
    Route::get('/roles/{id}/edit',    [RoleController::class, 'edit'])->name('roles.edit');
    Route::put('/roles/{id}',         [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{id}',      [RoleController::class, 'destroy'])->name('roles.destroy');
    Route::put('/roles/{id}/estado',  [RoleController::class, 'actualizarEstado']);

    // Usuario ↔ Roles
    Route::get('/usuarios_roles',                     [UserRoleController::class, 'index'])->name('usuarios_roles.index');
    Route::post('/usuarios_roles/asignar/{id}',       [UserRoleController::class, 'asignar'])->name('usuarios_roles.asignar');
    Route::post('/verificar-estado',                  [EstadoUsuarioController::class, 'verificarEstado'])->name('usuarios.verificar_estado');

    // Permisos
    Route::get('/permisos',                [PermisosController::class, 'index'])->name('permisos.index');
    Route::get('/permisos/rol/{id}',       [PermisosController::class, 'ver'])->name('roles.permisosVista');

    // Empleados
    Route::get('/empleados',               [EmpleadoController::class, 'index'])->name('empleados.index');
    Route::post('/empleados',              [EmpleadoController::class, 'store'])->name('empleados.store');
    Route::put('/empleados/{id}',          [EmpleadoController::class, 'update'])->name('empleados.update');
    Route::delete('/empleados/{id}',       [EmpleadoController::class, 'destroy'])->name('empleados.destroy');

    // Horarios Laborales
    Route::get('/horarios',                [HorarioLaboralController::class, 'index'])->name('horarios.index');
    Route::post('/horarios',               [HorarioLaboralController::class, 'store'])->name('horarios.store');
    Route::put('/horarios/{id}',           [HorarioLaboralController::class, 'update'])->name('horarios.update');
    Route::delete('/horarios/{id}',        [HorarioLaboralController::class, 'destroy'])->name('horarios.destroy');

    // Oficinas
    Route::get('/oficinas',                [OficinaController::class, 'index'])->name('oficinas.index');
    Route::post('/oficinas',               [OficinaController::class, 'store'])->name('oficinas.store');
    Route::put('/oficinas/{id}',           [OficinaController::class, 'update'])->name('oficinas.update');
    Route::delete('/oficinas/{id}',        [OficinaController::class, 'destroy'])->name('oficinas.destroy');

    // Puestos
    Route::get('/puestos',                 [PuestoController::class, 'index'])->name('puestos.index');
    Route::post('/puestos',                [PuestoController::class, 'store'])->name('puestos.store');
    Route::put('/puestos/{id}',            [PuestoController::class, 'update'])->name('puestos.update');
    Route::delete('/puestos/{id}',         [PuestoController::class, 'destroy'])->name('puestos.destroy');

    // Niveles Educativos
    Route::get('/niveles-educativos',      [NivelEducativoController::class, 'index'])->name('niveles.index');
    Route::post('/niveles-educativos',     [NivelEducativoController::class, 'store'])->name('niveles.store');
    Route::put('/niveles-educativos/{id}', [NivelEducativoController::class, 'update'])->name('niveles.update');
    Route::delete('/niveles-educativos/{id}', [NivelEducativoController::class, 'destroy'])->name('niveles.destroy');

    // Datos de empresa
    Route::get('/datos-empresa',           [DatosEmpresaController::class, 'index'])->name('datos_empresa.index');
    Route::put('/datos-empresa/{id}',      [DatosEmpresaController::class, 'actualizar'])->name('datos_empresa.actualizar');

    // Personas
    Route::get('/personas',                [PersonasController::class, 'index'])->name('personas.index');

    // Tipos de empleados
    Route::get('/tipos',                   [TipoEmpleadoController::class, 'index'])->name('tipos.index');
    Route::post('/tipos',                  [TipoEmpleadoController::class, 'store'])->name('tipos.store');
    Route::put('/tipos/{id}',              [TipoEmpleadoController::class, 'update'])->name('tipos.update');
    Route::delete('/tipos/{id}',           [TipoEmpleadoController::class, 'destroy'])->name('tipos.destroy');
});
