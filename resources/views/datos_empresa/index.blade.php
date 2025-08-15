@extends('layouts.dashboard')
@section('title', 'Datos de la Empresa')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/datos_empresa.css') }}">
@endpush

@section('content')

@if (session('success'))
<script>
  document.addEventListener('DOMContentLoaded', () => {
    Swal.fire({
      icon: 'success',
      title: 'Datos de la Empresa',
      text: '{{ session("success") }}',
      confirmButtonText: 'OK',
      confirmButtonColor: '#007bff'
    });
  });
</script>
@endif

<div id="datos-empresa" class="empresa-wrapper"> {{-- Agregado wrapper para encapsular el CSS --}}
  <div class="datos-empresa-wrapper">
    <div class="titulo-con-linea">
      <h2>Datos de la Empresa</h2>
    </div>

    <div class="resumen-empresa">
      <p><strong>Nombre:</strong> {{ $datos['nom_empresa'] }}</p>
      <p><strong>Contacto:</strong> {{ $datos['contacto'] }}</p>
      <p><strong>Dirección:</strong> {{ $datos['direccion'] }}</p>
      <p><strong>País:</strong> {{ $datos['pais'] }}</p>
      <p><strong>Ciudad:</strong> {{ $datos['ciudad'] }}</p>
      <p><strong>Departamento:</strong> {{ $datos['departamento'] }}</p>
      <p><strong>Correo:</strong> {{ $datos['email'] }}</p>
      <p><strong>Teléfono Fijo:</strong> {{ $datos['num_fijo'] }}</p>
      <p><strong>Teléfono Celular:</strong> {{ $datos['num_celular'] }}</p>
      <p><strong>Página Web:</strong> <a href="{{ $datos['pag_web'] }}" target="_blank">{{ $datos['pag_web'] }}</a></p>
    </div>

    <button class="btn btn-nuevo" id="btnEditarEmpresa">Editar Datos</button>
  </div>

  <!-- Modal para editar datos -->
  <div class="modal-rol" id="modalEditarEmpresa" style="display: none;">
    <div class="modal-contenido">
      <h3 class="titulo-modal">Editar Datos de la Empresa</h3>
      <form id="formEditarEmpresa">
        <input type="hidden" id="codEmpresa" value="{{ $datos['cod_empresa'] }}">

        @foreach([
          'nom_empresa' => 'Nombre',
          'contacto' => 'Contacto',
          'direccion' => 'Dirección',
          'pais' => 'País',
          'ciudad' => 'Ciudad',
          'departamento' => 'Departamento',
          'cod_municipio' => 'Código Municipio',
          'cod_postal' => 'Código Postal',
          'email' => 'Correo',
          'num_fijo' => 'Teléfono Fijo',
          'num_celular' => 'Teléfono Celular',
          'fax' => 'Fax',
          'pag_web' => 'Página Web',
          'usr_registro' => 'Usuario que registra'
        ] as $campo => $label)
        <div class="form-group">
          <label>{{ $label }}:</label>
          <input type="{{ $campo === 'email' ? 'email' : 'text' }}" name="{{ $campo }}" value="{{ $datos[$campo] }}" required>
        </div>
        @endforeach

        <div class="modal-botones">
          <button type="submit" class="btn btn-success">Guardar</button>
          <button type="button" class="btn btn-danger" id="cancelarEdicion">Cancelar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  const modal = document.getElementById('modalEditarEmpresa');
  const btnAbrir = document.getElementById('btnEditarEmpresa');
  const btnCancelar = document.getElementById('cancelarEdicion');

  btnAbrir.addEventListener('click', () => modal.style.display = 'flex');
  btnCancelar.addEventListener('click', () => modal.style.display = 'none');

  document.getElementById('formEditarEmpresa').addEventListener('submit', async function (e) {
    e.preventDefault();
    const codEmpresa = document.getElementById('codEmpresa').value;
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    try {
      const res = await fetch(`http://localhost:3000/api/datos_empresa/${codEmpresa}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });

      const result = await res.json();

      if (res.ok) {
        Swal.fire('Actualizado', result.mensaje, 'success').then(() => location.reload());
      } else {
        Swal.fire('Error', result.mensaje || 'Error al actualizar', 'error');
      }
    } catch (error) {
      Swal.fire('Error', 'No se pudo conectar con el servidor', 'error');
    }
  });
</script>
@endsection
