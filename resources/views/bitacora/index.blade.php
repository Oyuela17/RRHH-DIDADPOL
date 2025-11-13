{{-- resources/views/bitacora/index.blade.php --}}
@extends('layouts.dashboard')
@section('title', 'Bitácora')

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

@if (session('error'))
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      Swal.fire({
        icon: 'error',
        title: 'Bitácora',
        text: '{{ session("error") }}',
        confirmButtonText: 'Entendido',
        confirmButtonColor: '#d33'
      });
    });
  </script>
@endif

@php
  $filtros    = $filtros ?? [];
  $meta       = $meta ?? ['page' => 1, 'last_page' => 1, 'limit' => 20, 'sort' => 'fecha', 'dir' => 'desc', 'modo' => 'general'];
  $modoActual = $meta['modo'] ?? ($filtros['modo'] ?? 'general');
  $tituloSeccion = $modoActual === 'sesiones'
      ? 'Bitácora de Sesiones'
      : 'Bitácora General';
@endphp

<div class="bitacora-wrapper">
  {{-- PESTAÑAS --}}
  <div class="tabs-container bitacora-tabs">
    <button
      type="button"
      class="tab-btn bitacora-tab {{ $modoActual === 'general' ? 'active' : '' }}"
      data-modo="general"
    >
      General
    </button>

    <button
      type="button"
      class="tab-btn bitacora-tab {{ $modoActual === 'sesiones' ? 'active' : '' }}"
      data-modo="sesiones"
    >
      Sesiones
    </button>
  </div>

  {{-- CONTENIDO --}}
  <div class="tab-content active" id="bitacora-tab-content">

    {{-- TÍTULO SEGÚN PESTAÑA --}}
    <div class="bitacora-titulo-row">
      <h3 class="bitacora-titulo" id="bitacoraTitulo">{{ $tituloSeccion }}</h3>
    </div>

    {{-- FILTROS --}}
    <form method="GET" class="bitacora-filtros" id="formBitacora">
      {{-- Ocultos --}}
      <input type="hidden" name="modo" value="{{ $modoActual }}">
      <input type="hidden" name="page" value="{{ $meta['page'] ?? 1 }}">
      <input type="hidden" name="dir"  value="{{ $filtros['dir'] ?? $meta['dir'] ?? 'desc' }}">

      {{-- UNA SOLA FILA DE FILTROS --}}
      <div class="bitacora-filtros-row filtros-linea fila-filtros">

        {{-- BLOQUE IZQUIERDO: buscador grande + botones alineados --}}
<div class="bloque-izquierdo">

  {{-- Buscador con label --}}
  <div class="filtro-item filtro-buscador">
    <label class="filtro-label">Buscar</label>
    <input
      type="text"
      name="q"
      class="filtro-input inp buscador-grande"
      placeholder="Buscar (usuario, tabla, acción, evento...)"
      value="{{ $filtros['q'] ?? '' }}"
    >
  </div>

  {{-- Botones con label vacío para alinear --}}
  <div class="filtro-item filtro-botones">
    <label class="filtro-label">&nbsp;</label>

    <div class="filtros-botones">
      <button type="submit" id="btnAplicarFiltros" class="btn-primario">
        Aplicar filtros
      </button>
      <button type="button" id="btnLimpiarFiltros" class="btn-peligro">
        Limpiar
      </button>
    </div>
  </div>

</div>


        {{-- BLOQUE DERECHO: filtros secundarios pegados entre sí --}}
        <div class="filtros-secundarios-inner">
          <div class="filtro-item">
            <label class="filtro-label">Desde</label>
            <input
              type="date"
              name="desde"
              class="filtro-input inp"
              value="{{ $filtros['desde'] ?? '' }}"
              title="Desde"
            >
          </div>

          <div class="filtro-item">
            <label class="filtro-label">Hasta</label>
            <input
              type="date"
              name="hasta"
              class="filtro-input inp"
              value="{{ $filtros['hasta'] ?? '' }}"
              title="Hasta"
            >
          </div>

          <div class="filtro-item">
            <label class="filtro-label">Ordenar por</label>
            <select name="sort" class="filtro-select inp">
              <option value="fecha"   {{ ($filtros['sort'] ?? $meta['sort'] ?? 'fecha') === 'fecha' ? 'selected' : '' }}>Fecha</option>
              <option value="usuario" {{ ($filtros['sort'] ?? '') === 'usuario' ? 'selected' : '' }}>Usuario</option>
              <option value="accion"  {{ ($filtros['sort'] ?? '') === 'accion' ? 'selected' : '' }}>Acción</option>
              <option value="tabla"   {{ ($filtros['sort'] ?? '') === 'tabla' ? 'selected' : '' }}>Tabla</option>
              <option value="tipo"    {{ ($filtros['sort'] ?? '') === 'tipo' ? 'selected' : '' }}>Tipo evento</option>
            </select>
          </div>

          <div class="filtro-item">
            <label class="filtro-label">Mostrar</label>
            <div class="mostrar-wrapper">
              <select name="limit" class="filtro-select inp">
                @foreach([5,10,15,20,50] as $op)
                  <option value="{{ $op }}" {{ (int)($filtros['limit'] ?? $meta['limit'] ?? 20) === $op ? 'selected' : '' }}>
                    {{ $op }}
                  </option>
                @endforeach
              </select>
              <span class="texto-registros">registros</span>
            </div>
          </div>
        </div>
      </div>
    </form>

    {{-- RESULTADOS (TABLA + PAGINACIÓN) --}}
    <div id="bitacoraResultados">
      @include('bitacora.partials.resultados', [
        'registros'  => $registros,
        'meta'       => $meta,
        'modoActual' => $modoActual
      ])
    </div>
  </div>
