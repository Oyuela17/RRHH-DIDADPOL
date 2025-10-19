@extends('layouts.dashboard')
@section('title', 'Mi Timesheet')
<link rel="stylesheet" href="{{ asset('css/asistencia.css') }}">

@section('content')

@if (session('mensaje'))
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const mensaje = @json(session('mensaje'));
    const tipo = mensaje.includes('entrada y salida') ? 'warning' : 'success';

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

<div class="asistencia-wrapper">
  <div class="cabecera-timesheet">
    <h2>Mi Timesheet</h2>
    <div class="hora-actual-reloj">
      <i class="fas fa-clock icono-reloj"></i>
      <div class="hora" id="horaTexto">--:--:--</div>
    </div>
  </div>

  <div class="contenedor-principal-timesheet">
    <!-- Punch -->
    <div class="card-central card-con-circulo">
      <div class="fecha-dia">
        Hoy {{ \Carbon\Carbon::now('America/Tegucigalpa')->translatedFormat('d M Y') }}
      </div>

      <div class="sub-text">
        @php
          // $ultimoPunch ya viene formateado desde el controlador (h:i A) o '-'
          $horaMostrar = $ultimoPunch;
        @endphp
        @if ($accion === 'Entrada')
          Última Salida: {{ $horaMostrar }}
        @else
          Última Entrada: {{ $horaMostrar }}
        @endif
      </div>

      <!-- Reloj circular -->
      <div class="progreso-circular">
        <svg class="circle-chart" viewBox="0 0 36 36">
          <path class="circle-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
          <path class="circle" stroke-dasharray="0, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" id="circleProgress" />
          <text x="18" y="20.35" class="circle-text" id="circleTime">{{ number_format($estadisticas['hoy'] ?? 0, 2) }} hrs</text>
        </svg>
      </div>

      @if (!session('mensaje') || !\Illuminate\Support\Str::contains(session('mensaje'), 'entrada y salida'))
      <form method="POST" action="{{ route('asistencia.punch') }}" id="formAsistencia">
        @csrf
        <input type="hidden" name="tipo_registro" id="tipo_registro" value="{{ $accion }}">
        <button type="submit" class="btn-punch" id="btnPunch">
          {{ $accion === 'Entrada' ? 'Registrar Entrada' : 'Registrar Salida' }}
        </button>
      </form>
      @endif
    </div>

    <!-- Estadísticas -->
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
    </div>
  </div>

  <!-- Historial -->
  <div class="historial-timesheet">
    <h4>Historial</h4>
    <table class="tabla-historial">
      <thead>
        <tr>
          <th>Fecha</th>
          <th>Entrada</th>
          <th>Salida</th>
          <th>Observación</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($historial as $registro)
        <tr>
          <td>{{ \Carbon\Carbon::parse($registro['fecha'])->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}</td>
          <td>
            {{ isset($registro['hora_entrada'])
                ? \Carbon\Carbon::parse(explode('.', $registro['hora_entrada'])[0], 'America/Tegucigalpa')->format('h:i A')
                : '-' }}
          </td>
          <td>
            {{ isset($registro['hora_salida'])
                ? \Carbon\Carbon::parse(explode('.', $registro['hora_salida'])[0], 'America/Tegucigalpa')->format('h:i A')
                : '-' }}
          </td>
          <td>{{ $registro['observacion'] ?? '-' }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

<script>
  function actualizarHora() {
    const ahora = new Date();
    const hora = ahora.toLocaleTimeString('es-HN', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
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

    // Inicializa el círculo con el progreso actual
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
  });
</script>
@endsection
