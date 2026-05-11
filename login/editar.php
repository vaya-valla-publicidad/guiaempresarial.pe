<?php require_once __DIR__ . '/proteger.php'; ?>
<?php
include '../db.php';
include '../includes/slug_helper.php';
include '../includes/security.php';

$rol = $_SESSION['rol'];

if (!isset($_GET['id'])) {
    header("Location: " . (($_SESSION['rol'] === 'admin') ? 'admin.php' : 'editor.php'));
    exit;
}

$id = intval($_GET['id']);
$error = "";
$success = isset($_GET['ok']) ? "Empresa actualizada correctamente ✅" : "";

function validarImagen($tmpPath, $nombreOriginal): bool
{
    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $ext = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
    if (!in_array($ext, $extensionesPermitidas))
        return false;

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $tmpPath);
    finfo_close($finfo);

    $mimesPermitidos = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    return in_array($mime, $mimesPermitidos);
}

$stmt = $conexion->prepare("SELECT * FROM empresas WHERE id_empresa=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
if ($resultado->num_rows === 0) {
    header("Location: " . (($rol === 'admin') ? 'admin.php' : 'editor.php'));
    exit;
}
$empresa = $resultado->fetch_assoc();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!validarCSRF()) {
        $error = "Solicitud inválida. Intenta nuevamente.";
    } else {
        $nombre = inputLimpio($_POST['nombre'] ?? '');
        $telefono = inputLimpio($_POST['telefono'] ?? '');
        $direccion = inputLimpio($_POST['direccion'] ?? '');
        $id_categoria = intval($_POST['id_categoria'] ?? 0);
        $descripcion = inputLimpio($_POST['descripcion'] ?? '') ?: null;
        $horario = inputLimpio($_POST['horario'] ?? '') ?: null;
        $ubicacion_link = inputLimpio($_POST['ubicacion_link'] ?? '') ?: null;
        $link_empresa = inputLimpio($_POST['link_empresa'] ?? '') ?: null;
        $facebook = inputLimpio($_POST['facebook'] ?? '') ?: null;
        $logo = $empresa['logo'];
        $destacada_new = intval($_POST['destacada'] ?? 0);
        $slug = generarSlug($nombre);


        if ($destacada_new === 1 && $empresa['destacada'] == 0) {
            $totalDestResult = $conexion->query("SELECT COUNT(*) as total FROM empresas WHERE destacada = 1");
            $total_dest = $totalDestResult ? ($totalDestResult->fetch_assoc()['total'] ?? 0) : 0;
            if ($total_dest >= 3) {
                $error = "Ya hay 3 empresas destacadas. Quita una antes de destacar esta.";
                $destacada_new = 0;
            }
        }

        if (!empty($_FILES['logo']['name'])) {
            $resultado = subirImagenSegura($_FILES['logo'], __DIR__ . '/../assets/img/', [
                'tamano_max' => 2 * 1024 * 1024,
                'redimensionar' => true,
                'ancho_max' => 800,
                'alto_max' => 600,
                'webp' => true
            ]);

            if ($resultado['success']) {
                if (!empty($empresa['logo']) && file_exists(__DIR__ . "/../assets/img/" . $empresa['logo'])) {
                    unlink(__DIR__ . "/../assets/img/" . $empresa['logo']);
                }
                $logo = $resultado['nombre'];
            } else {
                $error = "Error con el logo: " . $resultado['error'];
            }
        }

        if (empty($error)) {
            $stmt = $conexion->prepare(
                "UPDATE empresas SET nombre=?, telefono=?, direccion=?, id_categoria=?,
                 descripcion=?, horario=?, ubicacion_link=?, link_empresa=?, facebook=?, logo=?, destacada=?, slug=?
                 WHERE id_empresa=?"
            );

            if (!$stmt) {
                error_log("Error preparando actualización de empresa ID {$id}: " . $conexion->error);
                $error = "No se pudo actualizar la empresa. Intenta nuevamente.";
            } else {
                $stmt->bind_param(
                    "sssissssssisi",
                    $nombre,
                    $telefono,
                    $direccion,
                    $id_categoria,
                    $descripcion,
                    $horario,
                    $ubicacion_link,
                    $link_empresa,
                    $facebook,
                    $logo,
                    $destacada_new,
                    $slug,
                    $id
                );

                if ($stmt->execute()) {
                    $stmt->close();

                    if (!empty($_FILES['fotos']['name'][0])) {
                        $carpeta = __DIR__ . "/../assets/img/empresascarrusel/";
                        $ordenActualStmt = $conexion->prepare("SELECT MAX(orden) as max FROM empresa_galeria WHERE id_empresa=?");
                        $orden_actual = 0;

                        if ($ordenActualStmt) {
                            $ordenActualStmt->bind_param("i", $id);
                            $ordenActualStmt->execute();
                            $ordenResult = $ordenActualStmt->get_result();
                            $orden_actual = $ordenResult ? (int) ($ordenResult->fetch_assoc()['max'] ?? 0) : 0;
                            $ordenActualStmt->close();
                        }

                        foreach ($_FILES['fotos']['tmp_name'] as $key => $tmp) {
                            if (empty($_FILES['fotos']['name'][$key]))
                                continue;

                            $foto_file = [
                                'name' => $_FILES['fotos']['name'][$key],
                                'type' => $_FILES['fotos']['type'][$key],
                                'tmp_name' => $_FILES['fotos']['tmp_name'][$key],
                                'error' => $_FILES['fotos']['error'][$key],
                                'size' => $_FILES['fotos']['size'][$key]
                            ];

                            $res_f = subirImagenSegura($foto_file, $carpeta, [
                                'redimensionar' => true,
                                'ancho_max' => 1200,
                                'alto_max' => 900,
                                'webp' => true
                            ]);

                            if ($res_f['success']) {
                                $orden_actual++;
                                $nombreFoto = $res_f['nombre'];
                                $stmtFoto = $conexion->prepare("INSERT INTO empresa_galeria (id_empresa, foto, orden) VALUES (?, ?, ?)");
                                if ($stmtFoto) {
                                    $stmtFoto->bind_param("isi", $id, $nombreFoto, $orden_actual);
                                    $stmtFoto->execute();
                                    $stmtFoto->close();
                                } else {
                                    error_log("Error preparando inserción de foto para empresa ID {$id}: " . $conexion->error);
                                }
                            }
                        }
                    }

                    header("Location: editar.php?id=" . $id . "&ok=1");
                    exit;

                } else {
                    error_log("Error ejecutando actualización de empresa ID {$id}: " . $stmt->error);
                    $error = "No se pudo actualizar la empresa. Intenta nuevamente.";
                    $stmt->close();
                }
            }
        }
    }
}

