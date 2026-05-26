<?php
include 'db.php';
include 'includes/components/empresa_card.php';
$seo_title = "Guía Empresarial - Impulsando Negocios Locales";
$seo_description = "Descubre, conecta y potencia empresas de tu región. Visibilidad real para negocios reales.";

$json_ld_schema = [
    [
        "@context" => "https://schema.org",
        "@type" => "Organization",
        "name" => "Guía Empresarial",
        "url" => APP_URL . "/",
        "logo" => APP_URL . "/assets/img/image.png",
        "sameAs" => [
            "https://www.facebook.com/guiaempresarios"
        ],
        "contactPoint" => [
            [
                "@type" => "ContactPoint",
                "telephone" => "+51 987 226 299",
                "contactType" => "customer service",
                "areaServed" => "PE",
                "availableLanguage" => "Spanish"
            ]
        ]
    ],
    [
        "@context" => "https://schema.org",
        "@type" => "WebSite",
        "name" => "Guía Empresarial",
        "url" => APP_URL . "/",
        "potentialAction" => [
            "@type" => "SearchAction",
            "target" => APP_URL . "/empresas?buscar={search_term_string}",
            "query-input" => "required name=search_term_string"
        ]
    ]
];

include 'includes/Header.php';
?>

<?php
$slides = [];
$res_banner = $conexion->query("SELECT * FROM banner_carrusel WHERE activo = 1 ORDER BY orden ASC, id_banner ASC");
if ($res_banner && $res_banner->num_rows > 0)
    while ($s = $res_banner->fetch_assoc())
        $slides[] = $s;
$total_slides = count($slides);
?>

<section class="hero">

    <?php if ($total_slides > 0): ?>
        <?php foreach ($slides as $i => $slide): ?>
            <div class="hero-slide <?= $i === 0 ? 'activo' : '' ?>" data-tiempo="<?= intval($slide['tiempo_ms']) ?>">
                <img src="<?= APP_URL ?>/assets/img/banner/<?= htmlspecialchars($slide['imagen']) ?>" alt="Banner <?= $i + 1 ?>"
                    loading="<?= $i === 0 ? 'eager' : 'lazy' ?>">
            </div>
        <?php endforeach; ?>

        <?php if ($total_slides > 1): ?>
            <button class="hero-arrow prev" aria-label="Anterior">&#8249;</button>
            <button class="hero-arrow next" aria-label="Siguiente">&#8250;</button>
            <div class="hero-dots">
                <?php for ($i = 0; $i < $total_slides; $i++): ?>
                    <button class="hero-dot <?= $i === 0 ? 'activo' : '' ?>" data-index="<?= $i ?>"></button>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="hero-bg"></div>
    <?php endif; ?>

    <div class="hero-content">
        <h1 class="anim-down">Impulsando Negocios Locales</h1>
        <p class="hero-subtitle anim-up anim-delay-1">Descubre, conecta y potencia empresas de tu región</p>
        <p class="hero-tagline anim-up anim-delay-1">Visibilidad real para negocios reales</p>
        <div class="search-wrapper anim-up anim-delay-2">
            <form id="formBuscar" class="search-form" onsubmit="if(typeof startBar === 'function') startBar();">
                <div class="search-box">
                    <input type="text" id="buscar" name="q" placeholder="Buscar empresas, productos o servicios..."
                        autocomplete="off" spellcheck="false" class="search-input">
                    <button type="submit">Buscar</button>
                </div>
            </form>
            <div id="resultados" class="resultados-live"></div>

            <!-- Inicio: Burbujas Rápidas (gestionadas desde panel) -->
            <?php
            $res_pills = $conexion->query("SELECT b.id_burbuja, b.texto, c.slug, c.icono FROM burbujas_busqueda b LEFT JOIN categorias c ON b.id_categoria = c.id_categoria WHERE b.activo = 1 ORDER BY b.orden ASC LIMIT 8");
            if ($res_pills && $res_pills->num_rows > 0):
                ?>
                <div class="quick-pills-ux"
                    style="margin-top: 16px; display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; align-items: center;">
                    <span
                        style="color: rgba(255, 255, 255, 0.85); font-size: 12px; font-weight: 500; letter-spacing: 0.3px; text-transform: uppercase;">Más
                        buscado:</span>
                    <?php while ($pill = $res_pills->fetch_assoc()):
                        $icono_p = !empty($pill['icono']) ? htmlspecialchars($pill['icono']) : 'bi-search';
                        $href = !empty($pill['slug']) ? APP_URL . '/rubro/' . htmlspecialchars($pill['slug']) : APP_URL . '/empresas?buscar=' . urlencode($pill['texto']);
                        ?>
                        <a href="<?= $href ?>" data-burbuja="<?= intval($pill['id_burbuja']) ?>"
                            style="display: inline-flex; align-items: center; gap: 4px; background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); border: 1px solid rgba(255, 255, 255, 0.2); color: white; padding: 5px 12px; border-radius: 50px; font-size: 13px; text-decoration: none; transition: all 0.2s; box-shadow: 0 4px 6px rgba(0,0,0,0.05);"
                            onmouseover="this.style.background='white'; this.style.color='#000'; this.style.transform='translateY(-2px)';"
                            onmouseout="this.style.background='rgba(255, 255, 255, 0.15)'; this.style.color='white'; this.style.transform='translateY(0)';">
                            <i class="bi <?= $icono_p ?>" style="opacity:0.8;"></i> <?= htmlspecialchars($pill['texto']) ?>
                        </a>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
            <!-- Fin: Burbujas Rápidas -->
        </div>
    </div>

