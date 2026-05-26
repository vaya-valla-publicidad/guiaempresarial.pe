<?php
include __DIR__ . '/../db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false]);
    exit;
}

$id = intval($_POST['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['ok' => false]);
    exit;
}

$stmt = $conexion->prepare("UPDATE burbujas_busqueda SET clics = clics + 1 WHERE id_burbuja = ? AND activo = 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

echo json_encode(['ok' => true]);
