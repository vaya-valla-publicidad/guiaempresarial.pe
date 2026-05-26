<?php
include 'db.php';
include 'includes/security.php';
include 'includes/components/empresa_card.php';
include 'includes/components/breadcrumbs.php';
$seo_title = "Empresas - Guía Empresarial";
$conexion->query("SET SESSION group_concat_max_len = 100000");
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
        header("Location: " . APP_URL . "/404", true, 302);
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
        header("Location: " . APP_URL . "/404", true, 302);
        exit;
    }
}
include 'includes/Header.php';
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
                <form action="<?= APP_URL ?>/empresas" method="GET" class="search-form"
                    style="display: flex; gap: 10px; background: white; padding: 6px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid var(--borde);">
                    <div style="position: relative; flex: 1; display: flex; align-items: center;">
                        <i class="bi bi-search"
                            style="position: absolute; left: 15px; color: var(--muted); font-size: 18px;"></i>
                        <input type="text" name="buscar" id="inputBuscarListing" value=""
                            placeholder="¿Qué estás buscando hoy?"
                            style="width: 100%; border: none; padding: 12px 40px 12px 45px; border-radius: 8px; outline: none; font-size: 16px;">
                        <?php if (!empty($_GET['buscar'])): ?>
                            <a href="<?= APP_URL ?>/empresas"
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

        $orden_actual = $_GET['orden'] ?? 'populares';
        if (!in_array($orden_actual, ['populares', 'valoradas', 'recientes']))
            $orden_actual = 'populares';

        $sql_select = "SELECT e.*, c.nombre AS categoria, c.slug AS cat_slug, 
                              GROUP_CONCAT(g.foto ORDER BY g.orden ASC, g.id_foto ASC SEPARATOR ',') as fotos_galeria,
                              (SELECT AVG(estrellas) FROM resenas r WHERE r.id_empresa = e.id_empresa) as promedio 
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
            $texto = escaparLike($buscar);
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
                    $total_filas = $res_c ? ($res_c->fetch_assoc()['total'] ?? 0) : 0;
                    $stmt_c->close();
                } else {
                    $total_filas = 0;
                }
            } else {
                $res_c = $conexion->query($sql_c);
                $total_filas = $res_c ? ($res_c->fetch_assoc()['total'] ?? 0) : 0;
            }
            $total_paginas = ceil($total_filas / $empresas_por_pagina);
        }

        $sql = $sql_select . $clausula_where . " GROUP BY e.id_empresa";

        $orden_sql = " ORDER BY e.destacada DESC, e.vistas DESC";
        if ($orden_actual === 'valoradas') {
            $orden_sql = " ORDER BY e.destacada DESC, promedio DESC, e.vistas DESC";
        } elseif ($orden_actual === 'recientes') {
            $orden_sql = " ORDER BY e.id_empresa DESC";
        }
        $sql .= $orden_sql;

        if (!$id_empresa) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = intval($empresas_por_pagina);
            $params[] = intval($offset);
            $types .= "ii";
        }

        $stmt = $conexion->prepare($sql);
        if ($stmt) {
            if (!empty($types)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $resultado = $stmt->get_result();
            $stmt->close();
        } else {
            $resultado = false;
        }

        if ($id_empresa && (!$resultado || $resultado->num_rows === 0)) {
            header("Location: " . APP_URL . "/404", true, 302);
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

            $vista_key = 'vista_empresa_' . $id_empresa_int;
            if (!isset($_SESSION[$vista_key]) && !isset($_COOKIE[$vista_key])) {
                $stmt_vistas = $conexion->prepare("UPDATE empresas SET vistas = vistas + 1 WHERE id_empresa = ?");
                $stmt_vistas->bind_param("i", $id_empresa_int);
                $stmt_vistas->execute();
                $_SESSION[$vista_key] = true;
            }
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
                    <?php
                    $banner_style = "";
                    if (count($fotos_arr) > 0) {
                        $banner_img = APP_URL . "/assets/img/empresascarrusel/" . htmlspecialchars($fotos_arr[0]);
                        $banner_style = "style=\"background-image: url('$banner_img'); background-size: cover; background-position: center; filter: blur(40px) brightness(0.7); opacity: 0.6;\"";
                    }
                    ?>
                    <div class="perfil-banner">
                        <div class="perfil-banner-overlay" <?= $banner_style ?>></div>
                    </div>
                    <div class="perfil-hero-body">
                        <?php if ($logo): ?>
                            <img class="perfil-logo" src="<?= APP_URL ?>/assets/img/<?= $logo ?>"
                                alt="<?= htmlspecialchars($fila['nombre']) ?>">
                        <?php else: ?>
                            <div class="perfil-logo logo-placeholder" style="width:120px;height:120px;font-size:48px;">
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
                                    <span class="empresa-card-badge">
                                        <?= htmlspecialchars($fila['categoria']) ?>
                                    </span>
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
                                    <button onclick="compartirNegocio()" class="btn-accion"
                                        style="background: #64748b; color: white;">
                                        <i class="bi bi-share-fill" style="margin-right: 5px;"></i> Compartir
                                    </button>
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
                                        <span class="perfil-dato-valor">
                                            <?= htmlspecialchars($fila['direccion']) ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($fila['horario'])): ?>
                                <div class="perfil-dato-item">
                                    <span class="perfil-dato-icon">🕒</span>
                                    <div>
                                        <span class="perfil-dato-label">Horario</span>
                                        <span class="perfil-dato-valor">
                                            <?= htmlspecialchars($fila['horario']) ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if ($numero): ?>
                                <div class="perfil-dato-item">
                                    <span class="perfil-dato-icon">📞</span>
                                    <div>
                                        <span class="perfil-dato-label">Teléfono</span>
                                        <span class="perfil-dato-valor">
                                            <?= $numero ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($fila['email'])): ?>
                                <div class="perfil-dato-item">
                                    <span class="perfil-dato-icon">✉</span>
                                    <div>
                                        <span class="perfil-dato-label">Correo</span>
                                        <span class="perfil-dato-valor">
                                            <?= htmlspecialchars($fila['email']) ?>
                                        </span>
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
                                            href="<?= htmlspecialchars($fila['link_empresa']) ?>" target="_blank">
                                            <?= htmlspecialchars($url_limpia) ?>
                                        </a>
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

                <script>
                    function compartirNegocio() {
                        const url = window.location.href;
                        if (navigator.share) {
                            navigator.share({
                                title: '<?= htmlspecialchars($fila['nombre'] ?? 'Guía Empresarial') ?>',
                                text: 'Mira este negocio en Guía Empresarial',
                                url: url
                            }).catch((error) => console.log('Error sharing', error));
                        } else {
                            navigator.clipboard.writeText(url).then(() => {
                                if (typeof showToast === 'function') {
                                    showToast('Enlace copiado al portapapeles', 'success');
                                } else {
                                    alert('Enlace copiado al portapapeles');
                                }
                            });
                        }
                    }
                </script>

                <?php if (!empty($fila['descripcion'])): ?>
                    <div class="perfil-descripcion">
                        <p class="perfil-section-label">Descripción</p>
                        <p class="perfil-descripcion-texto">
                            <?= nl2br(htmlspecialchars($fila['descripcion'])) ?>
                        </p>
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
                    while ($r = $resenas_q->fetch_assoc()) {
                        $r['likes'] = 0;
                        $r['dislikes'] = 0;
                        $r['my_vote'] = '';
                        $resenas_arr[] = $r;
                    }
                }

                $usuario_tiene_resena = false;
                if (isset($_SESSION['usuario_publico_id'])) {
                    $usuario_publico_actual = intval($_SESSION['usuario_publico_id']);
                    foreach ($resenas_arr as $rr) {
                        if (intval($rr['id_usuario_publico']) === $usuario_publico_actual) {
                            $usuario_tiene_resena = true;
                            break;
                        }
                    }
                }
                $my_votes = [];

                if (count($resenas_arr) > 0) {
                    $resenas_ids = array_column($resenas_arr, 'id_resena');
                    $placeholders = implode(',', array_fill(0, count($resenas_ids), '?'));
                    $types = str_repeat('i', count($resenas_ids));

                    $sql = "SELECT id_resena, SUM(tipo = 'like') AS likes, SUM(tipo = 'dislike') AS dislikes FROM resena_votos WHERE id_resena IN ($placeholders) GROUP BY id_resena";
                    $stmt_votes = $conexion->prepare($sql);
                    $bindParams = array_merge([$types], $resenas_ids);
                    $tmp = [];
                    foreach ($bindParams as $key => $value) {
                        $tmp[$key] = &$bindParams[$key];
                    }
                    call_user_func_array([$stmt_votes, 'bind_param'], $tmp);
                    $stmt_votes->execute();
                    $votes_q = $stmt_votes->get_result();
                    $votes_data = [];
                    while ($vote = $votes_q->fetch_assoc()) {
                        $votes_data[intval($vote['id_resena'])] = $vote;
                    }

                    if (isset($_SESSION['usuario_publico_id'])) {
                        $sql = "SELECT id_resena, tipo FROM resena_votos WHERE id_resena IN ($placeholders) AND id_usuario_publico = ?";
                        $stmt_my = $conexion->prepare($sql);
                        $bindParams = array_merge([$types . 'i'], $resenas_ids, [$usuario_publico_actual]);
                        $tmp = [];
                        foreach ($bindParams as $key => $value) {
                            $tmp[$key] = &$bindParams[$key];
                        }
                        call_user_func_array([$stmt_my, 'bind_param'], $tmp);
                        $stmt_my->execute();
                        $my_q = $stmt_my->get_result();
                        $my_votes = [];
                        while ($my = $my_q->fetch_assoc()) {
                            $my_votes[intval($my['id_resena'])] = $my['tipo'];
                        }
                    }

                    foreach ($resenas_arr as &$rr) {
                        $idr = intval($rr['id_resena']);
                        if (isset($votes_data[$idr])) {
                            $rr['likes'] = intval($votes_data[$idr]['likes']);
                            $rr['dislikes'] = intval($votes_data[$idr]['dislikes']);
                        }
                        if (!empty($my_votes[$idr])) {
                            $rr['my_vote'] = $my_votes[$idr];
                        }
                    }
                    unset($rr);
                }
                ?>

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
                        <span class="resenas-prom-numero">
                            <?= $promedio ?>
                        </span>
                        <div>
                            <div class="estrellas-display">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="<?= $i <= round($promedio) ? 'estrella-llena' : 'estrella-vacia' ?>">★</span>
                                <?php endfor; ?>
                            </div>
                            <span class="resenas-total">
                                <?= $total_resenas ?>
                                reseña
                                <?= $total_resenas > 1 ? 's' : '' ?>
                            </span>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="resena-form-wrapper">
                    <?php if (isset($_SESSION['usuario_publico_id'])): ?>
                        <?php if ($usuario_tiene_resena): ?>
                            <div id="resenaEditHint" class="resena-alerta resena-alerta-info">Ya tienes tu reseña enviada. Presiona
                                <strong>Editar</strong> en tu comentario para actualizarla.
                            </div>
                        <?php endif; ?>
                        <form id="formResena" method="POST" action="<?= APP_URL ?>/empresas?empresa=<?= $id_empresa ?>"
                            style="<?= $usuario_tiene_resena ? 'display:none;' : '' ?>">
                            <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
                            <input type="hidden" name="id_empresa" value="<?= $id_empresa ?>">
                            <input type="hidden" name="id_resena" id="idResena" value="">
                            <p class="resena-form-titulo" id="resenaFormTitulo"
                                data-default-text="Hola, <?= htmlspecialchars($_SESSION['usuario_publico_nombre']) ?> 👋 Deja tu reseña">
                                Hola,
                                <?= htmlspecialchars($_SESSION['usuario_publico_nombre']) ?> 👋
                                Deja tu reseña
                            </p>
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
                                    placeholder="Cuéntanos tu experiencia..." required maxlength="1000"></textarea>
                                <div id="charCount"
                                    style="text-align: right; font-size: 12px; color: var(--muted); margin-top: 5px;">1000
                                    caracteres restantes</div>
                            </div>
                            <div class="resena-form-actions">
                                <button type="submit" class="btn-enviar-resena" id="btnEnviarResena">Enviar reseña</button>
                                <button type="button" class="btn-cancelar-edicion" id="btnCancelarEdicion"
                                    style="display:none;">Cancelar</button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div style="text-align:center;padding:20px 0;">
                            <p style="color:var(--muted);margin-bottom:16px;font-size:15px;">
                                Inicia sesión para dejar tu reseña
                            </p>
                            <a href="<?= APP_URL ?>/login_usuario?redir=<?= urlencode('negocio/' . ($slug_param ?? $id_empresa) . '#resenas') ?>"
                                class="btn-enviar-resena" style="text-decoration:none;display:inline-block;">
                                Iniciar sesión
                            </a>
                            <p style="margin-top:12px;font-size:13px;color:var(--muted);">
                                ¿No tienes cuenta? <a href="<?= APP_URL ?>/registro_usuario"
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
                            $avatar_class = 'resena-avatar' . ($es_anonimo ? ' anonimo' : '');
                            $avatar_content = $es_anonimo ? '' : mb_strtoupper(mb_substr($r['nombre_autor'], 0, 1));
                            ?>
                            <div class="resena-item" data-resena-id="<?= intval($r['id_resena']) ?>">
                                <div class="resena-header">
                                    <div class="<?= $avatar_class ?>">
                                        <?= $avatar_content ?>
                                    </div>
                                    <div>
                                        <strong>
                                            <?= $nombre_mostrar ?>
                                        </strong>
                                        <?php if (isset($_SESSION['usuario_publico_id']) && intval($_SESSION['usuario_publico_id']) === intval($r['id_usuario_publico'])): ?>
                                            <span class="resena-badge resena-badge-propia">Tu reseña</span>
                                        <?php endif; ?>
                                        <div class="estrellas-display small">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <span
                                                    class="<?= $i <= $r['estrellas'] ? 'estrella-llena' : 'estrella-vacia' ?>">★</span>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <span class="resena-fecha">
                                        <?= date('d/m/Y', strtotime($r['fecha'])) ?>
                                    </span>
                                    <?php if (isset($_SESSION['usuario_publico_id']) && intval($_SESSION['usuario_publico_id']) === intval($r['id_usuario_publico'])): ?>
                                        <button type="button" class="btn-editar-resena" data-id="<?= intval($r['id_resena']) ?>"
                                            data-estrellas="<?= intval($r['estrellas']) ?>">Editar</button>
                                    <?php endif; ?>
                                </div>
                                <p class="resena-comentario">
                                    <?= nl2br(htmlspecialchars($r['comentario'])) ?>
                                </p>
                                <div class="resena-votos">
                                    <?php if (isset($_SESSION['usuario_publico_id']) && intval($_SESSION['usuario_publico_id']) !== intval($r['id_usuario_publico'])): ?>
                                        <button type="button"
                                            class="btn-resena-voto btn-voto-like <?= $r['my_vote'] === 'like' ? 'activo' : '' ?>"
                                            data-resena-id="<?= intval($r['id_resena']) ?>" data-tipo="like">
                                            👍 <span class="votos-like-count">
                                                <?= intval($r['likes']) ?>
                                            </span>
                                        </button>
                                        <button type="button"
                                            class="btn-resena-voto btn-voto-dislike <?= $r['my_vote'] === 'dislike' ? 'activo' : '' ?>"
                                            data-resena-id="<?= intval($r['id_resena']) ?>" data-tipo="dislike">
                                            👎 <span class="votos-dislike-count">
                                                <?= intval($r['dislikes']) ?>
                                            </span>
                                        </button>
                                    <?php elseif (isset($_SESSION['usuario_publico_id'])): ?>
                                        <span class="votos-summary">👍
                                            <?= intval($r['likes']) ?> · 👎
                                            <?= intval($r['dislikes']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
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
            $stmt_count_sim = $conexion->prepare("SELECT COUNT(*) as total FROM empresas WHERE id_categoria = ? AND id_empresa != ?");
            $stmt_count_sim->bind_param("ii", $id_cat_actual, $id_emp_actual);
            $stmt_count_sim->execute();
            $total_sim_res = $stmt_count_sim->get_result();
            $total_sim = $total_sim_res ? ($total_sim_res->fetch_assoc()['total'] ?? 0) : 0;
            $stmt_count_sim->close();

            if ($total_sim > 0):
                $offset_sim = rand(0, max(0, $total_sim - 3));

                $stmt_sim = $conexion->prepare("SELECT e.*, c.nombre AS categoria FROM empresas e JOIN categorias c ON e.id_categoria = c.id_categoria WHERE e.id_categoria = ? AND e.id_empresa != ? ORDER BY e.id_empresa LIMIT 3 OFFSET ?");
                $stmt_sim->bind_param("iii", $id_cat_actual, $id_emp_actual, $offset_sim);
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

                            $stmt_sim = $conexion->prepare("SELECT e.*, c.nombre AS categoria, GROUP_CONCAT(g.foto ORDER BY g.orden ASC SEPARATOR ',') as fotos_galeria FROM empresas e JOIN categorias c ON e.id_categoria = c.id_categoria LEFT JOIN empresa_galeria g ON e.id_empresa = g.id_empresa WHERE e.id_categoria = ? AND e.id_empresa != ? GROUP BY e.id_empresa ORDER BY e.id_empresa LIMIT 3 OFFSET ?");
                            $stmt_sim->bind_param("iii", $id_cat_actual, $id_emp_actual, $offset_sim);
                            $stmt_sim->execute();
                            $res_sim = $stmt_sim->get_result();
                            while ($f_sim = $res_sim->fetch_assoc()):
                                $fotos_sim = !empty($f_sim['fotos_galeria']) ? explode(',', $f_sim['fotos_galeria']) : [];
                                renderEmpresaCard($f_sim, $fotos_sim);
                            endwhile; ?>
                        </div>
                    </div>
                <?php endif;
            endif;
        }
        ?>
        </div>
        <?php
        elseif ($resultado && $resultado->num_rows > 0):
            $bc_items = [];
            if ($buscar) {
                $bc_items[] = ["name" => "Búsqueda", "url" => null];
            } elseif ($id_categoria) {
                $bc_items[] = ["name" => "Categorías", "url" => APP_URL . "/categorias"];
                $bc_items[] = ["name" => $cat_nombre ?? 'Categoría', "url" => null];
            } else {
                $bc_items[] = ["name" => "Empresas", "url" => null];
            }
            echo renderBreadcrumbs($bc_items);
            ?>

        <div class="filtros-acciones-top"
            style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 20px;">
            <div class="filtros-activos-wrap" style="display: flex; gap: 10px; flex-wrap: wrap;">
                <?php if ($buscar): ?>
                    <div class="filtro-activo">
                        🔍 Resultados para: "
                        <?= htmlspecialchars($buscar) ?>"
                        <a href="<?= APP_URL ?>/empresas" title="Limpiar">✕</a>
                    </div>
                <?php elseif ($id_categoria): ?>
                    <div class="filtro-activo">
                        🏷 Categoría:
                        <?= htmlspecialchars($cat_nombre ?? 'Categoría') ?>
                        <a href="<?= APP_URL ?>/empresas" title="Ver todas">✕</a>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!$id_empresa && ($resultado && $resultado->num_rows > 0)): ?>
                <div class="selector-orden" style="display: flex; align-items: center; gap: 10px;">
                    <label for="orden_select" style="font-size: 14px; font-weight: 600; color: var(--muted); margin: 0;">Ordenar
                        por:</label>
                    <select id="orden_select" onchange="window.location.href=this.value"
                        style="padding: 8px 15px; border-radius: 8px; border: 1px solid var(--borde); background: white; font-family: inherit; font-size: 14px; color: var(--ink); outline: none; cursor: pointer;">
                        <?php
                        $base_url = APP_URL . "/empresas?";
                        $query_params = $_GET;
                        unset($query_params['pagina']);

                        $query_params['orden'] = 'populares';
                        $url_pop = $base_url . http_build_query($query_params);
                        $query_params['orden'] = 'valoradas';
                        $url_val = $base_url . http_build_query($query_params);
                        $query_params['orden'] = 'recientes';
                        $url_rec = $base_url . http_build_query($query_params);
                        ?>
                        <option value="<?= htmlspecialchars($url_pop) ?>" <?= $orden_actual === 'populares' ? 'selected' : '' ?>>🔥
                            Más populares</option>
                        <option value="<?= htmlspecialchars($url_val) ?>" <?= $orden_actual === 'valoradas' ? 'selected' : '' ?>>⭐
                            Mejor valoradas</option>
                        <option value="<?= htmlspecialchars($url_rec) ?>" <?= $orden_actual === 'recientes' ? 'selected' : '' ?>>🆕
                            Más recientes</option>
                    </select>
                </div>
            <?php endif; ?>
        </div>

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
            if (!empty($orden_actual) && $orden_actual !== 'populares')
                $params_url['orden'] = $orden_actual;

            $query_str = http_build_query($params_url);
            if (!empty($query_str))
                $query_str = '&' . $query_str;
            ?>

            <div class="paginacion"
                style="display: flex; justify-content: center; flex-wrap: wrap; gap: 8px; margin-top: 40px; margin-bottom: 20px;">
                <?php if ($pagina_actual > 1): ?>
                    <a href="<?= APP_URL ?>/empresas?pagina=<?= $pagina_actual - 1 ?><?= $query_str ?>" class="btn-pag">Anterior</a>
                <?php endif; ?>

                <?php
                for ($p = 1; $p <= $total_paginas; $p++):
                    if ($p == 1 || $p == $total_paginas || abs($p - $pagina_actual) <= 2):
                        ?>
                        <a href="<?= APP_URL ?>/empresas?pagina=<?= $p ?><?= $query_str ?>"
                            class="btn-pag <?= $p == $pagina_actual ? 'activa' : '' ?>">
                            <?= $p ?>
                        </a>
                    <?php elseif (abs($p - $pagina_actual) == 3): ?>
                        <span style="display:flex; align-items:flex-end; color: #94a3b8;">...</span>
                    <?php endif; endfor; ?>

                <?php if ($pagina_actual < $total_paginas): ?>
                    <a href="<?= APP_URL ?>/empresas?pagina=<?= $pagina_actual + 1 ?><?= $query_str ?>"
                        class="btn-pag">Siguiente</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="no-results">
            <p>😕 No se encontraron
                empresas
                <?= $buscar ? ' para "<strong>' . htmlspecialchars($buscar) . '</strong>"' : '' ?>.
            </p>
            <br><br>
            <a href="<?= APP_URL ?>/empresas">Ver todas las empresas</a>
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
        const resenaEditHint = document.getElementById('resenaEditHint');
        const csrfToken = '<?= generarTokenCSRF() ?>';
        let usuarioTieneResena = <?= isset($usuario_tiene_resena) && $usuario_tiene_resena ? 'true' : 'false' ?>;
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
        const idResenaInput = document.getElementById('idResena');
        const cancelEditBtn = document.getElementById('btnCancelarEdicion');
        const resenaFormTitulo = document.getElementById('resenaFormTitulo');
        const resenaComentario = document.getElementById('resenaComentario');
        const charCount = document.getElementById('charCount');

        if (resenaComentario && charCount) {
            resenaComentario.addEventListener('input', () => {
                const remaining = 1000 - resenaComentario.value.length;
                charCount.textContent = `${remaining} caracteres restantes`;
                charCount.style.color = remaining < 50 ? '#ef4444' : 'var(--muted)';
            });
        }

        function restablecerEstrellas(valor) {
            starsValue.value = valor;
            btns.forEach((b, i) => b.classList.toggle('activa', i < valor));
        }

        function mostrarFormulario() {
            const form = document.getElementById('formResena');
            if (!form) return;
            form.style.display = 'block';
            if (resenaEditHint) {
                resenaEditHint.style.display = 'none';
            }
        }

        function resetEditMode() {
            if (!form) return;
            idResenaInput.value = '';
            if (cancelEditBtn) cancelEditBtn.style.display = 'none';
            form.reset();
            restablecerEstrellas(0);
            btns.forEach(b => b.classList.remove('activa'));
            if (charCount) charCount.textContent = '1000 caracteres restantes';
            document.getElementById('btnEnviarResena').innerText = 'Enviar reseña';
            if (resenaFormTitulo) {
                resenaFormTitulo.innerText = resenaFormTitulo.dataset.defaultText || 'Enviar reseña';
            }
            if (usuarioTieneResena && form) {
                form.style.display = 'none';
                if (resenaEditHint) {
                    resenaEditHint.style.display = 'block';
                }
            }
        }

        function activarModoEdicion(resenaId, estrellas, comentario) {
            mostrarFormulario();
            idResenaInput.value = resenaId;
            restablecerEstrellas(parseInt(estrellas, 10) || 0);
            document.getElementById('resenaComentario').value = comentario;
            if (cancelEditBtn) cancelEditBtn.style.display = 'inline-flex';
            document.getElementById('btnEnviarResena').innerText = 'Actualizar reseña';
            if (resenaFormTitulo) {
                resenaFormTitulo.innerText = 'Edita tu reseña';
            }
            document.getElementById('resenaComentario').focus();
            if (charCount) {
                const remaining = 1000 - comentario.length;
                charCount.textContent = `${remaining} caracteres restantes`;
            }
        }

        if (!form) return;

        document.addEventListener('click', function (event) {
            const button = event.target.closest('.btn-editar-resena');
            if (!button) return;
            const id = button.dataset.id;
            const estrellas = button.dataset.estrellas;
            const item = button.closest('.resena-item');
            const comentario = item ? item.querySelector('.resena-comentario').textContent.trim() : '';
            activarModoEdicion(id, estrellas, comentario);
        });

        document.addEventListener('click', function (event) {
            const voteButton = event.target.closest('.btn-resena-voto');
            if (!voteButton) return;
            const resenaId = voteButton.dataset.resenaId;
            const tipo = voteButton.dataset.tipo;
            const alertBox = document.getElementById('resenaAlertContainer');
            const body = new URLSearchParams();
            body.append('csrf_token', csrfToken);
            body.append('id_resena', resenaId);
            body.append('tipo', tipo);

            fetch('<?= APP_URL ?>/ajax/resena_vote.php', {
                method: 'POST',
                body
            })
                .then(r => r.json())
                .then(data => {
                    if (!data.success) {
                        alertBox.innerHTML = `<div class="resena-alerta resena-alerta-error">❌ ${data.error}</div>`;
                        return;
                    }
                    const item = document.querySelector(`.resena-item[data-resena-id="${resenaId}"]`);
                    if (!item) return;
                    const likeCount = item.querySelector('.votos-like-count');
                    const dislikeCount = item.querySelector('.votos-dislike-count');
                    const likeBtn = item.querySelector('.btn-voto-like');
                    const dislikeBtn = item.querySelector('.btn-voto-dislike');
                    if (likeCount) likeCount.textContent = data.likes;
                    if (dislikeCount) dislikeCount.textContent = data.dislikes;
                    if (likeBtn) likeBtn.classList.toggle('activo', data.my_vote === 'like');
                    if (dislikeBtn) dislikeBtn.classList.toggle('activo', data.my_vote === 'dislike');
                })
                .catch(() => {
                    alertBox.innerHTML = '<div class="resena-alerta resena-alerta-error">❌ Error al conectar con el servidor.</div>';
                });
        });

        if (cancelEditBtn) {
            cancelEditBtn.addEventListener('click', resetEditMode);
        }

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
                        alertBox.innerHTML = `<div class="resena-alerta resena-alerta-ok">✅ ${data.updated ? 'Tu reseña se ha actualizado con éxito!' : '¡Tu reseña se ha publicado con éxito!'}</div>`;
                        form.reset();
                        idResenaInput.value = '';
                        cancelEditBtn.style.display = 'none';
                        starsValue.value = '';
                        btns.forEach(b => b.classList.remove('activa'));
                        if (charCount) charCount.textContent = '1000 caracteres restantes';

                        if (!data.updated) {
                            usuarioTieneResena = true;
                        }

                        if (usuarioTieneResena && form) {
                            form.style.display = 'none';
                            if (resenaEditHint) {
                                resenaEditHint.style.display = 'block';
                            }
                        }

                        const lista = document.querySelector('.resenas-lista');
                        const emptyState = document.querySelector('.empty-reviews-premium');
                        const existingItem = document.querySelector(`.resena-item[data-resena-id="${data.id_resena}"]`);

                        if (existingItem) {
                            const starsSmall = existingItem.querySelector('.estrellas-display.small');
                            if (starsSmall) {
                                starsSmall.innerHTML = `${'★'.repeat(data.estrellas)}${'☆'.repeat(5 - data.estrellas)}`;
                            }
                            const comentarioText = existingItem.querySelector('.resena-comentario');
                            if (comentarioText) {
                                comentarioText.innerHTML = data.comentario;
                            }
                            const editarBtn = existingItem.querySelector('.btn-editar-resena');
                            if (editarBtn) {
                                editarBtn.dataset.estrellas = data.estrellas;
                            }
                        } else {
                            if (emptyState) emptyState.remove();

                            const item = document.createElement('div');
                            item.className = 'resena-item nuevo';
                            item.setAttribute('data-resena-id', data.id_resena);
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
                            <button type="button" class="btn-editar-resena" data-id="${data.id_resena}" data-estrellas="${data.estrellas}">Editar</button>
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
                        }
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