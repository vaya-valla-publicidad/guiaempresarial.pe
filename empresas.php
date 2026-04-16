<?php
session_start();
include 'db.php';
include 'includes/security.php';
include 'includes/components/empresa_card.php';
include 'includes/components/breadcrumbs.php';
$seo_title = "Empresas - Guía Empresarial";
$seo_description = "Explora negocios locales y descubre nuevas oportunidades en tu región.";

$seo_id_empresa = $_GET['empresa'] ?? null;
$seo_id_categoria = $_GET['id_categoria'] ?? null;
$slug_param = $_GET['slug'] ?? null;
$cat_slug_param = $_GET['cat_slug'] ?? null;

$id_empresa = $seo_id_empresa;
$id_categoria = $seo_id_categoria;

if ($seo_id_empresa && is_numeric($seo_id_empresa)) {
    $stmt_r = $conexion->prepare("SELECT slug FROM empresas WHERE id_empresa = ?");
    $stmt_r->bind_param("i", $seo_id_empresa);
    $stmt_r->execute();
    $res_r = $stmt_r->get_result();
    if ($res_r && $row = $res_r->fetch_assoc()) {
        header("Location: " . APP_URL . "/negocio/" . $row['slug'], true, 301);
        exit;
    }
}
if ($seo_id_categoria && is_numeric($seo_id_categoria)) {
    $stmt_r = $conexion->prepare("SELECT slug FROM categorias WHERE id_categoria = ?");
    $stmt_r->bind_param("i", $seo_id_categoria);
    $stmt_r->execute();
    $res_r = $stmt_r->get_result();
    if ($res_r && $row = $res_r->fetch_assoc()) {
        header("Location: " . APP_URL . "/rubro/" . $row['slug'], true, 301);
        exit;
    }
}

