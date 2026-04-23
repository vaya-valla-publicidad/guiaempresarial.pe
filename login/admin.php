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
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/login.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .panel-header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 1px solid #f1f5f9;
        }

        .usuario-info-premium {
            display: flex;
            align-items: center;
            gap: 15px;
            background: #f8fafc;
            padding: 8px 15px;
            border-radius: 50px;
            border: 1px solid #e2e8f0;
        }

        .user-details {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .user-welcome {
            font-size: 11px;
            color: #94a3b8;
            text-transform: uppercase;
            font-weight: 700;
        }

        .user-name {
            font-size: 14px;
            font-weight: 700;
            color: #1e293b;
        }

        .btn-logout-premium {
            width: 35px;
            height: 35px;
            background: #fee2e2;
            color: #ef4444;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            text-decoration: none;
            transition: 0.3s;
        }

        .btn-logout-premium:hover {
            background: #ef4444;
            color: #fff;
            transform: scale(1.1);
        }

        .quick-access-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 15px;
            margin-bottom: 40px;
        }

        .qa-card {
            background: #fff;
            padding: 20px;
            border-radius: 16px;
            text-decoration: none;
            color: #475569;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            border: 1px solid #e2e8f0;
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .qa-card i {
            font-size: 24px;
            color: #1B3A57;
        }

        .qa-card span {
            font-size: 13px;
            font-weight: 700;
        }

        .qa-card:hover {
            transform: translateY(-5px);
            border-color: #1B3A57;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            color: #1B3A57;
        }

        .qa-card.qa-primary {
            background: #1B3A57;
            color: #fff;
            border-color: #1B3A57;
        }

        .qa-card.qa-primary i {
            color: #fff;
        }

        .qa-card.qa-primary:hover {
            background: #152e45;
        }

        .stats-dashboard {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-box {
            background: #fff;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            gap: 12px;
            position: relative;
            border: 1px solid #f1f5f9;
            transition: 0.3s;
        }

        .stat-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .stat-box i {
            font-size: 32px;
            color: #1B3A57;
        }

        .stat-box .stat-title {
            font-size: 12px;
            font-weight: 800;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .stat-box .stat-value {
            font-size: 36px;
            font-weight: 900;
            color: #1e293b;
            line-height: 1;
        }

        .stat-box .stat-footer {
            font-size: 13px;
            color: #64748b;
            margin-top: auto;
        }

        .top5-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .top5-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f8fafc;
        }

        .top5-item:last-child {
            border-bottom: none;
        }

        .top5-name {
            font-weight: 600;
            font-size: 13px;
            color: #475569;
        }

        .top5-views {
            background: #f1f5f9;
            color: #1B3A57;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
        }

        .btn-reiniciar-vistas {
            background: #f8fafc;
            color: #64748b;
            padding: 8px 12px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 15px;
            font-weight: 700;
            width: fit-content;
            border: 1px solid #e2e8f0;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-reiniciar-vistas:hover {
            background: #fee2e2;
            color: #ef4444;
            border-color: #fecaca;
        }

        .btn-orden {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #64748b;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .btn-orden:hover {
            background: #1B3A57;
            color: #fff;
            border-color: #1B3A57;
        }

        @media (max-width: 768px) {
            .panel-header-flex {
                flex-direction: column;
                gap: 20px;
                align-items: flex-start;
            }

            .quick-access-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>

<body>

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
                <a href="<?= APP_URL ?>/index.php" class="qa-card">
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
                <a href="editar_sobre.php" class="qa-card">
                    <i class="bi bi-info-circle"></i>
                    <span>Sobre Nosotros</span>
                </a>
            </div>



            <?php
            $stats_global = $conexion->query("SELECT SUM(vistas) as total, COUNT(*) as empresas FROM empresas")->fetch_assoc();
            $total_visitas = $stats_global['total'] ?? 0;
            $total_emp = $stats_global['empresas'] ?? 0;
            $top5 = $conexion->query("SELECT nombre, vistas FROM empresas ORDER BY vistas DESC LIMIT 5");
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
                    $res = $conexion->query("SELECT id_usuario, nombre, rol FROM usuarios");
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
                    $res_cat = $conexion->query("SELECT * FROM categorias ORDER BY orden ASC");
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
            $res_dest = $conexion->query("SELECT id_empresa, nombre FROM empresas WHERE destacada=1");
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
                    $res = $conexion->query("
    SELECT e.id_empresa, e.logo, e.nombre, e.telefono, e.direccion,
           e.descripcion, e.horario, e.ubicacion_link, e.link_empresa,
           e.destacada, e.vistas, c.nombre AS categoria
    FROM empresas e
    JOIN categorias c ON e.id_categoria = c.id_categoria
    ORDER BY e.destacada DESC, e.vistas DESC
");
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
            <br><br>

        </section>
    </div>

    <script src="<?= APP_URL ?>/assets/js/toast.js"></script>
    <script>
        const csrfToken = '<?php echo $_SESSION['csrf_token'] ?? ''; ?>';

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
                body: 'id=' + id + '&dir=' + dir
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
    </script>
</body>

</html>