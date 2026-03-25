<?php
session_start();
include 'db.php';

$error = '';
$exito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if (!$nombre || !$email || !$password || !$confirm) {
        $error = 'Por favor completa todos los campos.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El correo no es válido.';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif ($password !== $confirm) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        $email_esc = $conexion->real_escape_string($email);
        $check = $conexion->query("SELECT id FROM usuarios_publicos WHERE email = '$email_esc'");
        if ($check && $check->num_rows > 0) {
            $error = 'Ya existe una cuenta con ese correo.';
        } else {
            $hash        = password_hash($password, PASSWORD_DEFAULT);
            $nombre_esc  = $conexion->real_escape_string($nombre);
            $conexion->query("INSERT INTO usuarios_publicos (nombre, email, password_hash)
                              VALUES ('$nombre_esc', '$email_esc', '$hash')");
            $exito = 'Cuenta creada. Ya puedes iniciar sesión.';
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<section class="page-section">
  <div class="container" style="max-width:480px;">
    <div class="auth-card">
      <h2 class="auth-title">Crear cuenta</h2>
      <p class="auth-sub">Regístrate para dejar reseñas en negocios locales</p>

      <?php if ($error): ?>
        <div class="resena-alerta resena-alerta-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <?php if ($exito): ?>
        <div class="resena-alerta resena-alerta-ok"><?= $exito ?> <a href="login_usuario.php">Ir al login →</a></div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-group">
          <label>Nombre</label>
          <input type="text" name="nombre" placeholder="Tu nombre completo" required maxlength="100"
                 value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Correo electrónico</label>
          <input type="email" name="email" placeholder="tucorreo@gmail.com" required
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Contraseña <span style="font-weight:400;color:var(--muted)">(mín. 6 caracteres)</span></label>
          <input type="password" name="password" placeholder="••••••••" required>
        </div>
        <div class="form-group">
          <label>Confirmar contraseña</label>
          <input type="password" name="confirm" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn-enviar-resena" style="width:100%;margin-top:8px;">Crear cuenta</button>
      </form>

      <p class="auth-footer">¿Ya tienes cuenta? <a href="login_usuario.php">Inicia sesión</a></p>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>