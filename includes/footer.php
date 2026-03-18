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
<script>
const toggleBtn = document.getElementById('toggle-theme');

if(localStorage.getItem("theme") === "dark") {
  document.body.classList.add("dark-mode");
  toggleBtn.checked = true;
}

toggleBtn.addEventListener('change', () => {

  document.body.classList.toggle('dark-mode');

  const dark = document.body.classList.contains('dark-mode');

  localStorage.setItem("theme", dark ? "dark" : "light");

});
</script>
<script>
const links = document.querySelectorAll('.nav-link');

function activarLink() {
    const hash = window.location.hash;

    links.forEach(link => {
        link.classList.remove('active');

        if (hash && link.getAttribute('href').includes(hash)) {
            link.classList.add('active');
        } else if (!hash && link.getAttribute('href') === 'index.php') {
            link.classList.add('active');
        }
    });
}

activarLink();
window.addEventListener('hashchange', activarLink);
</script>
</body>
</html>