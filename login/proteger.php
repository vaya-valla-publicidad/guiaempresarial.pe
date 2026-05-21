<?php

include_once __DIR__ . '/../includes/config.php';
include_once __DIR__ . '/../db.php';
include_once __DIR__ . '/../includes/security.php';


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

if (isset($_SESSION['id_usuario']) && isset($conexion)) {
    $id_usuario = intval($_SESSION['id_usuario']);
    $stmt = $conexion->prepare("SELECT contraseña_hash, rol FROM usuarios WHERE id_usuario = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($res) {
            $db_hash = $res['contraseña_hash'];
            $db_rol = $res['rol'];

            if (empty($_SESSION['admin_pw_hash'])) {
                $_SESSION['admin_pw_hash'] = $db_hash;
            }

            if ($db_rol !== $_SESSION['rol'] || !hash_equals($_SESSION['admin_pw_hash'], $db_hash)) {
                logSeguridad('integrity_failed_admin', 'El rol o la contraseña del administrador ha cambiado. Sesión cerrada.');
                session_unset();
                session_destroy();
                header("Location: " . APP_URL . "/index");
                exit();
            }
        } else {
            logSeguridad('integrity_failed_deleted', 'El usuario administrativo ya no existe en la base de datos. Sesión cerrada.');
            session_unset();
            session_destroy();
            header("Location: " . APP_URL . "/index");
            exit();
        }
    }
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
