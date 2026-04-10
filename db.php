<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/security_headers.php';

$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conexion->connect_error) {
    // Si la conexión falla, mostramos la página de error 500
    include_once __DIR__ . '/500.php';
    exit;
}

$conexion->set_charset(DB_CHARSET);
?>