<?php
session_start();
include 'db.php';
include 'includes/security.php';
include_once 'includes/components/empresa_card.php';

if (!isset($_SESSION['usuario_publico_id'])) {
  header('Location: login_usuario');
  exit;
}

$id_u = intval($_SESSION['usuario_publico_id']);
$stmt_u = $conexion->prepare("SELECT * FROM usuarios_publicos WHERE id = ?");
$stmt_u->bind_param("i", $id_u);
$stmt_u->execute();
$u = $stmt_u->get_result()->fetch_assoc();
$error = '';
$exito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
  if (!validarCSRF()) {
    logSeguridad('csrf_invalido', 'Intento de POST en mi_cuenta sin token');
    $error = 'Error de seguridad. Intente nuevamente.';
  } else {
    if ($_POST['accion'] === 'borrar_cuenta') {
      $stmt_del_r = $conexion->prepare("DELETE FROM resenas WHERE id_usuario_publico = ?");
      $stmt_del_r->bind_param("i", $id_u);
      $stmt_del_r->execute();

      $stmt_del_f = $conexion->prepare("DELETE FROM favoritos WHERE id_usuario_publico = ?");
      $stmt_del_f->bind_param("i", $id_u);
      $stmt_del_f->execute();

      $stmt_del_v = $conexion->prepare("DELETE FROM resena_votos WHERE id_usuario_publico = ?");
      $stmt_del_v->bind_param("i", $id_u);
      $stmt_del_v->execute();

      if (!empty($u['foto_perfil']) && file_exists('assets/img/avatars/' . $u['foto_perfil'])) {
        unlink('assets/img/avatars/' . $u['foto_perfil']);
      }

      $stmt_del_u = $conexion->prepare("DELETE FROM usuarios_publicos WHERE id = ?");
      $stmt_del_u->bind_param("i", $id_u);
      $stmt_del_u->execute();

      session_destroy();
      header('Location: index');
      exit;
    }

    if ($_POST['accion'] === 'borrar_foto') {
      if (!empty($u['foto_perfil']) && file_exists('assets/img/avatars/' . $u['foto_perfil'])) {
        unlink('assets/img/avatars/' . $u['foto_perfil']);
      }
      $stmt_bf = $conexion->prepare("UPDATE usuarios_publicos SET foto_perfil = NULL WHERE id = ?");
      $stmt_bf->bind_param("i", $id_u);
      $stmt_bf->execute();
      $_SESSION['usuario_publico_foto'] = null;
      $u['foto_perfil'] = null;
      $exito = 'Foto de perfil eliminada.';
    }

    if ($_POST['accion'] === 'datos') {
      $nombre_nuevo = trim($_POST['nombre'] ?? '');
      if ($nombre_nuevo && $nombre_nuevo !== $u['nombre']) {
        $stmt_dn = $conexion->prepare("UPDATE usuarios_publicos SET nombre = ? WHERE id = ?");
        $stmt_dn->bind_param("si", $nombre_nuevo, $id_u);
        $stmt_dn->execute();
        $_SESSION['usuario_publico_nombre'] = $nombre_nuevo;
        $exito = 'Nombre actualizado.';
        $u['nombre'] = $nombre_nuevo;
      }
    }

    if ($_POST['accion'] === 'password') {
      $bypass = isset($_SESSION['login_bypass_pw']) && $_SESSION['login_bypass_pw'] === true;
      $actual = $_POST['actual'] ?? '';
      $nueva = $_POST['nueva'] ?? '';
      $confirm = $_POST['confirm'] ?? '';
      if (!$bypass && !password_verify($actual, $u['password_hash'])) {
        $error = 'La contraseña actual no es correcta.';
      } elseif (strlen($nueva) < 6) {
        $error = 'La nueva contraseña debe tener al menos 6 caracteres.';
      } elseif ($nueva !== $confirm) {
        $error = 'Las contraseñas nuevas no coinciden.';
      } else {
        $hash = password_hash($nueva, PASSWORD_DEFAULT);
        $stmt_pw = $conexion->prepare("UPDATE usuarios_publicos SET password_hash = ? WHERE id = ?");
        $stmt_pw->bind_param("si", $hash, $id_u);
        $stmt_pw->execute();
        $exito = 'Contraseña actualizada.';
        if ($bypass)
          unset($_SESSION['login_bypass_pw']);
      }
    }

    if ($_POST['accion'] === 'foto' && isset($_FILES['foto_perfil'])) {
      $file = $_FILES['foto_perfil'];
      $resultado_subida = subirImagenSegura($file, 'assets/img/avatars', [
        'tamano_max' => 2 * 1024 * 1024,
        'extensiones' => ['jpg', 'jpeg', 'png', 'webp']
      ]);

      if ($resultado_subida['success']) {
        $nombre_archivo = $resultado_subida['nombre'];
        if ($u['foto_perfil'] && file_exists('assets/img/avatars/' . $u['foto_perfil'])) {
          unlink('assets/img/avatars/' . $u['foto_perfil']);
        }
        $stmt_fot = $conexion->prepare("UPDATE usuarios_publicos SET foto_perfil = ? WHERE id = ?");
        $stmt_fot->bind_param("si", $nombre_archivo, $id_u);
        $stmt_fot->execute();
        $_SESSION['usuario_publico_foto'] = $nombre_archivo;
        $u['foto_perfil'] = $nombre_archivo;
        $exito = 'Foto de perfil actualizada correctamente.';
      } else {
        $error = $resultado_subida['error'];
      }
    }

    if ($_POST['accion'] === 'privacidad') {
      $visi = $_POST['visibilidad_resenas'] ?? 'publico';
      if ($visi === 'publico' || $visi === 'anonimo') {
        $stmt_pri = $conexion->prepare("UPDATE usuarios_publicos SET visibilidad_resenas = ? WHERE id = ?");
        $stmt_pri->bind_param("si", $visi, $id_u);
        $stmt_pri->execute();
        $u['visibilidad_resenas'] = $visi;
        $exito = 'Opciones de privacidad guardadas.';
      }
    }
  }
}

