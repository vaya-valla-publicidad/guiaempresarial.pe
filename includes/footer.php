</main>

<footer>
    <div class="footer-content">
        <small>
            Guía Empresarial &copy; <?php echo date('Y'); ?> - Todos los derechos reservados
        </small>
        <br>
        <p>
            <a href="https://www.facebook.com/guiaempresarios" target="_blank" rel="noopener noreferrer">Facebook</a> |
            <a href="contacto.php">Contacto</a> |
            <a href="sobre.php">Sobre Nosotros</a> |
            <a href="https://wa.me/51987226299" target="_blank" rel="noopener noreferrer">📞 987 226 299</a>
        </p>
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