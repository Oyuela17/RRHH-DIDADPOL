<?php
require_once __DIR__.'/../config/conexion.php';

$cod_empleado = intval($_POST['cod_empleado'] ?? 0);
$fi = $_POST['fecha_inicio'] ?? null;
$ff = $_POST['fecha_fin'] ?? null;
$comentario = $_POST['comentario'] ?? '';

if (!$cod_empleado || !$fi || !$ff) { die('Datos incompletos'); }

// Validar que fin >= inicio
if (strtotime($ff) < strtotime($fi)) { die('Rango de fechas inválido'); }

// Revisa solapamiento con vacaciones existentes del mismo empleado
$solape = q("
  SELECT 1
  FROM vacaciones
  WHERE cod_empleado = $1
    AND NOT ($3 < fecha_inicio OR $2 > fecha_fin)
  LIMIT 1
", [$cod_empleado, $fi, $ff]);

if (pg_num_rows($solape) > 0) {
  die('El empleado ya tiene vacaciones en ese rango.');
}

// Insertar
q("INSERT INTO vacaciones (cod_empleado, fecha_inicio, fecha_fin, comentario)
   VALUES ($1, $2, $3, $4)",
  [$cod_empleado, $fi, $ff, $comentario]);

header('Location: index.php?msg=ok');
exit;
