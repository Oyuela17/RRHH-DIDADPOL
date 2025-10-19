<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Asistencia</title>
    <style>
        @page {
            margin: 20px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8.5px;
            margin: 0;
        }

        .titulo-global {
            text-align: center;
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 6px;
            text-transform: uppercase;
            color: #2c3e50;
        }

        .empleado-bloque {
            page-break-inside: avoid;
            break-inside: avoid;
            margin-bottom: 18px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .datos-empleado {
            font-weight: bold;
            margin-bottom: 5px;
            line-height: 1.3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
            margin-top: 6px;
            table-layout: fixed;
        }

        th, td {
            border: 1px solid #444;
            padding: 2px 3px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
        }

        th {
            background-color: #dbe9f4;
            font-weight: bold;
            color: #2c3e50;
        }

        .resumen-tiempo {
            text-align: right;
            font-size: 8px;
            margin-top: 4px;
            font-weight: bold;
            color: #1e7e34;
        }
    </style>
</head>
<body>

@foreach ($empleados as $emp)
    <div class="empleado-bloque">
        <div class="titulo-global">
            REPORTE INDIVIDUAL DE ASISTENCIA - {{ \Carbon\Carbon::create($anio, $mes)->locale('es')->isoFormat('MMMM [de] YYYY') }}
        </div>

        <div class="datos-empleado">
            Nombre: {{ $emp['nombre'] }}<br>
            Puesto: {{ $emp['puesto'] }}<br>
            DNI: {{ $emp['dni'] }}
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 10%;">Día</th>
                    <th style="width: 30%;">Hora de Entrada</th>
                    <th style="width: 30%;">Hora de Salida</th>
                    <th style="width: 30%;">Observación</th>
                </tr>
            </thead>
            <tbody>
                @for ($d = 1; $d <= $dias; $d++)
                    <tr>
                        <td>{{ $d }}</td>
                        <td>{{ $emp['dias'][$d]['entrada'] ?? '-' }}</td>
                        <td>{{ $emp['dias'][$d]['salida'] ?? '-' }}</td>
                        <td>{{ $emp['dias'][$d]['observacion'] ?? '-' }}</td>
                    </tr>
                @endfor
            </tbody>
        </table>

        @if (!empty($emp['tiempo_laborado']))
        <div class="resumen-tiempo">
            Tiempo total laborado en el mes: {{ $emp['tiempo_laborado'] }}
        </div>
        @endif
    </div>
@endforeach

</body>
</html>
