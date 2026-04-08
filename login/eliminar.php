<?php
require_once __DIR__ . '/proteger.php';
include '../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['admin', 'editor'])) {
    echo json_encode(['ok' => false, 'error' => 'No autorizado']);
    exit;
}

if (!validarCSRF()) {
    echo json_encode(['ok' => false, 'error' => 'Token CSRF inválido']);
    exit;
}

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $stmt = $conexion->prepare("DELETE FROM empresas WHERE id_empresa = ?");
    $stmt->bind_param("i", $id);

    if (!$stmt->execute()) {
        echo json_encode(['ok' => false, 'error' => 'Error al eliminar: ' . $stmt->error]);
        exit;
    }
    $stmt->close();
    echo json_encode(['ok' => true]);
    exit;
}
echo json_encode(['ok' => false, 'error' => 'No se proporcionó ID']);