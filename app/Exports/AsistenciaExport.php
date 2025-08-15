<?php

namespace App\Exports;

use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Carbon\Carbon;
use App\Exports\EmpleadoAsistenciaSheet;

class AsistenciaExport implements WithMultipleSheets
{
    protected $mes;
    protected $anio;
    protected $nombreFiltro;

    public function __construct($mes, $anio, $nombreFiltro = null)
    {
        $this->mes = $mes;
        $this->anio = $anio;
        $this->nombreFiltro = $nombreFiltro;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Llamada a API que genera la estructura del PDF (y sirve también para Excel)
        $response = Http::get('http://localhost:3000/api/control-asistencia/pdf', [
            'mes' => $this->mes,
            'anio' => $this->anio
        ]);

        if (!$response->ok()) {
            return []; // evita romper el Excel
        }

        $data = $response->json();

        $totalDias = $data['dias']; // ← es un número: 31, 30...
        $empleados = $data['empleados'];

        // Si hay filtro de nombre, aplicarlo
        if ($this->nombreFiltro) {
            $empleados = collect($empleados)->filter(fn($e) =>
                str_contains(strtolower($e['nombre']), strtolower($this->nombreFiltro))
            )->values()->all();
        }

        // Generar una hoja por empleado
        foreach ($empleados as $emp) {
            $sheets[] = new EmpleadoAsistenciaSheet($emp, $totalDias, $this->mes, $this->anio);
        }

        return $sheets;
    }
}
