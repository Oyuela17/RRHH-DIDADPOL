<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class ControlAsistenciaController extends Controller
{
    private string $apiBase = 'http://localhost:3000/api/control-asistencia';
    private string $tz      = 'America/Tegucigalpa';

    /** Formatea "HH:MM:SS(.ffff)" a "hh:mm A" en la TZ local */
    private function fmt(?string $hora): ?string
    {
        if (!$hora) return null;
        $limpia = explode('.', $hora)[0]; // quita milisegundos si vienen
        return Carbon::parse($limpia, $this->tz)->format('h:i A');
    }

    public function index()
    {
        try {
            // Validar relación empleado
            if (!auth()->check() || !auth()->user()->empleado) {
                return view('asistencia.index')->with('error', 'Usuario sin empleado asignado.');
            }

            $cod = auth()->user()->empleado->cod_empleado;

            // === Registro de HOY (la API ya usa CURRENT_DATE) ===
            $hoy = Http::get("{$this->apiBase}/{$cod}/hoy")->json(); // puede venir null

            // Último punch a mostrar (si hay salida usar salida, si no la entrada)
            $ultimoPunch = '-';
            if ($hoy) {
                if (!empty($hoy['hora_salida'])) {
                    $ultimoPunch = $this->fmt($hoy['hora_salida']);
                } elseif (!empty($hoy['hora_entrada'])) {
                    $ultimoPunch = $this->fmt($hoy['hora_entrada']);
                }
            }

            // Acción del botón: si hay entrada sin salida => Salida; de lo contrario => Entrada
            $accion = (!empty($hoy) && !empty($hoy['hora_entrada']) && empty($hoy['hora_salida']))
                        ? 'Salida'
                        : 'Entrada';

            // === Estadísticas (API ya calcula con CURRENT_DATE/LOCALTIME) ===
            $estadisticas = Http::get("{$this->apiBase}/{$cod}/estadisticas")->json();

            // === Actividad de HOY en la vista (usamos el endpoint de hoy) ===
            $actividadHoy = $hoy ? [[
                'fecha'        => Carbon::now($this->tz)->toDateString(),
                'hora_entrada' => $this->fmt($hoy['hora_entrada'] ?? null),
                'hora_salida'  => $this->fmt($hoy['hora_salida']  ?? null),
                'observacion'  => $hoy['observacion'] ?? null,
            ]] : [];

            // === Historial completo ===
            $historial = Http::get("{$this->apiBase}/{$cod}")->json();

            return view('asistencia.index', compact(
                'accion',
                'ultimoPunch',
                'estadisticas',
                'actividadHoy',
                'historial'
            ));
        } catch (\Exception $e) {
            return view('asistencia.index')->with('error', 'Error al cargar la asistencia: ' . $e->getMessage());
        }
    }

    public function registrar(Request $request)
    {
        try {
            if (!auth()->check() || !auth()->user()->empleado) {
                return redirect()->route('asistencia.index')->with('error', 'Usuario sin empleado asignado.');
            }

            $cod = auth()->user()->empleado->cod_empleado;

            // Consultar el estado de HOY (la API define "hoy")
            $hoy = Http::get("{$this->apiBase}/{$cod}/hoy")->json();

            if (!$hoy || empty($hoy['hora_entrada'])) {
                // No hay registro hoy => ENTRADA
                $payload = [
                    'cod_empleado'  => $cod,
                    'tipo_registro' => 'Entrada',
                    'observacion'   => '',
                ];
            } elseif (empty($hoy['hora_salida'])) {
                // Hay entrada sin salida => SALIDA (calculamos observación)
                $entrada = Carbon::parse(explode('.', $hoy['hora_entrada'])[0], $this->tz);
                $salida  = Carbon::now($this->tz);
                $horas   = $entrada->diffInMinutes($salida) / 60;

                $observacion = $horas < 8
                    ? 'Horas incompletas'
                    : ($horas <= 8.1 ? 'Asistencia normal' : 'Horas extra');

                $payload = [
                    'cod_empleado'  => $cod,
                    'tipo_registro' => 'Salida',
                    'observacion'   => $observacion,
                ];
            } else {
                // Ya tiene entrada y salida hoy
                return redirect()->route('asistencia.index')->with('mensaje', 'Ya registraste entrada y salida hoy.');
            }

            // Enviar a API
            $response = Http::post($this->apiBase, $payload);

            if ($response->successful()) {
                return redirect()->route('asistencia.index')->with('mensaje', 'Registro guardado correctamente.');
            }

            $msg = $response->json('error') ?? 'Error al registrar asistencia.';
            return redirect()->route('asistencia.index')->with('error', $msg);

        } catch (\Exception $e) {
            return redirect()->route('asistencia.index')->with('error', 'Error en conexión con la API: ' . $e->getMessage());
        }
    }
}
