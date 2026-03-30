<?php
require_once __DIR__ . '/proteger.php';
include '../db.php';

header('Content-Type: text/plain');

$token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!hash_equals($_SESSION['csrf_token'], $token)) {
    http_response_code(403);
    exit('token_invalido');
}

if (!isset($_GET['id']) || !isset($_GET['tipo'])) {
    http_response_code(400);
    exit('parametros_faltantes');
}

$id   = intval($_GET['id']);
$tipo = $_GET['tipo'];

if (!in_array($tipo, ['galeria', 'logo'], true)) {
    http_response_code(400);
    exit('tipo_invalido');
}

if ($tipo === 'galeria') {

    $stmt = $conexion->prepare("SELECT foto FROM empresa_galeria WHERE id_foto = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $foto = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$foto) {
        http_response_code(404);
        exit('no_encontrado');
    }

    $nombreArchivo = basename($foto['foto']);
    $ruta = __DIR__ . "/../assets/img/empresascarrusel/" . $nombreArchivo;

    if (file_exists($ruta)) {
        unlink($ruta);
    }

    $stmt2 = $conexion->prepare("DELETE FROM empresa_galeria WHERE id_foto = ?");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();
    $stmt2->close();
}

if ($tipo === 'logo') {

    $stmt = $conexion->prepare("SELECT logo FROM empresas WHERE id_empresa = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $empresa = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$empresa || empty($empresa['logo'])) {
        http_response_code(404);
        exit('no_encontrado');
    }

    $nombreArchivo = basename($empresa['logo']);
    $ruta = __DIR__ . "/../assets/img/" . $nombreArchivo;

    if (file_exists($ruta)) {
        unlink($ruta);
    }

    $stmt2 = $conexion->prepare("UPDATE empresas SET logo = NULL WHERE id_empresa = ?");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();
    $stmt2->close();
}

echo 'ok';