if ($slug_param) {
    $stmt_seo = $conexion->prepare("SELECT id_empresa, nombre, descripcion, logo FROM empresas WHERE slug = ?");
    $stmt_seo->bind_param("s", $slug_param);
    $stmt_seo->execute();
    $res_seo = $stmt_seo->get_result();
    if ($res_seo && $res_seo->num_rows === 1) {
        $emp_seo = $res_seo->fetch_assoc();
        $id_empresa = $emp_seo['id_empresa'];
        $seo_title = $emp_seo['nombre'] . " - Guía Empresarial";
        if (!empty($emp_seo['descripcion'])) {
            $seo_description = mb_strimwidth($emp_seo['descripcion'], 0, 150, "...");
        }
        if (!empty($emp_seo['logo'])) {
            $seo_image = APP_URL . "/assets/img/" . $emp_seo['logo'];
        }
    } else {
        header("Location: " . APP_URL . "/404.php", true, 302);
        exit;
    }
} elseif ($cat_slug_param) {
    $stmt_seo_c = $conexion->prepare("SELECT id_categoria, nombre FROM categorias WHERE slug = ?");
    $stmt_seo_c->bind_param("s", $cat_slug_param);
    $stmt_seo_c->execute();
    $res_seo_c = $stmt_seo_c->get_result();
    if ($res_seo_c && $res_seo_c->num_rows === 1) {
        $cat_seo = $res_seo_c->fetch_assoc();
        $id_categoria = $cat_seo['id_categoria'];
        $cat_nombre = $cat_seo['nombre'];
        $seo_title = "Empresas en " . $cat_nombre . " - Guía Empresarial";
    } else {
        header("Location: " . APP_URL . "/404.php", true, 302);
        exit;
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

        <?php if (!$id_empresa): ?>
            <div class="search-listing-wrapper"
                style="margin-bottom: 40px; max-width: 600px; margin-left: auto; margin-right: auto;">
                <form action="<?= APP_URL ?>/empresas.php" method="GET" class="search-form"
                    style="display: flex; gap: 10px; background: white; padding: 6px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid var(--borde);">
                    <div style="position: relative; flex: 1; display: flex; align-items: center;">
                        <i class="bi bi-search"
                            style="position: absolute; left: 15px; color: var(--muted); font-size: 18px;"></i>
                        <input type="text" name="buscar" id="inputBuscarListing" value=""
                            placeholder="¿Qué estás buscando hoy?"
                            style="width: 100%; border: none; padding: 12px 40px 12px 45px; border-radius: 8px; outline: none; font-size: 16px;">
                        <?php if (!empty($_GET['buscar'])): ?>
                            <a href="<?= APP_URL ?>/empresas.php"
                                style="position: absolute; right: 10px; color: var(--muted); font-size: 20px; text-decoration: none; display: flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: 50%; transition: background 0.2s;"
                                onmouseover="this.style.background='#f0f0f0'" onmouseout="this.style.background='transparent'">
                                <i class="bi bi-x"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn-primary"
                        style="margin: 0; padding: 0 30px; border-radius: 12px; border: none; font-size: 15px; min-width: 120px; font-weight: 700; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 12px rgba(230, 57, 70, 0.2);">
                        <i class="bi bi-search" style="margin-right: 5px;"></i> Buscar
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <?php
        $buscar = $_GET['buscar'] ?? null;

        $pagina_actual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
        $empresas_por_pagina = 20;
        $offset = ($pagina_actual - 1) * $empresas_por_pagina;

        $sql_select = "SELECT e.*, c.nombre AS categoria, c.slug AS cat_slug, 
                              GROUP_CONCAT(g.foto ORDER BY g.orden ASC, g.id_foto ASC SEPARATOR ',') as fotos_galeria 
                       FROM empresas e 
                       JOIN categorias c ON e.id_categoria = c.id_categoria
                       LEFT JOIN empresa_galeria g ON e.id_empresa = g.id_empresa";
        $sql_count = "SELECT COUNT(DISTINCT e.id_empresa) as total FROM empresas e JOIN categorias c ON e.id_categoria = c.id_categoria";

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

        $sql = $sql_select . $clausula_where . " GROUP BY e.id_empresa";
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
            header("Location: " . APP_URL . "/404.php", true, 302);
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

            <?php
            $json_ld_schema = [
                "@context" => "https://schema.org",
                "@type" => "LocalBusiness",
                "name" => $fila['nombre'],
                "description" => $fila['descripcion'] ?? '',
                "url" => APP_URL . "/negocio/" . $fila['slug'],
                "telephone" => $numero ? "+51 " . $numero : null,
                "address" => [
                    "@type" => "PostalAddress",
                    "streetAddress" => $fila['direccion'] ?? '',
                    "addressLocality" => "Pucallpa",
                    "addressRegion" => "Ucayali",
                    "addressCountry" => "PE"
                ],
                "image" => $logo ? APP_URL . "/assets/img/" . $logo : null,
                "priceRange" => "$$"
            ];

            $bc_items = [
                [
                    "name" => $fila['categoria'],
                    "url" => APP_URL . "/rubro/" . $fila['cat_slug']
                ],
                [
                    "name" => $fila['nombre'],
                    "url" => APP_URL . "/negocio/" . $fila['slug']
                ]
            ];
            echo renderBreadcrumbs($bc_items);
            ?>

            <div class="perfil-wrapper">
                <div class="perfil-hero">
                    <div class="perfil-banner"></div>
                    <div class="perfil-hero-body">
                        <?php if ($logo): ?>
                            <img class="perfil-logo" src="<?= APP_URL ?>/assets/img/<?= $logo ?>"
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
                                    <img src="<?= APP_URL ?>/assets/img/empresascarrusel/<?= htmlspecialchars($foto) ?>"
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

                    <div id="resenaAlertContainer">
                        <?php if ($resena_msg === 'ok'): ?>
                            <div class="resena-alerta resena-alerta-ok">✅ ¡Reseña enviada con éxito!</div>
                        <?php elseif ($resena_msg === 'mala'): ?>
                            <div class="resena-alerta resena-alerta-error">⚠️ Tu reseña contiene palabras no permitidas.</div>
                        <?php elseif ($resena_msg === 'error'): ?>
                            <div class="resena-alerta resena-alerta-error">❌ Por favor completa todos los campos y selecciona
                                estrellas.</div>
                        <?php endif; ?>
                    </div>

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
                            <form id="formResena" method="POST" action="<?= APP_URL ?>/empresas.php?empresa=<?= $id_empresa ?>">
                                <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
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
                                    <textarea name="comentario" id="resenaComentario" rows="3"
                                        placeholder="Cuéntanos tu experiencia..." required maxlength="500"></textarea>
                                </div>
                                <button type="submit" class="btn-enviar-resena" id="btnEnviarResena">Enviar reseña</button>
                            </form>
                        <?php else: ?>
                            <div style="text-align:center;padding:20px 0;">
                                <p style="color:var(--muted);margin-bottom:16px;font-size:15px;">
                                    Inicia sesión para dejar tu reseña
                                </p>
                                <a href="<?= APP_URL ?>/login_usuario.php?redir=<?= urlencode('negocio/' . ($slug_param ?? $id_empresa) . '#resenas') ?>"
                                    class="btn-enviar-resena" style="text-decoration:none;display:inline-block;">
                                    Iniciar sesión
                                </a>
                                <p style="margin-top:12px;font-size:13px;color:var(--muted);">
                                    ¿No tienes cuenta? <a href="<?= APP_URL ?>/registro_usuario.php"
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
                    <?php else: ?>
                        <div class="empty-reviews-premium">
                            <span class="empty-reviews-icon">
                                <i class="bi bi-chat-left-heart"></i>
                            </span>
                            <div class="empty-reviews-text">
                                <h3>¡Sé el primero en calificar!</h3>
                                <p>Este negocio aún no tiene reseñas. Comparte tu experiencia y ayuda a otros.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

            <?php

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
                            <?php

                            $stmt_sim = $conexion->prepare("SELECT e.*, c.nombre AS categoria, GROUP_CONCAT(g.foto ORDER BY g.orden ASC SEPARATOR ',') as fotos_galeria FROM empresas e JOIN categorias c ON e.id_categoria = c.id_categoria LEFT JOIN empresa_galeria g ON e.id_empresa = g.id_empresa WHERE e.id_categoria = ? AND e.id_empresa != ? GROUP BY e.id_empresa ORDER BY RAND() LIMIT 3");
                            $stmt_sim->bind_param("ii", $id_cat_actual, $id_emp_actual);
                            $stmt_sim->execute();
                            $res_sim = $stmt_sim->get_result();
                            while ($f_sim = $res_sim->fetch_assoc()):
                                $fotos_sim = !empty($f_sim['fotos_galeria']) ? explode(',', $f_sim['fotos_galeria']) : [];
                                renderEmpresaCard($f_sim, $fotos_sim);
                            endwhile; ?>
                        </div>
                    </div>
                <?php endif;
            }
            ?>
        </div>
        <?php
        elseif ($resultado && $resultado->num_rows > 0):
            $bc_items = [];
            if ($buscar) {
                $bc_items[] = ["name" => "Búsqueda", "url" => null];
            } elseif ($id_categoria) {
                $bc_items[] = ["name" => "Categorías", "url" => APP_URL . "/categorias.php"];
                $bc_items[] = ["name" => $cat_nombre ?? 'Categoría', "url" => null];
            } else {
                $bc_items[] = ["name" => "Empresas", "url" => null];
            }
            echo renderBreadcrumbs($bc_items);
            ?>

        <?php if ($buscar): ?>
            <div class="filtro-activo">
                🔍 Resultados para: "<?= htmlspecialchars($buscar) ?>"
                <a href="<?= APP_URL ?>/empresas.php" title="Limpiar">✕</a>
            </div>
        <?php elseif ($id_categoria): ?>
            <div class="filtro-activo">
                🏷 Categoría: <?= htmlspecialchars($cat_nombre ?? 'Categoría') ?>
                <a href="<?= APP_URL ?>/empresas.php" title="Ver todas">✕</a>
            </div>
        <?php endif; ?>

        <div class="empresas-list">
            <?php
            while ($fila = $resultado->fetch_assoc()):
                $fotos_arr = !empty($fila['fotos_galeria']) ? explode(',', $fila['fotos_galeria']) : [];
                renderEmpresaCard($fila, $fotos_arr);
            endwhile; ?>
        </div>

        <?php
        if (isset($total_paginas) && $total_paginas > 1 && !$id_empresa):
            $params_url = [];
            if (!empty($buscar))
                $params_url['buscar'] = $buscar;
            if (!empty($seo_id_categoria) && empty($cat_slug_param))
                $params_url['id_categoria'] = $seo_id_categoria;
            if (!empty($seo_id_empresa) && empty($slug_param))
                $params_url['empresa'] = $seo_id_empresa;

            $query_str = http_build_query($params_url);
            if (!empty($query_str))
                $query_str = '&' . $query_str;
            ?>

            <div class="paginacion"
                style="display: flex; justify-content: center; flex-wrap: wrap; gap: 8px; margin-top: 40px; margin-bottom: 20px;">
                <?php if ($pagina_actual > 1): ?>
                    <a href="<?= APP_URL ?>/empresas.php?pagina=<?= $pagina_actual - 1 ?><?= $query_str ?>"
                        class="btn-pag">Anterior</a>
                <?php endif; ?>

                <?php
                for ($p = 1; $p <= $total_paginas; $p++):
                    if ($p == 1 || $p == $total_paginas || abs($p - $pagina_actual) <= 2):
                        ?>
                        <a href="<?= APP_URL ?>/empresas.php?pagina=<?= $p ?><?= $query_str ?>"
                            class="btn-pag <?= $p == $pagina_actual ? 'activa' : '' ?>">
                            <?= $p ?>
                        </a>
                    <?php elseif (abs($p - $pagina_actual) == 3): ?>
                        <span style="display:flex; align-items:flex-end; color: #94a3b8;">...</span>
                    <?php endif; endfor; ?>

                <?php if ($pagina_actual < $total_paginas): ?>
                    <a href="<?= APP_URL ?>/empresas.php?pagina=<?= $pagina_actual + 1 ?><?= $query_str ?>"
                        class="btn-pag">Siguiente</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="no-results">
            <p>😕 No se encontraron
                empresas<?= $buscar ? ' para "<strong>' . htmlspecialchars($buscar) . '</strong>"' : '' ?>.</p>
            <br><br>
            <a href="<?= APP_URL ?>/empresas.php">Ver todas las empresas</a>
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
        const starsInput = document.getElementById('estrellasInput');
        const starsValue = document.getElementById('estrellasValor');
        if (!starsInput) return;
        const btns = starsInput.querySelectorAll('.estrella-btn');
        btns.forEach((btn, index) => {
            btn.addEventListener('mouseover', () => {
                btns.forEach((b, i) => b.classList.toggle('activa', i <= index));
            });
            btn.addEventListener('click', () => {
                starsValue.value = btn.dataset.valor;
                btns.forEach((b, i) => b.classList.toggle('activa', i <= index));
            });
        });
        starsInput.addEventListener('mouseleave', () => {
            const val = parseInt(starsValue.value || 0);
            btns.forEach((b, i) => b.classList.toggle('activa', i < val));
        });


        const form = document.getElementById('formResena');
        if (!form) return;

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const btn = document.getElementById('btnEnviarResena');
            const alertBox = document.getElementById('resenaAlertContainer');
            const stars = starsValue.value;
            const comment = document.getElementById('resenaComentario').value;

            if (!stars) {
                alertBox.innerHTML = '<div class="resena-alerta resena-alerta-error">❌ Por favor selecciona una calificación.</div>';
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Enviando...';

            const formData = new FormData(form);

            fetch('<?= APP_URL ?>/ajax/resena_submit.php', {
                method: 'POST',
                body: formData
            })
                .then(r => r.json())
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = 'Enviar reseña';

                    if (data.success) {
                        alertBox.innerHTML = '<div class="resena-alerta resena-alerta-ok">✅ ¡Tu reseña se ha publicado con éxito!</div>';
                        form.reset();
                        starsValue.value = '';
                        btns.forEach(b => b.classList.remove('activa'));


                        const lista = document.querySelector('.resenas-lista');
                        const emptyState = document.querySelector('.empty-reviews-premium');

                        if (emptyState) emptyState.remove();

                        const item = document.createElement('div');
                        item.className = 'resena-item nuevo';
                        item.innerHTML = `
                        <div class="resena-header">
                            <div class="resena-avatar">${data.letra}</div>
                            <div>
                                <strong>${data.nombre}</strong>
                                <div class="estrellas-display small">
                                    ${'★'.repeat(data.estrellas)}${'☆'.repeat(5 - data.estrellas)}
                                </div>
                            </div>
                            <span class="resena-fecha">${data.fecha}</span>
                        </div>
                        <p class="resena-comentario">${data.comentario}</p>
                    `;

                        if (lista) {
                            lista.prepend(item);
                        } else {
                            const nuevaLista = document.createElement('div');
                            nuevaLista.className = 'resenas-lista';
                            nuevaLista.appendChild(item);
                            form.closest('.resena-form-wrapper').after(nuevaLista);
                        }

                        setTimeout(() => item.classList.add('visible'), 10);
                    } else {
                        alertBox.innerHTML = `<div class="resena-alerta resena-alerta-error">❌ ${data.error}</div>`;
                    }
                })
                .catch(err => {
                    btn.disabled = false;
                    btn.innerHTML = 'Enviar reseña';
                    alertBox.innerHTML = '<div class="resena-alerta resena-alerta-error">❌ Error al conectar con el servidor.</div>';
                });
        });
    })();
</script>