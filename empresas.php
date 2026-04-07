<?php
session_start();
include 'db.php';
include 'includes/security.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resena_empresa'])) {
    if (!validarCSRF()) {
        logSeguridad('csrf_invalido', 'Intento de enviar reseña sin token válido');
        header("Location: empresas.php?empresa=" . intval($_POST['id_empresa'] ?? 0) . "&resena=error#resenas");
        exit;
    }

    if (!verificarRateLimit('enviar_resena', 3, 60)) {
        header("Location: empresas.php?empresa=" . intval($_POST['id_empresa'] ?? 0) . "&resena=error#resenas");
        exit;
    }

    $id_emp = intval($_POST['id_empresa']);
    $estrellas = intval($_POST['estrellas']);
    $comentario = inputLimpio($_POST['comentario']);
    $nombre = inputLimpio($_POST['nombre_autor'] ?? '');

    $palabras_prohibidas = [
        'idiota',
        'imbecil',
        'mierda',
        'puta',
        'puto',
        'pendejo',
        'estupido',
        'basura',
        'asco',
        'maldito',
        'inutil',
        'animal',
        'bestia',
        'burro',
        'bruto',
        'tarado',
        'retrasado',
        'subnormal',
        'mongolo',
        'hdp',
        'hijo de puta',
        'malparido',
        'desgraciado',
        'miserable',
        'imbécil',
        'estúpido',
        'inútil',
        'maldición',
        'cabrón',
        'cabron',
        'hijoputa',
        'gonorrea',
        'marica',
        'maricón',
        'maricon',
        'estafa',
        'estafador',
        'estafadora',
        'ladron',
        'ladrona',
        'ladrón',
        'roba',
        'roban',
        'robaron',
        'mentira',
        'mentiroso',
        'mentirosa',
        'falso',
        'falsa',
        'fraude',
        'fraudulento',
        'engaño',
        'engañan',
        'engañaron',
        'timo',
        'timador',
        'corrupto',
        'corrupta',
        'corruptos',
        'ilegal',
        'ilegales',
        'clandestino',
        'peligroso',
        'peligrosa',
        'trampa',
        'tramposo',
        'tramposa',
        'chanta',
        'chantaje',
        'amenaza',
        'amenazaron',
        'extorsion',
        'extorsión',
        'extorsionan',
        'sexo',
        'porno',
        'prostituta',
        'prostituto',
        'prepago',
        'escort',
        'matar',
        'muerte',
        'asesino',
        'asesina',
        'golpear',
        'golpean',
        'violencia',
        'violento',
        'violenta',
        'pegar',
        'pegaron',
        'amenazar',
        've a',
        'mejor vayan a',
        'vayan mejor',
        'no vayan',
        'cierren',
        'cierren este',
    ];

    $texto_check = strtolower($comentario . ' ' . $nombre);
    $tiene_mala_palabra = false;
    foreach ($palabras_prohibidas as $palabra) {
        if (strpos($texto_check, $palabra) !== false) {
            $tiene_mala_palabra = true;
            break;
        }
    }

    $id_u_pub = isset($_SESSION['usuario_publico_id']) ? intval($_SESSION['usuario_publico_id']) : 0;
    if ($id_u_pub && $comentario && $estrellas >= 1 && $estrellas <= 5 && !$tiene_mala_palabra) {
        $stmt = $conexion->prepare("INSERT INTO resenas (id_empresa, nombre_autor, estrellas, comentario, id_usuario_publico) VALUES (?, ?, ?, ?, ?)");
        $nombre_sesion = $_SESSION['usuario_publico_nombre'];
        $stmt->bind_param("isiss", $id_emp, $nombre_sesion, $estrellas, $comentario, $id_u_pub);

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: empresas.php?empresa=$id_emp&resena=ok#resenas");
            exit;
        } else {
            $stmt->close();
            logSeguridad('error_resena', 'Error al guardar reseña: ' . $conexion->error, 'error');
            header("Location: empresas.php?empresa=$id_emp&resena=error#resenas");
            exit;
        }
    } elseif ($tiene_mala_palabra) {
        header("Location: empresas.php?empresa=$id_emp&resena=mala#resenas");
        exit;
    } else {
        header("Location: empresas.php?empresa=$id_emp&resena=error#resenas");
        exit;
    }
}
?>
<?php
$seo_title = "Empresas - Guía Empresarial";
$seo_description = "Explora negocios locales y descubre nuevas oportunidades en tu región.";

