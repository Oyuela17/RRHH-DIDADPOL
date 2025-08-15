@extends('layouts.dashboard')

@section('title', 'Inicio')

@section('content')
<div class="home-wrapper">
  
  <div class="container-fluid dashboard-metrics">

    {{-- Encabezado: Bienvenida con el nombre (seguro si no hay sesión) --}}
   <h2 class="my-4 text-center bienvenida-titulo">
  Bienvenido <span>{{ strtoupper(optional(auth()->user())->name ?? '') }}</span>
</h2>

    <div class="metrics-scroll-row">

      {{-- Tarjeta de Asistencia como métrica --}}
      @php
        $accion  = $estadoAsistencia['accion'] ?? 'Entrada';
        $variant = 'bg-purple';
        $icon    = 'fa-hand-sparkles';
        if ($accion === 'Salida')     { $variant = 'bg-orange'; $icon = 'fa-hourglass-half'; }
        if ($accion === 'Completado') { $variant = 'bg-teal';   $icon = 'fa-check-circle';   }
      @endphp

      <div class="metrics-item metrics-card {{ $variant }}">
        <div class="metrics-icon">
          <i class="fas {{ $icon }}"></i>
        </div>
        <div class="metrics-info">
          <h5 class="metrics-title">
            Asistencia
            @if(!empty($accion))
              <span class="estado-chip">{{ $accion }}</span>
            @endif
          </h5>

          <small class="metrics-subtext">
            {{ $mensajeAsistencia }}

            @if (!empty($estadoAsistencia['detalle'])) <br>
              {{ $estadoAsistencia['detalle'] }}
            @endif

            @if (!empty($estadoAsistencia['horaEntrada'])) <br>
              <strong>Entrada:</strong> {{ $estadoAsistencia['horaEntrada'] }}
            @endif
            @if (!empty($estadoAsistencia['horaSalida'])) <br>
              <strong>Salida:</strong> {{ $estadoAsistencia['horaSalida'] }}
            @endif
          </small>

          {{-- Botón de acción (opcional) --}}
          @php
            $mostrarBoton = in_array($accion, ['Entrada','Salida']);
            $textoBoton   = $accion === 'Salida' ? 'Registrar salida' : 'Registrar entrada';
          @endphp
          @if ($mostrarBoton)
            <div class="mt-3">
              <form method="POST" action="{{ route('asistencia.punch') }}">

                @csrf
                <button class="btn btn-light btn-sm">{{ $textoBoton }}</button>
              </form>
            </div>
          @endif
        </div>
      </div>

      <!-- Total de Empleados -->
      <div class="metrics-item metrics-card bg-purple">
        <div class="metrics-icon"><i class="fas fa-users"></i></div>
        <div class="metrics-info">
          <h5 class="metrics-title">Empleados</h5>
          <p class="metrics-value">{{ $totalEmpleados }}</p>
          <small class="metrics-subtext">Total registrados</small>
        </div>
      </div>

      <!-- Usuarios Activos -->
      <div class="metrics-item metrics-card bg-orange">
        <div class="metrics-icon"><i class="fas fa-user-check"></i></div>
        <div class="metrics-info">
          <h5 class="metrics-title">Activos</h5>
          <p class="metrics-value">{{ $usuariosActivos }}</p>
          <small class="metrics-subtext">En uso actualmente</small>
        </div>
      </div>

      <!-- Usuarios Inactivos -->
      <div class="metrics-item metrics-card bg-teal">
        <div class="metrics-icon"><i class="fas fa-user-times"></i></div>
        <div class="metrics-info">
          <h5 class="metrics-title">Inactivos</h5>
          <p class="metrics-value">{{ $usuariosInactivos }}</p>
          <small class="metrics-subtext">Sin actividad</small>
        </div>
      </div>

    </div>
  </div>
</div>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('css/home.css') }}">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
  .bienvenida-titulo{
    font-family:'Poppins',sans-serif;font-weight:600;color:#2c3e50;letter-spacing:.5px
  }
  .estado-chip{
    font-size:12px;font-weight:600;background:rgba(255,255,255,.18);
    padding:4px 8px;border-radius:999px;margin-left:8px;display:inline-block
  }
</style>
@endsection

{{-- SweetAlert con el mensaje de asistencia --}}
@if (!empty($mensajeAsistencia))
  @push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const icon =
        {{ (($estadoAsistencia['accion'] ?? '') === 'Completado') ? "'success'" :
            ((($estadoAsistencia['accion'] ?? '') === 'Salida') ? "'warning'" : "'info'") }};
      const textLines = [
        @json($mensajeAsistencia),
        @json($estadoAsistencia['detalle'] ?? '')
      ].filter(Boolean).join(' — ');
      Swal.fire({
        icon,
        title: 'Asistencia',
        text: textLines,
        timer: 3200,
        timerProgressBar: true,
        confirmButtonColor: '#007bff'
      });
    });
  </script>
  @endpush
@endif
