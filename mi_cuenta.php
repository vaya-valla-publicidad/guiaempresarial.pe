<?php
session_start();
include 'db.php';

if (!isset($_SESSION['usuario_publico_id'])) {
    header('Location: login_usuario.php');
    exit;
}

$id_u   = intval($_SESSION['usuario_publico_id']);
$u      = $conexion->query("SELECT * FROM usuarios_publicos WHERE id = $id_u")->fetch_assoc();
$error  = '';
$exito  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
        // Borrar cuenta
    if ($_POST['accion'] === 'borrar_cuenta') {
        $conexion->query("DELETE FROM resenas WHERE id_usuario_publico = $id_u");
        if (!empty($u['foto_perfil']) && file_exists('assets/img/avatars/' . $u['foto_perfil'])) {
            unlink('assets/img/avatars/' . $u['foto_perfil']);
        }
        $conexion->query("DELETE FROM usuarios_publicos WHERE id = $id_u");
        session_destroy();
        header('Location: index.php');
        exit;
    }

    if ($_POST['accion'] === 'borrar_foto') {
        if (!empty($u['foto_perfil']) && file_exists('assets/img/avatars/' . $u['foto_perfil'])) {
            unlink('assets/img/avatars/' . $u['foto_perfil']); // elimina archivo físico
        }
        $conexion->query("UPDATE usuarios_publicos SET foto_perfil = NULL WHERE id = $id_u");
        $_SESSION['usuario_publico_foto'] = null;
        $u['foto_perfil'] = null;
        $exito = 'Foto de perfil eliminada.';
    }

    if ($_POST['accion'] === 'datos') {
        $nombre_nuevo = trim($_POST['nombre'] ?? '');
        if ($nombre_nuevo && $nombre_nuevo !== $u['nombre']) {
            $n = $conexion->real_escape_string($nombre_nuevo);
            $conexion->query("UPDATE usuarios_publicos SET nombre = '$n' WHERE id = $id_u");
            $_SESSION['usuario_publico_nombre'] = $nombre_nuevo;
            $exito = 'Nombre actualizado.';
            $u['nombre'] = $nombre_nuevo;
        }
    }

    if ($_POST['accion'] === 'password') {
        $actual  = $_POST['actual'] ?? '';
        $nueva   = $_POST['nueva'] ?? '';
        $confirm = $_POST['confirm'] ?? '';
        if (!password_verify($actual, $u['password_hash'])) {
            $error = 'La contraseña actual no es correcta.';
        } elseif (strlen($nueva) < 6) {
            $error = 'La nueva contraseña debe tener al menos 6 caracteres.';
        } elseif ($nueva !== $confirm) {
            $error = 'Las contraseñas nuevas no coinciden.';
        } else {
            $hash = password_hash($nueva, PASSWORD_DEFAULT);
            $conexion->query("UPDATE usuarios_publicos SET password_hash = '$hash' WHERE id = $id_u");
            $exito = 'Contraseña actualizada.';
        }
    }

    if ($_POST['accion'] === 'foto' && isset($_FILES['foto_perfil'])) {
        $file = $_FILES['foto_perfil'];
        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $permitidos = ['jpg','jpeg','png','webp'];
        if (!in_array($ext, $permitidos)) {
            $error = 'Solo se permiten imágenes JPG, PNG o WEBP.';
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $error = 'La imagen no puede superar 2MB.';
        } else {
            $nombre_archivo = 'avatar_' . $id_u . '_' . time() . '.' . $ext;
            $destino = 'assets/img/avatars/' . $nombre_archivo;
            if (!is_dir('assets/img/avatars')) mkdir('assets/img/avatars', 0755, true);
            if (move_uploaded_file($file['tmp_name'], $destino)) {
                if ($u['foto_perfil'] && file_exists('assets/img/avatars/' . $u['foto_perfil'])) {
                    unlink('assets/img/avatars/' . $u['foto_perfil']);
                }
                $fn = $conexion->real_escape_string($nombre_archivo);
                $conexion->query("UPDATE usuarios_publicos SET foto_perfil = '$fn' WHERE id = $id_u");
                $_SESSION['usuario_publico_foto'] = $nombre_archivo;
                $u['foto_perfil'] = $nombre_archivo;
                $exito = 'Foto de perfil actualizada.';
            } else {
                $error = 'No se pudo guardar la imagen.';
            }
        }
    }
}

$resenas_q = $conexion->query(
    "SELECT r.*, e.nombre AS empresa_nombre
     FROM resenas r
     JOIN empresas e ON r.id_empresa = e.id_empresa
     WHERE r.id_usuario_publico = $id_u
     ORDER BY r.fecha DESC"
);
$mis_resenas = [];
if ($resenas_q) while ($r = $resenas_q->fetch_assoc()) $mis_resenas[] = $r;

if (isset($_GET['eliminar_resena'])) {
    $id_r = intval($_GET['eliminar_resena']);
    $conexion->query("DELETE FROM resenas WHERE id_resena = $id_r AND id_usuario_publico = $id_u");
    header('Location: mi_cuenta.php');
    exit;
}
?>
<?php include 'includes/header.php'; ?>

