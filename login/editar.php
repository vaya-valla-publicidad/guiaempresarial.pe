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

    $descripcion = trim($_POST['descripcion']) ?: null;
    $horario = trim($_POST['horario']) ?: null;
    $ubicacion_link = trim($_POST['ubicacion_link']) ?: null;
    $link_empresa = trim($_POST['link_empresa']) ?: null;

    $logo = $empresa['logo']; // mantener el actual si no se sube nada
    if (!empty($_FILES['logo']['name'])) {
        $nombreArchivo = time() . "_" . basename($_FILES['logo']['name']);
        $rutaDestino = __DIR__ . "/../assets/img/" . $nombreArchivo;

        if (move_uploaded_file($_FILES['logo']['tmp_name'], $rutaDestino)) {
            $logo = $nombreArchivo;
        } else {
            $error = "Error al subir la imagen. Verifica que exista la carpeta /assets/img/";
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

    if (!$stmt->execute()) {
        $error = "Error: " . $stmt->error;
    } else {
        $success = "Empresa actualizada correctamente ✅";

        $stmt2 = $conexion->prepare("SELECT * FROM empresas WHERE id_empresa = ?");
        $stmt2->bind_param("i", $id);
        $stmt2->execute();
        $empresa = $stmt2->get_result()->fetch_assoc();
        $stmt2->close();
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

    <form method="post" enctype="multipart/form-data">

        <label>Logo de la empresa</label>
        <?php if(!empty($empresa['logo'])): ?>
            <img src="../assets/img/<?= htmlspecialchars($empresa['logo']) ?>" 
                 alt="Logo actual" style="width:80px;height:80px;object-fit:cover;border-radius:5px;">
        <?php endif; ?>
        <input type="file" name="logo" accept="image/*">

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
            <?php while($fila = $categorias->fetch_assoc()): ?>
                <option value="<?= $fila['id_categoria'] ?>"
                    <?= $fila['id_categoria'] == $empresa['id_categoria'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($fila['nombre']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label>Descripción</label>
        <textarea name="descripcion"><?= htmlspecialchars($empresa['descripcion']) ?></textarea>

        <label>Horario de atención</label>
        <input type="text" name="horario"
               value="<?= htmlspecialchars($empresa['horario']) ?>">

        <!-- Campo para pegar URL de Google Maps -->
        <label>Ubicación desde Google Maps</label>
        <input type="text" name="ubicacion_link"
               value="<?= htmlspecialchars($empresa['ubicacion_link']) ?>"
               placeholder="Pega aquí la URL de Google Maps">

        <!-- Botón para abrir Google Maps con el nombre -->
        <a id="mapsLink" href="#" target="_blank" class="btn">Buscar en Google Maps</a>

        <label>Enlace externo de la empresa</label>
        <input type="url" name="link_empresa"
               value="<?= htmlspecialchars($empresa['link_empresa']) ?>">

        <button type="submit" class="btn">Actualizar</button>
    </form>

    <br>
    <a href="admin.php" class="btn btn-danger">Volver al Panel</a>

</section>

<script>
  function actualizarLink() {
      var nombre = document.getElementById('nombre').value;
      var url = "https://www.google.com/maps/search/?api=1&query=" + encodeURIComponent(nombre);
      document.getElementById('mapsLink').href = url;
  }

  actualizarLink();
  document.getElementById('nombre').addEventListener('input', actualizarLink);
</script>

</body>
</html>
