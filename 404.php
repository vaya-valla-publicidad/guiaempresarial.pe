<?php
http_response_code(404);
include_once __DIR__ . '/includes/header.php';
?>
<section class="error-page-section">
    <div class="error-page-content">
        <div class="error-emoji">😵‍💫</div>
        <h1 class="error-code">404</h1>
        <h2 class="error-title">¡Ups! Página no encontrada</h2>
        <p class="error-description">
            Parece que el enlace está roto, la empresa ya no existe o te has perdido navegando.
            No te preocupes, volvamos a casa.
        </p>
        <a href="index.php" class="btn-error-home">
            <i class="bi bi-house"></i> Volver al Inicio
        </a>
    </div>
</section>
<?php include_once __DIR__ . '/includes/footer.php'; ?>