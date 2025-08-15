<?php
require_once __DIR__.'/../config/conexion.php';

$id = intval($_POST['id'] ?? 0);
$estado = $_POST['estado'] ?? '';
$validos = ['PENDIENTE','APROBADA','RECHAZADA','CANCELADA'];
if (!$id || !in_array($estado, $validos)) { die('Datos inválidos'); }

// Obtener solicitud
$sol = q("SELECT * FROM vacaciones WHERE id=$1", [$id]);
if (pg_num_rows($sol) == 0) { die('No existe la solicitud'); }
$vac = pg_fetch_assoc($sol);

// Actualizar estado
q("UPDATE vacaciones SET estado=$1 WHERE id=$2", [$estado, $id]);

// Si es aprobada: descontar saldo y registrar asistencia
if ($estado == 'APROBADA') {
    $anio = intval(date('Y', strtotime($vac['fecha_inicio'])));
    $dias = intval($vac['dias_solicitados']);

    // Actualizar o crear saldo
    $saldoRes = q("SELECT * FROM saldo_vacaciones WHERE cod_empleado=$1 AND anio=$2", [$vac['cod_empleado'], $anio]);
    if (pg_num_rows($saldoRes) > 0) {
        q("UPDATE saldo_vacaciones SET dias_disponibles = dias_disponibles - $1, dias_tomados = dias_tomados + $1
           WHERE cod_empleado=$2 AND anio=$3", [$dias, $vac['cod_empleado'], $anio]);
    } else {
        q("INSERT INTO saldo_vacaciones (cod_empleado, anio, dias_disponibles, dias_tomados) VALUES ($1,$2,$3,$4)",
          [$vac['cod_empleado'], $anio, 15 - $dias, $dias]);
    }

    // Insertar registros en asistencia
    $fi = new DateTime($vac['fecha_inicio']);
    $ff = new DateTime($vac['fecha_fin']);
    while ($fi <= $ff) {
        q("INSERT INTO asistencia (cod_empleado, fecha, estado) VALUES ($1, $2, 'Vacaciones')",
          [$vac['cod_empleado'], $fi->format('Y-m-d')]);
        $fi->modify('+1 day');
    }
}

echo 'ok';
?>
