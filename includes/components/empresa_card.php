<?php

function renderEmpresaCard($fila, $fotos_arr = [])
{
    $id = intval($fila['id_empresa']);
    $logo = !empty($fila['logo']) ? htmlspecialchars($fila['logo']) : null;
    $telefono = $fila['telefono'] ?? null;
    $numero = $telefono ? preg_replace('/[^0-9]/', '', $telefono) : null;
    $is_destacada = (isset($fila['destacada']) && $fila['destacada'] == 1);

    $path_img = APP_URL . '/assets/img/';
    $path_galeria = APP_URL . '/assets/img/empresascarrusel/';

    $css_class = 'empresa-item reveal' . ($is_destacada ? ' empresa-destacada' : '');
    ?>
    <div class="<?= $css_class ?>">
        <div class="empresa-info-logo">
            <div class="empresa-top-row">
                <div class="empresa-logo">
                    <?php if ($logo): ?>
                        <img src="<?= $path_img . $logo ?>" alt="<?= htmlspecialchars($fila['nombre']) ?>">
                    <?php else: ?>
                        <div class="logo-placeholder"><?= mb_strtoupper(mb_substr($fila['nombre'], 0, 1)) ?></div>
                    <?php endif; ?>
                </div>
                <div class="empresa-titles">
                    <h3><?= htmlspecialchars($fila['nombre']) ?>
                        <?php if ($is_destacada): ?><span class="badge-destacada">⭐ Destacada</span><?php endif; ?>
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
                <?php if (!empty($fila['horario'])): ?>
                    <span>🕒 <?= htmlspecialchars($fila['horario']) ?></span>
                <?php endif; ?>
                <?php if ($numero): ?><span>📞 <?= $numero ?></span><?php endif; ?>
                <span>👁 <?= number_format($fila['vistas']) ?> vistas</span>
            </div>
            <div class="empresa-actions">
                <a href="<?= APP_URL ?>/negocio/<?= htmlspecialchars($fila['slug']) ?>" class="btn-ver">Ver más</a>

                <?php if (!empty($fila['link_empresa'])): ?>
                    <a href="<?= htmlspecialchars($fila['link_empresa']) ?>" target="_blank" class="btn-ver"
                        style="background-color: #3b82f6; color: white;">🌐 Sitio Web</a>
                <?php endif; ?>

                <?php if (!empty($fila['facebook'])): ?>
                    <a href="<?= htmlspecialchars($fila['facebook']) ?>" target="_blank"
                        class="btn-accion btn-accion-facebook">📘 Facebook</a>
                <?php endif; ?>

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
                <?php
                global $user_favs;
                $favs_check = is_array($user_favs) ? $user_favs : [];
                $es_favorito = in_array((int) $id, $favs_check);
                if (isset($_SESSION['usuario_publico_id'])):
                    ?>
                    <button class="btn-favorito <?= $es_favorito ? 'activo' : '' ?>" onclick="toggleFavorito(event, <?= $id ?>)"
                        data-id="<?= $id ?>" title="<?= $es_favorito ? 'Quitar de favoritos' : 'Guardar en favoritos' ?>">
                        <i class="bi <?= $es_favorito ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                    </button>
                <?php endif; ?>

                <?php foreach ($fotos_arr as $i => $foto): ?>
                    <div class="slide <?= $i === 0 ? 'activo' : '' ?>"
                        style="background-image: url('<?= $path_galeria . htmlspecialchars($foto) ?>');">
                        <img src="<?= $path_galeria . htmlspecialchars($foto) ?>"
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
}


function renderFavoritoCard($fila, $fotos_arr = [])
{
    $id = intval($fila['id_empresa']);
    $path_galeria = APP_URL . '/assets/img/empresascarrusel/';
    $display_foto = !empty($fotos_arr[0]) ? $path_galeria . htmlspecialchars($fotos_arr[0]) : null;
    $slug = htmlspecialchars($fila['slug']);
    ?>
    <div class="fav-poster-card empresa-item" data-id="<?= $id ?>">
        <?php if ($display_foto): ?>
            <img src="<?= $display_foto ?>" alt="<?= htmlspecialchars($fila['nombre']) ?>" class="fav-p-bg" loading="lazy">
        <?php else: ?>
            <div class="fav-p-placeholder">
                <i class="bi bi-building"></i>
                <span><?= htmlspecialchars($fila['nombre']) ?></span>
            </div>
        <?php endif; ?>

        <div class="fav-p-overlay">
            <button class="fav-p-remove" onclick="toggleFavorito(event, <?= $id ?>)" title="Quitar">
                <i class="bi bi-heart-fill"></i>
            </button>

            <div class="fav-p-info">
                <span class="fav-p-cat"><?= htmlspecialchars($fila['categoria']) ?></span>
                <h3 class="fav-p-title"><?= htmlspecialchars($fila['nombre']) ?></h3>
                <div class="fav-p-actions">
                    <a href="<?= APP_URL ?>/negocio/<?= $slug ?>" class="fav-p-btn">Ver Detalles</a>
                </div>
            </div>
        </div>
    </div>
    <?php
}
