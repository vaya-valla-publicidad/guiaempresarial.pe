<?php
include 'proteger.php';   
include '../db.php';

$rol = $_SESSION['rol'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel <?= ucfirst($rol) ?></title>
<link rel="stylesheet" href="/clon/guiaempresarial.pe/assets/css/login.css">
</head>
<body>

<div class="panel-container">
<section class="panel">

<h1 class="panel-title">Panel <?= ucfirst($rol) ?></h1>

<div class="usuario-info">
Bienvenido, <?= htmlspecialchars($_SESSION['usuario']) ?> |
<a href="cerrar.php">Cerrar sesión</a>
</div>

<a href="../index.php" class="btn" style="margin-bottom:20px; display:inline-block;">
Ver sitio principal
</a>

<h2>Empresas</h2>
<a href="agregar_empresa.php" class="btn">Agregar Empresa</a>

<table>
<tr>
<th>ID</th>
<th>Nombre</th>
<th>Teléfono</th>
<th>Dirección</th>
<th>Rubro</th>
<th>Horario</th>
<th>Descripción</th>
<th>Coordenadas</th>
<th>Link</th>
<th>Acciones</th>
</tr>

<?php
$res = $conexion->query("
SELECT 
e.id_empresa,
e.nombre,
e.telefono,
e.direccion,
e.descripcion,
e.horario,
e.latitud,
e.longitud,
e.link_empresa,
c.nombre AS categoria
FROM empresas e
JOIN categorias c ON e.id_categoria = c.id_categoria
");

while($fila = $res->fetch_assoc()):
?>

<tr>
<td><?= $fila['id_empresa'] ?></td>
<td><?= htmlspecialchars($fila['nombre']) ?></td>
<td><?= htmlspecialchars($fila['telefono']) ?></td>
<td><?= htmlspecialchars($fila['direccion']) ?></td>
<td><?= htmlspecialchars($fila['categoria']) ?></td>

<td><?= !empty($fila['horario']) ? htmlspecialchars($fila['horario']) : '—' ?></td>

<td><?= !empty($fila['descripcion']) ? htmlspecialchars($fila['descripcion']) : '—' ?></td>

<td>
<?php if(!empty($fila['latitud']) && !empty($fila['longitud'])): ?>
<?= $fila['latitud'] ?> , <?= $fila['longitud'] ?>
<?php else: ?>
—
<?php endif; ?>
</td>

<td>
<?php if(!empty($fila['link_empresa'])): ?>
<a href="<?= htmlspecialchars($fila['link_empresa']) ?>" target="_blank">Ver</a>
<?php else: ?>
—
<?php endif; ?>
</td>

<td class="acciones">
<a href="editar.php?id=<?= $fila['id_empresa'] ?>" class="btn">Editar</a>
<a href="eliminar.php?id=<?= $fila['id_empresa'] ?>" class="btn btn-danger"
onclick="return confirm('¿Seguro que deseas eliminar esta empresa?');">
Eliminar
</a>
</td>
</tr>

<?php endwhile; ?>
</table>

<h2>Categorías</h2>
<a href="agregar_categoria.php" class="btn">Agregar Categoría</a>

<table>
<tr>
<th>ID</th>
<th>Nombre</th>
<th>Acciones</th>
</tr>

<?php
$res = $conexion->query("SELECT * FROM categorias");
while($fila = $res->fetch_assoc()):
?>

<tr>
<td><?= $fila['id_categoria'] ?></td>
<td><?= htmlspecialchars($fila['nombre']) ?></td>
<td class="acciones">
<a href="eliminar_categoria.php?id=<?= $fila['id_categoria'] ?>" class="btn btn-danger"
onclick="return confirm('¿Seguro que deseas eliminar esta categoría?');">
Eliminar
</a>
</td>
</tr>

<?php endwhile; ?>
</table>

</section>
</div>

</body>
</html>