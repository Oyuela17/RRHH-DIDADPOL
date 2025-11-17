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

  // ==== PERMISOS MÓDULO CONTROL DE ASISTENCIA ====
  $accionesControlAsis = $accionesPermitidas['CONTROL DE ASISTENCIA'] ?? [
      'crear'      => false,
      'actualizar' => false,
      'eliminar'   => false,
  ];
@endphp

<div class="asistencia-admin-wrapper">
  <div class="titulo-con-linea">
    <h2>CONTROL DE ASISTENCIA DEL PERSONAL</h2>
  </div>

  {{-- ===== FILTROS (Excel + PDF en la misma línea; sin botón manual) ===== --}}
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

      {{-- 5) Semana ISO (deshabilitado en "Mes") --}}
      <input type="week"
             id="semana"
             name="semana"
             value="{{ $semanaIso }}"
             aria-label="Semana ISO"
             class="week-input {{ $vista==='mes' ? 'week-disabled' : '' }}"
             {{ $vista==='mes' ? 'disabled' : '' }}
             title="{{ $vista==='mes' ? 'Disponible en Vista: Semana' : 'Seleccione una semana' }}">

      {{-- 6) Botones: Excel + PDF --}}
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
          <tr class="fila-empleado" data-cod-empleado="{{ $emp['cod_empleado'] ?? '' }}">
            {{-- Nombre: clickeable solo en Vista Semana --}}
            <td class="nombre-empleado th-sticky {{ $vista === 'semana' ? 'clickable-empleado' : '' }}"
                @if($vista === 'semana')
                  style="cursor:pointer;"
                  title="Registrar asistencia semanal"
                @endif>
              {{ $emp['nombre'] }}
            </td>

            @for ($d = 1; $d <= $diasMes; $d++)
              @php
                $fechaActual   = Carbon::create($anioActual, $mesActual, $d)->toDateString();
                $registro      = collect($emp['registros'])->firstWhere('fecha', $fechaActual);
                $clase         = 'rojo';
                $isManual      = false;
                $almInicioRaw  = $registro['almuerzo_inicio'] ?? null;
                $almFinRaw     = $registro['almuerzo_fin'] ?? null;

                if ($registro) {
                  switch(strtoupper($registro['observacion'] ?? 'ASISTENCIA')) {
                    case 'EXTRA':       $clase = 'extra'; break;
                    case 'INCOMPLETA':  $clase = 'incompleta'; break;
                    default:            $clase = 'verde'; break;
                  }
                  $isManual = ($registro['origen'] ?? '') === 'manual';
                }
              @endphp

              <td class="icono-dia"
                  data-dia="{{ $d }}"
                  data-date="{{ $fechaActual }}"
                  data-cod-empleado="{{ $emp['cod_empleado'] ?? '' }}"
                  data-nombre="{{ $emp['nombre'] }}"
                  data-hora-entrada="{{ $registro['hora_entrada'] ?? '' }}"
                  data-hora-salida="{{ $registro['hora_salida'] ?? '' }}"
                  data-almuerzo-inicio="{{ $almInicioRaw ?? '' }}"
                  data-almuerzo-fin="{{ $almFinRaw ?? '' }}">
                @if ($registro)
                  <div class="celda-wrap">
                    <i class="fas fa-check-circle {{ $clase }}"
                       onmouseenter="mostrarModal(event,
                         '{{ $emp['nombre'] }}',
                         '{{ $fechaActual }}',
                         '{{ $registro['hora_entrada'] ?? '' }}',
                         '{{ $registro['hora_salida'] ?? '' }}',
                         '{{ $almInicioRaw ?? '' }}',
                         '{{ $almFinRaw ?? '' }}',
                         '{{ $registro['observacion'] ?? 'Asistencia' }}')"
                       onmouseleave="ocultarModal()"></i>
                    @if($isManual)
                      <span class="badge-manual" title="Registro manual">M</span>
                    @endif
                  </div>
                @else
                  <i class="fas fa-times-circle rojo"
                     onmouseenter="mostrarModal(event,
                       '{{ $emp['nombre'] }}',
                       '{{ $fechaActual }}',
                       '',
                       '',
                       '',
                       '',
                       'Sin asistencia')"
                     onmouseleave="ocultarModal()"></i>
                @endif
              </td>
            @endfor
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  {{-- Tooltip hover info --}}
  <div id="modal-asistencia" class="modal-asistencia" style="display:none;">
    <h4 id="modal-nombre"></h4>
    <p><strong>Fecha:</strong> <span id="modal-fecha"></span></p>
    <p><strong>Hora Entrada:</strong> <span id="modal-entrada"></span></p>
    <p><strong>Hora Salida:</strong> <span id="modal-salida"></span></p>
    <p><strong>Almuerzo inicio:</strong> <span id="modal-alm-inicio"></span></p>
    <p><strong>Almuerzo fin:</strong> <span id="modal-alm-fin"></span></p>
    <p><strong>Observación:</strong> <span id="modal-observacion"></span></p>
  </div>
