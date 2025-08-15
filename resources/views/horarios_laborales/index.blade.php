@extends('layouts.dashboard')
@section('title', 'Mantenimiento de Horarios Laborales')

@section('content')

@if (session('success'))
<script>
  document.addEventListener('DOMContentLoaded', () => {
    Swal.fire({
      icon: 'success',
      title: 'Horarios',
      text: '{{ session("success") }}',
      confirmButtonText: 'OK',
      confirmButtonColor: '#007bff'
    });
  });
</script>
@endif

<div class="horarios-wrapper">
  <div class="titulo-con-linea">
    <h2>Mantenimiento de Horarios Laborales</h2>
  </div>

  <div class="acciones-superiores">
    <div class="lado-izquierdo">
      <form method="GET" action="{{ route('horarios.index') }}">
        <input type="text" name="busqueda" id="busqueda" class="form-control" placeholder="Buscar horario..." value="{{ request('busqueda') }}">
      </form>
    </div>
    <div class="lado-derecho">
      <a href="#" class="btn btn-nuevo" id="btnMostrarModal">
        <i class="fas fa-plus"></i> Nuevo Horario
      </a>
      
    </div>
  </div>

  <div class="horarios-container">
    <table class="horarios-table">
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Inicio</th>
          <th>Final</th>
          <th>Días</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($horarios as $h)
          <tr>
            <td>{{ $h['nom_horario'] }}</td>
            <td>{{ $h['hora_inicio'] }}</td>
            <td>{{ $h['hora_final'] }}</td>
            <td>{{ is_array($h['dias_semana']) ? implode(', ', $h['dias_semana']) : $h['dias_semana'] }}</td>
            <td class="acciones-botones">
              <a href="#" class="btn btn-warning btn-editar"
                 data-id="{{ $h['cod_horario'] }}"
                 data-nombre="{{ $h['nom_horario'] }}"
                 data-inicio="{{ $h['hora_inicio'] }}"
                 data-final="{{ $h['hora_final'] }}"
                 data-dias="{{ is_array($h['dias_semana']) ? implode(',', $h['dias_semana']) : $h['dias_semana'] }}">
                 Editar
              </a>
              <form action="{{ route('horarios.destroy', $h['cod_horario']) }}" method="POST" class="form-eliminar" data-nombre="{{ $h['nom_horario'] }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Eliminar</button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="text-center">No hay horarios registrados.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<!-- Modal -->
<div class="modal-rol" id="modalHorario" style="display: none;">
  <div class="modal-contenido">
    <h3 class="titulo-modal" id="tituloModal">Registrar Horario</h3>
    <form id="formHorario" method="POST" action="{{ route('horarios.store') }}">
      @csrf
      <input type="hidden" name="_method" id="metodoForm" value="POST">
      <input type="hidden" name="id" id="horarioId">

      <div class="form-group">
        <label>Nombre:</label>
        <input type="text" name="nom_horario" id="nombreHorario" required>
      </div>
      <div class="form-group">
        <label>Hora Inicio:</label>
        <input type="time" name="hora_inicio" id="horaInicio" required>
      </div>
      <div class="form-group">
        <label>Hora Final:</label>
        <input type="time" name="hora_final" id="horaFinal" required>
      </div>

      <div class="form-group">
        <label>Seleccione los días laborales:</label>
        <div class="dias-semana-container">
          @foreach (['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'] as $dia)
            <div class="dia-semana" data-dia="{{ $dia }}">{{ strtoupper($dia) }}</div>
          @endforeach
        </div>
        <input type="hidden" name="dias_semana" id="diasSeleccionados">
      </div>

      <div class="modal-botones">
        <button type="submit" class="btn btn-success">Guardar</button>
        <button type="button" class="btn btn-danger" id="cancelarHorario">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<link rel="stylesheet" href="{{ asset('css/horarios_laborales.css') }}">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('modalHorario');
  const form = document.getElementById('formHorario');
  const tituloModal = document.getElementById('tituloModal');
  const metodoForm = document.getElementById('metodoForm');
  const idInput = document.getElementById('horarioId');
  const nombreInput = document.getElementById('nombreHorario');
  const inicioInput = document.getElementById('horaInicio');
  const finalInput = document.getElementById('horaFinal');
  const diasInput = document.getElementById('diasSeleccionados');

  document.getElementById('btnMostrarModal').addEventListener('click', () => {
    form.action = "{{ route('horarios.store') }}";
    metodoForm.value = 'POST';
    tituloModal.textContent = 'Registrar Horario';
    form.reset();
    idInput.value = '';
    diasInput.value = '';
    document.querySelectorAll('.dia-semana').forEach(d => d.classList.remove('activo'));
    modal.style.display = 'flex';
  });

  document.getElementById('cancelarHorario').addEventListener('click', () => {
    modal.style.display = 'none';
  });

  document.querySelectorAll('.btn-editar').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.dataset.id;
      const nombre = btn.dataset.nombre;
      const inicio = btn.dataset.inicio;
      const fin = btn.dataset.final;
      const dias = btn.dataset.dias.split(',');

      form.action = `/horarios/${id}`;
      metodoForm.value = 'PUT';
      tituloModal.textContent = 'Editar Horario';
      idInput.value = id;
      nombreInput.value = nombre;
      inicioInput.value = inicio;
      finalInput.value = fin;

      document.querySelectorAll('.dia-semana').forEach(dia => {
        const d = dia.dataset.dia;
        if (dias.includes(d)) {
          dia.classList.add('activo');
        } else {
          dia.classList.remove('activo');
        }
      });

      diasInput.value = dias.join(', ');
      modal.style.display = 'flex';
    });
  });

  document.querySelectorAll('.dia-semana').forEach(boton => {
    boton.addEventListener('click', () => {
      boton.classList.toggle('activo');
      const seleccionados = Array.from(document.querySelectorAll('.dia-semana.activo'))
        .map(d => d.dataset.dia)
        .join(', ');
      diasInput.value = seleccionados;
    });
  });

  document.querySelectorAll('.form-eliminar').forEach(form => {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      const nombre = this.dataset.nombre;
      Swal.fire({
        title: '¿Eliminar?',
        text: `¿Deseas eliminar el horario "${nombre}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      }).then(result => {
        if (result.isConfirmed) this.submit();
      });
    });
  });
});
</script>
@endsection
