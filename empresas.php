<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Empresas</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <?php include 'db.php'; ?>

    <h2>Listado de Empresas</h2>

    <div class="empresas-grid">
        <?php
        if (!empty($_GET['id_empresa'])) {
            $id = intval($_GET['id_empresa']);
            $sql = "SELECT e.nombre, e.telefono, e.direccion, c.nombre AS categoria
                    FROM empresas e
                    JOIN categorias c ON e.id_categoria = c.id_categoria
                    WHERE e.id_empresa = $id";
            $resultado = $conexion->query($sql);

            if ($resultado->num_rows > 0) {
                $fila = $resultado->fetch_assoc();
                echo "<div class='empresa-card'>
                        <h3>{$fila['nombre']}</h3>
                        <p><strong>Rubro:</strong> {$fila['categoria']}</p>
                        <p><strong>Teléfono:</strong> {$fila['telefono']}</p>
                        <p><strong>Dirección:</strong> {$fila['direccion']}</p>
                      </div>";
            } else {
                echo "<p>No se encontró la empresa</p>";
            }

        } else {
            $id_categoria = $_GET['id_categoria'] ?? null;

            $sql = "SELECT e.nombre, e.telefono, e.direccion, c.nombre AS categoria
                    FROM empresas e
                    JOIN categorias c ON e.id_categoria = c.id_categoria";

            if ($id_categoria) {
                $sql .= " WHERE e.id_categoria = " . intval($id_categoria);
            }

            $resultado = $conexion->query($sql);

            while($fila = $resultado->fetch_assoc()) {
                echo "<div class='empresa-card'>
                        <h3>{$fila['nombre']}</h3>
                        <p><strong>Rubro:</strong> {$fila['categoria']}</p>
                        <p><strong>Teléfono:</strong> {$fila['telefono']}</p>
                        <p><strong>Dirección:</strong> {$fila['direccion']}</p>
                      </div>";
            }
        }
        ?>
    </div>


    <?php include 'includes/footer.php'; ?>
</body>
</html>