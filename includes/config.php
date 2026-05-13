<?php
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $lineas = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lineas as $linea) {
        $linea = trim($linea);
        if (str_starts_with($linea, '#'))
            continue;
        if (!str_contains($linea, '='))
            continue;

        [$clave, $valor] = explode('=', $linea, 2);
        $clave = trim($clave);
        $valor = trim($valor, " \t\n\r\0\x0B\"'");

        $_ENV[$clave] = $valor;
        putenv("$clave=$valor");
    }
}

if (!function_exists('config_env')) {
    function config_env($key, $default = null)
    {
        $value = $_ENV[$key] ?? getenv($key);
        return ($value !== false && $value !== null && $value !== '') ? $value : $default;
    }
}

define('DB_HOST', config_env('DB_HOST', 'localhost'));
define('DB_USER', config_env('DB_USER', 'root'));
define('DB_PASS', config_env('DB_PASS', ''));
define('DB_NAME', config_env('DB_NAME', 'guia_empresarial'));
define('DB_CHARSET', config_env('DB_CHARSET', 'utf8mb4'));

if (!config_env('DB_USER') || !config_env('DB_NAME')) {
    error_log('ADVERTENCIA: DB_USER o DB_NAME no definidos en .env');
}

define('SMTP_HOST', config_env('SMTP_HOST', 'smtp.gmail.com'));
define('SMTP_USER', config_env('SMTP_USER', ''));
define('SMTP_PASS', config_env('SMTP_PASS', ''));
define('SMTP_PORT', (int) config_env('SMTP_PORT', 587));
define('SMTP_FROM_EMAIL', config_env('SMTP_FROM_EMAIL', ''));
define('SMTP_FROM_NAME', config_env('SMTP_FROM_NAME', 'Guía Empresarial'));

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$default_url = $protocol . $host . '/guiaempresarial.pe';

define('APP_NAME', config_env('APP_NAME', 'Guía Empresarial'));
define('APP_URL', config_env('APP_URL', $default_url));
