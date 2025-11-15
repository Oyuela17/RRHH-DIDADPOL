@extends('layouts.dashboard')
@section('title', 'Mi Timesheet')
<link rel="stylesheet" href="{{ asset('css/asistencia.css') }}">

@section('content')

{{-- ============ FLASH: ASISTENCIA (Entrada / Salida) ============ --}}
@if (session('mensaje') || session('mensaje_asistencia'))
<script>
  document.addEventListener('DOMContentLoaded', () => {
    // Compatibilidad: usa mensaje_asistencia si existe, si no usa mensaje
    const mensaje = @json(session('mensaje_asistencia') ?? session('mensaje'));
    const tipo = mensaje && mensaje.includes('entrada y salida') ? 'warning' : 'success';

    Swal.fire({
      icon: tipo,
      title: tipo === 'success' ? 'Asistencia registrada' : 'Atención',
      text: tipo === 'success'
        ? '{{ $accion === "Entrada" ? "Hora de entrada registrada a las" : "Hora de salida registrada a las" }} {{ $ultimoPunch }}'
        : mensaje,
      confirmButtonColor: tipo === 'success' ? '#007bff' : '#ffc107',
      timer: 4000,
      timerProgressBar: true
    });
  });
</script>
@endif

{{-- ============ FLASH: ALMUERZO (Inicio / Fin) ============ --}}
@if (session('mensaje_almuerzo'))
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const mensajeAlmuerzo = @json(session('mensaje_almuerzo'));

    Swal.fire({
      icon: 'success',
      title: 'Almuerzo',
      text: mensajeAlmuerzo,
      confirmButtonColor: '#10b981',
      timer: 4000,
      timerProgressBar: true
    });
  });
</script>
@endif

@php
  // Datos de almuerzo para la tarjeta (formato 'hh:mm AM')
  $almuerzoInicioStr = $actividadHoy[0]['almuerzo_inicio'] ?? null;
  $almuerzoFinStr    = $actividadHoy[0]['almuerzo_fin'] ?? null;

  if (!$almuerzoInicioStr && !$almuerzoFinStr) {
      $estadoAlmuerzo = 'No iniciado';
  } elseif ($almuerzoInicioStr && !$almuerzoFinStr) {
      $estadoAlmuerzo = 'En curso';
  } elseif ($almuerzoInicioStr && $almuerzoFinStr) {
      $estadoAlmuerzo = 'Finalizado';
  } else {
      $estadoAlmuerzo = 'No iniciado';
  }
@endphp

