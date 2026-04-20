<?php
require_once __DIR__ . '/includes/config.php';
http_response_code(403);
$seo_title = "Fuera de Radar | " . APP_NAME;
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
            --bg-err: radial-gradient(circle at 50% 50%, #f1f5f9 0%, #cbd5e1 100%);
            --card-err: rgba(255, 255, 255, 0.7);
            --text-err: #1e293b;
            --text-muted-err: #64748b;
            --accent-err: #64748b;
        }

        .dark-mode {
            --bg-err: radial-gradient(circle at 50% 50%, #020617 0%, #1e293b 100%);
            --card-err: rgba(15, 23, 42, 0.6);
            --text-err: #f1f5f9;
            --text-muted-err: #94a3b8;
            --accent-err: #475569;
        }

        body, html {
            margin: 0; padding: 0; height: 100%;
            font-family: 'Inter', sans-serif; overflow: hidden;
        }

        .error-wrapper {
            position: relative; width: 100%; height: 100vh;
            background: var(--bg-err);
            display: flex; align-items: center; justify-content: center;
            padding: 20px; box-sizing: border-box;
        }

        .error-card {
            position: relative; z-index: 10;
            background: var(--card-err);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 40px;
            padding: 60px 40px;
            text-align: center;
            max-width: 500px; width: 100%;
            box-shadow: 0 40px 80px -20px rgba(0, 0, 0, 0.2);
        }

        .error-icon-box {
            width: 100px; height: 100px;
            background: rgba(100, 116, 139, 0.1);
            border-radius: 30px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 30px;
            color: var(--accent-err);
            font-size: 50px;
        }

        .error-title {
            font-size: 32px; font-weight: 800;
            color: var(--text-err); margin-bottom: 20px;
            letter-spacing: -1px;
        }

        .error-desc {
            font-size: 17px; color: var(--text-muted-err);
            line-height: 1.7; margin-bottom: 40px;
            padding: 0 10px;
        }

        .btn-action {
            display: inline-flex; align-items: center; gap: 12px;
            background: var(--text-err); color: white !important;
            padding: 16px 36px; border-radius: 100px;
            font-weight: 700; text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
            filter: brightness(1.2);
        }

        .brand-footer {
            margin-top: 50px; font-size: 11px;
            text-transform: uppercase; letter-spacing: 4px;
            color: var(--text-muted-err); opacity: 0.4;
            font-weight: 800;
        }
    </style>
    <script>
        (function() {
            const theme = localStorage.getItem('theme');
            if (theme === 'dark') document.documentElement.classList.add('dark-mode');
        })();
    </script>
</head>

<body>
    <div class="error-wrapper">
        <div class="error-card anim-up">
            <div class="error-icon-box">
                <i class="bi bi-map"></i>
            </div>

            <h1 class="error-title">Fuera de Radar</h1>
            <p class="error-desc">
                Señal interrumpida. No es posible sincronizar la ubicación solicitada. 
                Te sugerimos volver al punto de inicio para continuar navegando.
            </p>
            
            <a href="<?= APP_URL ?>" class="btn-action">
                <i class="bi bi-house-door"></i> Ir al Inicio
            </a>

            <div class="brand-footer"><?= htmlspecialchars(APP_NAME) ?></div>
        </div>
    </div>
</body>

</html>
