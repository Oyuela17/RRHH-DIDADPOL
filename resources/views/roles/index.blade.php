@extends('layouts.dashboard')
@section('title', 'Mantenimiento de Roles')

@section('content')

@if (session('success'))
<script>
  document.addEventListener('DOMContentLoaded', () => {
    Swal.fire({
      icon: 'success',
      title: 'Roles de usuario',
      text: '{{ session("success") }}',
      confirmButtonText: 'OK',
      confirmButtonColor: '#007bff'
    });
  });
</script>
@endif

<div class="roles-wrapper">
  <div class="titulo-con-linea">
    <h2>Mantenimiento de Roles</h2>
  </div>

  <div class="acciones-superiores">
    <div class="lado-izquierdo">
      <input type="text" id="campoBusqueda" class="form-control" placeholder="Buscar rol...">

    </div>

    <div class="lado-derecho">
      <a href="#" class="btn btn-nuevo" id="btnMostrarModal">
        <i class="fas fa-plus"></i> Nuevo Registro
      </a>

      <form method="GET" action="{{ route('roles.index') }}" class="mostrar-registros">
        <label>Ordenar por</label>
        <select name="ordenar" id="ordenarSelect">
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

  <div class="roles-container">
    <table class="roles-table">
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Descripción</th>
          <th>Estado</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
  @forelse ($roles as $rol)
  <tr data-fecha="{{ $rol->created_at }}">
    <td>{{ $rol->nombre }}</td>
    <td>{{ $rol->descripcion }}</td>
    <td>
      @if(strtoupper($rol->estado) === 'ACTIVO')
        <span class="badge-success">ACTIVO</span>
      @else
        <span class="badge-inactivo">INACTIVO</span>
      @endif
    </td>
    <td class="acciones-botones">
      <a href="#" class="btn btn-warning btn-editar"
         data-id="{{ $rol->id }}"
         data-nombre="{{ $rol->nombre }}"
         data-descripcion="{{ $rol->descripcion }}"
         data-estado="{{ $rol->estado }}">Editar</a>
      <form action="/roles/{{ $rol->id }}" method="POST" class="form-eliminar" data-nombre="{{ $rol->nombre }}">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger btn-eliminar">Eliminar</button>
      </form>
    </td>
  </tr>
  @empty
        <tr>
          <td colspan="4" class="text-center">No hay roles registrados.</td>
        </tr>
        <script>
          document.addEventListener('DOMContentLoaded', () => {
            Swal.fire({
              icon: 'info',
              title: 'Rol no encontrado',
              text: 'No se encontró ningún rol con ese nombre.',
              timer: 2500,
              showConfirmButton: false
            });
          });
        </script>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="paginacion-wrapper">
    {{ $roles->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
  </div>
</div>

<!-- Modal de Registro / Edición -->
<div class="modal-rol" id="modalNuevoRol" style="display: none;">
  <div class="modal-contenido">
    <h3 class="titulo-modal" id="tituloModal">Registrar Nuevo Rol</h3>
    <form id="formNuevoRol" action="{{ route('roles.store') }}" method="POST">
      @csrf
      <input type="hidden" name="_method" id="metodoForm" value="POST">
      <input type="hidden" id="rolId" name="id">

      <div class="form-group">
        <label for="nombreRol">Nombre del Rol:</label>
        <input type="text" id="nombreRol" name="nombre" maxlength="50" required>
      </div>

      <div class="form-group">
        <label for="descripcionRol">Descripción:</label>
        <input type="text" id="descripcionRol" name="descripcion" maxlength="50" required>
      </div>

      <div class="form-group">
        <label for="estadoRol">Estado:</label>
        <select id="estadoRol" name="estado" required>
          <option value="ACTIVO">ACTIVO</option>
          <option value="INACTIVO">INACTIVO</option>
        </select>
      </div>

      <div class="modal-botones">
        <button type="submit" class="btn btn-success" id="btnGuardarRol">Guardar</button>
        <button type="button" class="btn btn-danger" id="cancelarRol">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('modalNuevoRol');
  const nombreInput = document.getElementById('nombreRol');
  const descripcionInput = document.getElementById('descripcionRol');
  const metodoForm = document.getElementById('metodoForm');
  const form = document.getElementById('formNuevoRol');
  const tituloModal = document.getElementById('tituloModal');
  const rolId = document.getElementById('rolId');
  const campoBusqueda = document.getElementById('campoBusqueda');
  const estadoRol = document.getElementById('estadoRol');

  const tablaBody = document.querySelector('.roles-table tbody');
  const filasOriginales = Array.from(tablaBody.querySelectorAll('tr'));
  const paginacion = document.querySelector('.paginacion-wrapper');
  const ordenSelect = document.querySelector('select[name="ordenar"]');
  const cantidadSelect = document.querySelector('select[name="cantidad"]');

  // Modal: Registrar
  document.getElementById('btnMostrarModal').addEventListener('click', function (e) {
    e.preventDefault();
    form.action = "{{ route('roles.store') }}";
    metodoForm.value = 'POST';
    tituloModal.textContent = 'Registrar Nuevo Rol';
    nombreInput.value = '';
    descripcionInput.value = '';
    estadoRol.value = 'ACTIVO';
    modal.style.display = 'flex';
  });

  // Modal: Cancelar
  document.getElementById('cancelarRol').addEventListener('click', () => {
    modal.style.display = 'none';
  });

  // Modal: Editar
  document.querySelectorAll('.btn-editar').forEach(btn => {
    btn.addEventListener('click', function () {
      const id = this.dataset.id;
      const nombre = this.dataset.nombre;
      const descripcion = this.dataset.descripcion;
      const estado = this.dataset.estado.toUpperCase();
      form.action = `/roles/${id}`;
      metodoForm.value = 'PUT';
      tituloModal.textContent = 'Editar Rol';
      nombreInput.value = nombre;
      descripcionInput.value = descripcion;
      estadoRol.value = estado;
      modal.style.display = 'flex';
    });
  });

  // Validación campo "nombre"
  nombreInput.addEventListener('input', () => {
    let valor = nombreInput.value.toUpperCase().replace(/[^A-Z]/g, '');
    if (/\s/.test(nombreInput.value)) {
      Swal.fire({
        icon: 'warning',
        title: 'Nombre inválido',
        text: 'Solo se permite una palabra como máximo.',
        timer: 2000,
        showConfirmButton: false
      });
    }
    nombreInput.value = valor;
  });

  // Validación campo "descripción"
  descripcionInput.addEventListener('input', (e) => {
    let valor = e.target.value.toUpperCase();
    valor = valor.replace(/[^A-ZÁÉÍÓÚÑ ]/g, '').replace(/\s{2,}/g, ' ').replace(/(.)\1{2,}/g, '$1$1');

    const palabras = valor.trim().split(/\s+/);
    if (palabras.length > 2 || valor.length > 50) {
      Swal.fire({
        icon: 'warning',
        title: 'Límite de palabras',
        text: 'Solo se permiten 2 palabras como máximo.',
        timer: 2500,
        showConfirmButton: false
      });
      valor = palabras.slice(0, 2).join(' ').substring(0, 50);
    }
    e.target.value = valor;
  });

  // Confirmar eliminación
  document.querySelectorAll('.form-eliminar').forEach(form => {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      const nombreRol = this.dataset.nombre;
      Swal.fire({
        title: '¿Estás seguro?',
        text: `El rol "${nombreRol}" se eliminará permanentemente.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          this.submit();
        }
      });
    });
  });

  // Filtro de búsqueda y orden en tiempo real
  function filtrarTabla() {
    const filtro = campoBusqueda.value.trim().toUpperCase();
    const criterio = ordenSelect.value;

    let filas = [...filasOriginales];

    if (filtro) {
      filas = filas.filter(fila => fila.cells[0].textContent.toUpperCase().includes(filtro));
    }

    if (filtro.length > 0) {
      paginacion.style.display = 'none';
      ordenSelect.closest('form').style.display = 'none';
    } else {
      paginacion.style.display = '';
      ordenSelect.closest('form').style.display = '';
    }

    if (criterio === 'nombre') {
      filas.sort((a, b) => a.cells[0].textContent.localeCompare(b.cells[0].textContent));
    } else if (criterio === 'fecha') {
      filas.sort((a, b) => {
        const aFecha = new Date(a.dataset.fecha);
        const bFecha = new Date(b.dataset.fecha);
        return bFecha - aFecha;
      });
    }

    tablaBody.innerHTML = '';
    filas.forEach(fila => tablaBody.appendChild(fila));
  }

  campoBusqueda.addEventListener('input', () => {
    campoBusqueda.value = campoBusqueda.value.toUpperCase().replace(/[^A-Z]/g, '');
    filtrarTabla();
  });

  ordenSelect.addEventListener('change', () => {
    filtrarTabla();
  });
});
</script>
@endsection
