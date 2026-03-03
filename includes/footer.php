</main>

<footer class="main-footer">
    <div class="footer-container">

        <div class="footer-brand">
            <h3>GUÍA EMPRESARIAL</h3>
            <p>Impulsando negocios locales con visibilidad real.</p>
        </div>

        <div class="footer-links">
            <a href="https://www.facebook.com/guiaempresarios" target="_blank" rel="noopener noreferrer">
                Facebook
            </a>
            <a href="contacto.php">Contacto</a>
            <a href="sobre.php">Sobre Nosotros</a>
            <a href="https://wa.me/51987226299" target="_blank" rel="noopener noreferrer">
                WhatsApp
            </a>
        </div>

        <div class="footer-copy">
            <small>
                © <?php echo date('Y'); ?> Guía Empresarial - Todos los derechos reservados
            </small>
        </div>

    </div>
</footer>

<script>
if ("serviceWorker" in navigator) {
    window.addEventListener("load", function() {
        navigator.serviceWorker.register("service-worker.js")
        .catch(function(error) {
            console.log("Service Worker no se registró:", error);
        });
    });
}
</script>

</body>
</html>