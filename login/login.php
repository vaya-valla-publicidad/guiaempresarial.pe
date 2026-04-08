<?php
$config_file = __DIR__ . '/../includes/admin_config.php';
define('ACCESO_PERMITIDO', true);

if (isset($_GET['token'])) {
    if (file_exists($config_file)) {
        $config = include $config_file;
        if (password_verify($_GET['token'], $config['token_hash'])) {
            if (session_status() === PHP_SESSION_NONE)
                session_start();
            $_SESSION['admin_access_granted'] = true;
            header("Location: /guiaempresarial.pe/login/login.php");
            exit();
        } else {
            error_log("Intento de acceso con token inválido desde IP: " . $_SERVER['REMOTE_ADDR']);
        }
    }
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['admin_access_granted']) || $_SESSION['admin_access_granted'] !== true) {
    http_response_code(403);
    die("Acceso Denegado. La puerta secreta requiere una llave válida.");
}

include '../db.php';

$error = "";
$max_intentos = 3;
$bloqueo_minutos = 5;

if (!isset($_SESSION['intentos'])) {
    $_SESSION['intentos'] = 0;
    $_SESSION['ultimo_intento'] = 0;
}

$tiempo_actual = time();
$tiempo_bloqueo = $_SESSION['ultimo_intento'] + ($bloqueo_minutos * 60);

if ($_SESSION['intentos'] >= $max_intentos && $tiempo_actual < $tiempo_bloqueo) {
    $restante = $tiempo_bloqueo - $tiempo_actual;
    $minutos = floor($restante / 60);
    $segundos = $restante % 60;
    $error = "Demasiados intentos fallidos. Intenta de nuevo en {$minutos} min {$segundos} seg.";
} elseif (isset($_POST['usu'], $_POST['pass'])) {
    $usu = trim($_POST['usu']);
    $pass = $_POST['pass'];

    $stmt = $conexion->prepare("SELECT id_usuario, nombre, contraseña_hash, rol FROM usuarios WHERE nombre = ?");
    $stmt->bind_param("s", $usu);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $fila = $resultado->fetch_assoc();

        if (password_verify($pass, $fila['contraseña_hash'])) {
            session_regenerate_id(true);
            $_SESSION['usuario'] = $fila['nombre'];
            $_SESSION['rol'] = $fila['rol'];

            $_SESSION['intentos'] = 0;
            $_SESSION['ultimo_intento'] = 0;

            if ($fila['rol'] === 'admin') {
                header("Location: /guiaempresarial.pe/login/admin.php");
            } else {
                header("Location: /guiaempresarial.pe/login/editor.php");
            }
            exit();
        } else {
            $_SESSION['intentos']++;
            $_SESSION['ultimo_intento'] = time();
            $error = "Credenciales incorrectas.";
        }
    } else {
        $_SESSION['intentos']++;
        $_SESSION['ultimo_intento'] = time();
        $error = "Credenciales incorrectas.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de sesión</title>
    <link rel="stylesheet" href="/guiaempresarial.pe/assets/css/login.css">
</head>

<body>
    <div class="login-container">
        <section class="login-section">
            <h1 class="login-title">Inicio de sesión</h1>

            <?php if ($error): ?>
                <p class="login-error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <?php if (!($_SESSION['intentos'] >= $max_intentos && $tiempo_actual < $tiempo_bloqueo)): ?>
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
            <?php endif; ?>
        </section>
    </div>
</body>

</html>