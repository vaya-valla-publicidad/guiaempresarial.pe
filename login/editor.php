<?php
session_start();
include '../db.php';

// Solo admin o editor pueden acceder
if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['admin', 'editor'])) {
    header("Location: login.php");
    exit;
}

$rol = $_SESSION['rol'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel <?= ucfirst($rol) ?></title>
<link rel="stylesheet" href="/clon/guiaempresarial.pe/assets/css/login.css">
</head>
<body>

<div class="panel-container">
    <section class="panel">
        <h1 class="panel-title">Panel <?= ucfirst($rol) ?></h1>
        <div class="usuario-info">
            Bienvenido, <?= htmlspecialchars($_SESSION['usuario']) ?> | 
            <a href="cerrar.php">Cerrar sesión</a>
        </div>

        <!-- Empresas -->
        <h2>Empresas</h2>
        <a href="agregar_empresa.php" class="btn">Agregar Empresa</a>
        <table>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Teléfono</th>
                <th>Dirección</th>
                <th>Rubro</th>
                <th>Acciones</th>
            </tr>
            <?php
            $res = $conexion->query("SELECT e.id_empresa, e.nombre, e.telefono, e.direccion, c.nombre AS categoria 
                                     FROM empresas e 
                                     JOIN categorias c ON e.id_categoria = c.id_categoria");
            while($fila = $res->fetch_assoc()):
            ?>
            <tr>
                <td><?= $fila['id_empresa'] ?></td>
                <td><?= htmlspecialchars($fila['nombre']) ?></td>
                <td><?= htmlspecialchars($fila['telefono']) ?></td>
                <td><?= htmlspecialchars($fila['direccion']) ?></td>
                <td><?= htmlspecialchars($fila['categoria']) ?></td>
                <td class="acciones">
                    <a href="editar.php?id=<?= $fila['id_empresa'] ?>" class="btn">Editar</a>
                    <a href="eliminar.php?id=<?= $fila['id_empresa'] ?>" 
                       class="btn btn-danger" 
                       onclick="return confirm('¿Seguro que deseas eliminar esta empresa?');">
                       Eliminar
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>

        <!-- Categorías -->
        <h2>Categorías</h2>
        <a href="agregar_categoria.php" class="btn">Agregar Categoría</a>
        <table>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Acciones</th>
            </tr>
            <?php
            $res = $conexion->query("SELECT * FROM categorias");
            while($fila = $res->fetch_assoc()):
            ?>
            <tr>
                <td><?= $fila['id_categoria'] ?></td>
                <td><?= htmlspecialchars($fila['nombre']) ?></td>
                <td class="acciones">
                    <a href="eliminar_categoria.php?id=<?= $fila['id_categoria'] ?>" 
                       class="btn btn-danger" 
                       onclick="return confirm('¿Seguro que deseas eliminar esta categoría?');">
                       Eliminar
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>

        <br><br><br><br>
    </section>
</div>

</body>
</html>
