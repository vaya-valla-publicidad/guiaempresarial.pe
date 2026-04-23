<?php
session_start();
include 'db.php';
include_once 'libs/mailer.php';
include_once 'includes/security.php';

if (isset($_SESSION['usuario_publico_id'])) {
  header("Location: mi_cuenta");
  exit;
}

$error = '';
$exito = '';
$paso = $_GET['paso'] ?? 'registro';
$email_param = $_GET['email'] ?? '';

if (isset($_GET['check_email'])) {
  $email = trim($_GET['check_email']);
  $stmt = $conexion->prepare("SELECT id FROM usuarios_publicos WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  echo json_encode(['exists' => $stmt->get_result()->num_rows > 0]);
  exit;
}

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

  if ($limits['daily_count'] >= 20) {
    echo json_encode(['success' => false, 'error' => 'Límite diario alcanzado. Intenta en 24 horas.']);
  } elseif ($limits['count'] >= 10) {
    if ($now - $limits['last_time'] < 3600) {
      echo json_encode(['success' => false, 'error' => 'Límite de 10 intentos alcanzado. Espera 1 hora.']);
    } else {
      $limits['count'] = 0;
    }
  } elseif ($limits['count'] >= 7 && ($now - $limits['last_time'] < 1800)) {
    echo json_encode(['success' => false, 'error' => 'Demasiados intentos. Espera 30 minutos.']);
  }

  if (!isset($error) || !$error) {
    $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $expira = time() + 600;
    $stmt = $conexion->prepare("UPDATE usuarios_publicos SET codigo_verificacion=?, codigo_expira=? WHERE email=? AND verificado=0");
    $stmt->bind_param("sis", $codigo, $expira, $email);
    if ($stmt->execute()) {
      enviarCorreo($email, 'Usuario', 'Código de Acceso - Guía Empresarial', "Tu nuevo código: $codigo");
      $limits['count']++;
      $limits['daily_count']++;
      $limits['last_time'] = $now;
      echo json_encode(['success' => true]);
    } else {
      echo json_encode(['success' => false, 'error' => 'Error al procesar el envío.']);
    }
  }
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
  if (!validarCSRF()) {
    $error = 'Error de seguridad detectado. Por favor, recarga la página.';
  } else {
    if ($_POST['accion'] === 'registrar') {
      $nombre = trim($_POST['nombre']);
      $email = trim($_POST['email']);
      $password = $_POST['password'];
      $confirm = $_POST['confirm'];

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

      if ($limits['daily_count'] >= 20) {
        $error = 'Límite diario alcanzado. Intenta mañana.';
      } elseif ($limits['count'] >= 10 && ($now - $limits['last_time'] < 3600)) {
        $error = 'Límite de 10 intentos alcanzado. Espera 1 hora.';
      } elseif ($limits['count'] >= 7 && ($now - $limits['last_time'] < 1800)) {
        $error = 'Demasiados intentos. Espera 30 minutos.';
      } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
      } elseif ($password !== $confirm) {
        $error = 'Las contraseñas no coinciden.';
      } else {
        $stmt = $conexion->prepare("SELECT id FROM usuarios_publicos WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
          $error = 'Este correo ya está registrado.';
        } else {
          $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
          $expira = time() + 600;
          $pw_hash = password_hash($password, PASSWORD_DEFAULT);
          $stmt = $conexion->prepare("INSERT INTO usuarios_publicos (nombre, email, password_hash, codigo_verificacion, codigo_expira, verificado) VALUES (?, ?, ?, ?, ?, 0)");
          $stmt->bind_param("ssssi", $nombre, $email, $pw_hash, $codigo, $expira);
          if ($stmt->execute()) {
            enviarCorreo($email, $nombre, 'Verificación - Guía Empresarial', "Tu código: $codigo");
            $limits['count_30m']++;
            header("Location: registro_usuario?paso=verificar&email=" . urlencode($email));
            exit;
          } else {
            $error = 'Error al registrar. Inténtalo de nuevo.';
          }
        }
      }
    }

    if ($_POST['accion'] === 'verificar_codigo') {
      $email = trim($_POST['email']);
      $codigo = trim($_POST['codigo']);
      $stmt = $conexion->prepare("SELECT * FROM usuarios_publicos WHERE email = ? AND verificado = 0");
      $stmt->bind_param("s", $email);
      $stmt->execute();
      $res = $stmt->get_result();
      if ($res->num_rows === 1) {
        $u = $res->fetch_assoc();
        if ($codigo === $u['codigo_verificacion'] && time() < $u['codigo_expira']) {
          $conexion->query("UPDATE usuarios_publicos SET verificado=1, codigo_verificacion=NULL, codigo_expira=NULL WHERE id=" . $u['id']);
          $_SESSION['usuario_publico_id'] = $u['id'];
          $_SESSION['usuario_publico_nombre'] = $u['nombre'];
          if (isset($_POST['ajax'])) {
            echo json_encode(['success' => true, 'redirect' => 'mi_cuenta']);
            exit;
          }
          header("Location: mi_cuenta");
          exit;
        } else {
          if (isset($_POST['ajax'])) {
            echo json_encode(['success' => false, 'error' => 'Código incorrecto o expirado.']);
            exit;
          }
          $error = 'Código incorrecto o expirado.';
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
  <title>Registro — Guía Empresarial</title>
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
      <a href="index">
        <img src="assets/img/image.png" alt="Logo" class="brand-logo">
      </a>
      <span class="brand-name">Guía Empresarial</span>
    </nav>

    <main class="reg-card">
      <header class="reg-header">
        <?php if ($paso === 'registro'): ?>
          <h1>Únete ahora</h1>
          <p>Potencia tu marca en nuestra red.</p>
        <?php else: ?>
          <h1>Verificación</h1>
          <p>Confirma tu identidad para continuar.</p>
        <?php endif; ?>
      </header>

      <?php if ($error): ?>
        <script>document.addEventListener('DOMContentLoaded', () => { if (window.showToast) showToast("<?= addslashes($error) ?>", "error"); });</script>
      <?php endif; ?>

      <?php if ($paso === 'registro'): ?>
        <form method="POST" id="reg-form">
          <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
          <input type="hidden" name="accion" value="registrar">

          <div class="field-wrap">
            <label>Nombre Completo</label>
            <input type="text" name="nombre" placeholder="Nombre Completo" required
              value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
          </div>

          <div class="field-wrap">
            <label>Correo Electrónico</label>
            <div style="position:relative;">
              <input type="email" name="email" id="reg-email" placeholder="correo@ejemplo.com" required
                value="<?= htmlspecialchars($_POST['email'] ?? $email_param) ?>" onblur="checkEmail(this.value)">
              <div id="email-status" class="email-indicator"></div>
            </div>
          </div>

          <div class="field-wrap">
            <label>Contraseña</label>
            <div class="pw-group">
              <input type="password" name="password" id="pw1" placeholder="••••••••" required
                oninput="checkStrength(this.value)">
              <button type="button" class="pw-toggle" onclick="togglePw('pw1','ico1')"><i class="bi bi-eye"
                  id="ico1"></i></button>
            </div>
            <div class="strength-bar">
              <div class="strength-line" id="str-line"></div>
            </div>
          </div>

          <div class="field-wrap">
            <label>Confirmar Contraseña</label>
            <div class="pw-group">
              <input type="password" name="confirm" id="pw2" placeholder="••••••••" required>
              <button type="button" class="pw-toggle" onclick="togglePw('pw2','ico2')"><i class="bi bi-eye"
                  id="ico2"></i></button>
            </div>
          </div>

          <button type="submit" class="btn-primary">Crear mi cuenta</button>
        </form>

      <?php elseif ($paso === 'verificar'): ?>
        <div class="verification-view">
          <p style="text-align:center; font-size:14px; margin-bottom:25px; color:rgba(255,255,255,0.6);">
            Código enviado a <strong><?= htmlspecialchars($email_param) ?></strong>
          </p>

          <form method="POST" id="form-otp">
            <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
            <input type="hidden" name="accion" value="verificar_codigo">
            <input type="hidden" name="email" value="<?= htmlspecialchars($email_param) ?>">
            <input type="hidden" name="codigo" id="otp-hidden">

            <div class="otp-group">
              <?php for ($i = 0; $i < 6; $i++): ?>
                <input class="otp-box" type="text" maxlength="1" inputmode="numeric">
              <?php endfor; ?>
            </div>

            <button type="submit" class="btn-primary">Validar acceso</button>
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

      <footer class="auth-footer-nav">
        ¿Ya tienes cuenta? <a href="login_usuario">Inicia sesión</a>
      </footer>
    </main>
  </div>

  <script>
    function togglePw(id, ico) {
      const input = document.getElementById(id);
      const icon = document.getElementById(ico);
      input.type = input.type === 'password' ? 'text' : 'password';
      icon.className = input.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
    }

    function checkStrength(val) {
      const bar = document.getElementById('str-line');
      if (!val) { bar.style.width = '0%'; return; }
      let score = 0;
      if (val.length >= 6) score++;
      if (val.length >= 10) score++;
      if (/[A-Z]/.test(val)) score++;
      if (/[0-9]/.test(val) || /[^A-Za-z0-9]/.test(val)) score++;
      const colors = ['#ef4444', '#f59e0b', '#10b981', '#10b981'];
      bar.style.width = (score / 4) * 100 + '%';
      bar.style.background = colors[score - 1] || '#ef4444';
    }

    const otpBoxes = document.querySelectorAll('.otp-box');
    const otpHid = document.getElementById('otp-hidden');

    otpBoxes.forEach((inp, idx) => {
      inp.addEventListener('input', e => {
        e.target.value = e.target.value.replace(/\D/g, '').slice(-1);
        if (e.target.value && idx < otpBoxes.length - 1) otpBoxes[idx + 1].focus();
        collectOTP();
        if (otpHid.value.length === 6) handleFinalSubmit();
      });
      inp.addEventListener('keydown', e => {
        if (e.key === 'Backspace' && !e.target.value && idx > 0) otpBoxes[idx - 1].focus();
      });
    });

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
      fetch('registro_usuario', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            overlay.style.display = 'flex';
            msg.textContent = '¡Todo listo! ✓';
            setTimeout(() => { window.location.href = data.redirect; }, 1500);
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

    async function checkEmail(email) {
      if (!email || !email.includes('@')) return;
      const indicator = document.getElementById('email-status');
      try {
        const r = await fetch(`registro_usuario?check_email=${encodeURIComponent(email)}`);
        const data = await r.json();
        if (data.exists) {
          indicator.innerHTML = '<i class="bi bi-exclamation-circle-fill"></i> Ya registrado';
          indicator.className = 'email-indicator error';
        } else {
          indicator.innerHTML = '<i class="bi bi-check-circle"></i>';
          indicator.className = 'email-indicator success';
        }
      } catch (e) { }
    }

    function resendOTP() {
      const btn = document.getElementById('btn-resend');
      const email = "<?= $email_param ?>";
      const formData = new FormData();
      formData.append('accion', 'reenviar_codigo_ajax');
      formData.append('email', email);
      formData.append('csrf_token', '<?= generarTokenCSRF() ?>');

      fetch('registro_usuario', { method: 'POST', body: formData })
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

    <?php if ($paso === 'verificar'): ?>
      document.addEventListener('DOMContentLoaded', startResendTimer);
    <?php endif; ?>

    window.addEventListener('pageshow', (e) => {
      const overlay = document.getElementById('auth-loading');
      if (overlay) overlay.style.display = 'none';
      document.querySelectorAll('.btn-primary').forEach(btn => {
        btn.disabled = false;
        if (btn.textContent === 'Procesando...' || btn.textContent === 'Verificando...') {
          btn.textContent = document.getElementById('reg-form') ? 'Crear mi cuenta' : 'Validar acceso';
        }
      });
    });
  </script>
</body>

</html>