<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AsistenciaExport;

class ControlAsistenciaAdminController extends Controller
{
    private $apiBaseVista = 'http://localhost:3000/api/control-asistencia/mes';
    private $apiBasePDF   = 'http://localhost:3000/api/control-asistencia/pdf';

    /**
     * Muestra la vista del administrador con asistencia mensual
     */
    public function index(Request $request)
    {
        $mes = $request->input('mes', now()->month);
        $anio = $request->input('anio', now()->year);
        $nombreFiltro = $request->input('nombre');

        try {
            $response = Http::get($this->apiBaseVista, [
                'mes' => $mes,
                'anio' => $anio
            ]);

            if (!$response->ok()) {
                return back()->with('error', 'Error al obtener datos del servidor.');
            }

            $data = $response->json();
            $dias = $data['dias'];
            $empleados = $data['empleados'];

            if ($nombreFiltro) {
                $empleados = collect($empleados)->filter(fn($e) =>
                    str_contains(strtolower($e['nombre']), strtolower($nombreFiltro))
                )->values()->all();
            }

            return view('asistencia.admin', compact('dias', 'empleados', 'mes', 'anio'));

        } catch (\Exception $e) {
            return back()->with('error', 'Error de conexión con el servidor.');
        }
    }

    /**
     * Exportar asistencia mensual en PDF (diseño planilla oficial)
     */
    public function exportarPDF(Request $request)
    {
        $mes = $request->input('mes', now()->month);
        $anio = $request->input('anio', now()->year);
        $nombreFiltro = $request->input('nombre');

        try {
            $response = Http::get($this->apiBasePDF, [
                'mes' => $mes,
                'anio' => $anio
            ]);

            if (!$response->ok()) {
                return back()->with('error', 'Error al generar PDF.');
            }

            $data = $response->json();
            $dias = $data['dias'];
            $empleados = collect($data['empleados']);

            if ($nombreFiltro) {
                $empleados = $empleados->filter(fn($e) =>
                    str_contains(strtolower($e['nombre']), strtolower($nombreFiltro))
                )->values();
            }

            $pdf = PDF::loadView('asistencia.reporte_pdf', [
                'dias' => $dias,
                'empleados' => $empleados,
                'mes' => $mes,
                'anio' => $anio
            ])->setPaper('a4', 'landscape');

            return $pdf->stream("Asistencia-{$mes}-{$anio}.pdf");

        } catch (\Exception $e) {
            return back()->with('error', 'No se pudo generar el PDF.');
        }
    }

    /**
     * Exportar asistencia mensual en Excel
     */
    public function exportarExcel(Request $request)
    {
        $mes = $request->input('mes', now()->month);
        $anio = $request->input('anio', now()->year);
        $nombreFiltro = $request->input('nombre');

        return Excel::download(
            new AsistenciaExport($mes, $anio, $nombreFiltro),
            "Asistencia-{$mes}-{$anio}.xlsx"
        );
    }
}
