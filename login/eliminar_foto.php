<?php

include '../db.php';

if (!isset($_GET['id']) || !isset($_GET['tipo'])) {
    exit;
}

$id = intval($_GET['id']);
$tipo = $_GET['tipo'];

if ($tipo == "galeria") {

    $stmt = $conexion->prepare("SELECT foto FROM empresa_galeria WHERE id_foto=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $resultado = $stmt->get_result();
    $foto = $resultado->fetch_assoc();

    if ($foto) {

        $ruta = __DIR__ . "/../assets/img/empresas/" . $foto['foto'];

        if (file_exists($ruta)) {
            unlink($ruta);
        }

        $stmt2 = $conexion->prepare("DELETE FROM empresa_galeria WHERE id_foto=?");
        $stmt2->bind_param("i", $id);
        $stmt2->execute();
    }
}

if ($tipo == "logo") {

    $stmt = $conexion->prepare("SELECT logo FROM empresas WHERE id_empresa=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $resultado = $stmt->get_result();
    $empresa = $resultado->fetch_assoc();

    if ($empresa && !empty($empresa['logo'])) {

        $ruta = __DIR__ . "/../assets/img/" . $empresa['logo'];

        if (file_exists($ruta)) {
            unlink($ruta);
        }

        $stmt2 = $conexion->prepare("UPDATE empresas SET logo=NULL WHERE id_empresa=?");
        $stmt2->bind_param("i", $id);
        $stmt2->execute();
    }
}

echo "ok";
