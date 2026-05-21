<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/security_headers.php';

try {
    $conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
} catch (mysqli_sql_exception $e) {
    error_log('Error de conexión a la base de datos: ' . $e->getMessage());
    http_response_code(500);
    include_once __DIR__ . '/500.php';
    exit;
}

if ($conexion->connect_error) {
    error_log('Error de conexión a la base de datos: ' . $conexion->connect_error);
    http_response_code(500);
    include_once __DIR__ . '/500.php';
    exit;
}

if (!$conexion->set_charset(DB_CHARSET)) {
    error_log('No se pudo establecer el charset de la base de datos: ' . $conexion->error);
}

if (file_exists(__DIR__ . '/mantenimiento.flag')) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $es_admin = (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin');
    $ruta_actual = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $base_path = parse_url(APP_URL, PHP_URL_PATH) ?: '';

    if (!empty($base_path) && str_starts_with($ruta_actual, $base_path)) {
        $ruta_actual = substr($ruta_actual, strlen($base_path));
    }

    $whitelist = [
        '/login/',
        '/login_usuario',
        '/registro_usuario',
        '/logout',
        '/login/login',
        '/login/admin',
        '/login/editor',
        '/login/cerrar',
        '/login/panel',
    ];

    $es_excluido = false;
    foreach ($whitelist as $item) {
        if ($ruta_actual === $item || str_starts_with($ruta_actual, $item)) {
            $es_excluido = true;
            break;
        }
    }

    if (!$es_admin && !$es_excluido) {
        http_response_code(503);
        include __DIR__ . '/mantenimiento.php';
        exit;
    }
}
?>