@extends('layouts.dashboard')
@section('title', 'Cálculo de Planilla')

@section('content')
<link rel="stylesheet" href="{{ asset('vendor/datatable/datatables.min.css') }}">
<link rel="stylesheet" href="{{ asset('css/planilla.css') }}">

<div class="container-planilla">
  <h2 style="margin-bottom:14px">Cálculo de Planilla</h2>

  <!-- ===== Toolbar: NUEVO primero ===== -->
  <div class="toolbar-planilla">
    <div class="toolbar-left">

      <!-- Botón ahora va primero -->
      <button class="btn-nuevo" onclick="mostrarFormulario()">
        <i class="fas fa-plus"></i> Nuevo Registro
      </button>

      <input id="planillaSearch" type="text" placeholder="Buscar...">
      <span class="lbl">Ordenar por</span>
      <select id="planillaOrden">
        <option value="1|asc">Nombre (A‑Z)</option>
        <option value="1|desc">Nombre (Z‑A)</option>
        <option value="5|desc">Fecha ingreso (Nuevos primero)</option>
        <option value="5|asc">Fecha ingreso (Antiguos primero)</option>
        <option value="8|desc">Salario mensual (Mayor a menor)</option>
        <option value="8|asc">Salario mensual (Menor a mayor)</option>
        <option value="22|desc">Total deducciones (Mayor a menor)</option>
        <option value="23|desc">Total a pagar (Mayor a menor)</option>
        <option value="23|asc">Total a pagar (Menor a mayor)</option>
      </select>
      <span class="lbl">Mostrar</span>
      <select id="planillaLength">
        <option>5</option>
        <option selected>10</option>
        <option>25</option>
        <option>50</option>
      </select>
      <span class="lbl">registros</span>
    </div>
  </div>

  <div class="x-scroll">
    <table class="tabla-planilla display nowrap" id="tabla_planilla">
      <thead>
        <tr>
          <th rowspan="2" class="num" style="width:6ch">NO</th>
          <th rowspan="2" style="width:20ch">NOMBRE</th>
          <th rowspan="2" style="width:16ch">RTN</th>
          <th rowspan="2" style="width:16ch">DNI</th>
          <th rowspan="2" style="width:16ch">CARGO</th>
          <th rowspan="2" class="num" style="width:12ch">FECHA<br>INGRESO</th>
          <th colspan="2" style="width:12ch">DD/DT</th>
          <th rowspan="2" class="num" style="width:14ch">SALARIO<br>MENSUAL</th>
          <th rowspan="2" class="num" style="width:14ch">SALARIO<br>EN BRUTO</th>
          <th colspan="5">DEDUCCIONES DE LEY</th>
          <th colspan="6">DEDUCCIONES AUTORIZADAS</th>
          <th rowspan="2" class="num" style="width:14ch">TOTAL<br>DEDUCCIONES</th>
          <th rowspan="2" class="num" style="width:14ch">TOTAL A<br>PAGAR</th>
          <th rowspan="2" style="width:12ch">ACCIONES</th>
        </tr>
        <tr>
          <th class="num" style="width:6ch">DD</th>
          <th class="num" style="width:6ch">DT</th>
          <th class="num" style="width:11ch">IHSS</th>
          <th class="num" style="width:11ch">ISR</th>
          <th class="num" style="width:11ch">INJUPEMP</th>
          <th class="num" style="width:12ch">IMPUESTO<br>VECINAL</th>
          <th class="num" style="width:12ch">DÍAS<br>DESCARGADOS</th>
          <th class="num" style="width:12ch">INJUPEMP<br>REINGRESOS</th>
          <th class="num" style="width:12ch">INJUPEMP<br>PRÉSTAMOS</th>
          <th class="num" style="width:14ch">PRÉSTAMO BANCO<br>ATLÁNTIDA</th>
          <th class="num" style="width:12ch">PAGOS<br>DEDUCIBLES</th>
          <th class="num" style="width:14ch">COLEGIO DE ADMON.<br>EMPRESAS</th>
          <th class="num" style="width:12ch">CUOTA COOP.<br>ELGA</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</div>

