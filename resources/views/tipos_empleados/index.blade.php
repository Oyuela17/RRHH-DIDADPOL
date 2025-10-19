@extends('layouts.dashboard')
@section('title', 'Mantenimiento de Tipos de Empleado')

@section('content')

@if (session('success'))
<script>
  document.addEventListener('DOMContentLoaded', () => {
    Swal.fire({
      icon: 'success',
      title: 'Tipo de Empleado',
      text: @json(session('success')),
      confirmButtonText: 'OK',
      confirmButtonColor: '#007bff'
    });
  });
</script>
@endif

<div class="tipos-wrapper">
  <div class="titulo-con-linea">
    <h2>Mantenimiento de Tipos de Empleado</h2>
  </div>

  <div class="acciones-superiores">
    <div class="lado-izquierdo">
      <form method="GET" action="{{ route('tipos.index') }}">
        <input type="text" name="busqueda" id="busqueda" class="form-control"
               placeholder="Buscar tipo..." value="{{ request('busqueda') }}">
      </form>
    </div>
    <div class="lado-derecho">
      <a href="#" class="btn btn-nuevo" id="btnMostrarModal">
        <i class="fas fa-plus"></i> Nuevo Tipo
      </a>
      <form method="GET" action="{{ route('tipos.index') }}" class="mostrar-registros">
        <label>Ordenar por</label>
        <select name="ordenar" onchange="this.form.submit()">
          <option value="nombre" {{ request('ordenar', 'nombre') == 'nombre' ? 'selected' : '' }}>Nombre (A-Z)</option>
          <option value="fecha" {{ request('ordenar') == 'fecha' ? 'selected' : '' }}>Fecha de creación</option>
        </select>
        <label>Mostrar</label>
        <select name="cantidad" onchange="this.form.submit()">
          @foreach([5, 10, 15, 20] as $opcion)
            <option value="{{ $opcion }}" {{ request('cantidad', 5) == $opcion ? 'selected' : '' }}>{{ $opcion }}</option>
          @endforeach
        </select>
        <span>registros</span>
        <input type="hidden" name="busqueda" value="{{ request('busqueda') }}">
      </form>
    </div>
  </div>

  <div class="tipos-container">
    <table class="tipos-table">
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Descripción</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($tipos as $t)
          <tr>
            <td>{{ $t['nom_tipo'] }}</td>
            <td>{{ $t['descripcion'] }}</td>
            <td class="acciones-botones">
              <a href="#" class="btn btn-warning btn-editar"
                 data-id="{{ $t['cod_tipo_empleado'] }}"
                 data-nombre="{{ $t['nom_tipo'] }}"
                 data-descripcion="{{ $t['descripcion'] }}">
                Editar
              </a>
              <form action="{{ route('tipos.destroy', $t['cod_tipo_empleado']) }}" method="POST" class="form-eliminar" data-nombre="{{ $t['nom_tipo'] }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Eliminar</button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="3" class="text-center">No hay tipos registrados.</td>
          </tr>
        @endforelse
      </tbody>
    </table>

    {{-- PAGINACIÓN --}}
    <div class="paginacion-wrapper">
      {{ $tipos->appends(['busqueda' => request('busqueda')])->links('pagination::bootstrap-4') }}
    </div>
  </div>
</div>

{{-- MODAL --}}
<div class="modal-rol" id="modalTipo" style="display: none;">
  <div class="modal-contenido">
    <h3 class="titulo-modal" id="tituloModal">Registrar Tipo</h3>
    <form id="formTipo" method="POST" action="{{ route('tipos.store') }}">
      @csrf
      <input type="hidden" name="_method" id="metodoForm" value="POST">
      <input type="hidden" name="id" id="tipoId">

      <div class="form-group">
        <label>Nombre:</label>
        <input type="text" name="nom_tipo" id="nombreTipo" required maxlength="30"
               pattern="^[A-ZÁÉÍÓÚÑ ]+$" title="Solo letras mayúsculas y espacios">
      </div>
      <div class="form-group">
        <label>Descripción:</label>
        <input type="text" name="descripcion" id="descripcionTipo" required maxlength="100"
               pattern="^[A-ZÁÉÍÓÚÑ ]+$" title="Solo letras mayúsculas y espacios">
      </div>

      <div class="modal-botones">
        <button type="submit" class="btn btn-success">Guardar</button>
        <button type="button" class="btn btn-danger" id="cancelarTipo">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<link rel="stylesheet" href="{{ asset('css/tipos_empleados.css') }}">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('modalTipo');
  const form = document.getElementById('formTipo');
  const tituloModal = document.getElementById('tituloModal');
  const metodoForm = document.getElementById('metodoForm');
  const idInput = document.getElementById('tipoId');
  const nombreInput = document.getElementById('nombreTipo');
  const descripcionInput = document.getElementById('descripcionTipo');

  const baseUrl = @json(url('tipos')); // para armar /tipos/{id}

  // Mostrar modal para NUEVO
  document.getElementById('btnMostrarModal')?.addEventListener('click', (e) => {
    e.preventDefault();
    form.action = @json(route('tipos.store'));
    metodoForm.value = 'POST';
    tituloModal.textContent = 'Registrar Tipo';
    form.reset();
    idInput.value = '';
    modal.style.display = 'flex';
  });

  // Cancelar modal
  document.getElementById('cancelarTipo')?.addEventListener('click', () => {
    modal.style.display = 'none';
  });

  // EDITAR (delegación para no perder eventos si la tabla se redibuja)
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-editar');
    if (!btn) return;
    e.preventDefault();

    const id = btn.dataset.id;
    const nombre = btn.dataset.nombre || '';
    const descripcion = btn.dataset.descripcion || '';

    form.action = `${baseUrl}/${id}`; // <-- CORREGIDO (string, no regex)
    metodoForm.value = 'PUT';
    tituloModal.textContent = 'Editar Tipo';
    idInput.value = id;
    nombreInput.value = nombre;
    descripcionInput.value = descripcion;
    modal.style.display = 'flex';
  });

  // Confirmación de ELIMINAR
  document.querySelectorAll('.form-eliminar').forEach(f => {
    f.addEventListener('submit', function (e) {
      e.preventDefault();
      const nombre = this.dataset.nombre || '';
      Swal.fire({
        title: '¿Eliminar?',
        text: `¿Deseas eliminar el tipo "${nombre}"?`,
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

  // Normalización de texto
  nombreInput.addEventListener('input', () => {
    nombreInput.value = nombreInput.value
      .toUpperCase()
      .replace(/[^A-ZÁÉÍÓÚÑ ]/g, '')
      .replace(/\s{2,}/g, ' ')
      .trimStart();
  });

  descripcionInput.addEventListener('input', () => {
    descripcionInput.value = descripcionInput.value
      .toUpperCase()
      .replace(/[^A-ZÁÉÍÓÚÑ ]/g, '')
      .replace(/\s{2,}/g, ' ')
      .trimStart();
  });
});
</script>
@endsection
