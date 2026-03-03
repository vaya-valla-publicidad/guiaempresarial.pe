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
            $resultado = $conexion->query("SELECT * FROM categorias");
            while($fila = $resultado->fetch_assoc()):
            ?>
                <a href="empresas.php?id_categoria=<?= $fila['id_categoria'] ?>" class="categoria-card">
                    <?= htmlspecialchars($fila['nombre']) ?>
                </a>
            <?php endwhile; ?>
        </div>

    </div>
</section>

<?php include 'includes/footer.php'; ?>