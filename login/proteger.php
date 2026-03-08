<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: /guiaempresarial.pe/login/login.php?acceso=admin2026");
    exit();
}