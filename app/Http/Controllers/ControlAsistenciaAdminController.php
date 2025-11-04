<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AsistenciaExport;

class ControlAsistenciaAdminController extends Controller
{
    /** Endpoints de tu API Node (ajusta si cambian) */
    private string $apiVistaMes   = 'https://rrhh-didadpol-1.onrender.com/api/control-asistencia/mes';
    private string $apiPDF        = 'https://rrhh-didadpol-1.onrender.com/api/control-asistencia/pdf';
    private string $apiAdminBase  = 'https://rrhh-didadpol-1.onrender.com/api/control-asistencia/admin'; // PUT /manual, PATCH /{id}

    /** Zona horaria para cálculos locales */
    private string $tz = 'America/Tegucigalpa';

    /**
     * Vista admin (mes por defecto). El front ya sabe “filtrar” visualmente por semana.
     */
    public function index(Request $request)
    {
        $mes     = (int) $request->input('mes', now()->month);
        $anio    = (int) $request->input('anio', now()->year);
        $nombre  = $request->input('nombre');
        $vista   = $request->input('vista', 'mes');     // mes | semana
        $semana  = $request->input('semana');           // ISO: YYYY-Www

        try {
            $response = Http::timeout(15)->acceptJson()->get($this->apiVistaMes, [
                'mes'  => $mes,
                'anio' => $anio,
            ]);

            if (!$response->ok()) {
                return back()->with('error', 'Error al obtener datos del servidor.');
            }

            $data      = $response->json();
            $dias      = (int) ($data['dias'] ?? 0);
            $empleados = $data['empleados'] ?? [];

            if ($nombre) {
                $empleados = collect($empleados)->filter(
                    fn ($e) => str_contains(mb_strtolower($e['nombre']), mb_strtolower($nombre))
                )->values()->all();
            }

            return view('asistencia.admin', [
                'dias'      => $dias,
                'empleados' => $empleados,
                'mes'       => $mes,
                'anio'      => $anio,
                'vista'     => $vista,
                'semana'    => $semana,
            ]);
        } catch (\Throwable $e) {
            Log::error('index asistencia admin', ['ex' => $e->getMessage()]);
            return back()->with('error', 'Error de conexión con el servidor.');
        }
    }

    /**
     * Exportar PDF. Si viene vista=semana y semana=YYYY-Www, recorta días/registros.
     */
    public function exportarPDF(Request $request)
    {
        $mes     = (int) $request->input('mes', now()->month);
        $anio    = (int) $request->input('anio', now()->year);
        $nombre  = $request->input('nombre');
        $vista   = $request->input('vista', 'mes');
        $semana  = $request->input('semana');

        try {
            $response = Http::timeout(20)->acceptJson()->get($this->apiPDF, [
                'mes'  => $mes,
                'anio' => $anio,
            ]);

            if (!$response->ok()) {
                return back()->with('error', 'Error al generar PDF.');
            }

            $data      = $response->json();
            $diasMes   = (int) ($data['dias'] ?? 0);
            $empleados = collect($data['empleados'] ?? []);

            if ($nombre) {
                $empleados = $empleados->filter(
                    fn ($e) => str_contains(mb_strtolower($e['nombre']), mb_strtolower($nombre))
                )->values();
            }

            [$dias, $empleadosFiltrados] = $this->aplicarFiltroSemana(
                $vista, $semana, $anio, $mes, $diasMes, $empleados
            );

            $pdf = PDF::loadView('asistencia.reporte_pdf', [
                'dias'      => $dias,                  // int (mes completo) o array de días si semana
                'empleados' => $empleadosFiltrados,   // colección filtrada si semana
                'mes'       => $mes,
                'anio'      => $anio,
                'vista'     => $vista,
                'semana'    => $semana,
                'tz'        => $this->tz,
            ])->setPaper('a4', 'landscape');

            $suf = ($vista === 'semana' && $semana) ? "-{$semana}" : '';
            return $pdf->stream("Asistencia-{$mes}-{$anio}{$suf}.pdf");
        } catch (\Throwable $e) {
            Log::error('exportarPDF asistencia', ['ex' => $e->getMessage()]);
            return back()->with('error', 'No se pudo generar el PDF.');
        }
    }

