<?php
session_start();
include 'db.php';
include_once 'libs/mailer.php';
include_once 'includes/security.php';

if (isset($_SESSION['usuario_publico_id'])) {
  $destino = !empty($_GET['redir']) ? validarRedireccionLocal(urldecode($_GET['redir'])) : 'mi_cuenta';
  header("Location: $destino");
  exit;
}

$error = '';
$exito = '';
if (isset($_GET['exito']) && $_GET['exito'] === 'pw_cambiada') {
  $exito = 'Contraseña actualizada correctamente. Inicia sesión con tu nueva clave.';
}
$redir = validarRedireccionLocal($_GET['redir'] ?? '');
$paso = $_GET['paso'] ?? 'email';
$email_param = $_GET['email'] ?? '';

$max_30m = 5;
$max_24h = 10;

if (isset($_POST['accion']) && $_POST['accion'] === 'reenviar_codigo_ajax') {
  if (!validarCSRF()) {
    echo json_encode(['success' => false, 'error' => 'Error de seguridad.']);
    exit;
  }
  $email = trim($_POST['email']);

  if (!isset($_SESSION['otp_limits'])) {
    $_SESSION['otp_limits'] = ['count' => 0, 'last_time' => 0, 'daily_count' => 0, 'daily_start' => time()];
  }
  $limits = &$_SESSION['otp_limits'];
  $now = time();

  if ($now - $limits['daily_start'] > 86400) {
    $limits['daily_count'] = 0;
    $limits['daily_start'] = $now;
    $limits['count'] = 0;
  }

  if ($limits['daily_count'] >= $max_24h) {
    echo json_encode(['success' => false, 'error' => 'Límite diario alcanzado. Intenta en 24 horas.']);
  } elseif ($limits['count'] >= 10) {
    if ($now - $limits['last_time'] < 3600) {
      echo json_encode(['success' => false, 'error' => 'Límite de 10 intentos alcanzado. Espera 1 hora.']);
    } else {
      $limits['count'] = 0;
    }
  } elseif ($limits['count'] >= $max_30m && ($now - $limits['last_time'] < 1800)) {
    echo json_encode(['success' => false, 'error' => 'Demasiados intentos. Espera 30 minutos.']);
  } else {
    $stmt = $conexion->prepare("SELECT nombre FROM usuarios_publicos WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 1) {
      $u = $res->fetch_assoc();
      $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
      $expira = time() + 600;
      $stmt_upd = $conexion->prepare("UPDATE usuarios_publicos SET codigo_verificacion=?, codigo_expira=? WHERE email=?");
      $stmt_upd->bind_param("sis", $codigo, $expira, $email);
      if ($stmt_upd->execute()) {
        $cuerpo = plantillaCorreoOTP($u['nombre'], $codigo, 'acceso');
        enviarCorreo($email, $u['nombre'], 'Código de Acceso - Guía Empresarial', $cuerpo);
        $limits['count']++;
        $limits['daily_count']++;
        $limits['last_time'] = $now;
        echo json_encode(['success' => true]);
      } else {
        echo json_encode(['success' => false, 'error' => 'Error al procesar el envío.']);
      }
    } else {
      echo json_encode(['success' => false, 'error' => 'Usuario no encontrado.']);
    }
  }
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
  if (!empty($_POST['telefono_movil'])) {
    header("Location: index");
    exit;
  }

  if (!validarCSRF()) {
    $error = 'Error de seguridad detectado. Por favor, recarga la página.';
  } else {

    if ($_POST['accion'] === 'enviar_codigo') {
      $email = trim($_POST['email'] ?? '');
      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Correo inválido.';
      } else {
        if (!isset($_SESSION['otp_limits'])) {
          $_SESSION['otp_limits'] = ['count' => 0, 'last_time' => 0, 'daily_count' => 0, 'daily_start' => time()];
        }
        $limits = &$_SESSION['otp_limits'];
        $now = time();
        if ($now - $limits['daily_start'] > 86400) {
          $limits['daily_count'] = 0;
          $limits['daily_start'] = $now;
          $limits['count'] = 0;
        }

        if ($limits['daily_count'] >= $max_24h) {
          $error = 'Límite diario de 20 intentos alcanzado. Intenta en 24 horas.';
        } elseif ($limits['count'] >= 10 && ($now - $limits['last_time'] < 43200)) {
          $error = 'Has superado el límite de 10 intentos. Por seguridad, espera 12 horas.';
        } elseif ($limits['count'] >= $max_30m && ($now - $limits['last_time'] < 1800)) {
          $error = 'Demasiados intentos. Espera 30 minutos.';
        } else {
          $stmt = $conexion->prepare("SELECT id, nombre, verificado FROM usuarios_publicos WHERE email = ?");
          $stmt->bind_param("s", $email);
          $stmt->execute();
          $res = $stmt->get_result();
          if ($res->num_rows === 1) {
            $u = $res->fetch_assoc();
            $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expira = time() + 600;
            $stmt_upd = $conexion->prepare("UPDATE usuarios_publicos SET codigo_verificacion=?, codigo_expira=? WHERE id=?");
            $stmt_upd->bind_param("sii", $codigo, $expira, $u['id']);
            $stmt_upd->execute();

            $cuerpo = plantillaCorreoOTP($u['nombre'], $codigo, 'acceso');
            enviarCorreo($email, $u['nombre'], 'Acceso a Guía Empresarial', $cuerpo);

            $limits['count']++;
            $limits['daily_count']++;
            $limits['last_time'] = $now;

            header("Location: login_usuario?paso=codigo&email=" . urlencode($email) . ($redir ? '&redir=' . urlencode($redir) : ''));
            exit;
          } else {
            header("Location: registro_usuario?email=" . urlencode($email));
            exit;
          }
        }
      }
    }

    if ($_POST['accion'] === 'check_email') {
      $email = trim($_POST['email'] ?? '');
      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Ingresa un correo válido.';
      } else {
        $stmt = $conexion->prepare("SELECT id, password_hash, verificado FROM usuarios_publicos WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows === 1) {
          $u = $res->fetch_assoc();
          if ($u['password_hash']) {
            header("Location: login_usuario?paso=password&email=" . urlencode($email) . ($redir ? '&redir=' . urlencode($redir) : ''));
            exit;
          } else {

            header("Location: login_usuario?paso=codigo&email=" . urlencode($email) . ($redir ? '&redir=' . urlencode($redir) : ''));
            exit;
          }
        } else {
          header("Location: registro_usuario?email=" . urlencode($email));
          exit;
        }
      }
    }

    if ($_POST['accion'] === 'login_password') {
      $email = trim($_POST['email'] ?? '');
      $password = $_POST['password'] ?? '';
      $stmt_check = $conexion->prepare("SELECT * FROM usuarios_publicos WHERE email = ?");
      $stmt_check->bind_param("s", $email);
      $stmt_check->execute();
      $res = $stmt_check->get_result();
      if ($res && $res->num_rows === 1) {
        $u = $res->fetch_assoc();
        if (password_verify($password, $u['password_hash'])) {
          $_SESSION['usuario_publico_id'] = $u['id'];
          $_SESSION['usuario_publico_nombre'] = $u['nombre'];
          $_SESSION['usuario_publico_pw_hash'] = $u['password_hash'];
          $destino = $redir ? urldecode($redir) : 'mi_cuenta';
          if (isset($_POST['ajax'])) {
            echo json_encode(['success' => true, 'redirect' => $destino]);
            exit;
          }
          header("Location: $destino");
          exit;
        } else {
          if (isset($_POST['ajax'])) {
            echo json_encode(['success' => false, 'error' => 'Contraseña incorrecta.']);
            exit;
          }
          $error = 'Contraseña incorrecta.';
          $paso = 'password';
          $email_param = $email;
        }
      }
    }

    if ($_POST['accion'] === 'verificar_codigo') {
      $email = trim($_POST['email'] ?? '');
      $codigo = trim($_POST['codigo'] ?? '');
      $stmt_check = $conexion->prepare("SELECT * FROM usuarios_publicos WHERE email = ?");
      $stmt_check->bind_param("s", $email);
      $stmt_check->execute();
      $res = $stmt_check->get_result();
      if ($res && $res->num_rows === 1) {
        $u = $res->fetch_assoc();
        if ($codigo === $u['codigo_verificacion'] && time() < $u['codigo_expira']) {
          $stmt_upd = $conexion->prepare("UPDATE usuarios_publicos SET verificado=1, codigo_verificacion=NULL, codigo_expira=NULL WHERE id=?");
          $stmt_upd->bind_param("i", $u['id']);
          $stmt_upd->execute();
          $stmt_upd->close();
          $_SESSION['usuario_publico_id'] = $u['id'];
          $_SESSION['usuario_publico_nombre'] = $u['nombre'];
          $_SESSION['usuario_publico_pw_hash'] = $u['password_hash'];
          $_SESSION['login_bypass_pw'] = true;
          $destino = $redir ? urldecode($redir) : 'mi_cuenta';
          if (isset($_POST['ajax'])) {
            echo json_encode(['success' => true, 'redirect' => $destino]);
            exit;
          }
          header("Location: $destino");
          exit;
        } else {
          if (isset($_POST['ajax'])) {
            echo json_encode(['success' => false, 'error' => 'Código incorrecto o expirado.']);
            exit;
          }
          $error = 'Código incorrecto o expirado.';
          $paso = 'codigo';
          $email_param = $email;
        }
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ingresar — Guía Empresarial</title>
  <link rel="icon" href="assets/img/image.png" type="image/png">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="assets/css/registro_usuario.css">
  <script src="assets/js/toast.js"></script>
</head>

<body>

  <div id="auth-loading" class="auth-overlay" style="display:none;">
    <div class="loader-content">
      <div class="pulse-spinner"></div>
      <h2 id="loader-msg">Verificando...</h2>
      <p>Un momento, por favor.</p>
    </div>
  </div>

  <div class="page-wrap">
    <nav class="auth-nav">
      <a href="index"><img src="assets/img/image.png" alt="Logo" class="brand-logo"></a>
      <span class="brand-name">Guía Empresarial</span>
    </nav>

    <main class="reg-card">
      <header class="reg-header">
        <?php if ($paso === 'email'): ?>
          <h1>Bienvenido</h1>
          <p>Identifícate para entrar a tu panel.</p>
        <?php elseif ($paso === 'password'): ?>
          <h1>Hola de nuevo</h1>
          <p>Ingresa tu clave de acceso.</p>
        <?php elseif ($paso === 'codigo'): ?>
          <h1>Acceso Seguro</h1>
          <p>Ingresa el código dinámico enviado.</p><?php endif; ?>
      </header>

      <?php if ($error): ?>
        <script>document.addEventListener('DOMContentLoaded', () => { if (window.showToast) showToast("<?= addslashes($error) ?>", "error"); });</script>
      <?php endif; ?>

      <?php if ($exito): ?>
        <script>document.addEventListener('DOMContentLoaded', () => { if (window.showToast) showToast("<?= addslashes($exito) ?>", "success"); });</script>
      <?php endif; ?>

      <?php if ($paso === 'email'): ?>
        <form method="POST" id="login-form">
          <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>"><input type="hidden" name="accion"
            value="check_email">
          <?php if ($redir): ?><input type="hidden" name="redir" value="<?= htmlspecialchars($redir) ?>"><?php endif; ?>

          <div style="display:none; visibility:hidden; opacity:0; position:absolute; left:-9999px;">
            <label for="telefono_movil">Teléfono Móvil</label>
            <input type="text" name="telefono_movil" id="telefono_movil" tabindex="-1" autocomplete="off">
          </div>
          <div class="field-wrap"><label>Correo Electrónico</label><input type="email" name="email"
              placeholder="tu@correo.com" required value="<?= htmlspecialchars($email_param) ?>"></div>
          <button type="submit" class="btn-primary">Continuar</button>
        </form>
      <?php elseif ($paso === 'password'): ?>
        <form method="POST" id="pw-form">
          <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>"><input type="hidden" name="accion"
            value="login_password"><input type="hidden" name="email" value="<?= htmlspecialchars($email_param) ?>">
          <?php if ($redir): ?><input type="hidden" name="redir" value="<?= htmlspecialchars($redir) ?>"><?php endif; ?>
          <div class="field-wrap"><label>Tu Contraseña</label>
            <div class="pw-group"><input type="password" name="password" id="pw-login" placeholder="••••••••" required
                autofocus><button type="button" class="pw-toggle" onclick="togglePw('pw-login','ico-login')"><i
                  class="bi bi-eye" id="ico-login"></i></button></div>
          </div>
          <button type="submit" class="btn-primary">Entrar ahora</button>
        </form>
        <div style="text-align:center; margin-top:25px;">
          <form method="POST"><input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>"><input
              type="hidden" name="accion" value="enviar_codigo"><input type="hidden" name="email"
              value="<?= htmlspecialchars($email_param) ?>">
            <?php if ($redir): ?><input type="hidden" name="redir" value="<?= htmlspecialchars($redir) ?>"><?php endif; ?>
            <button type="submit"
              style="background:none; border:none; color:var(--aura-yellow); font-weight:700; font-size:13px; text-decoration:underline; cursor:pointer; padding:0;">¿Olvidaste
              tu contraseña? Entrar con código</button>
          </form>
        </div>
      <?php elseif ($paso === 'codigo'): ?>
        <div class="verification-view">
          <p style="text-align:center; font-size:14px; margin-bottom:25px; color:rgba(255,255,255,0.6);">Código enviado a
            <strong><?= htmlspecialchars($email_param) ?></strong>
          </p>
          <form method="POST" id="form-otp">
            <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>"><input type="hidden" name="accion"
              value="verificar_codigo"><input type="hidden" name="email" value="<?= htmlspecialchars($email_param) ?>">
            <?php if ($redir): ?><input type="hidden" name="redir"
                value="<?= htmlspecialchars($redir) ?>"><?php endif; ?><input type="hidden" name="codigo" id="otp-hidden">
            <div class="otp-group"><?php for ($i = 0; $i < 6; $i++): ?><input class="otp-box" type="text" maxlength="1"
                  inputmode="numeric"><?php endfor; ?></div>
            <button type="submit" class="btn-primary">Validar código</button>
          </form>
          <div style="text-align:center; margin-top:25px;">
            <button id="btn-resend"
              style="background:none; border:none; color:rgba(255,255,255,0.5); font-size:12px; cursor:pointer;"
              onclick="resendOTP()">¿No llegó? Reenviar código</button>
            <div id="resend-timer" style="color:rgba(255,255,255,0.3); font-size:12px; display:none;">Reenviar en <span
                id="timer-sec">60</span>s</div>
          </div>
        </div>
      <?php endif; ?>
      <footer class="auth-footer-nav"><?php if ($paso === 'email'): ?>¿Aún no tienes cuenta? <a
            href="registro_usuario">Regístrate gratis</a><?php else: ?><a href="login_usuario?paso=email">← Cambiar
            correo</a><?php endif; ?></footer>
    </main>
  </div>

  <script>
    function togglePw(id, ico) {
      const input = document.getElementById(id);
      const icon = document.getElementById(ico);
      input.type = input.type === 'password' ? 'text' : 'password';
      icon.className = input.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
    }

    const otpBoxes = document.querySelectorAll('.otp-box');
    const otpHid = document.getElementById('otp-hidden');

    if (otpBoxes.length > 0) {
      otpBoxes.forEach((inp, idx) => {
        inp.addEventListener('input', e => {
          if (e.target.value.length === 1 && idx < otpBoxes.length - 1) otpBoxes[idx + 1].focus();
          collectOTP();
          if (otpHid.value.length === 6) handleFinalSubmit();
        });
        inp.addEventListener('keydown', e => {
          if (e.key === 'Backspace' && !e.target.value && idx > 0) otpBoxes[idx - 1].focus();
        });
        inp.addEventListener('paste', e => {
          e.preventDefault();
          const data = e.clipboardData.getData('text').trim();
          const match = data.match(/\d{6}/);
          if (match) {
            const digits = match[0].split('');
            otpBoxes.forEach((box, i) => box.value = digits[i]);
            collectOTP();
            handleFinalSubmit();
          }
        });
      });
    }

    async function pegarCodigo() {
      try {
        const text = await navigator.clipboard.readText();
        const match = text.trim().match(/\d{6}/);
        if (match) {
          const digits = match[0].split('');
          otpBoxes.forEach((box, i) => box.value = digits[i]);
          collectOTP();
          if (window.showToast) showToast('Código pegado correctamente.', 'success');

        } else {
          if (window.showToast) showToast('No se encontró un código de 6 dígitos en el portapapeles.', 'error');
        }
      } catch (err) {
        if (window.showToast) showToast('Error al acceder al portapapeles. Asegúrate de dar permisos.', 'error');
      }
    }

    function collectOTP() { if (otpHid) otpHid.value = Array.from(otpBoxes).map(i => i.value).join(''); }

    function handleFinalSubmit() {
      const overlay = document.getElementById('auth-loading');
      const msg = document.getElementById('loader-msg');
      const form = document.getElementById('form-otp');
      if (!form) return;
      const btn = form.querySelector('.btn-primary');
      const originalText = btn.textContent;
      btn.disabled = true;
      btn.textContent = 'Verificando...';
      const formData = new FormData(form);
      formData.append('ajax', '1');
      fetch('login_usuario', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            overlay.style.display = 'flex';
            msg.textContent = '¡Bienvenido! ✓';
            setTimeout(() => { window.location.href = data.redirect; }, 800);
          } else {
            btn.disabled = false;
            btn.textContent = originalText;
            otpBoxes.forEach(i => i.value = '');
            otpBoxes[0].focus();
            collectOTP();
            if (window.showToast) showToast(data.error || 'Código incorrecto.', 'error');
          }
        })
        .catch(() => form.submit());
    }

    function resendOTP() {
      const btn = document.getElementById('btn-resend');
      const email = "<?= htmlspecialchars($email_param, ENT_QUOTES, 'UTF-8') ?>";
      const formData = new FormData();
      formData.append('accion', 'reenviar_codigo_ajax');
      formData.append('email', email);
      formData.append('csrf_token', '<?= generarTokenCSRF() ?>');
      fetch('login_usuario', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            if (window.showToast) showToast('Código enviado con éxito.', 'success');
            startResendTimer();
          } else {
            if (window.showToast) showToast(data.error, 'error');
            if (data.error.includes('Límite')) startResendTimer();
          }
        });
    }

    function startResendTimer() {
      const btn = document.getElementById('btn-resend');
      const timerWrap = document.getElementById('resend-timer');
      const timerSec = document.getElementById('timer-sec');
      if (!btn) return;
      let seconds = 60;
      btn.style.display = 'none';
      timerWrap.style.display = 'block';
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

    <?php if ($paso === 'codigo'): ?>
      document.addEventListener('DOMContentLoaded', startResendTimer);
    <?php endif; ?>

    document.getElementById('form-otp')?.addEventListener('submit', function (e) { e.preventDefault(); handleFinalSubmit(); });
    document.getElementById('login-form')?.addEventListener('submit', function () { const btn = this.querySelector('.btn-primary'); btn.disabled = true; btn.textContent = 'Buscando...'; });
    document.getElementById('pw-form')?.addEventListener('submit', function (e) {
      e.preventDefault();
      const overlay = document.getElementById('auth-loading');
      const msg = document.getElementById('loader-msg');
      const btn = this.querySelector('.btn-primary');
      const originalText = btn.textContent;
      btn.disabled = true;
      btn.textContent = 'Verificando...';
      const formData = new FormData(this);
      formData.append('ajax', '1');
      fetch('login_usuario', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            overlay.style.display = 'flex';
            msg.textContent = '¡Acceso correcto! ✓';
            setTimeout(() => { window.location.href = data.redirect; }, 800);
          } else {
            btn.disabled = false;
            btn.textContent = originalText;
            if (window.showToast) showToast(data.error || 'Credenciales incorrectas.', 'error');
          }
        })
        .catch(() => { btn.disabled = false; btn.textContent = originalText; this.submit(); });
    });

    window.addEventListener('pageshow', (e) => {
      const overlay = document.getElementById('auth-loading');
      if (overlay) overlay.style.display = 'none';
      document.querySelectorAll('.btn-primary').forEach(btn => {
        btn.disabled = false;
        if (btn.textContent === 'Buscando...' || btn.textContent === 'Procesando...' || btn.textContent === 'Verificando...') {
          if (document.getElementById('login-form')) btn.textContent = 'Continuar';
          if (document.getElementById('pw-form')) btn.textContent = 'Entrar ahora';
          if (document.getElementById('form-otp')) btn.textContent = 'Validar código';
        }
      });
    });
  </script>
</body>

</html>