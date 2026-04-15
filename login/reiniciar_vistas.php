<?php
require_once __DIR__ . '/proteger.php';
include '../db.php';

if ($_SESSION['rol'] !== 'admin') {
    die("No tienes permisos para realizar esta acción.");
}

$id = isset($_GET['id']) ? intval($_GET['id']) : null;

if ($id) {
    $stmt = $conexion->prepare("UPDATE empresas SET vistas = 0 WHERE id_empresa = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
} else {
    $conexion->query("UPDATE empresas SET vistas = 0");
}

header("Location: admin.php?views_reset=1");
exit;
?>
