@extends('layouts.dashboard')
@section('title', 'Mantenimiento de Horarios Laborales')

@section('content')

@if (session('success'))
<script>
  document.addEventListener('DOMContentLoaded', () => {
    Swal.fire({
      icon: 'success',
      title: 'Horarios',
      text: @json(session('success')),
      confirmButtonText: 'OK',
      confirmButtonColor: '#007bff'
    });
  });
</script>
@endif

@if (session('error'))
<script>
  document.addEventListener('DOMContentLoaded', () => {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: @json(session('error')),
      confirmButtonText: 'OK',
      confirmButtonColor: '#d33'
    });
  });
</script>
@endif

@if (session('advertencia') || $errors->any())
<script>
  document.addEventListener('DOMContentLoaded', () => {
    Swal.fire({
      icon: 'warning',
      title: 'Advertencia',
      text: @json(session('advertencia') ?? $errors->first()),
      confirmButtonText: 'Entendido',
      confirmButtonColor: '#f39c12'
    });
  });
</script>
@endif

@php
    // Permisos del módulo EMPLEADOS (donde cuelga Horarios)
    $accionesEmpleados = $accionesPermitidas['EMPLEADOS'] ?? [
        'crear'      => false,
        'actualizar' => false,
        'eliminar'   => false,
    ];
