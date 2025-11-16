@extends('layouts.dashboard')
@section('title', 'Mantenimiento de Puestos')

@section('content')

@php
    // Permisos del módulo EMPLEADOS
    $accionesEmpleados = $accionesPermitidas['EMPLEADOS'] ?? [
        'crear'      => false,
        'actualizar' => false,
        'eliminar'   => false,
    ];
@endphp

<div class="puestos-wrapper">
  <div class="titulo-con-linea">
    <h2>Mantenimiento de Puestos</h2>
  </div>

  <div class="acciones-superiores">
    <div class="lado-izquierdo">
      <input type="text" id="busqueda" class="form-control"
             placeholder="Buscar puesto..." oninput="filtrarPuestos()">
    </div>
    <div class="lado-derecho">
      <button
        class="btn btn-nuevo"
        id="btnMostrarModal"
        data-bloqueado="{{ $accionesEmpleados['crear'] ? '0' : '1' }}"
      >
        <i class="fas fa-plus"></i> Nuevo Puesto
      </button>
    </div>
  </div>

  <div class="puestos-container">
    <table class="puestos-table" id="tablaPuestos">
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Funciones</th>
          <th>Sueldo Base</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody id="cuerpoTabla"></tbody>
    </table>
  </div>
</div>

<!-- Modal -->
<div class="modal-rol" id="modalPuesto" style="display: none;">
  <div class="modal-contenido">
    <h3 class="titulo-modal" id="tituloModal">Registrar Puesto</h3>
    <form id="formPuesto">
      <input type="hidden" id="puestoId">
      <input type="hidden" id="fuente" value="1">

      <div class="form-group">
        <label>Nombre del Puesto:</label>
        <input
          type="text"
          id="nombre"
          required
          maxlength="50"
          pattern="^[A-ZÁÉÍÓÚÑ ]+$"
          title="Solo letras y espacios (sin números ni símbolos)."
        >
      </div>

      <div class="form-group">
        <label>Funciones:</label>
        <input
          type="text"
          id="funciones"
          required
          maxlength="200"
          pattern="^[A-ZÁÉÍÓÚÑ ]+$"
          title="Solo letras y espacios (sin números ni símbolos)."
        >
      </div>

      <div class="form-group">
        <label>Sueldo Base:</label>
        <input type="number" id="sueldo" required step="0.01" min="0">
      </div>

      <div class="modal-botones">
        <button type="submit" class="btn btn-success">Guardar</button>
        <button type="button" class="btn btn-danger" id="cancelarPuesto">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<link rel="stylesheet" href="{{ asset('css/puestos.css') }}">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// ===== Permisos del módulo EMPLEADOS =====
const P_CAN_CREATE_EMPLEADOS = {{ $accionesEmpleados['crear'] ? 'true' : 'false' }};
const P_CAN_UPDATE_EMPLEADOS = {{ $accionesEmpleados['actualizar'] ? 'true' : 'false' }};
const P_CAN_DELETE_EMPLEADOS = {{ $accionesEmpleados['eliminar'] ? 'true' : 'false' }};

const api          = 'https://rrhh-didadpol-1.onrender.com/api/puestos';
const cuerpoTabla  = document.getElementById('cuerpoTabla');
const modal        = document.getElementById('modalPuesto');
const btnNuevo     = document.getElementById('btnMostrarModal');
const cancelar     = document.getElementById('cancelarPuesto');
const form         = document.getElementById('formPuesto');
const idInput      = document.getElementById('puestoId');
const inputNombre  = document.getElementById('nombre');
const inputFunciones = document.getElementById('funciones');
const inputSueldo  = document.getElementById('sueldo');
let   modo         = 'crear';

// ====== FUNCIÓN GENERAL DE VALIDACIÓN PARA CAMPOS DE TEXTO ======
let ultimoAviso = 0; // control de frecuencia de alertas

function validarSoloLetras(input) {
  input.addEventListener('input', () => {
    const original = input.value;
    const upper    = original.toUpperCase();

    // Detectar si hay caracteres inválidos
    const hayInvalidos = /[^A-ZÁÉÍÓÚÑ ]/.test(upper);

    // Limpiar el texto visualmente
    const limpio = upper
      .replace(/[^A-ZÁÉÍÓÚÑ ]+/g, '')
      .replace(/\s{2,}/g, ' ')
      .replace(/^\s+/, '');

    input.value = limpio;

    // Mostrar alerta solo si se intentó poner algo inválido
    if (hayInvalidos) {
      const ahora = Date.now();
      if (ahora - ultimoAviso > 1200) {
        ultimoAviso = ahora;
        Swal.fire({
          icon: 'warning',
          title: 'Entrada inválida',
          text: 'No se aceptan números ni símbolos.',
          timer: 1400,
          showConfirmButton: false
        });
      }
    }
  });
}

// Aplicar validación a ambos campos
validarSoloLetras(inputNombre);
validarSoloLetras(inputFunciones);

// ====== SUELDO: solo valores positivos ======
inputSueldo.addEventListener('input', () => {
  const val = parseFloat(inputSueldo.value);
  if (isNaN(val) || val < 0) inputSueldo.value = '';
});

