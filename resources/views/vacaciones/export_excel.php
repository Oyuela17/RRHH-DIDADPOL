<?php
require_once __DIR__.'/../config/conexion.php';

$mes = isset($_GET['mes']) ? intval($_GET['mes']) : intval(date('n'));
$anio = isset($_GET['anio']) ? intval($_GET['anio']) : intval(date('Y'));
$cod_empleado = isset($_GET['cod_empleado']) ? intval($_GET['cod_empleado']) : 0;

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="vacaciones_'.$anio.'_'.$mes.'.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, ['Empleado','Inicio','Fin','Días','Estado','Comentario']);

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

while ($r = pg_fetch_assoc($res)) {
    fputcsv($out, [
        $r['empleado'],
        $r['fecha_inicio'],
        $r['fecha_fin'],
        $r['dias_solicitados'],
        $r['estado'],
        $r['comentario']
    ]);
}
fclose($out);
