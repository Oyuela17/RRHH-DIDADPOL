<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Dompdf\Dompdf; // si usarás PDF con dompdf/dompdf

class VacacionesController extends Controller
{
    public function index(Request $req)
    {
        \Carbon\Carbon::setLocale('es'); // Forzar español
        $mes = intval($req->get('mes', now()->month));
        $anio = intval($req->get('anio', now()->year));
        $cod_empleado = intval($req->get('cod_empleado', 0));

        // Empleados (para el select)
        $empleados = DB::table('empleados as e')
            ->join('personas as p', 'p.cod_persona', '=', 'e.cod_persona')
            ->select('e.cod_empleado', 'p.nombre_completo')
            ->orderBy('p.nombre_completo')
            ->get();

        $diasMes = cal_days_in_month(CAL_GREGORIAN, $mes, $anio);

        // Vacaciones (incluye cruces con el mes) - CORREGIDO con placeholders con nombre
        $sql = "
        SELECT v.id, v.cod_empleado, p.nombre_completo,
               v.fecha_inicio, v.fecha_fin, v.estado
        FROM vacaciones v
        JOIN empleados e ON e.cod_empleado = v.cod_empleado
        JOIN personas p  ON p.cod_persona  = e.cod_persona
        WHERE EXTRACT(YEAR FROM v.fecha_inicio) <= :anio1
          AND EXTRACT(YEAR FROM v.fecha_fin)    >= :anio2
          AND (
               EXTRACT(MONTH FROM v.fecha_inicio) = :mes1
            OR EXTRACT(MONTH FROM v.fecha_fin)    = :mes2
            OR (
                 v.fecha_inicio <= make_date(:anio3, :mes3, 1)
             AND v.fecha_fin    >= make_date(:anio4, :mes4, :diafin)
               )
          )
        ";
        $params = [
            'anio1'  => $anio,
            'anio2'  => $anio,
            'mes1'   => $mes,
            'mes2'   => $mes,
            'anio3'  => $anio,
            'mes3'   => $mes,
            'anio4'  => $anio,
            'mes4'   => $mes,
            'diafin' => $diasMes,
        ];
        if ($cod_empleado > 0) {
            $sql .= " AND v.cod_empleado = :emp ";
            $params['emp'] = $cod_empleado;
        }
        $sql .= " ORDER BY p.nombre_completo, v.fecha_inicio ";

        $vacaciones = DB::select($sql, $params);

        // Mapa (cod_empleado -> día -> estado)
        $map = [];
        foreach ($vacaciones as $r) {
            $start  = Carbon::parse($r->fecha_inicio);
            $end    = Carbon::parse($r->fecha_fin);
            $cursor = $start->copy();
            while ($cursor->lte($end)) {
                if ($cursor->month == $mes && $cursor->year == $anio) {
                    $d = intval($cursor->day);
                    $map[$r->cod_empleado]['nombre'] = $r->nombre_completo;
                    $map[$r->cod_empleado]['dias'][$d] = $r->estado; // última gana
                }
                $cursor->addDay();
            }
        }

        // Resumen (saldo / tomados)
        $saldo = null; $diasTomados = 0;
        if ($cod_empleado > 0) {
            $row = DB::table('saldo_vacaciones')
                ->select('dias_disponibles','dias_tomados')
                ->where('cod_empleado', $cod_empleado)
                ->where('anio', $anio)
                ->first();
            if ($row) {
                $saldo = intval($row->dias_disponibles);
                $diasTomados = intval($row->dias_tomados);
            }
        }
        // aprobadas del mes pintado
        foreach ($map as $info) {
            foreach (($info['dias'] ?? []) as $estado) {
                if (strtoupper($estado) === 'APROBADA') $diasTomados++;
            }
        }

        return view('vacaciones.index', compact(
            'empleados','mes','anio','cod_empleado','diasMes','map','saldo','diasTomados'
        ));
    }

