<?php require_once __DIR__ . '/proteger.php'; ?>
<?php
include '../db.php';

if (!isset($_GET['id'])) {
    header("Location: admin.php");
    exit;
}

$id = intval($_GET['id']);

$error = "";
$success = "";

$stmt = $conexion->prepare("SELECT * FROM empresas WHERE id_empresa=?");
$stmt->bind_param("i", $id);
$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    die("Empresa no encontrada");
}

$empresa = $resultado->fetch_assoc();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim($_POST['nombre']);
    $telefono = trim($_POST['telefono']);
    $direccion = trim($_POST['direccion']);
    $id_categoria = intval($_POST['id_categoria']);

    $descripcion = trim($_POST['descripcion']) ?: null;
    $horario = trim($_POST['horario']) ?: null;
    $ubicacion_link = trim($_POST['ubicacion_link']) ?: null;
    $link_empresa = trim($_POST['link_empresa']) ?: null;

    $logo = $empresa['logo'];

    if (!empty($_FILES['logo']['name'])) {

        $nombreArchivo = uniqid() . "_" . basename($_FILES['logo']['name']);
        $rutaDestino = __DIR__ . "/../assets/img/" . $nombreArchivo;

        if (move_uploaded_file($_FILES['logo']['tmp_name'], $rutaDestino)) {
            $logo = $nombreArchivo;
        }
    }

    $stmt = $conexion->prepare(
        "UPDATE empresas SET
nombre=?,
telefono=?,
direccion=?,
id_categoria=?,
descripcion=?,
horario=?,
ubicacion_link=?,
link_empresa=?,
logo=?
WHERE id_empresa=?"
    );

    $stmt->bind_param(
        "sssisssssi",
        $nombre,
        $telefono,
        $direccion,
        $id_categoria,
        $descripcion,
        $horario,
        $ubicacion_link,
        $link_empresa,
        $logo,
        $id
    );

    if ($stmt->execute()) {
        $success = "Empresa actualizada correctamente";
    } else {
        $error = "Error: " . $stmt->error;
    }

    $stmt->close();

    if (!empty($_FILES['fotos']['name'][0])) {

        $carpeta = __DIR__ . "/../assets/img/empresas/";

        foreach ($_FILES['fotos']['tmp_name'] as $key => $tmp) {

            $nombreFoto = uniqid() . "_" . basename($_FILES['fotos']['name'][$key]);
            $ruta = $carpeta . $nombreFoto;

            if (move_uploaded_file($tmp, $ruta)) {

                $stmtFoto = $conexion->prepare(
                    "INSERT INTO empresa_galeria (id_empresa,foto) VALUES (?,?)"
                );

                $stmtFoto->bind_param("is", $id, $nombreFoto);
                $stmtFoto->execute();
                $stmtFoto->close();
            }
        }
    }
}

$categorias = $conexion->query("SELECT id_categoria,nombre FROM categorias");
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

        <?php if ($error): ?>
            <p style="color:red"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <?php if ($success): ?>
            <p style="color:green"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">

            <h3>Logo de la empresa</h3>

            <?php if (!empty($empresa['logo'])): ?>

                <div style="position:relative;display:inline-block">

                    <img
                        src="../assets/img/<?= htmlspecialchars($empresa['logo']) ?>"
                        style="width:100px;height:100px;object-fit:cover;border-radius:6px;">

                    <a
                        href="javascript:void(0)"
                        onclick="eliminarFoto(<?= $id ?>,'logo',this)"
                        style="
