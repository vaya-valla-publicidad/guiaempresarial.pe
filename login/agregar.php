<?php
include 'proteger.php';   
include '../db.php';

$error = "";
$success = "";

if (!in_array($_SESSION['rol'], ['admin', 'editor'])) {
    header("Location: ../login/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim($_POST['nombre']);
    $pass = $_POST['pass'];
    $rol_usuario = $_POST['rol'];

    if (!empty($nombre) && !empty($pass) && !empty($rol_usuario)) {

        $hash = password_hash($pass, PASSWORD_DEFAULT);

        $stmt = $conexion->prepare(
            "INSERT INTO usuarios (nombre, contraseña_hash, rol) VALUES (?, ?, ?)"
        );
        $stmt->bind_param("sss", $nombre, $hash, $rol_usuario);

        if (!$stmt->execute()) {
            $error = "Error al agregar usuario. Puede que el nombre ya exista.";
        } else {
            $success = "Usuario agregado correctamente ✅";
        }

        $stmt->close();
    } else {
        $error = "Todos los campos son obligatorios.";
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

<section class="panel">
    <h2>Agregar Usuario</h2>

    <?php if($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if($success): ?>
        <p style="color:green;"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form method="post">

        <label>Nombre</label>
        <input type="text" name="nombre" required>

        <label>Contraseña</label>
        <input type="password" name="pass" required>

        <label>Rol</label>
        <select name="rol" required>
            <option value="admin">Admin</option>
            <option value="editor">Editor</option>
        </select>

        <button type="submit" class="btn">Agregar Usuario</button>
    </form>

    <br>
    <a href="admin.php" class="btn btn-danger">Volver al Panel</a>
</section>

</body>
</html>