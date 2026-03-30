<?php require_once __DIR__ . '/proteger.php'; ?>
<?php
include '../db.php';
$rol = $_SESSION['rol'];

if (!isset($_GET['id'])) { header("Location: panel.php"); exit; }

$id      = intval($_GET['id']);
$error   = "";
$success = isset($_GET['ok']) ? "Empresa actualizada correctamente ✅" : "";

// ─── Validación de imágenes ────────────────────────────────────────────────
function validarImagen($tmpPath, $nombreOriginal): bool {
    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $ext = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
    if (!in_array($ext, $extensionesPermitidas)) return false;

    // Verifica el contenido real del archivo (no solo el nombre/extensión)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $tmpPath);
    finfo_close($finfo);

    $mimesPermitidos = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    return in_array($mime, $mimesPermitidos);
}
// ──────────────────────────────────────────────────────────────────────────

$stmt = $conexion->prepare("SELECT * FROM empresas WHERE id_empresa=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
if ($resultado->num_rows === 0) { die("Empresa no encontrada"); }
$empresa = $resultado->fetch_assoc();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre         = trim($_POST['nombre']);
    $telefono       = trim($_POST['telefono']);
    $direccion      = trim($_POST['direccion']);
    $id_categoria   = intval($_POST['id_categoria']);
    $descripcion    = trim($_POST['descripcion']) ?: null;
    $horario        = trim($_POST['horario']) ?: null;
    $ubicacion_link = trim($_POST['ubicacion_link']) ?: null;
    $link_empresa   = trim($_POST['link_empresa']) ?: null;
    $facebook       = trim($_POST['facebook']) ?: null;
    $logo           = $empresa['logo'];
    $destacada_new  = intval($_POST['destacada']);

    if ($destacada_new === 1 && $empresa['destacada'] == 0) {
        $total_dest = $conexion->query("SELECT COUNT(*) as total FROM empresas WHERE destacada = 1")->fetch_assoc()['total'];
        if ($total_dest >= 3) {
            $error = "Ya hay 3 empresas destacadas. Quita una antes de destacar esta.";
            $destacada_new = 0;
        }
    }

    // ─── Logo: validar antes de mover ─────────────────────────────────────
    if (!empty($_FILES['logo']['name'])) {
        if (!validarImagen($_FILES['logo']['tmp_name'], $_FILES['logo']['name'])) {
            $error = "El logo debe ser una imagen válida (jpg, jpeg, png, webp, gif).";
        } else {
            $nombreArchivo = uniqid() . "_" . basename($_FILES['logo']['name']);
            $rutaDestino   = __DIR__ . "/../assets/img/" . $nombreArchivo;
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $rutaDestino)) {
                $logo = $nombreArchivo;
            }
        }
    }
    // ──────────────────────────────────────────────────────────────────────

    if (empty($error)) {
        $stmt = $conexion->prepare(
            "UPDATE empresas SET nombre=?, telefono=?, direccion=?, id_categoria=?,
             descripcion=?, horario=?, ubicacion_link=?, link_empresa=?, facebook=?, logo=?, destacada=?
             WHERE id_empresa=?"
        );
        $stmt->bind_param(
            "sssissssssii",
            $nombre, $telefono, $direccion, $id_categoria,
            $descripcion, $horario, $ubicacion_link, $link_empresa, $facebook, $logo, $destacada_new, $id
        );

        if ($stmt->execute()) {
            $stmt->close();

            // ─── Carrusel: validar cada foto antes de mover ───────────────
            if (!empty($_FILES['fotos']['name'][0])) {
                $carpeta      = __DIR__ . "/../assets/img/empresascarrusel/";
                $orden_actual = $conexion->query("SELECT MAX(orden) as max FROM empresa_galeria WHERE id_empresa=$id")->fetch_assoc()['max'] ?? 0;
                foreach ($_FILES['fotos']['tmp_name'] as $key => $tmp) {
                    if (empty($_FILES['fotos']['name'][$key])) continue;
                    if (!validarImagen($tmp, $_FILES['fotos']['name'][$key])) continue;
                    $nombreFoto = uniqid() . "_" . basename($_FILES['fotos']['name'][$key]);
                    $ruta       = $carpeta . $nombreFoto;
                    if (move_uploaded_file($tmp, $ruta)) {
                        $orden_actual++;
                        $stmtFoto = $conexion->prepare("INSERT INTO empresa_galeria (id_empresa, foto, orden) VALUES (?, ?, ?)");
                        $stmtFoto->bind_param("isi", $id, $nombreFoto, $orden_actual);
                        $stmtFoto->execute();
                        $stmtFoto->close();
                    }
                }
            }
            // ─────────────────────────────────────────────────────────────

            // PRG: redirigir para evitar duplicados al recargar la página
            header("Location: editar.php?id=" . $id . "&ok=1");
            exit;

        } else {
            $error = "Error: " . $stmt->error;
            $stmt->close();
        }
    }
}

