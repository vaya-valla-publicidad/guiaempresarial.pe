<?php
require_once __DIR__ . '/proteger.php';
include '../db.php';

$rol = $_SESSION['rol'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
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

<a href="/guiaempresarial.pe/index.php" class="btn" style="margin-bottom:10px; display:inline-block;">
    Ver sitio principal
</a>
<a href="editar_sobre.php" class="btn" style="margin-bottom:20px; display:inline-block;">
    Editar Sobre Nosotros
</a>

<!-- ══════════════ USUARIOS ══════════════ -->
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
    <td><?= $fila['id_usuario'] ?></td>
    <td><?= htmlspecialchars($fila['nombre']) ?></td>
    <td><?= htmlspecialchars($fila['rol']) ?></td>
    <td>
        <div class="acciones">
            <a href="editar_usuario.php?id=<?= $fila['id_usuario'] ?>" class="btn">Editar</a>
            <a href="eliminar_usuario.php?id=<?= $fila['id_usuario'] ?>"
               class="btn btn-danger"
               onclick="return confirm('¿Eliminar este usuario?');">Eliminar</a>
        </div>
    </td>
</tr>
<?php endwhile; ?>
</table>
</div>
<br><br>

<!-- ══════════════ DESTACADAS ══════════════ -->
<?php
$res_dest   = $conexion->query("SELECT id_empresa, nombre FROM empresas WHERE destacada=1");
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
    <p style="color:#aaa;font-size:13px;">No hay empresas destacadas. Haz clic en ☆ en la tabla para destacar una.</p>
    <?php endif; ?>
</div>

<!-- ══════════════ EMPRESAS ══════════════ -->
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
    <td><?= $fila['id_empresa'] ?></td>
    <td>
        <button class="btn-estrella <?= $fila['destacada'] ? 'activa' : '' ?>"
                onclick="toggleDestacada(<?= $fila['id_empresa'] ?>, '<?= $fila['destacada'] ? 'quitar' : 'destacar' ?>')"
                title="<?= $fila['destacada'] ? 'Quitar destacada' : 'Destacar' ?>">
            <?= $fila['destacada'] ? '⭐' : '☆' ?>
        </button>
    </td>
    <td><?= number_format($fila['vistas']) ?></td>
    <td>
        <?php if (!empty($fila['logo'])): ?>
        <img src="/guiaempresarial.pe/assets/img/<?= htmlspecialchars($fila['logo']) ?>"
             style="width:45px;height:45px;object-fit:cover;border-radius:6px;">
        <?php else: ?>—<?php endif; ?>
    </td>
    <td><?= htmlspecialchars($fila['nombre']) ?></td>
    <td><?= htmlspecialchars($fila['telefono'] ?? '—') ?></td>
    <td><?= htmlspecialchars($fila['direccion'] ?? '—') ?></td>
    <td><?= htmlspecialchars($fila['categoria']) ?></td>
    <td><?= !empty($fila['horario']) ? htmlspecialchars($fila['horario']) : '—' ?></td>
    <td><?= !empty($fila['descripcion']) ? htmlspecialchars(mb_strimwidth($fila['descripcion'], 0, 50, '…')) : '—' ?></td>
    <td>
        <?php if (!empty($fila['ubicacion_link'])): ?>
        <a href="<?= htmlspecialchars($fila['ubicacion_link']) ?>" target="_blank">Ver</a>
        <?php else: ?>—<?php endif; ?>
    </td>
    <td>
        <?php if (!empty($fila['link_empresa'])): ?>
        <a href="<?= htmlspecialchars($fila['link_empresa']) ?>" target="_blank">Ver</a>
        <?php else: ?>—<?php endif; ?>
    </td>
    <td>
        <div class="acciones">
            <a href="editar.php?id=<?= $fila['id_empresa'] ?>" class="btn">Editar</a>
            <a href="eliminar.php?id=<?= $fila['id_empresa'] ?>" class="btn btn-danger"
               onclick="return confirm('¿Eliminar esta empresa?');">Eliminar</a>
        </div>
    </td>
</tr>
<?php endwhile; ?>
</table>
</div>
<br><br>

<!-- ══════════════ CATEGORÍAS ══════════════ -->
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
    <td><?= $cat['id_categoria'] ?></td>
    <td style="font-size:22px;">
        <i class="bi <?= htmlspecialchars($cat['icono'] ?? 'bi-briefcase') ?>"></i>
    </td>
    <td><?= htmlspecialchars($cat['nombre']) ?></td>
    <td>
        <div class="acciones">
            <a href="editar_categoria.php?id=<?= $cat['id_categoria'] ?>" class="btn">Editar</a>
            <a href="eliminar_categoria.php?id=<?= $cat['id_categoria'] ?>" class="btn btn-danger"
               onclick="return confirm('¿Eliminar esta categoría?');">Eliminar</a>
        </div>
    </td>
</tr>
<?php endwhile; ?>
</table>
</div>

</section>
</div>

<script>
function toggleDestacada(id, accion) {
    if (accion === 'destacar' && !confirm('¿Destacar esta empresa?')) return;
    if (accion === 'quitar'   && !confirm('¿Quitar de destacadas?')) return;
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
</script>

</body>
</html>