<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class EmpleadoAsistenciaSheet implements FromArray, WithTitle, WithHeadings, WithStyles, WithColumnWidths
{
    protected $empleado;
    protected $dias;
    protected $mes;
    protected $anio;

    public function __construct($empleado, $dias, $mes, $anio)
    {
        $this->empleado = $empleado;
        $this->dias = $dias;
        $this->mes = $mes;
        $this->anio = $anio;
    }

    public function title(): string
    {
        return substr($this->empleado['nombre'], 0, 31);
    }

    public function headings(): array
    {
        return [
            ['REPORTE INDIVIDUAL DE ASISTENCIA - ' . strtoupper(Carbon::create($this->anio, $this->mes)->locale('es')->isoFormat('MMMM [de] YYYY'))],
            ['Nombre:', $this->empleado['nombre']],
            ['Puesto:', $this->empleado['puesto']],
            ['DNI:', $this->empleado['dni']],
            [],
            ['Día', 'Hora de Entrada', 'Hora de Salida', 'Observación']
        ];
    }

    public function array(): array
    {
        $data = [];

        for ($dia = 1; $dia <= $this->dias; $dia++) {
            $data[] = [
                $dia,
                $this->empleado['dias'][$dia]['entrada'] ?? '-',
                $this->empleado['dias'][$dia]['salida'] ?? '-',
                $this->empleado['dias'][$dia]['observacion'] ?? '-',
            ];
        }

        if (!empty($this->empleado['tiempo_laborado'])) {
            $data[] = [];
            $data[] = ['Total laborado:', $this->empleado['tiempo_laborado']];
        }

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        // Título general
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12)->getColor()->setRGB('2C3E50');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        // Datos del empleado
        $sheet->getStyle('A2:A4')->getFont()->setBold(true);
        $sheet->getStyle('B2:B4')->getAlignment()->setHorizontal('left');

        // Encabezado de tabla
        $sheet->getStyle('A6:D6')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '2C3E50']],
            'fill' => [
                'fillType' => 'solid',
                'startColor' => ['rgb' => 'dbe9f4']
            ],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'borders' => ['allBorders' => ['borderStyle' => 'thin']]
        ]);

        // Bordes y alineación del contenido
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle("A7:D{$lastRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => 'hair']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center']
        ]);

        // Alinear observación a la izquierda
        $sheet->getStyle("D7:D{$lastRow}")->getAlignment()->setHorizontal('left');
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 20,
            'C' => 20,
            'D' => 40,
        ];
    }
}
