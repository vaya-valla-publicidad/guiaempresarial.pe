<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/security.php';

if (!isset($_SESSION['usuario_publico_id'])) {
    echo json_encode(['success' => false, 'error' => 'Debes iniciar sesión para votar.']);
    exit;
}

if (!validarCSRF($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'error' => 'Error de seguridad. Recarga la página.']);
    exit;
}

$id_u = intval($_SESSION['usuario_publico_id']);
$id_resena = intval($_POST['id_resena'] ?? 0);
$tipo = trim($_POST['tipo'] ?? '');

if ($id_resena <= 0 || !in_array($tipo, ['like', 'dislike'], true)) {
    echo json_encode(['success' => false, 'error' => 'Datos de voto inválidos.']);
    exit;
}

$stmt = $conexion->prepare("SELECT id_usuario_publico FROM resenas WHERE id_resena = ?");
$stmt->bind_param("i", $id_resena);
$stmt->execute();
$result = $stmt->get_result();
if (!$result || $result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Reseña no encontrada.']);
    exit;
}
$resena = $result->fetch_assoc();
$resena_autor = intval($resena['id_usuario_publico']);
if ($resena_autor === $id_u) {
    echo json_encode(['success' => false, 'error' => 'No puedes votar tu propia reseña.']);
    exit;
}

$createTableSql = "CREATE TABLE IF NOT EXISTS resena_votos (
  id_voto int(11) NOT NULL AUTO_INCREMENT,
  id_resena int(11) NOT NULL,
  id_usuario_publico int(11) NOT NULL,
  tipo enum('like','dislike') NOT NULL,
  fecha datetime DEFAULT current_timestamp(),
  PRIMARY KEY (id_voto),
  UNIQUE KEY resena_usuario (id_resena, id_usuario_publico),
  KEY id_resena (id_resena),
  KEY id_usuario_publico (id_usuario_publico)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;";
$conexion->query($createTableSql);

$stmt_check = $conexion->prepare("SELECT tipo FROM resena_votos WHERE id_resena = ? AND id_usuario_publico = ?");
$stmt_check->bind_param("ii", $id_resena, $id_u);
$stmt_check->execute();
$existing = $stmt_check->get_result()->fetch_assoc();
$my_vote = '';

if ($existing) {
    if ($existing['tipo'] === $tipo) {
        $stmt_del = $conexion->prepare("DELETE FROM resena_votos WHERE id_resena = ? AND id_usuario_publico = ?");
        $stmt_del->bind_param("ii", $id_resena, $id_u);
        $stmt_del->execute();
        $my_vote = '';
    } else {
        $stmt_upd = $conexion->prepare("UPDATE resena_votos SET tipo = ?, fecha = NOW() WHERE id_resena = ? AND id_usuario_publico = ?");
        $stmt_upd->bind_param("sii", $tipo, $id_resena, $id_u);
        $stmt_upd->execute();
        $my_vote = $tipo;
    }
} else {
    $stmt_ins = $conexion->prepare("INSERT INTO resena_votos (id_resena, id_usuario_publico, tipo) VALUES (?, ?, ?)");
    $stmt_ins->bind_param("iis", $id_resena, $id_u, $tipo);
    $stmt_ins->execute();
    $my_vote = $tipo;
}

$stmt_count = $conexion->prepare("SELECT SUM(tipo = 'like') AS likes, SUM(tipo = 'dislike') AS dislikes FROM resena_votos WHERE id_resena = ?");
$stmt_count->bind_param("i", $id_resena);
$stmt_count->execute();
$countResult = $stmt_count->get_result()->fetch_assoc();

echo json_encode([
    'success' => true,
    'likes' => intval($countResult['likes']),
    'dislikes' => intval($countResult['dislikes']),
    'my_vote' => $my_vote,
]);
