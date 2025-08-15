@extends('layouts.dashboard')
@section('title', 'Mantenimiento de Títulos Académicos')

@section('content')
@if (session('success'))
<script>
  document.addEventListener('DOMContentLoaded', () => {
    Swal.fire({
      icon: 'success',
      title: 'Títulos Académicos',
      text: '{{ session("success") }}',
      confirmButtonText: 'OK',
      confirmButtonColor: '#007bff'
    });
  });
</script>
@endif

<div class="titulos-wrapper">
  <div class="titulo-con-linea">
    <h2>Mantenimiento de Títulos Académicos</h2>
  </div>

  <div class="acciones-superiores">
    <div class="lado-izquierdo">
      <form method="GET" action="{{ route('titulos.index') }}" autocomplete="off">
        <input type="text" name="busqueda" id="busqueda" class="form-control"
               placeholder="Buscar título..." value="{{ request('busqueda') }}">
      </form>
    </div>
    <div class="lado-derecho">
      <a href="#" class="btn btn-nuevo" id="btnMostrarModal">
        <i class="fas fa-plus"></i> Nuevo Título
      </a>
      <form method="GET" action="{{ route('titulos.index') }}" class="mostrar-registros">
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
    

  <div class="titulos-container">
    <table class="titulos-table">
      <thead>
        <tr>
          <th>Título</th>
          <th>Abreviatura</th>
          <th>Descripción</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
  @forelse($titulos as $t)
  <tr>
    {{-- ✅ Mostrar siempre el título --}}
    <td>{{ $t->titulo ?? 'SIN TÍTULO' }}</td>

    {{-- ✅ Mostrar abreviatura y descripción si existen --}}
    <td>{{ $t->abreviatura ?? '-' }}</td>
    <td>{{ $t->descripcion ?? '-' }}</td>

    <td class="acciones-botones">
      {{-- Botón Editar --}}
      <a href="#"
         class="btn btn-warning btn-editar"
         data-id="{{ $t->cod_titulo }}"
         data-titulo="{{ $t->titulo }}"
         data-abreviatura="{{ $t->abreviatura }}"
         data-descripcion="{{ $t->descripcion }}">
        Editar
      </a>

      {{-- Botón Eliminar --}}
      <form action="{{ route('titulos.destroy', $t->cod_titulo) }}" method="POST" class="form-eliminar" data-nombre="{{ $t->titulo }}">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">Eliminar</button>
      </form>
    </td>
  </tr>
  @empty
  <tr>
    <td colspan="4">No hay títulos disponibles.</td>
  </tr>
  @endforelse
</tbody>

    </table>

    <div class="paginacion-wrapper">
      {{ $titulos->appends(['busqueda' => request('busqueda')])->links('pagination::bootstrap-4') }}
    </div>
  </div>
</div>

<!-- MODAL -->
<div class="modal-rol" id="modalTitulo" style="display: none;">
  <div class="modal-contenido">
    <h3 class="titulo-modal" id="tituloModal">Registrar Título</h3>
    <form id="formTitulo" method="POST" action="{{ route('titulos.store') }}" autocomplete="off">
      @csrf
      <input type="hidden" name="_method" id="metodoForm" value="POST">
      <input type="hidden" name="id" id="tituloId">

      <div class="form-group">
        <label for="inputTitulo">Título:</label>
        <input type="text" name="titulo" id="inputTitulo" class="form-control" required maxlength="100"
               pattern="^[A-ZÁÉÍÓÚÑ ]+$" title="Solo letras mayúsculas y espacios">
      </div>

      <div class="form-group">
        <label for="inputAbreviatura">Abreviatura:</label>
        <input type="text" name="abreviatura" id="inputAbreviatura" class="form-control" required maxlength="20"
               pattern="^[A-ZÁÉÍÓÚÑ.]+$" title="Solo mayúsculas y puntos">
      </div>

      <div class="form-group">
        <label for="inputDescripcion">Descripción:</label>
        <input type="text" name="descripcion" id="inputDescripcion" class="form-control" required maxlength="150"
               pattern="^[A-ZÁÉÍÓÚÑ ]+$" title="Solo letras mayúsculas y espacios">
      </div>

      <div class="modal-botones">
        <button type="submit" class="btn btn-success">Guardar</button>
        <button type="button" class="btn btn-danger" id="cancelarTitulo">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<link rel="stylesheet" href="{{ asset('css/titulos.css') }}">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('modalTitulo');
  const form = document.getElementById('formTitulo');
  const tituloModal = document.getElementById('tituloModal');
  const metodoForm = document.getElementById('metodoForm');
  const idInput = document.getElementById('tituloId');
  const tituloInput = document.getElementById('inputTitulo');
  const abreviaturaInput = document.getElementById('inputAbreviatura');
  const descripcionInput = document.getElementById('inputDescripcion');

  // Mostrar modal para nuevo
  document.getElementById('btnMostrarModal')?.addEventListener('click', () => {
    form.action = "{{ route('titulos.store') }}";
    metodoForm.value = 'POST';
    tituloModal.textContent = 'Registrar Título';
    form.reset();
    idInput.value = '';
    modal.style.display = 'flex';
  });

  // Cancelar modal
  document.getElementById('cancelarTitulo')?.addEventListener('click', () => {
    modal.style.display = 'none';
  });

  // Cargar datos para edición
  document.querySelectorAll('.btn-editar').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.dataset.id;
      const titulo = btn.dataset.titulo;
      const abreviatura = btn.dataset.abreviatura;
      const descripcion = btn.dataset.descripcion;

      form.action = `/titulos/${id}`;
      metodoForm.value = 'PUT';
      tituloModal.textContent = 'Editar Título';
      idInput.value = id;
      tituloInput.value = titulo;
      abreviaturaInput.value = abreviatura;
      descripcionInput.value = descripcion;
      modal.style.display = 'flex';
    });
  });

  // Confirmación de eliminación
  document.querySelectorAll('.form-eliminar').forEach(f => {
    f.addEventListener('submit', function(event) {
      event.preventDefault();
      const nombre = this.dataset.nombre;
      Swal.fire({
        title: '¿Eliminar?',
        text: `¿Deseas eliminar el título "${nombre}"?`,
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

  // Validaciones en tiempo real
  tituloInput?.addEventListener('input', () => {
    tituloInput.value = tituloInput.value.toUpperCase()
      .replace(/[^A-ZÁÉÍÓÚÑ ]/g, '')
      .replace(/\s{2,}/g, ' ')
      .trimStart();
  });

  abreviaturaInput?.addEventListener('input', () => {
    abreviaturaInput.value = abreviaturaInput.value.toUpperCase()
      .replace(/[^A-ZÁÉÍÓÚÑ.]/g, '')
      .replace(/\.{2,}/g, '.')
      .replace(/^\.+/, ''); // evitar iniciar con punto
  });

  descripcionInput?.addEventListener('input', () => {
    descripcionInput.value = descripcionInput.value.toUpperCase()
      .replace(/[^A-ZÁÉÍÓÚÑ ]/g, '')
      .replace(/\s{2,}/g, ' ')
      .trimStart();
  });
});
</script>
@endsection