    /**
     * Exportar Excel (por ahora mes completo; amplía AsistenciaExport si deseas semana).
     */
    public function exportarExcel(Request $request)
    {
        $mes     = (int) $request->input('mes', now()->month);
        $anio    = (int) $request->input('anio', now()->year);
        $nombre  = $request->input('nombre');
        $vista   = $request->input('vista', 'mes');
        $semana  = $request->input('semana');

        $suf = ($vista === 'semana' && $semana) ? "-{$semana}" : '';

        return Excel::download(
            new AsistenciaExport($mes, $anio, $nombre),
            "Asistencia-{$mes}-{$anio}{$suf}.xlsx"
        );
    }

    /* =======================
       CARGA / AJUSTE MANUAL
       ======================= */

    /**
     * UPSERT manual mediante PUT (crea o actualiza por cod_empleado+fecha).
     * Requiere en la API: PUT {$apiAdminBase}/manual
     */
    public function manualUpsert(Request $request)
    {
        $data = $request->validate([
            'cod_empleado'  => 'required|integer',
            'fecha'         => 'required|date_format:Y-m-d',
            'hora_entrada'  => 'nullable|date_format:H:i',
            'hora_salida'   => 'nullable|date_format:H:i',
            'observacion'   => 'nullable|string|max:200',
        ], [
            'fecha.date_format'        => 'La fecha debe ser YYYY-MM-DD.',
            'hora_entrada.date_format' => 'La hora de entrada debe ser HH:MM.',
            'hora_salida.date_format'  => 'La hora de salida debe ser HH:MM.',
        ]);

        $entrada = $data['hora_entrada'] ?? null;
        $salida  = $data['hora_salida']  ?? null;

        // Coherencia previa
        if ($salida && !$entrada) {
            return back()->with('error', 'Para definir una salida, primero indique la hora de entrada.')->withInput();
        }
        if ($entrada && $salida && $salida < $entrada) {
            return back()->with('error', 'La hora de salida no puede ser menor que la de entrada.')->withInput();
        }

        // Observación automática si procede
        if ($entrada && $salida && empty($data['observacion'])) {
            $data['observacion'] = $this->calcularObservacion($entrada, $salida);
        }

        // Origen fijo: manual
        $payload = array_merge($data, ['origen' => 'manual']);

        try {
            $res = Http::timeout(15)->acceptJson()->put("{$this->apiAdminBase}/manual", $payload);

            if ($res->successful()) {
                return back()->with('mensaje', 'Asistencia manual guardada correctamente.');
            }

            $msg = $res->json('error') ?? $res->body() ?? 'No se pudo registrar la asistencia manual.';
            Log::warning('API manualUpsert no OK', [
                'status'  => $res->status(),
                'body'    => $res->body(),
                'payload' => $payload
            ]);
            return back()->with('error', $msg)->withInput();
        } catch (\Throwable $e) {
            Log::error('manualUpsert exception', ['ex' => $e->getMessage()]);
            return back()->with('error', 'Error de conexión con la API (manualUpsert).')->withInput();
        }
    }

