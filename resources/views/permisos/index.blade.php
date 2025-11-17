@extends('layouts.dashboard')
@section('title', 'Gestión de Permisos')

@section('content')
<link rel="stylesheet" href="{{ asset('css/permisos.css') }}">

<div class="roles-wrapper">
  <div class="titulo-con-linea">
    <h2>Gestión de Permisos</h2>
  </div>

  <!-- Filtros -->
  <div class="acciones-superiores">
    <div class="lado-izquierdo">
      <input type="text" id="campoBusqueda" class="form-control" placeholder="Buscar rol..." oninput="this.value = this.value.toUpperCase()">
    </div>
    <div class="lado-derecho">
      <form method="GET" action="{{ route('permisos.index') }}" class="mostrar-registros">
        <label>Ordenar por</label>
        <select id="ordenarSelect" class="form-control">
          <option value="nombre">Nombre (A-Z)</option>
          <option value="fecha">Fecha de creación</option>
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

  <!-- Tabla de roles -->
  <table class="roles-table">
    <thead>
      <tr>
        <th>Rol</th>
        <th>Estado</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($roles as $rol)
        <tr data-nombre="{{ strtoupper($rol->nombre) }}" data-fecha="{{ $rol->created_at }}">
          <td>{{ $rol->nombre }}</td>
          <td>
            @if(strtoupper($rol->estado) === 'ACTIVO')
              <span class="badge-success">ACTIVO</span>
            @else
              <span class="badge-inactivo">INACTIVO</span>
            @endif
          </td>
          <td>
            <a href="#" class="btn btn-primary btn-ver-permisos" data-id="{{ $rol->id }}" data-nombre="{{ $rol->nombre }}">
              Ver Permisos
            </a>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="3" class="text-center">No se encontraron roles.</td>
        </tr>
      @endforelse
    </tbody>
  </table>

  <!-- Paginación -->
  <div class="paginacion-wrapper">
    {{ $roles->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
  </div>
</div>

<!-- MODAL PERMISOS -->
<div class="modal-permisos" id="modalPermisos" style="display: none;">
  <div class="modal-contenido" id="contenedorModalPermisos">
    <h3 class="titulo-modal">Permisos de <span id="nombreRolModal"></span></h3>
    <form id="formPermisos">
      <input type="hidden" id="permisoRolId">

      <div class="cabecera-acciones">
        <span class="col-modulo">Módulo</span>
        <span class="col-switch">Acceso</span>
        <span class="col-switch">Crear</span>
        <span class="col-switch">Actualizar</span>
        <span class="col-switch">Eliminar</span>
      </div>

      <div id="listaModulos" class="modulos-lista"></div>

      <div class="modal-botones">
        <button type="submit" class="btn btn-guardar">Guardar</button>
        <button type="button" class="btn btn-cancelar" id="cancelarPermisos">Salir</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const API_URL = 'https://rrhh-didadpol-1.onrender.com';
let cacheModulos = [];


// ======================================================================
// SWITCHES — con eliminación de Actualizar/Eliminar para ciertos módulos
// ======================================================================
function crearSwitches(mod, permiso = {}) {

  const modName = mod.nombre.toUpperCase();
  const soloCrear = (modName === 'ASISTENCIA' || modName === 'REPORTES');

  return `
    <div class="permiso-item">
      <span class="col-modulo">${modName}</span>

      <!-- ACCESO -->
      <label class="switch-texto col-switch">
        <input type="checkbox" name="acceso_${mod.id}" ${permiso.tiene_acceso ? 'checked' : ''}>
        <span class="slider-texto"></span>
      </label>

      <!-- CREAR -->
      <label class="switch-texto col-switch">
        <input type="checkbox" name="crear_${mod.id}" ${permiso.puede_crear ? 'checked' : ''}>
        <span class="slider-texto"></span>
      </label>

      ${
        soloCrear
        ? `
          <span class="col-switch disabled"></span>
          <span class="col-switch disabled"></span>
        `
        : `
          <!-- ACTUALIZAR -->
          <label class="switch-texto col-switch">
            <input type="checkbox" name="actualizar_${mod.id}" ${permiso.puede_actualizar ? 'checked' : ''}>
            <span class="slider-texto"></span>
          </label>

          <!-- ELIMINAR -->
          <label class="switch-texto col-switch">
            <input type="checkbox" name="eliminar_${mod.id}" ${permiso.puede_eliminar ? 'checked' : ''}>
            <span class="slider-texto"></span>
          </label>
        `
      }
    </div>
  `;
}



// Abrir modal y cargar módulos + permisos
document.querySelectorAll('.btn-ver-permisos').forEach(btn => {
  btn.addEventListener('click', async function (e) {
    e.preventDefault();

    const rolId  = this.dataset.id;
    const nombre = this.dataset.nombre;

    document.getElementById('permisoRolId').value = rolId;
    document.getElementById('nombreRolModal').innerText = nombre.toUpperCase();
    document.getElementById('modalPermisos').style.display = 'flex';

    try {
      if (cacheModulos.length === 0) {
        const respMod = await fetch(`${API_URL}/api/modulos`);
        if (!respMod.ok) throw new Error('Error al obtener módulos');
        cacheModulos = await respMod.json();
      }

      const respPerm = await fetch(`${API_URL}/api/permisos/${rolId}`);
      if (!respPerm.ok) throw new Error('Error al obtener permisos');
      const permisos = await respPerm.json();

      const lista = document.getElementById('listaModulos');
      lista.innerHTML = '';

      cacheModulos.forEach(mod => {
        const permiso = permisos.find(p => p.modulo_id === mod.id) || {};
        lista.innerHTML += crearSwitches(mod, permiso);
      });

    } catch (error) {
      console.error(error);
      Swal.fire('Error', 'No se pudo cargar los permisos', 'error');
    }
  });
});


// Guardar
document.getElementById('formPermisos').addEventListener('submit', async function (e) {
  e.preventDefault();

  const rolId = document.getElementById('permisoRolId').value;

  try {
    if (cacheModulos.length === 0) {
      const respMod = await fetch(`${API_URL}/api/modulos`);
      cacheModulos = await respMod.json();
    }

    for (const mod of cacheModulos) {

      const modName = mod.nombre.toUpperCase();
      const soloCrear = (modName === 'ASISTENCIA' || modName === 'REPORTES');

      const permiso = {
        rol_id: parseInt(rolId),
        modulo_id: mod.id,
        tiene_acceso:     document.querySelector(`[name=acceso_${mod.id}]`).checked,
        puede_crear:      document.querySelector(`[name=crear_${mod.id}]`).checked,
        puede_actualizar: soloCrear ? false : document.querySelector(`[name=actualizar_${mod.id}]`).checked,
        puede_eliminar:   soloCrear ? false : document.querySelector(`[name=eliminar_${mod.id}]`).checked
      };

      await fetch(`${API_URL}/api/permisos`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(permiso)
      });
    }

    Swal.fire('Éxito', 'Permisos guardados correctamente', 'success');
    document.getElementById('modalPermisos').style.display = 'none';

  } catch (error) {
    console.error(error);
    Swal.fire('Error','No se pudo guardar los permisos','error');
  }
});

document.getElementById('cancelarPermisos').addEventListener('click', () => {
  document.getElementById('modalPermisos').style.display = 'none';
});


// --- Búsqueda + ordenamiento ---
const campoBusqueda = document.getElementById('campoBusqueda');
const ordenarSelect = document.getElementById('ordenarSelect');
const tablaBody     = document.querySelector('.roles-table tbody');
const paginacion    = document.querySelector('.paginacion-wrapper');

const filasOriginales = Array.from(tablaBody.querySelectorAll('tr')).filter(f => f.dataset.nombre);

function filtrarYOrdenar() {
  let filtro = campoBusqueda.value.trim().toUpperCase().replace(/[^A-ZÁÉÍÓÚÑ]/g, '');
  campoBusqueda.value = filtro;

  if (filtro.includes(' ')) {
    Swal.fire({
      icon: 'warning',
      title: 'Búsqueda inválida',
      text: 'Solo se permite una palabra sin espacios.',
      timer: 2000,
      showConfirmButton: false
    });
    return;
  }

  const criterio = ordenarSelect.value;
  let resultado = filasOriginales.filter(f => f.dataset.nombre.includes(filtro));

  resultado.sort((a, b) => {
    if (criterio === 'nombre') {
      return a.dataset.nombre.localeCompare(b.dataset.nombre);
    }
    if (criterio === 'fecha') {
      return new Date(b.dataset.fecha) - new Date(a.dataset.fecha);
    }
    return 0;
  });

  tablaBody.innerHTML = '';
  resultado.forEach(f => tablaBody.appendChild(f));
  paginacion.style.display = filtro ? 'none' : '';
}

campoBusqueda.addEventListener('input',  filtrarYOrdenar);
ordenarSelect.addEventListener('change', filtrarYOrdenar);

</script>
@endsection
