<?php
http_response_code(404);
include_once __DIR__ . '/includes/header.php';
?>
<section
    style="min-height: 70vh; display: flex; align-items: center; justify-content: center; text-align: center; padding: 40px 20px;">
    <div>
        <h1
            style="font-size: 100px; color: var(--primario); margin: 0; font-family: 'Instrument Serif', serif; line-height: 1;">
            404</h1>
        <h2 style="font-size: 28px; margin-bottom: 16px; color: var(--ink);">Página no encontrada</h2>
        <p style="color: var(--ink-muted); max-width: 500px; margin: 0 auto 30px; font-size: 18px;">
            ¡Ups! Parece que el enlace está roto, la empresa ya no existe o te has perdido navegando.
        </p>
        <a href="index.php"
            style="display: inline-block; background: var(--primario); color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; transition: transform 0.2s;">
            <i class="bi bi-house"></i> Volver al Inicio
        </a>
    </div>
</section>
<?php include_once __DIR__ . '/includes/footer.php'; ?>