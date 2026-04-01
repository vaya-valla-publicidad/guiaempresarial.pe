<?php
session_start();
include 'db.php';
include_once 'libs/mailer.php';

if (isset($_SESSION['usuario_publico_id'])) {
  header('Location: mi_cuenta.php');
  exit;
}

$error = '';
$exito = '';
$paso = $_GET['paso'] ?? 'registro';
$email_param = $_GET['email'] ?? '';

$conexion->query("DELETE FROM usuarios_publicos WHERE verificado = 0 AND codigo_expira < " . time());

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {

  if ($_POST['accion'] === 'registrar') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if (!$nombre || !$email || !$password || !$confirm) {
      $error = 'Por favor completa todos los campos.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $error = 'El correo no es válido.';
    } elseif (strlen($password) < 6) {
      $error = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif ($password !== $confirm) {
      $error = 'Las contraseñas no coinciden.';
    } else {
      $stmt = $conexion->prepare("SELECT id, verificado FROM usuarios_publicos WHERE email = ?");
      $stmt->bind_param("s", $email);
      $stmt->execute();
      $check = $stmt->get_result();
      if ($check && $check->num_rows > 0) {
        $fila_check = $check->fetch_assoc();
        if ($fila_check['verificado'] == 1) {
          $error = 'Ya existe una cuenta verificada con ese correo.';
        } else {
          $conexion->query("DELETE FROM usuarios_publicos WHERE id = " . intval($fila_check['id']));
        }
      }

      if (!$error) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expira = time() + 600;
        $stmt_insert = $conexion->prepare("INSERT INTO usuarios_publicos (nombre, email, password_hash, codigo_verificacion, codigo_expira, verificado) VALUES (?, ?, ?, ?, ?, 0)");
        $stmt_insert->bind_param("ssssi", $nombre, $email, $hash, $codigo, $expira);
        $stmt_insert->execute();

        $asunto = 'Confirma tu cuenta - Guía Empresarial';
        $mensaje = "Hola $nombre,\n\nTu código de verificación es: $codigo\n\nVálido por 10 minutos.\n\nSi no solicitaste esto, ignora este correo.";
        enviarCorreo($email, $nombre, $asunto, $mensaje);

        header("Location: registro_usuario.php?paso=verificar&email=" . urlencode($email));
        exit;
      }
    }
  }

  if ($_POST['accion'] === 'verificar_codigo') {
    if (!isset($_SESSION['pub_intentos_otp']))
      $_SESSION['pub_intentos_otp'] = 0;
    $email = trim($_POST['email'] ?? '');
    $codigo = trim($_POST['codigo'] ?? '');
    $is_ajax = isset($_POST['ajax']);

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
        $msg = 'Demasiados intentos. El código actual ha sido invalidado. Solicita uno nuevo.';
        if ($is_ajax) {
          echo json_encode(['success' => false, 'error' => $msg, 'invalidated' => true]);
          exit;
        }
        $error = $msg;
        $paso = 'verificar';
        $email_param = $email;
      } else {
        $codigo_guardado = $u['codigo_verificacion'] ?? '';
        $expira = $u['codigo_expira'] ?? 0;
        if ($codigo === $codigo_guardado && time() < $expira) {
          $stmt_upd = $conexion->prepare("UPDATE usuarios_publicos SET verificado=1, codigo_verificacion=NULL, codigo_expira=NULL WHERE email=?");
          $stmt_upd->bind_param("s", $email);
          $stmt_upd->execute();
          $_SESSION['usuario_publico_id'] = $u['id'];
          $_SESSION['usuario_publico_nombre'] = $u['nombre'];
          $_SESSION['usuario_publico_foto'] = $u['foto_perfil'];
          $_SESSION['pub_intentos_otp'] = 0;
          if ($is_ajax) {
            echo json_encode(['success' => true, 'redirect' => 'mi_cuenta.php']);
            exit;
          }
          header('Location: mi_cuenta.php');
          exit;
        } else {
          $_SESSION['pub_intentos_otp']++;
          $msg = 'Código incorrecto o expirado. Te quedan ' . (5 - $_SESSION['pub_intentos_otp']) . ' intentos.';
          if ($is_ajax) {
            echo json_encode(['success' => false, 'error' => $msg]);
            exit;
          }
          $error = $msg;
          $paso = 'verificar';
          $email_param = $email;
        }
      }
    } else {
      if ($is_ajax) {
        echo json_encode(['success' => false, 'error' => 'Usuario no encontrado o ya verificado.']);
        exit;
      }
    }
  }

  if ($_POST['accion'] === 'reenviar_codigo') {
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
    
    if ($envios['count_24h'] >= 6 || $envios['count_30min'] >= 3) {
      $exito = 'Has solicitado demasiados códigos. Por seguridad, inténtalo más tarde.';
    } else {
      $envios['count_30min']++;
      $envios['count_24h']++;
      
      $stmt_check = $conexion->prepare("SELECT id, nombre FROM usuarios_publicos WHERE email = ? AND verificado=0");
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
        $asunto = 'Nuevo código de verificación - Guía Empresarial';
        $mensaje = "Hola $nombre_u,\n\nTu nuevo código de verificación es: $codigo\n\nVálido por 10 minutos.\n\nSi no solicitaste esto, ignora este correo.";
        enviarCorreo($email, $nombre_u, $asunto, $mensaje);
        $exito = 'Nuevo código enviado a tu correo.';
      } else {
        $exito = 'Código reenviado (si el correo es válido).';
      }
    }
    
    $paso = 'verificar';
    $email_param = $email;
  }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Crear cuenta — Guía Empresarial</title>
  <link rel="icon" href="assets/img/image.png" type="image/png">
  <link
    href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Instrument+Serif:ital@0;1&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="assets/css/registro_usuario.css">
