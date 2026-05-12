<?php

include_once __DIR__ . '/../includes/config.php';
include_once __DIR__ . '/../includes/security.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario']) || !isset($_SESSION['rol']) || empty($_SESSION['admin_access_granted'])) {
    logSeguridad('acceso_no_autorizado', 'Intento de acceso al panel sin autorización de token o sesión');
    header("Location: " . APP_URL . "/index");
    exit();
}

if (!in_array($_SESSION['rol'], ['admin', 'editor'])) {
    logSeguridad('rol_no_autorizado', 'Usuario con rol ' . $_SESSION['rol'] . ' intentó acceder al panel');
    header("Location: " . APP_URL . "/index");
    exit();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!empty($_SESSION['ua_hash'])) {
    $ua_actual = hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? '');
    if (!hash_equals($_SESSION['ua_hash'], $ua_actual)) {
        session_destroy();
        header("Location: " . APP_URL . "/index");
        exit();
    }
}
