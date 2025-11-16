@extends('layouts.dashboard')
@section('title', 'Datos de la Empresa')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/datos_empresa.css') }}">
@endpush

@section('content')

@php
    $permDatosEmpresa = $accionesPermitidas['RECURSOS HUMANOS'] ?? [
        'crear'      => false,
        'actualizar' => false,
        'eliminar'   => false,
    ];
@endphp


@if (session('success'))
<script>
  document.addEventListener('DOMContentLoaded', () => {
    Swal.fire({
      icon: 'success',
      title: 'Datos de la Empresa',
      text: @json(session('success')),
      confirmButtonText: 'OK',
      confirmButtonColor: '#1a73e8'
    });
  });
</script>
@endif

<div id="datos-empresa" class="empresa-wrapper">

  <section class="empresa-card" aria-labelledby="titulo-datos-empresa">
    <header class="empresa-header">
      <div class="empresa-header__icon" aria-hidden="true"><i class="fas fa-building"></i></div>
      <div>
        <h2 id="titulo-datos-empresa" class="empresa-title">Datos de la Empresa</h2>
        <p class="empresa-subtitle">Información institucional y de contacto</p>
      </div>
      <div class="empresa-header__actions">
        <button class="btn btn-editar"
                id="btnEditarEmpresa"
                data-bloqueado="{{ $permDatosEmpresa['actualizar'] ? '0' : '1' }}">
          <i class="fas fa-edit"></i> Editar Datos
        </button>
      </div>
    </header>

    <div class="empresa-body">
      <dl class="empresa-info-grid">
        <div class="info-row">
          <dt><i class="fas fa-id-card"></i> Nombre</dt>
          <dd>{{ $datos['nom_empresa'] }}</dd>
        </div>

        <div class="info-row">
          <dt><i class="fas fa-user-tie"></i> Contacto</dt>
          <dd>{{ $datos['contacto'] }}</dd>
        </div>

        <div class="info-row info-row--full">
          <dt><i class="fas fa-map-marker-alt"></i> Dirección</dt>
          <dd>{{ $datos['direccion'] }}</dd>
        </div>

        <div class="info-row">
          <dt><i class="fas fa-flag"></i> País</dt>
          <dd>{{ $datos['pais'] }}</dd>
        </div>

        <div class="info-row">
          <dt><i class="fas fa-city"></i> Ciudad</dt>
          <dd>{{ $datos['ciudad'] }}</dd>
        </div>

        <div class="info-row">
          <dt><i class="fas fa-globe-americas"></i> Departamento</dt>
          <dd>{{ $datos['departamento'] }}</dd>
        </div>

        <div class="info-row">
          <dt><i class="fas fa-envelope"></i> Correo</dt>
          <dd>
            <a class="link" href="mailto:{{ $datos['email'] }}">{{ $datos['email'] }}</a>
          </dd>
        </div>

        <div class="info-row">
          <dt><i class="fas fa-phone"></i> Teléfono Fijo</dt>
          <dd>
            <a class="link" href="tel:{{ preg_replace('/\D+/', '', $datos['num_fijo']) }}">{{ $datos['num_fijo'] }}</a>
          </dd>
        </div>

        <div class="info-row">
          <dt><i class="fas fa-mobile-alt"></i> Teléfono Celular</dt>
          <dd>
            <a class="link" href="tel:{{ preg_replace('/\D+/', '', $datos['num_celular']) }}">{{ $datos['num_celular'] }}</a>
          </dd>
        </div>

        <div class="info-row info-row--full">
          <dt><i class="fas fa-external-link-alt"></i> Página Web</dt>
          <dd>
            <a class="link" href="{{ $datos['pag_web'] }}" target="_blank" rel="noopener">
              {{ $datos['pag_web'] }}
            </a>
          </dd>
        </div>
      </dl>
    </div>
  </section>

  <!-- MODAL: Editar datos -->
  <div class="modal-overlay" id="modalEditarEmpresa" aria-hidden="true">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-title">
      <header class="modal-header">
        <h3 id="modal-title" class="titulo-modal"><i class="fas fa-edit"></i> Editar Datos de la Empresa</h3>
        <button type="button" class="modal-close" id="cerrarModal" aria-label="Cerrar">
          <i class="fas fa-times"></i>
        </button>
      </header>

      <form id="formEditarEmpresa" class="modal-form" novalidate>
        <input type="hidden" id="codEmpresa" value="{{ $datos['cod_empresa'] }}">

        <div class="form-grid">

          @php
            $fields = [
              'nom_empresa'   => ['label'=>'Nombre',             'type'=>'text',   'attr'=>['maxlength'=>150, 'autocomplete'=>'organization']],
              'contacto'      => ['label'=>'Contacto',           'type'=>'text',   'attr'=>['maxlength'=>100, 'autocomplete'=>'name']],
              'direccion'     => ['label'=>'Dirección',          'type'=>'text',   'attr'=>['maxlength'=>255, 'autocomplete'=>'street-address', 'class'=>'full']],
              'pais'          => ['label'=>'País',               'type'=>'text',   'attr'=>['maxlength'=>60]],
              'ciudad'        => ['label'=>'Ciudad',             'type'=>'text',   'attr'=>['maxlength'=>80]],
              'departamento'  => ['label'=>'Departamento',       'type'=>'text',   'attr'=>['maxlength'=>80]],
              'cod_municipio' => ['label'=>'Código Municipio',   'type'=>'text',   'attr'=>['maxlength'=>10, 'inputmode'=>'numeric']],
              'cod_postal'    => ['label'=>'Código Postal',      'type'=>'text',   'attr'=>['maxlength'=>10, 'inputmode'=>'numeric']],
              'email'         => ['label'=>'Correo',             'type'=>'email',  'attr'=>['maxlength'=>120, 'autocomplete'=>'email']],
              'num_fijo'      => ['label'=>'Teléfono Fijo',      'type'=>'tel',    'attr'=>['maxlength'=>20, 'inputmode'=>'tel']],
              'num_celular'   => ['label'=>'Teléfono Celular',   'type'=>'tel',    'attr'=>['maxlength'=>20, 'inputmode'=>'tel']],
              'fax'           => ['label'=>'Fax',                'type'=>'text',   'attr'=>['maxlength'=>20]],
              'pag_web'       => ['label'=>'Página Web',         'type'=>'url',    'attr'=>['maxlength'=>180, 'placeholder'=>'https://...','class'=>'full']],
            ];
          @endphp

          @foreach($fields as $name => $meta)
            <div class="form-group {{ $meta['attr']['class'] ?? '' }}">
              <label for="{{ $name }}">{{ $meta['label'] }}</label>
              <input
                id="{{ $name }}"
                name="{{ $name }}"
                type="{{ $meta['type'] }}"
                value="{{ $datos[$name] }}"
                @foreach(($meta['attr'] ?? []) as $k=>$v)
                  @if($k !== 'class') {{ $k }}="{{ $v }}" @endif
                @endforeach
                required
              >
              <span class="error-msg" aria-live="polite"></span>
            </div>
          @endforeach

        </div>

        <footer class="modal-actions">
          <button type="submit" class="btn btn-success" id="btnGuardar">
            <span class="btn-text">Guardar</span>
            <span class="btn-spinner" aria-hidden="true"></span>
          </button>
          <button type="button" class="btn btn-secondary" id="cancelarEdicion">Cancelar</button>
        </footer>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  // --- helpers
  const qs = s => document.querySelector(s);
  const modal = qs('#modalEditarEmpresa');
  const btnAbrir = qs('#btnEditarEmpresa');
  const btnCerrar = qs('#cerrarModal');
  const btnCancelar = qs('#cancelarEdicion');
  const form = qs('#formEditarEmpresa');
  const btnGuardar = qs('#btnGuardar');
  const btnText = qs('#btnGuardar .btn-text');

  const openModal = () => {
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    setTimeout(() => document.getElementById('nom_empresa')?.focus(), 50);
  };

  const closeModal = () => {
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
  };

  // CLICK EN "EDITAR DATOS" CON VALIDACIÓN DE PERMISO
  btnAbrir.addEventListener('click', (e) => {
    const bloqueado = btnAbrir.getAttribute('data-bloqueado');
    if (bloqueado === '1') {
      e.preventDefault();
      Swal.fire({
        icon: 'error',
        title: 'Acción no permitida',
        text: 'No tienes permiso para editar los datos de la empresa.'
      });
      return;
    }
    openModal();
  });

  btnCerrar.addEventListener('click', closeModal);
  btnCancelar.addEventListener('click', closeModal);
  modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });

  // --- submit
  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    // estado loading
    btnGuardar.disabled = true;
    btnText.textContent = 'Guardando...';
    btnGuardar.classList.add('is-loading');

    const codEmpresa = document.getElementById('codEmpresa').value;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    if (data.pag_web && !/^https?:\/\//i.test(data.pag_web)) {
      data.pag_web = 'https://' + data.pag_web;
    }

    try {
      const res = await fetch(`http://localhost:3000/api/datos_empresa/${codEmpresa}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });

      const result = await res.json();

      if (res.ok) {
        closeModal();
        Swal.fire('Actualizado', result.mensaje || 'Datos guardados correctamente', 'success')
          .then(() => location.reload());
      } else {
        Swal.fire('Error', result.mensaje || 'Error al actualizar', 'error');
      }
    } catch (err) {
      Swal.fire('Error', 'No se pudo conectar con el servidor', 'error');
    } finally {
      btnGuardar.disabled = false;
      btnText.textContent = 'Guardar';
      btnGuardar.classList.remove('is-loading');
    }
  });
</script>
@endsection
