@extends('layouts.dashboard')
@section('title','Control de Vacaciones')

@section('content')
@if (session('success'))
@endif

 <div class="titulo-con-linea">
    <h2>Vacaciones</h2>
  </div>

<div class="vacaciones-wrapper">

  {{-- Filtros superiores --}}
  <div class="filtros-vacaciones">
    <form id="filtro" method="GET" action="{{ route('vacaciones.index') }}" class="formulario-filtros-vacaciones">
      <select class="form-select" name="cod_empleado" onchange="this.form.submit()">
        <option value="0">Todos los empleados</option>
        @foreach ($empleados as $e)
          <option value="{{ $e->cod_empleado }}" {{ $cod_empleado==$e->cod_empleado?'selected':'' }}>
            {{ $e->nombre_completo }}
          </option>
        @endforeach
      </select>

      <select class="form-select" name="mes" onchange="this.form.submit()">
        @for ($m=1;$m<=12;$m++)
          <option value="{{ $m }}" {{ $mes==$m?'selected':'' }}>
            {{ ucfirst(\Carbon\Carbon::create(null,$m,1)->translatedFormat('F')) }}
          </option>
        @endfor
      </select>

      <select class="form-select" name="anio" onchange="this.form.submit()">
        @for ($y=now()->year-2; $y<=now()->year+2; $y++)
          <option value="{{ $y }}" {{ $anio==$y?'selected':'' }}>{{ $y }}</option>
        @endfor
      </select>

      {{-- Botón Nueva Solicitud (sin Bootstrap) --}}
      <button type="button" class="btn-nueva-vacacion" id="btnNuevaSolicitud">
        <i class="fa-solid fa-plus"></i> Nueva Solicitud
      </button>
    </form>

    {{-- Botones de exportar --}}
    <div class="botones-exportar-vacaciones">
      <a class="btn-exportar-vacaciones pdf" href="{{ route('vacaciones.export.pdf', compact('mes','anio','cod_empleado')) }}">
        <i class="fa-solid fa-file-pdf"></i> PDF
      </a>
      <a class="btn-exportar-vacaciones excel" href="{{ route('vacaciones.export.csv', compact('mes','anio','cod_empleado')) }}">
        <i class="fa-solid fa-file-excel"></i> Excel
      </a>
    </div>
  </div>

  {{-- Resumen / tarjetas rápidas --}}
  <div class="row g-3" style="margin-bottom:16px;">
    <div class="col-md-3">
      <div class="vac-card">
        <div class="label">Saldo disponible</div>
        <div class="valor">{{ $saldo===null ? '—' : $saldo }} días</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="vac-card">
        <div class="label">Días tomados (año)</div>
        <div class="valor">{{ $diasTomados }}</div>
      </div>
    </div>
  </div>

  {{-- Tabla / calendario de días --}}
  <div class="tabla-vacaciones">
    <table>
      <thead>
        <tr>
          <th>Empleado</th>
          @for ($d=1; $d<=$diasMes; $d++)
            <th class="text-center">{{ $d }}</th>
          @endfor
        </tr>
      </thead>
      <tbody>
        @foreach ($empleados as $e)
          @if ($cod_empleado>0 && $cod_empleado!=$e->cod_empleado) @continue @endif
          @php $dias = $map[$e->cod_empleado]['dias'] ?? []; @endphp
          <tr>
            <td class="nombre-empleado-vacaciones">{{ $e->nombre_completo }}</td>
            @for ($d=1; $d<=$diasMes; $d++)
              @php $estado = strtoupper($dias[$d] ?? ''); @endphp
              <td class="text-center">
                @if ($estado==='APROBADA')
                  <span class="badge-dot dot-ap"></span>
                @elseif ($estado==='PENDIENTE')
                  <span class="badge-dot dot-pe"></span>
                @elseif ($estado==='RECHAZADA')
                  <span class="badge-dot dot-re"></span>
                @endif
              </td>
            @endfor
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

</div>

{{-- ===== Modal Nueva Solicitud (SIN Bootstrap) ===== --}}
<div id="nuevaSolicitud" class="vac-modal" aria-hidden="true">
  <div class="vac-modal__backdrop" data-close></div>

  <div class="vac-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="vacModalTitle">
    <div class="vac-modal__header">
      <h5 class="vac-modal__title" id="vacModalTitle">Nueva Solicitud de Vacaciones</h5>
      <button type="button" class="vac-modal__close" aria-label="Cerrar" data-close>&times;</button>
    </div>

    <form class="vac-modal__body" method="POST" action="{{ route('vacaciones.store') }}">
      @csrf
      <div class="mb-2">
        <label class="form-label">Empleado</label>
        <select class="form-select" name="cod_empleado" required>
          @foreach ($empleados as $e)
            <option value="{{ $e->cod_empleado }}">{{ $e->nombre_completo }}</option>
          @endforeach
        </select>
      </div>

      <div class="row g-2">
        <div class="col">
          <label class="form-label">Inicio</label>
          <input type="date" name="fecha_inicio" class="form-control" required>
        </div>
        <div class="col">
          <label class="form-label">Fin</label>
          <input type="date" name="fecha_fin" class="form-control" required>
        </div>
      </div>

      <div class="mt-2">
        <label class="form-label">Comentario</label>
        <textarea name="comentario" class="form-control" rows="2"></textarea>
      </div>

      <div class="vac-modal__footer">
        <button type="submit" class="btn-buscar-asistencia">
         <i class="fa-solid fa-paper-plane"></i> Enviar
        </button>
        <button type="button" class="btn-ghost" data-close>Cancelar</button>
      </div>
    </form>
  </div>
