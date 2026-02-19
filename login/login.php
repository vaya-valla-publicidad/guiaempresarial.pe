<?php
session_start();
include '../db.php'; 

$error = "";

if (isset($_POST['usu'], $_POST['pass'])) {
    $usu = $_POST['usu'];
    $pass = $_POST['pass'];

    $stmt = $conexion->prepare("SELECT id_usuario, nombre, contraseña_hash, rol FROM usuarios WHERE nombre = ?");
    $stmt->bind_param("s", $usu);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $fila = $resultado->fetch_assoc();

        if (password_verify($pass, $fila['contraseña_hash'])) {
            $_SESSION['usuario'] = $fila['nombre'];
            $_SESSION['rol'] = $fila['rol'];

            if ($fila['rol'] === 'admin') {
                header("Location: admin.php");
            } elseif ($fila['rol'] === 'editor') {
                header("Location: editor.php");
            } else {
                header("Location: ../index.php"); 
            }
            exit;
        } else {
            $error = "Contraseña incorrecta.";
        }
    } else {
        $error = "Usuario no encontrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inicio de sesión</title>
    <link rel="stylesheet" href="/clon/guiaempresarial.pe/assets/css/login.css">
</head>
<body>

<div class="login-container">
    <section class="login-section">
        <h1 class="login-title">Inicio de sesión</h1>

        <?php if($error): ?>
            <p class="login-error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form action="" method="post" class="login-form">
            <div class="form-group">
                <label for="usu">Usuario</label>
                <input type="text" name="usu" id="usu" required>
            </div>

            <div class="form-group">
                <label for="pass">Contraseña</label>
                <input type="password" name="pass" id="pass" required>
            </div>

            <button type="submit" class="login-btn">Ingresar</button>
        </form>
    </section>
</div>

</body>
</html>