</div>

{{-- ===== OVERLAY COMPARTIDO PARA LOS DOS MODALES ===== --}}
<div id="overlay-manual" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.35);z-index:9998;"></div>

{{-- ========= MODAL DE CARGA MANUAL (UN DÍA) ========= --}}
<div id="modal-manual" class="modal-asistencia-card" style="display:none;">
  <div class="modal-inner">
    {{-- Header estilo “Registrar Horario” --}}
    <div class="modal-header-asistencia">
      <h4 id="modal-manual-title" class="modal-title-main">
        Registrar/ajustar asistencia manual
      </h4>
      <div class="modal-header-underline"></div>
    </div>

    <form method="POST"
          action="{{ route('control_asistencia.admin.manual.upsert') }}"
          id="form-manual"
          autocomplete="off"
          class="modal-body-wrapper">
      @csrf
      @method('PUT')

      {{-- Empleado y fecha ocultos (se rellenan desde la celda clickeada) --}}
      <input type="hidden" name="cod_empleado" id="cod_empleado">
      <input type="hidden" name="fecha" id="fecha">

      {{-- FILA: Entrada + Salida --}}
      <div class="grid-two-cols">
        <div>
          <label class="lbl">Entrada</label>
          <div class="field-with-button">
            <input type="time" name="hora_entrada" id="hora_entrada" step="60">
            <button type="button" class="btn-ahora" onclick="setAhora('hora_entrada')">
                  <i class="far fa-clock"></i> Ahora
            </button>
          </div>
        </div>
        <div>
          <label class="lbl">Salida</label>
          <div class="field-with-button">
            <input type="time" name="hora_salida" id="hora_salida" step="60">
            <button type="button" class="btn-ahora" onclick="setAhora('hora_salida')">
                  <i class="far fa-clock"></i> Ahora
            </button>
          </div>
        </div>
      </div>

      {{-- Observación (oculta, la calculamos automático) --}}
      <input type="hidden" name="observacion" id="observacion">
      <div class="pill-observacion-wrapper">
        <span id="pill-observacion" class="legend-item pill-obs" style="display:none;"></span>
      </div>

      {{-- FOOTER --}}
      <div class="modal-actions">
        <button type="submit" class="btn-modal-primary">
          <i class="fas fa-save"></i> Guardar
        </button>
        <button type="button" id="close-manual" class="btn-modal-danger">
          <i class="fas fa-times"></i> Cerrar
        </button>
      </div>
    </form>
  </div>
</div>

