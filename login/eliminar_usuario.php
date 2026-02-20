<?php
session_start();
include '../db.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login/login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    if ($id == $_SESSION['id_usuario']) {
        die("No puedes eliminar tu propio usuario.");
    }

    $stmt = $conexion->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: admin.php");
        exit;
    } else {
        die("Error al eliminar usuario: " . $stmt->error);
    }
} else {
    header("Location: admin.php");
    exit;
}