</div>

{{-- JS: AJAX para pestañas, filtros y paginación sin recargar --}}
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const form        = document.getElementById('formBitacora');
    const resultados  = document.getElementById('bitacoraResultados');
    const modoInput   = form.querySelector('input[name="modo"]');
    const pageInput   = form.querySelector('input[name="page"]');
    const tabs        = document.querySelectorAll('.bitacora-tab');
    const btnLimpiar  = document.getElementById('btnLimpiarFiltros');
    const inputsAuto  = ['sort','limit'];
    const inputQ      = form.querySelector('input[name="q"]');
    const titulo      = document.getElementById('bitacoraTitulo');

    const URL_BASE = "{{ route('bitacora.index') }}";

    function setPage(num) {
      if (pageInput) pageInput.value = num;
    }

    function setModo(modo) {
      if (modoInput) modoInput.value = modo;
    }

    function activarTab(modo) {
      tabs.forEach(t => {
        t.classList.toggle('active', t.dataset.modo === modo);
      });
    }

    function actualizarTitulo(modo) {
      if (!titulo) return;
      titulo.textContent = (modo === 'sesiones')
        ? 'Bitácora de Sesiones'
        : 'Bitácora General';
    }

    async function cargarBitacora() {
      const formData = new FormData(form);
      const params   = new URLSearchParams(formData);

      resultados.classList.add('loading');

      try {
        const resp = await fetch(`${URL_BASE}?${params.toString()}`, {
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        const html = await resp.text();
        resultados.innerHTML = html;
        bindEventosResultados();
      } catch (e) {
        console.error('Error cargando bitácora:', e);
      } finally {
        resultados.classList.remove('loading');
      }
    }

    // Paginación AJAX
    function bindEventosResultados() {
      resultados.querySelectorAll('.page-btn[data-page]').forEach(btn => {
        btn.addEventListener('click', () => {
          const p = parseInt(btn.dataset.page || '1', 10);
          setPage(p);
          cargarBitacora();
        });
      });
    }

    // Inicial
    bindEventosResultados();

    // Botón Aplicar filtros
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      setPage(1);
      cargarBitacora();
    });

    // Cambios en sort / limit → recargar
    inputsAuto.forEach(name => {
      const el = form.querySelector(`[name="${name}"]`);
      if (el) {
        el.addEventListener('change', () => {
          setPage(1);
          cargarBitacora();
        });
      }
    });

    // Búsqueda en tiempo real
    if (inputQ) {
      let timer = null;
      inputQ.addEventListener('keyup', () => {
        clearTimeout(timer);
        timer = setTimeout(() => {
          setPage(1);
          cargarBitacora();
        }, 600);
      });
    }

    // Tabs General / Sesiones
    tabs.forEach(tab => {
      tab.addEventListener('click', () => {
        const modo = tab.dataset.modo;
        activarTab(modo);
        setModo(modo);
        setPage(1);
        actualizarTitulo(modo);
        cargarBitacora();
      });
    });

    // Limpiar filtros
    btnLimpiar?.addEventListener('click', () => {
      form.reset();
      const dirInput = form.querySelector('input[name="dir"]');
      if (dirInput) dirInput.value = 'desc';
      const modo = 'general';
      setModo(modo);
      activarTab(modo);
      actualizarTitulo(modo);
      setPage(1);
      cargarBitacora();
    });
  });
</script>
@endsection
