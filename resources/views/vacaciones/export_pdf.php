<?php
require_once __DIR__.'/../config/conexion.php';

$mes = isset($_GET['mes']) ? intval($_GET['mes']) : intval(date('n'));
$anio = isset($_GET['anio']) ? intval($_GET['anio']) : intval(date('Y'));
$cod_empleado = isset($_GET['cod_empleado']) ? intval($_GET['cod_empleado']) : 0;

$params = [$anio, $mes];
$condEmpleado = "";
if ($cod_empleado > 0) { $condEmpleado = "AND v.cod_empleado = $3"; $params[] = $cod_empleado; }

$sql = "
SELECT
  p.nombre_completo AS empleado,
  v.fecha_inicio,
  v.fecha_fin,
  v.dias_solicitados,
  v.estado,
  v.comentario
FROM vacaciones v
JOIN empleados e ON e.cod_empleado = v.cod_empleado
JOIN personas p  ON p.cod_persona  = e.cod_persona
WHERE EXTRACT(YEAR FROM v.fecha_inicio) = $1
  AND EXTRACT(MONTH FROM v.fecha_inicio) = $2
  $condEmpleado
ORDER BY p.nombre_completo, v.fecha_inicio
";
$res = q($sql, $params);

$html = '<h3 style="font-family:Arial;margin:0 0 10px">Reporte de Vacaciones</h3>';
$html .= '<p style="font-family:Arial">Mes: '.htmlspecialchars((string)$mes).' / Año: '.htmlspecialchars((string)$anio).'</p>';
$html .= '<table width="100%" border="1" cellspacing="0" cellpadding="6" style="border-collapse:collapse;font-family:Arial;font-size:12px">';
$html .= '<tr style="background:#0d6efd;color:white"><th>Empleado</th><th>Inicio</th><th>Fin</th><th>Días</th><th>Estado</th><th>Comentario</th></tr>';

while ($r = pg_fetch_assoc($res)) {
    $html .= '<tr>'
          .  '<td>'.htmlspecialchars($r['empleado']).'</td>'
          .  '<td>'.$r['fecha_inicio'].'</td>'
          .  '<td>'.$r['fecha_fin'].'</td>'
          .  '<td>'.$r['dias_solicitados'].'</td>'
          .  '<td>'.$r['estado'].'</td>'
          .  '<td>'.htmlspecialchars($r['comentario']).'</td>'
          .  '</tr>';
}
$html .= '</table>';

// Si está Dompdf instalado con Composer, funcionará; si no, imprime HTML
if (class_exists('\\Dompdf\\Dompdf')) {
    $dompdf = new Dompdf\Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream('vacaciones_'.$anio.'_'.$mes.'.pdf');
} else {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html><head><meta charset="utf-8"><title>Reporte Vacaciones</title></head><body>'.$html.'<script>window.print()</script></body></html>';
}