    public function store(Request $req)
    {
        $req->validate([
            'cod_empleado' => 'required|integer|min:1',
            'fecha_inicio' => 'required|date',
            'fecha_fin'    => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $cod_empleado = intval($req->cod_empleado);
        $fi = $req->fecha_inicio;
        $ff = $req->fecha_fin;
        $comentario = $req->comentario ?? '';

        // Solape
        $solape = DB::selectOne("
            SELECT 1 FROM vacaciones
            WHERE cod_empleado = ?
              AND NOT (? < fecha_inicio OR ? > fecha_fin)
            LIMIT 1
        ", [$cod_empleado, $fi, $ff]);

        if ($solape) {
            return back()->with('error', 'Ya existe una solicitud en ese rango.');
        }

        DB::table('vacaciones')->insert([
            'cod_empleado'    => $cod_empleado,
            'fecha_inicio'    => $fi,
            'fecha_fin'       => $ff,
            'comentario'      => $comentario,
            // estado por default: PENDIENTE (según tu enum)
            'fecha_solicitud' => now(),
        ]);

        return redirect()->route('vacaciones.index')->with('success', 'Solicitud registrada.');
    }

    public function cambiarEstado(Request $req)
    {
        $req->validate([
            'id'     => 'required|integer|min:1',
            'estado' => 'required|string'
        ]);

        $id = intval($req->id);
        $estado = strtoupper($req->estado); // PENDIENTE/APROBADA/RECHAZADA/CANCELADA

        DB::update("UPDATE vacaciones SET estado = ? WHERE id = ?", [$estado, $id]);

        // Si tienes triggers, ellos manejarán saldo y control_asistencia
        return back()->with('success', 'Estado actualizado.');
    }

   public function exportCsv(Request $req)
{
    $mes = (int) $req->get('mes', now()->month);
    $anio = (int) $req->get('anio', now()->year);
    $cod_empleado = (int) $req->get('cod_empleado', 0);
    $diafin = cal_days_in_month(CAL_GREGORIAN, $mes, $anio);

    $sql = "
    SELECT p.nombre_completo AS empleado,
           v.fecha_inicio, v.fecha_fin, v.estado, v.comentario,
           GREATEST(
             0,
             (LEAST(v.fecha_fin,   make_date(:anio, :mes, :diafin))
            - GREATEST(v.fecha_inicio, make_date(:anio, :mes, 1)) + 1)
           )::int AS dias
    FROM vacaciones v
    JOIN empleados e ON e.cod_empleado = v.cod_empleado
    JOIN personas  p ON p.cod_persona  = e.cod_persona
    WHERE v.fecha_inicio <= make_date(:anio, :mes, :diafin)
      AND v.fecha_fin    >= make_date(:anio, :mes, 1)
    ";

    $params = ['anio' => $anio, 'mes' => $mes, 'diafin' => $diafin];

    if ($cod_empleado > 0) {
        $sql .= " AND v.cod_empleado = :emp ";
        $params['emp'] = $cod_empleado;
    }

    $sql .= " ORDER BY p.nombre_completo, v.fecha_inicio ";

    $rows = DB::select($sql, $params);

    $filename = "vacaciones_{$anio}_{$mes}.csv";
    $headers  = [
        'Content-Type'        => 'text/csv; charset=utf-8',
        'Content-Disposition' => "attachment; filename=\"$filename\"",
    ];

    $callback = function () use ($rows, $mes, $anio) {
        $out = fopen('php://output', 'w');

        // Usar punto y coma como separador
        $delimiter = ';';

        // Título del reporte
        fputcsv($out, ["REPORTE DE VACACIONES"], $delimiter);
        fputcsv($out, ["Mes: {$mes} - Año: {$anio}"], $delimiter);
        fputcsv($out, [], $delimiter); // línea en blanco

        // Encabezados
        fputcsv($out, ['Empleado','Inicio','Fin','Días','Estado','Comentario'], $delimiter);

        // Datos
        foreach ($rows as $r) {
            fputcsv($out, [
                $r->empleado,
                $r->fecha_inicio,
                $r->fecha_fin,
                $r->dias,
                $r->estado,
                $r->comentario
            ], $delimiter);
        }

        fclose($out);
    };

    return response()->stream($callback, 200, $headers);
}

public function exportPdf(Request $req)
{
    $mes = (int) $req->get('mes', now()->month);
    $anio = (int) $req->get('anio', now()->year);
    $cod_empleado = (int) $req->get('cod_empleado', 0);
    $diafin = cal_days_in_month(CAL_GREGORIAN, $mes, $anio);

    $sql = "
    SELECT p.nombre_completo AS empleado,
           v.fecha_inicio, v.fecha_fin, v.estado, v.comentario,
           GREATEST(
             0,
             (LEAST(v.fecha_fin,   make_date(:anio, :mes, :diafin))
            - GREATEST(v.fecha_inicio, make_date(:anio, :mes, 1)) + 1)
           )::int AS dias
    FROM vacaciones v
    JOIN empleados e ON e.cod_empleado = v.cod_empleado
    JOIN personas  p ON p.cod_persona  = e.cod_persona
    WHERE v.fecha_inicio <= make_date(:anio, :mes, :diafin)
      AND v.fecha_fin    >= make_date(:anio, :mes, 1)
    ";

    $params = ['anio' => $anio, 'mes' => $mes, 'diafin' => $diafin];

    if ($cod_empleado > 0) {
        $sql .= " AND v.cod_empleado = :emp ";
        $params['emp'] = $cod_empleado;
    }

    $sql .= " ORDER BY p.nombre_completo, v.fecha_inicio ";

    $rows = DB::select($sql, $params);

    // Render simple en HTML
    $html  = '<h3 style="font-family:Arial;margin:0 0 10px">Reporte de Vacaciones</h3>';
    $html .= '<p style="font-family:Arial">Mes: '.e($mes).' / Año: '.e($anio).'</p>';
    $html .= '<table width="100%" border="1" cellspacing="0" cellpadding="6" style="border-collapse:collapse;font-family:Arial;font-size:12px">';
    $html .= '<tr style="background:#0d6efd;color:white"><th>Empleado</th><th>Inicio</th><th>Fin</th><th>Días</th><th>Estado</th><th>Comentario</th></tr>';
    foreach ($rows as $r) {
        $html .= '<tr>'
              .  '<td>'.e($r->empleado).'</td>'
              .  '<td>'.$r->fecha_inicio.'</td>'
              .  '<td>'.$r->fecha_fin.'</td>'
              .  '<td>'.$r->dias.'</td>'          // <- usamos el cálculo del mes
              .  '<td>'.$r->estado.'</td>'
              .  '<td>'.e($r->comentario).'</td>'
              .  '</tr>';
    }
    $html .= '</table>';

    if (class_exists(Dompdf::class)) {
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        return $dompdf->stream("vacaciones_{$anio}_{$mes}.pdf");
    }

    return response('<!doctype html><meta charset="utf-8">'.$html.'<script>window.print()</script>', 200)
        ->header('Content-Type','text/html; charset=utf-8');
}

}
