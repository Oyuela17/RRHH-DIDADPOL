@extends('layouts.dashboard')
@section('title','Backups')

{{-- (Opcional) Estilos mínimos de apoyo si no ya existen en tu CSS global --}}
@section('styles')
<style>
  .backups-title{font-size:2rem;font-weight:800;margin:6px 0 18px 0;color:#0b3a63}
  .backups-subinfo{margin-bottom:16px}
  .grid-status-upload{display:grid;grid-template-columns:1fr 1fr;gap:16px}
  .card-lite{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px}
  .section-title{margin:0 0 10px 0;font-weight:700;color:#0b3a63}
  .kv.small .kv-row{display:flex;gap:10px;margin:.25rem 0}
  .kv dt{min-width:90px;color:#6b7280}
  .bk-badge{display:inline-block;border-radius:999px;padding:2px 8px;font-size:.8rem;border:1px solid #ddd;color:#374151;background:#f9fafb}
  .bk-badge-success{background:#eaf5ef;color:#166b40;border-color:#cfe7d8}
  .bk-btn-ejecutar{background:#2e7d32;color:#fff;border:none;border-radius:8px;padding:10px 14px;font-weight:700;cursor:pointer}
  .input-hint{font-size:.9rem;color:#6b7280;margin:6px 0}
  .input-file input[type="file"]{display:block;width:100%}
  .bk-btn{display:inline-flex;align-items:center;gap:8px;border-radius:8px;padding:8px 12px;border:1px solid transparent;font-weight:600;text-decoration:none}
  .bk-btn-primary{background:#14532d;color:#fff}
  .bk-btn-info{background:#0b3a63;color:#fff}
  .bk-btn-success{background:#166b40;color:#fff}
  .bk-btn-danger{background:#dc3545;color:#fff}
  .bk-acciones-col{display:flex;flex-direction:column;gap:8px}
  .empleados-container{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:0;overflow:hidden}
  .empleados-table{width:100%;border-collapse:separate;border-spacing:0}
  .empleados-table thead th{background:#053b73;color:#fff;padding:10px 12px;text-align:left}
  .empleados-table tbody td{background:#fff;padding:10px 12px;border-top:1px solid #eef2f7;vertical-align:middle}
  @media (max-width: 1024px){ .grid-status-upload{grid-template-columns:1fr} }
</style>
@endsection

@section('content')
<div class="empleados-wrapper">

  <h2 class="backups-title">Backups</h2>

  {{-- Subinfo + botón ejecutar + subir archivo --}}
  <div class="backups-subinfo">
    {{-- Mensajes de sesión para SweetAlert (se lanzan abajo con JS) --}}
    @if(session('ok'))  <div class="alert success" style="display:none">{{ session('ok') }}</div> @endif
    @if(session('err')) <div class="alert danger"  style="display:none">{{ session('err') }}</div> @endif

    <div class="grid-status-upload">
      {{-- IZQUIERDA: Estado + Ejecutar --}}
      <div class="card-lite">
        <h4 class="section-title">Estado del último respaldo</h4>
        <dl class="kv small">
          <div class="kv-row">
            <dt>Fecha:</dt>
            <dd>{{ $ultimoBackup['fecha'] ?? '—' }}</dd>
          </div>
          <div class="kv-row">
            <dt>Estado:</dt>
            <dd>
              @php $e = $ultimoBackup['estado'] ?? null; @endphp
              <span class="bk-badge {{ $e==='listo'?'bk-badge-success':'' }}">{{ $e ?? '—' }}</span>
            </dd>
          </div>
          <div class="kv-row">
            <dt>Ubicación:</dt>
            <dd>{{ $ultimoBackup['ubicacion'] ?? '—' }}</dd>
          </div>
        </dl>

        {{-- Ejecutar respaldo --}}
        <form method="POST" action="{{ route('backups.run') }}" style="margin-top:12px">
          @csrf
          <input type="hidden" name="tipo" value="solo_bd">
          <button class="bk-btn-ejecutar">🗄️ Ejecutar respaldo ahora</button>
        </form>
      </div>

      {{-- DERECHA: Subir nuevo respaldo --}}
      <div class="card-lite">
        <h4 class="section-title">Subir nuevo respaldo</h4>
        <div class="input-hint">Seleccionar archivo (.sql o .zip):</div>

        <form method="POST" action="{{ route('backups.restore') }}" enctype="multipart/form-data">
          @csrf
          <div class="input-file">
            <input type="file" name="file" accept=".zip,.sql" required>
          </div>
          <div class="input-hint">Tamaño máximo recomendado 200 MB.</div>

          <button type="submit" class="bk-btn bk-btn-primary" style="margin-top:12px">
            <i class="fas fa-upload"></i> Subir y Restaurar
          </button>
        </form>
      </div>
    </div>
  </div>

  {{-- Tabla con estética de Empleados --}}
  <div class="empleados-container">
    <table class="empleados-table">
      <thead>
        <tr>
          <th style="width:80px">ID</th>
          <th>Backup</th>
          <th style="width:140px">Usuario</th>
          <th style="width:180px">Fecha</th>
          <th style="width:220px">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($files as $f)
        <tr>
          <td>{{ $f['id'] }}</td>
          <td style="text-align:left">
            <div style="font-weight:700">{{ $f['nombre_archivo'] ?? '-' }}</div>
            <div style="color:#6b7280;font-size:.9rem">
              {{ $f['tipo_backup'] ?? 'solo_bd' }} ·
              @if(isset($f['tamano'])) {{ number_format($f['tamano']/1024/1024, 2) }} MB @else — @endif ·
              <span class="bk-badge {{ ($f['estado'] ?? '') === 'listo' ? 'bk-badge-success' : '' }}">{{ $f['estado'] ?? '—' }}</span>
            </div>
          </td>
          <td>#{{ $f['usuario_id'] ?? '—' }}</td>
          <td>{{ isset($f['fecha']) ? \Carbon\Carbon::parse($f['fecha'])->format('Y-m-d H:i') : '—' }}</td>
          <td>
            <div class="bk-acciones-col">
              <a href="{{ route('backups.download', $f['id']) }}" class="bk-btn bk-btn-info">Descargar</a>

              <form action="{{ route('backups.restore.use', $f['id']) }}" method="POST" class="form-restaurar" data-nombre="{{ $f['nombre_archivo'] }}">
                @csrf
                <button type="submit" class="bk-btn bk-btn-success">Restaurar</button>
              </form>

              <form action="{{ route('backups.destroy', $f['id']) }}" method="POST" class="form-eliminar" data-nombre="{{ $f['nombre_archivo'] }}">
                @csrf @method('DELETE')
                <button type="submit" class="bk-btn bk-btn-danger">Eliminar</button>
              </form>
            </div>
          </td>
        </tr>
        @empty
          <tr><td colspan="5">No hay respaldos disponibles.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

</div>

{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  // Confirmar restauración
  document.querySelectorAll('.form-restaurar').forEach(f => {
    f.addEventListener('submit', function(e){
      e.preventDefault();
      const nombre = this.dataset.nombre || '';
      Swal.fire({
        title: '¿Restaurar backup?',
        text: `Se sobrescribirán datos usando "${nombre}". Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, restaurar',
        cancelButtonText: 'Cancelar'
      }).then(r => { if(r.isConfirmed) this.submit(); });
    });
  });

  // Confirmar eliminación
  document.querySelectorAll('.form-eliminar').forEach(f => {
    f.addEventListener('submit', function(e){
      e.preventDefault();
      const nombre = this.dataset.nombre || '';
      Swal.fire({
        title: '¿Eliminar backup?',
        text: `El backup "${nombre}" se eliminará definitivamente.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      }).then(r => { if(r.isConfirmed) this.submit(); });
    });
  });

  // Toasts de sesión
  @if(session('ok'))
    Swal.fire({toast:true, position:'top-end', timer:3500, showConfirmButton:false, icon:'success', title:@json(session('ok'))});
  @endif
  @if(session('err'))
    Swal.fire({toast:true, position:'top-end', timer:4000, showConfirmButton:false, icon:'error', title:@json(session('err'))});
  @endif
});
</script>
@endsection
