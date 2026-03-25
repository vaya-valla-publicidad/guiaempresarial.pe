<?php
session_start();
include 'db.php';

if (isset($_SESSION['usuario_publico_id'])) {
    header('Location: mi_cuenta.php');
    exit;
}

$error  = '';
$redir  = $_GET['redir'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Completa todos los campos.';
    } else {
        $email_esc = $conexion->real_escape_string($email);
        $res = $conexion->query("SELECT * FROM usuarios_publicos WHERE email = '$email_esc'");
        if ($res && $res->num_rows === 1) {
            $u = $res->fetch_assoc();
            if (password_verify($password, $u['password_hash'])) {
                $_SESSION['usuario_publico_id']     = $u['id'];
                $_SESSION['usuario_publico_nombre'] = $u['nombre'];
                $_SESSION['usuario_publico_foto']   = $u['foto_perfil'];
                $destino = $redir ? urldecode($redir) : 'mi_cuenta.php';
                header("Location: $destino");
                exit;
            } else {
                $error = 'Contraseña incorrecta.';
            }
        } else {
            $error = 'No existe una cuenta con ese correo.';
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<section class="page-section">
  <div class="container" style="max-width:480px;">
    <div class="auth-card">
      <h2 class="auth-title">Iniciar sesión</h2>
      <p class="auth-sub">Accede para dejar tu reseña</p>

      <?php if ($error): ?>
        <div class="resena-alerta resena-alerta-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-group">
          <label>Correo electrónico</label>
          <input type="email" name="email" placeholder="tucorreo@gmail.com" required
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Contraseña</label>
          <input type="password" name="password" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn-enviar-resena" style="width:100%;margin-top:8px;">Entrar</button>
      </form>

      <p class="auth-footer">¿No tienes cuenta? <a href="registro_usuario.php">Regístrate gratis</a></p>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>