@extends('layouts.dashboard')
@section('title', 'Cálculo de Planilla')

@section('content')
<link rel="stylesheet" href="{{ asset('vendor/datatable/datatables.min.css') }}">
<link rel="stylesheet" href="{{ asset('css/planilla.css') }}">

<div class="titulo-con-linea">
  <h2>Cálculo de Planilla</h2>
</div>

<div class="acciones-superiores">
  <div class="lado-izquierdo">
    <form class="form-busqueda" onsubmit="return false;">
      <input id="planillaSearch" type="text" placeholder="Buscar...">
    </form>
  </div>

<div class="lado-derecho">

    <!-- Botón NUEVO -->
    <button type="button" class="btn btn-nuevo" onclick="mostrarFormulario()">
      <i class="fas fa-plus"></i> Nuevo Registro
    </button>

    <!-- 🔹 Contenedor que controla espaciado/alineación -->
    <div class="toolbar-filtros">

        <label>Ordenar por</label>
        <select id="planillaOrden">
          <option value="0|asc">Nombre (A-Z)</option>
          <option value="0|desc">Nombre (Z-A)</option>
          <option value="3|desc">Fecha ingreso (Nuevos primero)</option>
          <option value="3|asc">Fecha ingreso (Antiguos primero)</option>
        </select>

        <label>Mostrar</label>
        <select id="planillaLength">
          <option>5</option>
          <option selected>10</option>
          <option>25</option>
          <option>50</option>
        </select>

        <span>registros</span>

        <label>Año</label>
        <select id="planillaAnio">
          <option value="">Todos</option>
          <option value="2024">2024</option>
          <option value="2025">2025</option>
        </select>

        <button type="button" id="btnLimpiarFiltro" class="btn btn-sm">
          Quitar filtro
        </button>
    </div>
</div>

<!-- ===== Tabla principal (con columna PERÍODO) ===== -->
<div class="planilla-container">
  <table class="tabla-planilla planilla-table display" id="tabla_planilla">
    <thead>
      <tr>
        <th>Nombre</th>
        <th>DNI</th>
        <th>Cargo</th>
        <th>Fecha ingreso</th>
        <th>Período</th>
        <th>DD / DT</th>
        <th>Salario</th>
        <th>Total deducciones</th>
        <th>Total a pagar</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
</div>

