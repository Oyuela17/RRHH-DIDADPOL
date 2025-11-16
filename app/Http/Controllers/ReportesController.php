<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Carbon\Carbon;


use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ReportesController extends Controller
{
 
    private string $apiBase;

    public function __construct()
    {
        $this->apiBase = rtrim(env('API_RRHH_BASE', 'https://rrhh-didadpol-1.onrender.com/api'), '/');
    }

    /**
     * Vista con pestañas (Empleados / Asistencia / Planilla).
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
            $url  = "{$this->apiBase}/reportes/empleados/general";
            $resp = Http::timeout(25)->acceptJson()->get($url);

            if ($resp->failed()) {
                return $this->apiFail('Empleados', $resp);
            }

            $data  = $resp->json();
            $tabla = collect($data['tabla'] ?? []);

            // ---------- Filtros y orden sobre la tabla ----------
            $busqueda = trim((string) $request->query('busqueda', ''));
            $oficina  = trim((string) $request->query('oficina', ''));
            $modalidad= trim((string) $request->query('modalidad', ''));
            $nivel    = trim((string) $request->query('nivel', ''));
            $puesto   = trim((string) $request->query('puesto', ''));

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
                    return (float) ($r['salario'] ?? 0);
                }, SORT_REGULAR, $dir === 'desc'),
                'fecha'   => $tabla->sortBy(function ($r) {
                    return $r['fecha_contratacion'] ?? null;
                }, SORT_REGULAR, $dir === 'desc'),
                default   => $tabla->sortBy(function ($r) {
                    return mb_strtoupper($r['nombre_completo'] ?? '');
                }, SORT_NATURAL, $dir === 'desc'),
            };

            // Paginación
            $paginados = $this->paginate(
                $tabla->values(),
                $request,
                perPageDefault: 10,
                routeName: 'reportes.empleados'
            );

            $out = [
                'kpis'    => $data['kpis']   ?? null,
                'charts'  => $data['charts'] ?? null,
                'tabla'   => $paginados,
                'filtros' => [
                    'busqueda' => $busqueda,
                    'oficina'  => $oficina,
                    'modalidad'=> $modalidad,
                    'nivel'    => $nivel,
                    'puesto'   => $puesto,
                    'ordenar'  => $ordenar,
                    'dir'      => $dir,
                ],
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
            $now  = Carbon::now();
            $mes  = (int) $request->query('mes',  $now->month);
            $anio = (int) $request->query('anio', $now->year);

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
            $busqueda = trim((string) $request->query('busqueda', ''));
            $oficina  = trim((string) $request->query('oficina', ''));
            $puesto   = trim((string) $request->query('puesto', ''));

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
                'dias'    => $tabla->sortBy(fn($r) => (int) ($r['dias_presentes'] ?? 0), SORT_REGULAR, $dir === 'desc'),
                'horas'   => $tabla->sortBy(fn($r) => (float) ($r['horas_mes'] ?? 0), SORT_REGULAR, $dir === 'desc'),
                'oficina' => $tabla->sortBy(fn($r) => mb_strtoupper($r['nombre_oficina'] ?? ''), SORT_NATURAL, $dir === 'desc'),
                default   => $tabla->sortBy(fn($r) => mb_strtoupper($r['nombre'] ?? ''), SORT_NATURAL, $dir === 'desc'),
            };

            // Paginación
            $paginados = $this->paginate(
                $tabla->values(),
                $request,
                perPageDefault: 10,
                routeName: 'reportes.asistencia'
            );

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

    /**
     * Reporte General de Planilla
     * GET /reportes/planilla/general?periodo=YYYY-MM-DD&anio=YYYY&busqueda=&ordenar=&dir=
     *
     * Usa internamente PlanillaController::data() que ya arma el JSON.
     */
    public function planillaGeneral(Request $request)
    {
        try {
            /** @var \App\Http\Controllers\PlanillaController $planillaCtrl */
            $planillaCtrl = app(PlanillaController::class);

            // data() devuelve JsonResponse con ['data' => [...]]
            $jsonResp = $planillaCtrl->data($request);
            $status   = method_exists($jsonResp, 'getStatusCode')
                ? $jsonResp->getStatusCode()
                : 200;

            if ($status !== 200) {
                return response()->json([
                    'error'  => 'No se pudieron obtener los datos de planilla',
                    'status' => $status,
                ], $status);
            }

            $payload = json_decode($jsonResp->getContent(), true);
            $rows    = collect($payload['data'] ?? []);

            // ========= Filtros sobre la tabla =========
            $busqueda = trim((string) $request->query('busqueda', ''));
            if ($busqueda !== '') {
                $rows = $rows->filter(function ($r) use ($busqueda) {
                    return stripos($r['nombre'] ?? '', $busqueda) !== false
                        || stripos($r['dni'] ?? '', $busqueda) !== false
                        || stripos($r['rtn'] ?? '', $busqueda) !== false;
                });
            }

            // ========= Orden =========
            $ordenar = $request->query('ordenar', 'nombre'); // nombre|salario|neto|periodo
            $dir     = strtolower($request->query('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

            $rows = match ($ordenar) {
                'salario' => $rows->sortBy(
                    fn($r) => (float) ($r['salariobruto'] ?? 0),
                    SORT_REGULAR,
                    $dir === 'desc'
                ),
                'neto'    => $rows->sortBy(
                    fn($r) => (float) ($r['total_a_pagar'] ?? 0),
                    SORT_REGULAR,
                    $dir === 'desc'
                ),
                'periodo' => $rows->sortBy(
                    fn($r) => $r['periodo'] ?? '',
                    SORT_NATURAL,
                    $dir === 'desc'
                ),
                default   => $rows->sortBy(
                    fn($r) => mb_strtoupper($r['nombre'] ?? ''),
                    SORT_NATURAL,
                    $dir === 'desc'
                ),
            };

            // ========= KPIs de planilla =========
            $totalEmpleados    = $rows->count();
            $totalDevengado    = $rows->sum(fn($r) => (float) ($r['salariobruto'] ?? 0));
            $totalDeducciones  = $rows->sum(fn($r) => (float) ($r['total_deducciones'] ?? 0));
            $totalNetoPagar    = $rows->sum(fn($r) => (float) ($r['total_a_pagar'] ?? 0));
            $salarioPromedio   = $totalEmpleados > 0 ? $totalDevengado / $totalEmpleados : 0;
            $deduccionPromedio = $totalEmpleados > 0 ? $totalDeducciones / $totalEmpleados : 0;
            $porcDeducciones   = $totalDevengado > 0 ? ($totalDeducciones / $totalDevengado) * 100 : 0;

            $kpis = [
                'total_empleados'      => $totalEmpleados,
                'total_devengado'      => round($totalDevengado, 2),
                'total_deducciones'    => round($totalDeducciones, 2),
                'total_neto_pagar'     => round($totalNetoPagar, 2),
                'salario_promedio'     => round($salarioPromedio, 2),
                'deduccion_promedio'   => round($deduccionPromedio, 2),
                'porcentaje_deduccion' => round($porcDeducciones, 2),
            ];

            // ========= Charts básicos =========
            $charts = [
                'deducciones_por_tipo' => [
                    'labels' => [
                        'IHSS',
                        'ISR',
                        'INJUPEMP',
                        'Impuesto Vecinal',
                        'Injupemp Reingresos',
                        'Injupemp Préstamos',
                        'Préstamo Atlántida',
                        'Pagos Deducibles',
                        'Colegio Admon.',
                        'Coop. ELGA',
                    ],
                    'data' => [
                        'ihss'                   => $rows->sum(fn($r) => (float) ($r['ihss'] ?? 0)),
                        'isr'                    => $rows->sum(fn($r) => (float) ($r['isr'] ?? 0)),
                        'injupemp'               => $rows->sum(fn($r) => (float) ($r['injupemp'] ?? 0)),
                        'vecinal'                => $rows->sum(fn($r) => (float) ($r['vecinal'] ?? 0)),
                        'injupemp_reingresos'    => $rows->sum(fn($r) => (float) ($r['injupemp_reingresos'] ?? 0)),
                        'injupemp_prestamos'     => $rows->sum(fn($r) => (float) ($r['injupemp_prestamos'] ?? 0)),
                        'prestamo_banco'         => $rows->sum(fn($r) => (float) ($r['prestamo_banco_atlantida'] ?? 0)),
                        'pagos_deducibles'       => $rows->sum(fn($r) => (float) ($r['pagos_deducibles'] ?? 0)),
                        'colegio_admon_empresas' => $rows->sum(fn($r) => (float) ($r['colegio_admon_empresas'] ?? 0)),
                        'cuota_coop_elga'        => $rows->sum(fn($r) => (float) ($r['cuota_coop_elga'] ?? 0)),
                    ],
                ],
                'salario_vs_neto' => [
                    'labels' => $rows->pluck('nombre')->all(),
                    'series' => [
                        'salariobruto'  => $rows->pluck('salariobruto')->map(fn($v) => (float) $v)->all(),
                        'total_a_pagar' => $rows->pluck('total_a_pagar')->map(fn($v) => (float) $v)->all(),
                    ],
                ],
            ];

            // ========= Paginación =========
            $paginados = $this->paginate(
                $rows->values(),
                $request,
                perPageDefault: 10,
                routeName: 'reportes.planilla'
            );

            $out = [
                'kpis'    => $kpis,
                'charts'  => $charts,
                'tabla'   => $paginados,
                'filtros' => [
                    'busqueda' => $busqueda,
                    'ordenar'  => $ordenar,
                    'dir'      => $dir,
                    'periodo'  => $request->input('periodo'),
                    'anio'     => $request->input('anio'),
                ],
            ];

            return response()->json($out, 200);

        } catch (\Throwable $e) {
            Log::error('Reporte planilla - excepción', ['msg' => $e->getMessage()]);
            return response()->json(['error' => 'Error interno al obtener reporte de planilla'], 500);
        }
    }

    /* =========================================================
     *                   EXPORTACIONES: PDF / EXCEL / HTML
     * =========================================================
     * GET /reportes/exportar/{tipo}/{formato}
     *   - tipo: empleados | asistencia | planilla
     *   - formato: pdf | excel | html
     * Para asistencia, puedes pasar ?mes=&anio=
     */
    public function exportar(Request $request, string $tipo, string $formato)
    {
        $tipo    = strtolower($tipo);
        $formato = strtolower($formato);

        if (!in_array($tipo, ['empleados', 'asistencia', 'planilla'], true)) {
            return back()->withErrors(['error' => 'Tipo de reporte no soportado.']);
        }
        if (!in_array($formato, ['pdf', 'excel', 'html'], true)) {
            return back()->withErrors(['error' => 'Formato no soportado.']);
        }

        $payload = null;
        $rows    = [];

        // ====== PLANILLA: usamos nuestro propio método interno ======
        if ($tipo === 'planilla') {
            $jsonResp = $this->planillaGeneral($request);
            if ($jsonResp->getStatusCode() !== 200) {
                return back()->withErrors(['error' => 'No se pudo obtener el reporte de planilla.']);
            }

            $payload = $jsonResp->getData(true);
            $rows    = $this->extractRows($payload['tabla'] ?? []);
        } else {
            // ====== Empleados / Asistencia: consumen API Node ======
            $endpoint = "{$this->apiBase}/reportes/{$tipo}/general";
            $query    = [];

            if ($tipo === 'asistencia') {
                $now          = Carbon::now();
                $query['mes'] = (int) $request->query('mes',  $now->month);
                $query['anio']= (int) $request->query('anio', $now->year);
            }

            $resp = Http::timeout(40)->acceptJson()->get($endpoint, $query);
            if ($resp->failed()) {
                return $this->apiFail(ucfirst($tipo), $resp, $query);
            }

            $payload = $resp->json();
            $rows    = $this->extractRows($payload['tabla'] ?? []);
        }

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
    private function paginate(
        Collection $items,
        Request $request,
        int $perPageDefault = 10,
        ?string $routeName = null
    ): LengthAwarePaginator {
        $perPage     = (int) $request->query('per_page', $perPageDefault);
        $currentPage = (int) $request->query('page', 1);
        $total       = $items->count();
        $slice       = $items->slice(($currentPage - 1) * $perPage, $perPage);

        $paginator = new LengthAwarePaginator(
            $slice->values(),
            $total,
            $perPage,
            $currentPage,
            [
                'path'  => $routeName ? route($routeName) : $request->url(),
                'query' => $request->query(),
            ]
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
            return mb_convert_case($k, MB_CASE_TITLE, 'UTF-8');
        }, $keys);

        // Matriz
        $matrix = [];
        foreach ($rows as $r) {
            $row = [];
            foreach ($keys as $k) {
                $val = $r[$k] ?? null;
                if (is_bool($val))                     $val = $val ? 'Sí' : 'No';
                if ($val === null)                    $val = '-';
                if ($val instanceof \DateTimeInterface) $val = $val->format('Y-m-d H:i:s');
                $row[] = $val;
            }
            $matrix[] = $row;
        }

        return [$headings, $matrix];
    }
}
