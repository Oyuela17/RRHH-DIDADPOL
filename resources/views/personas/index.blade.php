@extends('layouts.dashboard')
@section('title', 'Listado de Personas')

@section('content')
<link rel="stylesheet" href="{{ asset('css/personas.css') }}">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if (session('success'))
<script>
  document.addEventListener('DOMContentLoaded', () => {
    Swal.fire({
      icon: 'success',
      title: 'Personas',
      text: '{{ session("success") }}',
      confirmButtonText: 'OK',
      confirmButtonColor: '#007bff'
    });
  });
</script>
@endif

<div class="personas-wrapper">
  <div class="titulo-con-linea">
    <h2>Gestión de Personas</h2>
  </div>

  <div class="acciones-superiores">
    <div class="lado-izquierdo">
      <input type="text" id="busquedaInput" class="form-control" placeholder="Buscar por nombre">
    </div>

    <div class="lado-derecho">
      <div class="mostrar-registros">
        <label>Ordenar por</label>
        <select id="ordenarSelect">
          <option value="nombre">Nombre (A-Z)</option>
          <option value="fecha">Fecha de creación</option>
        </select>

        <label>Mostrar</label>
        <select id="cantidadSelect">
          @foreach([5, 10, 15, 20] as $opcion)
            <option value="{{ $opcion }}">{{ $opcion }}</option>
          @endforeach
        </select>
        <span>registros</span>
      </div>
    </div>
  </div>

  <div class="personas-container">
    <table class="personas-table">
      <thead>
        <tr>
          <th>Nombre</th>
          <th>DNI</th>
          <th>Teléfono</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody id="tablaPersonas">
        @forelse ($personas as $p)
        <tr data-nombre="{{ $p['nombre_completo'] ?? '' }}" data-fecha="{{ $p['created_at'] ?? '' }}">
          <td>{{ $p['nombre_completo'] ?? '—' }}</td>
          <td>{{ $p['dni'] ?? '—' }}</td>
          <td>{{ $p['numero'] ?? '—' }}</td>
          <td>
            <button class="btn btn-info btn-sm" onclick="verDetalles({{ json_encode($p) }})">Ver Detalles</button>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="4" class="text-center">No hay personas registradas.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Detalles Persona Compacto -->
<div id="modalDetalles" class="modal-detalles">
  <div class="contenido compacto">
    <span class="cerrar" onclick="cerrarModal()">&times;</span>
    <h2 id="nombrePersona" class="nombre-persona">NOMBRE COMPLETO</h2>
    <div class="dni centrado" id="dniPersona">0801-2002-08924</div>

    <div class="bloque-detalle">
      <div class="fila"><span>Email:</span><span class="valor" id="emailPersona">—</span></div>
      <div class="fila"><span>Teléfono:</span><span class="valor" id="telefonoPersona">—</span></div>
      <div class="fila"><span>Dirección:</span><span class="valor" id="direccionPersona">—</span></div>
      <div class="fila"><span>Municipio:</span><span class="valor" id="municipioPersona">—</span></div>
      <div class="fila"><span>Departamento:</span><span class="valor" id="departamentoPersona">—</span></div>
      <div class="fila"><span>Género:</span><span class="valor" id="generoPersona">—</span></div>
      <div class="fila"><span>Estado Civil:</span><span class="valor" id="estadoCivilPersona">—</span></div>
      <div class="fila"><span>Fecha Nacimiento:</span><span class="valor" id="fechaNacimientoPersona">—</span></div>
      <div class="fila"><span>Lugar Nacimiento:</span><span class="valor" id="lugarNacimientoPersona">—</span></div>
      <div class="fila"><span>Nacionalidad:</span><span class="valor" id="nacionalidadPersona">—</span></div>
      <div class="fila"><span>Contacto Emergencia:</span><span class="valor" id="contactoEmergenciaPersona">—</span></div>
      <div class="fila"><span>Tel. Emergencia:</span><span class="valor" id="telEmergenciaPersona">—</span></div>
    </div>
  </div>
</div>

<div id="paginacion" class="paginacion-wrapper" style="margin-top: 20px;"></div>

