{{-- resources/views/reportes/reporte_excel.blade.php --}}
@php
  $titulo   = $titulo ?? 'Reporte';
  $tipo     = $tipo ?? 'empleados';
  $periodo  = $data['periodo'] ?? null;
  $kpis     = $data['kpis'] ?? [];
  $tabla    = $rows ?? [];
  $modChart = $data['charts']['por_modalidad'] ?? [];
  $totalMod = 0;
  foreach ($modChart as $m) { $totalMod += (int)($m['total'] ?? 0); }
@endphp

<table border="1" cellspacing="0" cellpadding="4" style="border-collapse:collapse; width:100%; font-family:'Segoe UI', Arial, sans-serif; font-size:12px;">
  {{-- ====== TÍTULO ====== --}}
  <tr style="background:#1b4f72; color:#ffffff;">
    <td colspan="8" style="font-weight:bold; font-size:14px; padding:6px 8px;">
      {{ $titulo }} — {{ strtoupper($tipo ?? '') }}
      @if(!empty($periodo))
        (Mes {{ $periodo['mes'] ?? '' }} / {{ $periodo['anio'] ?? '' }})
      @endif
    </td>
  </tr>

  {{-- ====== RESUMEN / KPIs ====== --}}
  @if($tipo === 'empleados')
    <tr style="background:#eaf2f8;"><td><strong>Total</strong></td><td>{{ (float)($kpis['total'] ?? 0) }}</td></tr>
    <tr><td><strong>Activos</strong></td><td>{{ (float)($kpis['activos'] ?? 0) }}</td></tr>
    <tr><td><strong>Sin contrato</strong></td><td>{{ (float)($kpis['sin_contrato'] ?? 0) }}</td></tr>
    <tr><td><strong>Salario Promedio</strong></td><td>L. {{ number_format((float)($kpis['salario_promedio'] ?? 0), 2, '.', ',') }}</td></tr>
    <tr><td><strong>Salario Mediana</strong></td><td>L. {{ number_format((float)($kpis['salario_mediana'] ?? 0), 2, '.', ',') }}</td></tr>

    {{-- ====== Distribución por Modalidad ====== --}}
    <tr><td colspan="8" style="height:6px;"></td></tr>
    <tr style="background:#d6eaf8;">
      <td colspan="8" style="font-weight:bold;">Distribución por Modalidad</td>
    </tr>
    <tr style="background:#21618c; color:#ffffff; font-weight:bold;">
      <td>Modalidad</td>
      <td>Cantidad</td>
      <td>%</td>
      <td colspan="5"></td>
    </tr>
    @forelse($modChart as $m)
      @php
        $n = (int)($m['total'] ?? 0);
        $pct = $totalMod > 0 ? round(($n/$totalMod)*100, 0) : 0;
      @endphp
      <tr>
        <td>{{ $m['modalidad'] ?? '—' }}</td>
        <td>{{ $n }}</td>
        <td>{{ $pct }}%</td>
        <td colspan="5">
          <div style="background:#f1f1f1; height:10px; width:100%;">
            <div style="background:#4da3ff; height:10px; width:{{ max(2,$pct) }}%;"></div>
          </div>
        </td>
      </tr>
    @empty
      <tr><td colspan="8" style="color:#6b7280;">Sin datos para modalidades.</td></tr>
    @endforelse
    <tr style="background:#f9fbfe;"><td><strong>Total</strong></td><td>{{ $totalMod }}</td><td colspan="6"></td></tr>
  @else
    {{-- ====== KPIs ASISTENCIA ====== --}}
    <tr><td><strong>Empleados</strong></td><td>{{ (float)($kpis['empleados'] ?? 0) }}</td></tr>
    <tr><td><strong>Asistencia (%)</strong></td><td>{{ number_format((float)($kpis['asistencia_pct'] ?? 0),2,'.',',') }}%</td></tr>
    <tr><td><strong>Horas Totales</strong></td><td>{{ number_format((float)($kpis['horas_totales'] ?? 0),2,'.',',') }}</td></tr>
    <tr><td><strong>Promedio por empleado</strong></td><td>{{ number_format((float)($kpis['horas_promedio_empleado'] ?? 0),2,'.',',') }}</td></tr>
    @if(!empty($periodo))
      <tr><td><strong>Días del mes</strong></td><td>{{ (int)($periodo['dias_mes'] ?? 0) }}</td></tr>
    @endif
  @endif

  {{-- Separador --}}
  <tr><td colspan="8" style="height:8px;"></td></tr>

  {{-- ====== TABLA DETALLE ====== --}}
  @if($tipo === 'empleados')
    <tr style="background:#21618c; color:#ffffff; font-weight:bold;">
      <td>Nombre</td>
      <td>DNI</td>
      <td>Oficina</td>
      <td>Puesto</td>
      <td>Modalidad</td>
      <td>Nivel</td>
      <td>Contrato</td>
      <td>Salario</td>
    </tr>

    @forelse($tabla as $r)
      <tr>
        <td>{{ $r['nombre_completo'] ?? '' }}</td>
        <td>{{ $r['dni'] ?? '' }}</td>
        <td>{{ $r['nombre_oficina'] ?? '' }}</td>
        <td>{{ $r['puesto'] ?? '' }}</td>
        <td>{{ $r['modalidad'] ?? '' }}</td>
        <td>{{ $r['nivel_educativo'] ?? '' }}</td>
        <td>
          @php $c=$r['contrato_activo'] ?? null; @endphp
          @if($c===true)
            Activo
          @elseif($c===false)
            Inactivo
          @else
            Sin contrato
          @endif
        </td>
        <td style="text-align:right;">
          @if(isset($r['salario']) && $r['salario']!=='')
            L. {{ number_format((float)$r['salario'],2,'.',',') }}
          @else
            -
          @endif
        </td>
      </tr>
    @empty
      <tr><td colspan="8" style="color:#6b7280;">Sin registros.</td></tr>
    @endforelse
  @else
    <tr style="background:#21618c; color:#ffffff; font-weight:bold;">
      <td>Nombre</td>
      <td>Oficina</td>
      <td>Puesto</td>
      <td>Días Presentes</td>
      <td>Horas Mes</td>
    </tr>
    @forelse($tabla as $r)
      <tr>
        <td>{{ $r['nombre'] ?? '' }}</td>
        <td>{{ $r['nombre_oficina'] ?? '' }}</td>
        <td>{{ $r['puesto'] ?? '' }}</td>
        <td style="text-align:right;">{{ number_format((float)($r['dias_presentes'] ?? 0),0,'.',',') }}</td>
        <td style="text-align:right;">{{ number_format((float)($r['horas_mes'] ?? 0),2,'.',',') }}</td>
      </tr>
    @empty
      <tr><td colspan="5" style="color:#6b7280;">Sin registros.</td></tr>
    @endforelse
  @endif

  {{-- ====== PIE ====== --}}
  <tr><td colspan="8" style="height:10px;"></td></tr>
  <tr><td colspan="8" style="font-size:11px; color:#6b7280;">Generado automáticamente por DIDADPOL · RRHH</td></tr>
</table>
