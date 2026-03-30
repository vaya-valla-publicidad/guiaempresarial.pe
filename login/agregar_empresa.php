<?php
include 'proteger.php';
include '../db.php';
include '../includes/security.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validar token CSRF
    if (!validarCSRF()) {
        $error = "Token de seguridad inválido. Por favor recarga la página.";
        logSeguridad('csrf_invalido', 'Intento de agregar empresa sin token CSRF válido');
    } else {
        $nombre         = inputLimpio($_POST['nombre']);
        $telefono       = inputLimpio($_POST['telefono'] ?? '');
        $direccion      = inputLimpio($_POST['direccion'] ?? '');
        $id_categoria   = intval($_POST['id_categoria']);
        $descripcion    = inputLimpio($_POST['descripcion'] ?? '') ?: null;
        $horario        = inputLimpio($_POST['horario'] ?? '') ?: null;
        $ubicacion_link = inputLimpio($_POST['ubicacion_link'] ?? '') ?: null;
        $link_empresa   = inputLimpio($_POST['link_empresa'] ?? '') ?: null;
        $facebook       = inputLimpio($_POST['facebook'] ?? '') ?: null;
        $logo           = null;

        if (!empty($_FILES['logo']['name'])) {
            $resultado = subirImagenSegura($_FILES['logo'], __DIR__ . '/../assets/img/', [
                'tamano_max' => 2 * 1024 * 1024, // 2MB
                'redimensionar' => true,
                'ancho_max' => 800,
                'alto_max' => 600
            ]);
            
            if ($resultado['success']) {
                $logo = $resultado['nombre'];
            } else {
                $error = "Error con el logo: " . $resultado['error'];
            }
        }

    $stmt = $conexion->prepare(
    "INSERT INTO empresas (nombre,telefono,direccion,id_categoria,descripcion,horario,ubicacion_link,link_empresa,facebook,logo)
     VALUES (?,?,?,?,?,?,?,?,?,?)"
);
   $stmt->bind_param("sssissssss", $nombre, $telefono, $direccion, $id_categoria, $descripcion, $horario, $ubicacion_link, $link_empresa, $facebook, $logo);

    if (!$stmt->execute()) {
        $error = "Error SQL: " . $stmt->error;
    } else {
        $id_empresa = $stmt->insert_id;

        if (!empty($_FILES['fotos']['name'][0])) {
            $carpeta = __DIR__ . "/../assets/img/empresascarrusel/";
            $total   = min(count($_FILES['fotos']['name']), 5);
            for ($i = 0; $i < $total; $i++) {
                $tmp        = $_FILES['fotos']['tmp_name'][$i];
                $nombreFoto = uniqid() . "_" . basename($_FILES['fotos']['name'][$i]);
                $ruta       = $carpeta . $nombreFoto;
                if (move_uploaded_file($tmp, $ruta)) {
                    $stmtFoto = $conexion->prepare("INSERT INTO empresa_galeria (id_empresa,foto) VALUES (?,?)");
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
}

$categorias = $conexion->query("SELECT id_categoria,nombre FROM categorias");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Empresa</title>
    <link rel="stylesheet" href="../assets/css/login.css">
    <style>
        .mapa-wrap { display: flex; flex-direction: column; gap: 10px; }
        .mapa-buscar-row { display: flex; gap: 8px; }
        .mapa-buscar-row input { flex: 1; }
        .mapa-buscar-row button {
            padding: 10px 18px; background: #3498db; color: #fff;
            border: none; border-radius: 10px; cursor: pointer;
            font-weight: 600; white-space: nowrap; transition: 0.2s;
        }
        .mapa-buscar-row button:hover { background: #2e86c1; }
        .mapa-iframe {
            width: 100%; height: 320px; border-radius: 12px;
            border: 1px solid #ddd; display: block;
        }
        .mapa-tip { font-size: 12px; color: #888; }
    </style>
</head>
<body>
<div class="panel-container">
<section class="panel">
<h1 class="panel-title">Agregar Empresa</h1>
<div class="form-container">

    <?php if ($error): ?>
        <p style="color:red;text-align:center;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if ($success): ?>
        <p style="color:green;text-align:center;"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">

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
                    <option value="<?= $fila['id_categoria'] ?>"><?= htmlspecialchars($fila['nombre']) ?></option>
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
            <label>Ubicación en Google Maps</label>
            <div class="mapa-wrap">
                <div class="mapa-buscar-row">
                    <input type="text" id="mapa-query" placeholder="Busca el negocio aquí...">
                    <button type="button" onclick="buscarMapa()">Buscar</button>
                </div>
                <iframe id="mapa-iframe" class="mapa-iframe"
                    src="https://maps.google.com/maps?q=Peru&output=embed"
                    allowfullscreen loading="lazy"></iframe>
                <p class="mapa-tip">1. Busca el negocio arriba &nbsp;·&nbsp; 2. Entra a Google Maps, comparte la ubicación y pega el link abajo</p>
                <input type="text" name="ubicacion_link" id="ubicacion_link"
                       placeholder="Pega aquí la URL de Google Maps">
            </div>
        </div>

        <div class="form-group"><br>
            <div class="form-group">
    <label>Facebook</label>
    <input type="url" name="facebook" placeholder="https://facebook.com/tuempresa">
</div><br>
            <label>Enlace externo de la empresa</label>
            
            <input type="url" name="link_empresa">
        </div>

        <button type="submit" class="btn">Agregar Empresa</button>
    </form>

    <a href="admin.php" class="btn btn-danger">Volver al Panel</a>
</div>
</section>
</div>

<script>
function buscarMapa() {
    const q = document.getElementById('mapa-query').value.trim();
    if (!q) return;
    document.getElementById('mapa-iframe').src =
        'https://maps.google.com/maps?q=' + encodeURIComponent(q) + '&output=embed';
}

document.getElementById('mapa-query').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); buscarMapa(); }
});

document.getElementById('nombre').addEventListener('blur', function() {
    const val = this.value.trim();
    if (val && !document.getElementById('mapa-query').value) {
        document.getElementById('mapa-query').value = val;
        buscarMapa();
    }
});
</script>
</body>
</html>