</div>

{{-- ===== Estilos del modal (mínimos, no invaden otros) ===== --}}
<style>
.vac-modal{position:fixed;inset:0;display:none;z-index:2000}
.vac-modal.open{display:flex;align-items:center;justify-content:center;padding:16px}
.vac-modal__backdrop{position:absolute;inset:0;background:rgba(0,0,0,.45)}
.vac-modal__dialog{position:relative;background:#fff;border-radius:14px;width:min(640px,95vw);
  box-shadow:0 20px 60px rgba(0,0,0,.25);overflow:hidden}
.vac-modal__header,.vac-modal__footer{padding:12px 16px;background:#f8f9fa;display:flex;align-items:center;justify-content:space-between}
.vac-modal__body{padding:16px}
.vac-modal__title{margin:0;font-weight:700}
.vac-modal__close{background:none;border:0;font-size:24px;line-height:1;cursor:pointer}
body.modal-open{overflow:hidden}
.btn-ghost{display:inline-flex;align-items:center;gap:8px;border:0;border-radius:8px;padding:10px 16px;font-weight:600;background:#eef2f7;color:#111827;cursor:pointer}
.btn-ghost:hover{background:#e5e7eb}

/* Asegura que SweetAlert quede siempre por encima del modal */
.swal2-container{z-index:10000 !important;}
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  // ----- Modal sin Bootstrap -----
  const modal = document.getElementById('nuevaSolicitud');
  const openBtn = document.getElementById('btnNuevaSolicitud');

  const openModal = () => {
    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('modal-open');
    setTimeout(() => modal.querySelector('input,select,textarea,button')?.focus(), 0);
  };
  const closeModal = () => {
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('modal-open');
    openBtn?.focus();
  };

  openBtn?.addEventListener('click', (e) => { e.preventDefault(); openModal(); });
  modal.addEventListener('click', (e) => {
    if (e.target.dataset.close !== undefined || e.target === modal.querySelector('.vac-modal__backdrop')) closeModal();
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modal.classList.contains('open')) closeModal();
  });

  // ----- Flash (éxito / error del backend) -----
  @if (session('success'))
    Swal.fire({ icon: 'success', title: 'Vacaciones', text: @json(session('success')), confirmButtonColor: '#0d6efd' });
  @endif
  @if (session('error'))
    Swal.fire({ icon: 'error', title: 'Vacaciones', text: @json(session('error')), confirmButtonColor: '#0d6efd' });
  @endif

  // ----- Errores de validación: lista + abrir modal -----
  @if ($errors->any())
    const errs = @json($errors->all());
    Swal.fire({
      icon: 'error',
      title: 'Revisa el formulario',
      html: '<ul style="text-align:left;margin:0;padding-left:18px;">'
            + errs.map(e => `<li>${e}</li>`).join('') + '</ul>',
      confirmButtonColor: '#0d6efd'
    });
    openModal();
  @endif

  // ----- Confirmación + validación rápida antes de enviar -----
  const frm = modal.querySelector('form');
  if (frm) {
    frm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const fi = frm.querySelector('input[name="fecha_inicio"]').value;
      const ff = frm.querySelector('input[name="fecha_fin"]').value;

      if (fi && ff && ff < fi) {
        await Swal.fire({ icon: 'error', title: 'Rango inválido', text: 'La fecha fin no puede ser menor que la fecha inicio.' });
        return;
      }

      const empTxt = frm.querySelector('select[name="cod_empleado"]').selectedOptions[0]?.text || 'empleado';

      // Cerrar modal antes del SweetAlert para evitar superposición
      const wasOpen = modal.classList.contains('open');
      if (wasOpen) closeModal();

      const r = await Swal.fire({
        icon: 'question',
        title: 'Confirmar envío',
        text: `¿Enviar solicitud para ${empTxt} del ${fi} al ${ff}?`,
        showCancelButton: true,
        confirmButtonText: 'Sí, enviar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d'
      });

      if (r.isConfirmed) {
        frm.submit();
      } else if (wasOpen) {
        // Reabrir si cancelan
        openModal();
      }
    });
  }
});
</script>

@endsection
