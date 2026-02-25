<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Empresas</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="assets/img/image.png" type="image/png">
</head>
<body>

<?php include 'includes/header.php'; ?>
<?php include 'db.php'; ?>

<h2>Listado de Empresas</h2>

<div class="empresas-grid">

<?php
$id_categoria = $_GET['id_categoria'] ?? null;

$sql = "SELECT e.id_empresa, e.nombre, e.telefono, e.direccion, c.nombre AS categoria
        FROM empresas e
        JOIN categorias c ON e.id_categoria = c.id_categoria";

if ($id_categoria) {
    $sql .= " WHERE e.id_categoria = " . intval($id_categoria);
}

$resultado = $conexion->query($sql);

while($fila = $resultado->fetch_assoc()) {
    echo "
    <a href='contacto.php?empresa={$fila['id_empresa']}' class='empresa-card-link'>
        <div class='empresa-card'>
            <h3>{$fila['nombre']}</h3>
            <p><strong>Rubro:</strong> {$fila['categoria']}</p>
            <p><strong>Teléfono:</strong> {$fila['telefono']}</p>
            <p><strong>Dirección:</strong> {$fila['direccion']}</p>
        </div>
    </a>";
}
?>

</div>

<?php include 'includes/footer.php'; ?>

</body>
</html>