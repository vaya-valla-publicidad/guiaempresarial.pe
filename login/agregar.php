<?php require_once __DIR__ . '/proteger.php'; ?>
<?php
include '../db.php';

$error = "";
$success = "";

if ($_SESSION['rol'] !== 'admin') {
    header("Location: admin.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!validarCSRF()) {
        $error = "Token de seguridad inválido. Recargue la página.";
    }

    $nombre = trim($_POST['nombre'] ?? '');
    $pass = $_POST['pass'] ?? '';
    $rol_usuario = $_POST['rol'] ?? '';

    if (!empty($nombre) && !empty($pass) && !empty($rol_usuario)) {

        $roles_validos = ['admin', 'editor', 'viewer'];
        if (!in_array($rol_usuario, $roles_validos, true)) {
            $error = "Rol no válido.";
        }

        if (empty($error)) {

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
        }
    } else {
        $error = "Todos los campos son obligatorios.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar — Panel <?= ucfirst($_SESSION['rol']) ?></title>
    <link rel="icon" href="<?= APP_URL ?>/assets/img/image.png" type="image/png">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/login.css">
</head>

<body>

    <div class="panel-container">
        <section class="panel">

            <h1 class="panel-title">Agregar Usuario</h1>

            <div class="form-container">

                <?php if ($error): ?>
                    <p style="color:red; text-align:center;"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>

                <?php if ($success): ?>
                    <p style="color:green; text-align:center;"><?= htmlspecialchars($success) ?></p>
                <?php endif; ?>

                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" name="nombre" required>
                    </div>

                    <div class="form-group">
                        <label>Contraseña</label>
                        <input type="password" name="pass" required>
                    </div>

                    <div class="form-group">
                        <label>Rol</label>
                        <select name="rol" required>
                            <option value="admin">Admin</option>
                            <option value="editor">Editor</option>
                        </select>
                    </div>

                    <button type="submit" class="btn">Agregar Usuario</button>

                </form>

                <a href="admin.php" class="btn btn-danger">Volver al Panel</a>

            </div>

        </section>
    </div>

</body>

</html>