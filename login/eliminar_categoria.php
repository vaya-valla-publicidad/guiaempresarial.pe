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

    $stmt_check = $conexion->prepare("SELECT COUNT(*) as total FROM empresas WHERE id_categoria = ?");
    $stmt_check->bind_param("i", $id);
    $stmt_check->execute();
    $total = $stmt_check->get_result()->fetch_assoc()['total'];
    $stmt_check->close();

    if ($total > 0) {
        echo json_encode(['ok' => false, 'error' => "No se puede eliminar: hay $total empresa(s) usando esta categoría."]);
        exit;
    }

    $stmt = $conexion->prepare("DELETE FROM categorias WHERE id_categoria = ?");
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