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
</head>

<body>

    <div class="panel-container">
        <section class="panel">

            <h1 class="panel-title">Panel <?= ucfirst($rol) ?></h1>

            <div class="usuario-info">
                Bienvenido, <?= htmlspecialchars($_SESSION['usuario']) ?> |
                <a href="cerrar.php">Cerrar sesión</a>
            </div>

            <div class="panel-actions">
                <a href="<?= APP_URL ?>/index.php" class="btn">
                    <i class="bi bi-house"></i> Ver sitio principal
                </a>
                <a href="editar_sobre.php" class="btn">
                    <i class="bi bi-info-circle"></i> Editar Sobre Nosotros
                </a>
                <a href="gestionar_banner.php" class="btn btn-warning">

                    <i class="bi bi-images"></i> Gestionar Banner / Carrusel
                </a>
                <a href="gestionar_resenas.php" class="btn btn-danger-alt">
                    <i class="bi bi-star-half"></i> Gestionar Reseñas
                </a>
            </div>

            <style>
                .stats-dashboard {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
                    gap: 20px;
                    margin-bottom: 30px;
                }

                .stat-box {
                    background: #fff;
                    padding: 25px;
                    border-radius: 12px;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
                    display: flex;
                    flex-direction: column;
                    gap: 10px;
                    position: relative;
                    border-left: 5px solid #1B3A57;
                    border-left: 5px solid var(--azul, #1B3A57);
                    transition: 0.3s;
                }

                .stat-box:hover {
                    transform: translateY(-5px);
                    box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
                }

                .stat-box i {
                    font-size: 24px;
                    color: #1B3A57;
                    color: var(--azul, #1B3A57);
                }

                .stat-box .stat-title {
                    font-size: 14px;
                    font-weight: 600;
                    color: #64748b;
                    text-transform: uppercase;
                }

                .stat-box .stat-value {
                    font-size: 28px;
                    font-weight: 800;
                    color: #1e293b;
                }

                .stat-box .stat-footer {
                    font-size: 13px;
                    color: #94a3b8;
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
                    padding: 8px 0;
                    border-bottom: 1px solid #f1f5f9;
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
                    background: #e0f2fe;
                    color: #0369a1;
                    padding: 2px 8px;
                    border-radius: 999px;
                    font-size: 11px;
                    font-weight: 700;
                }

                .btn-reiniciar-vistas {
                    background: #fee2e2;
                    color: #991b1b;
                    padding: 5px 10px;
                    border-radius: 6px;
                    text-decoration: none;
                    font-size: 12px;
                    display: inline-flex;
                    align-items: center;
                    gap: 5px;
                    margin-top: 10px;
                    font-weight: 600;
                    width: fit-content;
                    border: none;
                    cursor: pointer;
                    transition: 0.2s;
                }

                .btn-reiniciar-vistas:hover {
                    background: #ef4444;
                    color: #fff;
                }

                .btn-orden {
                    background: #f1f5f9;
                    border: 1px solid #e2e8f0;
                    color: #475569;
                    width: 28px;
                    height: 28px;
                    border-radius: 6px;
                    cursor: pointer;
                    font-size: 12px;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    transition: all 0.2s;
                    padding: 0;
                }

                .btn-orden:hover {
                    background: #1B3A57;
                    color: #fff;
                    border-color: #1B3A57;
                }
            </style>

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
                    <button onclick="confirmarReinicio()" class="btn-reiniciar-vistas">
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

            <script>
                function confirmarReinicio() {
                    if (confirm('⚠️ ¿Estás COMPLETAMENTE SEGURO de reiniciar TODAS las vistas a cero?\n\nEsta acción no se puede deshacer.')) {
                        location.href = 'reiniciar_vistas.php';
                    }
                }
            </script>

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
                                    <a href="reiniciar_vistas.php?id=<?= $fila['id_empresa'] ?>"
                                        onclick="return confirm('¿Reiniciar vistas de esta empresa?')"
                                        style="font-size:14px; color:#cbd5e1; text-decoration:none;" title="Reiniciar">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </a>
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
                        <td colspan="13" style="text-align:center; padding:50px 20px; color:var(--ink-muted);">
                            <div style="font-size: 44px; margin-bottom: 12px; opacity: 0.3;">🔍</div>
                            <p style="font-size: 16px; font-weight: 600; margin-bottom: 4px;">No se encontraron
                                resultados</p>
                            <p style="font-size: 13px; opacity: 0.7;">Prueba con un término de búsqueda diferente.</p>
                        </td>
                    </tr>
                </table>
            </div>
            <br><br>

        </section>
    </div>

    <script>
        const csrfToken = '<?php echo $_SESSION['csrf_token'] ?? ''; ?>';

        function toggleDestacada(id, accion) {
            if (accion === 'destacar' && !confirm('¿Destacar esta empresa?')) return;
            if (accion === 'quitar' && !confirm('¿Quitar de destacadas?')) return;
            fetch('toggle_destacada.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + id + '&accion=' + accion + '&csrf_token=' + csrfToken
            })
                .then(r => r.json())
                .then(data => {
                    if (!data.ok) { alert(data.error); return; }
                    location.reload();
                });
        }

        function eliminarRegistro(tipo, id, btn) {
            if (!confirm('¿Estás seguro de eliminar permanentemente este registro?')) return;

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
                        alert(data.error || 'Error desconocido al borrar');
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
                    alert('Error de red al intentar eliminar.');
                    btn.disabled = false;
                    btn.innerText = 'Eliminar';
                    btn.style.opacity = '1';
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
                        if (data.error !== 'Ya está en el límite') alert(data.error);
                    }
                });
        }
    </script>

</body>

</html>