$stmt_res = $conexion->prepare(
  "SELECT r.*, e.nombre AS empresa_nombre
     FROM resenas r
     JOIN empresas e ON r.id_empresa = e.id_empresa
     WHERE r.id_usuario_publico = ?
     ORDER BY r.fecha DESC"
);
$stmt_res->bind_param("i", $id_u);
$stmt_res->execute();
$resenas_q = $stmt_res->get_result();
$mis_resenas = [];
if ($resenas_q) {
  while ($r = $resenas_q->fetch_assoc()) {
    $mis_resenas[] = $r;
  }
}

// Cargar favoritos
$stmt_favs_q = $conexion->prepare(
  "SELECT e.*, c.nombre AS categoria, 
          GROUP_CONCAT(g.foto ORDER BY g.orden ASC, g.id_foto ASC SEPARATOR ',') as fotos_galeria
   FROM favoritos f
   JOIN empresas e ON f.id_empresa = e.id_empresa
   JOIN categorias c ON e.id_categoria = c.id_categoria
   LEFT JOIN empresa_galeria g ON e.id_empresa = g.id_empresa
   WHERE f.id_usuario_publico = ?
   GROUP BY e.id_empresa
   ORDER BY f.fecha_agregado DESC"
);
$stmt_favs_q->bind_param("i", $id_u);
$stmt_favs_q->execute();
$res_favs = $stmt_favs_q->get_result();
$mis_favoritos = [];
while ($f = $res_favs->fetch_assoc()) {
  $mis_favoritos[] = $f;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_eliminar_resena'])) {
  header('Content-Type: application/json');
  if (!validarCSRF()) {
    echo json_encode(['success' => false, 'error' => 'CSRF Inválido']);
    exit;
  }
  $id_r = intval($_POST['ajax_eliminar_resena']);
  $stmt_del_votes = $conexion->prepare("DELETE FROM resena_votos WHERE id_resena = ?");
  $stmt_del_votes->bind_param("i", $id_r);
  $stmt_del_votes->execute();
  $stmt_del = $conexion->prepare("DELETE FROM resenas WHERE id_resena = ? AND id_usuario_publico = ?");
  $stmt_del->bind_param("ii", $id_r, $id_u);
  $res = $stmt_del->execute();
  echo json_encode(['success' => (bool) $res]);
  exit;
}

