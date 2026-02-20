<?php
include 'proteger.php';
include '../db.php';

if (!isset($_GET['id'])) {
    header("Location: admin.php");
    exit;
}

$id = intval($_GET['id']);

$error = "";
$success = "";

$stmt = $conexion->prepare("SELECT * FROM empresas WHERE id_empresa = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    die("Empresa no encontrada.");
}

$empresa = $resultado->fetch_assoc();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim($_POST['nombre']);
    $telefono = trim($_POST['telefono']);
    $direccion = trim($_POST['direccion']);
    $id_categoria = intval($_POST['id_categoria']);

    $stmt = $conexion->prepare(
        "UPDATE empresas 
         SET nombre=?, telefono=?, direccion=?, id_categoria=? 
         WHERE id_empresa=?"
    );

    $stmt->bind_param("sssii", $nombre, $telefono, $direccion, $id_categoria, $id);

    if (!$stmt->execute()) {
        $error = "Error: " . $stmt->error;
    } else {
        $success = "Empresa actualizada correctamente ✅";
    }

    $stmt->close();
}

$categorias = $conexion->query("SELECT id_categoria, nombre FROM categorias");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar Empresa</title>
<link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>

<section class="panel">
    <h2>Editar Empresa</h2>

    <?php if($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if($success): ?>
        <p style="color:green;"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form method="post">

        <label>Nombre</label>
        <input type="text" name="nombre" 
               value="<?= htmlspecialchars($empresa['nombre']) ?>" required>

        <label>Teléfono</label>
        <input type="text" name="telefono" 
               value="<?= htmlspecialchars($empresa['telefono']) ?>">

        <label>Dirección</label>
        <input type="text" name="direccion" 
               value="<?= htmlspecialchars($empresa['direccion']) ?>">

        <label>Categoría</label>
        <select name="id_categoria" required>
            <?php while($fila = $categorias->fetch_assoc()): ?>
                <option value="<?= $fila['id_categoria'] ?>"
                    <?= $fila['id_categoria'] == $empresa['id_categoria'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($fila['nombre']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <button type="submit" class="btn">Actualizar</button>
    </form>

    <br>
    <a href="admin.php" class="btn btn-danger">Volver al Panel</a>

</section>

</body>
</html>