$seo_id_empresa = $_GET['empresa'] ?? null;
$seo_id_categoria = $_GET['id_categoria'] ?? null;

if ($seo_id_empresa) {
    $stmt_seo = $conexion->prepare("SELECT nombre, descripcion, logo FROM empresas WHERE id_empresa = ?");
    $id_emp_int = intval($seo_id_empresa);
    $stmt_seo->bind_param("i", $id_emp_int);
    $stmt_seo->execute();
    $res_seo = $stmt_seo->get_result();
    if ($res_seo && $res_seo->num_rows === 1) {
        $emp_seo = $res_seo->fetch_assoc();
        $seo_title = $emp_seo['nombre'] . " - Guía Empresarial";
        if (!empty($emp_seo['descripcion'])) {
            $seo_description = mb_strimwidth($emp_seo['descripcion'], 0, 150, "...");
        }
        if (!empty($emp_seo['logo'])) {
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $seo_image = $protocol . "://" . $domain . "/guiaempresarial.pe/assets/img/" . $emp_seo['logo'];
        }
    }
} elseif ($seo_id_categoria) {
    $stmt_seo_c = $conexion->prepare("SELECT nombre FROM categorias WHERE id_categoria = ?");
    $id_cat_int = intval($seo_id_categoria);
    $stmt_seo_c->bind_param("i", $id_cat_int);
    $stmt_seo_c->execute();
    $res_seo_c = $stmt_seo_c->get_result();
    if ($res_seo_c && $res_seo_c->num_rows === 1) {
        $cat_seo = $res_seo_c->fetch_assoc();
        $seo_title = "Empresas en " . $cat_seo['nombre'] . " - Guía Empresarial";
    }
}
include 'includes/header.php';
?>

