<?php
require_once __DIR__ . '/proteger.php';
include '../db.php';
require_once __DIR__ . '/../includes/security.php';

$rol = $_SESSION['rol'];

// --- Acciones AJAX POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    if (!validarCSRF($_POST['csrf_token'] ?? '')) {
        echo json_encode(['ok' => false, 'error' => 'Token CSRF inválido.']);
        exit;
    }

    $accion = $_POST['accion'] ?? '';

    if ($accion === 'agregar') {
        $texto = trim($_POST['texto'] ?? '');
        $id_cat = intval($_POST['id_categoria'] ?? 0);
        if ($texto === '') {
            echo json_encode(['ok' => false, 'error' => 'El texto es obligatorio.']);
            exit;
        }
        $max_orden = $conexion->query("SELECT COALESCE(MAX(orden),0)+1 as next FROM burbujas_busqueda")->fetch_assoc()['next'];
        $stmt = $conexion->prepare("INSERT INTO burbujas_busqueda (texto, id_categoria, orden) VALUES (?, ?, ?)");
        $stmt->bind_param("sii", $texto, $id_cat, $max_orden);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['ok' => true]);
        exit;
    }

    if ($accion === 'toggle') {
        $id = intval($_POST['id'] ?? 0);
        $conexion->prepare("UPDATE burbujas_busqueda SET activo = NOT activo WHERE id_burbuja = ?")->bind_param("i", $id) || true;
        $stmt = $conexion->prepare("UPDATE burbujas_busqueda SET activo = NOT activo WHERE id_burbuja = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['ok' => true]);
        exit;
    }

    if ($accion === 'eliminar') {
        $id = intval($_POST['id'] ?? 0);
        $stmt = $conexion->prepare("DELETE FROM burbujas_busqueda WHERE id_burbuja = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['ok' => true]);
        exit;
    }
    if ($accion === 'editar') {
        $id = intval($_POST['id'] ?? 0);
        $texto = trim($_POST['texto'] ?? '');
        $id_cat = intval($_POST['id_categoria'] ?? 0);
        if ($texto === '') {
            echo json_encode(['ok' => false, 'error' => 'El texto es obligatorio.']);
            exit;
        }
        $stmt = $conexion->prepare("UPDATE burbujas_busqueda SET texto = ?, id_categoria = ? WHERE id_burbuja = ?");
        $stmt->bind_param("sii", $texto, $id_cat, $id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['ok' => true]);
        exit;
    }

    if ($accion === 'mover') {
        $id = intval($_POST['id'] ?? 0);
        $dir = $_POST['dir'] ?? '';

        $actual = $conexion->prepare("SELECT orden FROM burbujas_busqueda WHERE id_burbuja = ?");
        $actual->bind_param("i", $id);
        $actual->execute();
        $r = $actual->get_result()->fetch_assoc();
        if (!$r) {
            echo json_encode(['ok' => false, 'error' => 'No encontrado']);
            exit;
        }
        $orden_actual = intval($r['orden']);
        $actual->close();

        if ($dir === 'subir') {
            $vecino = $conexion->prepare("SELECT id_burbuja, orden FROM burbujas_busqueda WHERE orden < ? ORDER BY orden DESC LIMIT 1");
        } else {
            $vecino = $conexion->prepare("SELECT id_burbuja, orden FROM burbujas_busqueda WHERE orden > ? ORDER BY orden ASC LIMIT 1");
        }
        $vecino->bind_param("i", $orden_actual);
        $vecino->execute();
        $rv = $vecino->get_result()->fetch_assoc();
        $vecino->close();

        if (!$rv) {
            echo json_encode(['ok' => true]);
            exit;
        }

        $id_vecino = intval($rv['id_burbuja']);
        $orden_vecino = intval($rv['orden']);

        $s1 = $conexion->prepare("UPDATE burbujas_busqueda SET orden = ? WHERE id_burbuja = ?");
        $s1->bind_param("ii", $orden_vecino, $id);
        $s1->execute();
        $s1->close();

        $s2 = $conexion->prepare("UPDATE burbujas_busqueda SET orden = ? WHERE id_burbuja = ?");
        $s2->bind_param("ii", $orden_actual, $id_vecino);
        $s2->execute();
        $s2->close();

        echo json_encode(['ok' => true]);
        exit;
    }

    if ($accion === 'reiniciar_clics') {
        $conexion->query("UPDATE burbujas_busqueda SET clics = 0");
        echo json_encode(['ok' => true]);
        exit;
    }
    if ($accion === 'limpiar_log') {
        $conexion->query("DELETE FROM busquedas_log WHERE fecha < DATE_SUB(NOW(), INTERVAL 30 DAY)");
        echo json_encode(['ok' => true]);
        exit;
    }

    echo json_encode(['ok' => false, 'error' => 'Acción no válida']);
    exit;
}

$burbujas = $conexion->query("SELECT b.*, c.nombre AS categoria_nombre, c.icono FROM burbujas_busqueda b LEFT JOIN categorias c ON b.id_categoria = c.id_categoria ORDER BY b.orden ASC");
$categorias = $conexion->query("SELECT id_categoria, nombre, icono FROM categorias ORDER BY orden ASC");
$cats_arr = [];
while ($c = $categorias->fetch_assoc())
    $cats_arr[] = $c;

$top_busquedas = $conexion->query("SELECT termino, COUNT(*) as veces, SUM(resultados) as total_resultados FROM busquedas_log WHERE fecha >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY termino ORDER BY veces DESC LIMIT 10");
$sin_resultados = $conexion->query("SELECT termino, COUNT(*) as veces FROM busquedas_log WHERE resultados = 0 AND fecha >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY termino ORDER BY veces DESC LIMIT 10");
$total_busquedas_semana = $conexion->query("SELECT COUNT(*) as t FROM busquedas_log WHERE fecha >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetch_assoc()['t'] ?? 0;
$top_burbujas = $conexion->query("SELECT texto, clics FROM burbujas_busqueda WHERE activo = 1 AND clics > 0 ORDER BY clics DESC LIMIT 5");

$panel_url = ($rol === 'admin') ? 'admin.php' : 'editor.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Burbujas – Panel <?= ucfirst($rol) ?></title>
    <link rel="icon" href="<?= APP_URL ?>/assets/img/image.png" type="image/png">
    <link rel="stylesheet"
        href="<?= APP_URL ?>/assets/css/login.css?v=<?= filemtime(__DIR__ . '/../assets/css/login.css') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>
    <div class="panel-container">
        <section class="panel">

            <h1 class="panel-title"><i class="bi bi-search-heart"></i> Gestionar Burbujas de Búsqueda</h1>

            <div class="usuario-info">
                Bienvenido, <?= htmlspecialchars($_SESSION['usuario']) ?> |
                <a href="cerrar.php">Cerrar sesión</a>
            </div>

            <a href="<?= $panel_url ?>" class="btn btn-panel-nav-sep">← Volver al Panel</a>

            <div class="stats-dashboard" style="margin-top: 25px;">
                <div class="stat-box" style="border-left-color: #8b5cf6;">
                    <i class="bi bi-graph-up" style="color: #8b5cf6;"></i>
                    <span class="stat-title">Búsquedas (7 días)</span>
                    <span class="stat-value"><?= number_format($total_busquedas_semana) ?></span>
                    <span class="stat-footer">Total de búsquedas realizadas</span>
                </div>

                <div class="stat-box" style="border-left-color: #f59e0b;">
                    <i class="bi bi-fire" style="color: #f59e0b;"></i>
                    <span class="stat-title">Top Buscados</span>
                    <ul class="top5-list">
                        <?php if ($top_busquedas && $top_busquedas->num_rows > 0): ?>
                            <?php while ($tb = $top_busquedas->fetch_assoc()): ?>
                                <li class="top5-item">
                                    <span
                                        class="top5-name"><?= htmlspecialchars(mb_strimwidth($tb['termino'], 0, 25, '…')) ?></span>
                                    <span class="top5-views"><?= $tb['veces'] ?>×</span>
                                </li>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <li class="top5-item" style="color:#aaa;">Sin datos aún</li>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="stat-box" style="border-left-color: #ef4444;">
                    <i class="bi bi-emoji-frown" style="color: #ef4444;"></i>
                    <span class="stat-title">Sin Resultados</span>
                    <ul class="top5-list">
                        <?php if ($sin_resultados && $sin_resultados->num_rows > 0): ?>
                            <?php while ($sr = $sin_resultados->fetch_assoc()): ?>
                                <li class="top5-item">
                                    <span class="top5-name"
                                        style="color:#ef4444;"><?= htmlspecialchars(mb_strimwidth($sr['termino'], 0, 25, '…')) ?></span>
                                    <span class="top5-views"><?= $sr['veces'] ?>×</span>
                                </li>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <li class="top5-item" style="color:#10b981;">🎉 Todas las búsquedas encontraron resultados</li>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="stat-box" style="border-left-color: #3b82f6;">
                    <i class="bi bi-cursor-fill" style="color: #3b82f6;"></i>
                    <span class="stat-title">Burbujas Más Usadas</span>
                    <ul class="top5-list">
                        <?php if ($top_burbujas && $top_burbujas->num_rows > 0): ?>
                            <?php while ($tb = $top_burbujas->fetch_assoc()): ?>
                                <li class="top5-item">
                                    <span
                                        class="top5-name"><?= htmlspecialchars(mb_strimwidth($tb['texto'], 0, 25, '…')) ?></span>
                                    <span class="top5-views"><?= $tb['clics'] ?> clics</span>
                                </li>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <li class="top5-item" style="color:#aaa;">Sin datos aún</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <div style="display:flex; gap:10px; flex-wrap:wrap; margin:20px 0 10px;">
                <button
                    onclick="accionGlobal('reiniciar_clics', '¿Reiniciar contadores de clics de todas las burbujas?')"
                    class="btn-reiniciar-vistas">
                    <i class="bi bi-arrow-counterclockwise"></i> Reiniciar clics
                </button>
                <button onclick="accionGlobal('limpiar_log', '¿Eliminar búsquedas con más de 30 días?')"
                    class="btn-reiniciar-vistas" style="background:#fef3c7;color:#92400e;">
                    <i class="bi bi-trash3"></i> Limpiar log antiguo
                </button>
            </div>

            <div
                style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin: 25px 0;">
                <h3 style="font-size: 15px; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-plus-circle-fill" style="color: #10b981;"></i> Agregar Nueva Burbuja
                </h3>
                <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end;">
                    <div style="flex: 1; min-width: 150px;">
                        <label
                            style="font-size: 12px; font-weight: 600; color: #64748b; display: block; margin-bottom: 4px;">Texto
                            visible</label>
                        <input type="text" id="nuevoTexto" placeholder="Ej: Pizzerías" maxlength="50"
                            style="width: 100%; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px; outline: none;">
                    </div>
                    <div style="flex: 1; min-width: 150px;">
                        <label
                            style="font-size: 12px; font-weight: 600; color: #64748b; display: block; margin-bottom: 4px;">Categoría
                            asociada</label>
                        <select id="nuevaCategoria"
                            style="width: 100%; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px; outline: none; background: white;">
                            <option value="0">— Seleccionar —</option>
                            <?php foreach ($cats_arr as $ca): ?>
                                <option value="<?= $ca['id_categoria'] ?>"><?= htmlspecialchars($ca['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button onclick="agregarBurbuja()" class="btn" style="white-space: nowrap;">
                        <i class="bi bi-plus-lg"></i> Agregar
                    </button>
                </div>
            </div>

            <h2 style="font-size: 16px; margin-bottom: 15px;"><i class="bi bi-grid-3x3-gap"></i> Burbujas Activas</h2>

            <div class="table-wrap">
                <table>
                    <tr>
                        <th>Orden</th>
                        <th>Estado</th>
                        <th>Texto</th>
                        <th>Categoría</th>
                        <th>Clics</th>
                        <th>Acciones</th>
                    </tr>
                    <?php if ($burbujas && $burbujas->num_rows > 0): ?>
                        <?php while ($b = $burbujas->fetch_assoc()): ?>
                            <tr id="burbuja-<?= $b['id_burbuja'] ?>">
                                <td data-label="Orden">
                                    <div style="display:flex; align-items:center; gap:6px;">
                                        <span style="font-weight:700; color:#64748b; min-width:20px;"><?= $b['orden'] ?></span>
                                        <button onclick="moverBurbuja(<?= $b['id_burbuja'] ?>, 'subir')" class="btn-orden"
                                            title="Subir">▲</button>
                                        <button onclick="moverBurbuja(<?= $b['id_burbuja'] ?>, 'bajar')" class="btn-orden"
                                            title="Bajar">▼</button>
                                    </div>
                                </td>
                                <td data-label="Estado">
                                    <button onclick="toggleBurbuja(<?= $b['id_burbuja'] ?>)" class="btn-orden"
                                        style="font-size:18px; padding:4px 10px; border-radius:8px; background:<?= $b['activo'] ? '#dcfce7' : '#fee2e2' ?>; border:1px solid <?= $b['activo'] ? '#bbf7d0' : '#fecaca' ?>;"
                                        title="<?= $b['activo'] ? 'Desactivar' : 'Activar' ?>">
                                        <?= $b['activo'] ? '✅' : '❌' ?>
                                    </button>
                                </td>
                                <td data-label="Texto" style="font-weight:600;"><?= htmlspecialchars($b['texto']) ?></td>
                                <td data-label="Categoría">
                                    <?php if ($b['categoria_nombre']): ?>
                                        <span
                                            style="display:inline-flex;align-items:center;gap:4px;background:#f1f5f9;padding:3px 10px;border-radius:20px;font-size:12px;">
                                            <i class="bi <?= htmlspecialchars($b['icono'] ?? 'bi-tag') ?>"></i>
                                            <?= htmlspecialchars($b['categoria_nombre']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color:#aaa;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Clics">
                                    <span style="font-weight:700; color:#8b5cf6;"><?= number_format($b['clics']) ?></span>
                                </td>
                                <td data-label="Acciones">
                                    <div class="acciones">
                                        <button
                                            onclick="editarBurbuja(<?= $b['id_burbuja'] ?>, '<?= htmlspecialchars(addslashes($b['texto']), ENT_QUOTES) ?>', <?= intval($b['id_categoria'] ?? 0) ?>)"
                                            class="btn" style="font-size:11px !important; padding:6px 12px !important;">
                                            <i class="bi bi-pencil"></i> Editar
                                        </button>
                                        <button onclick="eliminarBurbuja(<?= $b['id_burbuja'] ?>)" class="btn btn-danger"
                                            style="font-size:11px !important; padding:6px 12px !important;">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center;color:#aaa;padding:30px;">No hay burbujas configuradas.
                                Agrega una arriba.</td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>

            <div id="modalEditar"
                style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
                <div
                    style="background:white; padding:20px; border-radius:12px; width:90%; max-width:400px; box-shadow:0 10px 25px rgba(0,0,0,0.2);">
                    <h3 style="margin-top:0; font-size:16px;">Editar Burbuja</h3>
                    <input type="hidden" id="editId">
                    <div style="margin-bottom:15px;">
                        <label
                            style="font-size:12px; font-weight:600; color:#64748b; display:block; margin-bottom:4px;">Texto</label>
                        <input type="text" id="editTexto"
                            style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:8px;">
                    </div>
                    <div style="margin-bottom:20px;">
                        <label
                            style="font-size:12px; font-weight:600; color:#64748b; display:block; margin-bottom:4px;">Categoría
                            asociada</label>
                        <select id="editCategoria"
                            style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:8px; background:white;">
                            <option value="0">— Ninguna —</option>
                            <?php foreach ($cats_arr as $ca): ?>
                                <option value="<?= $ca['id_categoria'] ?>"><?= htmlspecialchars($ca['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="display:flex; justify-content:flex-end; gap:10px;">
                        <button onclick="document.getElementById('modalEditar').style.display='none'" class="btn"
                            style="background:#e2e8f0; color:#475569;">Cancelar</button>
                        <button onclick="guardarEdicion()" class="btn" style="background:#8b5cf6;">Guardar</button>
                    </div>
                </div>
            </div>

        </section>
    </div>

    <script src="<?= APP_URL ?>/assets/js/toast.js"></script>
    <script>
        const csrfToken = '<?= generarTokenCSRF() ?>';

        function postAction(data) {
            data.append('csrf_token', csrfToken);
            return fetch('gestionar_burbujas.php', { method: 'POST', body: data })
                .then(r => r.json());
        }

        function agregarBurbuja() {
            const texto = document.getElementById('nuevoTexto').value.trim();
            const cat = document.getElementById('nuevaCategoria').value;
            if (!texto) { showToast('Escribe un texto para la burbuja.', 'error'); return; }
            const fd = new FormData();
            fd.append('accion', 'agregar');
            fd.append('texto', texto);
            fd.append('id_categoria', cat);
            postAction(fd).then(d => {
                if (d.ok) { showToast('Burbuja agregada.', 'success'); setTimeout(() => location.reload(), 600); }
                else showToast(d.error, 'error');
            }).catch(() => showToast('Error de conexión.', 'error'));
        }

        function toggleBurbuja(id) {
            const fd = new FormData();
            fd.append('accion', 'toggle');
            fd.append('id', id);
            postAction(fd).then(d => {
                if (d.ok) location.reload();
                else showToast(d.error, 'error');
            });
        }

        function eliminarBurbuja(id) {
            customConfirm('¿Eliminar esta burbuja permanentemente?', () => {
                const fd = new FormData();
                fd.append('accion', 'eliminar');
                fd.append('id', id);
                postAction(fd).then(d => {
                    if (d.ok) {
                        const row = document.getElementById('burbuja-' + id);
                        if (row) { row.style.transition = 'opacity 0.3s'; row.style.opacity = '0'; setTimeout(() => row.remove(), 300); }
                        showToast('Burbuja eliminada.', 'success');
                    } else showToast(d.error, 'error');
                });
            });
        }

        function editarBurbuja(id, textoActual, catActual) {
            document.getElementById('editId').value = id;
            document.getElementById('editTexto').value = textoActual;
            document.getElementById('editCategoria').value = catActual;
            document.getElementById('modalEditar').style.display = 'flex';
        }

        function guardarEdicion() {
            const id = document.getElementById('editId').value;
            const nuevoTexto = document.getElementById('editTexto').value.trim();
            const nuevaCat = document.getElementById('editCategoria').value;
            if (!nuevoTexto) { showToast('El texto es obligatorio.', 'error'); return; }

            const fd = new FormData();
            fd.append('accion', 'editar');
            fd.append('id', id);
            fd.append('texto', nuevoTexto);
            fd.append('id_categoria', parseInt(nuevaCat) || 0);
            postAction(fd).then(d => {
                if (d.ok) { showToast('Burbuja actualizada.', 'success'); setTimeout(() => location.reload(), 600); }
                else showToast(d.error, 'error');
            });
        }

        function moverBurbuja(id, dir) {
            const fd = new FormData();
            fd.append('accion', 'mover');
            fd.append('id', id);
            fd.append('dir', dir);
            postAction(fd).then(d => {
                if (d.ok) location.reload();
            });
        }

        function accionGlobal(accion, msg) {
            customConfirm(msg, () => {
                const fd = new FormData();
                fd.append('accion', accion);
                postAction(fd).then(d => {
                    if (d.ok) { showToast('Acción completada.', 'success'); setTimeout(() => location.reload(), 600); }
                    else showToast(d.error, 'error');
                });
            });
        }
    </script>
</body>

</html>