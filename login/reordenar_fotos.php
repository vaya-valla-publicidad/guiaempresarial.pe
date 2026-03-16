<?php
require_once __DIR__ . '/proteger.php';
include '../db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['orden']) || !is_array($data['orden'])) {
    echo json_encode(['ok' => false, 'error' => 'Datos inválidos']);
    exit;
}

$stmt = $conexion->prepare("UPDATE empresa_galeria SET orden=? WHERE id_foto=?");

foreach ($data['orden'] as $posicion => $id_foto) {
    $id_foto   = intval($id_foto);
    $posicion  = intval($posicion);
    $stmt->bind_param("ii", $posicion, $id_foto);
    $stmt->execute();
}

$stmt->close();
echo json_encode(['ok' => true]);