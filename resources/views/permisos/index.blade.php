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
      <form method="GET" action="{{ route('permisos.index') }}" class="mostrar-registros" id="formOrden">
        <label>Ordenar por</label>
        <select id="ordenarSelect" class="form-control" name="ordenar" onchange="document.getElementById('formOrden').submit()">
          <option value="nombre" {{ request('ordenar','nombre') === 'nombre' ? 'selected' : '' }}>Nombre (A-Z)</option>
          <option value="fecha"  {{ request('ordenar') === 'fecha' ? 'selected' : '' }}>Fecha de creación</option>
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
            @if(strtoupper($rol->estado ?? 'ACTIVO') === 'ACTIVO')
              <span class="badge-success">ACTIVO</span>
            @else
              <span class="badge-inactivo">INACTIVO</span>
            @endif
          </td>
          <td>
            <a href="#" class="btn btn-primary btn-ver-permisos"
               data-id="{{ $rol->id }}" data-nombre="{{ $rol->nombre }}">Ver Permisos</a>
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
<div class="modal-permisos" id="modalPermisos" style="display:none;">
  <div class="modal-contenido" id="contenedorModalPermisos">
    <h3 class="titulo-modal">Permisos de <span id="nombreRolModal"></span></h3>

    <div id="cargandoModulos" style="display:none; margin:8px 0;">
      <i class="fa fa-spinner fa-spin"></i> Cargando módulos y permisos...
    </div>

    <form id="formPermisos">
      @csrf
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
        <button type="submit" class="btn btn-guardar" id="btnGuardar">Guardar</button>
        <button type="button" class="btn btn-cancelar" id="cancelarPermisos">Salir</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const API_URL = 'https://rrhh-didadpol-1.onrender.com';

// ========== Utilidades ==========
const $ = (sel) => document.querySelector(sel);
const $$ = (sel) => Array.from(document.querySelectorAll(sel));

function crearSwitches(mod, permiso = {}) {
  const safeNombre = (mod.nombre || '').toString().toUpperCase().trim();
  return `
    <div class="permiso-item" data-modulo-id="${mod.id}">
      <span class="col-modulo">${safeNombre}</span>

      <label class="switch-texto col-switch">
        <input type="checkbox" name="acceso_${mod.id}" ${permiso.tiene_acceso ? 'checked' : ''}>
        <span class="slider-texto"></span>
      </label>

      <label class="switch-texto col-switch">
        <input type="checkbox" name="crear_${mod.id}" ${permiso.puede_crear ? 'checked' : ''}>
        <span class="slider-texto"></span>
      </label>

      <label class="switch-texto col-switch">
        <input type="checkbox" name="actualizar_${mod.id}" ${permiso.puede_actualizar ? 'checked' : ''}>
        <span class="slider-texto"></span>
      </label>

      <label class="switch-texto col-switch">
        <input type="checkbox" name="eliminar_${mod.id}" ${permiso.puede_eliminar ? 'checked' : ''}>
        <span class="slider-texto"></span>
      </label>
    </div>
  `;
}

// ========== Abrir modal y cargar datos ==========
$$('.btn-ver-permisos').forEach(btn => {
  btn.addEventListener('click', async (e) => {
    e.preventDefault();
    const rolId = btn.dataset.id;
    const nombre = (btn.dataset.nombre || '').toUpperCase();

    $('#permisoRolId').value = rolId;
    $('#nombreRolModal').innerText = nombre;
    $('#listaModulos').innerHTML = '';
    $('#modalPermisos').style.display = 'flex';
    $('#cargandoModulos').style.display = 'block';

    try {
      const [modResp, perResp] = await Promise.all([
        fetch(`${API_URL}/api/modulos`),
        fetch(`${API_URL}/api/permisos/${rolId}`)
      ]);

      if (!modResp.ok) throw new Error('Error cargando módulos');
      if (!perResp.ok) throw new Error('Error cargando permisos');

      const modulos = await modResp.json();
      const permisos = await perResp.json();

      const lista = $('#listaModulos');
      lista.innerHTML = '';

      modulos.forEach(mod => {
        const permiso = permisos.find(p => p.modulo_id === mod.id) || {};
        lista.insertAdjacentHTML('beforeend', crearSwitches(mod, permiso));
      });

    } catch (err) {
      console.error(err);
      Swal.fire('Error', 'No se pudo cargar la configuración de permisos', 'error');
      $('#modalPermisos').style.display = 'none';
    } finally {
      $('#cargandoModulos').style.display = 'none';
    }
  });
});

// ========== Guardar permisos ==========
$('#formPermisos').addEventListener('submit', async (e) => {
  e.preventDefault();
  const btn = $('#btnGuardar');
  btn.disabled = true;

  const rolId = parseInt($('#permisoRolId').value, 10);

  try {
    // Re-consultar módulos para conocer todos los IDs a guardar
    const modResp = await fetch(`${API_URL}/api/modulos`);
    if (!modResp.ok) throw new Error('Error cargando módulos');
    const modulos = await modResp.json();

    // Armar payloads por módulo
    const payloads = modulos.map(mod => {
      const id = mod.id;
      return {
        rol_id: rolId,
        modulo_id: id,
        tiene_acceso:   !!document.querySelector(`[name=acceso_${id}]`)?.checked,
        puede_crear:    !!document.querySelector(`[name=crear_${id}]`)?.checked,
        puede_actualizar: !!document.querySelector(`[name=actualizar_${id}]`)?.checked,
        puede_eliminar: !!document.querySelector(`[name=eliminar_${id}]`)?.checked
      };
    });

    // Guardar en paralelo con tolerancia a fallos
    const results = await Promise.allSettled(payloads.map(p =>
      fetch(`${API_URL}/api/permisos`, {
        method: 'POST',
        headers: { 'Content-Type':'application/json' },
        body: JSON.stringify(p)
      })
    ));

    const fallas = results.filter(r => r.status === 'rejected' || (r.value && !r.value.ok));
    if (fallas.length > 0) {
      throw new Error('Algunos permisos no se pudieron guardar');
    }

    Swal.fire('Éxito', 'Permisos guardados correctamente', 'success');
    $('#modalPermisos').style.display = 'none';

  } catch (err) {
    console.error(err);
    Swal.fire('Error', err.message || 'No se pudo guardar los permisos', 'error');
  } finally {
    btn.disabled = false;
  }
});

// ========== Búsqueda + Ordenamiento en tiempo real (solo UI) ==========
const campoBusqueda = $('#campoBusqueda');
const ordenarSelect = $('#ordenarSelect');
const tablaBody = document.querySelector('.roles-table tbody');
const paginacion = document.querySelector('.paginacion-wrapper');
const filasOriginales = Array.from(tablaBody.querySelectorAll('tr')).filter(f => f.dataset.nombre);

function filtrarYOrdenar() {
  let filtro = campoBusqueda.value.trim().toUpperCase();
  // Sanitizar: solo letras y espacios simples
  filtro = filtro.replace(/[^A-ZÁÉÍÓÚÑ ]/g, '').replace(/\s+/g, ' ').trim();
  campoBusqueda.value = filtro;

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

  // Oculta paginación solo cuando hay filtro activo
  paginacion.style.display = filtro ? 'none' : '';
}

campoBusqueda.addEventListener('input', filtrarYOrdenar);
ordenarSelect.addEventListener('change', filtrarYOrdenar);

</script>
@endsection
