<?php
include 'db.php';

$q = $_GET['q'] ?? '';
$q = trim($q);

if ($q === '') exit;

$texto     = $conexion->real_escape_string($q);
$resultado = $conexion->query(
    "SELECT e.id_empresa, e.nombre, e.logo, e.descripcion, c.nombre AS categoria
     FROM empresas e
     JOIN categorias c ON e.id_categoria = c.id_categoria
     WHERE e.nombre      LIKE '%$texto%'
        OR e.descripcion LIKE '%$texto%'
        OR c.nombre      LIKE '%$texto%'
     LIMIT 8"
);

if (!$resultado || $resultado->num_rows === 0) {
    echo '<p class="buscar-noresult">😕 Sin resultados para <strong>' . htmlspecialchars($q) . '</strong></p>';
    exit;
}

while ($f = $resultado->fetch_assoc()):
    $logo = !empty($f['logo']) ? htmlspecialchars($f['logo']) : 'default.png';
    $id   = intval($f['id_empresa']);
    $desc = !empty($f['descripcion']) ? mb_strimwidth($f['descripcion'], 0, 60, '…') : '';
?>
<a href="empresas.php?empresa=<?= $id ?>" class="buscar-result-item">
    <img src="assets/img/<?= $logo ?>" alt="<?= htmlspecialchars($f['nombre']) ?>" class="buscar-result-logo">
    <div class="buscar-result-info">
        <span class="buscar-result-nombre"><?= htmlspecialchars($f['nombre']) ?></span>
        <span class="buscar-result-cat"><?= htmlspecialchars($f['categoria']) ?></span>
        <?php if ($desc): ?>
        <span class="buscar-result-slogan"><?= htmlspecialchars($desc) ?></span>
        <?php endif; ?>
    </div>
</a>
<?php endwhile; ?>