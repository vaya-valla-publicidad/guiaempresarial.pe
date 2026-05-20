<?php
include '../db.php';
include '../includes/security.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_publico_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_eliminar_resena'])) {
  if (!validarCSRF()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'CSRF Inválido']);
    exit;
  }
  
  $id_u = intval($_SESSION['usuario_publico_id']);
  $id_r = intval($_POST['ajax_eliminar_resena']);

  $stmt_check = $conexion->prepare("SELECT id_resena FROM resenas WHERE id_resena = ? AND id_usuario_publico = ?");
  $stmt_check->bind_param("ii", $id_r, $id_u);
  $stmt_check->execute();
  $res_check = $stmt_check->get_result();
  if ($res_check->num_rows === 0) {
    $stmt_check->close();
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'No tienes permiso para eliminar esta reseña']);
    exit;
  }
  $stmt_check->close();

  $conexion->begin_transaction();
  try {
    $stmt_del_votes = $conexion->prepare("DELETE FROM resena_votos WHERE id_resena = ?");
    $stmt_del_votes->bind_param("i", $id_r);
    $stmt_del_votes->execute();
    $stmt_del_votes->close();

    $stmt_del = $conexion->prepare("DELETE FROM resenas WHERE id_resena = ? AND id_usuario_publico = ?");
    $stmt_del->bind_param("ii", $id_r, $id_u);
    $stmt_del->execute();
    $stmt_del->close();

    $conexion->commit();
    echo json_encode(['success' => true]);
    exit;
  } catch (Exception $e) {
    $conexion->rollback();
    error_log("Error al eliminar reseña (mi_cuenta): " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error interno al eliminar la reseña']);
    exit;
  }
}

http_response_code(400);
echo json_encode(['success' => false, 'error' => 'Petición inválida']);
