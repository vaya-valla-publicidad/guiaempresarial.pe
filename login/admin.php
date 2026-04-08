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
    <link rel="stylesheet" href="/guiaempresarial.pe/assets/css/login.css">
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
                <a href="/guiaempresarial.pe/index.php" class="btn">
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
            <a href="agregar_empresa.php" class="btn">Agregar Empresa</a>
            <br><br>

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
                            <td data-label="Vistas"><?= number_format($fila['vistas']) ?></td>
                            <td data-label="Logo">
                                <?php if (!empty($fila['logo'])): ?>
                                    <img src="/guiaempresarial.pe/assets/img/<?= htmlspecialchars($fila['logo']) ?>"
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
    </script>

</body>

</html>