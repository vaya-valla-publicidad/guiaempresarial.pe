<?php
include 'db.php';
include 'includes/security.php';

$q = inputLimpio($_GET['q'] ?? '');
if ($q === '')
    exit;

if (mb_strlen($q) < 3) {
}

$q_escaped = escaparLike($q);
$texto = '%' . $q_escaped . '%';

$stmt = $conexion->prepare(
    "SELECT e.id_empresa, e.nombre, e.logo, e.descripcion, e.slug, c.nombre AS categoria
     FROM empresas e
     JOIN categorias c ON e.id_categoria = c.id_categoria
     WHERE e.nombre      LIKE ?
        OR e.descripcion LIKE ?
        OR c.nombre      LIKE ?
     LIMIT 8"
);

if (!$stmt) {
    echo '<p class="buscar-noresult">Error en la búsqueda.</p>';
    exit;
}

$stmt->bind_param("sss", $texto, $texto, $texto);
$stmt->execute();
$resultado = $stmt->get_result();
$num_res = $resultado->num_rows;
if (mb_strlen($q) >= 3) {
    $termino_log = mb_substr($q, 0, 100);
    $stmt_check = $conexion->prepare(
        "SELECT 1 FROM busquedas_log 
             WHERE termino = ? AND fecha >= DATE_SUB(NOW(), INTERVAL 5 MINUTE) 
             LIMIT 1"
    );
    $stmt_check->bind_param("s", $termino_log);
    $stmt_check->execute();
    $ya_registrado = $stmt_check->get_result()->num_rows > 0;
    $stmt_check->close();

    if (!$ya_registrado) {
        $stmt_log = $conexion->prepare(
            "INSERT INTO busquedas_log (termino, resultados) VALUES (?, ?)"
        );
        if ($stmt_log) {
            $stmt_log->bind_param("si", $termino_log, $num_res);
            $stmt_log->execute();
            $stmt_log->close();
        }
    }
}

if ($resultado->num_rows === 0) {
    echo '<p class="buscar-noresult">😕 Sin resultados para <strong>' . htmlspecialchars($q) . '</strong></p>';
    exit;
}

while ($f = $resultado->fetch_assoc()):
    $id = intval($f['id_empresa']);
    $desc = !empty($f['descripcion']) ? mb_strimwidth($f['descripcion'], 0, 60, '…') : '';
    ?>
    <a href="<?= APP_URL ?>/negocio/<?= htmlspecialchars($f['slug']) ?>" class="buscar-result-item">
        <?php if (!empty($f['logo'])): ?>
            <img src="<?= APP_URL ?>/assets/img/<?= htmlspecialchars($f['logo']) ?>" alt="<?= htmlspecialchars($f['nombre']) ?>"
                class="buscar-result-logo" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
            <div class="buscar-result-logo logo-placeholder" style="display:none;">
                <?= mb_strtoupper(mb_substr($f['nombre'], 0, 1)) ?>
            </div>
        <?php else: ?>
            <div class="buscar-result-logo logo-placeholder">
                <?= mb_strtoupper(mb_substr($f['nombre'], 0, 1)) ?>
            </div>
        <?php endif; ?>
        <div class="buscar-result-info">
            <span class="buscar-result-nombre"><?= htmlspecialchars($f['nombre']) ?></span>
            <span class="buscar-result-cat"><?= htmlspecialchars($f['categoria']) ?></span>
            <?php if ($desc): ?>
                <span class="buscar-result-slogan"><?= htmlspecialchars($desc) ?></span>
            <?php endif; ?>
        </div>
    </a>
<?php endwhile;

$stmt->close();
?>