<!-- ========================= Modal AGREGAR / EDITAR / DETALLES ========================= -->
<div class="modal-planilla" id="modalPlanilla" style="display:none">
  <div class="modal-contenido">
    <div class="modal-header">
      <h5 class="m-0" id="modalTitulo">Registro de un nuevo cálculo</h5>

      <!-- Etiqueta de PERÍODO, solo texto, no editable -->
      <div style="margin-left:auto;font-size:13px;color:#fff;">
        Período: <span id="lblPeriodo">—</span>
      </div>

      <button class="btn-close-x" onclick="cerrarModal()">×</button>
    </div>
    <div class="modal-body">
      <!-- Bloque selección empleado (solo modo nuevo) -->
      <div class="card-soft" id="bloqueEmpleado">
        <div class="grid grid-4">
          <div>
            <label class="form-label">Empleado</label>
            <select id="selEmpleado" class="form-select">
              <option value="">— Seleccione empleado —</option>
            </select>
          </div>
          <div style="align-self:end">
            <button id="btnCargarEmpleado" class="btn btn-primary" style="width:100%">Cargar datos</button>
          </div>
        </div>
      </div>

      <!-- Cabecera de datos (Nombre, RTN, DNI, Cargo, etc.) -->
      <div class="card-soft">
        <div class="grid grid-3">
          <div>
            <label class="form-label">Nombre completo</label>
            <input id="p_nombre" class="form-control" readonly>
          </div>
          <div>
            <label class="form-label">RTN</label>
            <input id="p_rtn" class="form-control" readonly>
          </div>
          <div>
            <label class="form-label">DNI</label>
            <input id="p_dni" class="form-control" readonly>
          </div>
        </div>
        <div class="grid grid-3" style="margin-top:10px">
          <div>
            <label class="form-label">Cargo</label>
            <input id="p_puesto" class="form-control" readonly>
          </div>
          <div>
            <label class="form-label">Fecha ingreso</label>
            <input id="p_fecha_ingreso" class="form-control" readonly>
          </div>
          <div>
            <label class="form-label">Salario mensual</label>
            <input id="p_salario" class="form-control text-end" readonly>
          </div>
        </div>
      </div>

      <!-- DD / DT / Bruto -->
      <div class="card-soft">
        <div class="grid grid-3">
          <div>
            <label class="form-label">DD</label>
            <input id="p_dd" class="form-control text-end" readonly>
          </div>
          <div>
            <label class="form-label">DT</label>
            <input id="p_dt" class="form-control text-end" readonly>
          </div>
          <div>
            <label class="form-label">Salario en bruto</label>
            <input id="p_salario_bruto" class="form-control text-end" readonly>
          </div>
        </div>
      </div>

      <!-- Deducciones de ley -->
      <div class="card-soft">
        <div class="grid grid-4">
          <div>
            <label class="form-label">IHSS</label>
            <input id="p_ihss" class="form-control text-end" readonly>
          </div>
          <div>
            <label class="form-label">ISR</label>
            <input id="p_isr" class="form-control text-end" readonly>
          </div>
          <div>
            <label class="form-label">INJUPEMP</label>
            <input id="p_injupemp" class="form-control text-end" readonly>
          </div>
          <div>
            <label class="form-label">Impuesto vecinal</label>
            <input id="p_vecinal" class="form-control text-end" readonly>
          </div>
        </div>
      </div>

      <!-- Deducciones autorizadas -->
      <div class="card-soft">
        <div class="grid grid-3">
          <div>
            <label class="form-label">INJUPEMP / Reingresos</label>
            <input id="f_inj_reing" class="form-control text-end" inputmode="decimal" value="0">
          </div>
          <div>
            <label class="form-label">INJUPEMP Préstamos</label>
            <input id="f_inj_prest" class="form-control text-end" inputmode="decimal" value="0">
          </div>
          <div>
            <label class="form-label">Préstamo Banco Atlántida</label>
            <input id="f_banco_atl" class="form-control text-end" inputmode="decimal" value="0">
          </div>
        </div>
        <div class="grid grid-3" style="margin-top:10px">
          <div>
            <label class="form-label">Pagos deducibles</label>
            <input id="f_pagos_ded" class="form-control text-end" inputmode="decimal" value="0">
          </div>
          <div>
            <label class="form-label">Colegio Adm. Empresas</label>
            <input id="f_colegio" class="form-control text-end" inputmode="decimal" value="0">
          </div>
          <div>
            <label class="form-label">Cuota Coop. ELGA</label>
            <input id="f_coop_elga" class="form-control text-end" inputmode="decimal" value="0">
          </div>
        </div>
      </div>

      <!-- Totales -->
      <div class="card-soft">
        <div class="grid grid-2">
          <div>
            <label class="form-label">Total deducciones</label>
            <input id="p_total_ded" class="form-control text-end" readonly>
          </div>
          <div>
            <label class="form-label">Total a pagar</label>
            <input id="p_total_pagar" class="form-control text-end fw-bold" readonly>
          </div>
        </div>
      </div>
    </div>

    <div class="modal-footer">
      <button id="btnGuardarPlanilla" class="btn btn-primary">Guardar</button>
      <button class="btn btn-danger" onclick="cerrarModal()">Cancelar</button>
    </div>
  </div>
</div>
@endsection

@section('vendorjs')
  <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
  <script src="{{ asset('vendor/datatable/datatables.min.js') }}"></script>
  <script src="https://kit.fontawesome.com/a2d9d6e76d.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('scripts')
<script>
const API_BASE = 'https://rrhh-didadpol-1.onrender.com/api';
let modalMode = 'nuevo';
let currentCodPersona = null;

