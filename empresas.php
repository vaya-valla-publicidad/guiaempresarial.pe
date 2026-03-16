<?php include 'includes/header.php'; ?>
<?php include 'db.php'; ?>

<section class="empresas-page-section">
    <div class="container">

        <div class="section-header">
            <h1>Empresas</h1>
            <p>Explora negocios locales y descubre nuevas oportunidades</p>
        </div>

        <?php
        $id_categoria = $_GET['id_categoria'] ?? null;
        $id_empresa   = $_GET['empresa']      ?? null;
        $buscar       = $_GET['buscar']        ?? null;

        $sql = "SELECT e.*, c.nombre AS categoria
                FROM empresas e
                JOIN categorias c ON e.id_categoria = c.id_categoria";

        $where = [];
        if ($id_empresa)       $where[] = "e.id_empresa = " . intval($id_empresa);
        elseif ($id_categoria) $where[] = "e.id_categoria = " . intval($id_categoria);
        elseif ($buscar) {
            $texto   = $conexion->real_escape_string($buscar);
            $where[] = "(e.nombre LIKE '%$texto%' OR e.slogan LIKE '%$texto%' OR c.nombre LIKE '%$texto%')";
        }
        if (!empty($where)) $sql .= " WHERE " . implode(" AND ", $where);

        $resultado = $conexion->query($sql);

        if ($id_empresa && $resultado && $resultado->num_rows === 1):
            $fila     = $resultado->fetch_assoc();
            $logo     = !empty($fila['logo']) ? htmlspecialchars($fila['logo']) : 'default.png';
            $telefono = $fila['telefono'] ?? $fila['celular'] ?? $fila['whatsapp'] ?? null;
            $numero   = $telefono ? preg_replace('/[^0-9]/', '', $telefono) : null;
            $fotos_q  = $conexion->query("SELECT foto FROM empresa_galeria WHERE id_empresa = " . intval($id_empresa) . " ORDER BY orden ASC, id_foto ASC");
            $fotos_arr = [];
            if ($fotos_q && $fotos_q->num_rows > 0)
                while ($f = $fotos_q->fetch_assoc()) $fotos_arr[] = $f['foto'];
        ?>

        <a href="empresas.php" class="btn-volver">← Volver a empresas</a>

        <div class="perfil-wrapper">
            <div class="perfil-hero">
                <div class="perfil-banner"></div>
                <div class="perfil-hero-body">
                    <img class="perfil-logo" src="assets/img/<?= $logo ?>" alt="<?= htmlspecialchars($fila['nombre']) ?>">
                    <div class="perfil-hero-info">
                        <div class="perfil-hero-top">
                            <div>
                                <h2 class="perfil-nombre"><?= htmlspecialchars($fila['nombre']) ?></h2>
                                <span class="empresa-card-badge"><?= htmlspecialchars($fila['categoria']) ?></span>
                            </div>
                            <div class="perfil-acciones">
                                <?php if ($numero): ?>
                                <a href="https://wa.me/<?= $numero ?>" target="_blank" class="btn-accion btn-accion-whatsapp">WhatsApp</a>
                                <?php endif; ?>
                                <?php if (!empty($fila['ubicacion_link'])): ?>
                                <a href="<?= htmlspecialchars($fila['ubicacion_link']) ?>" target="_blank" class="btn-accion btn-accion-maps">📍 Ver en Maps</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if (!empty($fila['slogan'])): ?>
                        <p class="perfil-slogan">✨ <?= htmlspecialchars($fila['slogan']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="perfil-body">
                <div class="perfil-info-card">
                    <p class="perfil-section-label">Información</p>
                    <div class="perfil-datos">
                        <?php if (!empty($fila['direccion'])): ?>
                        <div class="perfil-dato-item">
                            <span class="perfil-dato-icon">📍</span>
                            <div>
                                <span class="perfil-dato-label">Dirección</span>
                                <span class="perfil-dato-valor"><?= htmlspecialchars($fila['direccion']) ?></span>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($fila['horario'])): ?>
                        <div class="perfil-dato-item">
                            <span class="perfil-dato-icon">🕒</span>
                            <div>
                                <span class="perfil-dato-label">Horario</span>
                                <span class="perfil-dato-valor"><?= htmlspecialchars($fila['horario']) ?></span>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if ($numero): ?>
                        <div class="perfil-dato-item">
                            <span class="perfil-dato-icon">📞</span>
                            <div>
                                <span class="perfil-dato-label">Teléfono</span>
                                <span class="perfil-dato-valor"><?= $numero ?></span>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($fila['email'])): ?>
                        <div class="perfil-dato-item">
                            <span class="perfil-dato-icon">✉</span>
                            <div>
                                <span class="perfil-dato-label">Correo</span>
                                <span class="perfil-dato-valor"><?= htmlspecialchars($fila['email']) ?></span>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($fila['link_empresa'])): ?>
                        <div class="perfil-dato-item">
                            <span class="perfil-dato-icon">🌐</span>
                            <div>
                                <span class="perfil-dato-label">Sitio web</span>
                                <a class="perfil-dato-valor perfil-link" href="<?= htmlspecialchars($fila['link_empresa']) ?>" target="_blank"><?= htmlspecialchars($fila['link_empresa']) ?></a>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (count($fotos_arr) > 0): ?>
                <div class="perfil-galeria-card">
                    <p class="perfil-section-label">Galería</p>
                    <div class="perfil-galeria-grid">
                        <?php foreach ($fotos_arr as $foto): ?>
                        <img src="assets/img/empresascarrusel/<?= htmlspecialchars($foto) ?>"
                             alt="Foto de <?= htmlspecialchars($fila['nombre']) ?>"
                             class="perfil-galeria-foto">
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($fila['descripcion'])): ?>
            <div class="perfil-descripcion">
                <p class="perfil-section-label">Descripción</p>
                <p class="perfil-descripcion-texto"><?= nl2br(htmlspecialchars($fila['descripcion'])) ?></p>
            </div>
            <?php endif; ?>
        </div>

        <?php
        elseif ($resultado && $resultado->num_rows > 0):

            if ($buscar): ?>
            <div class="filtro-activo">
                🔍 Resultados para: "<?= htmlspecialchars($buscar) ?>"
                <a href="empresas.php" title="Limpiar">✕</a>
            </div>
            <?php elseif ($id_categoria):
                $cat_res    = $conexion->query("SELECT nombre FROM categorias WHERE id_categoria=" . intval($id_categoria));
                $cat_nombre = $cat_res ? $cat_res->fetch_assoc()['nombre'] : 'Categoría';
            ?>
            <div class="filtro-activo">
                🏷 Categoría: <?= htmlspecialchars($cat_nombre) ?>
                <a href="empresas.php" title="Ver todas">✕</a>
            </div>
            <?php endif; ?>

        <div class="empresas-list">
        <?php
            while ($fila = $resultado->fetch_assoc()):
                $logo      = !empty($fila['logo']) ? htmlspecialchars($fila['logo']) : 'default.png';
                $telefono  = $fila['telefono'] ?? $fila['celular'] ?? $fila['whatsapp'] ?? null;
                $numero    = $telefono ? preg_replace('/[^0-9]/', '', $telefono) : null;
                $id        = intval($fila['id_empresa']);
                $fotos     = $conexion->query("SELECT foto FROM empresa_galeria WHERE id_empresa = $id ORDER BY orden ASC, id_foto ASC");
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

                    <p class="empresa-slogan">
                        <?= !empty($fila['slogan']) ? htmlspecialchars($fila['slogan']) : 'Tu mejor opción local' ?>
                    </p>

                    <?php if (!empty($fila['direccion'])): ?>
                    <p class="empresa-direccion">📍 <?= htmlspecialchars($fila['direccion']) ?></p>
                    <?php endif; ?>

                    <div class="empresa-datos">
                        <?php if (!empty($fila['horario'])): ?>
                        <span>🕒 <?= htmlspecialchars($fila['horario']) ?></span>
                        <?php endif; ?>
                        <?php if ($numero): ?>
                        <span>📞 <?= $numero ?></span>
                        <?php endif; ?>
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
                             alt="Imagen de <?= htmlspecialchars($fila['nombre']) ?>"
                             loading="lazy">
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
        <?php endwhile; ?>
        </div>

        <?php else: ?>
        <div class="no-results">
            <p>😕 No se encontraron empresas<?= $buscar ? ' para "<strong>' . htmlspecialchars($buscar) . '</strong>"' : '' ?>.</p>
            <br><br>
            <a href="empresas.php">Ver todas las empresas</a>
        </div>
        <?php endif; ?>

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
</script>