<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Guia Empresarial</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="assets/img/image.png" type="image/png">
</head>
<body>

<?php include 'includes/header.php'; ?>
<?php include 'db.php'; ?>

<h1>Contacto</h1>

<div class="contactos">

<?php
$tabla = "empresas";

if(isset($_GET['empresa'])) {
    $id = intval($_GET['empresa']);
    $sql = "SELECT * FROM $tabla WHERE id_empresa = $id";
} else {
    $sql = "SELECT * FROM $tabla";
}

$resultado = $conexion->query($sql);

if($resultado && $resultado->num_rows > 0):
    while($fila = $resultado->fetch_assoc()):
?>

    <div class="contacto-card" onclick="toggleContacto(this)">

        <div class="contacto-resumen">
            <h3><?= $fila['nombre'] ?? 'Empresa' ?></h3>
            <p><?= $fila['direccion'] ?? '' ?></p>
        </div>

        <div class="contacto-detalle">

            <?php
            $telefono = $fila['telefono'] ?? $fila['celular'] ?? $fila['whatsapp'] ?? null;
            if($telefono):
                $numero = preg_replace('/[^0-9]/', '', $telefono);
            ?>
                <p>
                    <strong>Teléfono:</strong> <?= $telefono ?><br>
                    <a class="whatsapp-btn" href="https://wa.me/<?= $numero ?>" target="_blank">
                        Contactar por WhatsApp
                    </a>
                </p>
            <?php endif; ?>

            <?php if(isset($fila['email'])): ?>
                <p><strong>Email:</strong> <?= $fila['email'] ?></p>
            <?php endif; ?>

            <?php if(isset($fila['direccion'])): ?>
                <p><strong>Dirección:</strong> <?= $fila['direccion'] ?></p>
            <?php endif; ?>

            <?php if(isset($fila['latitud']) && isset($fila['longitud'])): ?>
                <iframe
                    src="https://www.google.com/maps?q=<?= $fila['latitud'] ?>,<?= $fila['longitud'] ?>&hl=es&z=14&output=embed"
                    loading="lazy">
                </iframe>
            <?php elseif(isset($fila['direccion'])): ?>
                <iframe
                    src="https://www.google.com/maps?q=<?= urlencode($fila['direccion']) ?>&hl=es&z=14&output=embed"
                    loading="lazy">
                </iframe>
            <?php endif; ?>

        </div>

    </div>

<?php
    endwhile;
else:
    echo "<p>No hay registros para mostrar.</p>";
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