// 🔹 Filtros globales para la tabla
let filtroCodPersona = null; // se llena al hacer clic en el nombre
let filtroAnio       = '';   // se llena desde el select #planillaAnio

const modal = document.getElementById('modalPlanilla');

/* ========= Helpers generales ========= */
const nf2 = new Intl.NumberFormat('es-HN', {
  minimumFractionDigits: 2,
  maximumFractionDigits: 2
});
const num = v => Number(String(v || '0').replace(/[^0-9.\-]/g, '')) || 0;
const fmt = v => nf2.format(num(v));

function formatDate(val) {
  if (!val) return '';

  // Caso 1: yyyy-mm-dd
  let m = String(val).match(/^(\d{4})-(\d{2})-(\d{2})$/);
  if (m) {
    const meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
    return `${Number(m[3])} ${meses[Number(m[2]) - 1]} ${m[1]}`;
  }

  // Caso 2: ISO completo yyyy-mm-ddTHH:MM:SSZ
  let iso = Date.parse(val);
  if (!isNaN(iso)) {
    const d = new Date(iso);
    const meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
    return `${d.getUTCDate()} ${meses[d.getUTCMonth()]} ${d.getUTCFullYear()}`;
  }

  return val;
}

function formatPeriodo(val) {
  if (!val) return '';

  // Caso 1: yyyy-mm-dd
  let m = String(val).match(/^(\d{4})-(\d{2})-(\d{2})$/);
  if (m) {
    const meses = [
      'enero','febrero','marzo','abril','mayo','junio',
      'julio','agosto','septiembre','octubre','noviembre','diciembre'
    ];
    const año = m[1];
    const mes = meses[ Number(m[2]) - 1 ];
    const dia = Number(m[3]);
    return `${dia} ${mes} ${año}`;
  }

  // Caso 2: dd-mm-yyyy
  m = String(val).match(/^(\d{2})-(\d{2})-(\d{4})$/);
  if (m) {
    const meses = [
      'enero','febrero','marzo','abril','mayo','junio',
      'julio','agosto','septiembre','octubre','noviembre','diciembre'
    ];
    const dia = Number(m[1]);
    const mes = meses[ Number(m[2]) - 1 ];
    const año = m[3];
    return `${dia} ${mes} ${año}`;
  }

  return val;
}

// fecha de hoy en formato dd-mm-yyyy
function periodoHoy() {
  const d = new Date();
  const dd = String(d.getDate()).padStart(2,'0');
  const mm = String(d.getMonth()+1).padStart(2,'0');
  const yy = d.getFullYear();
  return `${dd}-${mm}-${yy}`;
}

function resolveSalario(row) {
  const s = Number(row.salario || 0);
  if (s > 0) return s;
  const dt = Number(row.dt || 0);
  const bruto = Number(row.salariobruto || row.salario_bruto || 0);
  return dt > 0 ? Math.round((bruto * 30 / dt) * 100) / 100 : 0;
}

/* ========= SweetAlert helpers ========= */
function swalInfo(text, title='Aviso') { return Swal.fire({ icon:'info', title, text }); }
function swalSuccess(text, title='Éxito') { return Swal.fire({ icon:'success', title, text }); }
function swalError(text, title='Error') { return Swal.fire({ icon:'error', title, text }); }
async function swalConfirm(text, title='¿Estás seguro?') {
  const r = await Swal.fire({
    icon:'question',
    title,
    text,
    showCancelButton:true,
    confirmButtonText:'Sí, continuar',
    cancelButtonText:'No, cancelar'
  });
  return r.isConfirmed;
}
async function runWithLoading(fn, title='Procesando...', text='Por favor espera') {
  Swal.fire({ title, text, allowOutsideClick:false, didOpen:()=>Swal.showLoading() });
  try {
    const out = await fn();
    Swal.close();
    return out;
  } catch(err) {
    Swal.close();
    throw err;
  }
}

