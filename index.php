<?php include 'includes/header.php'; ?>

<section class="hero">
    <div class="hero-content">
        <h1>Impulsando Negocios Locales</h1>
        <p class="hero-subtitle">Descubre, conecta y potencia empresas de tu región</p>
        <p class="hero-tagline">Visibilidad real para negocios reales</p>
        <div class="hero-actions">
            <section class="search-section">
                <div class="search-wrapper">
                    <form id="formBuscar" class="search-form">
                        <div class="search-box">
                            <input type="text" id="buscar" name="q" placeholder="Buscar empresas, productos o servicios...">
                            <button type="submit">Buscar</button>
                        </div>
                    </form>
                    <div id="resultados" class="empresas-grid resultados-live"></div>
                </div>
            </section>
        </div>
    </div>
</section>

<section id="empresas" class="page-section empresas-section">
  <div class="container">
    <div class="section-header">
      <h1>Empresas</h1>
      <p>Explora negocios locales y descubre nuevas oportunidades</p>
    </div>

    <?php
    include 'db.php';
    $sql = "SELECT e.*, c.nombre AS categoria
            FROM empresas e
            JOIN categorias c ON e.id_categoria = c.id_categoria
            LIMIT 6";
    $resultado = $conexion->query($sql);

    if ($resultado && $resultado->num_rows > 0):
        echo '<div class="empresas-list">';
        while ($fila = $resultado->fetch_assoc()):
            $logo     = !empty($fila['logo']) ? htmlspecialchars($fila['logo']) : 'default.png';
            $telefono = $fila['telefono'] ?? $fila['celular'] ?? $fila['whatsapp'] ?? null;
            $numero   = $telefono ? preg_replace('/[^0-9]/', '', $telefono) : null;
            $id       = intval($fila['id_empresa']);
            $fotos    = $conexion->query("SELECT foto FROM empresa_galeria WHERE id_empresa = $id ORDER BY orden ASC, id_foto ASC");
            $fotos_arr = [];
            if ($fotos && $fotos->num_rows > 0)
                while ($f = $fotos->fetch_assoc()) $fotos_arr[] = $f['foto'];
    ?>
        <div class="empresa-item">

            <div class="empresa-info-logo">
                <div class="empresa-top-row">
                    <div class="empresa-logo">
                        <img src="assets/img/<?= $logo ?>" alt="<?= htmlspecialchars($fila['nombre']) ?>">
                    </div>
                    <div class="empresa-titles">
                        <h3><?= htmlspecialchars($fila['nombre']) ?></h3>
                        <span class="empresa-categoria"><?= htmlspecialchars($fila['categoria']) ?></span>
                    </div>
                </div>

                <p class="empresa-slogan"><?= !empty($fila['slogan']) ? htmlspecialchars($fila['slogan']) : 'Tu mejor opción local' ?></p>
                <p class="empresa-direccion">📍 <?= htmlspecialchars($fila['direccion'] ?? '') ?></p>

                <div class="empresa-datos">
                    <span>🕒 <?= !empty($fila['horario']) ? htmlspecialchars($fila['horario']) : '9:00 AM - 6:00 PM' ?></span>
                    <?php if ($numero): ?><span>📞 <?= $numero ?></span><?php endif; ?>
                </div>

                <div class="empresa-actions">
                    <a href="empresas.php?empresa=<?= $id ?>" class="btn-ver">Ver más</a>
                    <?php if ($numero): ?>
                    <a href="https://wa.me/<?= $numero ?>" target="_blank" class="btn-whatsapp">WhatsApp</a>
                    <?php endif; ?>
                    <?php if (!empty($fila['ubicacion_link'])): ?>
                    <a href="<?= htmlspecialchars($fila['ubicacion_link']) ?>" target="_blank" class="btn-maps">Ubicación</a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (count($fotos_arr) > 0): ?>
            <div class="empresa-slider">
                <?php foreach ($fotos_arr as $i => $foto): ?>
                <div class="slide <?= $i === 0 ? 'activo' : '' ?>">
                    <img src="assets/img/empresascarrusel/<?= htmlspecialchars($foto) ?>"
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
    <?php
        endwhile;
        echo '</div>';
    ?>

    <div class="ver-mas-empresas">
        <a href="empresas.php" class="btn-ver-mas">Ver más empresas →</a>
    </div>

    <?php else: ?>
        <p class="no-results">No hay resultados disponibles.</p>
    <?php endif; ?>

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
            ?>
                <a href="empresas.php?id_categoria=<?= $fila['id_categoria'] ?>" class="categoria-card">
                    <?= htmlspecialchars($fila['nombre']) ?>
                </a>
            <?php endwhile; ?>
            <a href="categorias.php" class="categoria-card ver-mas-card">Ver más categorías</a>
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
document.querySelectorAll('.empresa-slider').forEach(slider => {
    const slides = slider.querySelectorAll('.slide');
    const dots   = slider.querySelectorAll('.slider-dot');
    if (slides.length <= 1) return;
    let idx = 0;
    function goTo(n) {
        slides[idx].classList.remove('activo');
        if (dots[idx]) dots[idx].classList.remove('activo');
        idx = (n + slides.length) % slides.length;
        slides[idx].classList.add('activo');
        if (dots[idx]) dots[idx].classList.add('activo');
    }
    const autoplay = setInterval(() => goTo(idx + 1), 4000);
    dots.forEach((dot, i) => dot.addEventListener('click', () => { clearInterval(autoplay); goTo(i); }));
});

const inputBuscar   = document.getElementById('buscar');
const resultadosDiv = document.getElementById('resultados');
const formBuscar    = document.getElementById('formBuscar');

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
</script>