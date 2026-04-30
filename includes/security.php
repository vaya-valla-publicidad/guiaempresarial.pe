<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
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
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('limpiarParaLike')) {
    function limpiarParaLike($data)
    {
        $data = str_replace(['%', '_', '\\'], ['\%', '\_', '\\\\'], $data);
        return inputLimpio($data);
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
        }
        if (isset($config['webp']) && $config['webp']) {
            $nuevaRuta = convertirAWebp($rutaDestino);
            if ($nuevaRuta) {
                $nombreArchivo = basename($nuevaRuta);
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

if (!function_exists('verificarLimiteCorreo')) {
    function verificarLimiteCorreo($email)
    {
        if (session_status() === PHP_SESSION_NONE)
            session_start();
        $clave = 'limite_correo_' . md5($email);

        if (!isset($_SESSION[$clave])) {
            $_SESSION[$clave] = [
                'count_12h' => 0,
                'first_request_12h' => time(),
                'locked_until' => 0
            ];
        }

        $now = time();
        $s = &$_SESSION[$clave];

        if ($now > $s['first_request_12h'] + 43200) {
            $s['count_12h'] = 0;
            $s['first_request_12h'] = $now;
        }

        if ($now < $s['locked_until']) {
            return false;
        }

        if ($s['count_12h'] >= 10) {
            $s['locked_until'] = max($now, $s['first_request_12h'] + 43200);
            return false;
        }

        $s['count_12h']++;

        if ($s['count_12h'] == 7) {
            $s['locked_until'] = $now + 1800;
        } else if ($s['count_12h'] == 10) {
            $s['locked_until'] = max($now, $s['first_request_12h'] + 43200);
        }

        return true;
    }
}

if (!function_exists('validarRedireccionLocal')) {
    function validarRedireccionLocal($url)
    {
        if (empty($url))
            return 'mi_cuenta.php';
        $url = trim($url);

        if (preg_match('~^(https?:)?//~i', $url)) {
            return 'mi_cuenta.php';
        }
        return $url;
    }
}