/* ========= Modal ========= */
function configurarCamposParaModo(modo) {
  modalMode = modo;
  const $editables = $('#f_inj_reing,#f_inj_prest,#f_banco_atl,#f_pagos_ded,#f_colegio,#f_coop_elga');

  if (modo === 'detalle') {
    $('#btnGuardarPlanilla').hide();
    $('#bloqueEmpleado').hide();
    $editables.prop('readonly', true);
  } else if (modo === 'editar') {
    $('#btnGuardarPlanilla').show();
    $('#bloqueEmpleado').hide();
    $editables.prop('readonly', false);
  } else { // nuevo
    $('#btnGuardarPlanilla').show();
    $('#bloqueEmpleado').show();
    $editables.prop('readonly', false);
  }
}

function mostrarFormulario() {
  configurarCamposParaModo('nuevo');
  currentCodPersona = null;
  $('#modalTitulo').text('Registro de un nuevo cálculo');
  limpiarModal();
  // período sugerido para nuevo (hoy)
  $('#lblPeriodo').text(periodoHoy());

  if (!$('#selEmpleado').data('loaded')) cargarEmpleados();
  modal.style.display = 'flex';
}

function cerrarModal() {
  modal.style.display = 'none';
}

function limpiarModal() {
  $('#selEmpleado').val('');
  $('#p_nombre,#p_rtn,#p_dni,#p_puesto,#p_fecha_ingreso').val('');
  $('#p_salario,#p_dd,#p_dt,#p_salario_bruto,#p_ihss,#p_isr,#p_injupemp,#p_vecinal,#p_total_ded,#p_total_pagar').val('');
  $('#f_inj_reing,#f_inj_prest,#f_banco_atl,#f_pagos_ded,#f_colegio,#f_coop_elga').val('0');
  $('#lblPeriodo').text('—');
}

function fillModalFromRow(row) {
  $('#p_nombre').val(row.nombre || '');
  $('#p_rtn').val(row.rtn || '');
  $('#p_dni').val(row.dni || '');
  $('#p_puesto').val(row.cargo || '');
  $('#p_fecha_ingreso').val(formatDate(row.fecha_ingreso || ''));
  $('#p_salario').val(fmt( resolveSalario(row) ));
  $('#p_dd').val(row.dd ?? '');
  $('#p_dt').val(row.dt ?? '');
  $('#p_salario_bruto').val(fmt(row.salariobruto || row.salario_bruto || 0));
  $('#p_ihss').val(fmt(row.ihss || 0));
  $('#p_isr').val(fmt(row.isr || 0));
  $('#p_injupemp').val(fmt(row.injupemp || 0));
  $('#p_vecinal').val(fmt(row.vecinal || row.impuesto_vecinal || 0));

  $('#f_inj_reing').val(row.injupemp_reingresos || 0);
  $('#f_inj_prest').val(row.injupemp_prestamos || 0);
  $('#f_banco_atl').val(row.prestamo_banco_atlantida || 0);
  $('#f_pagos_ded').val(row.pagos_deducibles || 0);
  $('#f_colegio').val(row.colegio_admon_empresas || 0);
  $('#f_coop_elga').val(row.cuota_coop_elga || 0);

  $('#p_total_ded').val(fmt(row.total_deducciones || 0));
  $('#p_total_pagar').val(fmt(row.total_a_pagar || 0));

  // período (si viene del backend)
  if (row.periodo !== undefined) {
    $('#lblPeriodo').text(formatPeriodo(row.periodo));
  }
}

