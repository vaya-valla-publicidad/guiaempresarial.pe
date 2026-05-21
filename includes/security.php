<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['usuario']) || isset($_SESSION['usuario_publico_id'])) {
    $max_time = (isset($_SESSION['rol'])) ? 900 : 1800;
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $max_time)) {
        $es_admin = isset($_SESSION['rol']);
        session_unset();
        session_destroy();
        header('Location: ' . APP_URL . ($es_admin ? '/login/login' : '/login_usuario'));
        exit;
    }
    $_SESSION['last_activity'] = time();
}

if (!function_exists('generarTokenCSRF')) {
    function generarTokenCSRF()
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('validarCSRF')) {
    function validarCSRF($token = null)
    {
        if ($token === null) {
            $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
        }
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}

if (!function_exists('validarImagen')) {
    function validarImagen($tmpPath, $nombreOriginal, $extensionesPermitidas = null)
    {
        if ($extensionesPermitidas === null) {
            $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        }
        $ext = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
        if (!in_array($ext, $extensionesPermitidas)) {
            return false;
        }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $tmpPath);
        finfo_close($finfo);
        $mimesPermitidos = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        return in_array($mime, $mimesPermitidos);
    }
}
if (!function_exists('inputLimpio')) {
    function inputLimpio($data)
    {
        return strip_tags(trim($data));
    }
}

if (!function_exists('esc')) {
    function esc($data)
    {
        return htmlspecialchars($data ?? '', ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('validarEmail')) {
    function validarEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

if (!function_exists('validarURL')) {
    function validarURL($url)
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
}

if (!function_exists('generarNombreArchivo')) {
    function generarNombreArchivo($nombreOriginal)
    {
        $ext = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
        return uniqid('', true) . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    }
}

if (!function_exists('subirImagenSegura')) {
    function subirImagenSegura($file, $directorio, $config = [])
    {
        $defaults = [
            'extensiones' => ['jpg', 'jpeg', 'png', 'webp', 'gif'],
            'tamano_max' => 5 * 1024 * 1024,
            'ancho_max' => 1920,
            'alto_max' => 1080
        ];
        $config = array_merge($defaults, $config);
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'nombre' => null, 'error' => 'Error en la subida'];
        }
        if ($file['size'] > $config['tamano_max']) {
            return ['success' => false, 'nombre' => null, 'error' => 'Archivo muy grande'];
        }
        if (!validarImagen($file['tmp_name'], $file['name'], $config['extensiones'])) {
            return ['success' => false, 'nombre' => null, 'error' => 'Archivo no válido'];
        }
        if (!is_dir($directorio)) {
            mkdir($directorio, 0755, true);
        }
        $nombreArchivo = generarNombreArchivo($file['name']);
        $rutaDestino = $directorio . DIRECTORY_SEPARATOR . $nombreArchivo;
        if (!move_uploaded_file($file['tmp_name'], $rutaDestino)) {
            return ['success' => false, 'nombre' => null, 'error' => 'No se pudo guardar'];
        }
        if (isset($config['redimensionar']) && $config['redimensionar']) {
            redimensionarImagen($rutaDestino, $config['ancho_max'], $config['alto_max']);

            $rutaThumb = $directorio . DIRECTORY_SEPARATOR . 'thumb_' . $nombreArchivo;
            copy($rutaDestino, $rutaThumb);
            redimensionarImagen($rutaThumb, 400, 400);
        }
        if (isset($config['webp']) && $config['webp']) {
            $nuevaRuta = convertirAWebp($rutaDestino);
            if ($nuevaRuta) {
                $nombreArchivo = basename($nuevaRuta);
            }
            if (isset($rutaThumb) && file_exists($rutaThumb)) {
                convertirAWebp($rutaThumb);
            }
        }
        return ['success' => true, 'nombre' => $nombreArchivo, 'error' => null];
    }
}

if (!function_exists('convertirAWebp')) {
    function convertirAWebp($ruta, $calidad = 80)
    {
        if (!function_exists('imagewebp')) {
            return false;
        }
        $info = getimagesize($ruta);
        if (!$info) {
            return false;
        }
        $tipo = $info[2];
        $imagen = null;
        switch ($tipo) {
            case IMAGETYPE_JPEG:
                $imagen = imagecreatefromjpeg($ruta);
                break;
            case IMAGETYPE_PNG:
                $imagen = imagecreatefrompng($ruta);
                imagepalettetotruecolor($imagen);
                imagealphablending($imagen, true);
                imagesavealpha($imagen, true);
                break;
            case IMAGETYPE_WEBP:
                return $ruta;
        }
        if (!$imagen) {
            return false;
        }
        $nuevaRuta = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $ruta);
        if (imagewebp($imagen, $nuevaRuta, $calidad)) {
            imagedestroy($imagen);
            unlink($ruta);
            return $nuevaRuta;
        }
        imagedestroy($imagen);
        return false;
    }
}

