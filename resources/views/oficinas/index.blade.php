@extends('layouts.dashboard')
@section('title', 'Mantenimiento de Oficinas')

@section('content')

@php
    // Permisos del módulo EMPLEADOS
    $accionesEmpleados = $accionesPermitidas['EMPLEADOS'] ?? [
        'crear'      => false,
        'actualizar' => false,
        'eliminar'   => false,
    ];
@endphp

<div class="oficinas-wrapper">
  <div class="titulo-con-linea">
    <h2>Mantenimiento de Oficinas</h2>
  </div>

  <div class="acciones-superiores">
    <div class="lado-izquierdo">
      <input type="text" id="busqueda" class="form-control"
             placeholder="Buscar oficina..." oninput="filtrarOficinas()">
    </div>
    <div class="lado-derecho">
      <button
        class="btn btn-nuevo"
        id="btnMostrarModal"
        data-bloqueado="{{ $accionesEmpleados['crear'] ? '0' : '1' }}"
      >
        <i class="fas fa-plus"></i> Nueva Oficina
      </button>
    </div>
  </div>

  <div class="oficinas-container">
    <table class="oficinas-table" id="tablaOficinas">
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Dirección</th>
          <th>Teléfono</th>
          <th>Encargado</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody id="cuerpoTabla"></tbody>
    </table>
  </div>
</div>

<!-- Modal -->
<div class="modal-rol" id="modalOficina" style="display: none;">
  <div class="modal-contenido">
    <h3 class="titulo-modal" id="tituloModal">Registrar Oficina</h3>

    <!-- Contenedor con scroll interno -->
    <div class="modal-cuerpo">
      <form id="formOficina">
        <input type="hidden" id="oficinaId">

        <div class="form-group">
          <label>Nombre de Oficina:</label>
          <input
            type="text"
            id="nombreOficina"
            required
            maxlength="80"
            pattern="^[A-ZÁÉÍÓÚÑ ]+$"
            title="Solo letras y espacios (sin números ni símbolos)."
          >
        </div>

        <div class="form-group">
          <label>Dirección:</label>
          <input type="text" id="direccion" required maxlength="150">
        </div>

        <div class="form-group">
          <label>Teléfono:</label>
          <input
            type="text"
            id="telefono"
            required
            maxlength="20"
            pattern="^[0-9\-]+$"
            title="Solo números y guiones."
          >
        </div>

        <div class="form-group">
          <label>Encargado:</label>
          <input
            type="text"
            id="aCargo"
            required
            maxlength="80"
            pattern="^[A-ZÁÉÍÓÚÑ ]+$"
            title="Solo letras y espacios (sin números ni símbolos)."
          >
        </div>

        <div class="form-group">
          <label>Dirección Corta:</label>
          <input type="text" id="direccionCorta" maxlength="80">
        </div>

        <div class="form-group">
          <label>Municipio:</label>
          <select id="codMunicipio" required>
            <option value="">Seleccione un municipio</option>
          </select>
        </div>

        <div class="form-group">
          <label>¿Asignable a empleados?</label>
          <select id="asignableEmpleados" required>
            <option value="true">Sí</option>
            <option value="false">No</option>
          </select>
        </div>
      </form>
    </div>

    <!-- Botones fijos abajo -->
    <div class="modal-botones">
      <button type="submit" form="formOficina" class="btn btn-success">Guardar</button>
      <button type="button" class="btn btn-danger" id="cancelarOficina">Cancelar</button>
    </div>
  </div>
</div>

<link rel="stylesheet" href="{{ asset('css/oficinas.css') }}">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// ===== Permisos del módulo EMPLEADOS =====
const P_CAN_CREATE_EMPLEADOS = {{ $accionesEmpleados['crear'] ? 'true' : 'false' }};
const P_CAN_UPDATE_EMPLEADOS = {{ $accionesEmpleados['actualizar'] ? 'true' : 'false' }};
const P_CAN_DELETE_EMPLEADOS = {{ $accionesEmpleados['eliminar'] ? 'true' : 'false' }};

const api     = 'https://rrhh-didadpol-1.onrender.com/api/oficinas?detalles=true';
const apiBase = 'https://rrhh-didadpol-1.onrender.com/api/oficinas';
const cuerpoTabla = document.getElementById('cuerpoTabla');
const modal       = document.getElementById('modalOficina');
const btnNuevo    = document.getElementById('btnMostrarModal');
const cancelar    = document.getElementById('cancelarOficina');
const form        = document.getElementById('formOficina');
const idInput     = document.getElementById('oficinaId');

