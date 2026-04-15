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
            <a href="<?= APP_URL ?>/contacto.php">Contacto</a>
            <a href="<?= APP_URL ?>/sobre.php">Sobre Nosotros</a>
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

<button id="backToTop" class="back-to-top" title="Volver arriba">
    <i class="bi bi-arrow-up"></i>
</button>

<script>
// BOTÓN VOLVER ARRIBA
const btt = document.getElementById('backToTop');
if (btt) {
    window.addEventListener('scroll', () => {
        if (window.scrollY > 400) {
            btt.classList.add('show');
        } else {
            btt.classList.remove('show');
        }
    });
    btt.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
}

if ("serviceWorker" in navigator) {
    window.addEventListener("load", function() {
        navigator.serviceWorker.register("<?= APP_URL ?>/service-worker.js")
        .catch(function(error) {
            console.log("Service Worker no se registró:", error);
        });
    });
}
</script>
<script>
const toggleTheme = document.getElementById('toggle-theme');
if (toggleTheme) {
    if (localStorage.getItem("theme") === "dark") {
        toggleTheme.checked = true;
    }
    toggleTheme.addEventListener('change', () => {
        const isDark = toggleTheme.checked;
        document.documentElement.classList.toggle('dark-mode', isDark);
        document.body.classList.toggle('dark-mode', isDark);
        localStorage.setItem("theme", isDark ? "dark" : "light");
        document.cookie = "theme=" + (isDark ? "dark" : "light") + ";path=/;max-age=" + (30 * 24 * 60 * 60);
    });
}
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

<script>
// SISTEMA DE FAVORITOS
function toggleFavorito(event, idEmpresa) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    const btn = event.currentTarget || event.target.closest('.btn-favorito');
    if (!btn) return;

    const icon = btn.querySelector('i');
    

    btn.style.pointerEvents = 'none';

    const formData = new FormData();
    formData.append('id_empresa', idEmpresa);
    formData.append('csrf_token', window.csrfToken || '');

    fetch('<?= APP_URL ?>/ajax/favoritos_toggle.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        btn.style.pointerEvents = '';
        if (data.ok) {

            const todosLosBotones = document.querySelectorAll(`.btn-favorito[data-id="${idEmpresa}"]`);
            
            todosLosBotones.forEach(b => {
                b.classList.toggle('activo');
                b.classList.add('animar');
                setTimeout(() => b.classList.remove('animar'), 500);
                
                const bIcon = b.querySelector('i');
                if (data.accion === 'agregado') {
                    bIcon.classList.remove('bi-heart');
                    bIcon.classList.add('bi-heart-fill');
                    b.title = 'Quitar de favoritos';
                } else {
                    bIcon.classList.remove('bi-heart-fill');
                    bIcon.classList.add('bi-heart');
                    b.title = 'Guardar en favoritos';
                }
            });

            if (data.accion === 'quitado') {

                if (window.location.pathname.includes('mi_cuenta.php')) {
                    const card = btn.closest('.empresa-item');
                    if (card) {
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.9)';
                        setTimeout(() => {
                            card.remove();

                            const list = document.querySelector('.empresas-list');
                            if (list && list.children.length === 0) {
                                location.reload();
                            }
                        }, 300);
                    }
                }
            }
        } else {

            console.error('Error Favoritos:', data.error);
        }
    })
    .catch(error => {
        btn.style.pointerEvents = '';
    });
}


function initSliders() {
    document.querySelectorAll('.empresa-slider').forEach(slider => {
        if (slider.dataset.initialized) return;
        slider.dataset.initialized = "true";

        const slides = slider.querySelectorAll('.slide');
        const dots = slider.querySelectorAll('.slider-dot');
        if (slides.length <= 1) return;
        
        let idx = 0;
        let ap = null;

        function goTo(n) {
            slides[idx].classList.remove('activo');
            if (dots[idx]) dots[idx].classList.remove('activo');
            idx = (n + slides.length) % slides.length;
            slides[idx].classList.add('activo');
            if (dots[idx]) dots[idx].classList.add('activo');
        }

        const startAuto = () => {
            clearInterval(ap);
            ap = setInterval(() => goTo(idx + 1), 4000);
        };

        startAuto();

        dots.forEach((d, i) => d.addEventListener('click', () => { 
            goTo(i); 
            startAuto(); 
        }));
    });
}

document.addEventListener('DOMContentLoaded', initSliders);
</script>
</body>
</html>