</section>

<section id="empresas" class="page-section empresas-section">
    <div class="container">
        <div class="section-header">
            <h2>⭐ Empresas Destacadas</h2>
        </div>
        <?php
        $sql_destacadas = "SELECT e.*, c.nombre AS categoria, c.slug AS cat_slug,
                                GROUP_CONCAT(g.foto ORDER BY g.orden ASC, g.id_foto ASC SEPARATOR ',') as fotos_galeria
                       FROM empresas e
                       JOIN categorias c ON e.id_categoria = c.id_categoria
                       LEFT JOIN empresa_galeria g ON e.id_empresa = g.id_empresa
                       WHERE e.destacada = 1 
                       GROUP BY e.id_empresa
                       LIMIT 3";
        $res_destacadas = $conexion->query($sql_destacadas);
        if ($res_destacadas && $res_destacadas->num_rows > 0):
            echo '<div class="empresas-list">';
            while ($fila = $res_destacadas->fetch_assoc()):
                $fotos_arr = !empty($fila['fotos_galeria']) ? explode(',', $fila['fotos_galeria']) : [];
                renderEmpresaCard($fila, $fotos_arr);
            endwhile;
            echo '</div>';
        else: ?>
            <p class="no-results">No hay empresas destacadas aún.</p>
        <?php endif; ?>
    </div>
</section>

<section class="page-section empresas-section" style="padding-top:0;">
    <div class="container">
        <div class="section-header">
            <h2>👁 Más Vistas</h2>
            <p>Las empresas más populares de nuestra guía</p>
        </div>
        <?php
        $sql_vistas = "SELECT e.*, c.nombre AS categoria, c.slug AS cat_slug,
                               GROUP_CONCAT(g.foto ORDER BY g.orden ASC, g.id_foto ASC SEPARATOR ',') as fotos_galeria
                    FROM empresas e
                    JOIN categorias c ON e.id_categoria = c.id_categoria
                    LEFT JOIN empresa_galeria g ON e.id_empresa = g.id_empresa
                    GROUP BY e.id_empresa
                    ORDER BY e.vistas DESC LIMIT 3";
        $res_vistas = $conexion->query($sql_vistas);
        if ($res_vistas && $res_vistas->num_rows > 0):
            echo '<div class="empresas-list">';
            while ($fila = $res_vistas->fetch_assoc()):
                $fotos_arr = !empty($fila['fotos_galeria']) ? explode(',', $fila['fotos_galeria']) : [];
                renderEmpresaCard($fila, $fotos_arr);
            endwhile;
            echo '</div>';
        endif;
        ?>
        <div class="ver-mas-empresas">
            <a href="<?= APP_URL ?>/empresas" class="btn-ver-mas">Ver más empresas →</a>
        </div>
    </div>
