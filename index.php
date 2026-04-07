<?php
include 'db.php';
$seo_title = "Guía Empresarial - Impulsando Negocios Locales";
$seo_description = "Descubre, conecta y potencia empresas de tu región. Visibilidad real para negocios reales.";
include 'includes/header.php';
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
                <img src="/guiaempresarial.pe/assets/img/banner/<?= htmlspecialchars($slide['imagen']) ?>"
                    alt="Banner <?= $i + 1 ?>" loading="<?= $i === 0 ? 'eager' : 'lazy' ?>">
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
        <h1>Impulsando Negocios Locales</h1>
        <p class="hero-subtitle">Descubre, conecta y potencia empresas de tu región</p>
        <p class="hero-tagline">Visibilidad real para negocios reales</p>
        <div class="search-wrapper">
            <form id="formBuscar" class="search-form">
                <div class="search-box">
                    <input type="text" id="buscar" name="q" placeholder="Buscar empresas, productos o servicios..."
                        autocomplete="off" spellcheck="false" style="text-align: left; padding-left: 18px;">
                    <button type="submit">Buscar</button>
                </div>
            </form>
            <div id="resultados" class="resultados-live"></div>
        </div>
    </div>

</section>

<section id="empresas" class="page-section empresas-section">
    <div class="container">
        <div class="section-header">
            <h1>⭐ Empresas Destacadas</h1>
        </div>
        <?php
        $sql_destacadas = "SELECT e.*, c.nombre AS categoria
                       FROM empresas e
                       JOIN categorias c ON e.id_categoria = c.id_categoria
                       WHERE e.destacada = 1 LIMIT 3";
        $res_destacadas = $conexion->query($sql_destacadas);
        if ($res_destacadas && $res_destacadas->num_rows > 0):
            echo '<div class="empresas-list">';
            while ($fila = $res_destacadas->fetch_assoc()):
                $logo = !empty($fila['logo']) ? htmlspecialchars($fila['logo']) : null;
                $telefono = $fila['telefono'] ?? null;
                $numero = $telefono ? preg_replace('/[^0-9]/', '', $telefono) : null;
                $id = intval($fila['id_empresa']);
                $fotos = $conexion->query("SELECT foto FROM empresa_galeria WHERE id_empresa = $id ORDER BY orden ASC, id_foto ASC");
                $fotos_arr = [];
                if ($fotos && $fotos->num_rows > 0)
                    while ($f = $fotos->fetch_assoc())
                        $fotos_arr[] = $f['foto'];
                ?>
                <div class="empresa-item empresa-destacada">
                    <div class="empresa-info-logo">
                        <div class="empresa-top-row">
                            <div class="empresa-logo">
                                <?php if ($logo): ?>
                                    <img src="/guiaempresarial.pe/assets/img/<?= $logo ?>"
                                        alt="<?= htmlspecialchars($fila['nombre']) ?>">
                                <?php else: ?>
                                    <div class="logo-placeholder"><?= mb_strtoupper(mb_substr($fila['nombre'], 0, 1)) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="empresa-titles">
                                <h3><?= htmlspecialchars($fila['nombre']) ?> <span class="badge-destacada">⭐ Destacada</span>
                                </h3>
                                <span class="empresa-categoria"><?= htmlspecialchars($fila['categoria']) ?></span>
                            </div>
                        </div>
                        <p class="empresa-slogan">
                            <?= !empty($fila['descripcion']) ? htmlspecialchars(mb_strimwidth($fila['descripcion'], 0, 80, '…')) : 'Tu mejor opción local' ?>
                        </p>
                        <?php if (!empty($fila['direccion'])): ?>
                            <p class="empresa-direccion">📍 <?= htmlspecialchars($fila['direccion']) ?></p>
                        <?php endif; ?>
                        <div class="empresa-datos">
                            <span>🕒
                                <?= !empty($fila['horario']) ? htmlspecialchars($fila['horario']) : '9:00 AM - 6:00 PM' ?></span>
                            <?php if ($numero): ?><span>📞 <?= $numero ?></span><?php endif; ?>
                            <span>👁 <?= number_format($fila['vistas']) ?> vistas</span>
                        </div>
                        <div class="empresa-actions">
                            <a href="empresas.php?empresa=<?= $id ?>" class="btn-ver">Ver más</a>
                            <?php if ($numero): ?>
                                <a href="https://wa.me/<?= $numero ?>" target="_blank" class="btn-whatsapp">WhatsApp</a>
                            <?php endif; ?>
                            <?php if (!empty($fila['ubicacion_link'])): ?>
                                <a href="<?= htmlspecialchars($fila['ubicacion_link']) ?>" target="_blank"
                                    class="btn-maps">Ubicación</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if (count($fotos_arr) > 0): ?>
                        <div class="empresa-slider">
                            <?php foreach ($fotos_arr as $i => $foto): ?>
                                <div class="slide <?= $i === 0 ? 'activo' : '' ?>">
                                    <img src="/guiaempresarial.pe/assets/img/empresascarrusel/<?= htmlspecialchars($foto) ?>"
                                        alt="Imagen de <?= htmlspecialchars($fila['nombre']) ?>" loading="lazy">
                                </div>
                            <?php endforeach; ?>
                            <?php if (count($fotos_arr) > 1): ?>
                                <div class="slider-dots">
                                    <?php foreach ($fotos_arr as $i => $_): ?>
                                        <button class="slider-dot <?= $i === 0 ? 'activo' : '' ?>" data-index="<?= $i ?>"></button>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile;
            echo '</div>';
        else: ?>
            <p class="no-results">No hay empresas destacadas aún.</p>
        <?php endif; ?>
    </div>
