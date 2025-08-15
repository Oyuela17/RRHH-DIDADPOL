@extends('layouts.dashboard')
@section('title', 'Mantenimiento de Oficinas')

@section('content')
<div class="oficinas-wrapper">
  <div class="titulo-con-linea">
    <h2>Mantenimiento de Oficinas</h2>
  </div>

  <div class="acciones-superiores">
    <div class="lado-izquierdo">
      <input type="text" id="busqueda" class="form-control" placeholder="Buscar oficina..." oninput="filtrarOficinas()">
    </div>
    <div class="lado-derecho">
      <button class="btn btn-nuevo" id="btnMostrarModal">
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
          <input type="text" id="nombreOficina" required>
        </div>

        <div class="form-group">
          <label>Dirección:</label>
          <input type="text" id="direccion" required>
        </div>

        <div class="form-group">
          <label>Teléfono:</label>
          <input type="text" id="telefono" required>
        </div>

        <div class="form-group">
          <label>Encargado:</label>
          <input type="text" id="aCargo" required>
        </div>

        <div class="form-group">
          <label>Dirección Corta:</label>
          <input type="text" id="direccionCorta">
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
const api = 'http://localhost:3000/api/oficinas?detalles=true';
const apiBase = 'http://localhost:3000/api/oficinas';
const cuerpoTabla = document.getElementById('cuerpoTabla');
const modal = document.getElementById('modalOficina');
const btnNuevo = document.getElementById('btnMostrarModal');
const cancelar = document.getElementById('cancelarOficina');
const form = document.getElementById('formOficina');
const idInput = document.getElementById('oficinaId');
let modo = 'crear';

btnNuevo.addEventListener('click', () => {
  modo = 'crear';
  form.reset();
  idInput.value = '';
  document.getElementById('tituloModal').textContent = 'Registrar Oficina';
  modal.style.display = 'flex';
});

cancelar.addEventListener('click', () => modal.style.display = 'none');

form.addEventListener('submit', async e => {
  e.preventDefault();

  const data = {
    cod_municipio: document.getElementById('codMunicipio').value,
    direccion: document.getElementById('direccion').value.trim(),
    nom_oficina: document.getElementById('nombreOficina').value.trim(),
    a_cargo: document.getElementById('aCargo').value.trim(),
    num_telefono: document.getElementById('telefono').value.trim(),
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
      Swal.fire('Éxito', resJson.mensaje, 'success');
      modal.style.display = 'none';
      cargarOficinas();
    } else {
      throw new Error(resJson.error || 'Error');
    }
  } catch (err) {
    Swal.fire('Error', err.message, 'error');
  }
});

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
            <td class="telefono">${o.num_telefono}</td>
            <td>${o.a_cargo}</td>
            <td class="acciones-botones">
              <button class="btn btn-warning" onclick='editar(${JSON.stringify(o)})'>Editar</button>
              <button class="btn btn-danger" onclick="eliminar(${o.cod_oficina}, '${o.nom_oficina}')">Eliminar</button>
            </td>
          </tr>`;
      });
    });
}

function editar(oficina) {
  modo = 'editar';
  idInput.value = oficina.cod_oficina;

  cargarMunicipios(() => {
    document.getElementById('codMunicipio').value = oficina.cod_municipio;
  });

  document.getElementById('nombreOficina').value = oficina.nom_oficina;
  document.getElementById('direccion').value = oficina.direccion;
  document.getElementById('telefono').value = oficina.num_telefono;
  document.getElementById('aCargo').value = oficina.a_cargo;
  document.getElementById('direccionCorta').value = oficina.direccion_corta;
  document.getElementById('asignableEmpleados').value = oficina.asignable_empleados ? 'true' : 'false';
  document.getElementById('tituloModal').textContent = 'Editar Oficina';
  modal.style.display = 'flex';
}

async function eliminar(id, nombre) {
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
          Swal.fire('Eliminado', json.mensaje, 'success');
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

function filtrarOficinas() {
  const valor = document.getElementById('busqueda').value.toLowerCase();
  const filas = cuerpoTabla.querySelectorAll('tr');
  filas.forEach(fila => {
    fila.style.display = fila.textContent.toLowerCase().includes(valor) ? '' : 'none';
  });
}

function cargarMunicipios(callback = null) {
  fetch('http://localhost:3000/api/municipios')
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
