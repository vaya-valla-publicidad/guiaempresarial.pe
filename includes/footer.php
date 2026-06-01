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
            <a href="<?= APP_URL ?>/contacto">Contacto</a>
            <a href="<?= APP_URL ?>/sobre">Sobre Nosotros</a>
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
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
        <path fill-rule="evenodd"
            d="M8 15a.5.5 0 0 0 .5-.5V2.707l3.146 3.147a.5.5 0 0 0 .708-.708l-4-4a.5.5 0 0 0-.708 0l-4 4a.5.5 0 1 0 .708.708L7.5 2.707V14.5a.5.5 0 0 0 .5.5z" />
    </svg>
</button>

<a href="https://wa.me/51987226299" class="whatsapp-float" target="_blank" rel="noopener noreferrer"
    title="Contáctanos por WhatsApp">
    <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" viewBox="0 0 16 16">
        <path
            d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z" />
    </svg>
    <span class="whatsapp-badge">Chat</span>
</a>

<style>
    .whatsapp-float {
        position: fixed;
        bottom: 30px;
        right: 30px;
        background-color: #25d366;
        color: #FFF;
        width: 60px;
        height: 60px;
        border-radius: 50px;
        text-align: center;
        font-size: 30px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        z-index: 1001;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .whatsapp-float:hover {
        transform: scale(1.1);
        background-color: #20ba5a;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
    }

    .whatsapp-badge {
        position: absolute;
        right: 100%;
        margin-right: 15px;
        background: #fff;
        color: #333;
        font-size: 14px;
        font-weight: 700;
        padding: 5px 15px;
        border-radius: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        opacity: 0;
        transform: translateX(10px);
        transition: 0.3s;
        pointer-events: none;
        white-space: nowrap;
    }

    .whatsapp-float:hover .whatsapp-badge {
        opacity: 1;
        transform: translateX(0);
    }

    @media (max-width: 768px) {
        .whatsapp-float {
            bottom: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            font-size: 24px;
        }

        .whatsapp-badge {
            display: none;
        }
    }
</style>

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
        window.addEventListener("load", function () {
            navigator.serviceWorker.register("<?= APP_URL ?>/service-worker.js")
                .catch(function (error) {
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
        const path = window.location.pathname;
        const params = new URLSearchParams(window.location.search);
        const jumpId = params.get('jump');

        links.forEach(link => {
            link.classList.remove('active');
            const href = link.getAttribute('href');

            if (jumpId && href.includes('jump=' + jumpId)) {
                link.classList.add('active');
            } else if (!jumpId && (path === '/' || path.endsWith('/index') || path.includes('/guiaempresarial.pe') && (path.split('/').pop() === '' || path.split('/').pop() === 'index')) && (href === '<?= APP_URL ?>/' || href === '<?= APP_URL ?>')) {
                link.classList.add('active');
            } else if (href !== '<?= APP_URL ?>/' && href !== '<?= APP_URL ?>' && href.includes(path) && path !== '/') {
                link.classList.add('active');
            }
        });
    }

    activarLink();
    // También activar al hacer clic en los links de salto
    links.forEach(link => {
        link.addEventListener('click', () => {
            setTimeout(activarLink, 100);
        });
    });
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

                        if (window.location.pathname.includes('mi_cuenta')) {
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

    document.addEventListener('DOMContentLoaded', () => {
        initSliders();

        const observerOptions = {
            threshold: 0.1,
            rootMargin: "0px 0px -50px 0px"
        };

        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

        document.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function (e) {
                const url = this.getAttribute('href');
                const target = this.getAttribute('target');

                if (url &&
                    !url.startsWith('#') &&
                    !url.includes('jump=') &&
                    !url.startsWith('javascript') &&
                    !url.startsWith('mailto') &&
                    !url.startsWith('tel') &&
                    target !== '_blank' &&
                    this.hostname === window.location.hostname) {

                    try {
                        const currentUrl = new URL(window.location.href);
                        const linkUrl = new URL(this.href);
                        if (currentUrl.pathname === linkUrl.pathname && linkUrl.hash) {
                            return;
                        }
                    } catch (err) { }

                    e.preventDefault();
                    document.body.classList.add('page-transitioning');

                    setTimeout(() => {
                        window.location.href = url;
                    }, 300);
                }
            });
        });
    });

    window.addEventListener('pageshow', function (event) {
        if (document.body.classList.contains('page-transitioning')) {
            document.body.classList.remove('page-transitioning');
        }
    });
</script>

<script>
    const topbar = document.getElementById('nprogress-bar');
    let timer_topbar, width = 0, fake;

    function startBar() {
        if (!topbar) return;
        width = 0;
        topbar.style.opacity = '1';
        topbar.style.width = '0%';
        topbar.style.transition = 'width 0.3s ease, opacity 0.5s ease';

        fake = setInterval(() => {
            const increment = width < 30 ? 8 : width < 60 ? 4 : width < 80 ? 1.5 : 0;
            width = Math.min(width + increment, 85);
            topbar.style.width = width + '%';
        }, 200);
    }

    function finishBar() {
        if (!topbar) return;
        clearInterval(fake);
        topbar.style.transition = 'width 0.2s ease, opacity 0.5s ease 0.3s';
        topbar.style.width = '100%';
        setTimeout(() => { topbar.style.opacity = '0'; }, 400);
        setTimeout(() => { topbar.style.width = '0%'; }, 900);
    }

    document.addEventListener('click', (e) => {
        const link = e.target.closest('a');
        if (!link) return;

        const href = link.getAttribute('href');
        if (!href) return;

        const isExternal = link.hostname && link.hostname !== location.hostname;
        const isAnchor = href.startsWith('#');
        const isJS = href.startsWith('javascript');
        const isBlank = link.target === '_blank';

        if (isExternal || isAnchor || isJS || isBlank) return;

        startBar();
    });

    window.addEventListener('popstate', startBar);

    window.addEventListener('pageshow', finishBar);
    
    document.addEventListener('DOMContentLoaded', () => {
        const d = new Date();
        const dayNames = ["domingo", "lunes", "martes", "miercoles", "jueves", "viernes", "sabado"];
        // Support variations (miércoles/miercoles, sábado/sabado) but db assumes no accents
        const currentDay = dayNames[d.getDay()];
        const currentHour = d.getHours();
        const currentMin = d.getMinutes();

        const pad = (n) => n.toString().padStart(2, '0');
        const currentDateStr = `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;

        document.querySelectorAll('.horario-display').forEach(el => {
            const rawStr = el.getAttribute('data-horario');
            if (!rawStr) return;
            
            let horarioStr = rawStr;
            let especialStr = '';
            if (rawStr.includes('|')) {
                const pipParts = rawStr.split('|');
                horarioStr = pipParts[0];
                especialStr = pipParts[1];
            }
            
            // Comprobar días especiales
            let isEspecial = false;
            let motivo = '';
            if (especialStr) {
                const feriados = especialStr.split(',');
                for (let f of feriados) {
                    f = f.trim();
                    const fParts = f.split(':');
                    if (fParts[0] === 'feriado' && fParts[1]) {
                        if (fParts[1] === currentDateStr) {
                            isEspecial = true;
                            if (fParts.length >= 3) {
                                motivo = fParts.slice(2).join(':');
                            }
                            break;
                        }
                    }
                }
            }

            if (isEspecial) {
                const mText = motivo ? ` (${motivo})` : '';
                el.innerHTML = `<span style="color:#dc3545; font-weight:600; font-size:0.9em;">🔴 Cerrado por día especial${mText}</span>`;
                return;
            }
            
            // Horario normal
            const parts = horarioStr.toLowerCase().split(',');
            let dayStr = null;
            
            for (let p of parts) {
                // remove accents just in case
                p = p.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                const colonIdx = p.indexOf(':');
                if (colonIdx === -1) continue;
                const dName = p.substring(0, colonIdx).trim();
                const tRange = p.substring(colonIdx + 1).trim();
                if (dName === currentDay) {
                    dayStr = tRange;
                    break;
                }
            }

            if (dayStr) {
                const rangeParts = dayStr.split('-');
                if (rangeParts.length === 2) {
                    const startH_M = rangeParts[0].split(':').map(Number);
                    const endH_M = rangeParts[1].split(':').map(Number);
                    
                    if (startH_M.length === 2 && endH_M.length === 2) {
                        const startH = startH_M[0], startM = startH_M[1];
                        const endH = endH_M[0], endM = endH_M[1];
                        
                        const startTime = startH * 60 + startM;
                        const endTime = endH * 60 + endM;
                        const nowTime = currentHour * 60 + currentMin;
                        
                        const format12h = (h, m) => {
                            const period = h >= 12 ? 'pm' : 'am';
                            let h12 = h % 12;
                            if (h12 === 0) h12 = 12;
                            return `${h12}:${m.toString().padStart(2, '0')} ${period}`;
                        };

                        const textLegible = `de ${format12h(startH, startM)} a ${format12h(endH, endM)}`;
                        
                        let badge = '';
                        if (nowTime >= startTime && nowTime <= endTime) {
                            badge = '<span style="color:#28a745; font-weight:600;">🟢 Abierto ahora</span>';
                        } else {
                            badge = '<span style="color:#dc3545; font-weight:600;">🔴 Cerrado</span>';
                        }
                        
                        el.innerHTML = `<span>🕒 ${textLegible}</span> <span style="margin-left:6px; font-size:0.9em;">${badge}</span>`;
                    }
                }
            } else {
                el.style.display = 'none';
            }
        });
    });
</script>
</body>

</html>