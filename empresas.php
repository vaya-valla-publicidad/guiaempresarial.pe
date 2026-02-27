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

<h1>Listado de Empresas</h1>

<div class="contactos">

<?php
$tabla = "empresas";

$id_categoria = $_GET['id_categoria'] ?? null;
$id_empresa   = $_GET['empresa'] ?? null;
$buscar       = $_GET['buscar'] ?? null;

$sql = "SELECT e.*, c.nombre AS categoria
        FROM $tabla e
        JOIN categorias c ON e.id_categoria = c.id_categoria";

$where = [];

if ($id_empresa) {
    $where[] = "e.id_empresa = " . intval($id_empresa);
}
elseif ($id_categoria) {
    $where[] = "e.id_categoria = " . intval($id_categoria);
}
elseif ($buscar) {
    $texto = $conexion->real_escape_string($buscar);
    $where[] = "e.nombre LIKE '%$texto%'";
}
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$resultado = $conexion->query($sql);

if($resultado && $resultado->num_rows > 0):
    while($fila = $resultado->fetch_assoc()):
?>

    <div class="contacto-card" onclick="toggleContacto(this)">

        <div class="contacto-resumen">
            <h3><?= htmlspecialchars($fila['nombre']) ?></h3>
            <p><?= htmlspecialchars($fila['direccion'] ?? '') ?></p>
        </div>

        <div class="contacto-detalle">

            <?php
            $telefono = $fila['telefono'] ?? $fila['celular'] ?? $fila['whatsapp'] ?? null;
            if($telefono):
                $numero = preg_replace('/[^0-9]/', '', $telefono);
            ?>
                <p>
                    <strong>Teléfono:</strong> <?= htmlspecialchars($telefono) ?><br>
                    <a class="whatsapp-btn" href="https://wa.me/<?= $numero ?>" target="_blank">
                        Contactar por WhatsApp
                    </a>
                </p>
            <?php endif; ?>

            <?php if(!empty($fila['email'])): ?>
                <p><strong>Email:</strong> <?= htmlspecialchars($fila['email']) ?></p>
            <?php endif; ?>

            <?php if(!empty($fila['direccion'])): ?>
                <p><strong>Dirección:</strong> <?= htmlspecialchars($fila['direccion']) ?></p>
            <?php endif; ?>

            <?php if(!empty($fila['latitud']) && !empty($fila['longitud'])): ?>
                <iframe
                    src="https://www.google.com/maps?q=<?= $fila['latitud'] ?>,<?= $fila['longitud'] ?>&hl=es&z=14&output=embed"
                    loading="lazy">
                </iframe>
            <?php endif; ?>

        </div>

        <div class="categoria-label">
            <?= htmlspecialchars($fila['categoria']) ?>
        </div>

    </div>

<?php
    endwhile;
else:
    echo "<p>No hay resultados para mostrar.</p>";
endif;
?>

</div>

<script>
function toggleContacto(elemento) {
    elemento.classList.toggle("activo");
}
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>