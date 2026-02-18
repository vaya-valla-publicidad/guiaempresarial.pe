<?php
$conexion = new mysqli("localhost", "root", "", "guia_empresarial");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
?>
