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
        if ($ubicacion_link && !preg_match('/^https?:\/\//i', $ubicacion_link)) {
            $error = "El enlace de ubicación debe comenzar con http:// o https://";
        }
        $link_empresa = inputLimpio($_POST['link_empresa'] ?? '') ?: null;
        if ($link_empresa && empty($error) && !preg_match('/^https?:\/\//i', $link_empresa)) {
            $error = "El enlace de empresa debe comenzar con http:// o https://";
        }
        $facebook = inputLimpio($_POST['facebook'] ?? '') ?: null;
        if ($facebook && empty($error) && !preg_match('/^https?:\/\//i', $facebook)) {
            $error = "El enlace de Facebook debe comenzar con http:// o https://";
        }
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
                $thumb_old = __DIR__ . '/../assets/img/thumb_' . $empresa['logo'];
                if (!empty($empresa['logo']) && file_exists($thumb_old)) {
                    unlink($thumb_old);
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
            <input type="hidden" name="horario" id="horario_hidden" value="<?= htmlspecialchars($empresa['horario'] ?? '') ?>">
            
            <div class="horario-editor" style="background:#f9f9f9; padding: 15px; border-radius: 8px; border: 1px solid #ddd; margin-bottom: 20px;">
                <p style="margin-top:0;font-size:0.9em;color:#666;">Selecciona los días y horas de apertura (formato 24h).</p>
                <?php
                $dias_semana = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
                $horario_str_full = $empresa['horario'] ?? '';
                $horario_str = $horario_str_full;
                $especial_str = '';
                if (strpos($horario_str_full, '|') !== false) {
                    $parts_p = explode('|', $horario_str_full);
                    $horario_str = $parts_p[0];
                    $especial_str = $parts_p[1] ?? '';
                }

                $horario_parsed = [];
                if (!empty($horario_str)) {
                    $parts = explode(',', strtolower($horario_str));
                    foreach ($parts as $p) {
                        $p = trim($p);
                        if(strpos($p, ':') !== false) {
                            list($dia, $horas) = explode(':', $p, 2);
                            $horario_parsed[trim($dia)] = trim($horas);
                        }
                    }
                }
                foreach ($dias_semana as $dia):
                    $is_active = isset($horario_parsed[$dia]);
                    $apertura = '09:00';
                    $cierre = '18:00';
                    if ($is_active) {
                        $horas_split = explode('-', $horario_parsed[$dia]);
                        if(count($horas_split) == 2) {
                            $apertura = trim($horas_split[0]);
                            $cierre = trim($horas_split[1]);
                        }
                    }

                    // Convert to 12h for dropdowns
                    $get12h = function($time24) {
                        if (!$time24) return [9, '00', 'am'];
                        $pts = explode(':', $time24);
                        if (count($pts) < 2) return [9, '00', 'am'];
                        $h = (int)$pts[0];
                        $m = $pts[1];
                        $validMins = ['00', '15', '30', '45'];
                        if (!in_array($m, $validMins)) $m = '00';
                        $period = $h >= 12 ? 'pm' : 'am';
                        $h12 = $h % 12;
                        if ($h12 == 0) $h12 = 12;
                        return [$h12, $m, $period];
                    };
                    
                    list($ap_h, $ap_m, $ap_p) = $get12h($apertura);
                    list($ci_h, $ci_m, $ci_p) = $get12h($cierre);
                ?>
                <div class="horario-dia-row" style="display:flex; align-items:center; gap: 10px; margin-bottom: 8px; flex-wrap:wrap;">
                    <label style="width: 100px; display:flex; align-items:center; gap:5px; margin:0; font-weight:normal;">
                        <input type="checkbox" class="dia-cb" data-dia="<?= $dia ?>" <?= $is_active ? 'checked' : '' ?>>
                        <?= ucfirst($dia) ?>
                    </label>
                    <div class="hora-inputs" style="<?= $is_active ? '' : 'opacity:0.4; pointer-events:none;' ?> display:flex; gap: 3px; align-items:center;">
                        <select class="hora-ap-h" style="padding: 4px; border:1px solid #ccc; border-radius:4px;">
                            <?php for($i=1; $i<=12; $i++): ?>
                                <option value="<?= $i ?>" <?= $ap_h == $i ? 'selected' : '' ?>><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?></option>
                            <?php endfor; ?>
                        </select>:
                        <select class="hora-ap-m" style="padding: 4px; border:1px solid #ccc; border-radius:4px;">
                            <?php foreach(['00','15','30','45'] as $mOp): ?>
                                <option value="<?= $mOp ?>" <?= $ap_m == $mOp ? 'selected' : '' ?>><?= $mOp ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select class="hora-ap-p" style="padding: 4px; border:1px solid #ccc; border-radius:4px;">
                            <option value="am" <?= $ap_p == 'am' ? 'selected' : '' ?>>AM</option>
                            <option value="pm" <?= $ap_p == 'pm' ? 'selected' : '' ?>>PM</option>
                        </select>
                        
                        <span style="margin: 0 5px; color:#555;"> a </span>
                        
                        <select class="hora-ci-h" style="padding: 4px; border:1px solid #ccc; border-radius:4px;">
                            <?php for($i=1; $i<=12; $i++): ?>
                                <option value="<?= $i ?>" <?= $ci_h == $i ? 'selected' : '' ?>><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?></option>
                            <?php endfor; ?>
                        </select>:
                        <select class="hora-ci-m" style="padding: 4px; border:1px solid #ccc; border-radius:4px;">
                            <?php foreach(['00','15','30','45'] as $mOp): ?>
                                <option value="<?= $mOp ?>" <?= $ci_m == $mOp ? 'selected' : '' ?>><?= $mOp ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select class="hora-ci-p" style="padding: 4px; border:1px solid #ccc; border-radius:4px;">
                            <option value="am" <?= $ci_p == 'am' ? 'selected' : '' ?>>AM</option>
                            <option value="pm" <?= $ci_p == 'pm' ? 'selected' : '' ?>>PM</option>
                        </select>
                    </div>
                </div>
                <?php endforeach; ?>

                <hr style="border:0; border-top:1px solid #ddd; margin: 15px 0;">
                <p style="margin-top:0;font-size:0.9em;color:#666; font-weight:bold;">Días no laborables o fechas especiales</p>
                <div id="feriados-container">
                    <?php
                    if (!empty($especial_str)) {
                        $feriados = explode(',', $especial_str);
                        foreach ($feriados as $fer) {
                            $fer = trim($fer);
                            if (strpos($fer, 'feriado:') === 0) {
                                $fer_parts = explode(':', $fer);
                                if (count($fer_parts) >= 2) {
                                    $f_date = $fer_parts[1];
                                    $f_motivo = count($fer_parts) >= 3 ? implode(':', array_slice($fer_parts, 2)) : '';
                                    echo '<div class="feriado-row" style="display:flex; gap:10px; margin-bottom:8px;">
                                            <input type="date" class="feriado-date" value="'.htmlspecialchars($f_date).'" style="width:150px; padding:5px;">
                                            <input type="text" class="feriado-motivo" value="'.htmlspecialchars($f_motivo).'" placeholder="Motivo (opcional)" style="flex:1; padding:5px;">
                                            <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove()" style="padding: 5px 10px;">X</button>
                                          </div>';
                                }
                            }
                        }
                    }
                    ?>
                </div>
                <button type="button" id="btn-add-feriado" class="btn btn-sm" style="background:#007BFF; color:#fff; padding: 5px 10px; font-size:13px; border:none; cursor:pointer;">+ Agregar fecha</button>
            </div>

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

        document.querySelectorAll('.dia-cb').forEach(cb => {
            cb.addEventListener('change', function() {
                const inputs = this.closest('.horario-dia-row').querySelector('.hora-inputs');
                if (this.checked) {
                    inputs.style.opacity = '1';
                    inputs.style.pointerEvents = 'auto';
                } else {
                    inputs.style.opacity = '0.4';
                    inputs.style.pointerEvents = 'none';
                }
            });
        });

        document.getElementById('btn-add-feriado').addEventListener('click', function() {
            const container = document.getElementById('feriados-container');
            const div = document.createElement('div');
            div.className = 'feriado-row';
            div.style.cssText = 'display:flex; gap:10px; margin-bottom:8px;';
            div.innerHTML = `
                <input type="date" class="feriado-date" style="width:150px; padding:5px;">
                <input type="text" class="feriado-motivo" placeholder="Motivo (opcional)" style="flex:1; padding:5px;">
                <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove()" style="padding: 5px 10px;">X</button>
            `;
            container.appendChild(div);
        });

        const formGeneral = document.querySelector('form');
        if (formGeneral) {
            formGeneral.addEventListener('submit', function() {
                let parts = [];
                
                const get24h = (h12, m, p) => {
                    let h = parseInt(h12, 10);
                    if (p === 'pm' && h < 12) h += 12;
                    if (p === 'am' && h === 12) h = 0;
                    return h.toString().padStart(2, '0') + ':' + m;
                };

                document.querySelectorAll('.horario-dia-row').forEach(row => {
                    const cb = row.querySelector('.dia-cb');
                    if (cb && cb.checked) {
                        const dia = cb.getAttribute('data-dia');
                        
                        const ap_h = row.querySelector('.hora-ap-h').value;
                        const ap_m = row.querySelector('.hora-ap-m').value;
                        const ap_p = row.querySelector('.hora-ap-p').value;
                        
                        const ci_h = row.querySelector('.hora-ci-h').value;
                        const ci_m = row.querySelector('.hora-ci-m').value;
                        const ci_p = row.querySelector('.hora-ci-p').value;
                        
                        const ap = get24h(ap_h, ap_m, ap_p);
                        const ci = get24h(ci_h, ci_m, ci_p);
                        
                        parts.push(`${dia}:${ap}-${ci}`);
                    }
                });
                
                let stringFinal = parts.join(',');

                // Feriados
                let feriados = [];
                document.querySelectorAll('.feriado-row').forEach(row => {
                    const dateVal = row.querySelector('.feriado-date').value;
                    const motivoVal = row.querySelector('.feriado-motivo').value.trim();
                    if (dateVal) {
                        let feriadoStr = `feriado:${dateVal}`;
                        if (motivoVal) {
                            // remove : and , and | from motivo to prevent parsing issues
                            let safeMotivo = motivoVal.replace(/[:|,]/g, ' ');
                            feriadoStr += `:${safeMotivo}`;
                        }
                        feriados.push(feriadoStr);
                    }
                });

                if (feriados.length > 0) {
                    stringFinal += '|' + feriados.join(',');
                }

                const hiddenH = document.getElementById('horario_hidden');
                if (hiddenH) hiddenH.value = stringFinal;
            });
        }


    </script>
    <script src="<?= APP_URL ?>/assets/js/toast.js"></script>
</body>

</html>