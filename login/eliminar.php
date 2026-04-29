<?php
require_once __DIR__ . '/proteger.php';
include '../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['admin', 'editor'])) {
    echo json_encode(['ok' => false, 'error' => 'No autorizado']);
    exit;
}

if (!validarCSRF()) {
    echo json_encode(['ok' => false, 'error' => 'Token CSRF inválido']);
    exit;
}

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);

    $stmt_l = $conexion->prepare("SELECT logo FROM empresas WHERE id_empresa = ?");
    $stmt_l->bind_param("i", $id);
    $stmt_l->execute();
    $res_l = $stmt_l->get_result();
    if ($e = $res_l->fetch_assoc()) {
        if (!empty($e['logo']) && file_exists('../assets/img/' . $e['logo'])) {
            unlink('../assets/img/' . $e['logo']);
        }
    }
    $stmt_l->close();

    $stmt_g = $conexion->prepare("SELECT foto FROM empresa_galeria WHERE id_empresa = ?");
    $stmt_g->bind_param("i", $id);
    $stmt_g->execute();
    $res_g = $stmt_g->get_result();
    while ($f = $res_g->fetch_assoc()) {
        if (!empty($f['foto']) && file_exists('../assets/img/' . $f['foto'])) {
            unlink('../assets/img/' . $f['foto']);
        }
    }
    $stmt_g->close();

    $conexion->query("DELETE FROM empresa_galeria WHERE id_empresa = $id");
    $conexion->query("DELETE FROM resenas WHERE id_empresa = $id");
    $conexion->query("DELETE FROM favoritos WHERE id_empresa = $id");

    $stmt = $conexion->prepare("DELETE FROM empresas WHERE id_empresa = ?");
    $stmt->bind_param("i", $id);

    if (!$stmt->execute()) {
        echo json_encode(['ok' => false, 'error' => 'Error al eliminar: ' . $stmt->error]);
        exit;
    }
    $stmt->close();
    echo json_encode(['ok' => true]);
    exit;
}
echo json_encode(['ok' => false, 'error' => 'No se proporcionó ID']);