position:absolute;
top:5px;
right:5px;
background:red;
color:white;
padding:4px 6px;
border-radius:4px;
text-decoration:none;
font-size:12px;
">
                        X
                    </a>

                </div>

            <?php endif; ?>

            <br><br>

            <input type="file" name="logo" accept="image/*">

            <h3>Fotos del carrusel</h3>

            <input type="file" name="fotos[]" multiple accept="image/*">

            <br><br>

            <h4>Fotos actuales</h4>

            <div style="display:flex;gap:10px;flex-wrap:wrap;">

                <?php

                $stmt = $conexion->prepare(
                    "SELECT * FROM empresa_galeria WHERE id_empresa=?"
                );

                $stmt->bind_param("i", $id);
                $stmt->execute();

                $fotos = $stmt->get_result();

                if ($fotos->num_rows == 0) {
                    echo "<p>No hay fotos aún</p>";
                }

                while ($foto = $fotos->fetch_assoc()):
                ?>

                    <div style="position:relative">

                        <img
                            src="../assets/img/empresas/<?= htmlspecialchars($foto['foto']) ?>"
                            style="width:120px;height:120px;object-fit:cover;border-radius:6px;">

                        <a
                            href="javascript:void(0)"
                            onclick="eliminarFoto(<?= $foto['id_foto'] ?>,'galeria',this)"
                            style="
position:absolute;
top:5px;
right:5px;
background:red;
color:white;
padding:4px 6px;
border-radius:4px;
font-size:12px;
text-decoration:none;
">
                            X
                        </a>

                    </div>

                <?php endwhile; ?>

            </div>

            <hr>

            <label>Nombre</label>
            <input type="text" id="nombre" name="nombre"
                value="<?= htmlspecialchars($empresa['nombre']) ?>" required>

            <label>Teléfono</label>
            <input type="text" name="telefono"
                value="<?= htmlspecialchars($empresa['telefono']) ?>">

            <label>Dirección</label>
            <input type="text" name="direccion"
                value="<?= htmlspecialchars($empresa['direccion']) ?>">

            <label>Categoría</label>

            <select name="id_categoria" required>

                <?php while ($fila = $categorias->fetch_assoc()): ?>

                    <option value="<?= $fila['id_categoria'] ?>"
                        <?= $fila['id_categoria'] == $empresa['id_categoria'] ? 'selected' : '' ?>>

                        <?= htmlspecialchars($fila['nombre']) ?>

                    </option>

                <?php endwhile; ?>

            </select>

            <label>Descripción</label>

            <textarea name="descripcion"><?= htmlspecialchars($empresa['descripcion']) ?></textarea>

            <label>Horario</label>

            <input type="text" name="horario"
                value="<?= htmlspecialchars($empresa['horario']) ?>">

            <label>Ubicación desde Google Maps</label>

            <input
                type="text"
                name="ubicacion_link"
                value="<?= htmlspecialchars($empresa['ubicacion_link']) ?>"
                placeholder="Pega la URL de Google Maps">

            <a id="mapsLink" href="#" target="_blank" class="btn">
                Buscar en Google Maps
            </a>

            <label>Enlace externo de la empresa</label>

            <input type="url" name="link_empresa"
                value="<?= htmlspecialchars($empresa['link_empresa']) ?>">

            <br><br>

            <button type="submit" class="btn">
                Actualizar empresa
            </button>

        </form>

        <br>

        <a href="admin.php" class="btn btn-danger">
            Volver al Panel
        </a>

    </section>

    <script>
        function eliminarFoto(id, tipo, elemento) {

            if (!confirm("¿Eliminar esta imagen?")) {
                return;
            }

            fetch("eliminar_foto.php?id=" + id + "&tipo=" + tipo)
                .then(res => res.text())
                .then(data => {

                    if (data.trim() == "ok") {
                        elemento.parentElement.remove();
                    } else {
                        alert("No se pudo eliminar");
                    }

                });

        }

        function actualizarLink() {

            var nombre = document.getElementById("nombre").value;

            var url = "https://www.google.com/maps/search/?api=1&query=" + encodeURIComponent(nombre);

            document.getElementById("mapsLink").href = url;

        }

        actualizarLink();

        document.getElementById("nombre").addEventListener("input", actualizarLink);
    </script>

</body>

</html>