@endphp

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
      <a
        href="#"
        class="btn btn-nuevo"
        id="btnMostrarModal"
        data-bloqueado="{{ $accionesEmpleados['crear'] ? '0' : '1' }}"
      >
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
            <td>
              @php
                $dias = is_array($h['dias_semana']) ? $h['dias_semana'] : explode(',', (string)$h['dias_semana']);
                $labels = ['LU'=>'LUNES','MA'=>'MARTES','MI'=>'MIÉRCOLES','JU'=>'JUEVES','VI'=>'VIERNES','SA'=>'SÁBADO','DO'=>'DOMINGO'];
                $mostrar = collect($dias)->map(function($d) use($labels){
                  $u = strtoupper(trim($d));
                  return $labels[$u] ?? $u;
                })->implode(', ');
              @endphp
              {{ $mostrar }}
            </td>
            <td class="acciones-botones">
              <a href="#"
                 class="btn btn-warning btn-editar"
                 data-id="{{ $h['cod_horario'] }}"
                 data-nombre="{{ $h['nom_horario'] }}"
                 data-inicio="{{ $h['hora_inicio'] }}"
                 data-final="{{ $h['hora_final'] }}"
                 data-dias="{{ is_array($h['dias_semana']) ? implode(',', $h['dias_semana']) : $h['dias_semana'] }}">
                 Editar
              </a>
              <form action="{{ route('horarios.destroy', $h['cod_horario']) }}"
                    method="POST"
                    class="form-eliminar"
                    data-nombre="{{ $h['nom_horario'] }}">
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
        <input type="text" name="nom_horario" id="nombreHorario" required maxlength="50"
               pattern="^[A-ZÁÉÍÓÚÑ ]+$" title="Solo letras y espacios.">
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
        <div class="dias-semana-container" id="diasUI">
          <div class="dia-semana" data-code="LU">LUNES</div>
          <div class="dia-semana" data-code="MA">MARTES</div>
          <div class="dia-semana" data-code="MI">MIÉRCOLES</div>
          <div class="dia-semana" data-code="JU">JUEVES</div>
          <div class="dia-semana" data-code="VI">VIERNES</div>
          <div class="dia-semana" data-code="SA">SÁBADO</div>
          <div class="dia-semana" data-code="DO">DOMINGO</div>
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
// Constantes de permisos (módulo EMPLEADOS)
const P_CAN_CREATE_EMPLEADOS   = {{ $accionesEmpleados['crear'] ? 'true' : 'false' }};
const P_CAN_UPDATE_EMPLEADOS   = {{ $accionesEmpleados['actualizar'] ? 'true' : 'false' }};
const P_CAN_DELETE_EMPLEADOS   = {{ $accionesEmpleados['eliminar'] ? 'true' : 'false' }};

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
  const diasUI = document.getElementById('diasUI');
  const btnNuevo = document.getElementById('btnMostrarModal');

  // Helpers para días
  const NOMBRE_A_COD = {
    'LUNES': 'LU','MARTES': 'MA','MIERCOLES':'MI','MIÉRCOLES':'MI',
    'JUEVES':'JU','VIERNES':'VI','SABADO':'SA','SÁBADO':'SA','DOMINGO':'DO'
  };
  const COD_VALIDOS = ['LU','MA','MI','JU','VI','SA','DO'];

  const setDiasHidden = () => {
    const seleccionados = Array.from(diasUI.querySelectorAll('.dia-semana.activo'))
      .map(el => el.dataset.code);
    diasInput.value = seleccionados.join(',');
  };

  diasUI.addEventListener('click', (e) => {
    const item = e.target.closest('.dia-semana');
    if (!item) return;
    item.classList.toggle('activo');
    setDiasHidden();
  });

  // Abrir modal NUEVO
  btnNuevo.addEventListener('click', (e) => {
    e.preventDefault();

    if (!P_CAN_CREATE_EMPLEADOS) {
      Swal.fire({
        icon: 'error',
        title: 'Acción no permitida',
        text: 'No tienes permiso para crear horarios.',
      });
      return;
    }

    form.action = @json(route('horarios.store'));
    metodoForm.value = 'POST';
    tituloModal.textContent = 'Registrar Horario';
    form.reset();
    idInput.value = '';
    diasInput.value = '';
    diasUI.querySelectorAll('.dia-semana').forEach(d => d.classList.remove('activo'));
    modal.style.display = 'flex';
  });

  // Cancelar
  document.getElementById('cancelarHorario').addEventListener('click', () => {
    modal.style.display = 'none';
  });

  // Editar (delegación)
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-editar');
    if (!btn) return;

    e.preventDefault();

    if (!P_CAN_UPDATE_EMPLEADOS) {
      Swal.fire({
        icon: 'error',
        title: 'Acción no permitida',
        text: 'No tienes permiso para editar horarios.',
      });
      return;
    }

    const id = btn.dataset.id;
    const nombre = btn.dataset.nombre || '';
    const inicio = btn.dataset.inicio || '';
    const fin = btn.dataset.final || '';
    const diasAttr = (btn.dataset.dias || '').split(',').map(s => s.trim());

    const diasCod = diasAttr.map(d => {
      const u = d.toUpperCase();
      return COD_VALIDOS.includes(u) ? u : (NOMBRE_A_COD[u] || null);
    }).filter(Boolean);

    form.action = @json(url('horarios')) + '/' + id;
    metodoForm.value = 'PUT';
    tituloModal.textContent = 'Editar Horario';
    idInput.value = id;

    nombreInput.value = nombre.toUpperCase();
    inicioInput.value = inicio;
    finalInput.value = fin;

    diasUI.querySelectorAll('.dia-semana').forEach(el => {
      el.classList.toggle('activo', diasCod.includes(el.dataset.code));
    });
    diasInput.value = diasCod.join(',');

    modal.style.display = 'flex';
  });

  // Confirmación eliminar
  document.querySelectorAll('.form-eliminar').forEach(f => {
    f.addEventListener('submit', function (e) {
      e.preventDefault();

      if (!P_CAN_DELETE_EMPLEADOS) {
        Swal.fire({
          icon: 'error',
          title: 'Acción no permitida',
          text: 'No tienes permiso para eliminar horarios.',
        });
        return;
      }

      const nombre = this.dataset.nombre || '';
      Swal.fire({
        title: '¿Eliminar?',
        text: `¿Deseas eliminar el horario "${nombre}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      }).then(r => {
        if (r.isConfirmed) this.submit();
      });
    });
  });

  // === ÚNICA VALIDACIÓN EXTRA: al menos un día seleccionado ===
  form.addEventListener('submit', (e) => {
    if (!diasInput.value) {
      e.preventDefault();
      Swal.fire('Validación', 'Selecciona al menos un día laboral.', 'warning');
    }
  });
});
</script>
@endsection