<!-- ========================= Modal ========================= -->
<div class="modal-planilla" id="modalPlanilla" style="display:none">
  <div class="modal-contenido">
    <div class="modal-header">
      <h5 class="m-0" id="modalTitulo">Registro de un nuevo cálculo</h5>
      <button class="btn-close-x" onclick="cerrarModal()">×</button>
    </div>
    <div class="modal-body">
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

      <div class="card-soft">
        <div class="grid grid-3">
          <div><label class="form-label">Nombre completo</label><input id="p_nombre" class="form-control" readonly></div>
          <div><label class="form-label">RTN</label><input id="p_rtn" class="form-control" readonly></div>
          <div><label class="form-label">DNI</label><input id="p_dni" class="form-control" readonly></div>
        </div>
        <div class="grid grid-3" style="margin-top:10px">
          <div><label class="form-label">Cargo</label><input id="p_puesto" class="form-control" readonly></div>
          <div><label class="form-label">Fecha ingreso</label><input id="p_fecha_ingreso" class="form-control" readonly></div>
          <div><label class="form-label">Salario mensual</label><input id="p_salario" class="form-control text-end" readonly></div>
        </div>
      </div>

      <div class="card-soft">
        <div class="grid grid-3">
          <div><label class="form-label">DD</label><input id="p_dd" class="form-control text-end" readonly></div>
          <div><label class="form-label">DT</label><input id="p_dt" class="form-control text-end" readonly></div>
          <div><label class="form-label">Salario en bruto</label><input id="p_salario_bruto" class="form-control text-end" readonly></div>
        </div>
      </div>

      <div class="card-soft">
        <div class="grid grid-4">
          <div><label class="form-label">IHSS</label><input id="p_ihss" class="form-control text-end" readonly></div>
          <div><label class="form-label">ISR</label><input id="p_isr" class="form-control text-end" readonly></div>
          <div><label class="form-label">INJUPEMP</label><input id="p_injupemp" class="form-control text-end" readonly></div>
          <div><label class="form-label">Impuesto vecinal</label><input id="p_vecinal" class="form-control text-end" readonly></div>
        </div>
      </div>

      <div class="card-soft">
        <div class="grid grid-3">
          <div><label class="form-label">INJUPEMP / Reingresos</label><input id="f_inj_reing" class="form-control text-end" inputmode="decimal" value="0"></div>
          <div><label class="form-label">INJUPEMP Préstamos</label><input id="f_inj_prest" class="form-control text-end" inputmode="decimal" value="0"></div>
          <div><label class="form-label">Préstamo Banco Atlántida</label><input id="f_banco_atl" class="form-control text-end" inputmode="decimal" value="0"></div>
        </div>
        <div class="grid grid-3" style="margin-top:10px">
          <div><label class="form-label">Pagos deducibles</label><input id="f_pagos_ded" class="form-control text-end" inputmode="decimal" value="0"></div>
          <div><label class="form-label">Colegio Adm. Empresas</label><input id="f_colegio" class="form-control text-end" inputmode="decimal" value="0"></div>
          <div><label class="form-label">Cuota Coop. ELGA</label><input id="f_coop_elga" class="form-control text-end" inputmode="decimal" value="0"></div>
        </div>
      </div>

      <div class="card-soft">
        <div class="grid grid-2">
          <div><label class="form-label">Total deducciones</label><input id="p_total_ded" class="form-control text-end" readonly></div>
          <div><label class="form-label">Total a pagar</label><input id="p_total_pagar" class="form-control text-end fw-bold" readonly></div>
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
const API_BASE='http://localhost:3000/api';
let modalMode='nuevo';
let currentCodPersona=null;

const modal=document.getElementById('modalPlanilla');
function mostrarFormulario(){
  modalMode='nuevo'; currentCodPersona=null;
  $('#modalTitulo').text('Registro de un nuevo cálculo');
  $('#bloqueEmpleado').show();
  limpiarModal();
  if(!$('#selEmpleado').data('loaded')) cargarEmpleados();
  modal.style.display='flex';
}
function cerrarModal(){ modal.style.display='none'; }

