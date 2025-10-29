@extends('layouts.dashboard')
@section('title', 'Reportes Generales')

@section('content')
<link rel="stylesheet" href="{{ asset('css/reportes.css') }}">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<div id="reportes-app" class="reportes-wrapper">
  <div class="tabs-container">
    <button class="tab-btn active" data-tab="empleados">Empleados</button>
    <button class="tab-btn" data-tab="asistencia">Asistencia</button>
  </div>

  {{-- ==================== PESTAÑA EMPLEADOS ==================== --}}
  <div class="tab-content active" id="tab-empleados">
    <div class="titulo-seccion-row">
      <div class="titulo-seccion">Reporte General de Empleados</div>
      <div class="acciones-exportar">
        <span>Descargar:</span>
        <a class="btn-export html"
           href="{{ route('reportes.exportar', ['tipo'=>'empleados','formato'=>'html']) }}"
           target="_blank">
          <i class="fa-solid fa-file-code"></i> HTML
        </a>
        <a class="btn-export excel"
           href="{{ route('reportes.exportar', ['tipo'=>'empleados','formato'=>'excel']) }}">
          <i class="fa-solid fa-file-excel"></i> Excel
        </a>
        <a class="btn-export pdf"
           href="{{ route('reportes.exportar', ['tipo'=>'empleados','formato'=>'pdf']) }}">
          <i class="fa-solid fa-file-pdf"></i> PDF
        </a>
      </div>
    </div>

    <div id="empleados-contenido" class="contenido-caja">
      <div class="loader">Cargando datos...</div>
    </div>

    {{-- Gráficas Empleados --}}
    <div class="charts-grid" id="charts-empleados" style="display:none;">
      <div class="chart-card">
        <h5>Empleados por Oficina</h5>
        <canvas id="chEmplOficina"></canvas>
      </div>
      <div class="chart-card">
        <h5>Distribución por Modalidad</h5>
        <canvas id="chEmplModalidad"></canvas>
      </div>
      <div class="chart-card">
        <h5>Distribución por Nivel Educativo</h5>
        <canvas id="chEmplNivel"></canvas>
      </div>
      <div class="chart-card">
        <h5>Top Puestos</h5>
        <canvas id="chEmplPuestos"></canvas>
      </div>
    </div>
  </div>

  {{-- ==================== PESTAÑA ASISTENCIA ==================== --}}
  <div class="tab-content" id="tab-asistencia">
    <div class="titulo-seccion-row">
      <div class="titulo-seccion">Reporte General de Asistencia</div>
      <div class="acciones-exportar">
        <span>Descargar:</span>
        {{-- estos href se actualizan por JS con ?mes=&anio= --}}
        <a id="expAsisHtml"  class="btn-export html"  href="#" target="_blank">
          <i class="fa-solid fa-file-code"></i> HTML
        </a>
        <a id="expAsisExcel" class="btn-export excel" href="#">
          <i class="fa-solid fa-file-excel"></i> Excel
        </a>
        <a id="expAsisPdf"   class="btn-export pdf"   href="#">
          <i class="fa-solid fa-file-pdf"></i> PDF
        </a>
      </div>
    </div>

    <div class="filtros-linea">
      <div>
        <label>Mes</label>
        <select id="mesSelect" class="inp">
          <option value="1">Enero</option>
          <option value="2">Febrero</option>
          <option value="3">Marzo</option>
          <option value="4">Abril</option>
          <option value="5">Mayo</option>
          <option value="6">Junio</option>
          <option value="7">Julio</option>
          <option value="8">Agosto</option>
          <option value="9">Septiembre</option>
          <option value="10">Octubre</option>
          <option value="11">Noviembre</option>
          <option value="12">Diciembre</option>
        </select>
      </div>
      <div>
        <label>Año</label>
        <select id="anioSelect" class="inp"></select>
      </div>
      <button id="btnAplicar" class="btn-primario">
        <i class="fa-solid fa-filter"></i> Aplicar
      </button>
    </div>

    <div id="asistencia-contenido" class="contenido-caja">
      <div class="loader">Cargando datos...</div>
    </div>

    {{-- Gráficas Asistencia --}}
    <div class="charts-grid" id="charts-asistencia" style="display:none;">
      <div class="chart-card">
        <h5>Asistencia % por Oficina</h5>
        <canvas id="chAsisOficina"></canvas>
      </div>
      <div class="chart-card">
        <h5>Ranking de Ausencias (Top 12)</h5>
        <canvas id="chAsisAusencias"></canvas>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const tabs = document.querySelectorAll(".tab-btn");
  const contents = document.querySelectorAll(".tab-content");
  const apiEmpleados = "{{ route('reportes.empleados') }}";
  const apiAsistencia = "{{ route('reportes.asistencia') }}";

  // rutas base para exportar asistencia
  const expBaseHtml  = "{{ route('reportes.exportar', ['tipo'=>'asistencia','formato'=>'html']) }}";
  const expBaseExcel = "{{ route('reportes.exportar', ['tipo'=>'asistencia','formato'=>'excel']) }}";
  const expBasePdf   = "{{ route('reportes.exportar', ['tipo'=>'asistencia','formato'=>'pdf']) }}";

  // Selects mes/año
  const mesSelect  = document.getElementById('mesSelect');
  const anioSelect = document.getElementById('anioSelect');
  const btnAplicar = document.getElementById('btnAplicar');

  // Set valores por defecto
  const now = new Date();
  mesSelect.value = (now.getMonth() + 1).toString();

  // Poblar años: 2010 -> año actual (descendente)
  (function fillYears(){
    const current = now.getFullYear();
    for (let y = current; y >= 2010; y--) {
      const opt = document.createElement('option');
      opt.value = String(y);
      opt.textContent = String(y);
      anioSelect.appendChild(opt);
    }
    anioSelect.value = String(current);
  })();

  // Botones export asistencia (href se actualiza con mes/año)
  const expAsisHtml  = document.getElementById('expAsisHtml');
  const expAsisExcel = document.getElementById('expAsisExcel');
  const expAsisPdf   = document.getElementById('expAsisPdf');

  function refreshExportAsistenciaLinks() {
    const mes  = parseInt(mesSelect.value, 10);
    const anio = parseInt(anioSelect.value, 10);
    const q = `?mes=${mes}&anio=${anio}`;
    expAsisHtml.href  = `${expBaseHtml}${q}`;
    expAsisExcel.href = `${expBaseExcel}${q}`;
    expAsisPdf.href   = `${expBasePdf}${q}`;
  }

  // Cargar Empleados al inicio
  fetchReporte('empleados', apiEmpleados);

  // Cambiar tabs
  tabs.forEach(btn => {
    btn.addEventListener("click", () => {
      tabs.forEach(b => b.classList.remove("active"));
      btn.classList.add("active");
      const tab = btn.dataset.tab;

      contents.forEach(c => c.classList.remove("active"));
      document.getElementById(`tab-${tab}`).classList.add("active");

      if (tab === "empleados") {
        fetchReporte('empleados', apiEmpleados);
      } else {
        refreshExportAsistenciaLinks();
        fetchAsistencia();
      }
    });
  });

  btnAplicar.addEventListener('click', () => {
    refreshExportAsistenciaLinks();
    fetchAsistencia();
  });

  function fetchAsistencia() {
    const mes  = parseInt(mesSelect.value, 10);
    const anio = parseInt(anioSelect.value, 10);
    fetchReporte('asistencia', `${apiAsistencia}?mes=${mes}&anio=${anio}`);
  }

  // ------- Render genérico tabla + KPI ----------
  function fetchReporte(tipo, url) {
    const container = document.getElementById(`${tipo}-contenido`);
    container.innerHTML = '<div class="loader">Cargando datos...</div>';

    fetch(url)
      .then(r => r.json())
      .then(data => {
        if (data.error) throw new Error(data.error);
        container.innerHTML = renderReporte(tipo, data);

        // Graficar
        if (tipo === 'empleados') {
          renderChartsEmpleados(data);
        } else {
          renderChartsAsistencia(data);
        }
      })
      .catch(err => {
        container.innerHTML = `<div class="error-msg">⚠️ Error al cargar: ${err.message}</div>`;
      });
  }

  function renderReporte(tipo, data) {
    if (tipo === 'empleados') {
      const total   = Number(data.kpis?.total ?? 0);
      const activos = Number(data.kpis?.activos ?? 0);
      const salario = Number(data.kpis?.salario_promedio ?? 0).toLocaleString('es-HN', {minimumFractionDigits:2});
      const rows    = (data.tabla?.data ?? data.tabla ?? []);

      const tablaHtml = rows.map(e => `
        <tr>
          <td>${e.nombre_completo}</td>
          <td>${e.dni ?? '-'}</td>
          <td>${e.nombre_oficina ?? '-'}</td>
          <td>${e.puesto ?? '-'}</td>
          <td>${e.salario ?? '-'}</td>
        </tr>`).join('');

      document.getElementById('charts-empleados').style.display = 'grid';

      return `
        <div class="kpi-grid">
          <div class="kpi-card"><h4>Total</h4><p>${total}</p></div>
          <div class="kpi-card"><h4>Activos</h4><p>${activos}</p></div>
          <div class="kpi-card"><h4>Salario Promedio</h4><p>L. ${salario}</p></div>
        </div>
        <table class="tabla-reporte">
          <thead>
            <tr><th>Nombre</th><th>DNI</th><th>Oficina</th><th>Puesto</th><th>Salario</th></tr>
          </thead>
          <tbody>${tablaHtml}</tbody>
        </table>`;
    } else {
      const empleados   = Number(data.kpis?.empleados ?? 0);
      const asistencia  = Number(data.kpis?.asistencia_pct ?? 0);
      const horasTot    = Number(data.kpis?.horas_totales ?? 0);
      const rows        = (data.tabla?.data ?? data.tabla ?? []);

      const tablaHtml = rows.map(a => `
        <tr>
          <td>${a.nombre}</td>
          <td>${a.nombre_oficina}</td>
          <td>${a.puesto}</td>
          <td>${a.dias_presentes}</td>
          <td>${a.horas_mes}</td>
        </tr>`).join('');

      document.getElementById('charts-asistencia').style.display = 'grid';

      return `
        <div class="kpi-grid">
          <div class="kpi-card"><h4>Empleados</h4><p>${empleados}</p></div>
          <div class="kpi-card"><h4>Asistencia (%)</h4><p>${asistencia}%</p></div>
          <div class="kpi-card"><h4>Horas Totales</h4><p>${horasTot}</p></div>
        </div>
        <table class="tabla-reporte">
          <thead>
            <tr><th>Nombre</th><th>Oficina</th><th>Puesto</th><th>Días Presentes</th><th>Horas</th></tr>
          </thead>
          <tbody>${tablaHtml}</tbody>
        </table>`;
    }
  }

  // =============== CHARTS ===============
  const chartRefs = {};
  function destroyIfExists(key) {
    if (chartRefs[key]) {
      chartRefs[key].destroy();
      chartRefs[key] = null;
    }
  }

  function renderChartsEmpleados(data) {
    const ch = data.charts || {};
    // Empleados por oficina
    {
      const labels = (ch.por_oficina ?? []).map(x => x.nombre_oficina);
      const values = (ch.por_oficina ?? []).map(x => Number(x.total));
      destroyIfExists('empl_oficina');
      chartRefs.empl_oficina = new Chart(document.getElementById('chEmplOficina'), {
        type: 'bar',
        data: { labels, datasets: [{ label: 'Empleados', data: values }] },
        options: { responsive: true, plugins: { legend: { display: false } } }
      });
    }
    // Modalidad (dona)
    {
      const labels = (ch.por_modalidad ?? []).map(x => x.modalidad);
      const values = (ch.por_modalidad ?? []).map(x => Number(x.total));
      destroyIfExists('empl_modalidad');
      chartRefs.empl_modalidad = new Chart(document.getElementById('chEmplModalidad'), {
        type: 'doughnut',
        data: { labels, datasets: [{ data: values }] },
        options: { responsive: true }
      });
    }
    // Nivel educativo
    {
      const labels = (ch.por_nivel_educativo ?? []).map(x => x.nivel_educativo);
      const values = (ch.por_nivel_educativo ?? []).map(x => Number(x.total));
      destroyIfExists('empl_nivel');
      chartRefs.empl_nivel = new Chart(document.getElementById('chEmplNivel'), {
        type: 'bar',
        data: { labels, datasets: [{ label: 'Empleados', data: values }] },
        options: { responsive: true, plugins: { legend: { display: false } } }
      });
    }
    // Top puestos (horizontal)
    {
      const labels = (ch.top_puestos ?? []).map(x => x.puesto);
      const values = (ch.top_puestos ?? []).map(x => Number(x.total));
      destroyIfExists('empl_puestos');
      chartRefs.empl_puestos = new Chart(document.getElementById('chEmplPuestos'), {
        type: 'bar',
        data: { labels, datasets: [{ label: 'Cantidad', data: values }] },
        options: {
          indexAxis: 'y',
          responsive: true,
          plugins: { legend: { display: false } }
        }
      });
    }
  }

  function renderChartsAsistencia(data) {
    const ch = data.charts || {};
    // Asistencia por oficina
    {
      const labels = (ch.asistencia_por_oficina ?? []).map(x => x.nombre_oficina);
      const values = (ch.asistencia_por_oficina ?? []).map(x => Number(x.asistencia_pct));
      destroyIfExists('asis_oficina');
      chartRefs.asis_oficina = new Chart(document.getElementById('chAsisOficina'), {
        type: 'bar',
        data: { labels, datasets: [{ label: '% Asistencia', data: values }] },
        options: {
          responsive: true,
          plugins: { legend: { display: false } },
          scales: { y: { ticks: { callback: v => v + '%' } } }
        }
      });
    }
    // Ranking ausencias (horizontal)
    {
      const rows = (ch.ranking_ausencias ?? []);
      const labels = rows.map(x => x.nombre);
      const values = rows.map(x => Number(x.ausencias));
      destroyIfExists('asis_ausencias');
      chartRefs.asis_ausencias = new Chart(document.getElementById('chAsisAusencias'), {
        type: 'bar',
        data: { labels, datasets: [{ label: 'Ausencias', data: values }] },
        options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false } } }
      });
    }
  }

  // Inicializar export links de asistencia
  refreshExportAsistenciaLinks();
});
</script>
@endsection
