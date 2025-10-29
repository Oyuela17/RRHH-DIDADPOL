<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Carbon\Carbon;

// 👉 Exportaciones
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

// ✅ NUEVO para Excel por Blade
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ReportesController extends Controller
{
    /**
     * Base de la API Node (pon en .env: API_RRHH_BASE=https://rrhh-didadpol-1.onrender.com/api)
     */
    private string $apiBase;

    public function __construct()
    {
        $this->apiBase = rtrim(env('API_RRHH_BASE', 'https://rrhh-didadpol-1.onrender.com/api'), '/');
    }

    /**
     * Vista con pestañas (Empleados / Asistencia).
     */
    public function index()
    {
        return view('reportes.index');
    }

    /**
     * Reporte General de Empleados (proxy + filtros/orden/paginación sobre la tabla)
     * GET /reportes/empleados/general
     */
    public function empleadosGeneral(Request $request)
    {
        try {
            $url = "{$this->apiBase}/reportes/empleados/general";
            $resp = Http::timeout(25)->acceptJson()->get($url);

            if ($resp->failed()) {
                return $this->apiFail('Empleados', $resp);
            }

            $data = $resp->json();

            // ---------- Filtros y orden sobre la tabla ----------
            $tabla = collect($data['tabla'] ?? []);

            $busqueda = trim((string)$request->query('busqueda', ''));
            $oficina  = trim((string)$request->query('oficina', ''));
            $modalidad= trim((string)$request->query('modalidad', ''));
            $nivel    = trim((string)$request->query('nivel', ''));
            $puesto   = trim((string)$request->query('puesto', ''));

            if ($busqueda !== '') {
                $tabla = $tabla->filter(function ($row) use ($busqueda) {
                    return stripos($row['nombre_completo'] ?? '', $busqueda) !== false
                        || stripos($row['dni'] ?? '', $busqueda) !== false;
                });
            }
            if ($oficina !== '')   $tabla = $tabla->where('nombre_oficina', $oficina);
            if ($modalidad !== '') $tabla = $tabla->where('modalidad', $modalidad);
            if ($nivel !== '')     $tabla = $tabla->where('nivel_educativo', $nivel);
            if ($puesto !== '')    $tabla = $tabla->where('puesto', $puesto);

            // Orden
            $ordenar = $request->query('ordenar', 'nombre'); // nombre|salario|fecha
            $dir     = strtolower($request->query('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

            $tabla = match ($ordenar) {
                'salario' => $tabla->sortBy(function ($r) {
                    return (float)($r['salario'] ?? 0);
                }, SORT_REGULAR, $dir === 'desc'),
                'fecha'   => $tabla->sortBy(function ($r) {
                    return $r['fecha_contratacion'] ?? null;
                }, SORT_REGULAR, $dir === 'desc'),
                default   => $tabla->sortBy(function ($r) {
                    return mb_strtoupper($r['nombre_completo'] ?? '');
                }, SORT_NATURAL, $dir === 'desc'),
            };

            // Paginación
            $paginados = $this->paginate($tabla->values(), $request, perPageDefault: 10, routeName: 'reportes.empleados');

            $out = [
                'kpis'   => $data['kpis']   ?? null,
                'charts' => $data['charts'] ?? null,
                'tabla'  => $paginados,
                'filtros'=> [
                    'busqueda' => $busqueda,
                    'oficina'  => $oficina,
                    'modalidad'=> $modalidad,
                    'nivel'    => $nivel,
                    'puesto'   => $puesto,
                    'ordenar'  => $ordenar,
                    'dir'      => $dir,
                ]
            ];

            return response()->json($out, 200);

        } catch (\Throwable $e) {
            Log::error('Reporte empleados - excepción', ['msg' => $e->getMessage()]);
            return response()->json(['error' => 'Error interno al obtener reporte de empleados'], 500);
        }
    }

    /**
     * Reporte General de Asistencia (mensual)
     * GET /reportes/asistencia/general?mes=MM&anio=YYYY
     */
    public function asistenciaGeneral(Request $request)
    {
        try {
            $now   = Carbon::now();
            $mes   = (int)$request->query('mes',  $now->month);
            $anio  = (int)$request->query('anio', $now->year);

            if ($mes < 1 || $mes > 12 || $anio < 1900) {
                return response()->json(['error' => 'Parámetros inválidos: mes/anio'], 422);
            }

            $url  = "{$this->apiBase}/reportes/asistencia/general";
            $resp = Http::timeout(30)->acceptJson()->get($url, ['mes' => $mes, 'anio' => $anio]);

            if ($resp->failed()) {
                return $this->apiFail('Asistencia', $resp, ['mes' => $mes, 'anio' => $anio]);
            }

            $data  = $resp->json();
            $tabla = collect($data['tabla'] ?? []);

            // -------- Filtros --------
            $busqueda = trim((string)$request->query('busqueda', ''));
            $oficina  = trim((string)$request->query('oficina', ''));
            $puesto   = trim((string)$request->query('puesto', ''));

            if ($busqueda !== '') {
                $tabla = $tabla->filter(function ($r) use ($busqueda) {
                    return stripos($r['nombre'] ?? '', $busqueda) !== false
                        || stripos($r['dni'] ?? '', $busqueda) !== false;
                });
            }
            if ($oficina !== '') $tabla = $tabla->where('nombre_oficina', $oficina);
            if ($puesto !== '')  $tabla = $tabla->where('puesto', $puesto);

            // -------- Orden --------
            $ordenar = $request->query('ordenar', 'nombre'); // nombre|dias|horas|oficina
            $dir     = strtolower($request->query('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

            $tabla = match ($ordenar) {
                'dias'    => $tabla->sortBy(fn($r) => (int)($r['dias_presentes'] ?? 0), SORT_REGULAR, $dir === 'desc'),
                'horas'   => $tabla->sortBy(fn($r) => (float)($r['horas_mes'] ?? 0), SORT_REGULAR, $dir === 'desc'),
                'oficina' => $tabla->sortBy(fn($r) => mb_strtoupper($r['nombre_oficina'] ?? ''), SORT_NATURAL, $dir === 'desc'),
                default   => $tabla->sortBy(fn($r) => mb_strtoupper($r['nombre'] ?? ''), SORT_NATURAL, $dir === 'desc'),
            };

            // Paginación
            $paginados = $this->paginate($tabla->values(), $request, perPageDefault: 10, routeName: 'reportes.asistencia');

            $out = [
                'periodo' => $data['periodo'] ?? ['mes' => $mes, 'anio' => $anio],
                'kpis'    => $data['kpis'] ?? null,
                'charts'  => $data['charts'] ?? null,
                'tabla'   => $paginados,
                'filtros' => [
                    'busqueda' => $busqueda,
                    'oficina'  => $oficina,
                    'puesto'   => $puesto,
                    'ordenar'  => $ordenar,
                    'dir'      => $dir,
                ],
            ];

            return response()->json($out, 200);

        } catch (\Throwable $e) {
            Log::error('Reporte asistencia - excepción', ['msg' => $e->getMessage()]);
            return response()->json(['error' => 'Error interno al obtener reporte de asistencia'], 500);
        }
    }

    /* =========================================================
     *                   EXPORTACIONES: PDF / EXCEL / HTML
     * =========================================================
     * GET /reportes/exportar/{tipo}/{formato}
     *   - tipo: empleados | asistencia
     *   - formato: pdf | excel | html
     * Para asistencia, puedes pasar ?mes=&anio=
     */
    public function exportar(Request $request, string $tipo, string $formato)
    {
        $tipo    = strtolower($tipo);
        $formato = strtolower($formato);

        if (!in_array($tipo, ['empleados', 'asistencia'], true)) {
            return back()->withErrors(['error' => 'Tipo de reporte no soportado.']);
        }
        if (!in_array($formato, ['pdf', 'excel', 'html'], true)) {
            return back()->withErrors(['error' => 'Formato no soportado.']);
        }

        // Endpoint y query (reenviamos mes/anio cuando es asistencia)
        $endpoint = "{$this->apiBase}/reportes/{$tipo}/general";
        $query = [];
        if ($tipo === 'asistencia') {
            $now = Carbon::now();
            $query['mes']  = (int)$request->query('mes',  $now->month);
            $query['anio'] = (int)$request->query('anio', $now->year);
        }

        $resp = Http::timeout(40)->acceptJson()->get($endpoint, $query);
        if ($resp->failed()) {
            return $this->apiFail(ucfirst($tipo), $resp, $query);
        }

        $payload = $resp->json();
        $rows    = $this->extractRows($payload['tabla'] ?? []);

        // ====== HTML ======
        if ($formato === 'html') {
            return view('reportes.reporte_html', [
                'tipo' => $tipo,
                'data' => $payload,
                'rows' => $rows,
            ]);
        }

        // ====== PDF ======
        if ($formato === 'pdf') {
            $pdf = Pdf::loadView('reportes.reporte_pdf', [
                'tipo' => $tipo,
                'data' => $payload,
                'rows' => $rows,
            ])->setPaper('a4', 'portrait');

            $filename = "reporte_{$tipo}_" . now()->format('Ymd_His') . ".pdf";
            return $pdf->download($filename);
        }

        // ====== Excel (Maatwebsite/Excel) — FromView con Blade ======
        $viewData = [
            'titulo' => 'Reporte',
            'tipo'   => $tipo,
            'data'   => $payload,
            'rows'   => $rows,
        ];

        $export = new class($viewData) implements FromView, WithTitle, ShouldAutoSize {
            public function __construct(private array $viewData) {}

            public function view(): View
            {
                // resources/views/reportes/reporte_excel.blade.php
                return view('reportes.reporte_excel', $this->viewData);
            }

            public function title(): string
            {
                $t = $this->viewData['tipo'] ?? 'Reporte';
                return 'Reporte ' . ucfirst($t);
            }
        };

        $filename = "reporte_{$tipo}_" . now()->format('Ymd_His') . ".xlsx";
        return Excel::download($export, $filename);
    }

    /* ====================== Helpers ====================== */

    /**
     * Paginar una colección como en tu EmpleadoController.
     */
    private function paginate(Collection $items, Request $request, int $perPageDefault = 10, ?string $routeName = null): LengthAwarePaginator
    {
        $perPage      = (int)$request->query('per_page', $perPageDefault);
        $currentPage  = (int)$request->query('page', 1);
        $total        = $items->count();
        $slice        = $items->slice(($currentPage - 1) * $perPage, $perPage);

        $paginator = new LengthAwarePaginator(
            $slice->values(),
            $total,
            $perPage,
            $currentPage,
            ['path' => $routeName ? route($routeName) : $request->url(), 'query' => $request->query()]
        );

        return $paginator;
    }

    /**
     * Manejo uniforme de fallo de la API Node.
     */
    private function apiFail(string $tipo, \Illuminate\Http\Client\Response $resp, array $extra = [])
    {
        Log::warning("Reporte {$tipo} - fallo API", array_merge([
            'status' => $resp->status(),
            'body'   => $resp->body(),
        ], $extra));

        return response()->json([
            'error'   => "No se pudo obtener el reporte de {$tipo}",
            'status'  => $resp->status(),
            'backend' => $this->safeBody($resp->json()),
        ], $resp->status() ?: 502);
    }

    private function safeBody($json)
    {
        return is_array($json) ? $json : ['raw' => $json];
    }

    /**
     * Extrae filas desde distintos posibles formatos:
     *  - arreglo plano
     *  - paginator -> ['data' => [...]]
     */
    private function extractRows($tabla): array
    {
        if (is_array($tabla) && array_key_exists('data', $tabla) && is_array($tabla['data'])) {
            return $tabla['data'];
        }
        return is_array($tabla) ? $tabla : [];
    }

    /**
     * Crea headings legibles y una matriz ordenada para Excel/HTML/PDF.
     */
    private function buildMatrix(array $rows): array
    {
        if (empty($rows)) {
            return [[], []];
        }

        // Usamos las llaves del primer registro como orden base
        $keys = array_keys($rows[0]);

        // Headings legibles
        $headings = array_map(function ($k) {
            $k = str_replace(['_id', '_cod'], ['', ''], $k);
            $k = str_replace('_', ' ', $k);
            return mb_convert_case($k, MB_CASE_TITLE, "UTF-8");
        }, $keys);

        // Matriz
        $matrix = [];
        foreach ($rows as $r) {
            $row = [];
            foreach ($keys as $k) {
                $val = $r[$k] ?? null;
                if (is_bool($val))      $val = $val ? 'Sí' : 'No';
                if ($val === null)      $val = '-';
                if ($val instanceof \DateTimeInterface) $val = $val->format('Y-m-d H:i:s');
                $row[] = $val;
            }
            $matrix[] = $row;
        }

        return [$headings, $matrix];
    }
}
