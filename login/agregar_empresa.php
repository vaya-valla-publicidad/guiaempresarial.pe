<?php
include 'proteger.php';
include '../db.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim($_POST['nombre']);
    $telefono = trim($_POST['telefono']);
    $direccion = trim($_POST['direccion']);
    $id_categoria = intval($_POST['id_categoria']);

    $descripcion = trim($_POST['descripcion']) ?: null;
    $horario = trim($_POST['horario']) ?: null;
    $latitud = trim($_POST['latitud']) ?: null;
    $longitud = trim($_POST['longitud']) ?: null;
    $link_empresa = trim($_POST['link_empresa']) ?: null;

    $stmt = $conexion->prepare(
        "INSERT INTO empresas 
        (nombre, telefono, direccion, id_categoria, descripcion, horario, latitud, longitud, link_empresa) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    $stmt->bind_param(
        "sssissdds",
        $nombre,
        $telefono,
        $direccion,
        $id_categoria,
        $descripcion,
        $horario,
        $latitud,
        $longitud,
        $link_empresa
    );

    if (!$stmt->execute()) {
        $error = "Error SQL: " . $stmt->error;
    } else {
        $success = "Empresa agregada correctamente ✅";
    }

    $stmt->close();
}

$categorias = $conexion->query("SELECT id_categoria, nombre FROM categorias");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Agregar Empresa</title>
<link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>

<div class="panel-container">
    <section class="panel">

        <h1 class="panel-title">Agregar Empresa</h1>

        <div class="form-container">

            <?php if($error): ?>
                <p style="color:red; text-align:center;"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <?php if($success): ?>
                <p style="color:green; text-align:center;"><?= htmlspecialchars($success) ?></p>
            <?php endif; ?>

            <form method="post">

                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" name="nombre" required>
                </div>

                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text" name="telefono">
                </div>

                <div class="form-group">
                    <label>Dirección</label>
                    <input type="text" name="direccion">
                </div>

                <div class="form-group">
                    <label>Categoría</label>
                    <select name="id_categoria" required>
                        <?php while($fila = $categorias->fetch_assoc()): ?>
                            <option value="<?= $fila['id_categoria'] ?>">
                                <?= htmlspecialchars($fila['nombre']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="descripcion"></textarea>
                </div>

                <div class="form-group">
                    <label>Horario de atención</label>
                    <input type="text" name="horario">
                </div>

                <div class="form-group">
                    <label>Latitud</label>
                    <input type="text" name="latitud" placeholder="-12.046374">
                </div>

                <div class="form-group">
                    <label>Longitud</label>
                    <input type="text" name="longitud" placeholder="-77.042793">
                </div>

                <div class="form-group">
                    <label>Enlace externo de la empresa</label>
                    <input type="url" name="link_empresa">
                </div>

                <button type="submit" class="btn">Agregar Empresa</button>
            </form>

            <a href="admin.php" class="btn btn-danger">Volver al Panel</a>

        </div>

    </section>
</div>

</body>
</html>