function previewTotales() {
  const salario_bruto = num($('#p_salario_bruto').val()),
        ihss = num($('#p_ihss').val()),
        isr = num($('#p_isr').val()),
        inj = num($('#p_injupemp').val()),
        vec = num($('#p_vecinal').val());
  const a1 = num($('#f_inj_reing').val()),
        a2 = num($('#f_inj_prest').val()),
        a3 = num($('#f_banco_atl').val()),
        a4 = num($('#f_pagos_ded').val()),
        a5 = num($('#f_colegio').val()),
        a6 = num($('#f_coop_elga').val());

  const total_ded = ihss + isr + inj + vec + a1 + a2 + a3 + a4 + a5 + a6;
  const total_pagar = Math.max(salario_bruto - total_ded, 0);

  $('#p_total_ded').val(fmt(total_ded));
  $('#p_total_pagar').val(fmt(total_pagar));
}
$(document).on('input', '#f_inj_reing,#f_inj_prest,#f_banco_atl,#f_pagos_ded,#f_colegio,#f_coop_elga', previewTotales);

/* ========= DataTable + toolbar ========= */
$(document).ready(function () {
  const dt = $('#tabla_planilla').DataTable({
    ajax: {
      url: '{{ route("planilla") }}',
      type: 'POST',
      headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
      // 🔹 Enviamos filtros al backend
      data: function(d){
        d.accion = 'ver_planilla';
        if (filtroCodPersona) d.cod_persona = filtroCodPersona;
        if (filtroAnio)       d.anio        = filtroAnio;
      },
      dataSrc: 'data',
      error: function(xhr){
        console.error('Ajax error', xhr.status, xhr.statusText, xhr.responseText);
        swalError('Error al cargar la planilla desde el servidor. Revisa la consola (F12).');
      }
    },
    ordering: true,
    order: [],
    paging: false,
    info: false,
    dom: 't',
    columns: [
      { data: 'nombre', className: 'col-nombre' },                 // 0 (clickable para filtrar)
      { data: 'dni' },                                             // 1
      { data: 'cargo' },                                           // 2
      { data: 'fecha_ingreso', render: v => formatDate(v) },       // 3
      { data: 'periodo', render: v => formatPeriodo(v) },          // 4
      { data: null, render: row => `${row.dd ?? ''}/${row.dt ?? ''}` },  // 5
      { data: null, render: row => fmt(resolveSalario(row)) },     // 6
      { data: 'total_deducciones', render: v => fmt(v || 0) },     // 7
      { data: 'total_a_pagar', render: v => fmt(v || 0) },         // 8
      {
        data: null,
        orderable: false,
        render: () => `
          <div class="acciones-col">
            <button class="btn-detalles-vis btn-detalles">Ver detalles</button>
            <button class="btn-editar-vis btn-editar">Editar</button>
            <button class="btn-eliminar-vis btn-eliminar">Eliminar</button>
          </div>`
      }                                                             // 9
    ],
    language: { url:'{{ asset("vendor/datatable/es-ES.json") }}' },
    initComplete: function () {
      $('#tabla_planilla thead th')
        .removeClass('sorting sorting_asc sorting_desc');
    }
  });

  // Buscar
  $('#planillaSearch').on('input', function(){
    dt.search(this.value).draw();
  });

  // Ordenar por
  $('#planillaOrden').on('change', function(){
    const [idx, dir] = this.value.split('|');
    dt.order([ Number(idx), dir ]).draw();
  });

  // Mostrar X (futuro)
  $('#planillaLength').on('change', function(){
    dt.page.len( Number(this.value) ).draw();
  });

  // 🔹 Filtro por AÑO (select)
  $('#planillaAnio').on('change', function(){
    filtroAnio = this.value || '';
    dt.ajax.reload(null,false);
  });

  // 🔹 Click en NOMBRE → filtrar por ese cod_persona
  $('#tabla_planilla').on('click', 'td.col-nombre', async function(){
    const row = dt.row(this).data();
    if (!row) return;
    filtroCodPersona = row.cod_persona || null;
    dt.ajax.reload(null,false);
  });

  // 🔹 Botón para limpiar filtro persona + año
  $('#btnLimpiarFiltro').on('click', function(){
    filtroCodPersona = null;
    filtroAnio       = '';
    $('#planillaAnio').val('');
    dt.ajax.reload(null,false);
  });

  /* === Helpers para acciones === */
  async function resolveCodPersona(row){
    if (row.cod_persona) return row.cod_persona;
    const dni = row.dni;
    if (!dni) return null;
    try {
      const res = await fetch(`${API_BASE}/personas/detalle`);
      const lista = await res.json();
      const match = (lista || []).find(p => (p.dni || '').trim() === String(dni).trim());
      return match ? match.cod_persona : null;
    } catch(e) {
      console.error('resolveCodPersona', e);
      return null;
    }
  }

  // VER DETALLES
  $('#tabla_planilla').on('click', '.btn-detalles', async function(){
    const tr = $(this).closest('tr');
    const row = dt.row(tr).data() || dt.row(tr.prev()).data();
    if (!row) {
      return swalInfo('No se pudo leer la fila seleccionada.');
    }

    currentCodPersona = await resolveCodPersona(row);
    configurarCamposParaModo('detalle');
    $('#modalTitulo').text('Detalles del cálculo');
    limpiarModal();
    fillModalFromRow(row);
    previewTotales();
    modal.style.display = 'flex';
  });

  // EDITAR
  $('#tabla_planilla').on('click', '.btn-editar', async function(){
    const tr = $(this).closest('tr');
    const row = dt.row(tr).data() || dt.row(tr.prev()).data();
    if (!row) {
      return swalInfo('No se pudo leer la fila seleccionada.');
    }
    currentCodPersona = await resolveCodPersona(row);
    if (!currentCodPersona) {
      return swalError('No se pudo identificar el empleado (cod_persona).');
    }
    configurarCamposParaModo('editar');
    $('#modalTitulo').text('Editar cálculos');
    limpiarModal();
    fillModalFromRow(row);
    previewTotales();
    modal.style.display = 'flex';
  });

  // ELIMINAR
  $('#tabla_planilla').on('click', '.btn-eliminar', async function(){
    const tr = $(this).closest('tr');
    const row = dt.row(tr).data() || dt.row(tr.prev()).data();
    if (!row) {
      return swalInfo('No se pudo leer la fila seleccionada.');
    }

    const codPersona = await resolveCodPersona(row);
    if (!codPersona) {
      return swalError('No se pudo identificar el empleado (cod_persona).');
    }

    const ok = await Swal.fire({
      icon:'question',
      title:'¿Estás seguro?',
      text:`¿Eliminar la planilla de "${row.nombre}"? Esta acción no se puede deshacer.`,
      showCancelButton:true,
      confirmButtonText:'Sí, eliminar',
      cancelButtonText:'Cancelar'
    }).then(r => r.isConfirmed);
    if (!ok) return;

    try {
      await runWithLoading(() => deletePlanillaByPersona(codPersona), 'Eliminando...');
      dt.ajax.reload(null,false);
      swalSuccess('Registro eliminado correctamente.', 'Éxito');
    } catch(e) {
      console.error(e);
      swalError('No fue posible eliminar el registro.');
    }
  });
});

