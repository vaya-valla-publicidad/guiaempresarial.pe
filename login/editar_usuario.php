<?php require_once __DIR__ . '/proteger.php'; ?>
<?php
include '../db.php';
include '../includes/security.php';

$error = "";
$success = "";

if ($_SESSION['rol'] !== 'admin') {
    header("Location: admin.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: admin.php");
    exit;
}

$id = intval($_GET['id']);

$stmt = $conexion->prepare("SELECT id_usuario, nombre, rol FROM usuarios WHERE id_usuario=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$usuario) {
    header("Location: admin.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!validarCSRF()) {
        $error = "Solicitud inválida. Intenta nuevamente.";
    } else {
        $nombre = inputLimpio($_POST['nombre'] ?? '');
        $rol_usuario = inputLimpio($_POST['rol'] ?? '');
        $pass = $_POST['pass'] ?? '';
        $rolesPermitidos = ['admin', 'editor'];

        if (empty($nombre) || empty($rol_usuario)) {
            $error = "El nombre y el rol son obligatorios.";
        } elseif (!in_array($rol_usuario, $rolesPermitidos, true)) {
            $error = "Rol inválido.";
        } else {
            if (!empty($pass)) {
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                $stmt = $conexion->prepare("UPDATE usuarios SET nombre=?, rol=?, contraseña_hash=? WHERE id_usuario=?");
                if ($stmt) {
                    $stmt->bind_param("sssi", $nombre, $rol_usuario, $hash, $id);
                }
            } else {
                $stmt = $conexion->prepare("UPDATE usuarios SET nombre=?, rol=? WHERE id_usuario=?");
                if ($stmt) {
                    $stmt->bind_param("ssi", $nombre, $rol_usuario, $id);
                }
            }

            if (empty($stmt)) {
                error_log("Error preparando actualización de usuario ID {$id}: " . $conexion->error);
                $error = "No se pudo actualizar el usuario. Intenta nuevamente.";
            } elseif ($stmt->execute()) {
                $success = "Usuario actualizado correctamente ✅";
                $usuario['nombre'] = $nombre;
                $usuario['rol'] = $rol_usuario;
            } else {
                error_log("Error ejecutando actualización de usuario ID {$id}: " . $stmt->error);
                $error = "No se pudo actualizar el usuario. Intenta nuevamente.";
            }

            if (!empty($stmt)) {
                $stmt->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario</title>
    <link rel="icon" href="<?= APP_URL ?>/assets/img/image.png" type="image/png">
    <link rel="stylesheet" href="../assets/css/login.css">
</head>

<body>
    <div class="panel-container">
        <section class="panel">
            <h1 class="panel-title">Editar Usuario</h1>
            <div class="form-container">

                <?php if ($error): ?>
                    <p style="color:red;text-align:center;"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>
                <?php if ($success): ?>
                    <p style="color:green;text-align:center;"><?= htmlspecialchars($success) ?></p>
                <?php endif; ?>

                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generarTokenCSRF()) ?>">
                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Nueva contraseña <small style="color:#888">(dejar vacío para no cambiar)</small></label>
                        <input type="password" name="pass">
                    </div>

                    <div class="form-group">
                        <label>Rol</label>
                        <select name="rol" required>
                            <option value="admin" <?= $usuario['rol'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                            <option value="editor" <?= $usuario['rol'] === 'editor' ? 'selected' : '' ?>>Editor</option>
                        </select>
                    </div>

                    <button type="submit" class="btn">Guardar cambios</button>
                </form>

                <a href="admin.php" class="btn btn-danger">Volver al Panel</a>
            </div>
        </section>
    </div>
</body>

</html>