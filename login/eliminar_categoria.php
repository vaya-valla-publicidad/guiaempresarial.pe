<?php
session_start();
include '../db.php';

if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['admin', 'editor'])) {
    header("Location: ../login/login.php");
    exit;
}

if (isset($_GET['id'])) {

    $id = intval($_GET['id']);

    $stmt = $conexion->prepare("DELETE FROM categorias WHERE id_categoria = ?");
    $stmt->bind_param("i", $id);

    if (!$stmt->execute()) {
        die("Error al eliminar: " . $stmt->error);
    }

    $stmt->close();
}

header("Location: admin.php");
exit;