const nf2=new Intl.NumberFormat('es-HN',{minimumFractionDigits:2,maximumFractionDigits:2});
const num=v=>Number(String(v||'0').replace(/[^0-9.\-]/g,''))||0;
const fmt=v=>nf2.format(num(v));

function swalInfo(text, title='Aviso'){ return Swal.fire({icon:'info', title, text}); }
function swalSuccess(text, title='Éxito'){ return Swal.fire({icon:'success', title, text}); }
function swalError(text, title='Error'){ return Swal.fire({icon:'error', title, text}); }
async function swalConfirm(text, title='¿Estás seguro?'){
  const r = await Swal.fire({ icon:'question', title, text, showCancelButton:true,
    confirmButtonText:'Sí, continuar', cancelButtonText:'No, cancelar' });
  return r.isConfirmed;
}
async function runWithLoading(fn, title='Procesando...', text='Por favor espera'){
  Swal.fire({title, text, allowOutsideClick:false, didOpen:()=>Swal.showLoading()});
  try{ const out = await fn(); Swal.close(); return out; } catch(err){ Swal.close(); throw err; }
}

function limpiarModal(){
  $('#selEmpleado').val('');
  $('#p_nombre,#p_rtn,#p_dni,#p_puesto,#p_fecha_ingreso').val('');
  $('#p_salario,#p_dd,#p_dt,#p_salario_bruto,#p_ihss,#p_isr,#p_injupemp,#p_vecinal,#p_total_ded,#p_total_pagar').val('');
  $('#f_inj_reing,#f_inj_prest,#f_banco_atl,#f_pagos_ded,#f_colegio,#f_coop_elga').val('0');
}

function fillModalFromRow(row){
  $('#p_nombre').val(row.nombre||'');
  $('#p_rtn').val(row.rtn||'');
  $('#p_dni').val(row.dni||'');
  $('#p_puesto').val(row.cargo||'');
  $('#p_fecha_ingreso').val(formatDate(row.fecha_ingreso||''));
  $('#p_salario').val(fmt( resolveSalario(row) ));
  $('#p_dd').val(row.dd??''); $('#p_dt').val(row.dt??'');
  $('#p_salario_bruto').val(fmt(row.salariobruto||row.salario_bruto||0));
  $('#p_ihss').val(fmt(row.ihss||0)); $('#p_isr').val(fmt(row.isr||0));
  $('#p_injupemp').val(fmt(row.injupemp||0));
  $('#p_vecinal').val(fmt(row.vecinal||row.impuesto_vecinal||0));
  $('#f_inj_reing').val(row.injupemp_reingresos||0);
  $('#f_inj_prest').val(row.injupemp_prestamos||0);
  $('#f_banco_atl').val(row.prestamo_banco_atlantida||0);
  $('#f_pagos_ded').val(row.pagos_deducibles||0);
  $('#f_colegio').val(row.colegio_admon_empresas||0);
  $('#f_coop_elga').val(row.cuota_coop_elga||0);
  $('#p_total_ded').val(fmt(row.total_deducciones||0));
  $('#p_total_pagar').val(fmt(row.total_a_pagar||0));
}

function previewTotales(){
  const salario_bruto=num($('#p_salario_bruto').val()),
        ihss=num($('#p_ihss').val()), isr=num($('#p_isr').val()),
        inj=num($('#p_injupemp').val()), vec=num($('#p_vecinal').val());
  const a1=num($('#f_inj_reing').val()), a2=num($('#f_inj_prest').val()),
        a3=num($('#f_banco_atl').val()), a4=num($('#f_pagos_ded').val()),
        a5=num($('#f_colegio').val()), a6=num($('#f_coop_elga').val());
  const total_ded=ihss+isr+inj+vec+a1+a2+a3+a4+a5+a6;
  const total_pagar=Math.max(salario_bruto-total_ded,0);
  $('#p_total_ded').val(fmt(total_ded));
  $('#p_total_pagar').val(fmt(total_pagar));
}
$(document).on('input','#f_inj_reing,#f_inj_prest,#f_banco_atl,#f_pagos_ded,#f_colegio,#f_coop_elga',previewTotales);

