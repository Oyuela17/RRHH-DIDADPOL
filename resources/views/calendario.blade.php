@extends('layouts.dashboard')

@section('title', 'Calendario de Eventos')

@section('styles')
  <link rel="stylesheet" href="{{ asset('css/calendario.css') }}">
@endsection

@section('content')
  {{-- Metas necesarias --}}
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="employee-code" content="{{ optional(auth()->user()->empleado)->cod_empleado }}">

  <div class="calendar-container">
    <h2>Calendario de Eventos</h2>
    <div id="calendar"></div>
  </div>
  
<div class="toast-wrap" id="toastWrap" aria-live="polite" aria-atomic="true"></div>

  <!-- Modal personalizado estilo sistema -->
  <div class="modal-rol" id="modalEvento" style="display:none;">
    <div class="modal-contenido" role="dialog" aria-modal="true" aria-labelledby="tituloModal">

      {{-- HEADER fijo --}}
      <h3 class="titulo-modal" id="tituloModal">Nuevo Evento</h3>

      {{-- ÁREA SCROLL (solo aquí hay barra) --}}
      <div class="modal-scroll-area">
        <form id="formEvento">
          <input type="hidden" id="evento_id">
          <input type="hidden" id="cod_empleado" value="{{ optional(auth()->user()->empleado)->cod_empleado }}">

          <!-- Título (fila completa) -->
          <div class="form-group full">
            <label for="titulo">Título:</label>
            <input type="text" id="titulo" required>
          </div>

          <!-- Inicio / Fin -->
          <div class="form-group">
            <label for="fecha_inicio">Inicio:</label>
            <input type="datetime-local" id="fecha_inicio" class="compact" required>
          </div>
          <div class="form-group">
            <label for="fecha_fin">Fin:</label>
            <input type="datetime-local" id="fecha_fin" class="compact" required>
          </div>

          <!-- Lugar -->
          <div class="form-group full">
            <label for="lugar">Lugar:</label>
            <input type="text" id="lugar">
          </div>

          <!-- Tipo -->
          <div class="form-group full">
            <label for="tipo">Tipo:</label>
            <input type="text" id="tipo">
          </div>

          <!-- Color -->
          <div class="form-group full">
            <label for="color_fondo">Color del evento:</label>
            <select id="color_fondo" class="form-select compact" required>
              <option value="#28a745">Verde</option>
              <option value="#007bff" selected>Azul</option>
              <option value="#dc3545">Rojo</option>
            </select>
          </div>

          <input type="hidden" id="color_texto" value="#ffffff">

          <!-- Enlace -->
          <div class="form-group full">
            <label for="enlace">Enlace:</label>
            <input type="url" id="enlace">
          </div>

          <!-- Recurrente / Todo el día -->
          <div class="form-group">
            <label for="recurrente">¿Recurrente?</label>
            <select id="recurrente" class="compact">
              <option value="false">No</option>
              <option value="true">Sí</option>
            </select>
          </div>
          <div class="form-group">
            <label for="todo_el_dia">¿Todo el día?</label>
            <select id="todo_el_dia" class="compact">
              <option value="false">No</option>
              <option value="true" selected>Sí</option>
            </select>
          </div>

          <!-- Descripción -->
          <div class="form-group full">
            <label for="descripcion">Descripción:</label>
            <textarea id="descripcion" class="tall"></textarea>
          </div>
        </form>
      </div>

      {{-- FOOTER fijo --}}
      <div class="modal-botones">
        <button type="submit" form="formEvento" class="btn btn-success">Guardar</button>
        <button type="button" class="btn btn-danger" id="cancelarEvento">Cancelar</button>
        <button type="button" class="btn btn-secondary" id="eliminarEvento" style="display:none;">Eliminar</button>
      </div>

    </div>
  </div>
@endsection

@section('scripts')
  @vite(['resources/js/app.js'])
@endsection
