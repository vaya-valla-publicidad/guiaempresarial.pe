<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/security.php';

if (!isset($_SESSION['usuario_publico_id'])) {
    echo json_encode(['success' => false, 'error' => 'Debes iniciar sesión para dejar una reseña.']);
    exit;
}

if (!validarCSRF($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'error' => 'Error de seguridad. Recarga la página.']);
    exit;
}

$id_u = intval($_SESSION['usuario_publico_id']);
$id_e = intval($_POST['id_empresa'] ?? 0);
$estrellas = intval($_POST['estrellas'] ?? 0);
$comentario = trim($_POST['comentario'] ?? '');

if ($id_e <= 0 || $estrellas < 1 || $estrellas > 5 || empty($comentario)) {
    echo json_encode(['success' => false, 'error' => 'Por favor completa todos los campos correctamente.']);
    exit;
}


$stmt_check = $conexion->prepare("SELECT id_resena FROM resenas WHERE id_usuario_publico = ? AND id_empresa = ?");
$stmt_check->bind_param("ii", $id_u, $id_e);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'Ya has dejado una reseña para este negocio.']);
    exit;
}


$stmt_u = $conexion->prepare("SELECT nombre FROM usuarios_publicos WHERE id = ?");
$stmt_u->bind_param("i", $id_u);
$stmt_u->execute();
$u = $stmt_u->get_result()->fetch_assoc();
$nombre_autor = $u['nombre'];

$stmt_ins = $conexion->prepare("INSERT INTO resenas (id_empresa, nombre_autor, estrellas, comentario, id_usuario_publico) VALUES (?, ?, ?, ?, ?)");
$stmt_ins->bind_param("isisi", $id_e, $nombre_autor, $estrellas, $comentario, $id_u);

if ($stmt_ins->execute()) {
    echo json_encode([
        'success' => true,
        'nombre' => $nombre_autor,
        'fecha' => date('d/m/Y'),
        'letra' => mb_strtoupper(mb_substr($nombre_autor, 0, 1)),
        'estrellas' => $estrellas,
        'comentario' => nl2br(htmlspecialchars($comentario))
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Error al guardar la reseña en la base de datos.']);
}
