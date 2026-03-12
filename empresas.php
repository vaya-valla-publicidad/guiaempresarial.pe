<?php include 'includes/header.php'; ?>
<?php include 'db.php'; ?>

<section class="page-section">
    <div class="container">

        <div class="section-header">
            <h1>Empresas</h1>
            <p>Explora negocios locales y descubre nuevas oportunidades</p>
        </div>

        <?php

        $tabla = "empresas";

        $id_categoria = $_GET['id_categoria'] ?? null;
        $id_empresa   = $_GET['empresa'] ?? null;
        $buscar       = $_GET['buscar'] ?? null;

        $sql = "SELECT e.*, c.nombre AS categoria
        FROM $tabla e
        JOIN categorias c ON e.id_categoria = c.id_categoria";

        $where = [];

        if ($id_empresa) {
            $where[] = "e.id_empresa = " . intval($id_empresa);
        } elseif ($id_categoria) {
            $where[] = "e.id_categoria = " . intval($id_categoria);
        } elseif ($buscar) {
            $texto = $conexion->real_escape_string($buscar);
            $where[] = "e.nombre LIKE '%$texto%'";
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $resultado = $conexion->query($sql);

        if ($resultado && $resultado->num_rows > 0):

            while ($fila = $resultado->fetch_assoc()):

                $logo = !empty($fila['logo']) ? htmlspecialchars($fila['logo']) : 'default.png';
                $telefono = $fila['telefono'] ?? $fila['celular'] ?? $fila['whatsapp'] ?? null;
                $numero = $telefono ? preg_replace('/[^0-9]/', '', $telefono) : null;

        ?>

                <div class="empresa-perfil">

                    <div class="empresa-perfil-img">
                        <img src="assets/img/<?= $logo ?>" alt="<?= htmlspecialchars($fila['nombre']) ?>">
                    </div>

                    <div class="empresa-perfil-info">

                        <div class="empresa-perfil-header">
                            <h2><?= htmlspecialchars($fila['nombre']) ?></h2>

                            <span class="categoria-badge">
                                <?= htmlspecialchars($fila['categoria']) ?>
                            </span>
                        </div>

                        <?php if (!empty($fila['slogan'])): ?>
                            <p class="empresa-slogan">
                                <?= htmlspecialchars($fila['slogan']) ?>
                            </p>
                        <?php endif; ?>

                        <p class="empresa-direccion">
                            📍 <?= htmlspecialchars($fila['direccion'] ?? 'Dirección no disponible') ?>
                        </p>

                        <div class="empresa-info-extra">

                            <?php if (!empty($fila['horario'])): ?>
                                <span>🕒 <?= htmlspecialchars($fila['horario']) ?></span>
                            <?php endif; ?>

                            <span>⭐ <?= !empty($fila['rating']) ? htmlspecialchars($fila['rating']) : '4.5' ?></span>

                            <span>👁 <?= rand(200, 800) ?> visitas</span>

                            <?php if ($numero): ?>
                                <span>📞 <?= $numero ?></span>
                            <?php endif; ?>

                        </div>

                        <div class="empresa-botones">

                            <?php if ($numero): ?>
                                <a href="https://wa.me/<?= $numero ?>" target="_blank" class="btn-whatsapp">
                                    WhatsApp
                                </a>
                            <?php endif; ?>

                            <?php if (!empty($fila['ubicacion_link'])): ?>
                                <a href="<?= htmlspecialchars($fila['ubicacion_link']) ?>" target="_blank" class="btn-maps">
                                    Ver ubicación
                                </a>
                            <?php endif; ?>

                        </div>

                        <?php if (!empty($fila['email'])): ?>
                            <p class="empresa-email">
                                ✉ <?= htmlspecialchars($fila['email']) ?>
                            </p>
                        <?php endif; ?>

                    </div>
                </div>

                <?php if (!empty($fila['latitud']) && !empty($fila['longitud'])): ?>
                    <div class="empresa-mapa">
                        <iframe
                            src="https://www.google.com/maps?q=<?= $fila['latitud'] ?>,<?= $fila['longitud'] ?>&hl=es&z=14&output=embed"
                            loading="lazy">
                        </iframe>
                    </div>
                <?php endif; ?>

        <?php
            endwhile;

        else:
            echo "<p class='no-results'>No hay resultados disponibles.</p>";
        endif;
        ?>

    </div>
</section>

<?php include 'includes/footer.php'; ?>