<?php
session_start();
include '../db.php';

// 🔐 Verificación correcta (estás dentro de login/)
if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['admin', 'editor'])) {
    header("Location: login.php");
    exit;
}

$error = "";
$success = "";

// ✅ Procesar formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim($_POST['nombre']);
    $telefono = trim($_POST['telefono']);
    $direccion = trim($_POST['direccion']);
    $id_categoria = intval($_POST['id_categoria']);

    $stmt = $conexion->prepare(
        "INSERT INTO empresas (nombre, telefono, direccion, id_categoria) 
         VALUES (?, ?, ?, ?)"
    );

    $stmt->bind_param("sssi", $nombre, $telefono, $direccion, $id_categoria);

    if (!$stmt->execute()) {
        $error = "Error SQL: " . $stmt->error;
    } else {
        $success = "Empresa agregada correctamente ✅";
    }

    $stmt->close();
}

// ✅ Obtener categorías
$categorias = $conexion->query("SELECT id_categoria, nombre FROM categorias");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Agregar Empresa</title>
<link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>

<section class="panel">
    <h2>Agregar Empresa</h2>

    <?php if($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if($success): ?>
        <p style="color:green;"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form method="post">

        <label>Nombre</label>
        <input type="text" name="nombre" required>

        <label>Teléfono</label>
        <input type="text" name="telefono">

        <label>Dirección</label>
        <input type="text" name="direccion">

        <label>Categoría</label>
        <select name="id_categoria" required>
            <?php while($fila = $categorias->fetch_assoc()): ?>
                <option value="<?= $fila['id_categoria'] ?>">
                    <?= htmlspecialchars($fila['nombre']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <button type="submit" class="btn">Agregar Empresa</button>
    </form>

    <br>
    <a href="admin.php" class="btn btn-danger">Volver al Panel</a>

</section>

</body>
</html>
