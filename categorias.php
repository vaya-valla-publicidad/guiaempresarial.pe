<?php include 'includes/header.php'; ?>
<?php include 'db.php'; ?>

<section class="page-section">
    <div class="container">

        <div class="section-header">
            <h1>Categorías</h1>
            <p>Selecciona un rubro para explorar empresas relacionadas</p>
        </div>

        <div class="categorias-modern">
            <?php
            $resultado = $conexion->query("SELECT * FROM categorias ORDER BY nombre ASC");
            while ($fila = $resultado->fetch_assoc()):
                $nombre = htmlspecialchars($fila['nombre']);
                $icono  = htmlspecialchars($fila['icono'] ?? 'bi-briefcase');
            ?>
                <a href="empresas.php?id_categoria=<?= $fila['id_categoria'] ?>" class="categoria-card">
                    <div class="categoria-icono-wrap">
                        <i class="bi <?= $icono ?>"></i>
                    </div>
                    <span class="categoria-nombre"><?= $nombre ?></span>
                </a>
            <?php endwhile; ?>
        </div>

    </div>
</section>

<?php include 'includes/footer.php'; ?>