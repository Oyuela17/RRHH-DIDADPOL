<?php
// config/conexion.php
// Ajusta estos parámetros a tu entorno
$PG_HOST = getenv('PG_HOST') ?: 'localhost';
$PG_DB   = getenv('PG_DB')   ?: 'RRHH-DIDADPOL';
$PG_USER = getenv('PG_USER') ?: 'postgres';
$PG_PASS = getenv('PG_PASS') ?: 'Didadpol';
$PG_PORT = getenv('PG_PORT') ?: '5432';

$conn = pg_connect("host={$PG_HOST} dbname={$PG_DB} user={$PG_USER} password={$PG_PASS} port={$PG_PORT}");
if (!$conn) {
    die('Error de conexión a PostgreSQL');
}

function q($sql, $params = []) {
    global $conn;
    $res = pg_query_params($conn, $sql, $params);
    if (!$res) {
        http_response_code(500);
        die(pg_last_error($conn));
    }
    return $res;
}
?>
