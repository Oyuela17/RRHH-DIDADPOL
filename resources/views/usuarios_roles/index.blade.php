@extends('layouts.dashboard')

@section('title', 'Usuarios Roles')

@section('content')
@if (session('success'))
<script>
  document.addEventListener('DOMContentLoaded', () => {
    Swal.fire({
      icon: 'success',
      title: 'Usuarios y Roles',
      text: '{{ session("success") }}',
      confirmButtonText: 'OK',
      confirmButtonColor: '#007bff'
    });
  });
</script>
@endif

<div class="usuarios-wrapper">
  <div class="titulo-con-linea">
    <h2>Usuarios Roles</h2>
  </div>

  <!-- Filtros y acciones -->
  <div class="acciones-superiores">
    <div class="lado-izquierdo">
      <input type="text" id="campoBusqueda" class="form-control" placeholder="Buscar usuario...">
    </div>

    <div class="lado-derecho">
      <form method="GET" action="{{ route('usuarios_roles.index') }}" class="mostrar-registros">
        <label>Ordenar por</label>
        <select name="ordenar" id="ordenarSelect">
          <option value="nombre" {{ request('ordenar', 'nombre') == 'nombre' ? 'selected' : '' }}>Nombre (A-Z)</option>
          <option value="fecha" {{ request('ordenar') == 'fecha' ? 'selected' : '' }}>Fecha de creación</option>
        </select>

        <label>Mostrar</label>
        <select name="registros" onchange="this.form.submit()">
          @foreach([5, 10, 15] as $opcion)
            <option value="{{ $opcion }}" {{ request('registros', 5) == $opcion ? 'selected' : '' }}>{{ $opcion }}</option>
          @endforeach
        </select>
        <span>registros</span>
      </form>
    </div>
  </div>

  <!-- Tabla de usuarios -->
  <div class="usuarios-container">
    <table class="usuarios-table" id="tablaUsuarios">
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Correo</th>
          <th>Rol</th>
          <th>Estado</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody id="cuerpoTabla">
        @forelse ($usuarios_roles as $usuario)
        <tr data-nombre="{{ strtoupper($usuario->name) }}" data-fecha="{{ $usuario->created_at }}">
          <td>{{ $usuario->name }}</td>
          <td>{{ $usuario->email }}</td>
          <td>{{ $usuario->nombre_rol ?? 'SIN ROL' }}</td>
          <td>
            @if(strtoupper($usuario->estado) === 'ACTIVO')
              <span class="badge-success">ACTIVO</span>
            @else
              <span class="badge-inactivo">INACTIVO</span>
            @endif
          </td>
          <td class="acciones-botones">
            <button 
              type="button"
              class="btn {{ $usuario->role_id ? 'btn-warning' : 'btn-asignar' }} btn-editar-rol"
              data-id="{{ $usuario->id }}"
              data-rol="{{ $usuario->role_id }}"
              data-estado="{{ $usuario->estado }}"
              data-modo="{{ $usuario->role_id ? 'editar' : 'asignar' }}">
              {{ $usuario->role_id ? 'Editar Rol' : 'Asignar Rol' }}
            </button>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="5" class="text-center">No hay usuarios registrados.</td>
        </tr>
        @endforelse
      </tbody>
    </table>

    <!-- Paginación -->
    <div class="paginacion-wrapper" id="paginacionWrapper">
      {{ $usuarios_roles->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
    </div>
  </div>
</div>

<!-- Modal -->
<div id="modalAsignarRol" class="modal-rol" style="display: none;">
  <div class="modal-contenido">
    <h3 class="titulo-modal" id="tituloModal">Asignar o Editar Rol</h3>
    <form id="formAsignarRol" method="POST">
      @csrf
      <input type="hidden" name="usuario_id" id="usuario_id">
      <input type="hidden" name="modo" id="modo">

      <div class="form-group">
        <label for="selectRol">Rol:</label>
        <select name="role_id" id="selectRol" required>
          <option value="">Seleccione un rol</option>
          @foreach ($roles as $rol)
            <option value="{{ $rol->id }}">{{ $rol->nombre }}</option>
          @endforeach
        </select>
      </div>

      <div class="form-group">
        <label for="estadoUsuario">Estado:</label>
        <select name="estado" id="estadoUsuario" required>
          <option value="ACTIVO">ACTIVO</option>
          <option value="INACTIVO">INACTIVO</option>
        </select>
      </div>

      <div class="modal-botones">
        <button type="submit" class="btn btn-success">Guardar</button>
        <button type="button" class="btn btn-danger" id="cancelarModal">Cancelar</button>
      </div>
    </form>
  </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('modalAsignarRol');
  const form = document.getElementById('formAsignarRol');
  const usuarioIdInput = document.getElementById('usuario_id');
  const rolSelect = document.getElementById('selectRol');
  const estadoSelect = document.getElementById('estadoUsuario');
  const modoInput = document.getElementById('modo');
  const tituloModal = document.getElementById('tituloModal');
  const campoBusqueda = document.getElementById('campoBusqueda');
  const ordenarSelect = document.getElementById('ordenarSelect');
  const cuerpoTabla = document.getElementById('cuerpoTabla');
  const paginacion = document.getElementById('paginacionWrapper');

  const filasOriginales = Array.from(cuerpoTabla.querySelectorAll('tr')).filter(f => f.dataset.nombre);

  // Abrir modal editar/asignar rol
  document.querySelectorAll('.btn-editar-rol').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.dataset.id;
      const rol = btn.dataset.rol || '';
      const estado = btn.dataset.estado || 'ACTIVO';
      const modo = btn.dataset.modo;

      usuarioIdInput.value = id;
      rolSelect.value = rol;
      estadoSelect.value = estado;
      modoInput.value = modo;

      tituloModal.textContent = (modo === 'editar') ? 'Editar Rol' : 'Asignar Rol';
      form.action = `/usuarios_roles/asignar/${id}`;
      modal.style.display = 'flex';
    });
  });

  document.getElementById('cancelarModal').addEventListener('click', () => {
    modal.style.display = 'none';
  });

  window.addEventListener('click', (e) => {
    if (e.target === modal) {
      modal.style.display = 'none';
    }
  });

  // Búsqueda en tiempo real
  campoBusqueda.addEventListener('input', () => {
    const valor = campoBusqueda.value.toUpperCase().replace(/[^A-ZÁÉÍÓÚÑ]/g, '').substring(0, 25);
    campoBusqueda.value = valor;

    filtrarYOrdenar();
  });

  ordenarSelect.addEventListener('change', filtrarYOrdenar);

  function filtrarYOrdenar() {
    const filtro = campoBusqueda.value;
    const criterio = ordenarSelect.value;

    let filtradas = filasOriginales.filter(fila =>
      fila.dataset.nombre.includes(filtro)
    );

    filtradas.sort((a, b) => {
      if (criterio === 'nombre') {
        return a.dataset.nombre.localeCompare(b.dataset.nombre);
      }
      if (criterio === 'fecha') {
        return new Date(b.dataset.fecha) - new Date(a.dataset.fecha);
      }
      return 0;
    });

    cuerpoTabla.innerHTML = '';
    filtradas.forEach(f => cuerpoTabla.appendChild(f));
    paginacion.style.display = filtro ? 'none' : 'block';
  }
});
</script>
@endsection
