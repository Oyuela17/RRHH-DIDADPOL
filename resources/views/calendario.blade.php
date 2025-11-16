@extends('layouts.dashboard')

@section('title', 'Calendario de Eventos')

@section('styles')
  <link rel="stylesheet" href="{{ asset('css/calendario.css') }}">
@endsection

@section('content')

@php
    // PERMISOS DEL MÓDULO CALENDARIO
    $acciones = $accionesPermitidas['CALENDARIO'] ?? [
        'crear' => false,
        'actualizar' => false,
        'eliminar' => false
    ];
@endphp

{{-- Metas necesarias --}}
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="employee-code" content="{{ optional(auth()->user()->empleado)->cod_empleado }}">

<div class="calendar-container">
    <h2>Calendario de Eventos</h2>

    {{-- === BLOQUEO PARA CREAR EVENTOS === --}}
    <button 
        id="btnNuevoEvento"
        class="btn btn-primary mb-3"
        data-bloqueado="{{ $acciones['crear'] ? '0' : '1' }}"
    >
        <i class="fas fa-plus"></i> Nuevo Evento
    </button>

    <div id="calendar"></div>
</div>

<div class="toast-wrap" id="toastWrap" aria-live="polite" aria-atomic="true"></div>

<!-- Modal personalizado -->
<div class="modal-rol" id="modalEvento" style="display:none;">
  <div class="modal-contenido" role="dialog" aria-modal="true" aria-labelledby="tituloModal">

    <h3 class="titulo-modal" id="tituloModal">Nuevo Evento</h3>

    <div class="modal-scroll-area">
      <form id="formEvento">
        <input type="hidden" id="evento_id">
        <input type="hidden" id="cod_empleado" value="{{ optional(auth()->user()->empleado)->cod_empleado }}">

        <div class="form-group full">
          <label for="titulo">Título:</label>
          <input type="text" id="titulo" required>
        </div>

        <div class="form-group">
          <label for="fecha_inicio">Inicio:</label>
          <input type="datetime-local" id="fecha_inicio" class="compact" required>
        </div>

        <div class="form-group">
          <label for="fecha_fin">Fin:</label>
          <input type="datetime-local" id="fecha_fin" class="compact" required>
        </div>

        <div class="form-group full">
          <label for="lugar">Lugar:</label>
          <input type="text" id="lugar">
        </div>

        <div class="form-group full">
          <label for="tipo">Tipo:</label>
          <input type="text" id="tipo">
        </div>

        <div class="form-group full">
          <label for="color_fondo">Color del evento:</label>
          <select id="color_fondo" class="form-select compact" required>
            <option value="#28a745">Verde</option>
            <option value="#007bff" selected>Azul</option>
            <option value="#dc3545">Rojo</option>
          </select>
        </div>

        <input type="hidden" id="color_texto" value="#ffffff">

        <div class="form-group full">
          <label for="enlace">Enlace:</label>
          <input type="url" id="enlace">
        </div>

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

        <div class="form-group full">
          <label for="descripcion">Descripción:</label>
          <textarea id="descripcion" class="tall"></textarea>
        </div>
      </form>
    </div>

    {{-- BOTONES DEL MODAL CON BLOQUEO DE PERMISOS --}}
    <div class="modal-botones">

      {{-- GUARDAR (Crear/Actualizar) --}}
      <button 
        type="submit" 
        form="formEvento" 
        id="btnGuardarEvento"
        class="btn btn-success"
        data-bloqueado-crear="{{ $acciones['crear'] ? '0' : '1' }}"
        data-bloqueado-actualizar="{{ $acciones['actualizar'] ? '0' : '1' }}"
      >
        Guardar
      </button>

      <button type="button" class="btn btn-danger" id="cancelarEvento">Cancelar</button>

      {{-- ELIMINAR --}}
      <button 
        type="button" 
        class="btn btn-secondary" 
        id="eliminarEvento" 
        style="display:none;"
        data-bloqueado="{{ $acciones['eliminar'] ? '0' : '1' }}"
      >
        Eliminar
      </button>

    </div>
  </div>
</div>

@endsection

@section('scripts')
@vite(['resources/js/app.js'])

<script>
/* ===========================
   BLOQUEO DE PERMISOS
=========================== */

// Bloquear botón "Nuevo Evento"
document.getElementById('btnNuevoEvento').addEventListener('click', function(e){
    if (this.dataset.bloqueado === '1') {
        e.preventDefault();
        Swal.fire('Acción no permitida', 'No tienes permiso para crear eventos.', 'error');
    }
});

// Bloquear botón "Guardar"
document.getElementById('btnGuardarEvento').addEventListener('click', function(e){

    const eventoId = document.getElementById('evento_id').value;
    const esEdicion = eventoId !== "";

    if (esEdicion && this.dataset.bloqueadoActualizar === '1') {
        e.preventDefault();
        Swal.fire('Acción no permitida', 'No tienes permiso para actualizar eventos.', 'error');
    }

    if (!esEdicion && this.dataset.bloqueadoCrear === '1') {
        e.preventDefault();
        Swal.fire('Acción no permitida', 'No tienes permiso para crear eventos.', 'error');
    }
});

// Bloquear botón "Eliminar"
document.getElementById('eliminarEvento').addEventListener('click', function(e){
    if (this.dataset.bloqueado === '1') {
        e.preventDefault();
        Swal.fire('Acción no permitida', 'No tienes permiso para eliminar eventos.', 'error');
    }
});
</script>

@endsection