const nombreOficina = document.getElementById('nombreOficina');
const telefono      = document.getElementById('telefono');
const aCargo        = document.getElementById('aCargo');

let modo = 'crear';

// ================== VALIDACIONES EN TIEMPO REAL ==================
let ultimoAviso = 0;
function avisar(texto) {
  const ahora = Date.now();
  if (ahora - ultimoAviso > 1200) {
    ultimoAviso = ahora;
    Swal.fire({
      icon: 'warning',
      title: 'Entrada inválida',
      text: texto,
      timer: 1400,
      showConfirmButton: false
    });
  }
}

// Solo letras y espacios (a MAYÚSCULAS)
function bindSoloLetras(input) {
  input.addEventListener('input', () => {
    const original = input.value;
    const upper = original.toUpperCase();
    const hayInvalidos = /[^A-ZÁÉÍÓÚÑ ]/.test(upper);
    input.value = upper
      .replace(/[^A-ZÁÉÍÓÚÑ ]+/g, '')
      .replace(/\s{2,}/g, ' ')
      .replace(/^\s+/, '');
    if (hayInvalidos) avisar('No se aceptan números ni símbolos.');
  });
}

// Solo números y guiones
function bindTelefono(input) {
  input.addEventListener('input', () => {
    const original = input.value;
    const hayInvalidos = /[^0-9\-]/.test(original);
    let limpio = original
      .replace(/[^0-9\-]+/g, '')
      .replace(/\-+/g, '-')
      .replace(/^-/, '')
      .replace(/-$/, '');
    input.value = limpio;
    if (hayInvalidos) avisar('Solo se permiten números y guiones.');
  });
}

bindSoloLetras(nombreOficina);
bindSoloLetras(aCargo);
bindTelefono(telefono);

// ================== CRUD ==================

// Nuevo
btnNuevo.addEventListener('click', () => {
  if (!P_CAN_CREATE_EMPLEADOS) {
    Swal.fire({
      icon: 'error',
      title: 'Acción no permitida',
      text: 'No tienes permiso para crear oficinas.',
    });
    return;
  }

  modo = 'crear';
  form.reset();
  idInput.value = '';
  document.getElementById('tituloModal').textContent = 'Registrar Oficina';
  modal.style.display = 'flex';
});

// Cancelar
cancelar.addEventListener('click', () => modal.style.display = 'none');

// Guardar (crear / editar)
form.addEventListener('submit', async e => {
  e.preventDefault();

  if (modo === 'editar' && !P_CAN_UPDATE_EMPLEADOS) {
    Swal.fire({
      icon: 'error',
      title: 'Acción no permitida',
      text: 'No tienes permiso para editar oficinas.',
    });
    return;
  }

  // Validaciones finales
  if (!nombreOficina.value || /[^A-ZÁÉÍÓÚÑ ]/.test(nombreOficina.value)) {
    return Swal.fire('Validación','Solo se permiten letras y espacios en el nombre.','warning');
  }
  if (!aCargo.value || /[^A-ZÁÉÍÓÚÑ ]/.test(aCargo.value)) {
    return Swal.fire('Validación','Solo se permiten letras y espacios en el encargado.','warning');
  }
  if (!telefono.value || /[^0-9\-]/.test(telefono.value)) {
    return Swal.fire('Validación','Solo se permiten números y guiones en el teléfono.','warning');
  }

  const data = {
    cod_municipio: document.getElementById('codMunicipio').value,
    direccion: document.getElementById('direccion').value.trim(),
    nom_oficina: nombreOficina.value.trim(),
    a_cargo: aCargo.value.trim(),
    num_telefono: telefono.value.trim(),
    usr_registro: 'admin',
    direccion_corta: document.getElementById('direccionCorta').value.trim(),
    asignable_empleados: document.getElementById('asignableEmpleados').value === 'true'
  };

  const id = idInput.value;

  try {
    const res = await fetch(modo === 'crear' ? apiBase : `${apiBase}/${id}`, {
      method: modo === 'crear' ? 'POST' : 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });

    const resJson = await res.json();
    if (res.ok) {
      Swal.fire('Éxito', resJson.mensaje || 'Operación realizada correctamente', 'success');
      modal.style.display = 'none';
      cargarOficinas();
    } else {
      throw new Error(resJson.error || 'Error');
    }
  } catch (err) {
    Swal.fire('Error', err.message, 'error');
  }
});

