<?php
include 'proteger.php';
include '../db.php';
include '../includes/security.php';
include '../includes/slug_helper.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!validarCSRF()) {
        $error = "Token de seguridad inválido. Por favor recarga la página.";
        logSeguridad('csrf_invalido', 'Intento de agregar empresa sin token CSRF válido');
    } else {
        $nombre = inputLimpio($_POST['nombre']);
        $telefono = inputLimpio($_POST['telefono'] ?? '');
        $direccion = inputLimpio($_POST['direccion'] ?? '');
        $id_categoria = intval($_POST['id_categoria']);
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
        $slug = generarSlug($nombre);

        $logo = null;

        if (!empty($_FILES['logo']['name'])) {
            $resultado = subirImagenSegura($_FILES['logo'], __DIR__ . '/../assets/img/', [
                'tamano_max' => 2 * 1024 * 1024,
                'redimensionar' => true,
                'ancho_max' => 800,
                'alto_max' => 600,
                'webp' => true
            ]);

            if ($resultado['success']) {
                $logo = $resultado['nombre'];
            } else {
                $error = "Error con el logo: " . $resultado['error'];
            }
        }

        if (empty($error)) {
            $stmt = $conexion->prepare(
                "INSERT INTO empresas (nombre,telefono,direccion,id_categoria,descripcion,horario,ubicacion_link,link_empresa,facebook,logo,slug)
         VALUES (?,?,?,?,?,?,?,?,?,?,?)"
            );
            $stmt->bind_param("sssisssssss", $nombre, $telefono, $direccion, $id_categoria, $descripcion, $horario, $ubicacion_link, $link_empresa, $facebook, $logo, $slug);

            if (!$stmt->execute()) {
                $error = "Ha ocurrido un error al guardar la empresa. Por favor intenta nuevamente.";
                error_log("SQL Error en agregar_empresa.php: " . $stmt->error);
            } else {
                $id_empresa = $stmt->insert_id;

                if (!empty($_FILES['fotos']['name'][0])) {
                    $total = min(count($_FILES['fotos']['name']), 5);
                    for ($i = 0; $i < $total; $i++) {
                        if (empty($_FILES['fotos']['name'][$i]))
                            continue;

                        $foto_file = [
                            'name' => $_FILES['fotos']['name'][$i],
                            'type' => $_FILES['fotos']['type'][$i],
                            'tmp_name' => $_FILES['fotos']['tmp_name'][$i],
                            'error' => $_FILES['fotos']['error'][$i],
                            'size' => $_FILES['fotos']['size'][$i]
                        ];

                        $res_f = subirImagenSegura($foto_file, __DIR__ . "/../assets/img/empresascarrusel/", [
                            'redimensionar' => true,
                            'ancho_max' => 1200,
                            'alto_max' => 900,
                            'webp' => true
                        ]);

                        if ($res_f['success']) {
                            $nombreFoto = $res_f['nombre'];
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
}

$categorias = $conexion->query("SELECT id_categoria,nombre FROM categorias ORDER BY orden ASC");
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Empresa</title>
    <link rel="icon" href="<?= APP_URL ?>/assets/img/image.png" type="image/png">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/login.css">
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
                                <option value="<?= $fila['id_categoria'] ?>"><?= htmlspecialchars($fila['nombre']) ?>
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
                        <input type="hidden" name="horario" id="horario_hidden" value="">
                        
                        <div class="horario-editor" style="background:#f9f9f9; padding: 15px; border-radius: 8px; border: 1px solid #ddd; margin-bottom: 20px;">
                            <p style="margin-top:0;font-size:0.9em;color:#666;">Selecciona los días y horas de apertura (formato 24h).</p>
                            <?php
                            $dias_semana = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
                            foreach ($dias_semana as $dia):
                            ?>
                            <div class="horario-dia-row" style="display:flex; align-items:center; gap: 10px; margin-bottom: 8px; flex-wrap:wrap;">
                                <label style="width: 100px; display:flex; align-items:center; gap:5px; margin:0; font-weight:normal;">
                                    <input type="checkbox" class="dia-cb" data-dia="<?= $dia ?>">
                                    <?= ucfirst($dia) ?>
                                </label>
                                <div class="hora-inputs" style="opacity:0.4; pointer-events:none; display:flex; gap: 3px; align-items:center;">
                                    <select class="hora-ap-h" style="padding: 4px; border:1px solid #ccc; border-radius:4px;">
                                        <?php for($i=1; $i<=12; $i++): ?>
                                            <option value="<?= $i ?>" <?= 9 == $i ? 'selected' : '' ?>><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?></option>
                                        <?php endfor; ?>
                                    </select>:
                                    <select class="hora-ap-m" style="padding: 4px; border:1px solid #ccc; border-radius:4px;">
                                        <?php foreach(['00','15','30','45'] as $mOp): ?>
                                            <option value="<?= $mOp ?>"><?= $mOp ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <select class="hora-ap-p" style="padding: 4px; border:1px solid #ccc; border-radius:4px;">
                                        <option value="am" selected>AM</option>
                                        <option value="pm">PM</option>
                                    </select>
                                    
                                    <span style="margin: 0 5px; color:#555;"> a </span>
                                    
                                    <select class="hora-ci-h" style="padding: 4px; border:1px solid #ccc; border-radius:4px;">
                                        <?php for($i=1; $i<=12; $i++): ?>
                                            <option value="<?= $i ?>" <?= 6 == $i ? 'selected' : '' ?>><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?></option>
                                        <?php endfor; ?>
                                    </select>:
                                    <select class="hora-ci-m" style="padding: 4px; border:1px solid #ccc; border-radius:4px;">
                                        <?php foreach(['00','15','30','45'] as $mOp): ?>
                                            <option value="<?= $mOp ?>"><?= $mOp ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <select class="hora-ci-p" style="padding: 4px; border:1px solid #ccc; border-radius:4px;">
                                        <option value="am">AM</option>
                                        <option value="pm" selected>PM</option>
                                    </select>
                                </div>
                            </div>
                            <?php endforeach; ?>

                            <hr style="border:0; border-top:1px solid #ddd; margin: 15px 0;">
                            <p style="margin-top:0;font-size:0.9em;color:#666; font-weight:bold;">Días no laborables o fechas especiales</p>
                            <div id="feriados-container"></div>
                            <button type="button" id="btn-add-feriado" class="btn btn-sm" style="background:#007BFF; color:#fff; padding: 5px 10px; font-size:13px; border:none; cursor:pointer;">+ Agregar fecha</button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Ubicación en Google Maps</label>
                        <div class="mapa-wrap">
                            <div class="mapa-buscar-row">
                                <input type="text" id="mapa-query" placeholder="Busca el negocio aquí...">
                                <button type="button" onclick="buscarMapa()">Buscar</button>
                            </div>
                            <iframe id="mapa-iframe" class="mapa-iframe"
                                src="https://maps.google.com/maps?q=Peru&output=embed" allowfullscreen
                                loading="lazy"></iframe>
                            <p class="mapa-tip">1. Busca el negocio arriba &nbsp;·&nbsp; 2. Entra a Google Maps,
                                comparte la ubicación y pega el link abajo</p>
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

        document.getElementById('mapa-query').addEventListener('keydown', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); buscarMapa(); }
        });

        document.getElementById('nombre').addEventListener('blur', function () {
            const val = this.value.trim();
            if (val && !document.getElementById('mapa-query').value) {
                document.getElementById('mapa-query').value = val;
                buscarMapa();
            }
        });

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
</body>

</html>