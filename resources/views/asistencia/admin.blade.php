@extends('layouts.dashboard')
@section('title', 'Control de Asistencia (Admin)')

<link rel="stylesheet" href="{{ asset('css/asistencia_admin.css') }}">
@section('content')

@php
  use Carbon\Carbon;
  $mesActual  = (int) request('mes', now()->month);
  $anioActual = (int) request('anio', now()->year);
  $diasMes    = $dias ?? Carbon::create($anioActual, $mesActual, 1)->daysInMonth;
  $vista      = request('vista', 'mes');  // mes | semana
  $semanaIso  = request('semana');
@endphp

<div class="asistencia-admin-wrapper">
  <div class="titulo-con-linea">
    <h2>CONTROL DE ASISTENCIA DEL PERSONAL</h2>
  </div>

  {{-- ===== FILTROS (con Manual + Excel + PDF en la misma línea) ===== --}}
  <div class="filtros-asistencia">
    <form method="GET"
          action="{{ route('control_asistencia.admin') }}"
          class="formulario-filtros"
          id="filtro-form"
          autocomplete="off">

      {{-- 1) Buscador --}}
      <input  type="text"
              name="nombre"
              id="input-nombre"
              placeholder="Nombre del empleado"
              value="{{ request('nombre') }}"
              aria-label="Buscar por nombre" />

      {{-- 2) Mes --}}
      <select name="mes" id="mes" aria-label="Mes">
        @foreach(range(1, 12) as $m)
          <option value="{{ $m }}" {{ $mesActual == $m ? 'selected' : '' }}>
            {{ Carbon::create()->month($m)->locale('es')->isoFormat('MMMM') }}
          </option>
        @endforeach
      </select>

      {{-- 3) Año --}}
      <select name="anio" id="anio" aria-label="Año">
        @for ($y = now()->year; $y >= 2020; $y--)
          <option value="{{ $y }}" {{ $anioActual == $y ? 'selected' : '' }}>{{ $y }}</option>
        @endfor
      </select>

      {{-- 4) Vista Mes/Semana --}}
      <select id="vista" name="vista" aria-label="Tipo de vista">
        <option value="mes"    {{ $vista==='mes'    ? 'selected' : '' }}>Vista: Mes</option>
        <option value="semana" {{ $vista==='semana' ? 'selected' : '' }}>Vista: Semana</option>
      </select>

      {{-- 5) Semana ISO (siempre visible; deshabilitado en "Mes") --}}
      <input type="week"
             id="semana"
             name="semana"
             value="{{ $semanaIso }}"
             aria-label="Semana ISO"
             class="week-input {{ $vista==='mes' ? 'week-disabled' : '' }}"
             {{ $vista==='mes' ? 'disabled' : '' }}
             title="{{ $vista==='mes' ? 'Disponible en Vista: Semana' : 'Seleccione una semana' }}">

      {{-- 6) Botones: Manual + Excel + PDF --}}
      <a id="btnAsistenciaManual" class="btn-exportar manual" href="javascript:void(0)">
        <i class="fas fa-user-check"></i> Asistencia manual
      </a>

      <a id="btnExportarExcel" class="btn-exportar excel" target="_blank" rel="noopener">
        <i class="fas fa-file-excel"></i> Excel
      </a>
      <a id="btnExportarPDF"   class="btn-exportar pdf"   target="_blank" rel="noopener">
        <i class="fas fa-file-pdf"></i> PDF
      </a>
    </form>
  </div>

  {{-- ===== TABLA / CALENDARIO ===== --}}
  <div class="tabla-asistencia">
    <table>
      <thead>
        <tr>
          <th class="th-sticky">Empleado</th>
          @for ($d = 1; $d <= $diasMes; $d++)
            @php
              $fecha = Carbon::create($anioActual, $mesActual, $d);
              $dow   = $fecha->locale('es')->isoFormat('dd');
            @endphp
            <th class="th-dia" data-dia="{{ $d }}" data-date="{{ $fecha->toDateString() }}">
              <div class="th-dia-wrap">
                <span class="num">{{ $d }}</span>
                <span class="dow">{{ $dow }}</span>
              </div>
            </th>
          @endfor
        </tr>
      </thead>

      <tbody id="tabla-empleados">
        @foreach ($empleados as $emp)
          <tr class="fila-empleado">
            <td class="nombre-empleado th-sticky">{{ $emp['nombre'] }}</td>

            @for ($d = 1; $d <= $diasMes; $d++)
              @php
                $fechaActual = Carbon::create($anioActual, $mesActual, $d)->toDateString();
                $registro = collect($emp['registros'])->firstWhere('fecha', $fechaActual);
                $clase = 'rojo'; $isManual = false;

                if ($registro) {
                  switch(strtoupper($registro['observacion'] ?? 'ASISTENCIA')) {
                    case 'EXTRA':       $clase = 'extra'; break;
                    case 'INCOMPLETA':  $clase = 'incompleta'; break;
                    default:            $clase = 'verde'; break;
                  }
                  $isManual = ($registro['origen'] ?? '') === 'manual';
                }
              @endphp

              <td class="icono-dia" data-dia="{{ $d }}" data-date="{{ $fechaActual }}">
                @if ($registro)
                  <div class="celda-wrap">
                    <i class="fas fa-check-circle {{ $clase }}"
                       onmouseenter="mostrarModal(event, '{{ $emp['nombre'] }}','{{ $fechaActual }}','{{ $registro['hora_entrada'] }}','{{ $registro['hora_salida'] }}','{{ $registro['observacion'] ?? 'Asistencia' }}','{{ $registro['origen'] ?? 'biometrico' }}')"
                       onmouseleave="ocultarModal()"></i>
                    @if($isManual)
                      <span class="badge-manual" title="Registro manual">M</span>
                    @endif
                  </div>
                @else
                  <i class="fas fa-times-circle rojo"
                     onmouseenter="mostrarModal(event, '{{ $emp['nombre'] }}','{{ $fechaActual }}','','','Sin asistencia','')"
                     onmouseleave="ocultarModal()"></i>
                @endif
              </td>
            @endfor
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  {{-- Modal hover info --}}
  <div id="modal-asistencia" class="modal-asistencia" style="display:none;">
    <h4 id="modal-nombre"></h4>
    <p><strong>Fecha:</strong> <span id="modal-fecha"></span></p>
    <p><strong>Hora Entrada:</strong> <span id="modal-entrada"></span></p>
    <p><strong>Hora Salida:</strong> <span id="modal-salida"></span></p>
    <p><strong>Observación:</strong> <span id="modal-observacion"></span></p>
    <p><strong>Origen:</strong> <span id="modal-origen"></span></p>
  </div>
