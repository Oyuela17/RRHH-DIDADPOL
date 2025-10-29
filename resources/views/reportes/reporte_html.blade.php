{{-- resources/views/reportes/reporte_html.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>{{ ucfirst($tipo ?? 'Reporte') }} — DIDADPOL</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <style>
    :root{
      --primary:#1b4f72;
      --primary-2:#21618c;
      --bg:#f4f7fb;
      --card:#ffffff;
      --muted:#5d6d7e;
      --ok:#27ae60;
      --warn:#f39c12;
      --danger:#c0392b;
      --line:#e5e7eb;
    }
    *{ box-sizing:border-box; font-family: "Segoe UI", Arial, sans-serif; }
    body{ margin:0; background:var(--bg); color:#1b2631; }
    .wrap{ max-width:1120px; margin:24px auto; padding:0 16px; }

    .header{
      background:linear-gradient(90deg, var(--primary), var(--primary-2));
      color:#fff; padding:16px 20px; border-radius:12px;
      display:flex; align-items:center; justify-content:space-between;
      box-shadow:0 8px 20px rgba(27,79,114,.15);
    }
    .brand{ display:flex; gap:12px; align-items:center; }
    .brand .logo{
      width:40px; height:40px; border-radius:10px; background:#fff1;
      display:grid; place-items:center; font-weight:800; letter-spacing:.5px;
      border:1px solid #fff2; color:#fff;
    }
    .brand h1{ font-size:18px; margin:0; }
    .meta{ font-size:13px; opacity:.9; text-align:right; }

    .panel{
      background:var(--card); border:1px solid var(--line); border-radius:12px;
      margin-top:16px; padding:16px; box-shadow:0 4px 18px rgba(0,0,0,.04);
    }
    .title{ font-size:18px; font-weight:700; margin:0 0 12px; color:#1b2631; }

    .kpis{ display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:12px; }
    .kpi{
      background:#eaf2f8; border:1px solid #d6eaf8; border-radius:12px; padding:14px; position:relative;
    }
    .kpi h4{ margin:0; font-size:12px; letter-spacing:.3px; color:var(--primary-2); }
    .kpi p{ margin:6px 0 0; font-size:20px; font-weight:800; color:var(--primary); }
    .kpi small{ display:block; color:var(--muted); margin-top:4px; }

    /* tarjeta-dona compacta */
    .kpi-donut { padding:12px 10px 8px 10px; }
    .donut-box{ height:130px; display:flex; align-items:center; justify-content:center; }
    .donut-legend{ display:flex; gap:10px; align-items:center; margin-top:6px; flex-wrap:wrap; font-size:12px; color:#34495e; }
    .dot{ width:10px; height:10px; border-radius:50%; display:inline-block; margin-right:6px; border:1px solid #e5e7eb; }

    table{ width:100%; border-collapse:collapse; margin-top:14px; font-size:13px; }
    thead th{ background:var(--primary-2); color:#fff; text-align:left; padding:10px; }
    tbody td{ padding:9px 10px; border-bottom:1px solid var(--line); }
    tbody tr:nth-child(even){ background:#f9fbfe; }

    .badge{ padding:3px 8px; border-radius:999px; font-size:12px; }
    .b-ok{ background:#eafaf1; color:#1e8449; border:1px solid #d4efdf; }
    .b-warn{ background:#fef5e7; color:#ca6f1e; border:1px solid #fae5d3; }
    .b-off{ background:#f4f6f7; color:#5d6d7e; border:1px solid #e5e7eb; }

    .footnote{ margin:14px 0 0; font-size:12px; color:#6b7280; }
  </style>

  {{-- Chart.js para la dona compacta --}}
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
<div class="wrap">

  {{-- ===== Encabezado ===== --}}
  <div class="header">
    <div class="brand">
      <div class="logo">DP</div>
      <div>
        <h1>Reporte {{ strtoupper($tipo ?? '') }}</h1>
        @php $periodo = $data['periodo'] ?? null; @endphp
        @if($periodo)
          <div style="font-size:12px; opacity:.9;">
            Período: Mes {{ $periodo['mes'] ?? '' }} / {{ $periodo['anio'] ?? '' }}
          </div>
        @endif
      </div>
    </div>
    <div class="meta">
      <div>DIDADPOL · RRHH</div>
      <div>{{ now()->format('d/m/Y H:i') }}</div>
    </div>
  </div>

  {{-- ===== KPIs ===== --}}
  @php
    $kpis = $data['kpis'] ?? [];
    $mod = is_array($data['charts']['por_modalidad'] ?? null) ? $data['charts']['por_modalidad'] : [];
    $modLabels = collect($mod)->pluck('modalidad')->map(fn($v)=>$v ?? '—')->values();
    $modValues = collect($mod)->pluck('total')->map(fn($v)=>(int)$v)->values();
  @endphp

  <div class="panel">
    <div class="title">Resumen</div>
    <div class="kpis">
      @if(($tipo ?? '') === 'empleados')
        <div class="kpi">
          <h4>Total</h4>
          <p>{{ number_format((float)($kpis['total'] ?? 0),0,'.',',') }}</p>
        </div>

        <div class="kpi">
          <h4>Activos</h4>
          <p>{{ number_format((float)($kpis['activos'] ?? 0),0,'.',',') }}</p>
          <small>Sin contrato: {{ number_format((float)($kpis['sin_contrato'] ?? 0),0,'.',',') }}</small>
        </div>

        <div class="kpi">
          <h4>Salario Promedio</h4>
          <p>L. {{ number_format((float)($kpis['salario_promedio'] ?? 0),2,'.',',') }}</p>
          <small>Mediana: L. {{ number_format((float)($kpis['salario_mediana'] ?? 0),2,'.',',') }}</small>
        </div>

        {{-- 🚀 Aquí va la dona compacta de Modalidad (reemplaza Antigüedad) --}}
        <div class="kpi kpi-donut">
          <h4>Distribución por Modalidad</h4>
          <div class="donut-box">
            <canvas id="donutModalidad" height="130"></canvas>
          </div>
          <div class="donut-legend" id="legendModalidad"></div>
        </div>
      @else
        <div class="kpi">
          <h4>Empleados</h4>
          <p>{{ number_format((float)($kpis['empleados'] ?? 0),0,'.',',') }}</p>
        </div>
        <div class="kpi">
          <h4>Asistencia (%)</h4>
          <p>{{ number_format((float)($kpis['asistencia_pct'] ?? 0),2,'.',',') }}%</p>
          <small>Registros: {{ number_format((float)($kpis['total_registros'] ?? 0),0,'.',',') }}</small>
        </div>
        <div class="kpi">
          <h4>Horas Totales</h4>
          <p>{{ number_format((float)($kpis['horas_totales'] ?? 0),2,'.',',') }}</p>
          <small>Promedio/emp: {{ number_format((float)($kpis['horas_promedio_empleado'] ?? 0),2,'.',',') }}</small>
        </div>
        <div class="kpi">
          <h4>Período</h4>
          <p>{{ $periodo['mes'] ?? '-' }}/{{ $periodo['anio'] ?? '-' }}</p>
          <small>Días del mes: {{ $periodo['dias_mes'] ?? '-' }}</small>
        </div>
      @endif
    </div>
  </div>

  {{-- ===== Tabla Detalle ===== --}}
  <div class="panel">
    <div class="title">Detalle</div>

    @if(($tipo ?? '') === 'empleados')
      <table>
        <thead>
        <tr>
          <th>Nombre</th>
          <th>DNI</th>
          <th>Oficina</th>
          <th>Puesto</th>
          <th>Modalidad</th>
          <th>Nivel</th>
          <th>Contrato</th>
          <th>Salario</th>
        </tr>
        </thead>
        <tbody>
        @foreach(($rows ?? []) as $r)
          <tr>
            <td>{{ $r['nombre_completo'] ?? '-' }}</td>
            <td>{{ $r['dni'] ?? '-' }}</td>
            <td>{{ $r['nombre_oficina'] ?? '-' }}</td>
            <td>{{ $r['puesto'] ?? '-' }}</td>
            <td>{{ $r['modalidad'] ?? '-' }}</td>
            <td>{{ $r['nivel_educativo'] ?? '-' }}</td>
            <td>
              @php $c = $r['contrato_activo'] ?? null; @endphp
              @if($c===true)<span class="badge b-ok">Activo</span>
              @elseif($c===false)<span class="badge b-warn">Inactivo</span>
              @else<span class="badge b-off">Sin contrato</span>@endif
            </td>
            <td>
              @if(isset($r['salario']) && $r['salario']!=='')
                L. {{ number_format((float)$r['salario'],2,'.',',') }}
              @else - @endif
            </td>
          </tr>
        @endforeach
        </tbody>
      </table>
    @else
      <table>
        <thead>
        <tr>
          <th>Nombre</th>
          <th>Oficina</th>
          <th>Puesto</th>
          <th>Días Presentes</th>
          <th>Horas Mes</th>
        </tr>
        </thead>
        <tbody>
        @foreach(($rows ?? []) as $r)
          <tr>
            <td>{{ $r['nombre'] ?? '-' }}</td>
            <td>{{ $r['nombre_oficina'] ?? '-' }}</td>
            <td>{{ $r['puesto'] ?? '-' }}</td>
            <td>{{ number_format((float)($r['dias_presentes'] ?? 0),0,'.',',') }}</td>
            <td>{{ number_format((float)($r['horas_mes'] ?? 0),2,'.',',') }}</td>
          </tr>
        @endforeach
        </tbody>
      </table>
      <div class="footnote">
        * Período: mes {{ $periodo['mes'] ?? '-' }} / {{ $periodo['anio'] ?? '-' }} ·
        Días del mes: {{ $periodo['dias_mes'] ?? '-' }}
      </div>
    @endif
  </div>

  <div class="footnote">Generado automáticamente por DIDADPOL · RRHH</div>
</div>

@if(($tipo ?? '') === 'empleados')
<script>
  (function(){
    const labels = @json($modLabels);
    const values = @json($modValues);

    const colors = ['#4da3ff','#ff6b8a','#ffd166','#6ee7b7','#c084fc'];
    const ctx = document.getElementById('donutModalidad');
    if (ctx && values.length) {
      new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels,
          datasets: [{ data: values, backgroundColor: colors.slice(0, values.length) }]
        },
        options: {
          cutout: '65%',
          plugins: { legend: { display: false } },
          responsive: true,
          maintainAspectRatio: false
        }
      });

      // leyenda compacta
      const legend = document.getElementById('legendModalidad');
      if (legend) {
        legend.innerHTML = labels.map((l,i)=>(
          `<span><span class="dot" style="background:${colors[i]}"></span>${l}</span>`
        )).join('');
      }
    } else {
      const legend = document.getElementById('legendModalidad');
      if (legend) legend.innerHTML = '<span style="color:#6b7280;">Sin datos</span>';
    }
  })();
</script>
@endif
</body>
</html>
