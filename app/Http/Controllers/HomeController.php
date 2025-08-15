<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class HomeController extends Controller
{
    // Base SOLO para el endpoint de estado-hoy (función 1)
    private string $apiAsistenciaBase = 'http://localhost:3000/api/asistencia';

    // Siguen vivas tus rutas de métricas existentes
    private string $apiUrlEmpleados = 'http://localhost:3000/api/empleados/total';
    private string $apiUrlUsuarios  = 'http://localhost:3000/api/usuarios/total';

    public function index()
    {
        $client = new Client();
        $tz = 'America/Tegucigalpa'; // tu zona

        // Valores por defecto (métricas)
        $totalEmpleados = ['total_empleados' => 0];
        $totalUsuarios  = ['usuarios_activos' => 0, 'usuarios_inactivos' => 0];

        // Mensajes por defecto (UI)
        $mensajeBienvenida = '¡Bienvenido!';
        $mensajeAsistencia = 'No has registrado entrada hoy.';
        $estadoAsistencia  = [
            'yaRegistroHoy' => false,     // true si ya hizo entrada y salida hoy
            'accion'        => 'Entrada', // Entrada | Salida | Completado
            'detalle'       => 'No has registrado entrada hoy.',
            'horaEntrada'   => null,
            'horaSalida'    => null,
        ];

        /* =========================
         *      MÉTRICAS
         * ========================= */
        try {
            $responseEmpleados = $client->get($this->apiUrlEmpleados);
            $totalEmpleados    = json_decode($responseEmpleados->getBody()->getContents(), true) ?: $totalEmpleados;

            $responseUsuarios  = $client->get($this->apiUrlUsuarios);
            $totalUsuarios     = json_decode($responseUsuarios->getBody()->getContents(), true) ?: $totalUsuarios;
        } catch (\Exception $e) {
            \Log::error('Error al obtener métricas: ' . $e->getMessage());
        }

        /* =========================
         *  SALUDO + ASISTENCIA HOY
         * ========================= */
        try {
            if (Auth::check() && Auth::user()->empleado) {
                // Encabezado/H2
                $nombre = strtoupper(Auth::user()->name ?? Auth::user()->empleado->nombres ?? 'USUARIO');
                $mensajeBienvenida = "¡Bienvenido, {$nombre}!";

                $cod = Auth::user()->empleado->cod_empleado;

                // === ÚNICA FUENTE: función 1 (status-hoy) ===
                $resp = $client->get("{$this->apiAsistenciaBase}/{$cod}/status-hoy");
                $statusHoy = json_decode($resp->getBody()->getContents(), true) ?: ['status' => 'sin-registro'];

                // Formateador de horas
                $fmt = function (?string $hora) use ($tz) {
                    if (!$hora) return null;
                    $limpia = explode('.', $hora)[0]; // por si vienen milisegundos
                    return Carbon::parse($limpia, $tz)->format('h:i A');
                };

                // Mapear estados a la tarjeta
                $estado = $statusHoy['status'] ?? 'sin-registro';
                $hIn    = $statusHoy['hora_entrada'] ?? null;
                $hOut   = $statusHoy['hora_salida']  ?? null;

                if ($estado === 'sin-registro') {
                    $estadoAsistencia['accion']        = 'Entrada';
                    $estadoAsistencia['detalle']       = 'No has registrado entrada hoy.';
                    $estadoAsistencia['yaRegistroHoy'] = false;
                    $estadoAsistencia['horaEntrada']   = null;
                    $estadoAsistencia['horaSalida']    = null;

                    $mensajeAsistencia = 'No has registrado entrada hoy.';
                } elseif ($estado === 'pendiente-salida') {
                    $estadoAsistencia['accion']        = 'Salida';
                    $estadoAsistencia['horaEntrada']   = $fmt($hIn);
                    $estadoAsistencia['horaSalida']    = null;
                    $estadoAsistencia['detalle']       = "Tienes una ENTRADA registrada a las {$estadoAsistencia['horaEntrada']}. Aún no registras la salida.";
                    $estadoAsistencia['yaRegistroHoy'] = false;

                    $mensajeAsistencia = 'Entrada registrada.';
                } elseif ($estado === 'completo') {
                    $estadoAsistencia['accion']        = 'Completado';
                    $estadoAsistencia['horaEntrada']   = $fmt($hIn);
                    $estadoAsistencia['horaSalida']    = $fmt($hOut);
                    $estadoAsistencia['detalle']       = "Hoy ya registraste ENTRADA ({$estadoAsistencia['horaEntrada']}) y SALIDA ({$estadoAsistencia['horaSalida']}).";
                    $estadoAsistencia['yaRegistroHoy'] = true;

                    $mensajeAsistencia = 'Asistencia del día completada.';
                } else {
                    // Cualquier valor no esperado lo tratamos como sin registro
                    $estadoAsistencia['accion']        = 'Entrada';
                    $estadoAsistencia['detalle']       = 'No has registrado entrada hoy.';
                    $estadoAsistencia['yaRegistroHoy'] = false;

                    $mensajeAsistencia = 'No has registrado entrada hoy.';
                }
            } else {
                // No autenticado o sin empleado
                $mensajeBienvenida = '¡Bienvenido!';
                $mensajeAsistencia = 'Inicia sesión o asigna un empleado para ver tu estado de asistencia.';
                $estadoAsistencia['detalle'] = $mensajeAsistencia;
            }
        } catch (\Exception $e) {
            \Log::error('Error al obtener asistencia de hoy: ' . $e->getMessage());
            // Mensaje neutro si la API falla
            $mensajeAsistencia = 'No fue posible consultar tu asistencia de hoy.';
            $estadoAsistencia['detalle'] = $mensajeAsistencia;
        }

        return view('home', [
            // Métricas
            'totalEmpleados'    => $totalEmpleados['total_empleados'] ?? 'Error al cargar',
            'usuariosActivos'   => $totalUsuarios['usuarios_activos'] ?? 'Error al cargar',
            'usuariosInactivos' => $totalUsuarios['usuarios_inactivos'] ?? 'Error al cargar',

            // Encabezado + Asistencia (tarjeta)
            'mensajeBienvenida' => $mensajeBienvenida,
            'mensajeAsistencia' => $mensajeAsistencia,
            'estadoAsistencia'  => $estadoAsistencia,
        ]);
    }
}
