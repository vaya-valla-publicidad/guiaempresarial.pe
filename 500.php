<?php
require_once __DIR__ . '/includes/config.php';
http_response_code(500);
$seo_title = "Error del Servidor | " . APP_NAME;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($seo_title) ?></title>
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
    <link rel="icon" href="<?= APP_URL ?>/assets/img/image.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --bg-500: radial-gradient(circle at 20% 20%, #f1f5f9 0%, #cbd5e1 100%);
            --card-500: rgba(255, 255, 255, 0.7);
            --text-500: #0f172a;
            --text-muted-500: #475569;
        }

        .dark-mode {
            --bg-500: radial-gradient(circle at 20% 20%, #020617 0%, #0f172a 100%);
            --card-500: rgba(30, 41, 59, 0.6);
            --text-500: #f8fafc;
            --text-muted-500: #94a3b8;
        }

        body,
        html {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: 'Inter', sans-serif;
            overflow: hidden;
        }

        .error-wrapper {
            position: relative;
            width: 100%;
            height: 100vh;
            background: var(--bg-500);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            box-sizing: border-box;
        }

        .blob {
            position: absolute;
            width: 400px;
            height: 400px;
            background: linear-gradient(135deg, #3b82f6, #06b6d4);
            filter: blur(80px);
            opacity: 0.1;
            border-radius: 50%;
            z-index: 0;
            animation: moveBlob 20s infinite alternate;
        }

        .blob-1 { top: -100px; right: -100px; }
        .blob-2 { bottom: -100px; left: -100px; animation-delay: -5s; }

        @keyframes moveBlob {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(-50px, -50px) scale(1.1); }
        }

        .error-card {
            position: relative;
            z-index: 10;
            background: var(--card-500);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 32px;
            padding: 60px 40px;
            text-align: center;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .error-icon {
            font-size: 80px;
            margin-bottom: 20px;
            display: inline-block;
            background: linear-gradient(135deg, #3b82f6, #06b6d4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .error-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-500);
            margin-bottom: 16px;
            letter-spacing: -0.5px;
        }

        .error-desc {
            font-size: 16px;
            color: var(--text-muted-500);
            line-height: 1.6;
            margin-bottom: 40px;
        }

        .btn-retry {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background: var(--text-500);
            color: white !important;
            padding: 16px 32px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-retry:hover {
            transform: translateY(-2px);
            background: #2563eb;
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.3);
        }

        .brand-footer {
            margin-top: 40px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--text-muted-500);
            opacity: 0.6;
            font-weight: 700;
        }
    </style>
    <script>
        (function() {
            const theme = localStorage.getItem('theme');
            if (theme === 'dark') {
                document.documentElement.classList.add('dark-mode');
            }
        })();
    </script>
</head>

<body>
    <div class="error-wrapper">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>

        <div class="error-card anim-up">
            <div class="error-icon">
                <i class="bi bi-shield-lock"></i>
            </div>
            <h1 class="error-title">Mantenimiento Inesperado</h1>
            <p class="error-desc">
                Estamos experimentando una alta carga de tráfico o una interrupción técnica momentánea.
                Nuestro equipo ya está trabajando para restablecer el servicio.
            </p>
            <a href="<?= APP_URL ?>" class="btn-retry">
                <i class="bi bi-arrow-clockwise"></i> Reintentar ahora
            </a>

            <div class="brand-footer">
                <?= htmlspecialchars(APP_NAME) ?>
            </div>
        </div>
    </div>
</body>

</html>
