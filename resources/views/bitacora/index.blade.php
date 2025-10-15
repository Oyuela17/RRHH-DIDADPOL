{{-- resources/views/bitacora/index.blade.php --}}
@extends('layouts.dashboard')
@section('title', 'Bitácora del Sistema')

@section('content')
<link rel="stylesheet" href="{{ asset('css/bitacora.css') }}">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if (session('success'))
<script>
  document.addEventListener('DOMContentLoaded', () => {
    Swal.fire({
      icon: 'success',
      title: 'Bitácora',
      text: '{{ session("success") }}',
      confirmButtonText: 'OK',
      confirmButtonColor: '#007bff'
    });
  });
</script>
@endif

<div class="bitacora-wrapper">
  <div class="titulo-con-linea">
    <h2>Bitácora del Sistema</h2>
  </div>

  <div class="acciones-superiores">
    <div class="lado-izquierdo">
      <input type="text" id="busquedaInput" class="form-control" placeholder="Buscar (usuario, tabla, descripción)">
    </div>

    <div class="lado-derecho">
      <div class="mostrar-registros">
        <label>Ordenar por</label>
        <select id="ordenarSelect">
          <option value="fecha">Fecha (recientes primero)</option>
          <option value="usuario">Usuario (A-Z)</option>
          <option value="accion">Acción (A-Z)</option>
          <option value="tabla">Tabla (A-Z)</option>
        </select>

        <label>Mostrar</label>
        <select id="cantidadSelect">
          @foreach([5,10,15,20] as $op)
            <option value="{{ $op }}">{{ $op }}</option>
          @endforeach
        </select>
        <span>registros</span>
      </div>
    </div>
  </div>

  <div class="bitacora-container">
    <table class="personas-table">
      <thead>
        <tr>
          <th>Fecha</th>
          <th>Usuario</th>
          <th>Acción</th>
          <th>Tabla</th>
          <th>Descripción</th>
          <th>IP</th>
        </tr>
      </thead>
      <tbody id="tablaBitacora">
        @forelse ($bitacora as $r)
          <tr
            data-fecha="{{ $r['fecha'] ?? '' }}"
            data-usuario="{{ Str::upper($r['usuario_nombre'] ?? '') }}"
            data-accion="{{ Str::upper($r['accion'] ?? '') }}"
            data-tabla="{{ Str::upper($r['tabla'] ?? '') }}"
            data-descripcion="{{ Str::upper($r['descripcion'] ?? '') }}"
          >
            <td>{{ \Carbon\Carbon::parse($r['fecha'] ?? null)->format('Y-m-d H:i:s') ?: '—' }}</td>
            <td>{{ $r['usuario_nombre'] ?? '—' }}</td>
            <td>{{ $r['accion'] ?? '—' }}</td>
            <td>{{ $r['tabla'] ?? '—' }}</td>
            <td class="texto-corto" title="{{ $r['descripcion'] ?? '' }}">{{ $r['descripcion'] ?? '—' }}</td>
            <td>{{ $r['ip_origen'] ?? '—' }}</td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-center">No hay registros en la bitácora.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div id="paginacion" class="paginacion-wrapper" style="margin-top: 20px;"></div>

<script>
// --- Filtros + orden + paginación (cliente) ---
const inputBusqueda = document.getElementById('busquedaInput');
const ordenarSelect = document.getElementById('ordenarSelect');
const cantidadSelect = document.getElementById('cantidadSelect');
const tabla = document.getElementById('tablaBitacora');
const paginacion = document.getElementById('paginacion');

let todasLasFilas = Array.from(tabla.querySelectorAll('tr'));
let paginaActual = 1;

function renderizarFilas() {
  const filtro = (inputBusqueda.value || '').toUpperCase();
  const criterio = ordenarSelect.value;
  const cantidad = parseInt(cantidadSelect.value);

  // Filtrar por usuario/tabla/descripcion
  let filtradas = todasLasFilas.filter(fila => {
    const usuario = fila.dataset.usuario || '';
    const tabla   = fila.dataset.tabla || '';
    const desc    = fila.dataset.descripcion || '';
    return (usuario.includes(filtro) || tabla.includes(filtro) || desc.includes(filtro));
  });

  // Ordenar
  filtradas.sort((a,b) => {
    if (criterio === 'fecha') return new Date(b.dataset.fecha) - new Date(a.dataset.fecha);
    if (criterio === 'usuario') return (a.dataset.usuario || '').localeCompare(b.dataset.usuario || '');
    if (criterio === 'accion')  return (a.dataset.accion  || '').localeCompare(b.dataset.accion  || '');
    if (criterio === 'tabla')   return (a.dataset.tabla   || '').localeCompare(b.dataset.tabla   || '');
    return 0;
  });

  // Paginación
  const totalPaginas = Math.ceil(filtradas.length / cantidad);
  paginaActual = Math.min(paginaActual, totalPaginas || 1);
  const inicio = (paginaActual - 1) * cantidad;
  const visibles = filtradas.slice(inicio, inicio + cantidad);

  // Render
  tabla.innerHTML = '';
  visibles.forEach(f => tabla.appendChild(f));

  // Paginación UI
  if (totalPaginas > 1) {
    renderizarPaginacion(totalPaginas);
    paginacion.style.display = 'flex';
  } else {
    paginacion.innerHTML = '';
    paginacion.style.display = 'none';
  }
}

function renderizarPaginacion(totalPaginas) {
  paginacion.innerHTML = '';

  const crearBoton = (num, texto = null, activo = false, deshabilitado = false) => {
    const btn = document.createElement('button');
    btn.textContent = texto || num;
    btn.className = 'btn btn-outline-primary btn-sm mx-1';
    if (activo) btn.classList.add('active');
    if (deshabilitado) btn.disabled = true;
    btn.onclick = () => { paginaActual = num; renderizarFilas(); };
    return btn;
  };

  paginacion.appendChild(crearBoton(paginaActual - 1, '‹', false, paginaActual === 1));
  for (let i = 1; i <= totalPaginas; i++) paginacion.appendChild(crearBoton(i, null, i === paginaActual));
  paginacion.appendChild(crearBoton(paginaActual + 1, '›', false, paginaActual === totalPaginas));
}

// Eventos
inputBusqueda.addEventListener('keyup', () => { paginaActual = 1; renderizarFilas(); });
ordenarSelect.addEventListener('change', () => { paginaActual = 1; renderizarFilas(); });
cantidadSelect.addEventListener('change', () => { paginaActual = 1; renderizarFilas(); });

// Inicial
renderizarFilas();
</script>
@endsection
