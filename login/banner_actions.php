<?php
require_once __DIR__ . '/proteger.php';
include '../db.php';

header('Content-Type: application/json');

$accion = $_POST['accion'] ?? '';

if ($accion === 'subir') {
    if (empty($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['ok' => false, 'error' => 'No se recibió ningún archivo.']);
        exit;
    }
    $file = $_FILES['imagen'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    if (!in_array($ext, $allowed)) {
        echo json_encode(['ok' => false, 'error' => 'Formato no permitido. Usa JPG, PNG, WEBP o GIF.']);
        exit;
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        echo json_encode(['ok' => false, 'error' => 'El archivo supera los 5 MB.']);
        exit;
    }
    $carpeta = __DIR__ . '/../assets/img/banner/';
    if (!is_dir($carpeta))
        mkdir($carpeta, 0755, true);
    $nombre = 'banner_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $carpeta . $nombre)) {
        echo json_encode(['ok' => false, 'error' => 'Error al guardar la imagen.']);
        exit;
    }
    $res_orden = $conexion->query("SELECT COALESCE(MAX(orden),0)+1 AS sig FROM banner_carrusel");
    $sig_orden = (int) $res_orden->fetch_assoc()['sig'];
    $tiempo = max(1000, min(30000, intval($_POST['tiempo_ms'] ?? 5000)));
    $stmt = $conexion->prepare("INSERT INTO banner_carrusel (imagen, orden, activo, tiempo_ms) VALUES (?, ?, 1, ?)");
    $stmt->bind_param("sii", $nombre, $sig_orden, $tiempo);
    $stmt->execute();
    $id_nuevo = $stmt->insert_id;
    $stmt->close();
    echo json_encode(['ok' => true, 'id' => $id_nuevo]);
    exit;
}

if ($accion === 'eliminar') {
    $id = intval($_POST['id'] ?? 0);
    if (!$id) {
        echo json_encode(['ok' => false, 'error' => 'ID inválido.']);
        exit;
    }
    $stmt = $conexion->prepare("SELECT imagen FROM banner_carrusel WHERE id_banner = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $ruta = __DIR__ . '/../assets/img/banner/' . $row['imagen'];
        if (file_exists($ruta))
            unlink($ruta);
    }
    $stmt->close();
    $stmt = $conexion->prepare("DELETE FROM banner_carrusel WHERE id_banner = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['ok' => true]);
    exit;
}

if ($accion === 'toggle_activo') {
    $id = intval($_POST['id'] ?? 0);
    if (!$id) {
        echo json_encode(['ok' => false, 'error' => 'ID inválido.']);
        exit;
    }
    $stmt = $conexion->prepare("UPDATE banner_carrusel SET activo = IF(activo=1,0,1) WHERE id_banner = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    $stmt = $conexion->prepare("SELECT activo FROM banner_carrusel WHERE id_banner = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $activo = (int) $stmt->get_result()->fetch_assoc()['activo'];
    $stmt->close();
    echo json_encode(['ok' => true, 'activo' => $activo]);
    exit;
}

if ($accion === 'set_tiempo') {
    $id = intval($_POST['id'] ?? 0);
    $tiempo = max(1000, min(30000, intval($_POST['tiempo_ms'] ?? 5000)));
    if (!$id) {
        echo json_encode(['ok' => false, 'error' => 'ID inválido.']);
        exit;
    }
    $stmt = $conexion->prepare("UPDATE banner_carrusel SET tiempo_ms = ? WHERE id_banner = ?");
    $stmt->bind_param("ii", $tiempo, $id);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['ok' => true, 'tiempo_ms' => $tiempo]);
    exit;
}

if ($accion === 'reordenar') {
    $ids = json_decode($_POST['ids'] ?? '[]', true);
    if (!is_array($ids)) {
        echo json_encode(['ok' => false, 'error' => 'Datos inválidos.']);
        exit;
    }
    foreach ($ids as $orden => $id) {
        $id = intval($id);
        $orden = intval($orden) + 1;
        $conexion->query("UPDATE banner_carrusel SET orden = $orden WHERE id_banner = $id");
    }
    echo json_encode(['ok' => true]);
    exit;
}

echo json_encode(['ok' => false, 'error' => 'Acción no reconocida.']);