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
        if (!empty($f['foto']) && file_exists('../assets/img/empresascarrusel/' . $f['foto'])) {
            unlink('../assets/img/empresascarrusel/' . $f['foto']);
        }
    }
    $stmt_g->close();

    $stmt_del1 = $conexion->prepare("DELETE FROM empresa_galeria WHERE id_empresa = ?");
    $stmt_del1->bind_param("i", $id);
    $stmt_del1->execute();
    $stmt_del1->close();

    $stmt_votos = $conexion->prepare("DELETE rv FROM resena_votos rv INNER JOIN resenas r ON rv.id_resena = r.id_resena WHERE r.id_empresa = ?");
    $stmt_votos->bind_param("i", $id);
    $stmt_votos->execute();
    $stmt_votos->close();

    $stmt_del2 = $conexion->prepare("DELETE FROM resenas WHERE id_empresa = ?");
    $stmt_del2->bind_param("i", $id);
    $stmt_del2->execute();
    $stmt_del2->close();

    $stmt_del3 = $conexion->prepare("DELETE FROM favoritos WHERE id_empresa = ?");
    $stmt_del3->bind_param("i", $id);
    $stmt_del3->execute();
    $stmt_del3->close();

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
