<?php

$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0)
            continue;
        if (strpos($line, '=') === false)
            continue;
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if (!getenv($key)) {
            putenv("$key=$value");
        }
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

define('SMTP_HOST', config_env('SMTP_HOST', 'smtp.gmail.com'));
define('SMTP_USER', config_env('SMTP_USER', 'abelperezcoca058@gmail.com'));
define('SMTP_PASS', config_env('SMTP_PASS', ''));
define('SMTP_PORT', (int) config_env('SMTP_PORT', 587));
define('SMTP_FROM_EMAIL', config_env('SMTP_FROM_EMAIL', 'abelperezcoca058@gmail.com'));
define('SMTP_FROM_NAME', config_env('SMTP_FROM_NAME', 'Guía Empresarial'));

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$default_url = $protocol . $host . '/guiaempresarial.pe';

define('APP_NAME', config_env('APP_NAME', 'Guía Empresarial'));
define('APP_URL', config_env('APP_URL', $default_url));
