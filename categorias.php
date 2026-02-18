<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Categorías</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <?php include 'db.php'; ?>

    <h1>Categorías</h1>
    <p>Selecciona un rubro para ver ejemplos:</p>

    <div class="categorias-grid">
        <?php
        $resultado = $conexion->query("SELECT * FROM categorias");
        while($fila = $resultado->fetch_assoc()) {
            echo "<a href='empresas.php?id_categoria={$fila['id_categoria']}' style='text-decoration:none;'>
                <div class='categoria-box'>
                    {$fila['nombre']}
                </div>
              </a>";

        }
        ?>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>