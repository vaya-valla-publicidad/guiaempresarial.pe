<?php
include __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/security.php';
$config_file = __DIR__ . '/../includes/admin_config.php';
define('ACCESO_PERMITIDO', true);

$acceso_autorizado = false;

// Verificamos el token en cada petición (GET o POST)
$token_actual = $_GET['token'] ?? ($_POST['token_admin'] ?? '');

if (!empty($token_actual)) {
    if (file_exists($config_file)) {
        $config = include $config_file;
        if (password_verify($token_actual, $config['token_hash'])) {
            $acceso_autorizado = true;
        }
    }
}

// Si no hay token válido, la página NO EXISTE para el usuario
if (!$acceso_autorizado) {
    http_response_code(404);
    include __DIR__ . '/../404.php';
    exit();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = "";
$max_intentos = 3;
$bloqueo_minutos = 5;

if (!verificarRateLimit('login_admin', 10, 300)) {
    http_response_code(429);
    die('Demasiados intentos. Espera 5 minutos.');
}

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
                    session_regenerate_id(true);
                    $_SESSION['usuario'] = $fila['nombre'];
                    $_SESSION['rol'] = $fila['rol'];
                    $_SESSION['id_usuario'] = (int) $fila['id_usuario'];
                    $_SESSION['admin_access_granted'] = true; // Por compatibilidad con proteger.php
                    $_SESSION['ua_hash'] = hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? '');

                    $_SESSION['intentos'] = 0;
                    $_SESSION['ultimo_intento'] = 0;

                    if ($fila['rol'] === 'admin') {
                        header("Location: " . APP_URL . "/login/admin");
                    } else {
                        header("Location: " . APP_URL . "/login/editor");
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
        $stmt->close();
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
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/login.css?v=<?= time() ?>">
</head>

<body>
    <div class="login-container">
        <section class="login-section">
            <h1 class="login-title">Inicio de sesión</h1>

            <?php if ($error): ?>
                <p class="login-error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <?php if (!($_SESSION['intentos'] >= $max_intentos && $tiempo_actual < $tiempo_bloqueo)): ?>
                <form action="login" method="post" class="login-form">
                    <input type="hidden" name="token_admin" value="<?= htmlspecialchars($token_actual) ?>">

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
            <?php endif; ?>
        </section>
    </div>
</body>

</html>