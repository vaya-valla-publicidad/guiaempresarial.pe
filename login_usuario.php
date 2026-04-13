<?php
session_start();
include 'db.php';
include_once 'libs/mailer.php';
include_once 'includes/security.php';

if (isset($_SESSION['usuario_publico_id'])) {
  $destino = !empty($_GET['redir']) ? validarRedireccionLocal(urldecode($_GET['redir'])) : 'mi_cuenta.php';
  header("Location: $destino");
  exit;
}

$error = '';
$exito = '';
$redir = validarRedireccionLocal($_GET['redir'] ?? '');
$paso = $_GET['paso'] ?? 'email';
$email_param = $_GET['email'] ?? '';

$conexion->query("DELETE FROM usuarios_publicos WHERE verificado = 0 AND codigo_expira < " . time());

$max_intentos_pw = 3;
$bloqueo_minutos = 5;

if (!isset($_SESSION['pub_intentos_pw'])) {
  $_SESSION['pub_intentos_pw'] = 0;
  $_SESSION['pub_ultimo_intento_pw'] = 0;
}

$tiempo_actual = time();
$tiempo_bloqueo_pw = $_SESSION['pub_ultimo_intento_pw'] + ($bloqueo_minutos * 60);
$bloqueado_pw = ($_SESSION['pub_intentos_pw'] >= $max_intentos_pw && $tiempo_actual < $tiempo_bloqueo_pw);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
  if (!validarCSRF()) {
    $error = 'Error de seguridad detectado (CSRF). Por favor recarga la página e intenta de nuevo.';
    $paso = $_POST['accion'] === 'check_email' ? 'email' : ($_POST['accion'] === 'verificar_codigo' ? 'codigo' : 'password');
    $email_param = $_POST['email'] ?? '';
  } else {
    if ($_POST['accion'] === 'check_email') {
      $email = trim($_POST['email'] ?? '');
      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Ingresa un correo válido.';
        $paso = 'email';
      } else {
        $stmt = $conexion->prepare("SELECT id, password_hash, verificado FROM usuarios_publicos WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows === 1) {
          $u = $res->fetch_assoc();
          if ($u['verificado'] == 0) {
            header("Location: registro_usuario.php?email=" . urlencode($email));
            exit;
          } elseif ($u['password_hash']) {
            header("Location: login_usuario.php?paso=password&email=" . urlencode($email) . ($redir ? '&redir=' . urlencode($redir) : ''));
            exit;
          } else {
            header("Location: login_usuario.php?paso=codigo&email=" . urlencode($email) . ($redir ? '&redir=' . urlencode($redir) : ''));
            exit;
          }
        } else {
          header("Location: registro_usuario.php?email=" . urlencode($email));
          exit;
        }
      }
    }

    if ($_POST['accion'] === 'login_password') {
      if ($bloqueado_pw) {
        $restante = $tiempo_bloqueo_pw - $tiempo_actual;
        $m = floor($restante / 60);
        $s = $restante % 60;
        $error = "Demasiados intentos. Intenta de nuevo en {$m}m {$s}s.";
        $paso = 'password';
        $email_param = trim($_POST['email'] ?? '');
      } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $stmt_check = $conexion->prepare("SELECT * FROM usuarios_publicos WHERE email = ?");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $res = $stmt_check->get_result();
        if ($res && $res->num_rows === 1) {
          $u = $res->fetch_assoc();
          if ($u['verificado'] == 0) {
            $_SESSION['pub_intentos_pw']++;
            $_SESSION['pub_ultimo_intento_pw'] = time();
            $error = 'Contraseña o cuenta incorrecta.';
            $paso = 'email';
          } elseif (password_verify($password, $u['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['usuario_publico_id'] = $u['id'];
            $_SESSION['usuario_publico_nombre'] = $u['nombre'];
            $_SESSION['usuario_publico_foto'] = $u['foto_perfil'];
            $_SESSION['pub_intentos_pw'] = 0;
            $_SESSION['pub_ultimo_intento_pw'] = 0;
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $disp = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $stmt_insert = $conexion->prepare("INSERT INTO sesiones_usuario (id_usuario_publico, ip, dispositivo) VALUES (?, ?, ?)");
            $stmt_insert->bind_param("iss", $u['id'], $ip, $disp);
            $stmt_insert->execute();
            $destino = $redir ? urldecode($redir) : 'mi_cuenta.php';
            header("Location: $destino");
            exit;
          } else {
            $_SESSION['pub_intentos_pw']++;
            $_SESSION['pub_ultimo_intento_pw'] = time();
            $error = 'Contraseña incorrecta.';
            $paso = 'password';
            $email_param = $email;
          }
        } else {
          $_SESSION['pub_intentos_pw']++;
          $_SESSION['pub_ultimo_intento_pw'] = time();
          $error = 'Contraseña o cuenta incorrecta.';
          $paso = 'email';
        }
      }
    }

    if ($_POST['accion'] === 'verificar_codigo') {
      if (!isset($_SESSION['pub_intentos_otp']))
        $_SESSION['pub_intentos_otp'] = 0;
      $email = trim($_POST['email'] ?? '');
      $codigo = trim($_POST['codigo'] ?? '');
      $stmt_check = $conexion->prepare("SELECT * FROM usuarios_publicos WHERE email = ?");
      $stmt_check->bind_param("s", $email);
      $stmt_check->execute();
      $res = $stmt_check->get_result();
      if ($res && $res->num_rows === 1) {
        $u = $res->fetch_assoc();
        if ($_SESSION['pub_intentos_otp'] >= 5) {
          $stmt_upd = $conexion->prepare("UPDATE usuarios_publicos SET codigo_verificacion=NULL, codigo_expira=NULL WHERE email=?");
          $stmt_upd->bind_param("s", $email);
          $stmt_upd->execute();
          $_SESSION['pub_intentos_otp'] = 0;
          $error = 'Demasiados intentos. El código actual ha sido invalidado de forma automática por seguridad. Solicita uno nuevo.';
          $paso = 'codigo';
          $email_param = $email;
        } else {
          if ($codigo === ($u['codigo_verificacion'] ?? '') && time() < ($u['codigo_expira'] ?? 0)) {
            $stmt_upd = $conexion->prepare("UPDATE usuarios_publicos SET verificado=1, codigo_verificacion=NULL, codigo_expira=NULL WHERE email=?");
            $stmt_upd->bind_param("s", $email);
            $stmt_upd->execute();
            session_regenerate_id(true);
            $_SESSION['usuario_publico_id'] = $u['id'];
            $_SESSION['usuario_publico_nombre'] = $u['nombre'];
            $_SESSION['usuario_publico_foto'] = $u['foto_perfil'];
            $_SESSION['login_bypass_pw'] = true;
            $_SESSION['pub_intentos_otp'] = 0;
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $disp = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $stmt_insert = $conexion->prepare("INSERT INTO sesiones_usuario (id_usuario_publico, ip, dispositivo) VALUES (?, ?, ?)");
            $stmt_insert->bind_param("iss", $u['id'], $ip, $disp);
            $stmt_insert->execute();
            $destino = $redir ? urldecode($redir) : 'mi_cuenta.php';
            header("Location: $destino");
            exit;
          } else {
            $_SESSION['pub_intentos_otp']++;
            $error = 'Código incorrecto o expirado. Te quedan ' . (5 - $_SESSION['pub_intentos_otp']) . ' intentos.';
            $paso = 'codigo';
            $email_param = $email;
          }
        }
      }
    }

    if ($_POST['accion'] === 'enviar_codigo') {
      $email = trim($_POST['email'] ?? '');

      if (!isset($_SESSION['envios_codigo'])) {
        $_SESSION['envios_codigo'] = [
          'count_30min' => 0,
          'inicio_30min' => time(),
          'count_24h' => 0,
          'inicio_24h' => time()
        ];
      }

      $envios = &$_SESSION['envios_codigo'];
      $tiempo_actual = time();

      if ($tiempo_actual - $envios['inicio_30min'] > 1800) {
        $envios['count_30min'] = 0;
        $envios['inicio_30min'] = $tiempo_actual;
      }

      if ($tiempo_actual - $envios['inicio_24h'] > 86400) {
        $envios['count_24h'] = 0;
        $envios['inicio_24h'] = $tiempo_actual;
      }

      if ($envios['count_24h'] >= 6) {
        $error = "Has solicitado demasiados códigos. Por seguridad, inténtalo más tarde.";
        $paso = 'codigo';
        $email_param = $email;
      } elseif ($envios['count_30min'] >= 3) {
        $error = "Has solicitado demasiados códigos. Por seguridad, inténtalo más tarde.";
        $paso = 'codigo';
        $email_param = $email;
      } else {
        $envios['count_30min']++;
        $envios['count_24h']++;

        $stmt_check = $conexion->prepare("SELECT id, nombre FROM usuarios_publicos WHERE email = ?");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $res = $stmt_check->get_result();

        if ($res && $res->num_rows === 1) {
          $u = $res->fetch_assoc();
          $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
          $expira = time() + 600;
          $stmt_upd = $conexion->prepare("UPDATE usuarios_publicos SET codigo_verificacion=?, codigo_expira=? WHERE email=?");
          $stmt_upd->bind_param("sis", $codigo, $expira, $email);
          $stmt_upd->execute();
          $nombre_u = $u['nombre'];
          $asunto = 'Tu código de acceso - Guía Empresarial';
          $mensaje = "Hola $nombre_u,\n\nTu código de acceso es: $codigo\n\nVálido por 10 minutos.\n\nSi no solicitaste esto, ignora este correo.";
          enviarCorreo($email, $nombre_u, $asunto, $mensaje);
        }

        header("Location: login_usuario.php?paso=codigo&email=" . urlencode($email) . ($redir ? '&redir=' . urlencode($redir) : ''));
        exit;
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
  <meta name="robots" content="noindex, nofollow">
  <link rel="icon" href="assets/img/image.png" type="image/png">
  <link
    href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Instrument+Serif:ital@0;1&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="assets/css/login_usuario.css">
</head>

<body>

  <div class="log-card">
    <div class="log-card-stripe"></div>
    <div class="log-inner">

      <div class="log-brand">
        <img src="assets/img/image.png" alt="Logo" class="log-brand-logo">
        <span class="log-brand-name">Guía Empresarial</span>
      </div>

      <?php if ($error): ?>
        <div class="log-alert log-alert-error">
          <i class="bi bi-exclamation-circle-fill"></i>
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>
      <?php if ($exito): ?>
        <div class="log-alert log-alert-ok">
          <i class="bi bi-check-circle-fill"></i>
          <?= htmlspecialchars($exito) ?>
        </div>
      <?php endif; ?>

      <?php if ($paso === 'email'): ?>
        <a href="index.php" class="log-home-link">
          <i class="bi bi-arrow-left"></i> Volver al inicio
        </a>

        <h1 class="log-title">Bienvenido<br>de <span>vuelta</span></h1>
        <p class="log-subtitle">Ingresa tu correo para continuar. Si no tienes cuenta, la crearemos automáticamente.</p>

        <form method="POST">
          <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
          <input type="hidden" name="accion" value="check_email">
          <?php if ($redir): ?>
            <input type="hidden" name="redir" value="<?= htmlspecialchars($redir) ?>">
          <?php endif; ?>
          <div class="log-field">
            <label>Correo electrónico</label>
            <input type="email" name="email" placeholder="tucorreo@gmail.com" required
              value="<?= htmlspecialchars($email_param) ?>" autocomplete="email">
          </div>
          <button type="submit" class="log-btn">Continuar →</button>
        </form>

        <div class="log-footer-link" style="margin-top:24px;">
          ¿Primera vez aquí? <a href="registro_usuario.php">Crea tu cuenta gratis</a>
        </div>

      <?php elseif ($paso === 'password'): ?>
        <div style="display:flex; gap:10px; margin-bottom:28px;">
          <a href="index.php" class="log-back"><i class="bi bi-house"></i> Inicio</a>
          <a href="login_usuario.php<?= $redir ? '?redir=' . urlencode($redir) : '' ?>" class="log-back">
            <i class="bi bi-arrow-left"></i> Volver
          </a>
        </div>

        <h1 class="log-title">Ingresa tu<br><span>contraseña</span></h1>

        <div class="log-email-badge">
          <i class="bi bi-envelope-fill"></i>
          <?= htmlspecialchars($email_param) ?>
          <a href="login_usuario.php<?= $redir ? '?redir=' . urlencode($redir) : '' ?>">✕</a>
        </div>

        <form method="POST">
          <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
          <input type="hidden" name="accion" value="login_password">
          <input type="hidden" name="email" value="<?= htmlspecialchars($email_param) ?>">
          <?php if ($redir): ?>
            <input type="hidden" name="redir" value="<?= htmlspecialchars($redir) ?>">
          <?php endif; ?>
          <div class="log-field log-field-pw">
            <label>Contraseña</label>
            <input type="password" name="password" id="pw-input" placeholder="••••••••" required
              autocomplete="current-password">
            <button type="button" class="pw-eye" onclick="togglePw()">
              <i class="bi bi-eye" id="pw-icon"></i>
            </button>
          </div>
          <button type="submit" class="log-btn">Entrar</button>
        </form>

        <div class="log-divider">o</div>

        <form method="POST">
          <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
          <input type="hidden" name="accion" value="enviar_codigo">
          <input type="hidden" name="email" value="<?= htmlspecialchars($email_param) ?>">
          <?php if ($redir): ?>
            <input type="hidden" name="redir" value="<?= htmlspecialchars($redir) ?>">
          <?php endif; ?>
          <button type="submit" class="log-btn-outline">
            <i class="bi bi-envelope"></i> Recibir código por correo
          </button>
        </form>

      <?php elseif ($paso === 'codigo'): ?>
        <div style="display:flex; gap:10px; margin-bottom:28px;">
          <a href="index.php" class="log-back"><i class="bi bi-house"></i> Inicio</a>
          <a href="login_usuario.php<?= $redir ? '?redir=' . urlencode($redir) : '' ?>" class="log-back">
            <i class="bi bi-arrow-left"></i> Volver
          </a>
        </div>

        <h1 class="log-title">Revisa tu<br><span>correo</span></h1>
        <p class="log-subtitle">Te enviamos un código de 6 dígitos a:</p>

        <div class="log-email-badge">
          <i class="bi bi-envelope-fill"></i>
          <?= htmlspecialchars($email_param) ?>
        </div>

        <form method="POST" id="form-otp">
          <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
          <input type="hidden" name="accion" value="verificar_codigo">
          <input type="hidden" name="email" value="<?= htmlspecialchars($email_param) ?>">
          <?php if ($redir): ?>
            <input type="hidden" name="redir" value="<?= htmlspecialchars($redir) ?>">
          <?php endif; ?>
          <input type="hidden" name="codigo" id="otp-hidden">

          <div class="otp-group" id="otp-group">
            <?php for ($i = 0; $i < 6; $i++): ?>
              <input class="otp-input" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"
                autocomplete="one-time-code">
            <?php endfor; ?>
          </div>

          <button type="submit" class="log-btn">Verificar código</button>
        </form>

        <div class="log-resend">
          ¿No llegó el código?
          <form method="POST" style="display:inline;">
            <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
            <input type="hidden" name="accion" value="enviar_codigo">
            <input type="hidden" name="email" value="<?= htmlspecialchars($email_param) ?>">
            <?php if ($redir): ?>
              <input type="hidden" name="redir" value="<?= htmlspecialchars($redir) ?>">
            <?php endif; ?>
            <button type="submit">Enviar nuevo código</button>
          </form>
        </div>
        <p style="text-align:center; margin-top:8px; font-size:12px; color:var(--ink-muted);">
          Puedes solicitar un nuevo código cuantas veces necesites.
        </p>

      <?php endif; ?>

    </div>
  </div>

  <script>
    function togglePw() {
      const input = document.getElementById('pw-input');
      const icon = document.getElementById('pw-icon');
      input.type = input.type === 'password' ? 'text' : 'password';
      icon.className = input.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
    }

    const otpInputs = document.querySelectorAll('.otp-input');
    const otpHidden = document.getElementById('otp-hidden');

    otpInputs.forEach((inp, idx) => {
      inp.addEventListener('input', e => {
        e.target.value = e.target.value.replace(/\D/g, '').slice(-1);
        e.target.classList.toggle('filled', !!e.target.value);
        if (e.target.value && idx < otpInputs.length - 1) otpInputs[idx + 1].focus();
        collectOTP(); checkAutoSubmit();
      });
      inp.addEventListener('keydown', e => {
        if (e.key === 'Backspace' && !e.target.value && idx > 0) {
          otpInputs[idx - 1].focus();
          otpInputs[idx - 1].classList.remove('filled');
        }
      });
      inp.addEventListener('paste', e => {
        e.preventDefault();
        const pasted = e.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
        pasted.split('').forEach((ch, i) => {
          if (otpInputs[i]) { otpInputs[i].value = ch; otpInputs[i].classList.add('filled'); }
        });
        collectOTP();
        const next = otpInputs[Math.min(pasted.length, 5)];
        if (next) next.focus();
        checkAutoSubmit();
      });
    });

    function collectOTP() {
      if (!otpHidden) return;
      otpHidden.value = Array.from(otpInputs).map(i => i.value).join('');
    }
    function checkAutoSubmit() {
      const val = Array.from(otpInputs).map(i => i.value).join('');
      if (val.length === 6) { collectOTP(); setTimeout(() => document.getElementById('form-otp')?.submit(), 350); }
    }
  </script>
</body>

</html>