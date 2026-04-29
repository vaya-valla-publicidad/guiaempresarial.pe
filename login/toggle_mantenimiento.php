<?php
require_once __DIR__ . '/proteger.php';
header('Content-Type: application/json');

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    echo json_encode(['ok' => false, 'error' => 'No autorizado']);
    exit;
}

if (!validarCSRF()) {
    echo json_encode(['ok' => false, 'error' => 'Token CSRF inválido']);
    exit;
}

$flag_file = __DIR__ . '/../mantenimiento.flag';

if (file_exists($flag_file)) {
    if (unlink($flag_file)) {
        echo json_encode(['ok' => true, 'mensaje' => 'Modo mantenimiento desactivado']);
    } else {
        echo json_encode(['ok' => false, 'error' => 'No se pudo desactivar el modo mantenimiento']);
    }
} else {
    if (file_put_contents($flag_file, 'active')) {
        echo json_encode(['ok' => true, 'mensaje' => 'Modo mantenimiento activado']);
    } else {
        echo json_encode(['ok' => false, 'error' => 'No se pudo activar el modo mantenimiento']);
    }
}
exit;