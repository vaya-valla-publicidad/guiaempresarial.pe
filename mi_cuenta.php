<?php
require_once 'db.php';
require_once 'includes/security.php';
require_once 'libs/mailer.php';
require_once 'includes/components/empresa_card.php';

if (!isset($_SESSION['usuario_publico_id'])) {
  header('Location: login_usuario');
  exit;
}

$id_u = intval($_SESSION['usuario_publico_id']);
$stmt_u = $conexion->prepare("SELECT * FROM usuarios_publicos WHERE id = ?");
$stmt_u->bind_param("i", $id_u);
$stmt_u->execute();
$u = $stmt_u->get_result()->fetch_assoc();

$error = $_SESSION['error'] ?? '';
$exito = $_SESSION['exito'] ?? '';
unset($_SESSION['error'], $_SESSION['exito']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
  if (!validarCSRF()) {
    logSeguridad('csrf_invalido', 'Intento de POST en mi_cuenta sin token');
    $error = 'Error de seguridad. Intente nuevamente.';
  } else {
    switch ($_POST['accion']) {
      case 'enviar_codigo_pw':
        if (!verificarRateLimit('envio_otp_pw', 10, 600)) {
          $error = 'Demasiados intentos. Espera unos minutos.';
        } else {
          $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
          $expira = time() + 600;
          $stmt_upd = $conexion->prepare("UPDATE usuarios_publicos SET codigo_verificacion=?, codigo_expira=? WHERE id=?");
          $stmt_upd->bind_param("sii", $codigo, $expira, $id_u);
          if ($stmt_upd->execute()) {
            $cuerpo = plantillaCorreoOTP($u['nombre'], $codigo, 'password');
            if (enviarCorreo($u['email'], $u['nombre'], 'Código de Seguridad - Cambio de Contraseña', $cuerpo)) {
              $exito = 'Código enviado a su correo electrónico.';
              $_SESSION['esperando_otp_pw'] = true;
            } else {
              $error = 'No se pudo enviar el correo. Intente más tarde.';
            }
          }
        }
        break;

      case 'verificar_codigo_pw':
        $codigo = trim($_POST['codigo_otp'] ?? '');
        $stmt_check = $conexion->prepare("SELECT * FROM usuarios_publicos WHERE id = ?");
        $stmt_check->bind_param("i", $id_u);
        $stmt_check->execute();
        $res_u = $stmt_check->get_result()->fetch_assoc();

        if ($codigo === $res_u['codigo_verificacion'] && time() < $res_u['codigo_expira']) {
          $stmt_clear = $conexion->prepare("UPDATE usuarios_publicos SET codigo_verificacion=NULL, codigo_expira=NULL WHERE id=?");
          $stmt_clear->bind_param("i", $id_u);
          $stmt_clear->execute();
          $stmt_clear->close();
          $_SESSION['login_bypass_pw'] = true;
          unset($_SESSION['esperando_otp_pw']);
          $exito = 'Código verificado. Ahora puede establecer su nueva contraseña.';
        } else {
          $error = 'Código incorrecto o expirado.';
        }
        break;

      case 'borrar_cuenta':
        $stmt_v = $conexion->prepare("SELECT password_hash, email, nombre, codigo_verificacion, codigo_expira FROM usuarios_publicos WHERE id = ?");
        $stmt_v->bind_param("i", $id_u);
        $stmt_v->execute();
        $u_v = $stmt_v->get_result()->fetch_assoc();

        $autorizado = false;

        if ($u_v['password_hash']) {
          $pw_confirm = $_POST['password_confirm'] ?? '';
          if (password_verify($pw_confirm, $u_v['password_hash'])) {
            $autorizado = true;
          } else {
            $error = 'La contraseña de confirmación es incorrecta.';
          }
        } else {
          $codigo_input = trim($_POST['codigo_borrar'] ?? '');
          if ($codigo_input && $codigo_input === $u_v['codigo_verificacion'] && time() < $u_v['codigo_expira']) {
            $autorizado = true;
          } else {
            $error = 'Código de verificación incorrecto o expirado.';
          }
        }

        if ($autorizado) {
          $conexion->begin_transaction();
          try {
            $stmt_del_r = $conexion->prepare("DELETE FROM resenas WHERE id_usuario_publico = ?");
            $stmt_del_r->bind_param("i", $id_u);
            $stmt_del_r->execute();
            $stmt_del_r->close();

            $stmt_del_f = $conexion->prepare("DELETE FROM favoritos WHERE id_usuario_publico = ?");
            $stmt_del_f->bind_param("i", $id_u);
            $stmt_del_f->execute();
            $stmt_del_f->close();

            $stmt_del_v = $conexion->prepare("DELETE FROM resena_votos WHERE id_usuario_publico = ?");
            $stmt_del_v->bind_param("i", $id_u);
            $stmt_del_v->execute();
            $stmt_del_v->close();

            $stmt_del_u = $conexion->prepare("DELETE FROM usuarios_publicos WHERE id = ?");
            $stmt_del_u->bind_param("i", $id_u);
            $stmt_del_u->execute();
            $stmt_del_u->close();

            $conexion->commit();

            if (!empty($u['foto_perfil']) && file_exists('assets/img/avatars/' . $u['foto_perfil'])) {
              unlink('assets/img/avatars/' . $u['foto_perfil']);
            }

            session_destroy();
            header('Location: index?msg=cuenta_eliminada');
            exit;
          } catch (Exception $ex) {
            $conexion->rollback();
            error_log("Error al eliminar la cuenta de usuario: " . $ex->getMessage());
            $error = 'No se pudo eliminar la cuenta. Intente nuevamente más tarde.';
          }
        }
        break;

      case 'enviar_codigo_borrar':
        if (!verificarRateLimit('envio_otp_del', 5, 300)) {
          $error = 'Demasiados intentos. Espera unos minutos.';
        } else {
          $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
          $expira = time() + 600;
          $stmt_upd = $conexion->prepare("UPDATE usuarios_publicos SET codigo_verificacion=?, codigo_expira=? WHERE id=?");
          $stmt_upd->bind_param("sii", $codigo, $expira, $id_u);
          if ($stmt_upd->execute()) {
            $cuerpo = plantillaCorreoOTP($u['nombre'], $codigo, 'borrado');
            if (enviarCorreo($u['email'], $u['nombre'], 'Código de Confirmación - Eliminar Cuenta', $cuerpo)) {
              $exito = 'Código de seguridad enviado a su correo.';
              $_SESSION['esperando_otp_del'] = true;
            } else {
              $error = 'No se pudo enviar el correo.';
            }
          }
        }
        break;

      case 'borrar_foto':
        if (!empty($u['foto_perfil']) && file_exists('assets/img/avatars/' . $u['foto_perfil'])) {
          unlink('assets/img/avatars/' . $u['foto_perfil']);
        }
        $stmt_bf = $conexion->prepare("UPDATE usuarios_publicos SET foto_perfil = NULL WHERE id = ?");
        $stmt_bf->bind_param("i", $id_u);
        $stmt_bf->execute();
        $_SESSION['usuario_publico_foto'] = null;
        $u['foto_perfil'] = null;
        $exito = 'Foto de perfil eliminada.';
        break;

      case 'datos':
        $nombre_nuevo = trim($_POST['nombre'] ?? '');
        if ($nombre_nuevo && $nombre_nuevo !== $u['nombre']) {
          $stmt_dn = $conexion->prepare("UPDATE usuarios_publicos SET nombre = ? WHERE id = ?");
          $stmt_dn->bind_param("si", $nombre_nuevo, $id_u);
          $stmt_dn->execute();
          $_SESSION['usuario_publico_nombre'] = $nombre_nuevo;
          $exito = 'Nombre actualizado.';
          $u['nombre'] = $nombre_nuevo;
        }
        break;

      case 'password':
        $bypass = isset($_SESSION['login_bypass_pw']) && $_SESSION['login_bypass_pw'] === true;
        $actual = $_POST['actual'] ?? '';
        $nueva = $_POST['nueva'] ?? '';
        $confirm = $_POST['confirm'] ?? '';
        $confirmar_misma = isset($_POST['confirmar_misma']) && $_POST['confirmar_misma'] === '1';

        if (!$bypass && !password_verify($actual, $u['password_hash'])) {
          $error = 'La contraseña actual no es correcta.';
        } elseif (strlen($nueva) < 8) {
          $error = 'La nueva contraseña debe tener al menos 8 caracteres.';
        } elseif ($nueva !== $confirm) {
          $error = 'Las contraseñas nuevas no coinciden.';
        } elseif (password_verify($nueva, $u['password_hash']) && !$confirmar_misma) {
          $error = 'MISMA_PW';
        } else {
          $hash = password_hash($nueva, PASSWORD_DEFAULT);
          $stmt_pw = $conexion->prepare("UPDATE usuarios_publicos SET password_hash = ? WHERE id = ?");
          $stmt_pw->bind_param("si", $hash, $id_u);
          $stmt_pw->execute();

          session_unset();
          session_destroy();
          header('Location: login_usuario?exito=pw_cambiada');
          exit;
        }
        break;

      case 'foto':
        if (isset($_FILES['foto_perfil'])) {
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
            $_SESSION['exito'] = $exito;
            header("Location: mi_cuenta#perfil");
            exit;
          } else {
            $error = "Error al subir: " . $resultado_subida['error'];
            $_SESSION['error'] = $error;
            header("Location: mi_cuenta#perfil");
            exit;
          }
        }
        break;

      case 'privacidad':
        $visi = $_POST['visibilidad_resenas'] ?? 'publico';
        if ($visi === 'publico' || $visi === 'anonimo') {
          $stmt_pri = $conexion->prepare("UPDATE usuarios_publicos SET visibilidad_resenas = ? WHERE id = ?");
          $stmt_pri->bind_param("si", $visi, $id_u);
          $stmt_pri->execute();
          $u['visibilidad_resenas'] = $visi;
          $exito = 'Opciones de privacidad guardadas.';
        }
        break;
    }
  }
}