/* ===== Cargar empleados (modo nuevo) ===== */
async function cargarEmpleados(){
  try{
    await runWithLoading(async ()=>{
      const res = await fetch(`${API_BASE}/empleados`);
      const empleados = await res.json();
      const $sel = $('#selEmpleado');
      $sel.empty().append(`<option value="">— Seleccione empleado —</option>`);
      empleados.forEach(e=>{
        if (e.cod_persona && e.nombre_completo) {
          $sel.append(`<option value="${e.cod_persona}">${e.nombre_completo} — ${e.puesto || '-'}</option>`);
        }
      });
      $sel.data('loaded',true);
    }, 'Cargando empleados...');
  }catch(e){
    console.error(e);
    swalError('No se pudo cargar la lista de empleados.');
  }
}

/* ===== API helpers ===== */
async function postPlanilla(payload){
  const res = await fetch(`${API_BASE}/planillas`, {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body:JSON.stringify(payload)
  });
  if (!res.ok) throw new Error((await res.json()).error || 'Error en POST /planillas');
  return res.json();
}
async function putPlanillaByPersona(cod_persona, payload){
  const res = await fetch(`${API_BASE}/planillas/by-persona/${cod_persona}`, {
    method:'PUT',
    headers:{'Content-Type':'application/json'},
    body:JSON.stringify(payload)
  });
  if (!res.ok) throw new Error((await res.json()).error || 'Error en PUT /planillas/by-persona');
  return res.json();
}
async function deletePlanillaByPersona(cod_persona){
  const res = await fetch(`${API_BASE}/planillas/by-persona/${cod_persona}`, {
    method:'DELETE'
  });
  if (!res.ok){
    let msg = 'Error en DELETE /planillas/by-persona';
    try{ msg = (await res.json()).error || msg; }catch(_){}
    throw new Error(msg);
  }
  return res.json();
}