/* ===== Helpers ===== */
function formatDate(val){
  if(!val) return '';
  if (typeof val === 'string' && /^\d{4}-\d{2}-\d{2}/.test(val)) return val.slice(0,10);
  const d = new Date(val); if(!isNaN(d)) return d.toISOString().slice(0,10);
  return String(val);
}
function resolveSalario(row){
  const s = Number(row.salario||0);
  if (s > 0) return s;
  const dt = Number(row.dt||0);
  const bruto = Number(row.salariobruto||row.salario_bruto||0);
  return dt > 0 ? Math.round((bruto*30/dt)*100)/100 : 0;
}

/* ===== DataTable + Toolbar ===== */
$(document).ready(function () {
  const nf2 = new Intl.NumberFormat('es-HN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  const fmt2 = v => (v != null && !isNaN(v) && Number(v) >= 0 ? nf2.format(Number(v)) : '—');
  const showIntOrDash = v => (v != null && Number(v) >= 0 ? Number(v) : '—');
  const totalDeduccionesClient = row =>
    (Number(row.ihss||0)+Number(row.isr||0)+Number(row.injupemp||0)+Number(row.vecinal||0)+
     Number(row.injupemp_reingresos||0)+Number(row.injupemp_prestamos||0)+Number(row.prestamo_banco_atlantida||0)+
     Number(row.pagos_deducibles||0)+Number(row.colegio_admon_empresas||0)+Number(row.cuota_coop_elga||0));
  const totalAPagarClient = row =>
    Math.max(Number(row.salariobruto||row.salario_bruto||0) - totalDeduccionesClient(row), 0);

  const dt = $('#tabla_planilla').DataTable({
    ajax:{
      url:'{{ route("planilla") }}',
      type:'POST',
      headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
      data:{ accion:'ver_planilla' },
      dataSrc:'data',
      error: function(xhr){
        console.error('Ajax error', xhr.status, xhr.statusText, xhr.responseText);
        swalError('Error al cargar la planilla desde el servidor. Revisa la consola (F12).');
      }
    },
    ordering: false,          
    order: [],
    paging: false, // 🔹 Desactiva paginación
    info: false,   // 🔹 Oculta "Mostrando X de X"
    dom: 't',    // tabla + info + paginación
    columns:[
      { data:'no' },
      { data:'nombre' },
      { data:'rtn' },
      { data:'dni' },
      { data:'cargo' },
      { data:'fecha_ingreso', render:v=>formatDate(v) },
      { data:'dd', render:showIntOrDash },
      { data:'dt', render:showIntOrDash },
      { data:null, render:(d,t,row)=>fmt2( resolveSalario(row) ) },
      { data:'salariobruto', render:v=>fmt2(v) },
      { data:'ihss', render:v=>fmt2(v) },
      { data:'isr', render:v=>fmt2(v) },
      { data:'injupemp', render:v=>fmt2(v) },
      { data:'vecinal', render:v=>fmt2(v) },
      { data:'dias_descargados', render:showIntOrDash },
      { data:'injupemp_reingresos', render:v=>fmt2(v) },
      { data:'injupemp_prestamos', render:v=>fmt2(v) },
      { data:'prestamo_banco_atlantida', render:v=>fmt2(v) },
      { data:'pagos_deducibles', render:v=>fmt2(v) },
      { data:'colegio_admon_empresas', render:v=>fmt2(v) },
      { data:'cuota_coop_elga', render:v=>fmt2(v) },
      { data:null, render:(d,t,row)=>fmt2((row.total_deducciones!=null&&!isNaN(row.total_deducciones))?Number(row.total_deducciones):totalDeduccionesClient(row)) },
      { data:null, render:(d,t,row)=>fmt2((row.total_a_pagar!=null&&!isNaN(row.total_a_pagar))?Number(row.total_a_pagar):totalAPagarClient(row)) },
      {
        data:null, orderable:false,
        render:()=>`
          <div class="acciones-col">
            <button class="btn-editar-vis btn-editar">Editar</button>
            <button class="btn-eliminar-vis btn-eliminar">Eliminar</button>
          </div>`
      }
    ],
    language:{ url:'{{ asset("vendor/datatable/es-ES.json") }}' }
  });

  /* Buscar en tiempo real */
  $('#planillaSearch').on('input', function(){ dt.search(this.value).draw(); });

  /* Ordenar por (select) */
  $('#planillaOrden').on('change', function(){
    const [idx, dir] = this.value.split('|');
    dt.order([ Number(idx), dir ]).draw();
  });

  /* Mostrar X registros (select) */
  $('#planillaLength').on('change', function(){
    dt.page.len( Number(this.value) ).draw();
  });

  /* === Acciones === */
  async function resolveCodPersona(row){
    if(row.cod_persona) return row.cod_persona;
    const dni = row.dni; if(!dni) return null;
    try{
      const res = await fetch(`${API_BASE}/personas/detalle`);
      const lista = await res.json();
      const match = (lista||[]).find(p => (p.dni||'').trim() === String(dni).trim());
      return match ? match.cod_persona : null;
    }catch(e){ console.error('resolveCodPersona', e); return null; }
  }

  $('#tabla_planilla').on('click','.btn-editar',async function(){
    const tr = $(this).closest('tr');
    const row = dt.row(tr).data() || dt.row(tr.prev()).data();
    if(!row){ return Swal.fire({icon:'info',title:'Aviso',text:'No se pudo leer la fila seleccionada.'}); }
    modalMode='editar';
    currentCodPersona = await resolveCodPersona(row);
    if(!currentCodPersona){ return Swal.fire({icon:'error',title:'Error',text:'No se pudo identificar el empleado (cod_persona).'}); }
    $('#modalTitulo').text('Editar Cálculos');
    $('#bloqueEmpleado').hide();
    limpiarModal(); fillModalFromRow(row); previewTotales();
    modal.style.display='flex';
  });

  $('#tabla_planilla').on('click','.btn-eliminar',async function(){
    const tr = $(this).closest('tr');
    const row = dt.row(tr).data() || dt.row(tr.prev()).data();
    if(!row){ return Swal.fire({icon:'info',title:'Aviso',text:'No se pudo leer la fila seleccionada.'}); }

    const codPersona = await resolveCodPersona(row);
    if(!codPersona){ return Swal.fire({icon:'error',title:'Error',text:'No se pudo identificar el empleado (cod_persona).'}); }

    const ok = await Swal.fire({icon:'question',title:'¿Estás seguro?',
      text:`¿Eliminar la planilla de "${row.nombre}"? Esta acción no se puede deshacer.`,
      showCancelButton:true, confirmButtonText:'Sí, eliminar', cancelButtonText:'Cancelar'}).then(r=>r.isConfirmed);
    if(!ok) return;

    try{
      await runWithLoading(()=>deletePlanillaByPersona(codPersona),'Eliminando...');
      dt.ajax.reload(null,false);
      Swal.fire({icon:'success',title:'Éxito',text:'Registro eliminado correctamente.'});
    }catch(e){
      console.error(e); Swal.fire({icon:'error',title:'Error',text:'No fue posible eliminar el registro.'});
    }
  });
});

/* ===== Cargar empleados (modo nuevo) ===== */
async function cargarEmpleados(){
  try{
    await runWithLoading(async ()=>{
      const res=await fetch(`${API_BASE}/empleados`);
      const empleados=await res.json();
      const $sel=$('#selEmpleado'); $sel.empty().append(`<option value="">— Seleccione empleado —</option>`);
      empleados.forEach(e=>{
        if(e.cod_persona && e.nombre_completo){
          $sel.append(`<option value="${e.cod_persona}">${e.nombre_completo} — ${e.puesto||'-'}</option>`);
        }
      });
      $sel.data('loaded',true);
    },'Cargando empleados...');
  }catch(e){ console.error(e); Swal.fire({icon:'error',title:'Error',text:'No se pudo cargar la lista de empleados.'}); }
}

/* ===== API helpers ===== */
async function postPlanilla(payload){
  const res=await fetch(`${API_BASE}/planillas`,{ method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload) });
  if(!res.ok) throw new Error((await res.json()).error||'Error en POST /planillas');
  return res.json();
}
async function putPlanillaByPersona(cod_persona, payload){
  const res=await fetch(`${API_BASE}/planillas/by-persona/${cod_persona}`,{ method:'PUT', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload) });
  if(!res.ok) throw new Error((await res.json()).error||'Error en PUT /planillas/by-persona');
  return res.json();
}
async function deletePlanillaByPersona(cod_persona){
  const res=await fetch(`${API_BASE}/planillas/by-persona/${cod_persona}`,{ method:'DELETE' });
  if(!res.ok){
    let msg='Error en DELETE /planillas/by-persona';
    try{ msg=(await res.json()).error||msg; }catch(_){}
    throw new Error(msg);
  }
  return res.json();
}

