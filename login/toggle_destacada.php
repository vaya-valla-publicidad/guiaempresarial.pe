<?php
require_once __DIR__ . '/proteger.php';
include '../db.php';

header('Content-Type: application/json');

if (!in_array($_SESSION['rol'], ['admin', 'editor'])) {
    echo json_encode(['ok' => false, 'error' => 'Sin permiso']);
    exit;
}

$id     = intval($_POST['id'] ?? 0);
$accion = $_POST['accion'] ?? '';

if (!$id) {
    echo json_encode(['ok' => false, 'error' => 'ID inválido']);
    exit;
}

if ($accion === 'destacar') {
    $total = $conexion->query("SELECT COUNT(*) as t FROM empresas WHERE destacada=1")->fetch_assoc()['t'];
    if ($total >= 3) {
        echo json_encode(['ok' => false, 'error' => 'Ya hay 3 destacadas. Quita una primero.']);
        exit;
    }
    $conexion->query("UPDATE empresas SET destacada=1 WHERE id_empresa=$id");
} else {
    $conexion->query("UPDATE empresas SET destacada=0 WHERE id_empresa=$id");
}

echo json_encode(['ok' => true]);