{{-- resources/views/bitacora/partials/resultados.blade.php --}}
@php
  $meta       = $meta ?? ['page' => 1, 'last_page' => 1];
  $modoActual = $modoActual ?? 'general';
@endphp

<div class="bitacora-container">
  <table class="bitacora-table tabla-reporte">
    <thead>
      <tr>
        <th>Fecha</th>
        <th>Usuario</th>
        <th>Tipo evento</th>
        <th>Acción</th>
        <th>Tabla</th>
        <th>Descripción</th>
        <th>IP</th>
        <th>Navegador</th> {{-- 👈 ahora siempre existe en ambas pestañas --}}
      </tr>
    </thead>
    <tbody>
      @forelse ($registros as $r)
        <tr>
          <td>
            @if(!empty($r['fecha']))
              {{ \Carbon\Carbon::parse($r['fecha'])->format('Y-m-d H:i:s') }}
            @else
              —
            @endif
          </td>

          <td>{{ $r['usuario_nombre'] ?? '—' }}</td>
          <td>{{ $r['tipo_evento'] ?? '—' }}</td>
          <td>{{ $r['accion'] ?? '—' }}</td>
          <td>{{ $r['tabla'] ?? '—' }}</td>

          <td class="texto-corto" title="{{ $r['descripcion'] ?? '' }}">
            {{ $r['descripcion'] ?? '—' }}
          </td>

          <td>{{ $r['ip_origen'] ?? '—' }}</td>

          {{-- Navegador: en General normalmente vendrá vacío, se muestra "—" --}}
          <td title="{{ $r['navegador'] ?? '' }}">
            {{ $r['navegador'] ? \Illuminate\Support\Str::limit($r['navegador'], 40) : '—' }}
          </td>
        </tr>
      @empty
        <tr>
          {{-- 👇 ahora siempre 8 columnas, iguales en ambas pestañas --}}
          <td colspan="8" class="text-center sin-registros">
            No hay registros en la bitácora con los filtros actuales.
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

{{-- Paginación AJAX (botones, no enlaces) --}}
@if (!empty($meta) && ($meta['last_page'] ?? 1) > 1)
  @php
    $paginaActual = (int)($meta['page'] ?? 1);
    $ultimaPagina = (int)($meta['last_page'] ?? 1);
  @endphp

  <div class="paginacion-wrapper">
    {{-- Anterior --}}
    @if ($paginaActual > 1)
      <button type="button" class="page-btn" data-page="{{ $paginaActual - 1 }}">‹</button>
    @else
      <span class="page-btn disabled">‹</span>
    @endif

    {{-- Números --}}
    @for ($i = 1; $i <= $ultimaPagina; $i++)
      @if ($i == $paginaActual)
        <span class="page-btn active">{{ $i }}</span>
      @else
        <button type="button" class="page-btn" data-page="{{ $i }}">{{ $i }}</button>
      @endif
    @endfor

    {{-- Siguiente --}}
    @if ($paginaActual < $ultimaPagina)
      <button type="button" class="page-btn" data-page="{{ $paginaActual + 1 }}">›</button>
    @else
      <span class="page-btn disabled">›</span>
    @endif
  </div>
@endif
