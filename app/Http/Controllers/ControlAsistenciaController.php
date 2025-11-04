<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class ControlAsistenciaController extends Controller
{
    private string $apiBase = 'https://rrhh-didadpol-1.onrender.com/api/control-asistencia';
    private string $tz      = 'America/Tegucigalpa';

    /** Formatea "HH:MM:SS(.ffff)" a "hh:mm A" en la TZ local */
    private function fmt(?string $hora): ?string
    {
        if (!$hora) return null;
        $limpia = explode('.', $hora)[0]; // quita milisegundos si vienen
        return Carbon::parse($limpia, $this->tz)->format('h:i A');
    }

    public function index(Request $request)
    {
        try {
            if (!auth()->check() || !auth()->user()->empleado) {
                return view('asistencia.index')->with('error', 'Usuario sin empleado asignado.');
            }

            $cod = auth()->user()->empleado->cod_empleado;

            // === Registro HOY ===
            $hoy = Http::get("{$this->apiBase}/{$cod}/hoy")->json();

            $ultimoPunch = '-';
            if ($hoy) {
                if (!empty($hoy['hora_salida'])) {
                    $ultimoPunch = $this->fmt($hoy['hora_salida']);
                } elseif (!empty($hoy['hora_entrada'])) {
                    $ultimoPunch = $this->fmt($hoy['hora_entrada']);
                }
            }

            $accion = (!empty($hoy) && !empty($hoy['hora_entrada']) && empty($hoy['hora_salida']))
                        ? 'Salida'
                        : 'Entrada';

            // === Estadísticas ===
            $estadisticas = Http::get("{$this->apiBase}/{$cod}/estadisticas")->json();

            // === Actividad de HOY para la vista ===
            $actividadHoy = $hoy ? [[
                'fecha'        => Carbon::now($this->tz)->toDateString(),
                'hora_entrada' => $this->fmt($hoy['hora_entrada'] ?? null),
                'hora_salida'  => $this->fmt($hoy['hora_salida']  ?? null),
                'observacion'  => $hoy['observacion'] ?? null,
            ]] : [];

            // ==========================================================
            // === Historial paginado (desde array => LengthAwarePaginator)
            // ==========================================================
            $perPage = (int) $request->input('per_page', 10);
            $page    = (int) $request->input('page', 1);

            // Si tu API soporta paginación, puedes descomentar y usar:
            // $apiResp   = Http::get("{$this->apiBase}/{$cod}", ['page'=>$page,'per_page'=>$perPage])->json();
            // $items     = $apiResp['data'] ?? [];
            // $total     = $apiResp['total'] ?? count($items);

            // Ahora: paginar localmente el array completo de la API
            $historialArray = Http::get("{$this->apiBase}/{$cod}")->json() ?? [];
            $total          = count($historialArray);

            // Ordenar por fecha desc si la API no lo hace
            usort($historialArray, function($a, $b){
                return strcmp($b['fecha'] ?? '', $a['fecha'] ?? '');
            });

            $offset   = max(0, ($page - 1) * $perPage);
            $slice    = array_slice($historialArray, $offset, $perPage);

            $historial = new LengthAwarePaginator(
                $slice,
                $total,
                $perPage,
                $page,
                [
                    'path'  => url()->current(),
                    'query' => $request->query(), // conserva ?per_page etc.
                ]
            );

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

            $hoy = Http::get("{$this->apiBase}/{$cod}/hoy")->json();

            if (!$hoy || empty($hoy['hora_entrada'])) {
                $payload = [
                    'cod_empleado'  => $cod,
                    'tipo_registro' => 'Entrada',
                    'observacion'   => '',
                ];
            } elseif (empty($hoy['hora_salida'])) {
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
                return redirect()->route('asistencia.index')->with('mensaje', 'Ya registraste entrada y salida hoy.');
            }

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