    /**
     * (Opcional) Actualizar por ID con PATCH si sigues usando edición directa.
     * Requiere en la API: PATCH {$apiAdminBase}/{id}
     */
    public function manualUpdate($id, Request $request)
    {
        $data = $request->validate([
            'hora_entrada' => 'nullable|date_format:H:i',
            'hora_salida'  => 'nullable|date_format:H:i',
            'observacion'  => 'nullable|string|max:200',
        ]);

        $entrada = $data['hora_entrada'] ?? null;
        $salida  = $data['hora_salida']  ?? null;

        if ($salida && !$entrada) {
            return back()->with('error', 'Para definir una salida, primero indique la hora de entrada.')->withInput();
        }
        if ($entrada && $salida && $salida < $entrada) {
            return back()->with('error', 'La hora de salida no puede ser menor que la de entrada.')->withInput();
        }

        if ($entrada && $salida && empty($data['observacion'])) {
            $data['observacion'] = $this->calcularObservacion($entrada, $salida);
        }

        $payload = array_merge($data, ['origen' => 'manual']);

        try {
            $res = Http::timeout(15)->acceptJson()->patch("{$this->apiAdminBase}/{$id}", $payload);

            if ($res->successful()) {
                return back()->with('mensaje', 'Asistencia actualizada.');
            }

            $msg = $res->json('error') ?? $res->body() ?? 'No se pudo actualizar la asistencia.';
            Log::warning('API manualUpdate no OK', [
                'status'  => $res->status(),
                'body'    => $res->body(),
                'payload' => $payload
            ]);
            return back()->with('error', $msg)->withInput();
        } catch (\Throwable $e) {
            Log::error('manualUpdate exception', ['ex' => $e->getMessage()]);
            return back()->with('error', 'Error de conexión con la API (manualUpdate).')->withInput();
        }
    }

    /* =======================
       Helpers internos
       ======================= */

    /** Observación según horas trabajadas (umbrales estándar 8h) */
    private function calcularObservacion(string $entrada, string $salida): string
    {
        [$eh, $em] = array_map('intval', explode(':', $entrada));
        [$sh, $sm] = array_map('intval', explode(':', $salida));
        $horas = ($sh + $sm / 60) - ($eh + $em / 60);

        if ($horas < 8 - 1e-9)  return 'Horas incompletas';
        if ($horas > 8 + 0.1)   return 'Horas extra'; // tolerancia ~6 min
        return 'Asistencia normal';
    }

    /**
     * Si vista=semana y viene semana=YYYY-Www:
     *  - retorna [$diasSemana, $empleadosFiltrados]
     * Caso contrario, [$diasMes, $empleados] intactos.
     */
    private function aplicarFiltroSemana(string $vista, ?string $semana, int $anio, int $mes, int $diasMes, $empleados)
    {
        if ($vista !== 'semana' || empty($semana)) {
            return [$diasMes, $empleados];
        }

        [$desdeISO, $hastaISO] = $this->rangoSemana($semana);

        // Días del mes dentro del rango ISO
        $dias = [];
        for ($d = 1; $d <= $diasMes; $d++) {
            $f = Carbon::create($anio, $mes, $d, 0, 0, 0, $this->tz)->toDateString();
            if ($f >= $desdeISO && $f <= $hastaISO) {
                $dias[] = $d;
            }
        }

        // Filtrar registros por fecha
        $emps = collect($empleados)->map(function ($e) use ($desdeISO, $hastaISO) {
            $e['registros'] = collect($e['registros'] ?? [])
                ->filter(fn ($r) => !empty($r['fecha']) && $r['fecha'] >= $desdeISO && $r['fecha'] <= $hastaISO)
                ->values()->all();
            return $e;
        });

        return [$dias, $emps];
    }

    /**
     * A partir de "YYYY-Www", retorna [lunesISO, domingoISO] en formato Y-m-d.
     */
    private function rangoSemana(string $isoWeek): array
    {
        [$yStr, $wStr] = explode('-W', $isoWeek);
        $y = (int) $yStr;
        $w = (int) $wStr;

        // Usamos DateTimeImmutable con ISO-8601
        $ref    = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $jueves = $ref->setISODate($y, $w);                 // día dentro de esa semana
        $lunes  = $jueves->modify('monday this week')->format('Y-m-d');
        $domingo= $jueves->modify('sunday this week')->format('Y-m-d');

        return [$lunes, $domingo];
    }
}