</section>

<section id="categorias" class="page-section">
    <div class="container">
        <div class="section-header">
            <h2>Categorías</h2>
            <p>Explora los rubros destacados</p>
        </div>
        <div class="categorias-modern">
            <?php
            $resultado = $conexion->query("SELECT * FROM categorias ORDER BY orden ASC LIMIT 5");
            while ($fila = $resultado->fetch_assoc()):
                $nombre = htmlspecialchars($fila['nombre']);
                $icono = htmlspecialchars($fila['icono'] ?? 'bi-briefcase');
                ?>
                <a href="<?= APP_URL ?>/rubro/<?= htmlspecialchars($fila['slug']) ?>" class="categoria-card reveal">
                    <div class="categoria-icono-wrap"><i class="bi <?= $icono ?>"></i></div>
                    <span class="categoria-nombre"><?= $nombre ?></span>
                </a>
            <?php endwhile; ?>
            <a href="<?= APP_URL ?>/categorias" class="categoria-card ver-mas-card reveal">
                <div class="categoria-icono-wrap"><i class="bi bi-plus-circle"></i></div>
                <span class="categoria-nombre">Ver más categorías</span>
            </a>
        </div>
    </div>
</section>

<section id="contacto" class="page-section">
    <div class="container">
        <div class="section-header">
            <h1>Contacto Oficial</h1>
            <p>Estas son las únicas vías oficiales para comunicarte con Guía Empresarial.</p>
        </div>
        <div class="contact-grid">
            <a href="https://wa.me/51987226299" target="_blank" class="contact-card whatsapp">
                <div class="contact-icon"><img src="assets/img/whatsapp2.png" alt="WhatsApp"></div>
                <h3>WhatsApp</h3>
                <p>Comunícate directamente para consultas rápidas o información sobre publicidad.</p>
            </a>
            <a href="https://www.facebook.com/guiaempresarios" target="_blank" class="contact-card facebook">
                <div class="contact-icon"><img src="assets/img/facebook2.png" alt="Facebook"></div>
                <h3>Facebook Oficial</h3>
                <p>Síguenos para conocer novedades, publicaciones y negocios destacados.</p>
            </a>
            <a href="https://m.me/guiaempresarios" target="_blank" class="contact-card messenger">
                <div class="contact-icon"><img src="assets/img/messenger2.png" alt="Messenger"></div>
                <h3>Messenger</h3>
                <p>Escríbenos por mensaje directo desde nuestra página oficial.</p>
            </a>
        </div>
        <div class="contact-info-box">
            <h3>Horario de Atención</h3>
            <p>Lunes a Sábado — 9:00 AM a 6:00 PM</p>
            <p>Respondemos mensajes lo más pronto posible.</p>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<script>
    (function () {
        const slides = document.querySelectorAll('.hero-slide');
        const dots = document.querySelectorAll('.hero-dot');
        if (slides.length <= 1) return;

        let idx = 0, timer = null;

        function getTiempo(i) {
            return parseInt(slides[i]?.dataset.tiempo) || 5000;
        }

        function goTo(n) {
            slides[idx].classList.remove('activo');
            if (dots[idx]) dots[idx].classList.remove('activo');
            idx = (n + slides.length) % slides.length;
            slides[idx].classList.add('activo');
            if (dots[idx]) dots[idx].classList.add('activo');
        }

        function startAuto() {
            clearTimeout(timer);
            timer = setTimeout(function tick() {
                goTo(idx + 1);
                clearTimeout(timer);
                timer = setTimeout(tick, getTiempo(idx));
            }, getTiempo(idx));
        }

        document.querySelector('.hero-arrow.prev')?.addEventListener('click', () => { goTo(idx - 1); startAuto(); });
        document.querySelector('.hero-arrow.next')?.addEventListener('click', () => { goTo(idx + 1); startAuto(); });
        dots.forEach(d => d.addEventListener('click', () => { goTo(+d.dataset.index); startAuto(); }));

        if (window.matchMedia('(hover: hover)').matches) {
            const hero = document.querySelector('.hero');
            hero?.addEventListener('mouseenter', () => clearTimeout(timer));
            hero?.addEventListener('mouseleave', startAuto);
        }

        let tx = null;
        const hero = document.querySelector('.hero');
        hero?.addEventListener('touchstart', e => { tx = e.touches[0].clientX; }, { passive: true });
        hero?.addEventListener('touchend', e => {
            if (tx === null) return;
            const diff = tx - e.changedTouches[0].clientX;
            if (Math.abs(diff) > 40) { goTo(diff > 0 ? idx + 1 : idx - 1); startAuto(); }
            tx = null;
        }, { passive: true });

        startAuto();
    })();


    const inputBuscar = document.getElementById('buscar');
    const resultadosDiv = document.getElementById('resultados');
    const formBuscar = document.getElementById('formBuscar');

    let buscarTimer;
    inputBuscar.addEventListener('keyup', function () {
        clearTimeout(buscarTimer);
        const q = this.value.trim();
        if (q.length > 0) {
            resultadosDiv.innerHTML = `
                <div class="buscar-result-item is-loading">
                    <div class="buscar-result-logo skeleton"></div>
                    <div class="buscar-result-info">
                        <div class="skeleton-text skeleton"></div>
                        <div class="skeleton-text short skeleton"></div>
                    </div>
                </div>
                <div class="buscar-result-item is-loading">
                    <div class="buscar-result-logo skeleton"></div>
                    <div class="buscar-result-info">
                        <div class="skeleton-text skeleton"></div>
                        <div class="skeleton-text short skeleton"></div>
                    </div>
                </div>
            `;
            buscarTimer = setTimeout(() => {
                fetch('<?= APP_URL ?>/buscar?q=' + encodeURIComponent(q))
                    .then(r => r.text())
                    .then(d => { resultadosDiv.innerHTML = d; });
            }, 350);
        } else {
            resultadosDiv.innerHTML = '';
        }
    });

    function limpiarBuscadorTotal() {
        if (inputBuscar) inputBuscar.value = '';
        if (resultadosDiv) resultadosDiv.innerHTML = '';
    }

    window.addEventListener('pageshow', function (event) {
        if (!inputBuscar || !resultadosDiv) return;

        const q = inputBuscar.value.trim();
        if (q.length === 0) {
            resultadosDiv.innerHTML = '';
            return;
        }

        if (resultadosDiv.innerHTML.trim() === '') {
            fetch('<?= APP_URL ?>/buscar?q=' + encodeURIComponent(q))
                .then(r => r.text())
                .then(d => { resultadosDiv.innerHTML = d; })
                .catch(() => { resultadosDiv.innerHTML = ''; });
        }
    });

    formBuscar.addEventListener('submit', function (e) {
        e.preventDefault();
        const q = inputBuscar.value.trim();
        if (q.length > 0) {
            limpiarBuscadorTotal();
            window.location.href = '<?= APP_URL ?>/empresas?buscar=' + encodeURIComponent(q);
        }
    });

    resultadosDiv.addEventListener('click', function (e) {
        if (e.target.closest('a')) {
            setTimeout(limpiarBuscadorTotal, 50);
        }
    });

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.search-wrapper')) {
            if (resultadosDiv) resultadosDiv.innerHTML = '';
        }
    });

    // Tracking de clics en burbujas
    document.querySelectorAll('[data-burbuja]').forEach(function (el) {
        el.addEventListener('click', function () {
            const id = this.dataset.burbuja;
            if (id) {
                const fd = new FormData();
                fd.append('id', id);
                navigator.sendBeacon('<?= APP_URL ?>/ajax/burbuja_clic.php', fd);
            }
        });
    });
</script>