@extends('layouts.dashboard')
@section('title', 'Control de Asistencia (Admin)')

<link rel="stylesheet" href="{{ asset('css/asistencia_admin.css') }}">
@section('content')

<div class="asistencia-admin-wrapper">
  <div class="titulo-con-linea">
    <h2>CONTROL DE ASISTENCIA DEL PERSONAL</h2>
  </div>

  <div class="filtros-asistencia">
    <form method="GET" action="{{ route('control_asistencia.admin') }}" class="formulario-filtros" id="filtro-form">
      <input type="text" name="nombre" id="input-nombre" placeholder="Nombre del empleado" value="{{ request('nombre') }}">

      <select name="mes" id="mes">
        @foreach(range(1, 12) as $m)
          <option value="{{ $m }}" {{ request('mes', now()->month) == $m ? 'selected' : '' }}>
            {{ \Carbon\Carbon::create()->month($m)->locale('es')->isoFormat('MMMM') }}
          </option>
        @endforeach
      </select>

      <select name="anio" id="anio">
        @for ($y = now()->year; $y >= 2020; $y--)
          <option value="{{ $y }}" {{ request('anio', now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
        @endfor
      </select>

      <button type="submit" class="btn-buscar-asistencia">🔍 Buscar</button>

      <a id="btnExportarPDF" class="btn-exportar pdf" target="_blank">
        <i class="fas fa-file-pdf"></i> PDF
      </a>
      <a id="btnExportarExcel" class="btn-exportar excel" target="_blank">
        <i class="fas fa-file-excel"></i> Excel
      </a>
    </form>
  </div>

  <div class="tabla-asistencia">
    <table>
      <thead>
        <tr>
          <th>Empleado</th>
          @for ($d = 1; $d <= $dias; $d++)
            <th>{{ $d }}</th>
          @endfor
        </tr>
      </thead>
      <tbody id="tabla-empleados">
        @foreach ($empleados as $emp)
          <tr class="fila-empleado">
            <td class="nombre-empleado">{{ $emp['nombre'] }}</td>
            @for ($d = 1; $d <= $dias; $d++)
              @php
                $fechaActual = \Carbon\Carbon::create($anio, $mes, $d)->toDateString();
                $registro = collect($emp['registros'])->firstWhere('fecha', $fechaActual);
                $clase = 'rojo';
                if ($registro) {
                  switch(strtoupper($registro['observacion'])) {
                    case 'EXTRA': $clase = 'extra'; break;
                    case 'INCOMPLETA': $clase = 'incompleta'; break;
                    case 'ASISTENCIA':
                    default: $clase = 'verde'; break;
                  }
                }
              @endphp
              <td class="icono-dia">
                @if ($registro)
                  <i class="fas fa-check-circle {{ $clase }}"
                     onmouseenter="mostrarModal(event, '{{ $emp['nombre'] }}','{{ $fechaActual }}','{{ $registro['hora_entrada'] }}','{{ $registro['hora_salida'] }}','{{ $registro['observacion'] }}')"
                     onmouseleave="ocultarModal()"></i>
                @else
                  <i class="fas fa-times-circle rojo"
                     onmouseenter="mostrarModal(event, '{{ $emp['nombre'] }}','{{ $fechaActual }}','','','Sin asistencia')"
                     onmouseleave="ocultarModal()"></i>
                @endif
              </td>
            @endfor
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <!-- Modal flotante -->
  <div id="modal-asistencia" class="modal-asistencia" style="display: none;">
    <h4 id="modal-nombre"></h4>
    <p><strong>Fecha:</strong> <span id="modal-fecha"></span></p>
    <p><strong>Hora Entrada:</strong> <span id="modal-entrada"></span></p>
    <p><strong>Hora Salida:</strong> <span id="modal-salida"></span></p>
    <p><strong>Observación:</strong> <span id="modal-observacion"></span></p>
  </div>
</div>

<script>
  document.getElementById('input-nombre').addEventListener('input', function () {
    const valor = this.value.toLowerCase();
    document.querySelectorAll('.fila-empleado').forEach(fila => {
      const nombre = fila.querySelector('.nombre-empleado').textContent.toLowerCase();
      fila.style.display = nombre.includes(valor) ? '' : 'none';
    });
  });

  function formatearHora(hora) {
    if (!hora) return '-';
    const [h, m, s] = hora.split(':');
    const date = new Date();
    date.setHours(h, m, s);
    return date.toLocaleTimeString('es-HN', {
      hour: 'numeric',
      minute: '2-digit',
      hour12: true
    });
  }

  function mostrarModal(event, nombre, fecha, entrada, salida, observacion) {
    const modal = document.getElementById('modal-asistencia');
    const windowWidth = window.innerWidth;

    document.getElementById('modal-nombre').textContent = nombre;
    document.getElementById('modal-fecha').textContent = fecha;
    document.getElementById('modal-entrada').textContent = formatearHora(entrada);
    document.getElementById('modal-salida').textContent = formatearHora(salida);
    document.getElementById('modal-observacion').textContent = observacion;

    const modalWidth = 250;
    const leftPos = (windowWidth - event.clientX < modalWidth + 20)
      ? event.clientX - modalWidth - 10
      : event.clientX + 15;

    modal.style.top = (event.clientY + 15) + 'px';
    modal.style.left = leftPos + 'px';
    modal.style.display = 'block';
  }

  function ocultarModal() {
    document.getElementById('modal-asistencia').style.display = 'none';
  }

  document.addEventListener('DOMContentLoaded', () => {
    const inputNombre = document.getElementById('input-nombre');
    const selectMes = document.getElementById('mes');
    const selectAnio = document.getElementById('anio');
    const btnPDF = document.getElementById('btnExportarPDF');
    const btnExcel = document.getElementById('btnExportarExcel');

    function construirURL(base) {
      const nombre = encodeURIComponent(inputNombre.value || '');
      const mes = selectMes.value;
      const anio = selectAnio.value;
      return `${base}?mes=${mes}&anio=${anio}&nombre=${nombre}`;
    }

    btnPDF.href = construirURL('/control-asistencia/export/pdf');
    btnExcel.href = construirURL('{{ route('asistencia.export.excel') }}');

    [inputNombre, selectMes, selectAnio].forEach(input => {
      input.addEventListener('input', () => {
        btnPDF.href = construirURL('/control-asistencia/export/pdf');
        btnExcel.href = construirURL('{{ route('asistencia.export.excel') }}');
      });
    });
  });
</script>

@endsection