$stmt_res = $conexion->prepare(
  "SELECT r.*, e.nombre AS empresa_nombre
     FROM resenas r
     JOIN empresas e ON r.id_empresa = e.id_empresa
     WHERE r.id_usuario_publico = ?
     ORDER BY r.fecha DESC LIMIT 10"
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

$stmt_favs_q = $conexion->prepare(
  "SELECT e.*, c.nombre AS categoria, 
          GROUP_CONCAT(g.foto ORDER BY g.orden ASC, g.id_foto ASC SEPARATOR ',') as fotos_galeria
   FROM favoritos f
   JOIN empresas e ON f.id_empresa = e.id_empresa
   JOIN categorias c ON e.id_categoria = c.id_categoria
   LEFT JOIN empresa_galeria g ON e.id_empresa = g.id_empresa
   WHERE f.id_usuario_publico = ?
   GROUP BY e.id_empresa
   ORDER BY f.fecha_agregado DESC LIMIT 10"
);
$stmt_favs_q->bind_param("i", $id_u);
$stmt_favs_q->execute();
$res_favs = $stmt_favs_q->get_result();
$mis_favoritos = [];
while ($f = $res_favs->fetch_assoc()) {
  $mis_favoritos[] = $f;
}

?>
<?php
$seo_title = "Mi Cuenta - Guía Empresarial";
$seo_robots = "noindex, nofollow";
$extra_css = ['mi_cuenta'];
require_once 'includes/Header.php';
?>

<style>
  .mc-avatar-preview {
    position: relative !important;
    width: 120px !important;
    height: 120px !important;
    border-radius: 50% !important;
    overflow: hidden !important;
    background: #f0f2f5 !important;
    border: 4px solid #ffffff !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
    display: block !important;
  }

  .mc-avatar-preview img,
  .mc-avatar-preview span {
    position: absolute !important;
    top: 50% !important;
    left: 50% !important;
    transform: translate(-50%, -50%) !important;
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 48px !important;
    font-weight: 800 !important;
    color: #3b5998 !important;
    margin: 0 !important;
  }

  .mc-avatar-overlay {
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    background: rgba(0, 0, 0, 0.45) !important;
    backdrop-filter: blur(2px) !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    opacity: 0 !important;
    transition: all 0.3s ease !important;
    z-index: 5 !important;
  }

  .mc-avatar-preview:hover .mc-avatar-overlay {
    opacity: 1 !important;
  }
</style>

<div class="mc-page">
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      <?php if ($error): ?>
        <?php if ($error === 'MISMA_PW'): ?>
          if (typeof showToast === 'function') showToast('La nueva contraseña es igual a la actual.', 'warning');
        <?php else: ?>
          if (typeof showToast === 'function') showToast(<?= json_encode($error) ?>, 'error');
        <?php endif; ?>
      <?php endif; ?>
      <?php if ($exito): ?>
        if (typeof showToast === 'function') showToast(<?= json_encode($exito) ?>, 'success');
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
          <div class="mc-avatar-preview" id="mc-avatar-preview" onclick="document.getElementById('foto_input').click()"
            style="cursor:pointer; position:relative; overflow:hidden;">
            <?php if (!empty($u['foto_perfil'])): ?>
              <img src="assets/img/avatars/<?= htmlspecialchars($u['foto_perfil']) ?>?t=<?= time() ?>" alt="Avatar">
            <?php else: ?>
              <span><?= mb_strtoupper(mb_substr($u['nombre'], 0, 1)) ?></span>
            <?php endif; ?>
            <div class="mc-avatar-overlay">
              <i class="bi bi-camera"></i>
            </div>
          </div>
          <div id="avatar-confirm-wrap"
            style="display:none; margin-top:15px; text-align:center; animation:fadeIn 0.3s;">
            <button type="button" onclick="document.getElementById('form-foto').submit()" class="mc-btn-primary"
              style="padding: 8px 16px; font-size:12px; background: #10b981; border:none; border-radius:8px; color:white; font-weight:700; cursor:pointer;">
              <i class="bi bi-check-circle"></i> Confirmar foto
            </button>
            <button type="button" onclick="location.reload()"
              style="background:none; border:none; color:#ef4444; font-size:11px; cursor:pointer; margin-left:12px; font-weight:600;">Cancelar</button>
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
                onchange="previewFoto(event);">
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
        <?php $esperando_otp = isset($_SESSION['esperando_otp_pw']) && $_SESSION['esperando_otp_pw'] === true; ?>

        <?php if ($bypass_active): ?>
          <div class="mc-alerta mc-alerta-ok" style="margin-bottom: 20px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="20 6 9 17 4 12" />
            </svg>
            Autorización concedida vía email. Puedes establecer tu nueva contraseña.
          </div>
          <form method="POST" class="mc-form">
            <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
            <input type="hidden" name="accion" value="password">
            <input type="hidden" name="actual" value="bypass">

            <?php if ($error === 'MISMA_PW'): ?>
              <div class="mc-alerta mc-alerta-warning"
                style="margin-bottom: 20px; border-left: 4px solid var(--aura-yellow);">
                <div style="display:flex; align-items:center; gap:10px;">
                  <i class="bi bi-exclamation-triangle" style="font-size: 20px;"></i>
                  <div>
                    <strong>Contraseña idéntica</strong><br>
                    La nueva contraseña es la misma que ya tienes configurada.
                  </div>
                </div>
                <div style="margin-top:12px;">
                  <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:13px;">
                    <input type="checkbox" name="confirmar_misma" value="1" required>
                    Entiendo, deseo mantener esta contraseña.
                  </label>
                </div>
              </div>
            <?php endif; ?>

            <div class="mc-field">
              <label>Nueva contraseña</label>
              <div class="pw-input-wrap">
                <input type="password" name="nueva" id="pw-nueva" placeholder="••••••••" required autofocus>
                <button type="button" class="pw-toggle" onclick="togglePw('pw-nueva','pw-nueva-icon')">
                  <i class="bi bi-eye" id="pw-nueva-icon"></i>
                </button>
              </div>
              <span class="mc-hint">Mínimo 8 caracteres.</span>
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
              <button type="submit" class="mc-btn-dark">Actualizar contraseña</button>
            </div>
          </form>

        <?php elseif ($esperando_otp): ?>
          <div class="mc-alerta mc-alerta-info" style="margin-bottom: 20px;">
            <i class="bi bi-envelope-at" style="margin-right:8px;"></i>
            Hemos enviado un código a <strong><?= htmlspecialchars($u['email']) ?></strong>.
          </div>
          <form method="POST" class="mc-form">
            <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
            <input type="hidden" name="accion" value="verificar_codigo_pw">
            <div class="mc-field">
              <label>Código de Verificación</label>
              <input type="text" name="codigo_otp" placeholder="000000" maxlength="6" required autofocus
                style="text-align: center; font-size: 24px; letter-spacing: 8px; font-weight: 700;">
            </div>
            <div class="mc-form-footer">
              <button type="submit" class="mc-btn-primary">Verificar código</button>
              <div style="display:inline-block; margin-left: 10px;">
                <button type="submit" name="accion" value="enviar_codigo_pw" class="mc-btn-ghost mc-btn-sm"
                  id="btn-resend-pw" formnovalidate>
                  Reenviar código
                </button>
                <span id="timer-resend-pw" style="font-size: 12px; color: rgba(255,255,255,0.4); display: none;">
                  Reenviar en <span id="timer-sec-pw">60</span>s
                </span>
              </div>
            </div>
          </form>

        <?php else: ?>
          <div class="mc-empty" style="padding: 20px 0;">
            <div
              style="background: rgba(255,255,255,0.03); width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
              <i class="bi bi-shield-lock" style="font-size: 32px; color: var(--aura-yellow);"></i>
            </div>
            <h3 style="margin-bottom: 10px;">Cambio de Contraseña</h3>
            <p style="color: rgba(255,255,255,0.6); font-size: 14px; max-width: 300px; margin: 0 auto 24px;">
              Para proteger tu cuenta, te enviaremos un código de seguridad a tu correo electrónico registrado.
            </p>
            <form method="POST">
              <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
              <input type="hidden" name="accion" value="enviar_codigo_pw">
              <button type="submit" class="mc-btn-primary">
                <i class="bi bi-send" style="margin-right: 8px;"></i>
                Enviar código al correo
              </button>
            </form>
          </div>
        <?php endif; ?>
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
            <?php if (count($mis_resenas) === 10): ?>
              <div style="text-align:center; padding: 15px 0;">
                <p style="font-size: 13px; color: rgba(255,255,255,0.5);">Mostrando las últimas 10 reseñas. (Paginación en
                  desarrollo)</p>
              </div>
            <?php endif; ?>
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
            <?php if (count($mis_favoritos) === 10): ?>
              <div style="text-align:center; padding: 15px 0; grid-column: span 100%;">
                <p style="font-size: 13px; color: rgba(255,255,255,0.5);">Mostrando los últimos 10 favoritos. (Paginación en
                  desarrollo)</p>
              </div>
            <?php endif; ?>
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
          <p>Una vez eliminada, no hay marcha atrás. Por seguridad, confirma la eliminación definitiva.</p>
          <form method="POST" id="form-borrar-cuenta">
            <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">

            <?php if ($u['password_hash']): ?>
              <input type="hidden" name="accion" value="borrar_cuenta">
              <div id="confirm-del-wrap" style="display: none; margin-bottom: 15px; animation: fadeIn 0.3s ease;">
                <input type="password" name="password_confirm" placeholder="Confirma tu contraseña actual"
                  style="width: 100%; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); padding: 12px; border-radius: 8px; color: white; outline: none;">
              </div>
              <button type="button" class="mc-btn-delete" id="btn-pre-delete"
                onclick="mostrarConfirmacionBorrado()">Eliminar cuenta</button>
            <?php else: ?>
              <?php if (isset($_SESSION['esperando_otp_del'])): ?>
                <input type="hidden" name="accion" value="borrar_cuenta">
                <div style="margin-bottom: 15px;">
                  <input type="text" name="codigo_borrar" placeholder="Ingresa el código de 6 dígitos" maxlength="6"
                    style="width: 100%; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); padding: 12px; border-radius: 8px; color: white; outline: none; text-align: center; letter-spacing: 4px;">
                </div>
                <button type="submit" class="mc-btn-delete" style="background: #ef4444;">Confirmar eliminación
                  definitiva</button>
              <?php else: ?>
                <input type="hidden" name="accion" value="enviar_codigo_borrar">
                <button type="submit" class="mc-btn-delete">Enviar código de confirmación para eliminar</button>
              <?php endif; ?>
            <?php endif; ?>

            <?php if ($u['password_hash']): ?>
              <div id="final-del-actions" style="display: none; gap: 10px; animation: fadeIn 0.3s ease;">
                <button type="submit" class="mc-btn-delete" style="background: #ef4444; flex: 1;">Confirmar
                  eliminación</button>
                <button type="button" class="mc-btn-ghost" style="flex: 1;"
                  onclick="cancelarBorrarCuenta()">Cancelar</button>
              </div>
            <?php endif; ?>
          </form>
        </div>

      </div>

    </main>
  </div>
</div>

<div id="delete-modal" class="mc-modal-overlay">
  <div class="mc-modal">
    <div class="mc-modal-icon">
      <i class="bi bi-trash3-fill"></i>
    </div>
    <h3 class="mc-modal-title">¿Eliminar reseña?</h3>
    <p class="mc-modal-text">¿Seguro que deseas eliminar esta reseña permanentemente? Esta acción no se puede deshacer.
    </p>
    <div class="mc-modal-actions">
      <button type="button" class="mc-btn-ghost" onclick="closeDeleteModal()">Cancelar</button>
      <button type="button" class="mc-btn-primary" style="background: var(--mc-red);" id="btn-confirm-delete">Eliminar
        ahora</button>
    </div>
  </div>
</div>

<script>
  function mostrarConfirmacionBorrado() {
    document.getElementById('confirm-del-wrap').style.display = 'block';
    document.getElementById('final-del-actions').style.display = 'flex';
    document.getElementById('btn-pre-delete').style.display = 'none';
  }

  function cancelarBorrarCuenta() {
    document.getElementById('confirm-del-wrap').style.display = 'none';
    document.getElementById('final-del-actions').style.display = 'none';
    document.getElementById('btn-pre-delete').style.display = 'block';
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

      const previewImg = document.querySelector('#mc-avatar-preview img');
      if (previewImg) {
        previewImg.src = ev.target.result;
      } else {
        const preview = document.getElementById('mc-avatar-preview');
        const img = document.createElement('img');
        img.src = ev.target.result;
        img.alt = 'Avatar';
        img.style.position = 'absolute';
        img.style.top = '50%';
        img.style.left = '50%';
        img.style.transform = 'translate(-50%, -50%)';
        img.style.width = '100%';
        img.style.height = '100%';
        img.style.objectFit = 'cover';

        preview.insertBefore(img, preview.firstChild);
        const span = preview.querySelector('span');
        if (span) span.style.display = 'none';
      }

      const headerAvatar = document.querySelector('.mc-header-avatar img');
      if (headerAvatar) {
        headerAvatar.src = ev.target.result;
      }

      const confirmWrap = document.getElementById('avatar-confirm-wrap');
      if (confirmWrap) confirmWrap.style.display = 'block';

      if (window.showToast) showToast('Previsualización cargada. ¿Te gusta cómo queda?', 'success');
    };
    reader.readAsDataURL(file);
  }

  let resenaToDelete = null;
  function eliminarResenaAjax(btn, id_resena) {
    resenaToDelete = { btn, id: id_resena };
    const modal = document.getElementById('delete-modal');
    modal.classList.add('open');
    document.getElementById('btn-confirm-delete').onclick = ejecutarEliminacion;
  }

  function closeDeleteModal() {
    document.getElementById('delete-modal').classList.remove('open');
    resenaToDelete = null;
  }

  function ejecutarEliminacion() {
    if (!resenaToDelete) return;
    const { btn, id: id_resena } = resenaToDelete;
    closeDeleteModal();

    const resenaDiv = btn.closest('.mc-resena');
    btn.innerText = 'Eliminando...';
    btn.disabled = true;
    btn.style.opacity = '0.7';
    btn.style.cursor = 'not-allowed';

    fetch('ajax/eliminar_resena', {
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
          if (window.showToast) showToast(data.error || 'Hubo un error al eliminar la reseña.', 'error');
          resetBtn();
        }
      })
      .catch(err => {
        console.error(err);
        if (window.showToast) showToast('Error de conexión con el servidor.', 'error');
        resetBtn();
      });

    function resetBtn() {
      btn.innerText = 'Eliminar';
      btn.disabled = false;
      btn.style.opacity = '1';
      btn.style.cursor = 'pointer';
    }
  }

  function startResendTimerPw() {
    const btn = document.getElementById('btn-resend-pw');
    const timerWrap = document.getElementById('timer-resend-pw');
    const timerSec = document.getElementById('timer-sec-pw');
    if (!btn || !timerWrap) return;

    let seconds = 60;
    btn.style.display = 'none';
    timerWrap.style.display = 'inline-block';
    timerSec.textContent = seconds;

    const interval = setInterval(() => {
      seconds--;
      timerSec.textContent = seconds;
      if (seconds <= 0) {
        clearInterval(interval);
        btn.style.display = 'inline-block';
        timerWrap.style.display = 'none';
      }
    }, 1000);
  }

  <?php if ($esperando_otp): ?>
    document.addEventListener('DOMContentLoaded', startResendTimerPw);
  <?php endif; ?>
</script>

<?php include 'includes/footer.php'; ?>