$panel_activo = $_GET['tab'] ?? 'perfil';
?>
<?php
$seo_title = "Mi Cuenta - Guía Empresarial";
$seo_robots = "noindex, nofollow";
include 'includes/header.php';
?>

<link rel="stylesheet" href="assets/css/mi_cuenta.css">

<div class="mc-page">

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      <?php if ($error): ?>
        if (typeof showToast === 'function') showToast('<?= addslashes($error) ?>', 'error');
      <?php endif; ?>
      <?php if ($exito): ?>
        if (typeof showToast === 'function') showToast('<?= addslashes($exito) ?>', 'success');
      <?php endif; ?>
    });
  </script>

  <header class="mc-header">
    <div class="mc-header-avatar">
      <?php if (!empty($u['foto_perfil'])): ?>
        <img src="assets/img/avatars/<?= htmlspecialchars($u['foto_perfil']) ?>" alt="Avatar">
      <?php else: ?>
        <span><?= mb_strtoupper(mb_substr($u['nombre'], 0, 1)) ?></span>
      <?php endif; ?>
      <div class="mc-avatar-aura"></div>
    </div>
    <div class="mc-header-text">
      <div class="mc-header-title">
        <?= htmlspecialchars($u['nombre']) ?>
        <span class="mc-sep">/</span>
        <span class="mc-section-label" id="mc-header-section">Editar perfil</span>
      </div>
      <div class="mc-header-sub" id="mc-header-sub">Configure su presencia y datos de cuenta</div>
    </div>
    <a href="logout_usuario" class="mc-logout">Cerrar sesión</a>
  </header>

  <div class="mc-mobile-nav">
    <select class="mc-mobile-select" onchange="mcSwitch(this.value)">
      <option value="perfil">Editar perfil</option>
      <option value="password">Contraseña</option>
      <option value="resenas">Mis reseñas</option>
      <option value="favoritos">Favoritos</option>
      <option value="privacidad">Privacidad y seguridad</option>
    </select>
  </div>

  <div class="mc-layout">

    <aside class="mc-sidebar">
      <nav>
        <a class="mc-nav-link active" onclick="mcSwitch('perfil')" id="mcnav-perfil">Editar perfil</a>
        <a class="mc-nav-link" onclick="mcSwitch('password')" id="mcnav-password">Contraseña</a>
        <a class="mc-nav-link" onclick="mcSwitch('resenas')" id="mcnav-resenas">
          Mis reseñas
          <span class="mc-badge" id="badge-resenas"><?= count($mis_resenas) ?></span>
        </a>
        <a class="mc-nav-link" onclick="mcSwitch('favoritos')" id="mcnav-favoritos">
          Favoritos
          <span class="mc-badge" id="badge-favoritos"><?= count($mis_favoritos) ?></span>
        </a>
        <a class="mc-nav-link" onclick="mcSwitch('privacidad')" id="mcnav-privacidad">Privacidad y seguridad</a>
      </nav>
    </aside>

    <main class="mc-content">

      <div class="mc-panel active" id="mc-panel-perfil">

        <div class="mc-avatar-row">
          <div class="mc-avatar-preview" id="mc-avatar-preview">
            <?php if (!empty($u['foto_perfil'])): ?>
              <img src="assets/img/avatars/<?= htmlspecialchars($u['foto_perfil']) ?>" alt="Avatar">
            <?php else: ?>
              <span><?= mb_strtoupper(mb_substr($u['nombre'], 0, 1)) ?></span>
            <?php endif; ?>
          </div>
          <div class="mc-avatar-actions">
            <form method="POST" enctype="multipart/form-data" id="form-foto">
              <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
              <input type="hidden" name="accion" value="foto">
              <label class="mc-btn-upload" for="foto_input">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                  <polyline points="17 8 12 3 7 8" />
                  <line x1="12" y1="3" x2="12" y2="15" />
                </svg>
                Subir foto
              </label>
              <input type="file" id="foto_input" name="foto_perfil" accept="image/*" style="display:none"
                onchange="previewFoto(event); this.form.submit();">
            </form>
            <?php if (!empty($u['foto_perfil'])): ?>
              <form method="POST" style="display:inline">
                <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
                <input type="hidden" name="accion" value="borrar_foto">
                <button type="submit" class="mc-btn-ghost mc-btn-sm">Eliminar foto</button>
              </form>
            <?php endif; ?>
            <span class="mc-upload-hint">JPG, PNG o WEBP · máx. 2MB</span>
          </div>
        </div>

        <form method="POST" class="mc-form">
          <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
          <input type="hidden" name="accion" value="datos">
          <div class="mc-field">
            <label>Nombre <span class="mc-req">*</span></label>
            <input type="text" name="nombre" value="<?= htmlspecialchars($u['nombre']) ?>" required maxlength="100">
          </div>
          <div class="mc-field">
            <label>Correo electrónico</label>
            <input type="text" value="<?= htmlspecialchars($u['email']) ?>" disabled class="mc-disabled">
            <span class="mc-hint">El correo no se puede cambiar desde aquí.</span>
          </div>
          <div class="mc-form-footer">
            <button type="submit" class="mc-btn-primary">Guardar cambios</button>
          </div>
        </form>
      </div>

      <div class="mc-panel" id="mc-panel-password">
        <?php $bypass_active = isset($_SESSION['login_bypass_pw']) && $_SESSION['login_bypass_pw'] === true; ?>
        <?php if ($bypass_active): ?>
          <div class="mc-alerta mc-alerta-ok" style="margin-bottom: 20px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="20 6 9 17 4 12" />
            </svg>
            Has ingresado con un código de recuperación. Puedes crear una nueva contraseña ahora mismo sin conocer la
            anterior.
          </div>
        <?php endif; ?>
        <form method="POST" class="mc-form">
          <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
          <input type="hidden" name="accion" value="password">
          <?php if (!$bypass_active): ?>
            <div class="mc-field">
              <label>Contraseña actual</label>
              <div class="pw-input-wrap">
                <input type="password" name="actual" id="pw-actual" placeholder="••••••••" required>
                <button type="button" class="pw-toggle" onclick="togglePw('pw-actual','pw-actual-icon')">
                  <i class="bi bi-eye" id="pw-actual-icon"></i>
                </button>
              </div>
            </div>
          <?php else: ?>
            <input type="hidden" name="actual" value="bypass">
          <?php endif; ?>
          <div class="mc-field">
            <label>Nueva contraseña</label>
            <div class="pw-input-wrap">
              <input type="password" name="nueva" id="pw-nueva" placeholder="••••••••" required>
              <button type="button" class="pw-toggle" onclick="togglePw('pw-nueva','pw-nueva-icon')">
                <i class="bi bi-eye" id="pw-nueva-icon"></i>
              </button>
            </div>
            <span class="mc-hint">Mínimo 6 caracteres.</span>
          </div>
          <div class="mc-field">
            <label>Confirmar nueva contraseña</label>
            <div class="pw-input-wrap">
              <input type="password" name="confirm" id="pw-confirm" placeholder="••••••••" required>
              <button type="button" class="pw-toggle" onclick="togglePw('pw-confirm','pw-confirm-icon')">
                <i class="bi bi-eye" id="pw-confirm-icon"></i>
              </button>
            </div>
          </div>
          <div class="mc-form-footer">
            <button type="submit" class="mc-btn-dark">Cambiar contraseña</button>
          </div>
        </form>
      </div>

      <div class="mc-panel" id="mc-panel-resenas">
        <?php if (empty($mis_resenas)): ?>
          <div class="mc-empty">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
            </svg>
            <p>Aún no has dejado ninguna reseña.</p>
          </div>
        <?php else: ?>
          <div class="mc-resenas-lista">
            <?php foreach ($mis_resenas as $r): ?>
              <div class="mc-resena">
                <div class="mc-resena-top">
                  <div class="mc-resena-avatar">
                    <?php if (!empty($u['foto_perfil'])): ?>
                      <img src="assets/img/avatars/<?= htmlspecialchars($u['foto_perfil']) ?>" alt="">
                    <?php else: ?>
                      <span><?= mb_strtoupper(mb_substr($u['nombre'], 0, 1)) ?></span>
                    <?php endif; ?>
                  </div>
                  <div class="mc-resena-meta">
                    <strong><?= htmlspecialchars($r['empresa_nombre']) ?></strong>
                    <div class="mc-estrellas">
                      <?php for ($i = 1; $i <= 5; $i++): ?>
                        <span class="<?= $i <= $r['estrellas'] ? 'mc-star-on' : 'mc-star-off' ?>">★</span>
                      <?php endfor; ?>
                    </div>
                  </div>
                  <span class="mc-resena-fecha"><?= date('d/m/Y', strtotime($r['fecha'])) ?></span>
                </div>
                <p class="mc-resena-texto"><?= nl2br(htmlspecialchars($r['comentario'])) ?></p>
                <div class="mc-resena-acciones">
                  <a href="empresas?empresa=<?= $r['id_empresa'] ?>" class="mc-btn-ghost mc-btn-sm">Ver empresa</a>
                  <button type="button" class="mc-btn-ghost mc-btn-sm mc-btn-danger mc-btn-eliminar"
                    onclick="eliminarResenaAjax(this, <?= $r['id_resena'] ?>)">Eliminar</button>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="mc-panel" id="mc-panel-favoritos">
        <?php if (empty($mis_favoritos)): ?>
          <div class="mc-empty">
            <i class="bi bi-heart" style="font-size: 40px; margin-bottom: 10px;"></i>
            <p>No tienes empresas en favoritos todavía.</p>
            <a href="empresas" class="mc-btn-primary mc-btn-sm" style="margin-top:10px; text-decoration:none;">Explorar
              empresas</a>
          </div>
        <?php else: ?>
          <div class="empresas-list" style="grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
            <?php
            foreach ($mis_favoritos as $fila):
              $fotos_arr = !empty($fila['fotos_galeria']) ? explode(',', $fila['fotos_galeria']) : [];
              renderFavoritoCard($fila, $fotos_arr);
            endforeach;
            ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="mc-panel" id="mc-panel-privacidad">

        <p class="mc-section-title">Privacidad de la actividad</p>

        <form method="POST" class="mc-form">
          <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
          <input type="hidden" name="accion" value="privacidad">

          <div class="mc-field">
            <label>Visibilidad de reseñas</label>
            <select name="visibilidad_resenas" class="mc-mobile-select"
              style="background-image: none; padding-right: 14px;">
              <option value="publico" <?= ($u['visibilidad_resenas'] ?? 'publico') === 'publico' ? 'selected' : '' ?>>
                Público (Todos pueden ver mis reseñas)</option>
              <option value="anonimo" <?= ($u['visibilidad_resenas'] ?? '') === 'anonimo' ? 'selected' : '' ?>>Anónimo
                (Ocultar mi nombre en la reseña)</option>
            </select>
            <span class="mc-hint">Elige cómo se mostrarán tus reseñas a los demás usuarios.</span>
          </div>

          <div class="mc-form-footer" style="margin-top: 10px; margin-bottom: 24px;">
            <button type="submit" class="mc-btn-primary mc-btn-sm">Guardar preferencias</button>
          </div>
        </form>

        <div class="mc-danger-zone">
          <h3>Eliminar cuenta</h3>
          <p>Eliminar tu cuenta eliminará permanentemente tu perfil y todo el contenido asociado. Esta acción no se
            puede revertir.</p>
          <form method="POST" id="form-borrar-cuenta">
            <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
            <input type="hidden" name="accion" value="borrar_cuenta">
            <button type="button" class="mc-btn-delete" onclick="confirmarBorrarCuenta()">Eliminar cuenta</button>
          </form>
        </div>

      </div>

    </main>
  </div>
