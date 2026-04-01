<?php
require_once __DIR__ . '/includes/security_headers.php';

$conexion = new mysqli("localhost", "root", "", "guia_empresarial");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$conexion->set_charset("utf8mb4");
?>