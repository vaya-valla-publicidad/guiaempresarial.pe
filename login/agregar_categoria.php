<?php require_once __DIR__ . '/proteger.php'; ?>
<?php
include '../db.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim($_POST['nombre']);

    if (!empty($nombre)) {

        $stmt = $conexion->prepare("INSERT INTO categorias (nombre) VALUES (?)");
        $stmt->bind_param("s", $nombre);

        if (!$stmt->execute()) {
            $error = "Error: " . $stmt->error;
        } else {
            $success = "Categoría agregada correctamente ✅";
        }

        $stmt->close();
    } else {
        $error = "El nombre no puede estar vacío.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Agregar Categoría</title>
<link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>

<div class="panel-container">
    <section class="panel">

        <h1 class="panel-title">Agregar Categoría</h1>

        <div class="form-container">

            <?php if($error): ?>
                <p style="color:red; text-align:center;"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <?php if($success): ?>
                <p style="color:green; text-align:center;"><?= htmlspecialchars($success) ?></p>
            <?php endif; ?>

            <form method="post">

                <div class="form-group">
                    <label>Nombre de la categoría</label>
                    <input type="text" name="nombre" required>
                </div>

                <button type="submit" class="btn">Agregar</button>
            </form>

            <a href="admin.php" class="btn btn-danger">Volver al Panel</a>

        </div>

    </section>
</div>

</body>
</html>