/* ===== Guardar desde modal ===== */
$('#btnCargarEmpleado').on('click', async function(){
  const cod_persona=$('#selEmpleado').val();
  if(!cod_persona) return Swal.fire({icon:'info',title:'Aviso',text:'Seleccione un empleado.'});
  try{
    const resp = await runWithLoading(()=>postPlanilla({
      cod_persona,
      injupemp_reingresos:0, injupemp_prestamos:0, prestamo_banco_atlantida:0,
      pagos_deducibles:0, colegio_admon_empresas:0, cuota_coop_elga:0
    }),'Cargando datos...');
    currentCodPersona = cod_persona;
    fillModalFromRow({
      nombre: resp.persona?.nombre_completo,
      rtn: resp.persona?.rtn,
      dni: resp.persona?.dni,
      cargo: resp.puesto?.nom_puesto,
      fecha_ingreso: resp.contrato?.fecha_inicio_contrato,
      salario: resp.contrato?.salario,
      dd: resp.calculados?.dd, dt: resp.calculados?.dt,
      salariobruto: resp.calculados?.salario_bruto,
      ihss: resp.calculados?.ihss, isr: resp.calculados?.isr,
      injupemp: resp.calculados?.injupemp, vecinal: resp.calculados?.impuesto_vecinal,
      total_deducciones: resp.calculados?.total_deducciones, total_a_pagar: resp.calculados?.total_a_pagar
    });
    previewTotales();
    Swal.fire({icon:'success',title:'OK',text:'Datos cargados para el cálculo.'});
  }catch(e){ console.error(e); Swal.fire({icon:'error',title:'Error',text:'No fue posible cargar los datos calculados.'}); }
});

