<?php
if (empty($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    echo json_encode(['ok' => false, 'error' => 'Sin permisos']);
    exit;
}

include __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/security.php';

if (!validarCSRF($_POST['csrf_token'] ?? '')) {
    echo json_encode(['ok' => false, 'error' => 'Error de seguridad CSRF']);
    exit;
}

$id = intval($_POST['id'] ?? 0);
$dir = $_POST['dir'] ?? '';

if (!$id || !in_array($dir, ['subir', 'bajar'])) {
    echo json_encode(['ok' => false, 'error' => 'Datos inválidos']);
    exit;
}

$stmt = $conexion->prepare("SELECT id_categoria, orden FROM categorias WHERE id_categoria = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$actual = $stmt->get_result()->fetch_assoc();

if (!$actual) {
    echo json_encode(['ok' => false, 'error' => 'Categoría no encontrada']);
    exit;
}

$orden_actual = $actual['orden'];

if ($dir === 'subir') {
    $vecino = $conexion->prepare("SELECT id_categoria, orden FROM categorias WHERE orden < ? ORDER BY orden DESC LIMIT 1");
} else {
    $vecino = $conexion->prepare("SELECT id_categoria, orden FROM categorias WHERE orden > ? ORDER BY orden ASC LIMIT 1");
}
$vecino->bind_param("i", $orden_actual);
$vecino->execute();
$otro = $vecino->get_result()->fetch_assoc();

if (!$otro) {
    echo json_encode(['ok' => false, 'error' => 'Ya está en el límite']);
    exit;
}

$conexion->begin_transaction();
try {
    $s1 = $conexion->prepare("UPDATE categorias SET orden = ? WHERE id_categoria = ?");
    $s1->bind_param("ii", $otro['orden'], $actual['id_categoria']);
    $s1->execute();

    $s2 = $conexion->prepare("UPDATE categorias SET orden = ? WHERE id_categoria = ?");
    $s2->bind_param("ii", $orden_actual, $otro['id_categoria']);
    $s2->execute();

    $conexion->commit();
    echo json_encode(['ok' => true]);
} catch (Exception $e) {
    $conexion->rollback();
    echo json_encode(['ok' => false, 'error' => 'Error al reordenar']);
}