</div>

<script>
  function confirmarBorrarCuenta() {
    if (typeof window.customConfirm === 'function') {
      window.customConfirm('¿Seguro que deseas borrar tu cuenta? Esta acción no se puede deshacer y perderás todas tus reseñas y favoritos.', () => {
        document.getElementById('form-borrar-cuenta').submit();
      });
    } else if (confirm('¿Seguro que deseas borrar tu cuenta? Esta acción no se puede deshacer.')) {
      document.getElementById('form-borrar-cuenta').submit();
    }
  }
  const mcPanels = {
    perfil: { label: 'Editar perfil', sub: 'Configure su presencia y datos de cuenta' },
    password: { label: 'Contraseña', sub: 'Administre su contraseña de acceso' },
    resenas: { label: 'Mis reseñas', sub: 'Gestiona las reseñas que has publicado' },
    favoritos: { label: 'Favoritos', sub: 'Tus negocios y servicios guardados' },
    privacidad: { label: 'Privacidad y seguridad', sub: 'Administre su configuración de privacidad' },
  };

  function mcSwitch(id, updateHash = true) {
    const panel = document.getElementById('mc-panel-' + id);
    const nav = document.getElementById('mcnav-' + id);
    if (!panel || !nav) return;

    document.querySelectorAll('.mc-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.mc-nav-link').forEach(a => a.classList.remove('active'));

    panel.classList.add('active');
    nav.classList.add('active');

    if (mcPanels[id]) {
      document.getElementById('mc-header-section').textContent = mcPanels[id].label;
      document.getElementById('mc-header-sub').textContent = mcPanels[id].sub;
    }

    document.querySelector('.mc-mobile-select').value = id;

    if (updateHash) {
      history.pushState(null, null, '#' + id);
    }
  }

  // Manejar Hash inicial y cambios
  window.addEventListener('load', () => {
    const hash = window.location.hash.replace('#', '');
    if (hash && mcPanels[hash]) {
      mcSwitch(hash, false);
    }
  });

  window.addEventListener('hashchange', () => {
    const hash = window.location.hash.replace('#', '');
    if (hash && mcPanels[hash]) {
      mcSwitch(hash, false);
    }
  });

  function togglePw(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (input.type === 'password') {
      input.type = 'text';
      icon.className = 'bi bi-eye-slash';
    } else {
      input.type = 'password';
      icon.className = 'bi bi-eye';
    }
  }

  function previewFoto(e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = ev => {
      const preview = document.getElementById('mc-avatar-preview');
      preview.innerHTML = `<img src="${ev.target.result}" alt="Avatar">`;
      const headerAvatar = document.querySelector('.mc-header-avatar');
      headerAvatar.innerHTML = `<img src="${ev.target.result}" alt="Avatar">`;
    };
    reader.readAsDataURL(file);
  }

  function eliminarResenaAjax(btn, id_resena) {
    if (!confirm('¿Seguro que deseas eliminar esta reseña permanentemente?')) return;

    const resenaDiv = btn.closest('.mc-resena');
    btn.innerText = 'Eliminando...';
    btn.disabled = true;
    btn.style.opacity = '0.7';
    btn.style.cursor = 'not-allowed';

    fetch('mi_cuenta', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'ajax_eliminar_resena=' + id_resena + '&csrf_token=' + window.csrfToken
    })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          resenaDiv.style.transition = 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
          resenaDiv.style.opacity = '0';
          resenaDiv.style.transform = 'scale(0.9) translateY(-10px)';

          setTimeout(() => {
            const contenedor = resenaDiv.parentElement;
            resenaDiv.remove();

            const badge = document.getElementById('badge-resenas');
            if (badge) {
              let count = parseInt(badge.textContent);
              if (count > 0) badge.textContent = count - 1;
            }

            if (contenedor.children.length === 0) {
              contenedor.innerHTML = `
              <div class="mc-empty" style="animation: fadeIn 0.5s ease-in;">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                </svg>
                <p>Aún no has dejado ninguna reseña.</p>
              </div>
            `;
            }
          }, 400);
        } else {
          alert('Hubo un error al eliminar la reseña.');
          resetBtn();
        }
      })
      .catch(err => {
        console.error(err);
        alert('Error de conexión con el servidor.');
        resetBtn();
      });

    function resetBtn() {
      btn.innerText = 'Eliminar';
      btn.disabled = false;
      btn.style.opacity = '1';
      btn.style.cursor = 'pointer';
    }
  }
</script>

<?php include 'includes/footer.php'; ?>