</head>

<body>

  <div class="reg-card">
    <div class="reg-card-stripe"></div>
    <div class="reg-inner">

      <div class="reg-brand">
        <img src="assets/img/image.png" alt="Logo" class="reg-brand-logo">
        <span class="reg-brand-name">Guía Empresarial</span>
      </div>

      <div class="reg-steps-bar">
        <div class="reg-step-pip <?= $paso !== 'registro' ? 'done' : 'active' ?>"></div>
        <div class="reg-step-sep <?= $paso !== 'registro' ? 'done-sep' : '' ?>"></div>
        <div class="reg-step-pip <?= $paso === 'verificar' ? 'active' : '' ?>"></div>
        <div class="reg-step-sep" id="final-sep"></div>
        <div class="reg-step-pip" id="final-pip"></div>
      </div>

      <?php if ($error): ?>
        <div class="reg-alert reg-alert-error">
          <i class="bi bi-exclamation-circle-fill"></i>
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>
      <?php if ($exito): ?>
        <div class="reg-alert reg-alert-ok">
          <i class="bi bi-check-circle-fill"></i>
          <?= htmlspecialchars($exito) ?>
        </div>
      <?php endif; ?>

      <?php if ($paso === 'registro'): ?>

        <a href="index.php" class="reg-home-link">
          <i class="bi bi-arrow-left"></i> Volver al inicio
        </a>

        <h1 class="reg-title">Crea tu<br><span>cuenta gratis</span></h1>
        <p class="reg-subtitle">Únete y empieza a dejar reseñas en negocios locales.</p>

        <form method="POST">
          <input type="hidden" name="accion" value="registrar">

          <div class="reg-field">
            <label>Nombre completo</label>
            <input type="text" name="nombre" placeholder="Tu nombre completo" required maxlength="100"
              value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
          </div>

          <div class="reg-field">
            <label>Correo electrónico</label>
            <input type="email" name="email" placeholder="tucorreo@gmail.com" required
              value="<?= htmlspecialchars($_POST['email'] ?? $email_param) ?>">
          </div>

          <div class="reg-field reg-field-pw">
            <label>Contraseña <small style="font-weight:500;text-transform:none;letter-spacing:0;">(mín. 6
                caracteres)</small></label>
            <input type="password" name="password" id="pw1" placeholder="••••••••" required
              oninput="checkStrength(this.value)">
            <button type="button" class="pw-eye" onclick="togglePw('pw1','ico1')">
              <i class="bi bi-eye" id="ico1"></i>
            </button>
            <div class="pw-bars">
              <div class="pw-bar-seg" id="seg1"></div>
              <div class="pw-bar-seg" id="seg2"></div>
              <div class="pw-bar-seg" id="seg3"></div>
              <div class="pw-bar-seg" id="seg4"></div>
            </div>
            <div class="pw-hint-txt" id="pw-hint"></div>
          </div>

          <div class="reg-field reg-field-pw">
            <label>Confirmar contraseña</label>
            <input type="password" name="confirm" id="pw2" placeholder="••••••••" required>
            <button type="button" class="pw-eye" onclick="togglePw('pw2','ico2')">
              <i class="bi bi-eye" id="ico2"></i>
            </button>
          </div>

          <button type="submit" class="reg-btn">Crear cuenta →</button>
        </form>

        <p class="reg-terms">
          Al registrarte aceptas nuestros <a href="#">Términos</a> y <a href="#">Política de privacidad</a>.
        </p>
        <div class="reg-footer-link">
          ¿Ya tienes cuenta? <a href="login_usuario.php">Inicia sesión</a>
        </div>

      <?php elseif ($paso === 'verificar'): ?>
        <div style="display:flex; gap:10px; margin-bottom:28px;">
          <a href="index.php" class="reg-back-btn">
            <i class="bi bi-house"></i> Inicio
          </a>
          <a href="registro_usuario.php" class="reg-back-btn">
            <i class="bi bi-arrow-left"></i> Volver al registro
          </a>
        </div>

        <h1 class="reg-title">Revisa tu<br><span>correo</span></h1>
        <p class="reg-subtitle">Ingresa el código de 6 dígitos que enviamos a:</p>

        <div class="reg-email-badge">
          <i class="bi bi-envelope-fill"></i>
          <?= htmlspecialchars($email_param) ?>
        </div>

        <form method="POST" id="form-otp">
          <input type="hidden" name="accion" value="verificar_codigo">
          <input type="hidden" name="email" value="<?= htmlspecialchars($email_param) ?>">
          <input type="hidden" name="codigo" id="otp-hidden">

          <div class="otp-group" id="otp-group">
            <?php for ($i = 0; $i < 6; $i++): ?>
              <input class="otp-input" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"
                autocomplete="one-time-code">
            <?php endfor; ?>
          </div>

          <button type="submit" class="reg-btn">Confirmar cuenta ✓</button>
        </form>

        <div class="reg-resend">
          ¿No llegó el código?
          <form method="POST" style="display:inline;">
            <input type="hidden" name="accion" value="reenviar_codigo">
            <input type="hidden" name="email" value="<?= htmlspecialchars($email_param) ?>">
            <button type="submit">Enviar nuevo código</button>
          </form>
        </div>

        <p style="text-align:center; margin-top:10px; font-size:12px; color:var(--ink-muted);">
          Puedes solicitar un nuevo código cuantas veces necesites.
        </p>

      <?php endif; ?>

    </div>
  </div>

  <style>
    @keyframes spin {
      0% {
        transform: rotate(0deg);
      }

      100% {
        transform: rotate(360deg);
      }
    }

    .spinner {
      display: inline-block;
      width: 16px;
      height: 16px;
      border: 2px solid rgba(255, 255, 255, 0.3);
      border-radius: 50%;
      border-top-color: #fff;
      animation: spin 1s ease-in-out infinite;
      vertical-align: middle;
      margin-right: 8px;
    }

    .success-anim {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 50px;
    }

    .checkmark__circle {
      stroke-dasharray: 166;
      stroke-dashoffset: 166;
      stroke-width: 2;
      stroke-miterlimit: 10;
      stroke: var(--emerald);
      fill: none;
      animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
    }

    .checkmark {
      width: 56px;
      height: 56px;
      border-radius: 50%;
      display: block;
      stroke-width: 2;
      stroke: #fff;
      stroke-miterlimit: 10;
      margin: 0 auto;
      box-shadow: inset 0px 0px 0px var(--emerald);
      animation: fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both;
    }

    .checkmark__check {
      transform-origin: 50% 50%;
      stroke-dasharray: 48;
      stroke-dashoffset: 48;
      animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
    }

    @keyframes stroke {
      100% {
        stroke-dashoffset: 0;
      }
    }

    @keyframes scale {

      0%,
      100% {
        transform: none;
      }

      50% {
        transform: scale3d(1.1, 1.1, 1);
      }
    }

    @keyframes fill {
      100% {
        box-shadow: inset 0px 0px 0px 30px var(--emerald);
      }
    }

    .done-sep {
      background-color: var(--primary) !important;
    }
  </style>
  <script>
    function togglePw(id, ico) {
      const input = document.getElementById(id);
      const icon = document.getElementById(ico);
      input.type = input.type === 'password' ? 'text' : 'password';
      icon.className = input.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
    }

    function checkStrength(val) {
      const segs = ['seg1', 'seg2', 'seg3', 'seg4'].map(id => document.getElementById(id));
      const hint = document.getElementById('pw-hint');
      segs.forEach(s => { s.className = 'pw-bar-seg'; });
      if (!val) { hint.textContent = ''; return; }
      let score = 0;
      if (val.length >= 6) score++;
      if (val.length >= 10) score++;
      if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
      if (/[0-9]/.test(val) || /[^A-Za-z0-9]/.test(val)) score++;
      const cls = score <= 1 ? 'weak' : score <= 3 ? 'medium' : 'strong';
      const labels = { weak: 'Débil', medium: 'Regular', strong: 'Segura' };
      const colors = { weak: '#f43f5e', medium: '#f59e0b', strong: '#22c55e' };
      for (let i = 0; i < score; i++) segs[i].classList.add(cls);
      hint.textContent = 'Contraseña ' + labels[cls];
      hint.style.color = colors[cls];
    }

    const otpInputs = document.querySelectorAll('.otp-input');
    const otpHidden = document.getElementById('otp-hidden');

    otpInputs.forEach((inp, idx) => {
      inp.addEventListener('input', e => {
        e.target.value = e.target.value.replace(/\D/g, '').slice(-1);
        e.target.classList.toggle('filled', !!e.target.value);
        if (e.target.value && idx < otpInputs.length - 1) otpInputs[idx + 1].focus();
        collectOTP();
        checkAutoSubmit();
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
      if (val.length === 6) {
        collectOTP();

        const form = document.getElementById('form-otp');
        const formData = new FormData(form);
        formData.append('ajax', '1');

        const otpGroup = document.getElementById('otp-group');
        const btn = form.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span> Verificando...';
        otpGroup.style.opacity = '0.7';
        otpGroup.style.pointerEvents = 'none';

        fetch('registro_usuario.php', { method: 'POST', body: formData })
          .then(r => r.json())
          .then(data => {
            if (data.success) {
              document.getElementById('final-sep').classList.add('done-sep');
              document.getElementById('final-pip').classList.add('active');

              otpGroup.innerHTML = `
             <div class="success-anim">
               <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                 <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/>
                 <path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
               </svg>
             </div>`;
              otpGroup.style.opacity = '1';
              btn.style.display = 'none';

              document.querySelector('.reg-subtitle').innerHTML = '<span style="color:var(--emerald);font-weight:600;">¡Tu cuenta está lista!</span> Redirigiendo...';

              setTimeout(() => { window.location.href = data.redirect; }, 1600);
            } else {
              btn.disabled = false;
              btn.innerHTML = 'Confirmar cuenta ✓';
              otpGroup.style.opacity = '1';
              otpGroup.style.pointerEvents = 'auto';
              otpInputs.forEach(i => { i.value = ''; i.classList.remove('filled'); });
              otpInputs[0].focus();

              alert(data.error || 'Código incorrecto. Intenta de nuevo.');
              if (data.invalidated) window.location.reload();
            }
          })
          .catch(err => {
            form.submit();
          });
      }
    }
  </script>
</body>

</html>