$categorias       = $conexion->query("SELECT id_categoria, nombre FROM categorias");
$mapaQuery        = urlencode($empresa['nombre'] . ' ' . $empresa['direccion']);
$total_destacadas = $conexion->query("SELECT COUNT(*) as total FROM empresas WHERE destacada = 1")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Empresa</title>
    <link rel="stylesheet" href="../assets/css/login.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.2/Sortable.min.js"></script>
    <style>
        .fotos-grid { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 10px; }
        .foto-item {
            position: relative; cursor: grab; user-select: none;
            border-radius: 10px; overflow: hidden;
            border: 2px solid #e0e0e0; transition: border-color .2s, box-shadow .2s;
        }
        .foto-item:hover { border-color: #3498db; box-shadow: 0 4px 12px rgba(52,152,219,.25); }
        .foto-item.sortable-chosen { border-color: #3498db; box-shadow: 0 8px 20px rgba(52,152,219,.3); opacity: .85; }
        .foto-item.sortable-ghost  { opacity: .3; }
        .foto-item img { width: 120px; height: 120px; object-fit: cover; display: block; }
        .foto-item .orden-badge {
            position: absolute; top: 5px; left: 5px;
            background: rgba(27,58,87,.8); color: #fff;
            font-size: 11px; font-weight: 700; padding: 2px 7px; border-radius: 20px;
        }
        .foto-item .btn-borrar {
            position: absolute; top: 5px; right: 5px;
            background: red; color: white; padding: 3px 7px;
            border-radius: 4px; font-size: 12px; cursor: pointer; border: none;
        }
        .drag-hint { font-size: 12px; color: #888; margin-top: 6px; }
        .btn-guardar-orden {
            margin-top: 12px; padding: 9px 22px;
            background: #2c3e50; color: #fff; border: none;
            border-radius: 10px; font-weight: 600; cursor: pointer;
            font-size: 14px; transition: .2s;
        }
        .btn-guardar-orden:hover  { background: #3498db; }
        .btn-guardar-orden.guardado { background: #27ae60; }
        .mapa-wrap { display: flex; flex-direction: column; gap: 10px; }
        .mapa-buscar-row { display: flex; gap: 8px; }
        .mapa-buscar-row input { flex: 1; }
        .mapa-buscar-row button {
            padding: 10px 18px; background: #3498db; color: #fff;
            border: none; border-radius: 10px; cursor: pointer; font-weight: 600;
        }
        .mapa-buscar-row button:hover { background: #2e86c1; }
        .mapa-iframe { width: 100%; height: 320px; border-radius: 12px; border: 1px solid #ddd; display: block; }
        .mapa-tip { font-size: 12px; color: #888; }
        .destacada-info { font-size: 12px; color: #888; margin-top: 6px; }
        .destacada-info.lleno { color: #e74c3c; font-weight: 600; }
    </style>
</head>
<body>
<section class="panel">
<h2>Editar Empresa</h2>

<?php if ($error):   ?><p style="color:red"><?=   htmlspecialchars($error)   ?></p><?php endif; ?>
<?php if ($success): ?><p style="color:green"><?= htmlspecialchars($success) ?></p><?php endif; ?>

<form method="post" enctype="multipart/form-data">

    <h3>Logo de la empresa</h3>
    <?php if (!empty($empresa['logo'])): ?>
    <div style="position:relative;display:inline-block">
        <img src="../assets/img/<?= htmlspecialchars($empresa['logo']) ?>"
             style="width:100px;height:100px;object-fit:cover;border-radius:6px;">
        <a href="javascript:void(0)"
           onclick="eliminarFoto(<?= $id ?>,'logo',this.parentElement)"
           style="position:absolute;top:5px;right:5px;background:red;color:white;
                  padding:4px 6px;border-radius:4px;text-decoration:none;font-size:12px;">X</a>
    </div>
    <?php endif; ?>
    <br><br>
    <input type="file" name="logo" accept="image/*">

    <h3>Fotos del carrusel</h3>
    <input type="file" name="fotos[]" multiple accept="image/*">
    <br><br>

    <h4>Fotos actuales <span style="font-weight:400;font-size:13px;color:#888;">(arrastra para reordenar)</span></h4>

    <?php
    $stmt = $conexion->prepare("SELECT * FROM empresa_galeria WHERE id_empresa=? ORDER BY orden ASC, id_foto ASC");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $fotos = $stmt->get_result();
    ?>

    <?php if ($fotos->num_rows == 0): ?>
        <p>No hay fotos aún</p>
    <?php else: ?>
        <div class="fotos-grid" id="fotos-sortable">
            <?php $pos = 1; while ($foto = $fotos->fetch_assoc()): ?>
            <div class="foto-item" data-id="<?= $foto['id_foto'] ?>">
                <span class="orden-badge"><?= $pos++ ?></span>
                <img src="../assets/img/empresascarrusel/<?= htmlspecialchars($foto['foto']) ?>" alt="Foto">
                <button type="button" class="btn-borrar"
                        onclick="eliminarFoto(<?= $foto['id_foto'] ?>,'galeria',this.parentElement)">X</button>
            </div>
            <?php endwhile; ?>
        </div>
        <p class="drag-hint">🖱 Arrastra las fotos para cambiar el orden del carrusel</p>
        <button type="button" class="btn-guardar-orden" id="btn-orden" onclick="guardarOrden()">
            💾 Guardar orden
        </button>
    <?php endif; ?>

    <hr>

    <label>Nombre</label>
    <input type="text" name="nombre" value="<?= htmlspecialchars($empresa['nombre']) ?>" required>

    <label>Teléfono</label>
    <input type="text" name="telefono" value="<?= htmlspecialchars($empresa['telefono'] ?? '') ?>">

    <label>Dirección</label>
    <input type="text" name="direccion" value="<?= htmlspecialchars($empresa['direccion'] ?? '') ?>">

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
    <textarea name="descripcion"><?= htmlspecialchars($empresa['descripcion'] ?? '') ?></textarea>

    <label>Horario</label>
    <input type="text" name="horario" value="<?= htmlspecialchars($empresa['horario'] ?? '') ?>">

    <label>Ubicación en Google Maps</label>
    <div class="mapa-wrap">
        <div class="mapa-buscar-row">
            <input type="text" id="mapa-query"
                   value="<?= htmlspecialchars($empresa['nombre'] . ' ' . $empresa['direccion']) ?>"
                   placeholder="Busca el negocio aquí...">
            <button type="button" onclick="buscarMapa()">Buscar</button>
        </div>
        <iframe id="mapa-iframe" class="mapa-iframe"
            src="https://maps.google.com/maps?q=<?= $mapaQuery ?>&output=embed"
            allowfullscreen loading="lazy"></iframe>
        <p class="mapa-tip">1. Busca el negocio arriba &nbsp;·&nbsp; 2. En Google Maps comparte la ubicación y pega el link abajo</p>
        <input type="text" name="ubicacion_link"
               value="<?= htmlspecialchars($empresa['ubicacion_link'] ?? '') ?>"
               placeholder="Pega la URL de Google Maps">
    </div>

    <label>Facebook</label>
    <input type="url" name="facebook"
           value="<?= htmlspecialchars($empresa['facebook'] ?? '') ?>"
           placeholder="https://facebook.com/tuempresa">

    <label>Enlace externo de la empresa</label>
    <input type="url" name="link_empresa"
           value="<?= htmlspecialchars($empresa['link_empresa'] ?? '') ?>"
           placeholder="https://tuempresa.com">

    <label>¿Empresa destacada? ⭐</label>
    <select name="destacada">
        <option value="0" <?= $empresa['destacada'] == 0 ? 'selected' : '' ?>>No</option>
        <option value="1" <?= $empresa['destacada'] == 1 ? 'selected' : '' ?>>Sí ⭐</option>
    </select>
    <p class="destacada-info <?= $total_destacadas >= 3 && $empresa['destacada'] == 0 ? 'lleno' : '' ?>">
        <?php if ($total_destacadas >= 3 && $empresa['destacada'] == 0): ?>
            ⚠️ Ya hay 3 empresas destacadas. Debes quitar una antes de destacar esta.
        <?php else: ?>
            <?= $total_destacadas ?>/3 empresas destacadas actualmente.
        <?php endif; ?>
    </p>

    <br><br>
    <button type="submit" class="btn">Actualizar empresa</button>
</form>

<br>
<a href="<?= ($rol === 'admin') ? 'admin.php' : 'editor.php' ?>" class="btn btn-danger">Volver al Panel</a>

</section>

<script>
const sortableEl = document.getElementById('fotos-sortable');
if (sortableEl) {
    Sortable.create(sortableEl, {
        animation: 150,
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        onEnd: function () {
            document.querySelectorAll('.foto-item').forEach((el, i) => {
                el.querySelector('.orden-badge').textContent = i + 1;
            });
            const btn = document.getElementById('btn-orden');
            btn.style.background = '#e67e22';
            btn.textContent = '💾 Guardar orden (hay cambios)';
        }
    });
}

function guardarOrden() {
    const items = document.querySelectorAll('.foto-item');
    const orden = Array.from(items).map(el => el.dataset.id);
    const btn   = document.getElementById('btn-orden');
    btn.textContent = 'Guardando...';
    btn.disabled    = true;
    fetch('reordenar_fotos.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ orden })
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            btn.textContent = '✅ Orden guardado';
            btn.classList.add('guardado');
            setTimeout(() => {
                btn.textContent = '💾 Guardar orden';
                btn.classList.remove('guardado');
                btn.style.background = '';
                btn.disabled = false;
            }, 2000);
        } else {
            alert('Error al guardar: ' + (data.error || 'desconocido'));
            btn.disabled = false;
        }
    })
    .catch(() => { alert('Error de conexión'); btn.disabled = false; });
}

function buscarMapa() {
    const q = document.getElementById('mapa-query').value.trim();
    if (!q) return;
    document.getElementById('mapa-iframe').src =
        'https://maps.google.com/maps?q=' + encodeURIComponent(q) + '&output=embed';
}
document.getElementById('mapa-query').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') { e.preventDefault(); buscarMapa(); }
});

function eliminarFoto(id, tipo, elemento) {
    if (!confirm('¿Eliminar esta imagen?')) return;
    fetch('eliminar_foto.php?id=' + id + '&tipo=' + tipo, {
        headers: { 'X-CSRF-TOKEN': '<?= htmlspecialchars($_SESSION['csrf_token']) ?>' }
    })
    .then(r => r.text())
    .then(data => {
        if (data.trim() === 'ok') elemento.remove();
        else alert('No se pudo eliminar: ' + data);
    });
}
</script>
</body>
</html>