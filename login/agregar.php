<?php
session_start();
include '../db.php';

// Solo admin puede acceder
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$error = "";
$exito = "";

if (isset($_POST['nombre'], $_POST['pass'], $_POST['rol'])) {
    $nombre = trim($_POST['nombre']);
    $pass = $_POST['pass'];
    $rol = $_POST['rol'];

    // Hashear la contraseña
    $hash = password_hash($pass, PASSWORD_DEFAULT);

    // Preparar la consulta (sin email)
    $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, contraseña_hash, rol) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nombre, $hash, $rol);

    if ($stmt->execute()) {
        $exito = "Usuario agregado correctamente.";
    } else {
        $error = "Error al agregar usuario. Revisa que no exista otro con el mismo nombre.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Usuario</title>
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>
<section class="login-section">
    <h1>Agregar Usuario</h1>

    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if ($exito): ?>
        <p style="color:green;"><?= htmlspecialchars($exito) ?></p>
    <?php endif; ?>

    <form action="" method="post">
        <label for="nombre">Nombre</label>
        <input type="text" name="nombre" id="nombre" required>

        <label for="pass">Contraseña</label>
        <input type="password" name="pass" id="pass" required>

        <label for="rol">Rol</label>
        <select name="rol" id="rol" required>
            <option value="admin">Admin</option>
            <option value="editor">Editor</option>
        </select>

        <button type="submit">Agregar Usuario</button>
    </form>

    <p><a href="admin.php">Volver al panel de Admin</a></p>
</section>
</body>
</html>