<script>
function verDetalles(persona) {
  document.getElementById('nombrePersona').innerText = persona.nombre_completo || '—';
  document.getElementById('dniPersona').innerText = persona.dni || '—';
  document.getElementById('emailPersona').innerText = persona.email_trabajo || '—';
  document.getElementById('telefonoPersona').innerText = persona.numero || '—';
  document.getElementById('direccionPersona').innerText = persona.direccion || '—';
  document.getElementById('municipioPersona').innerText = persona.nombre_municipio || '—';
  document.getElementById('departamentoPersona').innerText = persona.nombre_departamento || '—';
  document.getElementById('generoPersona').innerText = persona.genero || '—';
  document.getElementById('estadoCivilPersona').innerText = persona.estado_civil || '—';

  const fechaRaw = persona.fec_nacimiento;
  let fechaFormateada = '—';
  if (fechaRaw) {
    const fecha = new Date(fechaRaw);
    const dia = String(fecha.getDate()).padStart(2, '0');
    const mes = String(fecha.getMonth() + 1).padStart(2, '0');
    const anio = fecha.getFullYear();
    fechaFormateada = `${dia}/${mes}/${anio}`;
  }
  document.getElementById('fechaNacimientoPersona').innerText = fechaFormateada;

  document.getElementById('lugarNacimientoPersona').innerText = persona.lugar_nacimiento || '—';
  document.getElementById('nacionalidadPersona').innerText = persona.nacionalidad || '—';
  document.getElementById('contactoEmergenciaPersona').innerText = persona.nombre_contacto_emergencia || '—';
  document.getElementById('telEmergenciaPersona').innerText = persona.telefono_emergencia || '—';

  document.getElementById('modalDetalles').style.display = 'flex';
}

function cerrarModal() {
  document.getElementById('modalDetalles').style.display = 'none';
}

// --- Filtros y paginación en tiempo real ---
const inputBusqueda = document.getElementById('busquedaInput');
const ordenarSelect = document.getElementById('ordenarSelect');
const cantidadSelect = document.getElementById('cantidadSelect');
const tabla = document.getElementById('tablaPersonas');
const paginacion = document.getElementById('paginacion');
let todasLasFilas = Array.from(tabla.querySelectorAll('tr'));
let paginaActual = 1;

function renderizarFilas() {
  const filtro = inputBusqueda.value.toUpperCase();
  const criterio = ordenarSelect.value;
  const cantidad = parseInt(cantidadSelect.value);

  // Filtrar
  let filtradas = todasLasFilas.filter(fila => {
    const nombre = fila.dataset.nombre?.toUpperCase() || '';
    return nombre.includes(filtro);
  });

  // Ordenar
  filtradas.sort((a, b) => {
    if (criterio === 'nombre') return a.dataset.nombre.localeCompare(b.dataset.nombre);
    if (criterio === 'fecha') return new Date(b.dataset.fecha) - new Date(a.dataset.fecha);
    return 0;
  });

  // Paginación
  const totalPaginas = Math.ceil(filtradas.length / cantidad);
  paginaActual = Math.min(paginaActual, totalPaginas || 1);
  const inicio = (paginaActual - 1) * cantidad;
  const visibles = filtradas.slice(inicio, inicio + cantidad);

  // Renderizar tabla
  tabla.innerHTML = '';
  visibles.forEach(fila => tabla.appendChild(fila));

  // Renderizar paginación
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
    btn.onclick = () => {
      paginaActual = num;
      renderizarFilas();
    };
    return btn;
  };

  paginacion.appendChild(crearBoton(paginaActual - 1, '‹', false, paginaActual === 1));

  for (let i = 1; i <= totalPaginas; i++) {
    paginacion.appendChild(crearBoton(i, null, i === paginaActual));
  }

  paginacion.appendChild(crearBoton(paginaActual + 1, '›', false, paginaActual === totalPaginas));
}

// Eventos
inputBusqueda.addEventListener('keyup', () => {
  paginaActual = 1;
  renderizarFilas();
});
ordenarSelect.addEventListener('change', () => {
  paginaActual = 1;
  renderizarFilas();
});
cantidadSelect.addEventListener('change', () => {
  paginaActual = 1;
  renderizarFilas();
});

// Inicial
renderizarFilas();
</script>

@endsection