/* ===== Cargar datos en modal (botón CARGAR DATOS) ===== */
$('#btnCargarEmpleado').on('click', async function(){
  const cod_persona = $('#selEmpleado').val();
  if (!cod_persona) return swalInfo('Seleccione un empleado.');

  try{
    const resp = await runWithLoading(()=>postPlanilla({
      cod_persona,
      solo_preview: true,  // 🔹 Solo previsualizar, no guardar
      injupemp_reingresos:0,
      injupemp_prestamos:0,
      prestamo_banco_atlantida:0,
      pagos_deducibles:0,
      colegio_admon_empresas:0,
      cuota_coop_elga:0
    }), 'Cargando datos...');

    currentCodPersona = cod_persona;
    fillModalFromRow({
      nombre: resp.persona?.nombre_completo,
      rtn: resp.persona?.rtn,
      dni: resp.persona?.dni,
      cargo: resp.puesto?.nom_puesto,
      fecha_ingreso: resp.contrato?.fecha_inicio_contrato,
      salario: resp.contrato?.salario,
      dd: resp.calculados?.dd,
      dt: resp.calculados?.dt,
      salariobruto: resp.calculados?.salario_bruto,
      ihss: resp.calculados?.ihss,
      isr: resp.calculados?.isr,
      injupemp: resp.calculados?.injupemp,
      vecinal: resp.calculados?.impuesto_vecinal,
      total_deducciones: resp.calculados?.total_deducciones,
      total_a_pagar: resp.calculados?.total_a_pagar,
      periodo: periodoHoy() 
    });
    previewTotales();
    swalSuccess('Datos cargados para el cálculo.', 'OK');
  }catch(e){
    console.error(e);
    swalError('No fue posible cargar los datos calculados.');
  }
});

/* ===== Guardar planilla ===== */
$('#btnGuardarPlanilla').on('click', async function(){
  try{
    const payload = {
      injupemp_reingresos: num($('#f_inj_reing').val()),
      injupemp_prestamos: num($('#f_inj_prest').val()),
      prestamo_banco_atlantida: num($('#f_banco_atl').val()),
      pagos_deducibles: num($('#f_pagos_ded').val()),
      colegio_admon_empresas: num($('#f_colegio').val()),
      cuota_coop_elga: num($('#f_coop_elga').val())
    };

    if (modalMode === 'editar') {
      if (!currentCodPersona) {
        return swalError('No se pudo identificar el empleado.');
      }
      await runWithLoading(()=>putPlanillaByPersona(currentCodPersona, payload), 'Guardando cambios...');
      swalSuccess('Cambios guardados correctamente.', 'OK');
    } else {
      const cod_persona = $('#selEmpleado').val();
      if (!cod_persona) {
        return swalInfo('Seleccione un empleado.');
      }
      await runWithLoading(()=>postPlanilla({ cod_persona, ...payload }), 'Creando planilla...');
      swalSuccess('Planilla creada correctamente.', 'OK');
    }

    $('#tabla_planilla').DataTable().ajax.reload(null,false);
    cerrarModal();
  }catch(e){
    console.error(e);
    swalError('No fue posible guardar los cambios.');
  }
});
</script>
@endsection
