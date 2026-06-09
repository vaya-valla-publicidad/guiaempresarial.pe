<?php
include __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/security.php';
$config_file = __DIR__ . '/../includes/admin_config.php';
define('ACCESO_PERMITIDO', true);

$token_actual = $_POST['token_admin'] ?? $_GET['token'] ?? $_GET['token_admin'] ?? '';

if (!empty($token_actual)) {
    if (file_exists($config_file)) {
        $config = include $config_file;
        if (password_verify($token_actual, $config['token_hash'])) {
            $_SESSION['admin_token_valid'] = true;
        }
    }
}

$acceso_autorizado = !empty($_SESSION['admin_token_valid']);

if (!$acceso_autorizado) {
    http_response_code(404);
    include __DIR__ . '/../404.php';
    exit();
}


$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['usu'], $_POST['pass'])) {
    if (!validarCSRF()) {
        $error = "Token de seguridad inválido. Recarga la página e intenta de nuevo.";
    } else {
        if (!verificarRateLimit('login_admin', 10, 300)) {
            http_response_code(429);
            $error = "Demasiados intentos. Espera 5 minutos.";
        } else {
            if (!empty($_POST['segundo_nombre'])) {
                header("Location: " . APP_URL . "/index");
                exit();
            }

            $usu = trim($_POST['usu']);
            $pass = $_POST['pass'];

            $stmt = $conexion->prepare("SELECT id_usuario, nombre, contraseña_hash, rol FROM usuarios WHERE nombre = ?");

        if (!$stmt) {
            error_log("Error preparando login: " . $conexion->error);
            $error = "No se pudo procesar el inicio de sesión. Intenta nuevamente.";
        } else {
            $stmt->bind_param("s", $usu);

            if (!$stmt->execute()) {
                error_log("Error ejecutando login para usuario {$usu}: " . $stmt->error);
                $error = "No se pudo procesar el inicio de sesión. Intenta nuevamente.";
            } else {
                $resultado = $stmt->get_result();

                if (!$resultado) {
                    error_log("Error obteniendo resultado de login para usuario {$usu}: " . $stmt->error);
                    $error = "No se pudo procesar el inicio de sesión. Intenta nuevamente.";
                } elseif ($resultado->num_rows === 1) {
                    $fila = $resultado->fetch_assoc();

                    if (password_verify($pass, $fila['contraseña_hash'])) {
                        if ($fila['rol'] === 'viewer') {
                            $error = "Tu cuenta no tiene acceso al panel de administración.";
                        } else {
                            session_regenerate_id(true);
                            $_SESSION['usuario'] = $fila['nombre'];
                            $_SESSION['rol'] = $fila['rol'];
                            $_SESSION['id_usuario'] = (int) $fila['id_usuario'];
                            $_SESSION['admin_pw_hash'] = $fila['contraseña_hash'];
                            $_SESSION['admin_access_granted'] = true;
                            $_SESSION['ua_hash'] = hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? '');

                            if ($fila['rol'] === 'admin') {
                                header("Location: " . APP_URL . "/login/admin");
                            } else {
                                header("Location: " . APP_URL . "/login/editor");
                            }
                            exit();
                        }
                    } else {
                        $error = "Credenciales incorrectas.";
                    }
                } else {
                    $error = "Credenciales incorrectas.";
                }
            }
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
    <title>Inicio de sesión</title>
    <link rel="icon" href="<?= APP_URL ?>/assets/img/image.png" type="image/png">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/login.css?v=<?= ASSET_VERSION ?>">
</head>

<body>
    <div class="login-container">
        <section class="login-section">
            <h1 class="login-title">Inicio de sesión</h1>

            <?php if ($error): ?>
                <p class="login-error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

                <form action="" method="post" class="login-form">
                    <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">

                    <div style="display:none; visibility:hidden; opacity:0; position:absolute; left:-9999px;">
                        <label for="segundo_nombre">Segundo Nombre</label>
                        <input type="text" name="segundo_nombre" id="segundo_nombre" tabindex="-1" autocomplete="off">
                    </div>
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