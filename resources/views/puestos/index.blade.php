@extends('layouts.dashboard')
@section('title', 'Mantenimiento de Puestos')

@section('content')
<div class="puestos-wrapper">
  <div class="titulo-con-linea">
    <h2>Mantenimiento de Puestos</h2>
  </div>

  <div class="acciones-superiores">
    <div class="lado-izquierdo">
      <input type="text" id="busqueda" class="form-control" placeholder="Buscar puesto..." oninput="filtrarPuestos()">
    </div>
    <div class="lado-derecho">
      <button class="btn btn-nuevo" id="btnMostrarModal">
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
        <input type="text" id="nombre" required>
      </div>
      <div class="form-group">
        <label>Funciones:</label>
        <input type="text" id="funciones" required>
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
const api = 'http://localhost:3000/api/puestos';
const cuerpoTabla = document.getElementById('cuerpoTabla');
const modal = document.getElementById('modalPuesto');
const btnNuevo = document.getElementById('btnMostrarModal');
const cancelar = document.getElementById('cancelarPuesto');
const form = document.getElementById('formPuesto');
const idInput = document.getElementById('puestoId');
let modo = 'crear';

btnNuevo.addEventListener('click', () => {
  modo = 'crear';
  form.reset();
  idInput.value = '';
  document.getElementById('tituloModal').textContent = 'Registrar Puesto';
  modal.style.display = 'flex';
});

cancelar.addEventListener('click', () => modal.style.display = 'none');

form.addEventListener('submit', async e => {
  e.preventDefault();
  const data = {
    nom_puesto: document.getElementById('nombre').value.trim(),
    funciones_puesto: document.getElementById('funciones').value.trim(),
    sueldo_base: parseFloat(document.getElementById('sueldo').value),
    fec_registro: new Date().toISOString(),
    usr_registro: 'admin',
    cod_fuente_financiamiento: parseInt(document.getElementById('fuente').value)
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
      Swal.fire('Éxito', resJson.mensaje, 'success');
      modal.style.display = 'none';
      cargarPuestos();
    } else {
      throw new Error(resJson.error || 'Error al guardar');
    }
  } catch (err) {
    Swal.fire('Error', err.message, 'error');
  }
});

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
              <button class="btn btn-danger" onclick="eliminar(${p.cod_puesto}, '${p.nom_puesto}')">Eliminar</button>
            </td>
          </tr>`;
      });
    });
}

function editar(p) {
  modo = 'editar';
  idInput.value = p.cod_puesto;
  document.getElementById('nombre').value = p.nom_puesto;
  document.getElementById('funciones').value = p.funciones_puesto;
  document.getElementById('sueldo').value = p.sueldo_base;
  document.getElementById('tituloModal').textContent = 'Editar Puesto';
  modal.style.display = 'flex';
}

async function eliminar(id, nombre) {
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
        const res = await fetch(`${api}/${id}`, { method: 'DELETE' });
        const json = await res.json();
        if (res.ok) {
          Swal.fire('Eliminado', json.mensaje, 'success');
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
