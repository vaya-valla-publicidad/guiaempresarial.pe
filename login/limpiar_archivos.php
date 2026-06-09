<?php
require_once __DIR__ . '/proteger.php';
include '../db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    echo json_encode(['ok' => false, 'error' => 'No autorizado']);
    exit;
}

if (!validarCSRF()) {
    echo json_encode(['ok' => false, 'error' => 'Token CSRF inválido']);
    exit;
}

$accion = $_POST['accion'] ?? 'escanear';

$archivos_usados = [];

$res = $conexion->query("SELECT logo FROM empresas WHERE logo IS NOT NULL AND logo != ''");
while($f = $res->fetch_assoc()) $archivos_usados[strtolower('assets/img/'.trim(basename($f['logo'])))] = true;

$res = $conexion->query("SELECT foto FROM empresa_galeria WHERE foto IS NOT NULL AND foto != ''");
while($f = $res->fetch_assoc()) $archivos_usados[strtolower('assets/img/empresascarrusel/'.trim(basename($f['foto'])))] = true;

$res = $conexion->query("SELECT foto_perfil FROM usuarios_publicos WHERE foto_perfil IS NOT NULL AND foto_perfil != ''");
while($f = $res->fetch_assoc()) $archivos_usados[strtolower('assets/img/avatars/'.trim(basename($f['foto_perfil'])))] = true;

$res = $conexion->query("SELECT imagen FROM banner_carrusel WHERE imagen IS NOT NULL AND imagen != ''");
while($f = $res->fetch_assoc()) $archivos_usados[strtolower('assets/img/banner/'.trim(basename($f['imagen'])))] = true;

$directorios = [
    '../assets/img' => 'assets/img/',
    '../assets/img/avatars' => 'assets/img/avatars/',
    '../assets/img/banner' => 'assets/img/banner/',
    '../assets/img/empresascarrusel' => 'assets/img/empresascarrusel/'
];

$archivos_huerfanos = [];

foreach($directorios as $ruta_fisica => $prefijo) {
    if (!is_dir($ruta_fisica)) continue;
    
    $files = scandir($ruta_fisica);
    foreach($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $ruta_relativa = $prefijo . $file;
        
        if (is_dir($ruta_fisica . '/' . $file)) continue;
        
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'])) continue;
        
        if (strpos($file, 'icon-') === 0 || $file === 'image.png' || $file === 'whatsapp2.png' || $file === 'facebook2.png' || $file === 'messenger2.png') continue;

        if (!isset($archivos_usados[strtolower($ruta_relativa)])) {
            $archivos_huerfanos[] = [
                'nombre' => $file,
                'ruta' => $ruta_relativa,
                'ruta_full' => realpath($ruta_fisica . '/' . $file)
            ];
        }
    }
}

if ($accion === 'escanear') {
    echo json_encode(['ok' => true, 'huerfanos' => $archivos_huerfanos]);
} elseif ($accion === 'limpiar') {
    $offset = max(0, intval($_POST['offset'] ?? 0));
    $batch_size = 50;
    $lote = array_slice($archivos_huerfanos, $offset, $batch_size);
    $borrados = 0;
    foreach ($lote as $h) {
        if (file_exists($h['ruta_full'])) {
            if (unlink($h['ruta_full'])) {
                logSeguridad('archivo_huerfano_borrado', 'Archivo eliminado: ' . $h['ruta']);
                $borrados++;
            }
        }
    }
    $siguiente_offset = $offset + $batch_size;
    $hay_mas = $siguiente_offset < count($archivos_huerfanos);
    echo json_encode([
        'ok' => true,
        'borrados' => $borrados,
        'offset_actual' => $offset,
        'siguiente_offset' => $siguiente_offset,
        'hay_mas' => $hay_mas,
        'total_huerfanos' => count($archivos_huerfanos)
    ]);
}
exit;
