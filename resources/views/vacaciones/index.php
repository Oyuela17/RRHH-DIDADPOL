<?php
require_once __DIR__.'/../config/conexion.php';

// (opcional) meses en español
setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'es');

// Cargar empleados (cod_empleado + nombre_completo)
$empleados = [];
$re = q("
  SELECT e.cod_empleado, p.nombre_completo
  FROM empleados e
  JOIN personas p ON p.cod_persona = e.cod_persona
  ORDER BY p.nombre_completo
");
while ($row = pg_fetch_assoc($re)) { $empleados[] = $row; }

$mes = isset($_GET['mes']) ? intval($_GET['mes']) : intval(date('n'));
$anio = isset($_GET['anio']) ? intval($_GET['anio']) : intval(date('Y'));
$cod_empleado = isset($_GET['cod_empleado']) ? intval($_GET['cod_empleado']) : 0;

function dias_del_mes($mes, $anio) { return cal_days_in_month(CAL_GREGORIAN, $mes, $anio); }
$dias_mes = dias_del_mes($mes, $anio);

// Obtener vacaciones del mes seleccionado (incluye cruces)
$params = [$anio, $mes];
$condEmpleado = "";
if ($cod_empleado > 0) { $condEmpleado = "AND v.cod_empleado = $3"; $params[] = $cod_empleado; }

$sql = "
SELECT v.id, v.cod_empleado, p.nombre_completo,
       v.fecha_inicio, v.fecha_fin, v.estado
FROM vacaciones v
JOIN empleados e ON e.cod_empleado = v.cod_empleado
JOIN personas p  ON p.cod_persona  = e.cod_persona
WHERE EXTRACT(YEAR FROM v.fecha_inicio) <= $1
  AND EXTRACT(YEAR FROM v.fecha_fin)   >= $1
  AND (
       EXTRACT(MONTH FROM v.fecha_inicio) = $2
    OR EXTRACT(MONTH FROM v.fecha_fin)    = $2
    OR (v.fecha_inicio <= make_date($1,$2,1)
        AND v.fecha_fin >= make_date($1,$2,$$DIAFIN$$))
  )
  $condEmpleado
ORDER BY p.nombre_completo, v.fecha_inicio
";
$sql = str_replace('$$DIAFIN$$', $dias_mes, $sql);
$res = q($sql, $params);

// Mapear: cod_empleado -> día -> estado
$map = [];
while ($r = pg_fetch_assoc($res)) {
    $start  = new DateTime($r['fecha_inicio']);
    $end    = new DateTime($r['fecha_fin']);
    $cursor = clone $start;
    while ($cursor <= $end) {
        $m = intval($cursor->format('n'));
        $y = intval($cursor->format('Y'));
        if ($m == $mes && $y == $anio) {
            $d = intval($cursor->format('j'));
            $map[$r['cod_empleado']]['nombre'] = $r['nombre_completo'];
            $map[$r['cod_empleado']]['dias'][$d] = $r['estado'];
        }
        $cursor->modify('+1 day');
    }
}

// Resumen (saldo/tomados) simple
$saldo = null;
$dias_tomados = 0;
if ($cod_empleado > 0) {
  $rs = q("SELECT dias_disponibles, dias_tomados
           FROM saldo_vacaciones
           WHERE cod_empleado=$1 AND anio=$2", [$cod_empleado, $anio]);
  if ($r = pg_fetch_assoc($rs)) { $saldo = intval($r['dias_disponibles']); $dias_tomados = intval($r['dias_tomados']); }
}
foreach ($map as $info) {
  foreach (($info['dias'] ?? []) as $estado) {
    if (strtoupper($estado) === 'APROBADA') $dias_tomados++;
  }
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Control de Vacaciones</title>

<style>
/* ====== Base y layout (sin Bootstrap) ====== */
*{box-sizing:border-box}
body{margin:0;background:#f3f5f7;color:#1f2937;font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,'Helvetica Neue',Arial,sans-serif}
.container-fluid{max-width:1200px;margin:0 auto;padding:24px}
.row{display:flex;flex-wrap:wrap;gap:12px}
.col{flex:1 1 0}
.col-md-4{flex:1 1 360px}
.col-md-3{flex:1 1 260px}
.col-md-2{flex:1 1 180px}
.text-end{text-align:right}

/* ====== Cards y wrappers ====== */
.rounded{border-radius:14px}
.shadow-sm{box-shadow:0 4px 16px rgba(0,0,0,.06)}
.bg-white{background:#fff}
.p-3{padding:12px}
.p-4{padding:16px}

/* ====== Controles ====== */
label{font-size:.9rem;color:#6b7280;margin-bottom:6px;display:block}
.form-label{font-size:.9rem;color:#6b7280;margin-bottom:6px;display:block}
.form-select,.form-control,select,input[type="date"],textarea{
  width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:8px;background:#fff;font:inherit
}
textarea{resize:vertical}

/* ====== Botones ====== */
.btn{display:inline-flex;align-items:center;gap:8px;border:0;border-radius:8px;padding:10px 16px;font-weight:600;cursor:pointer;transition:.15s}
.btn-primary{background:#0ea5e9;color:#fff}.btn-primary:hover{background:#0284c7}
.btn-danger{background:#ef4444;color:#fff}.btn-danger:hover{background:#dc2626}
.btn-success{background:#22c55e;color:#fff}.btn-success:hover{background:#16a34a}

/* ====== Tabla calendario ====== */
.table-responsive{overflow:auto}
.table{width:100%;border-collapse:collapse;font-size:13px}
.table thead th{position:sticky;top:0;background:#0d47a1;color:#fff;padding:10px 6px}
.table tbody td,.table tbody th{border-top:1px solid #eef2f7;padding:8px 6px;background:#fff}
.table tbody tr:hover td{background:#f7fbff}
.table th:first-child,.table td:first-child{position:sticky;left:0;background:inherit}
.table th:first-child{min-width:220px;text-align:left}
.table td:first-child{font-weight:600;color:#0b2e4d}

/* Dots */
.badge-dot{width:10px;height:10px;display:inline-block;border-radius:50%}
.dot-ap{background:#22c55e}.dot-pe{background:#f59e0b}.dot-re{background:#ef4444}

/* ====== Modal propio (sin Bootstrap) ====== */
.nb-modal{position:fixed;inset:0;display:none;z-index:2000}
.nb-modal.open{display:flex;align-items:center;justify-content:center;padding:16px}
.nb-backdrop{position:absolute;inset:0;background:rgba(0,0,0,.45)}
.nb-dialog{position:relative;background:#fff;border-radius:14px;width:min(640px,95vw);box-shadow:0 20px 60px rgba(0,0,0,.25);overflow:hidden}
.nb-header,.nb-footer{padding:12px 16px;background:#f8f9fa;display:flex;align-items:center;justify-content:space-between}
.nb-body{padding:16px}
.nb-title{margin:0;font-weight:700}
.nb-close{background:none;border:0;font-size:24px;line-height:1;cursor:pointer}
body.modal-open{overflow:hidden}
</style>
</head>
<body class="bg-light">
<div class="container-fluid p-4">

  <div class="row mb-3">
    <div class="col">
      <h3 style="margin:0;font-weight:700">CONTROL DE VACACIONES DEL PERSONAL</h3>
    </div>
  </div>

  <!-- Filtros (form corregido abarcando toda la fila) -->
  <form id="filtro" method="GET" class="row align-items-end mb-3">
    <div class="col-md-4">
      <label class="form-label">Empleado</label>
      <select class="form-select" name="cod_empleado" onchange="this.form.submit()">
        <option value="0">Todos</option>
        <?php foreach ($empleados as $e): ?>
          <option value="<?= $e['cod_empleado'] ?>" <?= $cod_empleado==$e['cod_empleado']?'selected':'' ?>>
            <?= htmlspecialchars($e['nombre_completo']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Mes</label>
      <select class="form-select" name="mes" onchange="this.form.submit()">
        <?php for ($m=1;$m<=12;$m++): ?>
          <option value="<?=$m?>" <?= $mes==$m?'selected':'' ?>>
            <?= ucfirst(strftime('%B', mktime(0,0,0,$m,1))) ?>
          </option>
        <?php endfor; ?>
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label">Año</label>
      <select class="form-select" name="anio" onchange="this.form.submit()">
        <?php for ($y=date('Y')-2;$y<=date('Y')+2;$y++): ?>
          <option value="<?=$y?>" <?= $anio==$y?'selected':'' ?>><?=$y?></option>
        <?php endfor; ?>
      </select>
    </div>
    <div class="col-md-3" style="text-align:right">
      <button type="button" class="btn btn-primary" id="btnNuevaSolicitud">Nueva Solicitud</button>
      <a class="btn btn-danger" href="export_pdf.php?mes=<?=$mes?>&anio=<?=$anio?>&cod_empleado=<?=$cod_empleado?>">PDF</a>
      <a class="btn btn-success" href="export_excel.php?mes=<?=$mes?>&anio=<?=$anio?>&cod_empleado=<?=$cod_empleado?>">Excel</a>
    </div>
  </form>

  <div class="row mb-3">
    <div class="col-md-3"><div class="bg-white rounded shadow-sm p-3">Saldo: <strong><?= $saldo===null? "—" : $saldo ?></strong> días</div></div>
    <div class="col-md-3"><div class="bg-white rounded shadow-sm p-3">Días tomados (año): <strong><?= $dias_tomados ?></strong></div></div>
  </div>

  <div class="table-responsive bg-white rounded shadow-sm">
    <table class="table align-middle table-sticky">
      <thead>
        <tr>
          <th>Empleado</th>
          <?php for ($d=1; $d<=$dias_mes; $d++): ?>
            <th class="text-center"><?=$d?></th>
          <?php endfor; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($empleados as $e):
            if ($cod_empleado>0 && $cod_empleado!=$e['cod_empleado']) continue;
            $dias = $map[$e['cod_empleado']]['dias'] ?? [];
        ?>
        <tr>
          <td><?= htmlspecialchars($e['nombre_completo']) ?></td>
          <?php for ($d=1; $d<=$dias_mes; $d++):
            $estado = strtoupper($dias[$d] ?? '');
            $cls = $estado==='APROBADA' ? 'dot-ap' : ($estado==='PENDIENTE' ? 'dot-pe' : ($estado==='RECHAZADA' ? 'dot-re' : ''));
          ?>
            <td class="text-center"><?php if ($cls): ?><span class="badge-dot <?= $cls ?>"></span><?php endif; ?></td>
          <?php endfor; ?>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="mt-3">
    <span class="me-3"><span class="badge-dot dot-ap"></span> Aprobada</span>
    <span class="me-3"><span class="badge-dot dot-pe"></span> Pendiente</span>
    <span class="me-3"><span class="badge-dot dot-re"></span> Rechazada</span>
  </div>
</div>

<!-- Modal propio (sin Bootstrap) -->
<div class="nb-modal" id="nuevaSolicitud" aria-hidden="true">
  <div class="nb-backdrop" data-close></div>

  <div class="nb-dialog" role="dialog" aria-modal="true" aria-labelledby="nbModalTitle">
    <div class="nb-header">
      <h5 class="nb-title" id="nbModalTitle">Nueva Solicitud de Vacaciones</h5>
      <button type="button" class="nb-close" aria-label="Cerrar" data-close>&times;</button>
    </div>

    <form class="nb-body" method="POST" action="guardar_vacacion.php">
      <div class="row" style="gap:12px">
        <div class="col">
          <label class="form-label">Empleado</label>
          <select class="form-select" name="cod_empleado" required>
            <?php foreach ($empleados as $e): ?>
              <option value="<?= $e['cod_empleado'] ?>"><?= htmlspecialchars($e['nombre_completo']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="row" style="gap:12px;margin-top:8px">
        <div class="col">
          <label class="form-label">Inicio</label>
          <input type="date" name="fecha_inicio" class="form-control" required>
        </div>
        <div class="col">
          <label class="form-label">Fin</label>
          <input type="date" name="fecha_fin" class="form-control" required>
        </div>
      </div>

      <div style="margin-top:8px">
        <label class="form-label">Comentario</label>
        <textarea name="comentario" class="form-control" rows="2"></textarea>
      </div>

      <div class="nb-footer">
        <button class="btn btn-success" type="submit">Enviar</button>
        <button class="btn btn-danger" type="button" data-close>Cancelar</button>
      </div>
    </form>
  </div>
</div>

<script>
// Modal sin Bootstrap
document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('nuevaSolicitud');
  const btnOpen = document.getElementById('btnNuevaSolicitud');

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
    btnOpen?.focus();
  };

  btnOpen?.addEventListener('click', (e) => { e.preventDefault(); openModal(); });
  modal.addEventListener('click', (e) => {
    if (e.target.dataset.close !== undefined || e.target === modal.querySelector('.nb-backdrop')) closeModal();
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modal.classList.contains('open')) closeModal();
  });
});
</script>
</body>
</html>
