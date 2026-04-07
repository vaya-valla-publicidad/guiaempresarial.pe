<?php
http_response_code(404);
include_once __DIR__ . '/includes/header.php';
?>
<section
    style="min-height: 70vh; display: flex; align-items: center; justify-content: center; text-align: center; padding: 40px 20px;">
    <div style="animation: fadeIn 0.5s ease-in;">
        <div style="font-size: 80px; margin-bottom: 20px;">😵‍💫</div>
        <h1
            style="font-size: 80px; color: #10b981; margin: 0 0 10px 0; font-family: 'Instrument Serif', serif; line-height: 1;">
            404</h1>
        <h2 style="font-size: 24px; margin-bottom: 16px; color: #1f2937;">¡Ups! Página no encontrada</h2>
        <p style="color: #6b7280; max-width: 450px; margin: 0 auto 30px; font-size: 16px; line-height: 1.6;">
            Parece que el enlace está roto, la empresa ya no existe o te has perdido navegando.
            No te preocupes, volvamos a casa.
        </p>
        <a href="index.php"
            style="display: inline-flex; align-items: center; gap: 8px; background: #10b981; color: white !important; padding: 14px 32px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 16px; transition: transform 0.2s, box-shadow 0.2s;">
            <i class="bi bi-house"></i> Volver al Inicio
        </a>
    </div>
</section>
<style>
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
<?php include_once __DIR__ . '/includes/footer.php'; ?>