if (!function_exists('redimensionarImagen')) {
    function redimensionarImagen($ruta, $anchoMax, $altoMax)
    {
        $info = getimagesize($ruta);
        if (!$info)
            return;
        $ancho = $info[0];
        $alto = $info[1];
        $tipo = $info[2];
        if ($ancho <= $anchoMax && $alto <= $altoMax) {
            return;
        }
        $ratio = min($anchoMax / $ancho, $altoMax / $alto);
        $nuevoAncho = (int) ($ancho * $ratio);
        $nuevoAlto = (int) ($alto * $ratio);
        $imagenOriginal = null;
        switch ($tipo) {
            case IMAGETYPE_JPEG:
                $imagenOriginal = imagecreatefromjpeg($ruta);
                break;
            case IMAGETYPE_PNG:
                $imagenOriginal = imagecreatefrompng($ruta);
                break;
            case IMAGETYPE_WEBP:
                $imagenOriginal = imagecreatefromwebp($ruta);
                break;
            case IMAGETYPE_GIF:
                $imagenOriginal = imagecreatefromgif($ruta);
                break;
        }
        if (!$imagenOriginal)
            return;
        $imagenNueva = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
        if (in_array($tipo, [IMAGETYPE_PNG, IMAGETYPE_GIF])) {
            imagealphablending($imagenNueva, false);
            imagesavealpha($imagenNueva, true);
        }
        imagecopyresampled($imagenNueva, $imagenOriginal, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
        switch ($tipo) {
            case IMAGETYPE_JPEG:
                imagejpeg($imagenNueva, $ruta, 90);
                break;
            case IMAGETYPE_PNG:
                imagepng($imagenNueva, $ruta, 9);
                break;
            case IMAGETYPE_WEBP:
                imagewebp($imagenNueva, $ruta, 90);
                break;
            case IMAGETYPE_GIF:
                imagegif($imagenNueva, $ruta);
                break;
        }
        imagedestroy($imagenOriginal);
        imagedestroy($imagenNueva);
    }
}

if (!function_exists('logSeguridad')) {
    function logSeguridad($accion, $detalle = '', $nivel = 'info')
    {
        $archivoLog = __DIR__ . '/../logs/seguridad.log';
        $fecha = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $usuario = $_SESSION['usuario'] ?? ($_SESSION['usuario_publico_nombre'] ?? 'anonimo');
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $userAgent = preg_replace('/[\r\n\t]/', ' ', $userAgent);
        $entrada = sprintf(
            "[%s] [%s] Usuario: %s | IP: %s | Acción: %s | Detalle: %s | UA: %s\n",
            $fecha,
            strtoupper($nivel),
            $usuario,
            $ip,
            $accion,
            $detalle,
            $userAgent
        );
        $dirLog = dirname($archivoLog);
        if (!is_dir($dirLog)) {
            mkdir($dirLog, 0755, true);
        }
        if (file_exists($archivoLog) && filesize($archivoLog) > 5 * 1024 * 1024) {
            $nuevoNombre = str_replace('.log', '_' . date('Y-m-d_H-i-s') . '.log', $archivoLog);
            rename($archivoLog, $nuevoNombre);
        }
        file_put_contents($archivoLog, $entrada, FILE_APPEND);
    }
}

if (!function_exists('verificarRateLimit')) {
    function verificarRateLimit($accion, $limite = 5, $tiempo = 300)
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $clave = "rate_limit_" . md5($accion . $ip);
        $intentos = $_SESSION[$clave] ?? ['count' => 0, 'inicio' => time()];
        if (time() - $intentos['inicio'] > $tiempo) {
            $intentos = ['count' => 0, 'inicio' => time()];
        }
        $intentos['count']++;
        $_SESSION[$clave] = $intentos;
        if ($intentos['count'] > $limite) {
            logSeguridad('rate_limit_excedido', "Acción: $accion | Intentos: {$intentos['count']}", 'warning');
            return false;
        }
        return true;
    }
}

