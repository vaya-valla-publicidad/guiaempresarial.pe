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
        }
        elseif ($id_categoria) {
            $where[] = "e.id_categoria = " . intval($id_categoria);
        }
        elseif ($buscar) {
            $texto = $conexion->real_escape_string($buscar);
            $where[] = "e.nombre LIKE '%$texto%'";
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $resultado = $conexion->query($sql);

        if($resultado && $resultado->num_rows > 0):
            while($fila = $resultado->fetch_assoc()):
        ?>

        <div class="empresa-card-modern" onclick="toggleContacto(this)">

            <div class="empresa-header">
                <h3><?= htmlspecialchars($fila['nombre']) ?></h3>
                <span class="categoria-badge">
                    <?= htmlspecialchars($fila['categoria']) ?>
                </span>
            </div>

            <p class="empresa-direccion">
                <?= htmlspecialchars($fila['direccion'] ?? '') ?>
            </p>

            <div class="empresa-detalle">

                <?php
                $telefono = $fila['telefono'] ?? $fila['celular'] ?? $fila['whatsapp'] ?? null;
                if($telefono):
                    $numero = preg_replace('/[^0-9]/', '', $telefono);
                ?>
                    <a class="btn-gold" href="https://wa.me/<?= $numero ?>" target="_blank">
                        Contactar por WhatsApp
                    </a>
                <?php endif; ?>
                <br><br>
                <?php if(!empty($fila['email'])): ?>
                    <p><strong>Email:</strong> <?= htmlspecialchars($fila['email']) ?></p>
                <?php endif; ?>
                <br>
                <?php if(!empty($fila['direccion'])): ?>
                    <p><strong>Dirección:</strong> <?= htmlspecialchars($fila['direccion']) ?></p>
                <?php endif; ?>
                <br>
                <?php if(!empty($fila['latitud']) && !empty($fila['longitud'])): ?>
                    <iframe
                        src="https://www.google.com/maps?q=<?= $fila['latitud'] ?>,<?= $fila['longitud'] ?>&hl=es&z=14&output=embed"
                        loading="lazy">
                    </iframe>
                <?php endif; ?>

            </div>

        </div>

        <?php
            endwhile;
        else:
            echo "<p class='no-results'>No hay resultados disponibles.</p>";
        endif;
        ?>

    </div>
</section>

<script>
function toggleContacto(elemento) {
    elemento.classList.toggle("activo");
}
</script>

<?php include 'includes/footer.php'; ?>