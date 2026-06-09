<?php
require_once __DIR__ . '/proteger.php';
include '../db.php';

$rol = $_SESSION['rol'];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel <?= ucfirst($rol) ?></title>
    <link rel="icon" href="<?= APP_URL ?>/assets/img/image.png" type="image/png">
    <link rel="stylesheet"
        href="<?= APP_URL ?>/assets/css/login.css?v=<?= filemtime(__DIR__ . '/../assets/css/login.css') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>
    <?php require_once __DIR__ . '/../includes/admin_layout_top.php'; ?>


    <div class="panel-container">
        <section class="panel">

            <div class="panel-header-flex">
                <h1 class="panel-title">Panel <?= ucfirst($rol) ?></h1>
                <div class="usuario-info-premium">
                    <div class="user-details">
                        <span class="user-welcome">Bienvenido,</span>
                        <span class="user-name"><?= htmlspecialchars($_SESSION['usuario']) ?></span>
                    </div>
                    <a href="cerrar.php" class="btn-logout-premium" title="Cerrar sesión">
                        <i class="bi bi-box-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="quick-access-grid">
                <a href="<?= APP_URL ?>/index" class="qa-card">
                    <i class="bi bi-house-door"></i>
                    <span>Ver Sitio</span>
                </a>
                <a href="agregar_empresa.php" class="qa-card qa-primary">
                    <i class="bi bi-plus-lg"></i>
                    <span>Nueva Empresa</span>
                </a>
                <a href="agregar_categoria.php" class="qa-card">
                    <i class="bi bi-tags"></i>
                    <span>Nueva Categoría</span>
                </a>
                <a href="gestionar_banner.php" class="qa-card">
                    <i class="bi bi-images"></i>
                    <span>Banners</span>
                </a>
                <a href="gestionar_resenas.php" class="qa-card">
                    <i class="bi bi-chat-right-quote"></i>
                    <span>Reseñas</span>
                </a>
                <a href="gestionar_burbujas.php" class="qa-card">
                    <i class="bi bi-search-heart"></i>
                    <span>Burbujas</span>
                </a>
                <a href="editar_sobre.php" class="qa-card">
                    <i class="bi bi-info-circle"></i>
                    <span>Sobre Nosotros</span>
                </a>
            </div>



            <?php
            $stmt_stats = $conexion->prepare("SELECT SUM(vistas) as total, COUNT(*) as empresas FROM empresas");
            $stmt_stats->execute();
            $stats_global = $stmt_stats->get_result()->fetch_assoc();

            $total_visitas = $stats_global['total'] ?? 0;
            $total_emp = $stats_global['empresas'] ?? 0;

            $stmt_top5 = $conexion->prepare("SELECT nombre, vistas FROM empresas ORDER BY vistas DESC LIMIT 5");
            $stmt_top5->execute();
            $top5 = $stmt_top5->get_result();
            ?>

            <div class="stats-dashboard">
                <div class="stat-box">
                    <i class="bi bi-eye"></i>
                    <span class="stat-title">Impacto Global</span>
                    <span class="stat-value"><?= number_format($total_visitas) ?></span>
                    <span class="stat-footer">Visitas totales en la plataforma</span>
                    <button onclick="customConfirm('¿Reiniciar TODAS las vistas a cero?', () => reiniciarVistas())"
                        class="btn-reiniciar-vistas">
                        <i class="bi bi-arrow-counterclockwise"></i> Reiniciar todas las vistas
                    </button>
                </div>

                <div class="stat-box" style="border-left-color: #f7941d;">
                    <i class="bi bi-graph-up-arrow" style="color: #f7941d;"></i>
                    <span class="stat-title">Top 5 Popularidad</span>
                    <ul class="top5-list">
                        <?php if ($top5 && $top5->num_rows > 0): ?>
                            <?php while ($t = $top5->fetch_assoc()): ?>
                                <li class="top5-item">
                                    <span
                                        class="top5-name"><?= htmlspecialchars(mb_strimwidth($t['nombre'], 0, 20, '…')) ?></span>
                                    <span class="top5-views"><?= $t['vistas'] ?></span>
                                </li>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <li class="top5-item">No hay datos suficientes</li>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="stat-box" style="border-left-color: #10b981;">
                    <i class="bi bi-building" style="color: #10b981;"></i>
                    <span class="stat-title">Activos</span>
                    <span class="stat-value"><?= $total_emp ?></span>
                    <span class="stat-footer">Empresas registradas actualmente</span>
                    <div style="margin-top:auto;">
                        <a href="agregar_empresa.php" class="btn-reiniciar-vistas"
                            style="background:#dcfce7; color:#166534;">
                            <i class="bi bi-plus-circle"></i> Nuevo registro
                        </a>
                    </div>
                </div>
            </div>

            <div class="system-tools-grid">
                <div class="stat-box" style="border-top: 4px solid #6366f1; padding: 20px;">
                    <h3 style="font-size: 15px; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                        <i class="bi bi-shield-lock"></i> Modo Mantenimiento
                    </h3>
                    <button id="btn-toggle-maint" onclick="toggleMantenimiento()" class="btn-reiniciar-vistas"
                        style="width: 100%; justify-content: center; transition: 0.3s; padding: 12px; font-weight: 700; border-radius: 12px; border: 2px solid;">
                        <?php $mantenimiento_activo = file_exists(__DIR__ . '/../mantenimiento.flag'); ?>
                        <i class="bi <?= $mantenimiento_activo ? 'bi-toggle-on' : 'bi-toggle-off' ?>"></i>
                        <span
                            id="maint-text"><?= $mantenimiento_activo ? 'Desactivar Mantenimiento' : 'Activar Mantenimiento' ?></span>
                    </button>
                    <script>
                        const isM = <?= $mantenimiento_activo ? 'true' : 'false' ?>;
                        const btnM = document.getElementById('btn-toggle-maint');
                        btnM.style.background = isM ? '#fee2e2' : '#f0fdf4';
                        btnM.style.color = isM ? '#ef4444' : '#16a34a';
                        btnM.style.borderColor = isM ? '#fecaca' : '#bbf7d0';
                    </script>
                </div>

                <div class="stat-box" style="border-top: 4px solid #ec4899; padding: 20px;">
                    <h3 style="font-size: 15px; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                        <i class="bi bi-trash3"></i> Limpiador de Imágenes
                    </h3>
                    <button onclick="escanearHuerfanos()" class="btn-reiniciar-vistas"
                        style="width: 100%; justify-content: center; background: #f8fafc; color: #475569; padding: 12px; border-radius: 12px;">
                        <i class="bi bi-search"></i> Escanear imágenes sin uso
                    </button>
                    <div id="huerfanos-info"
                        style="font-size: 12px; margin-top: 15px; display: none; background: #fff5f7; padding: 15px; border-radius: 12px; border: 1px solid #fecaca;">
                        <div id="huerfanos-lista"
                            style="max-height: 150px; overflow-y: auto; margin-bottom: 12px; background: #fff; border-radius: 8px; border: 1px solid #fecaca; padding: 5px;">
                        </div>
                        <button onclick="limpiarArchivos()" class="btn-reiniciar-vistas"
                            style="width: 100%; background: #e11d48; color: #fff; border: none; font-weight: 800; padding: 10px;">
                            Borrar <span id="huerfanos-count">0</span> archivos
                        </button>
                    </div>
                </div>
            </div>



            <h2>Administración de Usuarios</h2><br>
            <a href="agregar.php" class="btn">Agregar Usuario</a>
            <br><br>

            <div class="table-wrap">
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                    <?php
                    $stmt_u = $conexion->prepare("SELECT id_usuario, nombre, rol FROM usuarios");
                    $stmt_u->execute();
                    $res = $stmt_u->get_result();
                    while ($fila = $res->fetch_assoc()):
                        ?>
                        <tr>
                            <td data-label="ID"><?= $fila['id_usuario'] ?></td>
                            <td data-label="Usuario"><?= htmlspecialchars($fila['nombre']) ?></td>
                            <td data-label="Rol"><?= htmlspecialchars($fila['rol']) ?></td>
                            <td data-label="Acciones">
                                <div class="acciones">
                                    <a href="editar_usuario.php?id=<?= $fila['id_usuario'] ?>" class="btn">Editar</a>
                                    <button onclick="eliminarRegistro('usuario', <?= $fila['id_usuario'] ?>, this)"
                                        class="btn btn-danger">Eliminar</button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>
            <br><br>

            <h2>Categorías</h2><br>
            <a href="agregar_categoria.php" class="btn">Agregar Categoría</a>
            <br><br>

            <div class="table-wrap">
                <table>
                    <tr>
                        <th>Orden</th>
                        <th>Icono</th>
                        <th>Nombre</th>
                        <th>Acciones</th>
                    </tr>
                    <?php
                    $stmt_cat = $conexion->prepare("SELECT * FROM categorias ORDER BY orden ASC");
                    $stmt_cat->execute();
                    $res_cat = $stmt_cat->get_result();
                    while ($cat = $res_cat->fetch_assoc()):
                        ?>
                        <tr id="cat-<?= $cat['id_categoria'] ?>">
                            <td data-label="Orden">
                                <div style="display:flex; align-items:center; gap:6px;">
                                    <span
                                        style="font-weight:700; color:#64748b; min-width:20px;"><?= $cat['orden'] ?></span>
                                    <button onclick="moverCategoria(<?= $cat['id_categoria'] ?>, 'subir')" class="btn-orden"
                                        title="Subir">▲</button>
                                    <button onclick="moverCategoria(<?= $cat['id_categoria'] ?>, 'bajar')" class="btn-orden"
                                        title="Bajar">▼</button>
                                </div>
                            </td>
                            <td data-label="Icono" style="font-size:22px;">
                                <i class="bi <?= htmlspecialchars($cat['icono'] ?? 'bi-briefcase') ?>"></i>
                            </td>
                            <td data-label="Nombre"><?= htmlspecialchars($cat['nombre']) ?></td>
                            <td data-label="Acciones">
                                <div class="acciones">
                                    <a href="editar_categoria.php?id=<?= $cat['id_categoria'] ?>" class="btn">Editar</a>
                                    <button onclick="eliminarRegistro('categoria', <?= $cat['id_categoria'] ?>, this)"
                                        class="btn btn-danger">Eliminar</button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>
            <br><br>

            <?php
            $stmt_dest = $conexion->prepare("SELECT id_empresa, nombre FROM empresas WHERE destacada=1");
            $stmt_dest->execute();
            $res_dest = $stmt_dest->get_result();
            $total_dest = $res_dest->num_rows;
            ?>
            <div class="destacadas-section">
                <h3>⭐ Empresas Destacadas</h3>
                <p class="contador-dest">
                    <span><?= $total_dest ?></span>/3 destacadas actualmente
                    <?= $total_dest >= 3 ? '— <strong style="color:#e74c3c">Límite alcanzado</strong>' : '' ?>
                </p>
                <?php if ($total_dest > 0): ?>
                    <div class="destacadas-lista">
                        <?php while ($d = $res_dest->fetch_assoc()): ?>
                            <div class="destacada-chip" id="chip-<?= $d['id_empresa'] ?>">
                                ⭐ <?= htmlspecialchars($d['nombre']) ?>
                                <button onclick="toggleDestacada(<?= $d['id_empresa'] ?>, 'quitar')" title="Quitar">✕</button>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p style="color:#aaa;font-size:13px;">No hay empresas destacadas. Haz clic en ☆ en la tabla para
                        destacar una.</p>
                <?php endif; ?>
            </div>

            <h2>Empresas</h2><br>
            <div
                style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; margin-bottom:20px;">
                <a href="agregar_empresa.php" class="btn">Agregar Empresa</a>
                <div class="search-panel" style="position:relative; flex:1; max-width:400px;">
                    <i class="bi bi-search"
                        style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#aaa;"></i>
                    <input type="text" id="filtroEmpresa" placeholder="Buscar por nombre o rubro..."
                        style="width:100%; padding:10px 15px 10px 40px; border:1px solid #ddd; border-radius:8px; font-size:14px; outline:none; transition:border-color 0.3s;">
                </div>
            </div>

            <div class="table-wrap">
                <table>
                    <tr>
                        <th>ID</th>
                        <th>⭐</th>
                        <th>👁</th>
                        <th>Logo</th>
                        <th>Nombre</th>
                        <th>Teléfono</th>
                        <th>Dirección</th>
                        <th>Rubro</th>
                        <th>Horario</th>
                        <th>Descripción</th>
                        <th>Ubicación</th>
                        <th>Enlace</th>
                        <th>Acciones</th>
                    </tr>
                    <?php
                    $pagina = max(1, intval($_GET['pagina'] ?? 1));
                    $por_pagina = 25;
                    $offset_emp = ($pagina - 1) * $por_pagina;
                    $total_empresas_res = $conexion->query("SELECT COUNT(*) as t FROM empresas")->fetch_assoc();
                    $total_empresas_p = $total_empresas_res['t'] ?? 0;
                    $total_paginas = (int) ceil($total_empresas_p / $por_pagina);

                    $stmt_emp = $conexion->prepare("
                        SELECT e.id_empresa, e.logo, e.nombre, e.telefono, e.direccion,
                               e.descripcion, e.horario, e.ubicacion_link, e.link_empresa,
                               e.destacada, e.vistas, c.nombre AS categoria
                        FROM empresas e
                        JOIN categorias c ON e.id_categoria = c.id_categoria
                        ORDER BY e.destacada DESC, e.vistas DESC
                        LIMIT ?, ?
                    ");
                    $stmt_emp->bind_param("ii", $offset_emp, $por_pagina);
                    $stmt_emp->execute();
                    $res = $stmt_emp->get_result();
                    while ($fila = $res->fetch_assoc()):
                        ?>
                        <tr id="fila-<?= $fila['id_empresa'] ?>">
                            <td data-label="ID"><?= $fila['id_empresa'] ?></td>
                            <td data-label="Destacada">
                                <button class="btn-estrella <?= $fila['destacada'] ? 'activa' : '' ?>"
                                    onclick="toggleDestacada(<?= $fila['id_empresa'] ?>, '<?= $fila['destacada'] ? 'quitar' : 'destacar' ?>')"
                                    title="<?= $fila['destacada'] ? 'Quitar destacada' : 'Destacar' ?>">
                                    <?= $fila['destacada'] ? '⭐' : '☆' ?>
                                </button>
                            </td>
                            <td data-label="Vistas">
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <?= number_format($fila['vistas']) ?>
                                    <button
                                        onclick="customConfirm('¿Reiniciar vistas de esta empresa?', () => reiniciarVistas(<?= $fila['id_empresa'] ?>))"
                                        style="font-size:14px; color:#cbd5e1; text-decoration:none; background:none; border:none; cursor:pointer; padding:0;"
                                        title="Reiniciar">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                </div>
                            </td>
                            <td data-label="Logo">
                                <?php if (!empty($fila['logo'])): ?>
                                    <img src="<?= APP_URL ?>/assets/img/<?= htmlspecialchars($fila['logo']) ?>"
                                        style="width:45px;height:45px;object-fit:cover;border-radius:6px;">
                                <?php else: ?>—<?php endif; ?>
                            </td>
                            <td data-label="Nombre"><?= htmlspecialchars($fila['nombre']) ?></td>
                            <td data-label="Teléfono"><?= htmlspecialchars($fila['telefono'] ?? '—') ?></td>
                            <td data-label="Dirección"><?= htmlspecialchars($fila['direccion'] ?? '—') ?></td>
                            <td data-label="Rubro"><?= htmlspecialchars($fila['categoria']) ?></td>
                            <td data-label="Horario">
                                <?= !empty($fila['horario']) ? htmlspecialchars($fila['horario']) : '—' ?>
                            </td>
                            <td data-label="Descripción">
                                <?= !empty($fila['descripcion']) ? htmlspecialchars(mb_strimwidth($fila['descripcion'], 0, 50, '…')) : '—' ?>
                            </td>
                            <td data-label="Mapa">
                                <?php if (!empty($fila['ubicacion_link'])): ?>
                                    <a href="<?= htmlspecialchars($fila['ubicacion_link']) ?>" target="_blank">Ver mapa</a>
                                <?php else: ?>—<?php endif; ?>
                            </td>
                            <td data-label="Web">
                                <?php if (!empty($fila['link_empresa'])): ?>
                                    <a href="<?= htmlspecialchars($fila['link_empresa']) ?>" target="_blank">Ver página</a>
                                <?php else: ?>—<?php endif; ?>
                            </td>
                            <td data-label="Acciones">
                                <div class="acciones">
                                    <a href="editar.php?id=<?= $fila['id_empresa'] ?>" class="btn">Editar</a>
                                    <button onclick="eliminarRegistro('empresa', <?= $fila['id_empresa'] ?>, this)"
                                        class="btn btn-danger">Eliminar</button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    <tr id="noResultsAdmin" style="display:none;">
                        <td colspan="13">
                            <div class="empty-state">
                                <i class="bi bi-search empty-icon"></i>
                                <h3>Sin coincidencias</h3>
                                <p>Prueba con un nombre o rubro diferente.</p>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            <?php
            if ($total_paginas > 1):
                echo '<div style="display:flex; gap:8px; margin-top:16px; align-items:center; flex-wrap:wrap;">';
                for ($i = 1; $i <= $total_paginas; $i++) {
                    $activa = $i === $pagina ? 'background:#334155;color:#fff;' : 'background:#f1f5f9;color:#334155;';
                    echo '<a href="?pagina=' . $i . '" style="padding:6px 12px; border-radius:6px; text-decoration:none; font-size:14px; ' . $activa . '">' . $i . '</a>';
                }
                echo '<span style="font-size:13px; color:#94a3b8; margin-left:8px;">Página ' . $pagina . ' de ' . $total_paginas . ' (' . $total_empresas_p . ' empresas)</span>';
                echo '</div>';
            endif;
            ?>
            <br><br>

        </section>
    </div>

    <script src="<?= APP_URL ?>/assets/js/toast.js"></script>
    <script>
        const csrfToken = '<?= $_SESSION['csrf_token'] ?>';
        let currentMaintState = <?= file_exists(__DIR__ . '/../mantenimiento.flag') ? 'true' : 'false' ?>;

        function confirmarReinicio() {
            customConfirm('⚠️ ¿Estás COMPLETAMENTE SEGURO de reiniciar TODAS las vistas a cero?\n\nEsta acción no se puede deshacer.', () => {
                reiniciarVistas();
            });
        }

        function reiniciarVistas(id = null) {
            const fd = new FormData();
            if (id) fd.append('id', id);
            fd.append('csrf_token', csrfToken);

            fetch('reiniciar_vistas.php', {
                method: 'POST',
                body: fd
            })
                .then(r => r.json())
                .then(data => {
                    if (data.ok) {
                        location.reload();
                    } else {
                        showToast(data.error, 'error');
                    }
                })
                .catch(err => showToast('Error de conexión con el servidor.', 'error'));
        }

        function toggleDestacada(id, accion) {
            let msg = accion === 'destacar' ? '¿Destacar esta empresa?' : '¿Quitar de destacadas?';
            customConfirm(msg, () => {
                fetch('toggle_destacada.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'id=' + id + '&accion=' + accion + '&csrf_token=' + csrfToken
                })
                    .then(r => r.json())
                    .then(data => {
                        if (!data.ok) { showToast(data.error, 'error'); return; }
                        location.reload();
                    });
            });
        }

        function eliminarRegistro(tipo, id, btn) {
            customConfirm('¿Estás seguro de eliminar permanentemente este registro?', () => {
                let archivo = '';
                if (tipo === 'usuario') archivo = 'eliminar_usuario.php';
                if (tipo === 'empresa') archivo = 'eliminar.php';
                if (tipo === 'categoria') archivo = 'eliminar_categoria.php';

                btn.disabled = true;
                btn.innerText = 'Borrando...';
                btn.style.opacity = '0.7';

                fetch(archivo, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'id=' + id + '&csrf_token=' + csrfToken
                })
                    .then(r => r.json())
                    .then(data => {
                        if (!data.ok) {
                            showToast(data.error || 'Error desconocido al borrar', 'error');
                            btn.disabled = false;
                            btn.innerText = 'Eliminar';
                            btn.style.opacity = '1';
                            return;
                        }

                        let fila = btn.closest('tr');
                        if (fila) {
                            fila.style.transition = "opacity 0.3s ease";
                            fila.style.opacity = '0';
                            setTimeout(() => fila.remove(), 300);
                        } else {
                            location.reload();
                        }
                    })
                    .catch(e => {
                        showToast('Error de red al intentar eliminar.', 'error');
                        btn.disabled = false;
                        btn.innerText = 'Eliminar';
                        btn.style.opacity = '1';
                    });
            });
        }

        document.getElementById('filtroEmpresa')?.addEventListener('keyup', function () {
            const term = this.value.toLowerCase();
            const rows = document.querySelectorAll('table tr[id^="fila-"]');
            let found = false;

            rows.forEach(row => {
                const nombre = row.querySelector('[data-label="Nombre"]')?.textContent.toLowerCase() || "";
                const rubro = row.querySelector('[data-label="Rubro"]')?.textContent.toLowerCase() || "";

                if (nombre.includes(term) || rubro.includes(term)) {
                    row.style.display = "";
                    found = true;
                } else {
                    row.style.display = "none";
                }
            });

            const noResults = document.getElementById('noResultsAdmin');
            if (noResults) {
                noResults.style.display = found ? "none" : "";
            }
        });

        function moverCategoria(id, dir) {
            fetch('<?= APP_URL ?>/ajax/reordenar_categoria.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + id + '&dir=' + dir + '&csrf_token=' + csrfToken
            })
                .then(r => r.json())
                .then(data => {
                    if (data.ok) {
                        location.reload();
                    } else {
                        if (data.error !== 'Ya está en el límite') showToast(data.error, 'error');
                    }
                });
        }

        function toggleMantenimiento() {
            startProgress();
            const btn = document.getElementById('btn-toggle-maint');
            const text = document.getElementById('maint-text');
            const banner = document.getElementById('mantenimiento-banner');

            btn.disabled = true;

            const fd = new FormData();
            fd.append('csrf_token', csrfToken);
            fetch('toggle_mantenimiento.php', {
                method: 'POST',
                body: fd
            })
                .then(r => r.json())
                .then(data => {
                    finishProgress();
                    btn.disabled = false;
                    if (data.ok) {
                        const isM = !data.mensaje.toLowerCase().includes('desactivado');
                        currentMaintState = isM;

                        btn.style.background = isM ? '#fee2e2' : '#f0fdf4';
                        btn.style.color = isM ? '#ef4444' : '#16a34a';
                        btn.style.borderColor = isM ? '#fecaca' : '#bbf7d0';
                        btn.innerHTML = `<i class="bi bi-toggle-${isM ? 'on' : 'off'}"></i> <span id="maint-text">${isM ? 'Desactivar' : 'Activar'} Mantenimiento</span>`;

                        banner.style.display = 'flex';
                        if (isM) {
                            banner.classList.remove('online');
                        } else {
                            banner.classList.add('online');
                        }
                        banner.innerHTML = `<i class="bi bi-${isM ? 'exclamation-triangle' : 'check-circle'}-fill"></i> ${isM ? 'EL SITIO WEB SE ENCUENTRA ACTUALMENTE EN MANTENIMIENTO (PÚBLICO BLOQUEADO)' : 'EL SITIO WEB ESTÁ EN LÍNEA Y ACCESIBLE PARA EL PÚBLICO'}`;

                        if (!isM) {
                            setTimeout(() => {
                                if (!currentMaintState) {
                                    banner.style.transition = 'opacity 0.5s ease';
                                    banner.style.opacity = '0';
                                    setTimeout(() => {
                                        if (!currentMaintState) {
                                            banner.style.display = 'none';
                                            banner.style.opacity = '1';
                                        }
                                    }, 500);
                                }
                            }, 3000);
                        } else {
                            banner.style.opacity = '1';
                        }

                        showToast(data.mensaje, isM ? 'warning' : 'success');
                    } else {
                        showToast(data.error, 'error');
                    }
                })
                .catch(() => {
                    finishProgress();
                    btn.disabled = false;
                    showToast('Error al conectar con el servidor', 'error');
                });
        }


        function escanearHuerfanos() {
            const fd = new FormData();
            fd.append('csrf_token', csrfToken);
            fd.append('accion', 'escanear');

            const btn = event.currentTarget;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Escaneando...';
            btn.disabled = true;

            fetch('limpiar_archivos.php', {
                method: 'POST',
                body: fd
            })
                .then(r => r.json())
                .then(data => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    if (data.ok) {
                        const infoDiv = document.getElementById('huerfanos-info');
                        const listDiv = document.getElementById('huerfanos-lista');
                        const countSpan = document.getElementById('huerfanos-count');

                        countSpan.innerText = data.huerfanos.length;
                        listDiv.innerHTML = '';

                        if (data.huerfanos.length > 0) {
                            infoDiv.style.display = 'block';
                            data.huerfanos.forEach(h => {
                                const item = document.createElement('div');
                                item.style.padding = '10px';
                                item.style.borderBottom = '1px solid #f1f5f9';
                                item.style.fontSize = '11px';
                                item.style.display = 'flex';
                                item.style.alignItems = 'center';
                                item.style.gap = '12px';

                                const imageUrl = '<?= APP_URL ?>/' + h.ruta;

                                item.innerHTML = `
                                    <div style="width: 40px; height: 40px; border-radius: 6px; overflow: hidden; background: #f1f5f9; flex-shrink: 0; border: 1px solid #e2e8f0;">
                                        <img src="${imageUrl}" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.src='<?= APP_URL ?>/assets/img/image.png'; this.style.opacity='0.5';">
                                    </div>
                                    <div style="flex-grow: 1; min-width: 0;">
                                        <div style="color: #1e293b; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${h.nombre}</div>
                                        <div style="color: #94a3b8; font-size: 10px;">${h.ruta}</div>
                                    </div>
                                `;
                                listDiv.appendChild(item);
                            });
                        } else {
                            showToast('No se encontraron imágenes huérfanas.', 'success');
                            infoDiv.style.display = 'none';
                        }
                    } else {
                        showToast(data.error, 'error');
                    }
                })
                .catch(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    showToast('Error al escanear archivos', 'error');
                });
        }

        function limpiarArchivos() {
            customConfirm('¿Seguro que deseas eliminar permanentemente todas las imágenes sin uso? Esta acción no se puede deshacer.', () => {
                let totalBorrados = 0;
                
                function procesarLote(offset) {
                    const fd = new FormData();
                    fd.append('csrf_token', csrfToken);
                    fd.append('accion', 'limpiar');
                    fd.append('offset', offset);

                    fetch('limpiar_archivos.php', {
                        method: 'POST',
                        body: fd
                    })
                        .then(r => r.json())
                        .then(data => {
                            if (data.ok) {
                                totalBorrados += data.borrados;
                                document.getElementById('huerfanos-count').innerText = "Borrando... " + totalBorrados + " de " + data.total_huerfanos;
                                
                                if (data.hay_mas) {
                                    procesarLote(data.siguiente_offset);
                                } else {
                                    showToast('Se eliminaron ' + totalBorrados + ' archivos correctamente.', 'success');
                                    document.getElementById('huerfanos-info').style.display = 'none';
                                }
                            } else {
                                showToast(data.error, 'error');
                            }
                        })
                        .catch(() => showToast('Error al limpiar archivos', 'error'));
                }
                
                procesarLote(0);
            });
        }

        const bar = document.getElementById('nprogress-bar');

        function startProgress() {
            if (!bar) return;
            bar.style.opacity = '1';
            bar.style.transition = 'none';
            bar.style.width = '0%';
            requestAnimationFrame(() => {
                bar.style.transition = 'width 0.4s cubic-bezier(0.1, 0.5, 0.5, 1)';
                bar.style.width = '70%';
            });
        }

        function finishProgress() {
            if (!bar) return;
            bar.style.transition = 'width 0.2s ease-out';
            bar.style.width = '100%';
            setTimeout(() => {
                bar.style.opacity = '0';
                setTimeout(() => {
                    bar.style.width = '0%';
                }, 400);
            }, 200);
        }

        document.addEventListener('click', e => {
            const a = e.target.closest('a');
            if (a && a.href && !a.target && a.href !== '#' &&
                new URL(a.href).origin === location.origin &&
                !a.hasAttribute('download') &&
                !a.href.includes('mailto:') &&
                !a.href.includes('tel:')) {
                startProgress();
            }
        });

        window.addEventListener('pageshow', () => {
            finishProgress();
        });

        window.addEventListener('beforeunload', () => {
            startProgress();
        });
    </script>
</body>

</html>