// ====== Modal NUEVO ======
btnNuevo.addEventListener('click', () => {
  if (!P_CAN_CREATE_EMPLEADOS) {
    Swal.fire({
      icon: 'error',
      title: 'Acción no permitida',
      text: 'No tienes permiso para crear puestos.',
    });
    return;
  }

  modo = 'crear';
  form.reset();
  idInput.value = '';
  document.getElementById('tituloModal').textContent = 'Registrar Puesto';
  modal.style.display = 'flex';
});

// Cancelar
cancelar.addEventListener('click', () => modal.style.display = 'none');

// ====== Guardar (crear/editar) ======
form.addEventListener('submit', async e => {
  e.preventDefault();

  if (modo === 'crear' && !P_CAN_CREATE_EMPLEADOS) {
    Swal.fire({
      icon: 'error',
      title: 'Acción no permitida',
      text: 'No tienes permiso para crear puestos.',
    });
    return;
  }
  if (modo === 'editar' && !P_CAN_UPDATE_EMPLEADOS) {
    Swal.fire({
      icon: 'error',
      title: 'Acción no permitida',
      text: 'No tienes permiso para editar puestos.',
    });
    return;
  }

  // Validaciones antes de enviar
  if (!inputNombre.value) {
    Swal.fire('Validación','El nombre del puesto es obligatorio.','warning');
    return;
  }
  if (!inputFunciones.value) {
    Swal.fire('Validación','Debes completar las funciones.','warning');
    return;
  }
  if (!inputSueldo.value || parseFloat(inputSueldo.value) < 0) {
    Swal.fire('Validación','Ingresa un sueldo base válido (0 o mayor).','warning');
    return;
  }

  const data = {
    nom_puesto:       inputNombre.value.trim(),
    funciones_puesto: inputFunciones.value.trim(),
    sueldo_base:      parseFloat(inputSueldo.value),
    fec_registro:     new Date().toISOString(),
    usr_registro:     'admin',
    cod_fuente_financiamiento: 1
  };
  const id = idInput.value;

  try {
    const res = await fetch(modo === 'crear' ? api : `${api}/${id}`, {
      method: modo === 'crear' ? 'POST' : 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });

    const resJson = await res.json();
    if (res.ok) {
      Swal.fire('Éxito', resJson.mensaje || 'Operación realizada correctamente', 'success');
      modal.style.display = 'none';
      cargarPuestos();
    } else {
      throw new Error(resJson.error || 'Error al guardar');
    }
  } catch (err) {
    Swal.fire('Error', err.message, 'error');
  }
});

// ====== Carga de tabla ======
function cargarPuestos() {
  fetch(api + '?detalles=true')
    .then(res => res.json())
    .then(puestos => {
      cuerpoTabla.innerHTML = '';
      puestos.forEach(p => {
        cuerpoTabla.innerHTML += `
          <tr>
            <td>${p.nom_puesto}</td>
            <td>${p.funciones_puesto}</td>
            <td>L. ${parseFloat(p.sueldo_base).toFixed(2)}</td>
            <td class="acciones-botones">
              <button class="btn btn-warning" onclick='editar(${JSON.stringify(p)})'>Editar</button>
              <button class="btn btn-danger" onclick="eliminar(${p.cod_puesto}, '${p.nom_puesto.replace(/'/g, "\\'")}')">Eliminar</button>
            </td>
          </tr>`;
      });
    })
    .catch(() => {
      cuerpoTabla.innerHTML =
        '<tr><td colspan="4" class="text-center">No se pudo cargar la lista de puestos.</td></tr>';
    });
}

// ====== Editar ======
function editar(p) {
  if (!P_CAN_UPDATE_EMPLEADOS) {
    Swal.fire({
      icon: 'error',
      title: 'Acción no permitida',
      text: 'No tienes permiso para editar puestos.',
    });
    return;
  }

  modo = 'editar';
  idInput.value        = p.cod_puesto;
  inputNombre.value    = (p.nom_puesto || '').toString().toUpperCase();
  inputFunciones.value = (p.funciones_puesto || '').toString().toUpperCase();
  inputSueldo.value    = p.sueldo_base ?? '';
  document.getElementById('tituloModal').textContent = 'Editar Puesto';
  modal.style.display = 'flex';
}

// ====== Eliminar ======
async function eliminar(id, nombre) {
  if (!P_CAN_DELETE_EMPLEADOS) {
    Swal.fire({
      icon: 'error',
      title: 'Acción no permitida',
      text: 'No tienes permiso para eliminar puestos.',
    });
    return;
  }

  Swal.fire({
    title: '¿Eliminar?',
    text: `¿Deseas eliminar el puesto "${nombre}"?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar'
  }).then(async result => {
    if (result.isConfirmed) {
      try {
        const res  = await fetch(`${api}/${id}`, { method: 'DELETE' });
        const json = await res.json();
        if (res.ok) {
          Swal.fire('Eliminado', json.mensaje || 'Puesto eliminado correctamente', 'success');
          cargarPuestos();
        } else {
          throw new Error(json.error || 'Error al eliminar');
        }
      } catch (err) {
        Swal.fire('Error', err.message, 'error');
      }
    }
  });
}

// ====== Filtro búsqueda ======
function filtrarPuestos() {
  const valor = document.getElementById('busqueda').value.toLowerCase();
  const filas = cuerpoTabla.querySelectorAll('tr');
  filas.forEach(fila => {
    fila.style.display = fila.textContent.toLowerCase().includes(valor) ? '' : 'none';
  });
}

document.addEventListener('DOMContentLoaded', cargarPuestos);
</script>
@endsection