{{-- ========= MODAL ASISTENCIA SEMANAL (VISTA: SEMANA) ========= --}}
<div id="modal-semana" class="modal-asistencia-card" style="display:none;">
  <div class="modal-inner">
    <div class="modal-header-asistencia">
      <h4 id="modal-semana-title" class="modal-title-main">
        Asistencia semanal
      </h4>
      <div class="modal-header-underline"></div>
    </div>

    <form method="POST"
          action="{{ route('control_asistencia.admin.manual.semana') }}"
          id="form-semana"
          autocomplete="off"
          class="modal-body-wrapper">
      @csrf

      <input type="hidden" name="cod_empleado" id="semana_cod_empleado">
      <input type="hidden" name="semana_iso"   id="semana_iso">

      {{-- Horario de la semana --}}
      <div class="grid-two-cols">
        <div>
          <label class="lbl">Entrada (toda la semana)</label>
          <div class="field-with-button">
            <input type="time" name="hora_entrada" id="semana_hora_entrada" step="60">
            <button type="button" class="btn-ahora" onclick="setAhoraSemana('semana_hora_entrada')">
                 <i class="far fa-clock"></i> Ahora
            </button>

          </div>
        </div>
        <div>
          <label class="lbl">Salida (toda la semana)</label>
          <div class="field-with-button">
            <input type="time" name="hora_salida" id="semana_hora_salida" step="60">
            <button type="button" class="btn-ahora" onclick="setAhoraSemana('semana_hora_salida')">
                  <i class="far fa-clock"></i> Ahora
            </button>
          </div>
        </div>
      </div>

      {{-- Días a aplicar --}}
      <div class="dias-semana-wrapper">
        <label class="lbl">Aplicar a los días:</label>
        <div class="dias-semana-checkboxes">
          @php
            $diasSemana = [
              1 => 'Lunes',
              2 => 'Martes',
              3 => 'Miércoles',
              4 => 'Jueves',
              5 => 'Viernes',
              6 => 'Sábado',
              7 => 'Domingo',
            ];
          @endphp
          @foreach($diasSemana as $num => $label)
            <label class="chk-dia">
              <input type="checkbox" name="dias[]" value="{{ $num }}" {{ $num <= 5 ? 'checked' : '' }}>
              <span>{{ $label }}</span>
            </label>
          @endforeach
        </div>
      </div>

      {{-- FOOTER --}}
      <div class="modal-actions">
        <button type="submit" class="btn-modal-primary">
          <i class="fas fa-save"></i> Guardar semana
        </button>
        <button type="button" id="close-semana" class="btn-modal-danger">
          <i class="fas fa-times"></i> Cerrar
        </button>
      </div>
    </form>
  </div>
</div>

