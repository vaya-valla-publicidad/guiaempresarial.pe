<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/security_headers.php';

$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conexion->connect_error) {
    error_log('Error de conexión a la base de datos: ' . $conexion->connect_error);
    include_once __DIR__ . '/500.php';
    exit;
}

if (!$conexion->set_charset(DB_CHARSET)) {
    error_log('No se pudo establecer el charset de la base de datos: ' . $conexion->error);
}
?>