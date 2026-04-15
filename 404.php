<?php
http_response_code(404);
$seo_title = "Página no encontrada - Guía Empresarial";
include_once __DIR__ . '/includes/header.php';
?>
<style>
    .error-page-wrapper {
        min-height: 80vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 60px 20px;
        background: radial-gradient(circle at 50% 0%, #f8fafc, #e2e8f0);
    }

    .error-card {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.6);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1), 0 0 0 1px rgba(0, 0, 0, 0.02);
        border-radius: 30px;
        padding: 60px 40px;
        text-align: center;
        max-width: 500px;
        width: 100%;
        position: relative;
        overflow: hidden;
    }

    .error-card::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(247, 220, 5, 0.1) 0%, transparent 60%);
        animation: rotateGradient 15s linear infinite;
        z-index: 0;
        pointer-events: none;
    }

    @keyframes rotateGradient {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }

    .error-content {
        position: relative;
        z-index: 1;
    }

    .error-code {
        font-size: 120px;
        font-weight: 800;
        line-height: 1;
        margin: 0;
        background: linear-gradient(135deg, var(--azul), var(--rojo));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-family: 'Instrument Serif', serif;
        font-style: italic;
        letter-spacing: -4px;
    }

    .error-title {
        font-size: 28px;
        color: var(--ink);
        font-weight: 700;
        margin: 20px 0 15px;
        letter-spacing: -0.5px;
    }

    .error-description {
        color: var(--ink-muted);
        font-size: 17px;
        line-height: 1.6;
        margin-bottom: 35px;
        padding: 0 20px;
    }

    .btn-error-home {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        background: var(--ink);
        color: white;
        padding: 14px 30px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 16px;
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.2, 1, 0.3, 1);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .btn-error-home:hover {
        background: var(--rojo);
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(230, 57, 70, 0.3);
    }

    .btn-error-home i {
        font-size: 18px;
    }
</style>

<div class="error-page-wrapper">
    <div class="error-card">
        <div class="error-content">
            <h1 class="error-code anim-down">404</h1>
            <h2 class="error-title anim-up anim-delay-1">Ruta desconectada</h2>
            <p class="error-description anim-up anim-delay-2">
                El enlace que seguiste parece estar roto, o la empresa ya no se encuentra en esta dirección.
                Volvamos a donde sucede la acción.
            </p>
            <a href="index.php" class="btn-error-home anim-up anim-delay-2">
                <i class="bi bi-compass"></i> Explorar negocios
            </a>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>