<section class="page-section">
  <div class="container" style="max-width:700px;">

    <?php if ($error): ?>
      <div class="resena-alerta resena-alerta-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($exito): ?>
      <div class="resena-alerta resena-alerta-ok">✅ <?= htmlspecialchars($exito) ?></div>
    <?php endif; ?>

    <div class="auth-card">
      <div class="perfil-header">
        <?php if (!empty($u['foto_perfil'])): ?>
          <img src="assets/img/avatars/<?= htmlspecialchars($u['foto_perfil']) ?>" class="perfil-avatar" alt="Foto de perfil">
        <?php else: ?>
          <div class="perfil-avatar perfil-avatar-placeholder"><?= mb_strtoupper(mb_substr($u['nombre'], 0, 1)) ?></div>
        <?php endif; ?>
        <div class="perfil-info">
          <h2><?= htmlspecialchars($u['nombre']) ?></h2>
          <p><?= htmlspecialchars($u['email']) ?></p>
        </div>
        <a href="logout_usuario.php" class="btn-ver btn-cerrar-sesion">Cerrar sesión</a>
      </div>

      <form method="POST" enctype="multipart/form-data" class="perfil-section">
        <input type="hidden" name="accion" value="foto">
        <p class="perfil-section-label">Foto de perfil</p>
        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
          <input type="file" name="foto_perfil" accept="image/*" style="font-size:13px;">
          <button type="submit" class="btn-ver">Subir foto</button>
        </div>
      </form>

      <form method="POST" class="perfil-section">
        <input type="hidden" name="accion" value="borrar_foto">
        <p class="perfil-section-label">Acciones de foto</p>
        <button type="submit" class="btn-ver btn-ver-danger">Borrar foto</button>
      </form>

      <form method="POST" class="perfil-section">
        <input type="hidden" name="accion" value="datos">
        <p class="perfil-section-label">Cambiar nombre</p>
        <div class="form-group">
          <label>Nombre</label>
          <input type="text" name="nombre" value="<?= htmlspecialchars($u['nombre']) ?>" required maxlength="100">
        </div>
        <button type="submit" class="btn-ver">Guardar nombre</button>
      </form>

      <form method="POST" class="perfil-section">
        <input type="hidden" name="accion" value="password">
        <p class="perfil-section-label">Cambiar contraseña</p>
        <div class="form-group">
          <label>Contraseña actual</label>
          <input type="password" name="actual" placeholder="••••••••" required>
        </div>
        <div class="form-group">
          <label>Nueva contraseña</label>
          <input type="password" name="nueva" placeholder="••••••••" required>
        </div>
        <div class="form-group">
          <label>Confirmar nueva</label>
          <input type="password" name="confirm" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn-ver">Actualizar contraseña</button>
      </form>

      <form method="POST" class="perfil-section" onsubmit="return confirm('¿Seguro que deseas borrar tu cuenta? Esta acción no se puede deshacer.');">
        <input type="hidden" name="accion" value="borrar_cuenta">
        <p class="perfil-section-label">Acciones de cuenta</p>
        <button type="submit" class="btn-ver btn-ver-danger">Borrar cuenta</button>
      </form>
    </div>

    <div class="auth-card">
      <p class="perfil-section-label">Mis reseñas (<?= count($mis_resenas) ?>)</p>
      <?php if (empty($mis_resenas)): ?>
        <p style="color:var(--muted);font-size:14px;">Aún no has dejado ninguna reseña.</p>
      <?php else: ?>
        <div class="resenas-lista">
          <?php foreach ($mis_resenas as $r): ?>
          <div class="resena-item">
            <div class="resena-header">
              <div class="resena-avatar"><?= mb_strtoupper(mb_substr($u['nombre'], 0, 1)) ?></div>
              <div>
                <strong class="resena-empresa"><?= htmlspecialchars($r['empresa_nombre']) ?></strong>
                <div class="estrellas-display small">
                  <?php for ($i = 1; $i <= 5; $i++): ?>
                    <span class="<?= $i <= $r['estrellas'] ? 'estrella-llena' : 'estrella-vacia' ?>">★</span>
                  <?php endfor; ?>
                </div>
              </div>
              <span class="resena-fecha"><?= date('d/m/Y', strtotime($r['fecha'])) ?></span>
            </div>
            <p class="resena-comentario"><?= nl2br(htmlspecialchars($r['comentario'])) ?></p>
            <div style="margin-top:10px;display:flex;gap:8px;">
              <a href="empresas.php?empresa=<?= $r['id_empresa'] ?>" class="btn-ver" style="font-size:12px;padding:5px 12px;">Ver empresa</a>
              <a href="mi_cuenta.php?eliminar_resena=<?= $r['id_resena'] ?>"
                 class="btn-ver btn-ver-danger" style="font-size:12px;padding:5px 12px;"
                 onclick="return confirm('¿Eliminar esta reseña?')">Eliminar</a>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

  </div>
</section>

<?php include 'includes/footer.php'; ?>