</section>

<section class="page-section empresas-section" style="padding-top:0;">
    <div class="container">
        <div class="section-header">
            <h1>👁 Más Vistas</h1>
            <p>Las empresas más populares de nuestra guía</p>
        </div>
        <?php
        $sql_vistas = "SELECT e.*, c.nombre AS categoria
                   FROM empresas e
                   JOIN categorias c ON e.id_categoria = c.id_categoria
                   ORDER BY e.vistas DESC LIMIT 3";
        $res_vistas = $conexion->query($sql_vistas);
        if ($res_vistas && $res_vistas->num_rows > 0):
            echo '<div class="empresas-list">';
            while ($fila = $res_vistas->fetch_assoc()):
                $logo = !empty($fila['logo']) ? htmlspecialchars($fila['logo']) : null;
                $telefono = $fila['telefono'] ?? null;
                $numero = $telefono ? preg_replace('/[^0-9]/', '', $telefono) : null;
                $id = intval($fila['id_empresa']);
                $fotos = $conexion->query("SELECT foto FROM empresa_galeria WHERE id_empresa = $id ORDER BY orden ASC, id_foto ASC");
                $fotos_arr = [];
                if ($fotos && $fotos->num_rows > 0)
                    while ($f = $fotos->fetch_assoc())
                        $fotos_arr[] = $f['foto'];
                ?>
                <div class="empresa-item">
                    <div class="empresa-info-logo">
                        <div class="empresa-top-row">
                            <div class="empresa-logo">
                                <?php if ($logo): ?>
                                    <img src="/guiaempresarial.pe/assets/img/<?= $logo ?>"
                                        alt="<?= htmlspecialchars($fila['nombre']) ?>">
                                <?php else: ?>
                                    <div class="logo-placeholder"><?= mb_strtoupper(mb_substr($fila['nombre'], 0, 1)) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="empresa-titles">
                                <h3><?= htmlspecialchars($fila['nombre']) ?></h3>
                                <span class="empresa-categoria"><?= htmlspecialchars($fila['categoria']) ?></span>
                            </div>
                        </div>
                        <p class="empresa-slogan">
                            <?= !empty($fila['descripcion']) ? htmlspecialchars(mb_strimwidth($fila['descripcion'], 0, 80, '…')) : 'Tu mejor opción local' ?>
                        </p>
                        <?php if (!empty($fila['direccion'])): ?>
                            <p class="empresa-direccion">📍 <?= htmlspecialchars($fila['direccion']) ?></p>
                        <?php endif; ?>
                        <div class="empresa-datos">
                            <span>🕒
                                <?= !empty($fila['horario']) ? htmlspecialchars($fila['horario']) : '9:00 AM - 6:00 PM' ?></span>
                            <?php if ($numero): ?><span>📞 <?= $numero ?></span><?php endif; ?>
                            <span>👁 <?= number_format($fila['vistas']) ?> vistas</span>
                        </div>
                        <div class="empresa-actions">
                            <a href="empresas.php?empresa=<?= $id ?>" class="btn-ver">Ver más</a>
                            <?php if ($numero): ?>
                                <a href="https://wa.me/<?= $numero ?>" target="_blank" class="btn-whatsapp">WhatsApp</a>
                            <?php endif; ?>
                            <?php if (!empty($fila['ubicacion_link'])): ?>
                                <a href="<?= htmlspecialchars($fila['ubicacion_link']) ?>" target="_blank"
                                    class="btn-maps">Ubicación</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if (count($fotos_arr) > 0): ?>
                        <div class="empresa-slider">
                            <?php foreach ($fotos_arr as $i => $foto): ?>
                                <div class="slide <?= $i === 0 ? 'activo' : '' ?>">
                                    <img src="/guiaempresarial.pe/assets/img/empresascarrusel/<?= htmlspecialchars($foto) ?>"
                                        alt="Imagen de <?= htmlspecialchars($fila['nombre']) ?>" loading="lazy">
                                </div>
                            <?php endforeach; ?>
                            <?php if (count($fotos_arr) > 1): ?>
                                <div class="slider-dots">
                                    <?php foreach ($fotos_arr as $i => $_): ?>
                                        <button class="slider-dot <?= $i === 0 ? 'activo' : '' ?>" data-index="<?= $i ?>"></button>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile;
            echo '</div>';
        endif; ?>
        <div class="ver-mas-empresas">
            <a href="empresas.php" class="btn-ver-mas">Ver más empresas →</a>
        </div>
    </div>
