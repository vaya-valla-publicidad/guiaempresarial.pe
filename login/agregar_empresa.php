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
    $ubicacion_link = trim($_POST['ubicacion_link']) ?: null;
    $link_empresa = trim($_POST['link_empresa']) ?: null;

    $logo = null;

    if (!empty($_FILES['logo']['name'])) {

        $nombreLogo = uniqid() . "_" . basename($_FILES['logo']['name']);
        $rutaLogo = __DIR__ . "/../assets/img/" . $nombreLogo;

        if (move_uploaded_file($_FILES['logo']['tmp_name'], $rutaLogo)) {
            $logo = $nombreLogo;
        }
    }

    $stmt = $conexion->prepare(
        "INSERT INTO empresas 
(nombre,telefono,direccion,id_categoria,descripcion,horario,ubicacion_link,link_empresa,logo)
VALUES (?,?,?,?,?,?,?,?,?)"
    );

    $stmt->bind_param(
        "sssisssss",
        $nombre,
        $telefono,
        $direccion,
        $id_categoria,
        $descripcion,
        $horario,
        $ubicacion_link,
        $link_empresa,
        $logo
    );

    if (!$stmt->execute()) {

        $error = "Error SQL: " . $stmt->error;
    } else {

        $id_empresa = $stmt->insert_id;

        if (!empty($_FILES['fotos']['name'][0])) {

            $carpeta = __DIR__ . "/../assets/img/empresas/";

            /* máximo 5 fotos */
            $total = min(count($_FILES['fotos']['name']), 5);

            for ($i = 0; $i < $total; $i++) {

                $tmp = $_FILES['fotos']['tmp_name'][$i];

                $nombreFoto = uniqid() . "_" . basename($_FILES['fotos']['name'][$i]);
                $ruta = $carpeta . $nombreFoto;

                if (move_uploaded_file($tmp, $ruta)) {

                    $stmtFoto = $conexion->prepare(
                        "INSERT INTO empresa_galeria (id_empresa,foto) VALUES (?,?)"
                    );

                    $stmtFoto->bind_param("is", $id_empresa, $nombreFoto);
                    $stmtFoto->execute();
                    $stmtFoto->close();
                }
            }
        }

        $success = "Empresa agregada correctamente";
    }

    $stmt->close();
}

$categorias = $conexion->query("SELECT id_categoria,nombre FROM categorias");
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

                <?php if ($error): ?>
                    <p style="color:red;text-align:center;">
                        <?= htmlspecialchars($error) ?>
                    </p>
                <?php endif; ?>

                <?php if ($success): ?>
                    <p style="color:green;text-align:center;">
                        <?= htmlspecialchars($success) ?>
                    </p>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data">

                    <div class="form-group">
                        <label>Logo o imagen principal</label>
                        <input type="file" name="logo" accept="image/*">
                    </div>

                    <div class="form-group">
                        <label>Fotos para carrusel (máximo 5)</label>
                        <input type="file" name="fotos[]" multiple accept="image/*">
                    </div>

                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" id="nombre" name="nombre" required>
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

                            <?php while ($fila = $categorias->fetch_assoc()): ?>

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
                        <label>Ubicación desde Google Maps</label>

                        <input type="text"
                            name="ubicacion_link"
                            placeholder="Pega aquí la URL de Google Maps">
                    </div>

                    <a id="mapsLink" href="#" target="_blank" class="btn">
                        Buscar en Google Maps
                    </a>

                    <div class="form-group">
                        <label>Enlace externo de la empresa</label>
                        <input type="url" name="link_empresa">
                    </div>

                    <button type="submit" class="btn">
                        Agregar Empresa
                    </button>

                </form>

                <a href="admin.php" class="btn btn-danger">
                    Volver al Panel
                </a>

            </div>

        </section>

    </div>

    <script>
        function actualizarLink() {

            var nombre = document.getElementById("nombre").value;

            var url = "https://www.google.com/maps/search/?api=1&query=" + encodeURIComponent(nombre);

            document.getElementById("mapsLink").href = url;

        }

        document.getElementById("nombre").addEventListener("input", actualizarLink);

        actualizarLink();
    </script>

</body>

</html>