// Cargar tabla
function cargarOficinas() {
  fetch(api)
    .then(res => res.json())
    .then(oficinas => {
      cuerpoTabla.innerHTML = '';
      oficinas.forEach(o => {
        cuerpoTabla.innerHTML += `
          <tr>
            <td>${o.nom_oficina}</td>
            <td>${o.direccion}</td>
            <td class="telefono">${o.num_telefono ?? ''}</td>
            <td>${o.a_cargo ?? ''}</td>
            <td class="acciones-botones">
              <button class="btn btn-warning" onclick='editar(${JSON.stringify(o)})'>Editar</button>
              <button class="btn btn-danger" onclick="eliminar(${o.cod_oficina}, '${(o.nom_oficina || '').replace(/'/g, "\\'")}')">Eliminar</button>
            </td>
          </tr>`;
      });
    })
    .catch(() => {
      cuerpoTabla.innerHTML =
        '<tr><td colspan="5" class="text-center">No se pudo cargar la lista de oficinas.</td></tr>';
    });
}

// Editar
function editar(oficina) {
  if (!P_CAN_UPDATE_EMPLEADOS) {
    Swal.fire({
      icon: 'error',
      title: 'Acción no permitida',
      text: 'No tienes permiso para editar oficinas.',
    });
    return;
  }

  modo = 'editar';
  idInput.value = oficina.cod_oficina;

  cargarMunicipios(() => {
    document.getElementById('codMunicipio').value = oficina.cod_municipio;
  });

  nombreOficina.value = (oficina.nom_oficina || '').toString().toUpperCase();
  document.getElementById('direccion').value = oficina.direccion || '';
  telefono.value = oficina.num_telefono || '';
  aCargo.value = (oficina.a_cargo || '').toString().toUpperCase();
  document.getElementById('direccionCorta').value = oficina.direccion_corta || '';
  document.getElementById('asignableEmpleados').value = oficina.asignable_empleados ? 'true' : 'false';

  document.getElementById('tituloModal').textContent = 'Editar Oficina';
  modal.style.display = 'flex';
}

// Eliminar
async function eliminar(id, nombre) {
  if (!P_CAN_DELETE_EMPLEADOS) {
    Swal.fire({
      icon: 'error',
      title: 'Acción no permitida',
      text: 'No tienes permiso para eliminar oficinas.',
    });
    return;
  }

  Swal.fire({
    title: '¿Eliminar?',
    text: `¿Deseas eliminar la oficina "${nombre}"?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar'
  }).then(async result => {
    if (result.isConfirmed) {
      try {
        const res = await fetch(`${apiBase}/${id}`, { method: 'DELETE' });
        const json = await res.json();
        if (res.ok) {
          Swal.fire('Eliminado', json.mensaje || 'Oficina eliminada correctamente', 'success');
          cargarOficinas();
        } else {
          throw new Error(json.error || 'Error');
        }
      } catch (err) {
        Swal.fire('Error', err.message, 'error');
      }
    }
  });
}

// Filtro búsqueda
function filtrarOficinas() {
  const valor = document.getElementById('busqueda').value.toLowerCase();
  const filas = cuerpoTabla.querySelectorAll('tr');
  filas.forEach(fila => {
    fila.style.display = fila.textContent.toLowerCase().includes(valor) ? '' : 'none';
  });
}

// Cargar municipios
function cargarMunicipios(callback = null) {
  fetch('https://rrhh-didadpol-1.onrender.com/api/municipios')
    .then(res => res.json())
    .then(municipios => {
      const select = document.getElementById('codMunicipio');
      select.innerHTML = '<option value="">Seleccione un municipio</option>';
      municipios.forEach(m => {
        const option = document.createElement('option');
        option.value = m.cod_municipio;
        option.textContent = m.nombre;
        select.appendChild(option);
      });
      if (callback) callback();
    })
    .catch(error => {
      console.error('Error al cargar municipios:', error);
    });
}

document.addEventListener('DOMContentLoaded', () => {
  cargarOficinas();
  cargarMunicipios();
});
</script>
@endsection