</section>

<section id="categorias" class="page-section">
    <div class="container">
        <div class="section-header">
            <h1>Categorías</h1>
            <p>Explora los rubros destacados</p>
        </div>
        <div class="categorias-modern">
            <?php
            $resultado = $conexion->query("SELECT * FROM categorias LIMIT 5");
            while ($fila = $resultado->fetch_assoc()):
                $nombre = htmlspecialchars($fila['nombre']);
                $icono = htmlspecialchars($fila['icono'] ?? 'bi-briefcase');
                ?>
                <a href="empresas.php?id_categoria=<?= $fila['id_categoria'] ?>" class="categoria-card">
                    <div class="categoria-icono-wrap"><i class="bi <?= $icono ?>"></i></div>
                    <span class="categoria-nombre"><?= $nombre ?></span>
                </a>
            <?php endwhile; ?>
            <a href="categorias.php" class="categoria-card ver-mas-card">
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

    document.querySelectorAll('.empresa-slider').forEach(slider => {
        const slides = slider.querySelectorAll('.slide');
        const dots = slider.querySelectorAll('.slider-dot');
        if (slides.length <= 1) return;
        let idx = 0;
        function goTo(n) {
            slides[idx].classList.remove('activo');
            if (dots[idx]) dots[idx].classList.remove('activo');
            idx = (n + slides.length) % slides.length;
            slides[idx].classList.add('activo');
            if (dots[idx]) dots[idx].classList.add('activo');
        }
        const ap = setInterval(() => goTo(idx + 1), 4000);
        dots.forEach((d, i) => d.addEventListener('click', () => { clearInterval(ap); goTo(i); }));
    });

    const inputBuscar = document.getElementById('buscar');
    const resultadosDiv = document.getElementById('resultados');
    const formBuscar = document.getElementById('formBuscar');

    inputBuscar.addEventListener('keyup', function () {
        const q = this.value.trim();
        if (q.length > 0) fetch('buscar.php?q=' + encodeURIComponent(q)).then(r => r.text()).then(d => { resultadosDiv.innerHTML = d; });
        else resultadosDiv.innerHTML = '';
    });

    formBuscar.addEventListener('submit', function (e) {
        e.preventDefault();
        const q = inputBuscar.value.trim();
        if (q.length > 0) window.location.href = 'empresas.php?buscar=' + encodeURIComponent(q);
    });

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.search-wrapper')) {
            resultadosDiv.innerHTML = '';
        }
    });
</script>