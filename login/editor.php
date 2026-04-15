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

            <a href="<?= APP_URL ?>/index.php" class="btn btn-panel-nav">
                Ver sitio principal
            </a>
            <a href="editar_sobre.php" class="btn btn-panel-nav-sep">
                Editar Sobre Nosotros
            </a>
            <a href="gestionar_banner.php" class="btn btn-warning">
                <i class="bi bi-images"></i> Gestionar Banner / Carrusel
            </a>
            <a href="gestionar_resenas.php" class="btn btn-danger-alt btn-panel-nav-sep">
                <i class="bi bi-star-half"></i> Gestionar Reseñas
            </a>
            <h2>Categorías</h2><br>
            <a href="agregar_categoria.php" class="btn">Agregar Categoría</a>
            <br><br>

            <div class="table-wrap">
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Icono</th>
                        <th>Nombre</th>
                        <th>Acciones</th>
                    </tr>
                    <?php
                    $res_cat = $conexion->query("SELECT * FROM categorias ORDER BY nombre ASC");
                    while ($cat = $res_cat->fetch_assoc()):
                        ?>
                        <tr>
                            <td data-label="ID"><?= $cat['id_categoria'] ?></td>
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
                    <tr id="noResultsEditor" style="display:none;">
                        <td colspan="13" style="text-align:center; padding:50px 20px; color:var(--ink-muted);">
                            <div style="font-size: 44px; margin-bottom: 12px; opacity: 0.3;">🔍</div>
                            <p style="font-size: 16px; font-weight: 600; margin-bottom: 4px;">No se encontraron resultados</p>
                            <p style="font-size: 13px; opacity: 0.7;">Prueba con un término de búsqueda diferente.</p>
                        </td>
                    </tr>
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
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; margin-bottom:20px;">
                <a href="agregar_empresa.php" class="btn">Agregar Empresa</a>
                <div class="search-panel" style="position:relative; flex:1; max-width:400px;">
                    <i class="bi bi-search" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#aaa;"></i>
                    <input type="text" id="filtroEmpresa" placeholder="Buscar por nombre o rubro..." 
                           style="width:100%; padding:10px 15px 10px 40px; border:1px solid #ddd; border-radius:8px; font-size:14px; outline:none; transition:border-color 0.3s;">
                </div>
            </div>

            <div class="table-wrap">
                <table>
                    <tr>
                        <th>ID</th>
                        <th>⭐</th>
                        <th>👁 Vistas</th>
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
                                <?= !empty($fila['horario']) ? htmlspecialchars($fila['horario']) : '—' ?></td>
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
                    <tr id="noResultsEditor" style="display:none;">
                        <td colspan="13" style="text-align:center; padding:30px; color:#aaa;">
                            <i class="bi bi-search" style="font-size:24px; display:block; margin-bottom:10px;"></i>
                            No se encontraron empresas que coincidan con la búsqueda.
                        </td>
                    </tr>
                </table>
            </div>
            <br><br>

        </section>
    </div>

    <script>
        function toggleDestacada(id, accion) {
            if (accion === 'destacar' && !confirm('¿Destacar esta empresa?')) return;
            if (accion === 'quitar' && !confirm('¿Quitar de destacadas?')) return;
            fetch('toggle_destacada.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + id + '&accion=' + accion
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

            if (tipo === 'empresa') archivo = 'eliminar.php';
            if (tipo === 'categoria') archivo = 'eliminar_categoria.php';

            btn.disabled = true;
            btn.innerText = 'Borrando...';
            btn.style.opacity = '0.7';

            fetch(archivo + '?id=' + id)
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

        document.getElementById('filtroEmpresa')?.addEventListener('keyup', function() {
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

            const noResults = document.getElementById('noResultsEditor');
            if (noResults) {
                noResults.style.display = found ? "none" : "";
            }
        });
    </script>

</body>

</html>