$categorias = $conexion->query("SELECT id_categoria, nombre FROM categorias ORDER BY orden ASC");
$mapaQuery = urlencode($empresa['nombre'] . ' ' . $empresa['direccion']);
$total_destacadas = $conexion->query("SELECT COUNT(*) as total FROM empresas WHERE destacada = 1")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Empresa</title>
    <link rel="icon" href="<?= APP_URL ?>/assets/img/image.png" type="image/png">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/login.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.2/Sortable.min.js"></script>
</head>

<body>
    <section class="panel">
        <h2>Editar Empresa</h2>

        <?php if ($error): ?>
            <p style="color:red"><?= htmlspecialchars($error) ?></p><?php endif; ?>
        <?php if ($success): ?>
            <p style="color:green"><?= htmlspecialchars($success) ?></p><?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generarTokenCSRF()) ?>">

            <h3>Logo de la empresa</h3>
            <?php if (!empty($empresa['logo'])): ?>
                <div style="position:relative;display:inline-block;margin-bottom:15px;">
                    <img src="<?= APP_URL ?>/assets/img/<?= htmlspecialchars($empresa['logo']) ?>"
                        style="width:120px;height:120px;object-fit:cover;border-radius:12px;border:3px solid #fff;box-shadow:0 5px 15px rgba(0,0,0,0.1);">
                    <a href="javascript:void(0)" onclick="eliminarFoto(<?= $id ?>,'logo',this.parentElement)"
                        class="btn-borrar" style="position:absolute;top:-10px;right:-10px;">✕</a>
                </div>
            <?php endif; ?>
            <br>
            <input type="file" name="logo" accept="image/*">

            <h3 style="margin-top:30px;">Fotos del carrusel</h3>
            <input type="file" name="fotos[]" multiple accept="image/*">
            <br><br>

            <h4>Fotos actuales <span style="font-weight:400;font-size:13px;color:#888;">(arrastra para reordenar)</span>
            </h4>

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
                    <?php $pos = 1;
                    while ($foto = $fotos->fetch_assoc()): ?>
                        <div class="foto-item" data-id="<?= $foto['id_foto'] ?>">
                            <span class="orden-badge"><?= $pos++ ?></span>
                            <img src="<?= APP_URL ?>/assets/img/empresascarrusel/<?= htmlspecialchars($foto['foto']) ?>"
                                alt="Foto">
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
                    <option value="<?= $fila['id_categoria'] ?>" <?= $fila['id_categoria'] == $empresa['id_categoria'] ? 'selected' : '' ?>>
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
                    src="https://maps.google.com/maps?q=<?= $mapaQuery ?>&output=embed" allowfullscreen
                    loading="lazy"></iframe>

                <p class="mapa-tip">1. Busca el negocio arriba &nbsp;·&nbsp; 2. Entra a Google Maps, comparte la
                    ubicación y pega el link abajo</p>

                <input type="text" name="ubicacion_link"
                    value="<?= htmlspecialchars($empresa['ubicacion_link'] ?? '') ?>"
                    placeholder="Pega la URL de Google Maps">
            </div>

            <label>Facebook</label>
            <input type="url" name="facebook" value="<?= htmlspecialchars($empresa['facebook'] ?? '') ?>"
                placeholder="https://facebook.com/tuempresa">

            <label>Enlace externo de la empresa</label>
            <input type="url" name="link_empresa" value="<?= htmlspecialchars($empresa['link_empresa'] ?? '') ?>"
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
                    const btn = document.getElementById('btn-orden');
                    if (btn) btn.classList.add('visible');

                    document.querySelectorAll('.foto-item').forEach((el, i) => {
                        const badge = el.querySelector('.orden-badge');
                        if (badge) badge.textContent = i + 1;
                    });

                    if (btn) {
                        btn.style.background = '#e67e22';
                        btn.textContent = '💾 Guardar orden (hay cambios)';
                    }
                }
            });
        }

        function guardarOrden() {
            const items = document.querySelectorAll('.foto-item');
            const orden = Array.from(items).map(el => el.dataset.id);
            const btn = document.getElementById('btn-orden');
            btn.textContent = 'Guardando...';
            btn.disabled = true;
            fetch('reordenar_fotos.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '<?= htmlspecialchars($_SESSION["csrf_token"]) ?>'
                },
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
                        showToast('Error al guardar: ' + (data.error || 'desconocido'), 'error');
                        btn.disabled = false;
                    }
                })
                .catch(() => { showToast('Error de conexión', 'error'); btn.disabled = false; });
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
            customConfirm('¿Eliminar esta imagen?', () => {
                fetch('eliminar_foto.php?id=' + id + '&tipo=' + tipo, {
                    headers: { 'X-CSRF-TOKEN': '<?= htmlspecialchars($_SESSION['csrf_token']) ?>' }
                })
                    .then(r => r.text())
                    .then(data => {
                        if (data.trim() === 'ok') elemento.remove();
                        else showToast('No se pudo eliminar: ' + data, 'error');
                    });
            });
        }

    </script>
    <script src="<?= APP_URL ?>/assets/js/toast.js"></script>
</body>

</html>