</div>

{{-- ========= MODAL DE CARGA MANUAL (UPSERT con PUT) ========= --}}
<div id="overlay-manual" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.35);z-index:9998;"></div>
<div id="modal-manual" style="display:none;position:fixed;z-index:9999;left:50%;top:50%;transform:translate(-50%,-50%);
  width:min(900px,96vw);background:#fff;border:1px solid #e7ebf0;border-radius:14px;box-shadow:0 18px 40px rgba(0,0,0,.2);">

  {{-- Header con TÍTULO CENTRADO (sin botón aquí) --}}
  <div style="display:flex;align-items:center;justify-content:center;padding:14px 18px;border-bottom:1px solid #eef2f7">
    <h4 style="margin:0;color:#003366;font-weight:800;text-align:center;">Registrar/ajustar asistencia manual</h4>
  </div>

  <form method="POST" action="{{ route('control_asistencia.admin.manual.upsert') }}" id="form-manual" autocomplete="off" style="padding:16px;">
    @csrf
    @method('PUT')

    {{-- FILA 1: Empleado + Fecha (MISMA LÍNEA) --}}
    <div style="display:grid;grid-template-columns:1.4fr 0.8fr;gap:12px;align-items:end;">
      <div>
        <label class="lbl">Empleado</label>
        <select name="cod_empleado" id="cod_empleado" required>
          <option value="">— Seleccione —</option>
          @foreach($empleados as $emp)
            @isset($emp['cod_empleado'])
              <option value="{{ $emp['cod_empleado'] }}">{{ $emp['nombre'] }}</option>
            @endisset
          @endforeach
        </select>
      </div>
      <div>
        <label class="lbl">Fecha</label>
        <input type="date" name="fecha" id="fecha" required>
      </div>
    </div>

    {{-- FILA 2: Entrada + Salida (ABAJO) --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;align-items:end;margin-top:12px;">
      <div>
        <label class="lbl">Entrada</label>
        <div style="display:flex;gap:6px;">
          <input type="time" name="hora_entrada" id="hora_entrada" step="60">
          <button type="button" class="btn-exportar manual" style="height:42px;" onclick="setAhora('hora_entrada')">
            <i class="far fa-clock"></i> Ahora
          </button>
        </div>
      </div>
      <div>
        <label class="lbl">Salida</label>
        <div style="display:flex;gap:6px;">
          <input type="time" name="hora_salida" id="hora_salida" step="60">
          <button type="button" class="btn-exportar manual" style="height:42px;" onclick="setAhora('hora_salida')">
            <i class="far fa-clock"></i> Ahora
          </button>
        </div>
      </div>
    </div>

    {{-- Observación: OCULTA (se muestra como pill cuando haya entrada y salida) --}}
    <input type="hidden" name="observacion" id="observacion">
    <div style="margin-top:10px; text-align:center;">
      <span id="pill-observacion" class="legend-item pill-obs" style="display:none; margin: 0 auto;"></span>
    </div>

    {{-- FOOTER: Botones pequeños y centrados --}}
    <div class="modal-actions" style="display:flex;gap:12px;align-items:center;justify-content:center;margin-top:16px;">
      <button type="submit" class="btn-exportar excel btn-sm" style="height:38px;padding:0 14px;min-width:160px;">
        <i class="fas fa-save"></i> Guardar
      </button>
      <button type="button" id="close-manual" class="btn-exportar btn-sm btn-cerrar"
              style="height:38px;padding:0 14px;min-width:160px;background:linear-gradient(90deg,#64748b,#94a3b8);">
        <i class="fas fa-times"></i> Cerrar
      </button>
    </div>
  </form>
</div>

{{-- ================= SCRIPTS ================= --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  /* ---------- Búsqueda ---------- */
  const inputNombre = document.getElementById('input-nombre');
  if (inputNombre) {
    inputNombre.addEventListener('input', function () {
      const valor = (this.value || '').toLowerCase();
      document.querySelectorAll('.fila-empleado').forEach(fila => {
        const nombre = fila.querySelector('.nombre-empleado').textContent.toLowerCase();
        fila.style.display = nombre.includes(valor) ? '' : 'none';
      });
    });
  }

  /* ---------- Auto-refresh Mes/Año ---------- */
  const selectMes  = document.getElementById('mes');
  const selectAnio = document.getElementById('anio');
  [selectMes, selectAnio].forEach(s => {
    s && s.addEventListener('change', () => {
      const params = new URLSearchParams(window.location.search);
      params.set('mes', selectMes.value);
      params.set('anio', selectAnio.value);
      window.location.search = params.toString();
    });
  });

  /* ---------- Vista Mes/Semana ---------- */
  const vistaSel  = document.getElementById('vista');
  const inpSemana = document.getElementById('semana');

  function toggleSemanaInput() {
    const isSemana = vistaSel.value === 'semana';
    inpSemana.disabled = !isSemana;
    inpSemana.title = isSemana ? 'Seleccione una semana' : 'Disponible en Vista: Semana';
    inpSemana.classList.toggle('week-disabled', !isSemana);

    if (isSemana) {
      if (!inpSemana.value) {
        const now = new Date();
        inpSemana.value = getISOWeekString(now);
      }
      filtrarPorSemana(inpSemana.value);
    } else {
      mostrarMesCompleto();
    }
    actualizarExportLinks();
  }
  vistaSel && vistaSel.addEventListener('change', toggleSemanaInput);
  inpSemana && inpSemana.addEventListener('change', () => filtrarPorSemana(inpSemana.value));

  function getISOWeekString(d) {
    const date = new Date(Date.UTC(d.getFullYear(), d.getMonth(), d.getDate()));
    const dayNum = date.getUTCDay() || 7;
    date.setUTCDate(date.getUTCDate() + 4 - dayNum);
    const y = date.getUTCFullYear();
    const weekNo = Math.ceil((((date - new Date(Date.UTC(y,0,1))) / 86400000) + 1) / 7);
    return `${y}-W${String(weekNo).padStart(2,'0')}`;
  }
  function mostrarMesCompleto() {
    document.querySelectorAll('.th-dia, .icono-dia').forEach(el => el.style.display = '');
  }
  function ocultarFueraDeRango(desdeISO, hastaISO) {
    document.querySelectorAll('.th-dia').forEach(h => {
      const dt = h.getAttribute('data-date');
      h.style.display = (dt >= desdeISO && dt <= hastaISO) ? '' : 'none';
    });
    document.querySelectorAll('.icono-dia').forEach(c => {
      const dt = c.getAttribute('data-date');
      c.style.display = (dt >= desdeISO && dt <= hastaISO) ? '' : 'none';
    });
  }
  function rangoSemana(isoWeekStr) {
    const [yStr, wStr] = isoWeekStr.split('-W');
    const y = parseInt(yStr, 10);
    const w = parseInt(wStr, 10);
    const simple = new Date(Date.UTC(y,0,1 + (w-1)*7));
    const dow = simple.getUTCDay();
    const ISOweekStart = new Date(simple);
    if (dow <= 4) ISOweekStart.setUTCDate(simple.getUTCDate() - simple.getUTCDay() + 1);
    else          ISOweekStart.setUTCDate(simple.getUTCDate() + 8 - simple.getUTCDay());
    const ISOweekEnd = new Date(ISOweekStart);
    ISOweekEnd.setUTCDate(ISOweekStart.getUTCDate() + 6);
    return [ISOweekStart.toISOString().slice(0,10), ISOweekEnd.toISOString().slice(0,10)];
  }
  function filtrarPorSemana(isoWeekStr) {
    if (!isoWeekStr) return;
    const [desde, hasta] = rangoSemana(isoWeekStr);
    const anyInMonth = Array.from(document.querySelectorAll('.th-dia'))
      .some(h => {
        const dt = h.getAttribute('data-date');
        return (dt >= desde && dt <= hasta);
      });
    if (!anyInMonth) { ocultarFueraDeRango('9999-12-31','9999-12-31'); return; }
    ocultarFueraDeRango(desde, hasta);
  }

  /* ---------- Modal hover (info) ---------- */
  function formatearHora(hora) {
    if (!hora) return '-';
    const [h, m, s] = hora.split(':');
    const date = new Date(); date.setHours(h, m, s || 0);
    return date.toLocaleTimeString('es-HN', { hour: 'numeric', minute: '2-digit', hour12: true });
  }
  window.mostrarModal = function (event, nombre, fecha, entrada, salida, observacion, origen) {
    const modal = document.getElementById('modal-asistencia');
    const windowWidth = window.innerWidth;
    document.getElementById('modal-nombre').textContent = nombre;
    document.getElementById('modal-fecha').textContent = fecha;
    document.getElementById('modal-entrada').textContent = formatearHora(entrada);
    document.getElementById('modal-salida').textContent = formatearHora(salida);
    document.getElementById('modal-observacion').textContent = observacion || '-';
    document.getElementById('modal-origen').textContent = (origen || '—').toUpperCase();
    const modalWidth = 260;
    const leftPos = (windowWidth - event.clientX < modalWidth + 20)
      ? event.clientX - modalWidth - 10
      : event.clientX + 15;
    modal.style.top = (event.clientY + 15) + 'px';
    modal.style.left = leftPos + 'px';
    modal.style.display = 'block';
  }
  window.ocultarModal = function () {
    document.getElementById('modal-asistencia').style.display = 'none';
  }

  /* ---------- Export links ---------- */
  function actualizarExportLinks() {
    const btnPDF   = document.getElementById('btnExportarPDF');
    const btnExcel = document.getElementById('btnExportarExcel');
    if (!btnPDF || !btnExcel) return;

    const params = new URLSearchParams({
      mes:  document.getElementById('mes').value,
      anio: document.getElementById('anio').value,
      nombre: (document.getElementById('input-nombre').value || ''),
      vista: document.getElementById('vista').value
    });
    if (document.getElementById('vista').value === 'semana' && document.getElementById('semana').value) {
      params.set('semana', document.getElementById('semana').value);
    }

    btnPDF.href   = "{{ route('control_asistencia.export.pdf') }}" + '?' + params.toString();
    btnExcel.href = "{{ route('asistencia.export.excel') }}" + '?' + params.toString(); // queda igual
  }
  ['input-nombre','mes','anio','vista','semana'].forEach(id=>{
    const el = document.getElementById(id);
    el && el.addEventListener('input', actualizarExportLinks);
    el && el.addEventListener('change', actualizarExportLinks);
  });

  /* ---------- Init ---------- */
  document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('vista')) {
      toggleSemanaInput();
      actualizarExportLinks();
    }
  });

  /* ---------- Modal Manual: abrir/cerrar ---------- */
  const openManual  = document.getElementById('btnAsistenciaManual');
  const closeManual = document.getElementById('close-manual');
  const modalManual = document.getElementById('modal-manual');
  const overlayMan  = document.getElementById('overlay-manual');
  function showManual(){ modalManual.style.display='block'; overlayMan.style.display='block'; }
  function hideManual(){ modalManual.style.display='none'; overlayMan.style.display='none'; }
  openManual && openManual.addEventListener('click', showManual);
  closeManual && closeManual.addEventListener('click', hideManual);
  overlayMan  && overlayMan.addEventListener('click', hideManual);

  /* ---------- Aux: setAhora ---------- */
  function setAhora(idCampo){
    const d = new Date();
    const hh = String(d.getHours()).padStart(2,'0');
    const mm = String(d.getMinutes()).padStart(2,'0');
    document.getElementById(idCampo).value = `${hh}:${mm}`;
    calcularObservacion(); // recalcular al usar "Ahora"
  }

  /* ---------- Observación automática + validación ---------- */
  const formManual = document.getElementById('form-manual');
  const entradaEl  = document.getElementById('hora_entrada');
  const salidaEl   = document.getElementById('hora_salida');
  const obsEl      = document.getElementById('observacion');
  const pillObs    = document.getElementById('pill-observacion');

  [entradaEl, salidaEl].forEach(el => el && el.addEventListener('input', calcularObservacion));

  function calcularObservacion(){
    const ent = entradaEl.value, sal = salidaEl.value;
    pillObs.style.display = 'none';

    if (!ent || !sal) {
      obsEl.value = '';
      return;
    }

    const [eh, em] = ent.split(':').map(Number);
    const [sh, sm] = sal.split(':').map(Number);
    const horas = (sh + sm/60) - (eh + em/60);

    let obs = 'Asistencia normal';
    let clase = 'verde';
    if (horas < 8)        { obs = 'Horas incompletas'; clase = 'incompleta'; }
    else if (horas > 8.1) { obs = 'Horas extra';       clase = 'extra'; }

    obsEl.value = obs;

    // pill visual centrado
    pillObs.className = 'legend-item pill-obs';
    pillObs.innerHTML = `<i class="fas fa-check-circle ${clase}"></i> ${obs}`;
    pillObs.style.display = 'inline-flex';
  }

  formManual && formManual.addEventListener('submit', (e)=>{
    const ent = entradaEl.value;
    const sal = salidaEl.value;

    if (sal && !ent) {
      e.preventDefault();
      Swal.fire('Atención','Para definir una salida, primero indique la hora de entrada.','warning');
      return false;
    }
    if (ent && sal && sal < ent) {
      e.preventDefault();
      Swal.fire('Atención','La hora de salida no puede ser menor que la hora de entrada.','warning');
      return false;
    }
    calcularObservacion(); // asegurar que vaya seteada
  });

  /* ---------- SweetAlerts por respuesta del servidor ---------- */
  @if (session('mensaje'))
    Swal.fire({
      icon: 'success',
      title: 'Guardado',
      text: @json(session('mensaje')),
      timer: 2500,
      showConfirmButton: false
    });
  @endif

  @if (session('error'))
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: @json(session('error')),
    });
  @endif
</script>

@endsection
