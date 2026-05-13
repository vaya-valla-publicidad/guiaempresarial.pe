<?php require_once __DIR__ . '/proteger.php'; ?>
<?php include '../db.php'; ?>

<?php
require_once __DIR__ . '/../includes/security.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar'])) {
    if (!validarCSRF($_POST['csrf_token'] ?? '')) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => 'Token CSRF inválido.']);
        exit;
    }
    $id_resena = intval($_POST['eliminar']);
    $id_emp_back = intval($_POST['empresa'] ?? 0);
    $stmt_votes = $conexion->prepare("DELETE FROM resena_votos WHERE id_resena = ?");
    $stmt_votes->bind_param("i", $id_resena);
    $stmt_votes->execute();
    $stmt_votes->close();
    $stmt = $conexion->prepare("DELETE FROM resenas WHERE id_resena = ?");
    $stmt->bind_param("i", $id_resena);
    $stmt->execute();
    $stmt->close();

    if (!empty($_POST['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
        exit;
    }
    header("Location: gestionar_resenas.php?empresa=$id_emp_back&ok=1#resenas");
    exit;
}

$id_empresa = intval($_GET['empresa'] ?? 0);

if (!$id_empresa):
    $empresas_q = $conexion->query("
        SELECT DISTINCT e.id_empresa, e.nombre, e.logo,
               COUNT(r.id_resena) as total_resenas,
               ROUND(AVG(r.estrellas),1) as promedio
        FROM empresas e
        INNER JOIN resenas r ON r.id_empresa = e.id_empresa
        GROUP BY e.id_empresa
        ORDER BY e.nombre ASC
    ");
    ?>
    <!DOCTYPE html>
    <html lang="es">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Gestionar Reseñas</title>
        <link rel="icon" href="<?= APP_URL ?>/assets/img/image.png" type="image/png">
        <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/login.css?v=<?= filemtime(__DIR__ . '/../assets/css/login.css') ?>">
    </head>

    <body>
        <div class="panel-container">
            <section class="panel panel-narrow">
                <h1 class="panel-title">Gestionar Reseñas</h1>
                <div class="form-container">
                    <p style="color:#666;">Selecciona una empresa para ver y gestionar sus reseñas.</p>

                    <?php if ($empresas_q && $empresas_q->num_rows > 0): ?>
                        <div class="empresa-sel-lista">
                            <?php while ($emp = $empresas_q->fetch_assoc()): ?>
                                <a href="gestionar_resenas.php?empresa=<?= $emp['id_empresa'] ?>" class="empresa-sel-item">
                                    <?php if (!empty($emp['logo'])): ?>
                                        <img src="<?= APP_URL ?>/assets/img/<?= htmlspecialchars($emp['logo']) ?>"
                                            class="empresa-sel-logo">
                                    <?php else: ?>
                                        <div class="logo-letra"><?= mb_strtoupper(mb_substr($emp['nombre'], 0, 1)) ?></div>
                                    <?php endif; ?>
                                    <div class="empresa-sel-info">
                                        <div class="empresa-sel-nombre"><?= htmlspecialchars($emp['nombre']) ?></div>
                                        <div class="empresa-sel-meta">
                                            <span class="empresa-sel-estrella">★</span> <?= $emp['promedio'] ?>
                                            &nbsp;·&nbsp; <?= $emp['total_resenas'] ?>
                                            reseña<?= $emp['total_resenas'] > 1 ? 's' : '' ?>
                                        </div>
                                    </div>
                                    <span class="item-chevron">›</span>
                                </a>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="sin-resenas">😕 No hay reseñas registradas todavía.</div>
                    <?php endif; ?>

                    <br>
                    <a href="admin.php" class="btn btn-danger-alt">
                        <i class="bi bi-arrow-left"></i> Volver al Panel
                    </a>
                </div>
            </section>
        </div>
    </body>

    </html>

    <?php
    return;
endif;

$stmt_emp = $conexion->prepare("
    SELECT e.*, c.nombre AS categoria
    FROM empresas e
    JOIN categorias c ON e.id_categoria = c.id_categoria
    WHERE e.id_empresa = ?
");
$stmt_emp->bind_param("i", $id_empresa);
$stmt_emp->execute();
$res = $stmt_emp->get_result();
if (!$res || $res->num_rows === 0) {
    header("Location: gestionar_resenas.php");
    exit;
}
$fila = $res->fetch_assoc();
$logo = !empty($fila['logo']) ? htmlspecialchars($fila['logo']) : null;
$telefono = $fila['telefono'] ?? null;
$numero = $telefono ? preg_replace('/[^0-9]/', '', $telefono) : null;

$stmt_fotos = $conexion->prepare("SELECT foto FROM empresa_galeria WHERE id_empresa = ? ORDER BY orden ASC, id_foto ASC");
$stmt_fotos->bind_param("i", $id_empresa);
$stmt_fotos->execute();
$fotos_q = $stmt_fotos->get_result();
$fotos_arr = [];
if ($fotos_q && $fotos_q->num_rows > 0)
    while ($f = $fotos_q->fetch_assoc())
        $fotos_arr[] = $f['foto'];

$stmt_resenas = $conexion->prepare("SELECT * FROM resenas WHERE id_empresa = ? ORDER BY fecha DESC");
$stmt_resenas->bind_param("i", $id_empresa);
$stmt_resenas->execute();
$resenas_q = $stmt_resenas->get_result();
$total_resenas = $resenas_q ? $resenas_q->num_rows : 0;
$promedio = 0;
$resenas_arr = [];
if ($total_resenas > 0) {
    $stmt_prom = $conexion->prepare("SELECT AVG(estrellas) as prom FROM resenas WHERE id_empresa = ?");
    $stmt_prom->bind_param("i", $id_empresa);
    $stmt_prom->execute();
    $sum_q = $stmt_prom->get_result();
    $promedio = round($sum_q->fetch_assoc()['prom'], 1);
    while ($r = $resenas_q->fetch_assoc())
        $resenas_arr[] = $r;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reseñas — <?= htmlspecialchars($fila['nombre']) ?></title>
    <link rel="icon" href="<?= APP_URL ?>/assets/img/image.png" type="image/png">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>

    <div class="admin-topbar">
        <a href="gestionar_resenas.php">← Volver a empresas</a>
        <span>|</span>
        <a href="admin.php">Panel Admin</a>
        <span style="margin-left:auto;color:#aaa;font-size:13px;">
            Modo gestión — <?= htmlspecialchars($_SESSION['usuario']) ?>
        </span>
    </div>

    <section class="empresas-page-section">
        <div class="container">

            <?php if (isset($_GET['ok'])): ?>
                <div class="alerta-ok">✅ Reseña eliminada correctamente.</div>
            <?php endif; ?>

            <div class="perfil-wrapper">
                <div class="perfil-hero">
                    <div class="perfil-banner"></div>
                    <div class="perfil-hero-body">
                        <?php if ($logo): ?>
                            <img class="perfil-logo" src="<?= APP_URL ?>/assets/img/<?= $logo ?>"
                                alt="<?= htmlspecialchars($fila['nombre']) ?>">
                        <?php else: ?>
                            <div class="perfil-logo logo-placeholder" style="width:90px;height:90px;font-size:32px;">
                                <?= mb_strtoupper(mb_substr($fila['nombre'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                        <div class="perfil-hero-info">
                            <div class="perfil-hero-top">
                                <div>
                                    <h2 class="perfil-nombre">
                                        <?= htmlspecialchars($fila['nombre']) ?>
                                        <?php if ($fila['destacada']): ?>
                                            <span class="badge-destacada">⭐ Destacada</span>
                                        <?php endif; ?>
                                    </h2>
                                    <span class="empresa-card-badge"><?= htmlspecialchars($fila['categoria']) ?></span>
                                </div>
                            </div>
                            <?php if (!empty($fila['descripcion'])): ?>
                                <p class="perfil-slogan">✨
                                    <?= htmlspecialchars(mb_strimwidth($fila['descripcion'], 0, 100, '…')) ?>
                                </p>
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
                        </div>
                    </div>

                    <?php if (count($fotos_arr) > 0): ?>
                        <div class="perfil-galeria-card">
                            <p class="perfil-section-label">Galería</p>
                            <div class="perfil-galeria-grid">
                                <?php foreach ($fotos_arr as $foto): ?>
                                    <img src="<?= APP_URL ?>/assets/img/empresascarrusel/<?= htmlspecialchars($foto) ?>"
                                        alt="Foto" class="perfil-galeria-foto">
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

                <div class="perfil-resenas" id="resenas">
                    <p class="perfil-section-label">
                        Reseñas
                        <span style="font-size:12px;color:#888;font-weight:400;margin-left:8px;">
                            (<?= $total_resenas ?> en total)
                        </span>
                    </p>

                    <?php if ($total_resenas > 0): ?>
                        <div class="resenas-promedio">
                            <span class="resenas-prom-numero"><?= $promedio ?></span>
                            <div>
                                <div class="estrellas-display">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span
                                            class="<?= $i <= round($promedio) ? 'estrella-llena' : 'estrella-vacia' ?>">★</span>
                                    <?php endfor; ?>
                                </div>
                                <span class="resenas-total"><?= $total_resenas ?>
                                    reseña<?= $total_resenas > 1 ? 's' : '' ?></span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (count($resenas_arr) > 0): ?>
                        <div class="resenas-lista">
                            <?php foreach ($resenas_arr as $r): ?>
                                <div class="resena-item">
                                    <div class="resena-header">
                                        <div class="resena-avatar"><?= mb_strtoupper(mb_substr($r['nombre_autor'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <strong><?= htmlspecialchars($r['nombre_autor']) ?></strong>
                                            <div class="estrellas-display small">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <span
                                                        class="<?= $i <= $r['estrellas'] ? 'estrella-llena' : 'estrella-vacia' ?>">★</span>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <span class="resena-fecha"><?= date('d/m/Y', strtotime($r['fecha'])) ?></span>
                                    </div>
                                    <p class="resena-comentario"><?= nl2br(htmlspecialchars($r['comentario'])) ?></p>
                                    <button class="btn btn-danger"
                                        onclick="eliminarResena(<?= $r['id_resena'] ?>, <?= $id_empresa ?>, this)"
                                        style="font-size:11px !important; padding: 6px 15px !important; margin-top: 10px;">
                                        <i class="bi bi-trash3"></i> Eliminar reseña
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p style="color:#aaa;font-size:14px;">Esta empresa no tiene reseñas aún.</p>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </section>

    <script src="<?= APP_URL ?>/assets/js/toast.js"></script>
    <script>
        function eliminarResena(idResena, idEmpresa, btn) {
            customConfirm('¿Seguro que deseas eliminar esta reseña permanentemente?', () => {
                btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Eliminando...';
                btn.disabled = true;
                btn.style.opacity = '0.5';

                const fd = new FormData();
                fd.append('eliminar', idResena);
                fd.append('empresa', idEmpresa);
                fd.append('ajax', '1');
                fd.append('csrf_token', '<?= generarTokenCSRF() ?>');

                fetch('gestionar_resenas.php', {
                    method: 'POST',
                    body: fd
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.ok) {
                            const resenaDiv = btn.closest('.resena-item');
                            if (resenaDiv) {
                                resenaDiv.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                                resenaDiv.style.opacity = '0';
                                resenaDiv.style.transform = 'translateX(30px)';
                                setTimeout(() => resenaDiv.remove(), 400);
                            }
                            showToast('Reseña eliminada correctamente.', 'success');
                        } else {
                            showToast(data.error || 'No se pudo eliminar la reseña.', 'error');
                            btn.innerHTML = '<i class="bi bi-trash3"></i> Eliminar reseña';
                            btn.disabled = false;
                            btn.style.opacity = '1';
                        }
                    })
                    .catch(err => {
                        showToast('Error de conexión con el servidor.', 'error');
                        btn.innerHTML = '<i class="bi bi-trash3"></i> Eliminar reseña';
                        btn.disabled = false;
                        btn.style.opacity = '1';
                    });
            });
        }
    </script>

</body>

</html>