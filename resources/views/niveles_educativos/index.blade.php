@extends('layouts.dashboard')
@section('title', 'Mantenimiento de Niveles Educativos')

@section('content')
<div class="niveles-wrapper">
  <div class="titulo-con-linea">
    <h2>Mantenimiento de Niveles Educativos</h2>
  </div>

  <div class="acciones-superiores">
    <div class="lado-izquierdo">
      <input type="text" id="busqueda" class="form-control" placeholder="Buscar nivel educativo..." oninput="filtrarNiveles()">
    </div>
    <div class="lado-derecho">
      <button class="btn btn-nuevo" id="btnMostrarModal">
        <i class="fas fa-plus"></i> Nuevo Nivel
      </button>
    </div>
  </div>

  <div class="niveles-container">
    <table class="niveles-table" id="tablaNiveles">
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Descripción</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody id="cuerpoTabla"></tbody>
    </table>
  </div>
</div>

<!-- Modal -->
<div class="modal-rol" id="modalNivel" style="display: none;">
  <div class="modal-contenido">
    <h3 class="titulo-modal" id="tituloModal">Registrar Nivel Educativo</h3>
    <form id="formNivel">
      <input type="hidden" id="nivelId">
      <div class="form-group">
        <label>Nombre del Nivel:</label>
        <input type="text" id="nombre" required>
      </div>
      <div class="form-group">
        <label>Descripción:</label>
        <input type="text" id="descripcion" required>
      </div>
      <div class="modal-botones">
        <button type="submit" class="btn btn-success">Guardar</button>
        <button type="button" class="btn btn-danger" id="cancelarNivel">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<link rel="stylesheet" href="{{ asset('css/niveles_educativos.css') }}">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
const api = 'http://localhost:3000/api/niveles-educativos';
const cuerpoTabla = document.getElementById('cuerpoTabla');
const modal = document.getElementById('modalNivel');
const btnNuevo = document.getElementById('btnMostrarModal');
const cancelar = document.getElementById('cancelarNivel');
const form = document.getElementById('formNivel');
const idInput = document.getElementById('nivelId');
let modo = 'crear';

btnNuevo.addEventListener('click', () => {
  modo = 'crear';
  form.reset();
  idInput.value = '';
  document.getElementById('tituloModal').textContent = 'Registrar Nivel Educativo';
  modal.style.display = 'flex';
});

cancelar.addEventListener('click', () => modal.style.display = 'none');

form.addEventListener('submit', async e => {
  e.preventDefault();
  const data = {
    nom_nivel: document.getElementById('nombre').value.trim().toUpperCase(),
    descripcion: document.getElementById('descripcion').value.trim(),
    fec_modificacion: new Date().toISOString(),
    usr_modificacion: 'admin'
  };

  if (modo === 'crear') {
    data.fec_registro = data.fec_modificacion;
    data.usr_registro = data.usr_modificacion;
  }

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
      cargarNiveles();
    } else {
      throw new Error(resJson.error || 'Error al guardar');
    }
  } catch (err) {
    Swal.fire('Error', err.message, 'error');
  }
});

function cargarNiveles() {
  fetch(`${api}?detalles=true`)
    .then(res => res.json())
    .then(niveles => {
      cuerpoTabla.innerHTML = '';
      niveles.forEach(n => {
        cuerpoTabla.innerHTML += `
          <tr>
            <td>${n.nom_nivel}</td>
            <td>${n.descripcion}</td>
            <td class="acciones-botones">
              <button class="btn btn-warning" onclick='editar(${JSON.stringify(n)})'>Editar</button>
              <button class="btn btn-danger" onclick="eliminar(${n.cod_nivel_educativo}, '${n.nom_nivel}')">Eliminar</button>
            </td>
          </tr>`;
      });
    });
}

function editar(n) {
  modo = 'editar';
  idInput.value = n.cod_nivel_educativo;
  document.getElementById('nombre').value = n.nom_nivel;
  document.getElementById('descripcion').value = n.descripcion;
  document.getElementById('tituloModal').textContent = 'Editar Nivel Educativo';
  modal.style.display = 'flex';
}

async function eliminar(id, nombre) {
  Swal.fire({
    title: '¿Eliminar?',
    text: `¿Deseas eliminar el nivel "${nombre}"?`,
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
          cargarNiveles();
        } else {
          throw new Error(json.error || 'Error al eliminar');
        }
      } catch (err) {
        Swal.fire('Error', err.message, 'error');
      }
    }
  });
}

function filtrarNiveles() {
  const valor = document.getElementById('busqueda').value.toLowerCase();
  const filas = cuerpoTabla.querySelectorAll('tr');
  filas.forEach(fila => {
    fila.style.display = fila.textContent.toLowerCase().includes(valor) ? '' : 'none';
  });
}

document.addEventListener('DOMContentLoaded', cargarNiveles);
</script>
@endsection