<section class="empresas-page-section">
    <div class="container">

        <div class="section-header">
            <h1>Empresas</h1>
            <p>Explora negocios locales y descubre nuevas oportunidades</p>
        </div>

        <?php
        $id_categoria = $_GET['id_categoria'] ?? null;
        $id_empresa = $_GET['empresa'] ?? null;
        $buscar = $_GET['buscar'] ?? null;

        $pagina_actual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
        $empresas_por_pagina = 20;
        $offset = ($pagina_actual - 1) * $empresas_por_pagina;

        $sql_select = "SELECT e.*, c.nombre AS categoria FROM empresas e JOIN categorias c ON e.id_categoria = c.id_categoria";
        $sql_count = "SELECT COUNT(*) as total FROM empresas e JOIN categorias c ON e.id_categoria = c.id_categoria";

        $where = [];
        $params = [];
        $types = "";

        if ($id_empresa) {
            $where[] = "e.id_empresa = ?";
            $params[] = intval($id_empresa);
            $types .= "i";
        } elseif ($id_categoria) {
            $where[] = "e.id_categoria = ?";
            $params[] = intval($id_categoria);
            $types .= "i";
        } elseif ($buscar) {
            $texto = limpiarParaLike($buscar);
            $where[] = "(e.nombre LIKE ? OR e.descripcion LIKE ? OR c.nombre LIKE ?)";
            $busqueda = "%$texto%";
            $params[] = $busqueda;
            $params[] = $busqueda;
            $params[] = $busqueda;
            $types .= "sss";
        }

        $clausula_where = !empty($where) ? " WHERE " . implode(" AND ", $where) : "";

        $total_paginas = 1;
        if (!$id_empresa) {
            $sql_c = $sql_count . $clausula_where;
            if (!empty($where)) {
                $stmt_c = $conexion->prepare($sql_c);
                if ($types && $stmt_c) {
                    $stmt_c->bind_param($types, ...$params);
                    $stmt_c->execute();
                    $res_c = $stmt_c->get_result();
                    $total_filas = $res_c->fetch_assoc()['total'] ?? 0;
                } else {
                    $total_filas = $conexion->query($sql_c)->fetch_assoc()['total'] ?? 0;
                }
            } else {
                $total_filas = $conexion->query($sql_c)->fetch_assoc()['total'] ?? 0;
            }
            $total_paginas = ceil($total_filas / $empresas_por_pagina);
        }

        $sql = $sql_select . $clausula_where;
        if (!$id_empresa) {
            $sql .= " LIMIT $empresas_por_pagina OFFSET $offset";
        }

        if (!empty($where)) {
            $stmt = $conexion->prepare($sql);
            if ($types && $stmt) {
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $resultado = $stmt->get_result();
            } else {
                $resultado = $conexion->query($sql);
            }
        } else {
            $resultado = $conexion->query($sql);
        }

        if ($id_empresa && (!$resultado || $resultado->num_rows === 0)) {
            echo "<script>window.location.href='404.php';</script>";
            exit;
        }

        if ($id_empresa && $resultado && $resultado->num_rows === 1):
            $fila = $resultado->fetch_assoc();
            $logo = !empty($fila['logo']) ? htmlspecialchars($fila['logo']) : null;
            $telefono = $fila['telefono'] ?? null;
            $numero = $telefono ? preg_replace('/[^0-9]/', '', $telefono) : null;

            $stmt_fotos = $conexion->prepare("SELECT foto FROM empresa_galeria WHERE id_empresa = ? ORDER BY orden ASC, id_foto ASC");
            $id_empresa_int = intval($id_empresa);
            $stmt_fotos->bind_param("i", $id_empresa_int);
            $stmt_fotos->execute();
            $fotos_q = $stmt_fotos->get_result();
            $fotos_arr = [];
            if ($fotos_q && $fotos_q->num_rows > 0)
                while ($f = $fotos_q->fetch_assoc())
                    $fotos_arr[] = $f['foto'];

            $stmt_vistas = $conexion->prepare("UPDATE empresas SET vistas = vistas + 1 WHERE id_empresa = ?");
            $stmt_vistas->bind_param("i", $id_empresa_int);
            $stmt_vistas->execute();
            ?>

            <a href="empresas.php" class="btn-volver">← Volver a empresas</a>

            <div class="perfil-wrapper">
                <div class="perfil-hero">
                    <div class="perfil-banner"></div>
                    <div class="perfil-hero-body">
                        <?php if ($logo): ?>
                            <img class="perfil-logo" src="assets/img/<?= $logo ?>"
                                alt="<?= htmlspecialchars($fila['nombre']) ?>">
                        <?php else: ?>
                            <div class="perfil-logo logo-placeholder" style="width:90px;height:90px;font-size:32px;">
                                <?= mb_strtoupper(mb_substr($fila['nombre'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                        <div class="perfil-hero-info">
                            <div class="perfil-hero-top">
                                <div>
                                    <h2 class="perfil-nombre">
                                        <?= htmlspecialchars($fila['nombre']) ?>
                                        <?php if ($fila['destacada']): ?>
                                            <span class="badge-destacada">⭐ Destacada</span>
                                        <?php endif; ?>
                                    </h2>
                                    <span class="empresa-card-badge"><?= htmlspecialchars($fila['categoria']) ?></span>
                                </div>
                                <div class="perfil-acciones">
                                    <?php if (!empty($fila['link_empresa'])): ?>
                                        <a href="<?= htmlspecialchars($fila['link_empresa']) ?>" target="_blank"
                                            class="btn-accion" style="background-color: #3b82f6; color: white;">
                                            🌐 Sitio Web
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($numero): ?>
                                        <a href="https://wa.me/<?= $numero ?>" target="_blank"
                                            class="btn-accion btn-accion-whatsapp">WhatsApp</a>
                                    <?php endif; ?>
                                    <?php if (!empty($fila['ubicacion_link'])): ?>
                                        <a href="<?= htmlspecialchars($fila['ubicacion_link']) ?>" target="_blank"
                                            class="btn-accion btn-accion-maps">📍 Ver en Maps</a>
                                    <?php endif; ?>
                                    <?php if (!empty($fila['facebook'])): ?>
                                        <a href="<?= htmlspecialchars($fila['facebook']) ?>" target="_blank"
                                            class="btn-accion btn-accion-facebook">
                                            📘 Facebook
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if (!empty($fila['descripcion'])): ?>
                                <p class="perfil-slogan">✨
                                    <?= htmlspecialchars(mb_strimwidth($fila['descripcion'], 0, 100, '…')) ?>
                                </p>
                            <?php endif; ?>
                            <p style="font-size:12px;color:var(--muted);margin-top:8px;">👁
                                <?= number_format($fila['vistas']) ?> vistas
                            </p>
                        </div>
                    </div>
                </div>

                <div class="perfil-body">
                    <div class="perfil-info-card">
                        <p class="perfil-section-label">Información</p>
                        <div class="perfil-datos">
                            <?php if (!empty($fila['direccion'])): ?>
                                <div class="perfil-dato-item">
                                    <span class="perfil-dato-icon">📍</span>
                                    <div>
                                        <span class="perfil-dato-label">Dirección</span>
                                        <span class="perfil-dato-valor"><?= htmlspecialchars($fila['direccion']) ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($fila['horario'])): ?>
                                <div class="perfil-dato-item">
                                    <span class="perfil-dato-icon">🕒</span>
                                    <div>
                                        <span class="perfil-dato-label">Horario</span>
                                        <span class="perfil-dato-valor"><?= htmlspecialchars($fila['horario']) ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if ($numero): ?>
                                <div class="perfil-dato-item">
                                    <span class="perfil-dato-icon">📞</span>
                                    <div>
                                        <span class="perfil-dato-label">Teléfono</span>
                                        <span class="perfil-dato-valor"><?= $numero ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($fila['email'])): ?>
                                <div class="perfil-dato-item">
                                    <span class="perfil-dato-icon">✉</span>
                                    <div>
                                        <span class="perfil-dato-label">Correo</span>
                                        <span class="perfil-dato-valor"><?= htmlspecialchars($fila['email']) ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($fila['link_empresa'])): ?>
                                <div class="perfil-dato-item">
                                    <span class="perfil-dato-icon">🌐</span>
                                    <div>
                                        <span class="perfil-dato-label">Sitio web</span>
                                        <?php
                                        $url_limpia = preg_replace('/^https?:\/\/(www\.)?|www\./i', '', $fila['link_empresa']);
                                        $url_limpia = rtrim($url_limpia, '/');
                                        ?>
                                        <a class="perfil-dato-valor perfil-link"
                                            href="<?= htmlspecialchars($fila['link_empresa']) ?>"
                                            target="_blank"><?= htmlspecialchars($url_limpia) ?></a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (count($fotos_arr) > 0): ?>
                        <div class="perfil-galeria-card">
                            <p class="perfil-section-label">Galería</p>
                            <div class="perfil-galeria-grid">
                                <?php foreach ($fotos_arr as $foto): ?>
                                    <img src="assets/img/empresascarrusel/<?= htmlspecialchars($foto) ?>"
                                        alt="Foto de <?= htmlspecialchars($fila['nombre']) ?>" class="perfil-galeria-foto">
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($fila['descripcion'])): ?>
                    <div class="perfil-descripcion">
                        <p class="perfil-section-label">Descripción</p>
                        <p class="perfil-descripcion-texto"><?= nl2br(htmlspecialchars($fila['descripcion'])) ?></p>
                    </div>
                <?php endif; ?>

                <?php
                $resena_msg = $_GET['resena'] ?? null;
                $id_empresa_int = intval($id_empresa);
                $stmt_res = $conexion->prepare("SELECT r.*, u.visibilidad_resenas FROM resenas r LEFT JOIN usuarios_publicos u ON r.id_usuario_publico = u.id WHERE r.id_empresa = ? ORDER BY r.fecha DESC");
                $stmt_res->bind_param("i", $id_empresa_int);
                $stmt_res->execute();
                $resenas_q = $stmt_res->get_result();
                $total_resenas = $resenas_q ? $resenas_q->num_rows : 0;
                $promedio = 0;
                $resenas_arr = [];
                if ($total_resenas > 0) {
                    $stmt_prom = $conexion->prepare("SELECT AVG(estrellas) as prom FROM resenas WHERE id_empresa = ?");
                    $stmt_prom->bind_param("i", $id_empresa_int);
                    $stmt_prom->execute();
                    $sum_q = $stmt_prom->get_result();
                    $promedio = round($sum_q->fetch_assoc()['prom'], 1);
                    while ($r = $resenas_q->fetch_assoc())
                        $resenas_arr[] = $r;
                }
                ?>

                <div class="perfil-resenas" id="resenas">
                    <p class="perfil-section-label">Reseñas</p>

                    <?php if ($resena_msg === 'ok'): ?>
                        <div class="resena-alerta resena-alerta-ok">✅ ¡Reseña enviada con éxito!</div>
                    <?php elseif ($resena_msg === 'mala'): ?>
                        <div class="resena-alerta resena-alerta-error">⚠️ Tu reseña contiene palabras no permitidas.</div>
                    <?php elseif ($resena_msg === 'error'): ?>
                        <div class="resena-alerta resena-alerta-error">❌ Por favor completa todos los campos y selecciona
                            estrellas.</div>
                    <?php endif; ?>

                    <?php if ($total_resenas > 0): ?>
                        <div class="resenas-promedio">
                            <span class="resenas-prom-numero"><?= $promedio ?></span>
                            <div>
                                <div class="estrellas-display">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="<?= $i <= round($promedio) ? 'estrella-llena' : 'estrella-vacia' ?>">★</span>
                                    <?php endfor; ?>
                                </div>
                                <span class="resenas-total"><?= $total_resenas ?>
                                    reseña<?= $total_resenas > 1 ? 's' : '' ?></span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="resena-form-wrapper">
                        <?php if (isset($_SESSION['usuario_publico_id'])): ?>
                            <p class="resena-form-titulo">Hola, <?= htmlspecialchars($_SESSION['usuario_publico_nombre']) ?> 👋
                                Deja tu reseña</p>
                            <form method="POST" action="empresas.php?empresa=<?= $id_empresa ?>">
                                <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
                                <input type="hidden" name="resena_empresa" value="1">
                                <input type="hidden" name="id_empresa" value="<?= $id_empresa ?>">
                                <div class="form-group">
                                    <label>Calificación</label>
                                    <div class="estrellas-input" id="estrellasInput">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <span class="estrella-btn" data-valor="<?= $i ?>">★</span>
                                        <?php endfor; ?>
                                    </div>
                                    <input type="hidden" name="estrellas" id="estrellasValor" required>
                                </div>
                                <div class="form-group">
                                    <label>Comentario</label>
                                    <textarea name="comentario" rows="3" placeholder="Cuéntanos tu experiencia..." required
                                        maxlength="500"></textarea>
                                </div>
                                <button type="submit" class="btn-enviar-resena">Enviar reseña</button>
                            </form>
                        <?php else: ?>
                            <div style="text-align:center;padding:20px 0;">
                                <p style="color:var(--muted);margin-bottom:16px;font-size:15px;">
                                    Inicia sesión para dejar tu reseña
                                </p>
                                <a href="login_usuario.php?redir=<?= urlencode('empresas.php?empresa=' . $id_empresa . '#resenas') ?>"
                                    class="btn-enviar-resena" style="text-decoration:none;display:inline-block;">
                                    Iniciar sesión
                                </a>
                                <p style="margin-top:12px;font-size:13px;color:var(--muted);">
                                    ¿No tienes cuenta? <a href="registro_usuario.php"
                                        style="color:var(--rojo);font-weight:700;">Regístrate gratis</a>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if (count($resenas_arr) > 0): ?>
                        <div class="resenas-lista">
                            <?php foreach ($resenas_arr as $r):
                                $es_anonimo = ($r['visibilidad_resenas'] ?? 'publico') === 'anonimo';
                                $nombre_mostrar = $es_anonimo ? 'Usuario Anónimo' : htmlspecialchars($r['nombre_autor']);
                                $letra_avatar = $es_anonimo ? '👤' : mb_strtoupper(mb_substr($r['nombre_autor'], 0, 1));
                                ?>
                                <div class="resena-item">
                                    <div class="resena-header">
                                        <div class="resena-avatar"><?= $letra_avatar ?></div>
                                        <div>
                                            <strong><?= $nombre_mostrar ?></strong>
                                            <div class="estrellas-display small">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <span
                                                        class="<?= $i <= $r['estrellas'] ? 'estrella-llena' : 'estrella-vacia' ?>">★</span>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <span class="resena-fecha"><?= date('d/m/Y', strtotime($r['fecha'])) ?></span>
                                    </div>
                                    <p class="resena-comentario"><?= nl2br(htmlspecialchars($r['comentario'])) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

            <?php
            // EMPRESAS SIMILARES
            $id_cat_actual = intval($fila['id_categoria'] ?? 0);
            $id_emp_actual = intval($id_empresa);
            if ($id_cat_actual > 0) {
                $stmt_sim = $conexion->prepare("SELECT e.*, c.nombre AS categoria FROM empresas e JOIN categorias c ON e.id_categoria = c.id_categoria WHERE e.id_categoria = ? AND e.id_empresa != ? ORDER BY RAND() LIMIT 3");
                $stmt_sim->bind_param("ii", $id_cat_actual, $id_emp_actual);
                $stmt_sim->execute();
                $res_sim = $stmt_sim->get_result();

                if ($res_sim && $res_sim->num_rows > 0):
                    ?>
                    <div style="margin-top: 40px; padding-top: 30px; border-top: 1px solid var(--borde);">
                        <div class="section-header" style="text-align: left; margin-bottom: 24px;">
                            <h2 style="font-size: 24px; color: var(--ink);">También podría interesarte...</h2>
                            <p style="color: var(--muted); font-size: 15px; margin-top: 4px;">Otras opciones en
                                <?= htmlspecialchars($fila['categoria'] ?? '') ?>
                            </p>
                        </div>
                        <div class="empresas-list">
                            <?php while ($f_sim = $res_sim->fetch_assoc()):
                                $logo_s = !empty($f_sim['logo']) ? htmlspecialchars($f_sim['logo']) : null;
                                $tel_s = $f_sim['telefono'] ?? null;
                                $num_s = $tel_s ? preg_replace('/[^0-9]/', '', $tel_s) : null;
                                $id_s = intval($f_sim['id_empresa']);
                                ?>
                                <div class="empresa-item <?= $f_sim['destacada'] ? 'empresa-destacada' : '' ?>">
                                    <div class="empresa-info-logo">
                                        <div class="empresa-top-row">
                                            <div class="empresa-logo">
                                                <?php if ($logo_s): ?>
                                                    <img src="assets/img/<?= $logo_s ?>" alt="<?= htmlspecialchars($f_sim['nombre']) ?>">
                                                <?php else: ?>
                                                    <div class="logo-placeholder"><?= mb_strtoupper(mb_substr($f_sim['nombre'], 0, 1)) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="empresa-titles">
                                                <h3 style="font-size: 16px;">
                                                    <?= htmlspecialchars($f_sim['nombre']) ?>
                                                    <?php if ($f_sim['destacada']): ?>
                                                        <span class="badge-destacada">⭐</span>
                                                    <?php endif; ?>
                                                </h3>
                                                <span class="empresa-categoria"
                                                    style="font-size: 12px;"><?= htmlspecialchars($f_sim['categoria']) ?></span>
                                            </div>
                                        </div>
                                        <p class="empresa-slogan" style="font-size: 13px;">
                                            <?= !empty($f_sim['descripcion']) ? htmlspecialchars(mb_strimwidth($f_sim['descripcion'], 0, 70, '…')) : 'Tu mejor opción local' ?>
                                        </p>
                                        <div class="empresa-datos" style="font-size: 12px;">
                                            <span>👁 <?= number_format($f_sim['vistas']) ?> vistas</span>
                                        </div>
                                        <div class="empresa-actions">
                                            <a href="empresas.php?empresa=<?= $id_s ?>" class="btn-ver"
                                                style="padding: 6px 12px; font-size: 13px;">Ver perfil</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                <?php endif;
            }
            ?>

            <?php
        elseif ($resultado && $resultado->num_rows > 0):
            if ($buscar): ?>
                <div class="filtro-activo">
                    🔍 Resultados para: "<?= htmlspecialchars($buscar) ?>"
                    <a href="empresas.php" title="Limpiar">✕</a>
                </div>
            <?php elseif ($id_categoria):
                $id_cat_int = intval($id_categoria);
                $stmt_cat = $conexion->prepare("SELECT nombre FROM categorias WHERE id_categoria=?");
                $stmt_cat->bind_param("i", $id_cat_int);
                $stmt_cat->execute();
                $cat_res = $stmt_cat->get_result();
                $cat_nombre = $cat_res ? $cat_res->fetch_assoc()['nombre'] : 'Categoría';
                ?>
                <div class="filtro-activo">
                    🏷 Categoría: <?= htmlspecialchars($cat_nombre) ?>
                    <a href="empresas.php" title="Ver todas">✕</a>
                </div>
            <?php endif; ?>

            <div class="empresas-list">
                <?php
                while ($fila = $resultado->fetch_assoc()):
                    $logo = !empty($fila['logo']) ? htmlspecialchars($fila['logo']) : null;
                    $telefono = $fila['telefono'] ?? null;
                    $numero = $telefono ? preg_replace('/[^0-9]/', '', $telefono) : null;
                    $id = intval($fila['id_empresa']);
                    $stmt_f = $conexion->prepare("SELECT foto FROM empresa_galeria WHERE id_empresa = ? ORDER BY orden ASC, id_foto ASC");
                    $stmt_f->bind_param("i", $id);
                    $stmt_f->execute();
                    $fotos = $stmt_f->get_result();
                    $fotos_arr = [];
                    if ($fotos && $fotos->num_rows > 0)
                        while ($f = $fotos->fetch_assoc())
                            $fotos_arr[] = $f['foto'];
                    ?>
                    <div class="empresa-item <?= $fila['destacada'] ? 'empresa-destacada' : '' ?>">
                        <div class="empresa-info-logo">
                            <div class="empresa-top-row">
                                <div class="empresa-logo">
                                    <?php if ($logo): ?>
                                        <img src="assets/img/<?= $logo ?>" alt="<?= htmlspecialchars($fila['nombre']) ?>">
                                    <?php else: ?>
                                        <div class="logo-placeholder"><?= mb_strtoupper(mb_substr($fila['nombre'], 0, 1)) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="empresa-titles">
                                    <h3>
                                        <?= htmlspecialchars($fila['nombre']) ?>
                                        <?php if ($fila['destacada']): ?>
                                            <span class="badge-destacada">⭐ Destacada</span>
                                        <?php endif; ?>
                                    </h3>
                                    <span class="empresa-categoria"><?= htmlspecialchars($fila['categoria']) ?></span>
                                </div>
                            </div>
                            <p class="empresa-slogan">
                                <?= !empty($fila['descripcion']) ? htmlspecialchars(mb_strimwidth($fila['descripcion'], 0, 80, '…')) : 'Tu mejor opción local' ?>
                            </p>
                            <?php if (!empty($fila['direccion'])): ?>
                                <p class="empresa-direccion">📍 <?= htmlspecialchars($fila['direccion']) ?></p>
                            <?php endif; ?>
                            <div class="empresa-datos">
                                <?php if (!empty($fila['horario'])): ?>
                                    <span>🕒 <?= htmlspecialchars($fila['horario']) ?></span>
                                <?php endif; ?>
                                <?php if ($numero): ?>
                                    <span>📞 <?= $numero ?></span>
                                <?php endif; ?>
                                <span>👁 <?= number_format($fila['vistas']) ?> vistas</span>
                            </div>
                            <div class="empresa-actions">
                                <a href="empresas.php?empresa=<?= $id ?>" class="btn-ver">Ver más</a>
                                <?php if (!empty($fila['link_empresa'])): ?>
                                    <a href="<?= htmlspecialchars($fila['link_empresa']) ?>" target="_blank" class="btn-ver"
                                        style="background-color: #3b82f6; color: white;">
                                        🌐 Sitio Web
                                    </a>
                                <?php endif; ?>
                                <?php if (!empty($fila['facebook'])): ?>
                                    <a href="<?= htmlspecialchars($fila['facebook']) ?>" target="_blank"
                                        class="btn-accion btn-accion-facebook">
                                        📘 Facebook
                                    </a>
                                <?php endif; ?>
                                <?php if ($numero): ?>
                                    <a href="https://wa.me/<?= $numero ?>" target="_blank" class="btn-whatsapp">WhatsApp</a>
                                <?php endif; ?>
                                <?php if (!empty($fila['ubicacion_link'])): ?>
                                    <a href="<?= htmlspecialchars($fila['ubicacion_link']) ?>" target="_blank"
                                        class="btn-maps">Ubicación</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if (count($fotos_arr) > 0): ?>
                            <div class="empresa-slider">
                                <?php foreach ($fotos_arr as $i => $foto): ?>
                                    <div class="slide <?= $i === 0 ? 'activo' : '' ?>">
                                        <img src="assets/img/empresascarrusel/<?= htmlspecialchars($foto) ?>"
                                            alt="Imagen de <?= htmlspecialchars($fila['nombre']) ?>" loading="lazy">
                                    </div>
                                <?php endforeach; ?>
                                <?php if (count($fotos_arr) > 1): ?>
                                    <div class="slider-dots">
                                        <?php foreach ($fotos_arr as $i => $_): ?>
                                            <button class="slider-dot <?= $i === 0 ? 'activo' : '' ?>" data-index="<?= $i ?>"></button>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>

            <?php
            if (isset($total_paginas) && $total_paginas > 1 && !$id_empresa):
                $params_url = [];
                if (!empty($buscar))
                    $params_url['buscar'] = $buscar;
                if (!empty($id_categoria))
                    $params_url['id_categoria'] = $id_categoria;
                $query_str = http_build_query($params_url);
                if (!empty($query_str))
                    $query_str = '&' . $query_str;
                ?>
                <style>
                    .paginacion .btn-pag {
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        padding: 8px 16px;
                        border-radius: 8px;
                        background: white;
                        color: var(--ink);
                        text-decoration: none;
                        font-weight: 500;
                        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
                        border: 1px solid #e2e8f0;
                        transition: all 0.2s;
                    }

                    .paginacion .btn-pag:hover {
                        background: #f8fafc;
                        border-color: #cbd5e1;
                    }

                    .paginacion .btn-pag.activa {
                        background: var(--primario);
                        color: white;
                        border-color: var(--primario);
                        pointer-events: none;
                    }

                    @media (max-width: 600px) {
                        .paginacion .btn-pag {
                            padding: 6px 12px;
                            font-size: 14px;
                        }
                    }
                </style>

                <div class="paginacion"
                    style="display: flex; justify-content: center; flex-wrap: wrap; gap: 8px; margin-top: 40px; margin-bottom: 20px;">
                    <?php if ($pagina_actual > 1): ?>
                        <a href="?pagina=<?= $pagina_actual - 1 ?><?= $query_str ?>" class="btn-pag">Anterior</a>
                    <?php endif; ?>

                    <?php
                    for ($p = 1; $p <= $total_paginas; $p++):
                        // Mostramos solo un rango de +-2 botones alrededor del actual, y los extremos.
                        if ($p == 1 || $p == $total_paginas || abs($p - $pagina_actual) <= 2):
                            ?>
                            <a href="?pagina=<?= $p ?><?= $query_str ?>" class="btn-pag <?= $p == $pagina_actual ? 'activa' : '' ?>">
                                <?= $p ?>
                            </a>
                        <?php elseif (abs($p - $pagina_actual) == 3): ?>
                            <span style="display:flex; align-items:flex-end; color: #94a3b8;">...</span>
                        <?php endif; endfor; ?>

                    <?php if ($pagina_actual < $total_paginas): ?>
                        <a href="?pagina=<?= $pagina_actual + 1 ?><?= $query_str ?>" class="btn-pag">Siguiente</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="no-results">
                <p>😕 No se encontraron
                    empresas<?= $buscar ? ' para "<strong>' . htmlspecialchars($buscar) . '</strong>"' : '' ?>.</p>
                <br><br>
                <a href="empresas.php">Ver todas las empresas</a>
            </div>
        <?php endif; ?>

    </div>
