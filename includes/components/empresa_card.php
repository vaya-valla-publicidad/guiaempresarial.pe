<?php

function renderEmpresaCard($fila, $fotos_arr = [])
{
    $id = intval($fila['id_empresa']);
    $logo = !empty($fila['logo']) ? htmlspecialchars($fila['logo']) : null;
    $telefono = $fila['telefono'] ?? null;
    $numero = $telefono ? preg_replace('/[^0-9]/', '', $telefono) : null;
    $is_destacada = (isset($fila['destacada']) && $fila['destacada'] == 1);

    $path_img = 'assets/img/';
    $path_galeria = 'assets/img/empresascarrusel/';

    $css_class = 'empresa-item' . ($is_destacada ? ' empresa-destacada' : '');
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
                <a href="empresas.php?empresa=<?= $id ?>" class="btn-ver">Ver más</a>

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
                <?php foreach ($fotos_arr as $i => $foto): ?>
                    <div class="slide <?= $i === 0 ? 'activo' : '' ?>">
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
