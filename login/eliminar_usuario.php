<?php require_once __DIR__ . '/proteger.php'; ?>
<?php
session_start();
include '../db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    echo json_encode(['ok' => false, 'error' => 'No autorizado']);
    exit;
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    if ($id == $_SESSION['id_usuario']) {
        echo json_encode(['ok' => false, 'error' => 'No puedes eliminar tu propio usuario.']);
        exit;
    }

    $stmt = $conexion->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
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