</section>

<?php include 'includes/footer.php'; ?>

<script>
    document.querySelectorAll('.empresa-slider').forEach(slider => {
        const slides = slider.querySelectorAll('.slide');
        const dots = slider.querySelectorAll('.slider-dot');
        if (slides.length <= 1) return;
        let idx = 0;
        function goTo(n) {
            slides[idx].classList.remove('activo');
            if (dots[idx]) dots[idx].classList.remove('activo');
            idx = (n + slides.length) % slides.length;
            slides[idx].classList.add('activo');
            if (dots[idx]) dots[idx].classList.add('activo');
        }
        const autoplay = setInterval(() => goTo(idx + 1), 4000);
        dots.forEach((dot, i) => dot.addEventListener('click', () => { clearInterval(autoplay); goTo(i); }));
    });

    (function () {
        const estrellasInput = document.getElementById('estrellasInput');
        const estrellasValor = document.getElementById('estrellasValor');
        if (!estrellasInput) return;
        const btns = estrellasInput.querySelectorAll('.estrella-btn');
        btns.forEach((btn, index) => {
            btn.addEventListener('mouseover', () => {
                btns.forEach((b, i) => b.classList.toggle('activa', i <= index));
            });
            btn.addEventListener('click', () => {
                estrellasValor.value = btn.dataset.valor;
                btns.forEach((b, i) => b.classList.toggle('activa', i <= index));
            });
        });
        estrellasInput.addEventListener('mouseleave', () => {
            const val = parseInt(estrellasValor.value || 0);
            btns.forEach((b, i) => b.classList.toggle('activa', i < val));
        });
    })();
</script>