<div class="asistencia-wrapper">
  {{-- Encabezado --}}
  <div class="cabecera-timesheet">
    <h2>Mi Timesheet</h2>
    <div class="hora-actual-reloj">
      <i class="fas fa-clock icono-reloj"></i>
      <div class="hora" id="horaTexto">--:--:--</div>
    </div>
  </div>

  <div class="contenedor-principal-timesheet">
    {{-- Tarjeta central con punch --}}
    <div class="card-central card-con-circulo">
      <div class="fecha-dia">
        Hoy {{ \Carbon\Carbon::now('America/Tegucigalpa')->translatedFormat('d M Y') }}
      </div>

      <div class="sub-text">
        @php $horaMostrar = $ultimoPunch; @endphp
        @if ($accion === 'Entrada')
          Última Salida: {{ $horaMostrar }}
        @else
          Última Entrada: {{ $horaMostrar }}
        @endif
      </div>

      {{-- Reloj circular --}}
      <div class="progreso-circular">
        <svg class="circle-chart" viewBox="0 0 36 36">
          <path class="circle-bg"
                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
          <path class="circle"
                stroke-dasharray="0, 100"
                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                id="circleProgress" />
          <text x="18" y="20.35" class="circle-text" id="circleTime">
            {{ number_format($estadisticas['hoy'] ?? 0, 2) }} hrs
          </text>
        </svg>
      </div>

      @if (!(session('mensaje') || session('mensaje_asistencia')) || !\Illuminate\Support\Str::contains(session('mensaje_asistencia') ?? session('mensaje', ''), 'entrada y salida'))
      <form method="POST" action="{{ route('asistencia.punch') }}" id="formAsistencia">
        @csrf
        <input type="hidden" name="tipo_registro" id="tipo_registro" value="{{ $accion }}">
        <button type="submit" class="btn-punch" id="btnPunch">
          {{ $accion === 'Entrada' ? 'Registrar Entrada' : 'Registrar Salida' }}
        </button>
      </form>
      @endif
    </div>

    {{-- Tarjetas de estadísticas --}}
    <div class="estadisticas-tarjetas" id="estadisticasContainer">
      <div class="card-estadistica">
        <div class="icono orange"><i class="fas fa-clock"></i></div>
        <div>
          <span class="label">Hoy</span>
          <span class="valor" id="estadHoy">{{ number_format($estadisticas['hoy'] ?? 0, 2) }} / 8 hrs</span>
        </div>
      </div>

      <div class="card-estadistica">
        <div class="icono yellow"><i class="fas fa-calendar-week"></i></div>
        <div>
          <span class="label">Esta semana</span>
          <span class="valor" id="estadSemana">{{ number_format($estadisticas['semana'] ?? 0, 2) }} / 40 hrs</span>
        </div>
      </div>

      <div class="card-estadistica">
        <div class="icono teal"><i class="fas fa-calendar-alt"></i></div>
        <div>
          <span class="label">Este mes</span>
          <span class="valor" id="estadMes">{{ number_format($estadisticas['mes'] ?? 0, 2) }} / 160 hrs</span>
        </div>
      </div>

      <div class="card-estadistica">
        <div class="icono blue"><i class="fas fa-hourglass-half"></i></div>
        <div>
          <span class="label">Restantes</span>
          <span class="valor" id="estadRestantes">{{ number_format($estadisticas['restantes'] ?? 0, 2) }} hrs</span>
        </div>
      </div>

      <div class="card-estadistica">
        <div class="icono purple"><i class="fas fa-plus-circle"></i></div>
        <div>
          <span class="label">Compensatorio</span>
          <span class="valor" id="estadExtra">{{ number_format($estadisticas['extra'] ?? 0, 2) }} hrs</span>
        </div>
      </div>

      {{-- NUEVA TARJETA: ALMUERZO --}}
      <div class="card-estadistica card-almuerzo">
        <div class="icono green"><i class="fas fa-utensils"></i></div>
        <div class="almuerzo-contenido">
          <span class="label">Almuerzo</span>
          <span class="valor" id="almuerzoEstado">{{ $estadoAlmuerzo }}</span>
          <div class="almuerzo-tiempo" id="almuerzoTiempo">
            @if ($estadoAlmuerzo === 'No iniciado')
              —
            @elseif ($estadoAlmuerzo === 'En curso')
              Calculando…
            @else
              Completado
            @endif
          </div>

          @if ($estadoAlmuerzo !== 'Finalizado')
            <form method="POST" action="{{ route('asistencia.almuerzo') }}" id="formAlmuerzo">
              @csrf
              <button type="submit"
                      class="btn-punch btn-almuerzo"
                      id="btnAlmuerzo">
                {{ $estadoAlmuerzo === 'No iniciado' ? 'Iniciar almuerzo' : 'Finalizar almuerzo' }}
              </button>
            </form>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- Historial con paginación Bootstrap-5 --}}
  <div class="historial-timesheet">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
      <h4 style="margin:0;">Historial</h4>

      {{-- Selector por página (opcional) --}}
      <form method="GET" action="{{ url()->current() }}" style="display:flex;align-items:center;gap:8px;">
        @foreach(request()->except(['per_page','page']) as $k => $v)
          <input type="hidden" name="{{ $k }}" value="{{ $v }}">
        @endforeach
        <label style="font-size:13px;color:#6b7280;">Mostrar</label>
        <select name="per_page" onchange="this.form.submit()" class="form-select"
                style="height:34px;padding:0 8px;width:84px;font-size:13px;">
          @php $pp = (int)request('per_page', 10); @endphp
          @foreach([5,10,15,20,25,30] as $n)
            <option value="{{ $n }}" {{ $pp===$n ? 'selected' : '' }}>{{ $n }}</option>
          @endforeach
        </select>
        <span style="font-size:13px;color:#6b7280;">filas</span>
      </form>
    </div>

    <table class="tabla-historial" style="margin-top:10px;">
      <thead>
        <tr>
          <th>Fecha</th>
          <th>Entrada</th>
          <th>Salida</th>
          <th>Almuerzo inicio</th>
          <th>Almuerzo fin</th>
          <th>Observación</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($historial as $registro)
          <tr>
            {{-- Fecha --}}
            <td>
              {{ \Carbon\Carbon::parse($registro['fecha'])
                    ->locale('es')
                    ->isoFormat('D [de] MMMM [de] YYYY') }}
            </td>

            {{-- Entrada --}}
            <td>
              {{ isset($registro['hora_entrada'])
                  ? \Carbon\Carbon::parse(
                        explode('.', $registro['hora_entrada'])[0],
                        'America/Tegucigalpa'
                    )->format('h:i A')
                  : '-' }}
            </td>

            {{-- Salida --}}
            <td>
              {{ isset($registro['hora_salida'])
                  ? \Carbon\Carbon::parse(
                        explode('.', $registro['hora_salida'])[0],
                        'America/Tegucigalpa'
                    )->format('h:i A')
                  : '-' }}
            </td>

            {{-- Almuerzo inicio --}}
            <td>
              {{ isset($registro['almuerzo_inicio']) && $registro['almuerzo_inicio']
                  ? \Carbon\Carbon::parse(
                        explode('.', $registro['almuerzo_inicio'])[0],
                        'America/Tegucigalpa'
                    )->format('h:i A')
                  : '-' }}
            </td>

            {{-- Almuerzo fin --}}
            <td>
              {{ isset($registro['almuerzo_fin']) && $registro['almuerzo_fin']
                  ? \Carbon\Carbon::parse(
                        explode('.', $registro['almuerzo_fin'])[0],
                        'America/Tegucigalpa'
                    )->format('h:i A')
                  : '-' }}
            </td>

            {{-- Observación --}}
            <td>{{ $registro['observacion'] ?? '-' }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="6" style="text-align:center;padding:16px;">Sin registros.</td>
          </tr>
        @endforelse
      </tbody>
    </table>

    {{-- Controles de paginación centrados (tema Bootstrap-5) --}}
    @if ($historial->hasPages())
      <div style="display:flex;justify-content:center;margin-top:14px;">
        {{ $historial->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
      </div>
    @endif
  </div>
</div>

<script>
  function actualizarHora() {
    const ahora = new Date();
    const hora = ahora.toLocaleTimeString('es-HN', {
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit'
    });
    document.getElementById('horaTexto').textContent = hora;
  }
  setInterval(actualizarHora, 1000);
  actualizarHora();

  document.addEventListener('DOMContentLoaded', () => {
    const accionActual = "{{ $accion }}";
    const circle = document.getElementById('circleProgress');
    const texto = document.getElementById('circleTime');

    let horasBase = parseFloat({{ $estadisticas['hoy'] ?? 0 }});
    let semana = parseFloat({{ $estadisticas['semana'] ?? 0 }});
    let mes = parseFloat({{ $estadisticas['mes'] ?? 0 }});

    let segundosHoy = Math.round(horasBase * 3600);
    const totalJornada = 8 * 3600;

    const porcentajeInicial = Math.min((segundosHoy / totalJornada) * 100, 100);
    circle.setAttribute('stroke-dasharray', `${porcentajeInicial}, 100`);

    function actualizarProgreso() {
      if (accionActual !== 'Salida') return;

      segundosHoy += 1;

      const horas = segundosHoy / 3600;
      const porcentaje = Math.min((segundosHoy / totalJornada) * 100, 100);

      circle.setAttribute('stroke-dasharray', `${porcentaje}, 100`);
      texto.textContent = `${horas.toFixed(2)} hrs`;

      document.getElementById('estadHoy').textContent = `${horas.toFixed(2)} / 8 hrs`;
      document.getElementById('estadSemana').textContent = `${(semana + horas - horasBase).toFixed(2)} / 40 hrs`;
      document.getElementById('estadMes').textContent = `${(mes + horas - horasBase).toFixed(2)} / 160 hrs`;

      const totalMes = mes + horas - horasBase;
      const restantes = 160 - totalMes;
      const extra = totalMes > 160 ? totalMes - 160 : 0;

      document.getElementById('estadRestantes').textContent = `${restantes.toFixed(2)} hrs`;
      document.getElementById('estadExtra').textContent = `${extra.toFixed(2)} hrs`;
    }

    if (accionActual === 'Salida') {
      setInterval(actualizarProgreso, 1000);
    }

    // ==========================
    // Cronómetro ALMUERZO (frontal)
    // ==========================
    const almuerzoInicioStr = @json($almuerzoInicioStr);
    const almuerzoFinStr    = @json($almuerzoFinStr);
    const estadoAlmuerzoPhp = @json($estadoAlmuerzo);
    const duracionAlmuerzoMin = 60; // duración teórica en minutos

    const lblEstado   = document.getElementById('almuerzoEstado');
    const lblTiempo   = document.getElementById('almuerzoTiempo');

    function parseHora12ToDate(horaStr) {
      if (!horaStr) return null;
      const partes = horaStr.split(' ');
      if (partes.length < 2) return null;
      const time = partes[0];
      const ampm = partes[1].toUpperCase();
      let [h, m] = time.split(':').map(Number);
      if (ampm.startsWith('P') && h < 12) h += 12;
      if (ampm.startsWith('A') && h === 12) h = 0;
      const now = new Date();
      return new Date(now.getFullYear(), now.getMonth(), now.getDate(), h, m, 0);
    }

    function actualizarAlmuerzo() {
      if (!lblEstado || !lblTiempo) return;

      if (!almuerzoInicioStr) {
        lblEstado.textContent = 'No iniciado';
        lblTiempo.textContent = '—';
        return;
      }

      const inicio = parseHora12ToDate(almuerzoInicioStr);
      if (!inicio) {
        lblTiempo.textContent = '—';
        return;
      }

      if (almuerzoFinStr) {
        lblEstado.textContent = 'Finalizado';
        lblTiempo.textContent = 'Completado';
        return;
      }

      // En curso
      const ahora = new Date();
      const finTeorico = new Date(inicio.getTime() + duracionAlmuerzoMin * 60000);
      const diffMs = finTeorico - ahora;
      const diffMin = Math.round(diffMs / 60000);

      lblEstado.textContent = 'En curso';

      if (diffMin >= 0) {
        lblTiempo.textContent = diffMin + ' min restantes';
      } else {
        lblTiempo.textContent = '+' + Math.abs(diffMin) + ' min extra';
      }
    }

    if (estadoAlmuerzoPhp === 'En curso') {
      actualizarAlmuerzo();
      setInterval(actualizarAlmuerzo, 60000);
    } else {
      actualizarAlmuerzo();
    }
  });
</script>
@endsection