{{-- ================= SCRIPTS ================= --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  // ===== PERMISOS (módulo CONTROL DE ASISTENCIA) =====
  const P_CAN_CREATE_CTRL_ASIS = {{ $accionesControlAsis['crear'] ? 'true' : 'false' }};
  const P_CAN_UPDATE_CTRL_ASIS = {{ $accionesControlAsis['actualizar'] ? 'true' : 'false' }};
  const P_CAN_DELETE_CTRL_ASIS = {{ $accionesControlAsis['eliminar'] ? 'true' : 'false' }};

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
  const selectMes   = document.getElementById('mes');
  const selectAnio  = document.getElementById('anio');
  const filtroForm  = document.getElementById('filtro-form');
  const vistaSel    = document.getElementById('vista');
  const inpSemana   = document.getElementById('semana');

  [selectMes, selectAnio].forEach(s => {
    s && s.addEventListener('change', () => {
      const params = new URLSearchParams(window.location.search);
      params.set('mes', selectMes.value);
      params.set('anio', selectAnio.value);
      window.location.search = params.toString();
    });
  });

  /* ---------- Vista Mes/Semana ---------- */
  function toggleSemanaInput() {
    const isSemana = vistaSel.value === 'semana';
    inpSemana.disabled = !isSemana;
    inpSemana.title = isSemana ? 'Seleccione una semana' : 'Disponible en Vista: Semana';
    inpSemana.classList.toggle('week-disabled', !isSemana);
  }

  vistaSel && vistaSel.addEventListener('change', () => {
    const params = new URLSearchParams(window.location.search);

    if (selectMes)   params.set('mes',   selectMes.value);
    if (selectAnio)  params.set('anio',  selectAnio.value);
    if (vistaSel)    params.set('vista', vistaSel.value);

    if (vistaSel.value === 'semana') {
      if (!inpSemana.value) {
        const now = new Date();
        inpSemana.value = getISOWeekString(now);
      }
      params.set('semana', inpSemana.value);
    } else {
      params.delete('semana');
    }

    window.location.search = params.toString();
  });

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

  /* ---------- Tooltip hover ---------- */
  function formatearHora(hora) {
    if (!hora) return '-';
    const partes = hora.split(':');
    if (partes.length < 2) return hora;
    const [h, m] = partes;
    const date = new Date();
    date.setHours(parseInt(h,10), parseInt(m,10), 0);
    return date.toLocaleTimeString('es-HN', { hour: 'numeric', minute: '2-digit', hour12: true });
  }
  window.mostrarModal = function (event, nombre, fecha, entrada, salida, almInicio, almFin, observacion) {
    const modal = document.getElementById('modal-asistencia');
    const windowWidth = window.innerWidth;

    document.getElementById('modal-nombre').textContent       = nombre;
    document.getElementById('modal-fecha').textContent        = fecha;
    document.getElementById('modal-entrada').textContent      = formatearHora(entrada);
    document.getElementById('modal-salida').textContent       = formatearHora(salida);
    document.getElementById('modal-alm-inicio').textContent   = formatearHora(almInicio);
    document.getElementById('modal-alm-fin').textContent      = formatearHora(almFin);
    document.getElementById('modal-observacion').textContent  = observacion || '-';

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
    btnExcel.href = "{{ route('asistencia.export.excel') }}" + '?' + params.toString();
  }
  ['input-nombre','mes','anio','vista','semana'].forEach(id=>{
    const el = document.getElementById(id);
    el && el.addEventListener('input', actualizarExportLinks);
    el && el.addEventListener('change', actualizarExportLinks);
  });

  /* ---------- Overlays / cerrar ---------- */
  const overlayMan  = document.getElementById('overlay-manual');
  const modalManual = document.getElementById('modal-manual');
  const modalSemana = document.getElementById('modal-semana');
  const closeManual = document.getElementById('close-manual');
  const closeSemana = document.getElementById('close-semana');
  const titleManual = document.getElementById('modal-manual-title');
  const titleSemana = document.getElementById('modal-semana-title');

  function showOverlay()   { overlayMan.style.display='block'; }
  function hideOverlay()   { overlayMan.style.display='none'; }
  function showManual()    { modalSemana.style.display='none'; modalManual.style.display='block'; showOverlay(); }
  function showSemana()    { modalManual.style.display='none'; modalSemana.style.display='block'; showOverlay(); }
  function hideModals()    { modalManual.style.display='none'; modalSemana.style.display='none'; hideOverlay(); }

  closeManual && closeManual.addEventListener('click', hideModals);
  closeSemana && closeSemana.addEventListener('click', hideModals);
  overlayMan  && overlayMan.addEventListener('click', hideModals);

  /* ---------- Modal de DÍA: click en celdas ---------- */
  const celdasDias     = document.querySelectorAll('.icono-dia');
  const hiddenEmpleado = document.getElementById('cod_empleado');
  const hiddenFecha    = document.getElementById('fecha');
  const entradaEl      = document.getElementById('hora_entrada');
  const salidaEl       = document.getElementById('hora_salida');

  function backendHoraToInput(hora) {
    if (!hora) return '';
    return hora.substring(0,5); // "HH:MM"
  }

  celdasDias.forEach(td => {
    td.addEventListener('click', () => {
      // BLOQUEO POR PERMISOS
      if (!P_CAN_UPDATE_CTRL_ASIS) {
        Swal.fire({
          icon: 'error',
          title: 'Acción no permitida',
          text: 'No tienes permiso para editar la asistencia.',
        });
        return;
      }

      const cod    = td.dataset.codEmpleado || '';
      const fecha  = td.dataset.date || '';
      const nombre = td.dataset.nombre || '';
      const hEnt   = td.dataset.horaEntrada || '';
      const hSal   = td.dataset.horaSalida || '';

      if (hiddenEmpleado && cod) hiddenEmpleado.value = cod;
      if (hiddenFecha && fecha)  hiddenFecha.value = fecha;

      if (entradaEl) entradaEl.value = backendHoraToInput(hEnt);
      if (salidaEl)  salidaEl.value  = backendHoraToInput(hSal);

      if (titleManual) {
        let fechaBonita = fecha;
        if (fecha && fecha.includes('-')) {
          const [y,m,d] = fecha.split('-');
          fechaBonita = `${d}/${m}/${y}`;
        }
        titleManual.textContent = `Registrar/ajustar asistencia – ${nombre} (${fechaBonita})`;
      }

      calcularObservacion();
      showManual();
    });
  });

  /* ---------- Modal de SEMANA: click en nombre (solo Vista: Semana) ---------- */
  const filasEmpleados = document.querySelectorAll('.fila-empleado');
  const semanaCodEmp   = document.getElementById('semana_cod_empleado');
  const semanaIsoInp   = document.getElementById('semana_iso');
  const semanaEntEl    = document.getElementById('semana_hora_entrada');
  const semanaSalEl    = document.getElementById('semana_hora_salida');

  filasEmpleados.forEach(fila => {
    const nombreTd = fila.querySelector('.nombre-empleado');
    if (!nombreTd) return;

    nombreTd.addEventListener('click', () => {
      if (!vistaSel || vistaSel.value !== 'semana') {
        return; // solo en Vista: Semana
      }

      // BLOQUEO POR PERMISOS
      if (!P_CAN_UPDATE_CTRL_ASIS) {
        Swal.fire({
          icon: 'error',
          title: 'Acción no permitida',
          text: 'No tienes permiso para registrar asistencia semanal.',
        });
        return;
      }

      const cod   = fila.dataset.codEmpleado || '';
      const nombre= nombreTd.textContent.trim();
      const iso   = inpSemana ? inpSemana.value : '';

      if (!cod || !iso) return;

      if (semanaCodEmp) semanaCodEmp.value = cod;
      if (semanaIsoInp) semanaIsoInp.value = iso;

      if (semanaEntEl) semanaEntEl.value = '';
      if (semanaSalEl) semanaSalEl.value = '';

      if (titleSemana) {
        titleSemana.textContent = `Asistencia semanal – ${nombre} (${iso})`;
      }

      showSemana();
    });
  });

  /* ---------- Aux: setAhora (día / semana) ---------- */
  function setAhora(idCampo){
    const d = new Date();
    const hh = String(d.getHours()).padStart(2,'0');
    const mm = String(d.getMinutes()).padStart(2,'0');
    const el = document.getElementById(idCampo);
    if (el) {
      el.value = `${hh}:${mm}`;
      if (idCampo === 'hora_entrada' || idCampo === 'hora_salida') {
        calcularObservacion();
      }
    }
  }
  window.setAhora = setAhora;

  function setAhoraSemana(idCampo){
    const d = new Date();
    const hh = String(d.getHours()).padStart(2,'0');
    const mm = String(d.getMinutes()).padStart(2,'0');
    const el = document.getElementById(idCampo);
    if (el) el.value = `${hh}:${mm}`;
  }
  window.setAhoraSemana = setAhoraSemana;

  /* ---------- Observación automática + validación (DÍA) ---------- */
  const formManual = document.getElementById('form-manual');
  const obsEl      = document.getElementById('observacion');
  const pillObs    = document.getElementById('pill-observacion');

  [entradaEl, salidaEl].forEach(el => el && el.addEventListener('input', calcularObservacion));

  function calcularObservacion(){
    if (!entradaEl || !salidaEl || !obsEl || !pillObs) return;

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

    pillObs.className = 'legend-item pill-obs';
    pillObs.innerHTML = `<i class="fas fa-check-circle ${clase}"></i> ${obs}`;
    pillObs.style.display = 'inline-flex';
  }

  formManual && formManual.addEventListener('submit', (e)=>{
    // BLOQUEO POR PERMISOS
    if (!P_CAN_UPDATE_CTRL_ASIS) {
      e.preventDefault();
      Swal.fire({
        icon: 'error',
        title: 'Acción no permitida',
        text: 'No tienes permiso para guardar cambios de asistencia.',
      });
      return false;
    }

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
    calcularObservacion();
  });

  /* ---------- Validación básica ASISTENCIA SEMANAL ---------- */
  const formSemana = document.getElementById('form-semana');
  formSemana && formSemana.addEventListener('submit', (e)=>{
    // BLOQUEO POR PERMISOS
    if (!P_CAN_UPDATE_CTRL_ASIS) {
      e.preventDefault();
      Swal.fire({
        icon: 'error',
        title: 'Acción no permitida',
        text: 'No tienes permiso para guardar asistencia semanal.',
      });
      return false;
    }

    const ent = semanaEntEl ? semanaEntEl.value : '';
    const sal = semanaSalEl ? semanaSalEl.value : '';
    if (sal && !ent) {
      e.preventDefault();
      Swal.fire('Atención','Para definir una salida semanal, primero indique la hora de entrada.','warning');
      return false;
    }
    if (ent && sal && sal < ent) {
      e.preventDefault();
      Swal.fire('Atención','La hora de salida no puede ser menor que la hora de entrada.','warning');
      return false;
    }
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

  /* ---------- Init ---------- */
  document.addEventListener('DOMContentLoaded', () => {
    if (!vistaSel || !inpSemana) {
      actualizarExportLinks();
      return;
    }

    if (vistaSel.value === 'semana') {
      inpSemana.disabled = false;
      inpSemana.classList.remove('week-disabled');
      inpSemana.title = 'Seleccione una semana';

      if (!inpSemana.value) {
        const now = new Date();
        inpSemana.value = getISOWeekString(now);
      }

      filtrarPorSemana(inpSemana.value);
    } else {
      mostrarMesCompleto();
    }

    actualizarExportLinks();
  });

</script>

@endsection
