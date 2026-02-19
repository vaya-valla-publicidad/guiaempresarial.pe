<?php
session_start();
include '../db.php';

// 🔐 Seguridad
if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['admin', 'editor'])) {
    header("Location: login.php");
    exit;
}

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

<section class="panel">
<h2>Agregar Categoría</h2>

<?php if($error): ?>
<p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if($success): ?>
<p style="color:green;"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<form method="post">
    <label>Nombre de la categoría</label>
    <input type="text" name="nombre" required>

    <button type="submit" class="btn">Agregar</button>
</form>

<br>
<a href="admin.php" class="btn btn-danger">Volver al Panel</a>

</section>

</body>
</html>
