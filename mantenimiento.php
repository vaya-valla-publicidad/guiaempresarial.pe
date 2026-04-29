<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mantenimiento - Guía Empresarial</title>
    <link rel="icon" href="assets/img/image.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --bg: #f1f5f9;
            --accent: #1B3A57;
            --brand-yellow: #F7DC05;
            --text-main: #0f172a;
            --text-muted: #64748b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg);
            color: var(--text-main);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        body::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(15, 23, 42, 0.05) 1px, transparent 1px);
            background-size: 30px 30px;
            z-index: 1;
        }

        .glow {
            position: absolute;
            width: 800px;
            height: 800px;
            background: radial-gradient(circle, rgba(247, 220, 5, 0.15) 0%, transparent 70%);
            z-index: 0;
            filter: blur(80px);
            animation: moveGlow 12s infinite alternate ease-in-out;
        }

        @keyframes moveGlow {
            from { transform: translate(-15%, -15%); }
            to { transform: translate(15%, 15%); }
        }

        .container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 500px;
            padding: 50px;
            text-align: center;
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 40px;
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.05);
            animation: fadeIn 1s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logo-container {
            margin-bottom: 30px;
        }

        .logo {
            width: 110px;
            height: auto;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: #fff;
            color: var(--accent);
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 30px;
            box-shadow: 0 4px 10px rgba(15, 23, 42, 0.05);
            border: 1px solid rgba(15, 23, 42, 0.05);
        }

        .status-dot {
            width: 8px;
            height: 8px;
            background-color: var(--brand-yellow);
            border-radius: 50%;
            box-shadow: 0 0 8px var(--brand-yellow);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.4); opacity: 0.6; }
            100% { transform: scale(1); opacity: 1; }
        }

        h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 20px;
            letter-spacing: -1px;
            color: var(--accent);
        }

        p {
            font-size: 16px;
            color: var(--text-muted);
            line-height: 1.8;
            margin-bottom: 40px;
            font-weight: 400;
        }

        .progress-container {
            width: 100%;
            max-width: 250px;
            height: 4px;
            background: #e2e8f0;
            margin: 0 auto 50px;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-bar {
            width: 0%;
            height: 100%;
            background: linear-gradient(90deg, var(--brand-yellow), var(--accent));
            border-radius: 10px;
            animation: fill 3s infinite ease-in-out;
        }

        @keyframes fill {
            0% { width: 0%; transform: translateX(-100%); }
            100% { width: 100%; transform: translateX(100%); }
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .social-item {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 15px;
            color: var(--accent);
            text-decoration: none;
            font-size: 20px;
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 6px rgba(15, 23, 42, 0.02);
        }

        .social-item:hover {
            background: var(--accent);
            color: #fff;
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(15, 23, 42, 0.1);
        }

        .footer {
            margin-top: 50px;
            font-size: 10px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 3px;
        }
    </style>
</head>
<body>

    <div class="glow"></div>

    <div class="container">
        <div class="logo-container">
            <img src="assets/img/image.png" alt="Logo Guía Empresarial" class="logo">
        </div>

        <div class="status-badge">
            <div class="status-dot"></div>
            Mejorando el Sistema
        </div>

        <h1>Estamos renovando<br>tu experiencia</h1>
        
        <p>Volvemos en breve</p>

        <div class="progress-container">
            <div class="progress-bar"></div>
        </div>

        <div class="social-links">
            <a href="https://wa.me/51987226299" class="social-item" target="_blank"><i class="bi bi-whatsapp"></i></a>
            <a href="https://www.facebook.com/guiaempresarios" class="social-item" target="_blank"><i class="bi bi-facebook"></i></a>
            <a href="https://m.me/guiaempresarios" class="social-item" target="_blank"><i class="bi bi-messenger"></i></a>
        </div>

        <div class="footer">
            Guía Empresarial &copy; 2026
        </div>
    </div>

</body>
</html>
