<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

// Modelos
use App\Models\EmpleadoPlanilla;
use App\Models\ISRPlanilla;
use App\Models\ControlAsistencia;
use App\Models\Planilla;
use App\Models\PersonaPlanilla; 
use App\Models\EmpleadoContratoHistorial; // <-- usando historial para fecha y salario

class PlanillaController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax() || $request->isMethod('post')) {
            try {
                return $this->store($request);
            } catch (\Throwable $e) {
                \Log::error('Planilla AJAX error', [
                    'm' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                return response()->json([
                    'message' => 'Error interno',
                    'error'   => $e->getMessage(),
                ], 500);
            }
        }
        return view('planilla.index');
    }

    public function store(Request $request)
    {
        $accion = $request->input('accion');
        return match ($accion) {
            'ver_planilla' => $this->handleView(),
            default => response()->json(['message' => 'Acción no válida.'], 422),
        };
    }

    // Si luego agregas GET /planilla/data, puedes llamar a este método:
    public function data(Request $request)
    {
        return $this->handleView();
    }

    private function handleView()
    {
        // Nombres reales de tablas
        $tblP   = (new Planilla)->getTable();                 // planillas
        $tblPer = (new PersonaPlanilla)->getTable();          // personas
        $tblEmp = (new EmpleadoPlanilla)->getTable();         // empleados
        $tblHist= (new EmpleadoContratoHistorial)->getTable();// empleados_contratos_histor

        // Existen tablas?
        $hasEmp  = Schema::hasTable($tblEmp);
        $hasPer  = Schema::hasTable($tblPer);
        $hasPue  = Schema::hasTable('puestos');
        $hasHist = Schema::hasTable($tblHist);

        // Query base
        $q = DB::table("$tblP as p");

        // JOIN personas
        if ($hasPer) {
            $q->leftJoin("$tblPer as per", 'per.cod_persona', '=', 'p.cod_persona');
        }

        // JOIN empleados
        if ($hasEmp) {
            $q->leftJoin("$tblEmp as e", 'e.cod_persona', '=', 'p.cod_persona');
        }

        // JOIN historial de contratos (activo) por cod_empleado
        if ($hasHist && $hasEmp) {
            $q->leftJoin("$tblHist as ch", function ($j) {
                $j->on('ch.cod_empleado', '=', 'e.cod_empleado')
                  ->where('ch.contrato_activo', true);
            });
        }

        // JOIN puestos
        if ($hasPue && $hasEmp) {
            $q->leftJoin('puestos as pu', 'pu.cod_puesto', '=', 'e.cod_puesto');
        }

        // SELECT seguro: si falta una tabla/columna, devolvemos campo vacío o 0
        $selects = [
            'p.cod_persona',
            DB::raw($hasPer ? "COALESCE(per.nombre_completo,'') AS nombre_completo" : "'' AS nombre_completo"),
            DB::raw($hasPer ? "COALESCE(per.rtn,'') AS rtn" : "'' AS rtn"),
            DB::raw($hasPer ? "COALESCE(per.dni,'') AS dni" : "'' AS dni"),

            DB::raw(($hasPue && $hasEmp && Schema::hasColumn('puestos', 'nom_puesto'))
                ? "COALESCE(pu.nom_puesto,'') AS nom_puesto" : "'' AS nom_puesto"),

            // Fecha de ingreso desde historial activo
            DB::raw(($hasHist && $hasEmp && Schema::hasColumn($tblHist, 'fecha_inicio_contrato'))
                ? "ch.fecha_inicio_contrato AS fecha_inicio_contrato"
                : "NULL AS fecha_inicio_contrato"),

            // Salario desde historial activo
            DB::raw(($hasHist && $hasEmp && Schema::hasColumn($tblHist, 'salario'))
                ? "COALESCE(ch.salario,0) AS salario"
                : "0 AS salario"),

            DB::raw("COALESCE(p.dd,0) AS dd"),
            DB::raw("COALESCE(p.dt,0) AS dt"),
            DB::raw("COALESCE(p.salario_bruto,0) AS salario_bruto"),

            DB::raw("COALESCE(p.ihss,0) AS ihss"),
            DB::raw("COALESCE(p.isr,0) AS isr"),
            DB::raw("COALESCE(p.injupemp,0) AS injupemp"),
            DB::raw("COALESCE(p.impuesto_vecinal,0) AS impuesto_vecinal"),
            DB::raw("COALESCE(p.dias_descargados,0) AS dias_descargados"),

            DB::raw("COALESCE(p.injupemp_reingresos,0) AS injupemp_reingresos"),
            DB::raw("COALESCE(p.injupemp_prestamos,0) AS injupemp_prestamos"),
            DB::raw("COALESCE(p.prestamo_banco_atlantida,0) AS prestamo_banco_atlantida"),
            DB::raw("COALESCE(p.pagos_deducibles,0) AS pagos_deducibles"),
            DB::raw("COALESCE(p.colegio_admon_empresas,0) AS colegio_admon_empresas"),
            DB::raw("COALESCE(p.cuota_coop_elga,0) AS cuota_coop_elga"),

            DB::raw("COALESCE(p.total_deducciones,0) AS total_deducciones"),
            DB::raw("COALESCE(p.total_a_pagar,0) AS total_a_pagar"),
        ];

        $rowsPlan = $q->select($selects)
            ->orderBy('p.id')
            ->get();

        $data = $rowsPlan->map(function ($r, $i) {
            // Formato seguro de la fecha
            $fechaIngreso = '';
            if (!empty($r->fecha_inicio_contrato)) {
                $fechaIngreso = $r->fecha_inicio_contrato instanceof \DateTimeInterface
                    ? $r->fecha_inicio_contrato->format('Y-m-d')
                    : Carbon::parse($r->fecha_inicio_contrato)->format('Y-m-d');
            }

            // Totales de respaldo si en DB están en 0
            $totDed = (float)$r->ihss
                    + (float)$r->isr
                    + (float)$r->injupemp
                    + (float)$r->impuesto_vecinal
                    + (float)$r->injupemp_reingresos
                    + (float)$r->injupemp_prestamos
                    + (float)$r->prestamo_banco_atlantida
                    + (float)$r->pagos_deducibles
                    + (float)$r->colegio_admon_empresas
                    + (float)$r->cuota_coop_elga;

            $salBruto = (float)$r->salario_bruto;
            $totPagar = max(round($salBruto - $totDed, 2), 0);

            return [
                'no'                    => $i + 1,
                'cod_persona'           => $r->cod_persona,
                'nombre'                => $r->nombre_completo,
                'rtn'                   => $r->rtn,
                'dni'                   => $r->dni,
                'cargo'                 => $r->nom_puesto,
                'fecha_ingreso'         => $fechaIngreso, // <- para la tabla

                'salario'               => (float)($r->salario ?? 0),
                'salariobruto'          => $salBruto,

                'ihss'                  => (float)$r->ihss,
                'isr'                   => (float)$r->isr,
                'injupemp'              => (float)$r->injupemp,
                'vecinal'               => (float)$r->impuesto_vecinal,

                'dt'                    => (int)$r->dt,
                'dd'                    => (int)$r->dd,
                'dias_descargados'      => (int)$r->dias_descargados,

                'injupemp_reingresos'      => (float)$r->injupemp_reingresos,
                'injupemp_prestamos'       => (float)$r->injupemp_prestamos,
                'prestamo_banco_atlantida' => (float)$r->prestamo_banco_atlantida,
                'pagos_deducibles'         => (float)$r->pagos_deducibles,
                'colegio_admon_empresas'   => (float)$r->colegio_admon_empresas,
                'cuota_coop_elga'          => (float)$r->cuota_coop_elga,

                'total_deducciones'     => ($r->total_deducciones > 0) ? (float)$r->total_deducciones : round($totDed, 2),
                'total_a_pagar'         => ($r->total_a_pagar > 0) ? (float)$r->total_a_pagar : $totPagar,
            ];
        })->values()->all();

        return response()->json(['data' => $data]);
    }

    private function calcularISR(float $salarioMensual): float
    {
        $rangos = ISRPlanilla::where('tipo', 'ISR')->orderBy('sueldo_inicio')->get();
        $base = $salarioMensual;
        $totalISR = 0.0;

        foreach ($rangos as $rango) {
            if ($base > $rango->sueldo_fin) {
                $monto = $rango->sueldo_fin - $rango->sueldo_inicio;
            } elseif ($base > $rango->sueldo_inicio) {
                $monto = $base - $rango->sueldo_inicio;
            } else {
                continue;
            }
            $totalISR += $monto * ((float)$rango->porcentaje / 100);
        }

        return round($totalISR, 2);
    }

    private function calcularImpuestoVecinal(float $salarioMensual): float
    {
        $ingresoAnual = $salarioMensual * 12;
        $rango = ISRPlanilla::where('tipo', 'Vecinal')
            ->where('sueldo_inicio', '<=', $ingresoAnual)
            ->where('sueldo_fin', '>=', $ingresoAnual)
            ->first();

        if (!$rango) return 0.00;

        $impAnual = ($ingresoAnual / 1000) * (float)$rango->porcentaje;
        return round($impAnual / 12, 2);
    }
}
