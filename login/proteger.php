<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario']) || !isset($_SESSION['rol'])) {
    header("Location: /guiaempresarial.pe/login/login.php?acceso=admin2026");
    exit();
}

if (!in_array($_SESSION['rol'], ['admin', 'editor'])) {
    header("Location: /guiaempresarial.pe/login/login.php?acceso=admin2026");
    exit();
}