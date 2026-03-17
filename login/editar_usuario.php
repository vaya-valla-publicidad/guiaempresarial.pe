<?php require_once __DIR__ . '/proteger.php'; ?>
<?php
include '../db.php';

$error   = "";
$success = "";

if ($_SESSION['rol'] !== 'admin') {
    header("Location: admin.php"); exit;
}

if (!isset($_GET['id'])) {
    header("Location: admin.php"); exit;
}

$id = intval($_GET['id']);

$stmt = $conexion->prepare("SELECT id_usuario, nombre, rol FROM usuarios WHERE id_usuario=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$usuario) { die("Usuario no encontrado"); }

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST['nombre']);
    $rol_usuario = $_POST['rol'];
    $pass  = $_POST['pass'];

    if (empty($nombre) || empty($rol_usuario)) {
        $error = "El nombre y el rol son obligatorios.";
    } else {
        if (!empty($pass)) {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $conexion->prepare("UPDATE usuarios SET nombre=?, rol=?, contraseña_hash=? WHERE id_usuario=?");
            $stmt->bind_param("sssi", $nombre, $rol_usuario, $hash, $id);
        } else {
            $stmt = $conexion->prepare("UPDATE usuarios SET nombre=?, rol=? WHERE id_usuario=?");
            $stmt->bind_param("ssi", $nombre, $rol_usuario, $id);
        }

        if ($stmt->execute()) {
            $success = "Usuario actualizado correctamente ✅";
            $usuario['nombre'] = $nombre;
            $usuario['rol']    = $rol_usuario;
        } else {
            $error = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario</title>
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
                        <option value="admin"  <?= $usuario['rol'] === 'admin'  ? 'selected' : '' ?>>Admin</option>
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