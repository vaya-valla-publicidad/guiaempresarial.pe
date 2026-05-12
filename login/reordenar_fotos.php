<?php
require_once __DIR__ . '/proteger.php';
include '../db.php';

header('Content-Type: application/json');

$token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!hash_equals($_SESSION['csrf_token'], $token)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Token inválido']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['orden']) || !is_array($data['orden'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Datos inválidos']);
    exit;
}

$orden = array_map('intval', $data['orden']);

$stmt = $conexion->prepare("UPDATE empresa_galeria SET orden = ? WHERE id_foto = ?");
$check = $conexion->prepare("SELECT id_foto FROM empresa_galeria WHERE id_foto = ? LIMIT 1");
foreach ($orden as $posicion => $id_foto) {
    if ($id_foto <= 0) continue;
    $check->bind_param("i", $id_foto);
    $check->execute();
    if ($check->get_result()->num_rows === 0) continue;

    $pos = intval($posicion) + 1;
    $stmt->bind_param("ii", $pos, $id_foto);
    $stmt->execute();
}
$check->close();

$stmt->close();
echo json_encode(['ok' => true]);