$('#btnGuardarPlanilla').on('click', async function(){
  try{
    const payload={
      injupemp_reingresos:num($('#f_inj_reing').val()),
      injupemp_prestamos:num($('#f_inj_prest').val()),
      prestamo_banco_atlantida:num($('#f_banco_atl').val()),
      pagos_deducibles:num($('#f_pagos_ded').val()),
      colegio_admon_empresas:num($('#f_colegio').val()),
      cuota_coop_elga:num($('#f_coop_elga').val())
    };

    if(modalMode==='editar'){
      if(!currentCodPersona) return Swal.fire({icon:'error',title:'Error',text:'No se pudo identificar el empleado.'});
      await runWithLoading(()=>putPlanillaByPersona(currentCodPersona, payload),'Guardando cambios...');
      Swal.fire({icon:'success',title:'OK',text:'Cambios guardados correctamente.'});
    }else{
      const cod_persona=$('#selEmpleado').val();
      if(!cod_persona) return Swal.fire({icon:'info',title:'Aviso',text:'Seleccione un empleado.'});
      await runWithLoading(()=>postPlanilla({ cod_persona, ...payload }),'Creando planilla...');
      Swal.fire({icon:'success',title:'OK',text:'Planilla creada correctamente.'});
    }

    $('#tabla_planilla').DataTable().ajax.reload(null,false);
    cerrarModal();
  }catch(e){ console.error(e); Swal.fire({icon:'error',title:'Error',text:'No fue posible guardar los cambios.'}); }
});
</script>
@endsection
