<?php
require_once __DIR__ . '/includes/config.php';
http_response_code(404);
$seo_title = "Página no encontrada | " . APP_NAME;
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
            --bg-404: radial-gradient(circle at 20% 20%, #f1f5f9 0%, #e2e8f0 100%);
            --card-404: rgba(255, 255, 255, 0.7);
            --text-404: #1e293b;
            --text-muted-404: #64748b;
        }

        .dark-mode {
            --bg-404: radial-gradient(circle at 20% 20%, #0f172a 0%, #020617 100%);
            --card-404: rgba(15, 23, 42, 0.6);
            --text-404: #f8fafc;
            --text-muted-404: #94a3b8;
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
            background: var(--bg-404);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            box-sizing: border-box;
        }

        /* Blobs decorativos */
        .blob {
            position: absolute;
            width: 400px;
            height: 400px;
            background: linear-gradient(135deg, var(--primario), var(--secundario));
            filter: blur(80px);
            opacity: 0.15;
            border-radius: 50%;
            z-index: 0;
            animation: moveBlob 20s infinite alternate;
        }

        .blob-1 { top: -100px; left: -100px; }
        .blob-2 { bottom: -100px; right: -100px; animation-delay: -5s; }

        @keyframes moveBlob {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(50px, 50px) scale(1.1); }
        }

        .error-card {
            position: relative;
            z-index: 10;
            background: var(--card-404);
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

        .error-num {
            font-size: 150px;
            font-weight: 800;
            line-height: 0.8;
            margin: 0;
            background: linear-gradient(135deg, var(--azul), var(--rojo));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -8px;
            margin-bottom: 20px;
        }

        .error-title {
            font-size: 32px;
            font-weight: 700;
            color: var(--text-404);
            margin-bottom: 16px;
            letter-spacing: -0.5px;
        }

        .error-desc {
            font-size: 17px;
            color: var(--text-muted-404);
            line-height: 1.6;
            margin-bottom: 40px;
        }

        .btn-home {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background: var(--primario);
            color: white !important;
            padding: 16px 32px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.3);
        }

        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -5px rgba(16, 185, 129, 0.4);
            filter: brightness(1.1);
        }

        .btn-home i {
            font-size: 20px;
        }

        .brand-footer {
            margin-top: 40px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--text-muted-404);
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
            <div class="error-num">404</div>
            <h1 class="error-title">Ruta Extraviada</h1>
            <p class="error-desc">
                Parece que el camino que seguías ya no existe o ha sido movido.
                No te preocupes, el éxito está a un clic de distancia.
            </p>
            <a href="<?= APP_URL ?>" class="btn-home">
                <i class="bi bi-arrow-left-circle"></i> Volver al Inicio
            </a>

            <div class="brand-footer">
                <?= htmlspecialchars(APP_NAME) ?>
            </div>
        </div>
    </div>
</body>

</html>