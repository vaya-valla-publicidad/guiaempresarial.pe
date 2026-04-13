<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/security.php';

if (!isset($_SESSION['usuario_publico_id'])) {
    echo json_encode(['ok' => false, 'error' => 'Debes iniciar sesión para guardar favoritos.']);
    exit;
}

if (!validarCSRF($_POST['csrf_token'] ?? '')) {
    echo json_encode(['ok' => false, 'error' => 'Sesión inválida. Recarga la página.']);
    exit;
}

$id_usuario = intval($_SESSION['usuario_publico_id']);
$id_empresa = intval($_POST['id_empresa'] ?? 0);

if ($id_empresa <= 0) {
    echo json_encode(['ok' => false, 'error' => 'Empresa no válida.']);
    exit;
}

$stmt = $conexion->prepare("SELECT id_favorito FROM favoritos WHERE id_usuario_publico = ? AND id_empresa = ?");
$stmt->bind_param("ii", $id_usuario, $id_empresa);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    $stmt_del = $conexion->prepare("DELETE FROM favoritos WHERE id_usuario_publico = ? AND id_empresa = ?");
    $stmt_del->bind_param("ii", $id_usuario, $id_empresa);
    if ($stmt_del->execute()) {
        echo json_encode(['ok' => true, 'accion' => 'quitado']);
    } else {
        echo json_encode(['ok' => false, 'error' => 'Error al quitar favorito.']);
    }
} else {
    $stmt_ins = $conexion->prepare("INSERT INTO favoritos (id_usuario_publico, id_empresa) VALUES (?, ?)");
    $stmt_ins->bind_param("ii", $id_usuario, $id_empresa);
    if ($stmt_ins->execute()) {
        echo json_encode(['ok' => true, 'accion' => 'agregado']);
    } else {
        echo json_encode(['ok' => false, 'error' => 'Error al agregar favorito.']);
    }
}
