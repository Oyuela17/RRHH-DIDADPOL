{{-- resources/views/reportes/reporte_pdf.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>{{ ucfirst($tipo ?? 'Reporte') }} — DIDADPOL</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    *{ box-sizing:border-box; font-family: DejaVu Sans, Arial, Helvetica, sans-serif; }
    body{ margin:0; color:#1b2631; background:#ffffff; }

    .wrap{ width:100%; max-width:780px; margin:18px auto; padding:0 10px; }

    .header{
      border:1px solid #d9e2ec; background:#1b4f72; color:#fff;
      padding:10px 12px; border-radius:8px;
    }
    .topline{ font-size:11px; color:#e8f1f8; text-align:right; margin-bottom:4px; }
    .header-title{ font-size:16px; font-weight:bold; margin:0; }
    .header-meta{ font-size:11px; margin-top:4px; }

    .panel{
      border:1px solid #e6eef6; border-radius:8px;
      padding:10px; margin-top:12px; background:#f9fbfe;
    }
    .panel-title{ font-weight:bold; font-size:13px; margin:0 0 8px 0; color:#0b2f4a; }

    /* KPIs en tabla (sin flex) */
    .kpis{ width:100%; border-spacing:8px; border-collapse:separate; }
    .kpi{
      border:1px solid #d6eaf8; background:#eef6fc; padding:8px; border-radius:8px; vertical-align:top;
    }
    .kpi h4{ margin:0; font-size:11px; color:#21618c; }
    .kpi p{ margin:4px 0 0 0; font-size:16px; font-weight:700; color:#1b4f72; }
    .kpi small{ display:block; color:#5d6d7e; margin-top:3px; font-size:10px; }

    /* Tabla detalle */
    table.data{ width:100%; border-collapse:collapse; font-size:12px; }
    table.data thead th{
      background:#21618c; color:#fff; text-align:left; padding:7px; font-weight:600;
    }
    table.data tbody td{ border-bottom:1px solid #e5e7eb; padding:6px 7px; }

    .badge{
      display:inline-block; padding:2px 6px; border-radius:999px; font-size:10px;
      border:1px solid #d4efdf; color:#1e8449; background:#eafaf1;
    }
    .badge-warn{ border-color:#fae5d3; color:#ca6f1e; background:#fef5e7; }
    .badge-off{ border-color:#e5e7eb; color:#5d6d7e; background:#f4f6f7; }

    .foot{ margin-top:8px; font-size:10px; color:#6b7280; }

    /* Barras mini */
    .bar-row{ margin:3px 0 6px 0; }
    .bar-label{ font-size:11px; margin-bottom:2px; }
    .bar-wrap{
      width:100%; height:10px; background:#eef2f7; border:1px solid #dae2ec; border-radius:6px;
    }
    .bar-fill{ height:8px; margin:0; background:#7db6e6; border-radius:6px; }

    /* Grid lateral (2 columnas) */
    .grid2{ width:100%; border-spacing:8px; border-collapse:separate; }
    .col{ vertical-align:top; width:50%; }

    /* Dona mini */
    .donut-box{ display:inline-block; width:74px; height:74px; }
    .legend{ margin-top:6px; font-size:11px; color:#34495e; }
    .legend-item{ display:inline-block; margin-right:10px; }
    .dot{ width:8px; height:8px; border-radius:50%; display:inline-block; margin-right:4px; vertical-align:middle; border:1px solid #e5e7eb; }
  </style>
</head>
<body>
@php
  $periodo = $data['periodo'] ?? null;
  $kpis    = $data['kpis'] ?? [];
  $charts  = $data['charts'] ?? [];

  /* ====== Empleados: distribución por modalidad ====== */
  $mod = is_array($charts['por_modalidad'] ?? null) ? $charts['por_modalidad'] : [];
  $modSum = 0;
  foreach ($mod as $m) { $modSum += (int)($m['total'] ?? 0); }
  // Segmentos para la dona
  $donutColors = ['#4da3ff','#ff6b8a','#ffd166','#6ee7b7','#c084fc','#f59e0b'];
  $donutSegments = [];
  if ($modSum > 0) {
    foreach ($mod as $i => $m) {
      $n   = (int)($m['total'] ?? 0);
      $pct = $modSum ? ($n / $modSum) : 0;
      $donutSegments[] = [
        'label' => $m['modalidad'] ?? '—',
        'pct'   => $pct,
        'color' => $donutColors[$i % count($donutColors)],
      ];
    }
  }

  /* ====== Asistencia: % por oficina & ranking ====== */
  $off = is_array($charts['asistencia_por_oficina'] ?? null) ? $charts['asistencia_por_oficina'] : [];
  $rank = is_array($charts['ranking_ausencias'] ?? null) ? array_slice($charts['ranking_ausencias'], 0, 12) : [];
  $rankMax = 0; foreach ($rank as $r) { $rankMax = max($rankMax, (int)($r['ausencias'] ?? 0)); }
@endphp

<div class="wrap">
  {{-- ===== Encabezado ===== --}}
  <div class="header">
    <div class="topline">DIDADPOL · RRHH — {{ now()->format('d/m/Y H:i') }}</div>
    <p class="header-title">Reporte {{ strtoupper($tipo ?? '') }}</p>
    @if($periodo)
      <p class="header-meta">Período: Mes {{ $periodo['mes'] ?? '' }} / {{ $periodo['anio'] ?? '' }}</p>
    @endif
  </div>

  {{-- ===== Resumen ===== --}}
  <div class="panel">
    <p class="panel-title">Resumen</p>

    @if(($tipo ?? '') === 'empleados')
      <table class="kpis">
        <tr>
          <td class="kpi" style="width:25%;">
            <h4>Total</h4>
            <p>{{ number_format((float)($kpis['total'] ?? 0),0,'.',',') }}</p>
          </td>
          <td class="kpi" style="width:25%;">
            <h4>Activos</h4>
            <p>{{ number_format((float)($kpis['activos'] ?? 0),0,'.',',') }}</p>
            <small>Sin contrato: {{ number_format((float)($kpis['sin_contrato'] ?? 0),0,'.',',') }}</small>
          </td>
          <td class="kpi" style="width:25%;">
            <h4>Salario Promedio</h4>
            <p>L. {{ number_format((float)($kpis['salario_promedio'] ?? 0),2,'.',',') }}</p>
            <small>Mediana: L. {{ number_format((float)($kpis['salario_mediana'] ?? 0),2,'.',',') }}</small>
          </td>

          {{-- Dona mini de Modalidad (SVG) --}}
          <td class="kpi" style="width:25%;">
            <h4>Distribución por Modalidad</h4>
            @if($modSum>0)
              @php
                $r = 30; $cx = 37; $cy = 37;
                $circ = 2 * 3.141592653589793 * $r;
                $offset = 0;
              @endphp
              <div class="donut-box">
                <svg xmlns="http://www.w3.org/2000/svg" width="74" height="74" viewBox="0 0 74 74">
                  <!-- Fondo -->
                  <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}" fill="none" stroke="#e5e7eb" stroke-width="12"/>
                  @foreach($donutSegments as $seg)
                    @php
                      $len = $circ * $seg['pct'];
                    @endphp
                    <circle
                      cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}" fill="none"
                      stroke="{{ $seg['color'] }}" stroke-width="12"
                      stroke-dasharray="{{ $len }},{{ $circ - $len }}"
                      stroke-dashoffset="{{ $circ - $offset }}"
                      stroke-linecap="butt"
                      transform="rotate(-90 {{ $cx }} {{ $cy }})"
                    />
                    @php $offset += $len; @endphp
                  @endforeach
                  <!-- Agujero -->
                  <circle cx="{{ $cx }}" cy="{{ $cy }}" r="20" fill="#f9fbfe"/>
                </svg>
              </div>
              <div class="legend">
                @foreach($donutSegments as $seg)
                  @php $pctTxt = round($seg['pct']*100); @endphp
                  <span class="legend-item">
                    <span class="dot" style="background:{{ $seg['color'] }}"></span>{{ $seg['label'] }} {{ $pctTxt }}%
                  </span>
                @endforeach
              </div>
            @else
              <small style="color:#6b7280;">Sin datos de modalidad.</small>
            @endif
          </td>
        </tr>
      </table>

    @elseif(($tipo ?? '') === 'planilla')
      <table class="kpis">
        <tr>
          <td class="kpi">
            <h4>Empleados en Planilla</h4>
            <p>{{ number_format((float)($kpis['total_empleados'] ?? 0),0,'.',',') }}</p>
          </td>
          <td class="kpi">
            <h4>Total Devengado</h4>
            <p>L. {{ number_format((float)($kpis['total_devengado'] ?? 0),2,'.',',') }}</p>
          </td>
          <td class="kpi">
            <h4>Total Deducciones</h4>
            <p>L. {{ number_format((float)($kpis['total_deducciones'] ?? 0),2,'.',',') }}</p>
          </td>
          <td class="kpi">
            <h4>Total Neto a Pagar</h4>
            <p>L. {{ number_format((float)($kpis['total_neto_pagar'] ?? 0),2,'.',',') }}</p>
          </td>
        </tr>
        <tr>
          <td class="kpi">
            <h4>Salario Promedio</h4>
            <p>L. {{ number_format((float)($kpis['salario_promedio'] ?? 0),2,'.',',') }}</p>
          </td>
          <td class="kpi">
            <h4>Deducción Promedio</h4>
            @php
              $dedProm = (float)($kpis['deduccion_promedio'] ?? 0);
              $porcDed = (float)($kpis['porcentaje_deduccion'] ?? 0);
            @endphp
            <p>L. {{ number_format($dedProm,2,'.',',') }}</p>
            <small>Carga promedio: {{ number_format($porcDed,2,'.',',') }}%</small>
          </td>
        </tr>
      </table>

    @else {{-- asistencia --}}
      <table class="kpis">
        <tr>
          <td class="kpi">
            <h4>Empleados</h4>
            <p>{{ number_format((float)($kpis['empleados'] ?? 0),0,'.',',') }}</p>
          </td>
          <td class="kpi">
            <h4>Asistencia (%)</h4>
            <p>{{ number_format((float)($kpis['asistencia_pct'] ?? 0),2,'.',',') }}%</p>
            <small>Registros: {{ number_format((float)($kpis['total_registros'] ?? 0),0,'.',',') }}</small>
          </td>
        </tr>
        <tr>
          <td class="kpi">
            <h4>Horas Totales</h4>
            <p>{{ number_format((float)($kpis['horas_totales'] ?? 0),2,'.',',') }}</p>
            <small>Promedio/emp: {{ number_format((float)($kpis['horas_promedio_empleado'] ?? 0),2,'.',',') }}</small>
          </td>
          <td class="kpi">
            @if($periodo)
              <h4>Período</h4>
              <p>{{ $periodo['mes'] ?? '-' }}/{{ $periodo['anio'] ?? '-' }}</p>
              <small>Días del mes: {{ $periodo['dias_mes'] ?? '-' }}</small>
            @endif
          </td>
        </tr>
      </table>
    @endif
  </div>

  {{-- ===== Paneles laterales para ASISTENCIA (2 a la par) ===== --}}
  @if(($tipo ?? '') === 'asistencia')
    <table class="grid2">
      <tr>
        {{-- Asistencia por oficina --}}
        <td class="col">
          <div class="panel">
            <p class="panel-title">Asistencia % por Oficina</p>
            @if(is_array($off) && count($off))
              @foreach($off as $o)
                @php
                  $label = $o['nombre_oficina'] ?? '—';
                  $pct   = (float)($o['asistencia_pct'] ?? 0);
                  $w     = max(2, min(100, (int)round($pct)));
                @endphp
                <div class="bar-row">
                  <div class="bar-label">{{ $label }} — {{ number_format($pct,2,'.',',') }}%</div>
                  <div class="bar-wrap"><div class="bar-fill" style="width: {{ $w }}%;"></div></div>
                </div>
              @endforeach
            @else
              <small style="color:#6b7280;">Sin datos por oficina.</small>
            @endif
          </div>
        </td>

        {{-- Ranking de ausencias --}}
        <td class="col">
          <div class="panel">
            <p class="panel-title">Ranking de Ausencias (Top 12)</p>
            @if($rankMax > 0)
              @foreach($rank as $r)
                @php
                  $a  = (int)($r['ausencias'] ?? 0);
                  $nm = $r['nombre'] ?? '—';
                  $w  = $rankMax ? (int)round(($a/$rankMax)*100) : 0;
                @endphp
                <div class="bar-row">
                  <div class="bar-label">{{ $nm }} — {{ $a }}</div>
                  <div class="bar-wrap" style="height:8px;">
                    <div class="bar-fill" style="height:6px; width: {{ max(2,$w) }}%;"></div>
                  </div>
                </div>
              @endforeach
            @else
              <small style="color:#6b7280;">Sin datos de ausencias.</small>
            @endif
          </div>
        </td>
      </tr>
    </table>
  @endif

  {{-- ===== Detalle ===== --}}
  <div class="panel">
    <p class="panel-title">Detalle</p>

    @if(($tipo ?? '') === 'empleados')
      <table class="data">
        <thead>
          <tr>
            <th>Nombre</th>
            <th>DNI</th>
            <th>Oficina</th>
            <th>Puesto</th>
            <th>Modalidad</th>
            <th>Nivel</th>
            <th>Contrato</th>
            <th style="text-align:right;">Salario</th>
          </tr>
        </thead>
        <tbody>
          @forelse(($rows ?? []) as $r)
            <tr>
              <td>{{ $r['nombre_completo'] ?? '-' }}</td>
              <td>{{ $r['dni'] ?? '-' }}</td>
              <td>{{ $r['nombre_oficina'] ?? '-' }}</td>
              <td>{{ $r['puesto'] ?? '-' }}</td>
              <td>{{ $r['modalidad'] ?? '-' }}</td>
              <td>{{ $r['nivel_educativo'] ?? '-' }}</td>
              <td>
                @php $c = $r['contrato_activo'] ?? null; @endphp
                @if($c===true)
                  <span class="badge">Activo</span>
                @elseif($c===false)
                  <span class="badge badge-warn">Inactivo</span>
                @else
                  <span class="badge badge-off">Sin contrato</span>
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
        </tbody>
      </table>

    @elseif(($tipo ?? '') === 'planilla')
      <table class="data">
        <thead>
          <tr>
            <th>Nombre</th>
            <th>DNI</th>
            <th>Puesto</th>
            <th style="text-align:right;">Salario Bruto</th>
            <th style="text-align:right;">Total Deducciones</th>
            <th style="text-align:right;">Neto a Pagar</th>
            <th>Período</th>
          </tr>
        </thead>
        <tbody>
          @forelse(($rows ?? []) as $r)
            <tr>
              <td>{{ $r['nombre'] ?? '-' }}</td>
              <td>{{ $r['dni'] ?? '-' }}</td>
              <td>{{ $r['cargo'] ?? '-' }}</td>
              <td style="text-align:right;">
                L. {{ number_format((float)($r['salariobruto'] ?? 0), 2, '.', ',') }}
              </td>
              <td style="text-align:right;">
                L. {{ number_format((float)($r['total_deducciones'] ?? 0), 2, '.', ',') }}
              </td>
              <td style="text-align:right;">
                L. {{ number_format((float)($r['total_a_pagar'] ?? 0), 2, '.', ',') }}
              </td>
              <td>{{ $r['periodo'] ?? '-' }}</td>
            </tr>
          @empty
            <tr><td colspan="7" style="color:#6b7280;">Sin registros.</td></tr>
          @endforelse
        </tbody>
      </table>

    @else {{-- asistencia --}}
      <table class="data">
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Oficina</th>
            <th>Puesto</th>
            <th style="text-align:right;">Días Presentes</th>
            <th style="text-align:right;">Horas Mes</th>
          </tr>
        </thead>
        <tbody>
          @forelse(($rows ?? []) as $r)
            <tr>
              <td>{{ $r['nombre'] ?? '-' }}</td>
              <td>{{ $r['nombre_oficina'] ?? '-' }}</td>
              <td>{{ $r['puesto'] ?? '-' }}</td>
              <td style="text-align:right;">{{ number_format((float)($r['dias_presentes'] ?? 0),0,'.',',') }}</td>
              <td style="text-align:right;">{{ number_format((float)($r['horas_mes'] ?? 0),2,'.',',') }}</td>
            </tr>
          @empty
            <tr><td colspan="5" style="color:#6b7280;">Sin registros.</td></tr>
          @endforelse
        </tbody>
      </table>
    @endif

    <p class="foot">Generado automáticamente por DIDADPOL · RRHH</p>
  </div>
</div>
</body>
</html>
