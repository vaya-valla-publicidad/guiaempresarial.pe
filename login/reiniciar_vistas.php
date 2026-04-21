<?php
require_once __DIR__ . '/proteger.php';
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validarCSRF($_POST['csrf_token'] ?? '')) {
    header('Content-Type: application/json');
    echo json_encode(['ok' => false, 'error' => 'CSRF Inválido o Método no permitido']);
    exit;
}

if ($_SESSION['rol'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['ok' => false, 'error' => 'No tienes permisos para realizar esta acción.']);
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : null;

if ($id) {
    $stmt = $conexion->prepare("UPDATE empresas SET vistas = 0 WHERE id_empresa = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
} else {
    $conexion->query("UPDATE empresas SET vistas = 0");
}

header('Content-Type: application/json');
echo json_encode(['ok' => true]);
exit;
?>