if (!function_exists('escaparLike')) {
    function escaparLike($cadena)
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $cadena);
    }
}


if (!function_exists('validarRedireccionLocal')) {
    function validarRedireccionLocal($url)
    {
        if (empty($url))
            return 'mi_cuenta.php';
        $url = trim($url);

        if (str_contains($url, '//') || str_contains($url, '\\') || str_contains($url, ':')) {
            return 'mi_cuenta.php';
        }

        if (str_contains($url, '..') || str_contains($url, "\0")) {
            return 'mi_cuenta.php';
        }

        $permitidas = ['/', 'mi_cuenta', 'mi_cuenta.php', 'negocio/', 'rubro/', 'empresas'];
        foreach ($permitidas as $p) {
            if (str_starts_with($url, $p))
                return $url;
        }

        return 'mi_cuenta.php';
    }
}

if (isset($_SESSION['usuario_publico_id']) && isset($conexion)) {
    $stmt_check_session = $conexion->prepare("SELECT password_hash FROM usuarios_publicos WHERE id = ?");
    $stmt_check_session->bind_param("i", $_SESSION['usuario_publico_id']);
    $stmt_check_session->execute();
    $res_check_session = $stmt_check_session->get_result()->fetch_assoc();

    if ($res_check_session) {
        $hash_actual = $res_check_session['password_hash'];
        $hash_sesion = $_SESSION['usuario_publico_pw_hash'] ?? '';

        if ($hash_actual !== $hash_sesion) {
            session_unset();
            session_destroy();
            header('Location: login_usuario?error=sesion_expirada');
            exit;
        }
    }
}

if (!function_exists('registrarIntentoOTP')) {
    function registrarIntentoOTP($identificador)
    {
        global $conexion;
        if (!$conexion)
            return;
        $stmt = $conexion->prepare("INSERT INTO otp_intentos (identificador, intentos, ultimo_intento) 
                                   VALUES (?, 1, NOW()) 
                                   ON DUPLICATE KEY UPDATE 
                                   intentos = intentos + 1, 
                                   ultimo_intento = NOW()");
        $stmt->bind_param("s", $identificador);
        $stmt->execute();
        $stmt->close();
    }
}

if (!function_exists('verificarBloqueoOTP')) {
    function verificarBloqueoOTP($identificador)
    {
        global $conexion;
        if (!$conexion)
            return true;
        $stmt = $conexion->prepare("SELECT intentos, ultimo_intento, bloqueado_hasta FROM otp_intentos WHERE identificador = ?");
        $stmt->bind_param("s", $identificador);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$res)
            return true;

        $now = time();
        if ($res['bloqueado_hasta']) {
            $bloqueo = strtotime($res['bloqueado_hasta']);
            if ($now < $bloqueo)
                return false;
        }

        if ($res['intentos'] >= 5) {
            $ultimo = strtotime($res['ultimo_intento']);
            if ($now - $ultimo < 900) {
                if (!$res['bloqueado_hasta']) {
                    $bloqueo_hasta = date('Y-m-d H:i:s', $now + 900);
                    $stmt_upd = $conexion->prepare("UPDATE otp_intentos SET bloqueado_hasta = ? WHERE identificador = ?");
                    $stmt_upd->bind_param("ss", $bloqueo_hasta, $identificador);
                    $stmt_upd->execute();
                    $stmt_upd->close();
                }
                return false;
            } else {
                $stmt_res = $conexion->prepare("UPDATE otp_intentos SET intentos = 0, bloqueado_hasta = NULL WHERE identificador = ?");
                $stmt_res->bind_param("s", $identificador);
                $stmt_res->execute();
                $stmt_res->close();
            }
        }
        return true;
    }
}

if (!function_exists('limpiarIntentosOTP')) {
    function limpiarIntentosOTP($identificador)
    {
        global $conexion;
        if (!$conexion)
            return;
        $stmt = $conexion->prepare("DELETE FROM otp_intentos WHERE identificador = ?");
        $stmt->bind_param("s", $identificador);
        $stmt->execute();
        $stmt->close();
    }
}
