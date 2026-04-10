<?php
if (!isset($seo_title))
  $seo_title = "Guía Empresarial";
if (!isset($seo_description))
  $seo_description = "Descubre, conecta y potencia empresas de tu región con Guía Empresarial.";
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
if (!isset($seo_image))
  $seo_image = APP_URL . "/assets/img/image.png";
if (!isset($seo_url))
  $seo_url = $protocol . "://" . $domain . $_SERVER['REQUEST_URI'];
if (!isset($seo_robots))
  $seo_robots = "index, follow";
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title><?= htmlspecialchars($seo_title) ?></title>
  <meta name="description" content="<?= htmlspecialchars($seo_description) ?>">
  <meta name="robots" content="<?= htmlspecialchars($seo_robots) ?>">

  <meta property="og:type" content="website">
  <meta property="og:url" content="<?= htmlspecialchars($seo_url) ?>">
  <meta property="og:title" content="<?= htmlspecialchars($seo_title) ?>">
  <meta property="og:description" content="<?= htmlspecialchars($seo_description) ?>">
  <meta property="og:image" content="<?= htmlspecialchars($seo_image) ?>">

  <meta property="twitter:card" content="summary_large_image">
  <meta property="twitter:url" content="<?= htmlspecialchars($seo_url) ?>">
  <meta property="twitter:title" content="<?= htmlspecialchars($seo_title) ?>">
  <meta property="twitter:description" content="<?= htmlspecialchars($seo_description) ?>">
  <meta property="twitter:image" content="<?= htmlspecialchars($seo_image) ?>">

  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/mi_cuenta.css">
  <link rel="icon" href="<?= APP_URL ?>/assets/img/image.png" type="image/png">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="manifest" href="<?= APP_URL ?>/manifest.json">
  <meta name="theme-color" content="#0d6efd">
  <script>window.csrfToken = '<?php echo function_exists('generarTokenCSRF') ? generarTokenCSRF() : ""; ?>';</script>
</head>

<body>

  <div class="nav-overlay" id="nav-overlay"></div>

  <header class="main-header">
    <div class="nav-container">

      <div class="brand">
        <img src="<?= APP_URL ?>/assets/img/image.png" alt="Guía Empresarial" class="logo">
        <span class="brand-name">GUÍA EMPRESARIAL</span>
      </div>

      <button class="nav-toggle" id="nav-toggle" aria-label="Abrir menú">
        <i class="bi bi-list"></i>
      </button>

      <div class="nav-actions" id="nav-actions">
        <?php
        if (session_status() === PHP_SESSION_NONE)
          session_start();
        ?>
        <nav class="nav-links">
          <a href="<?= APP_URL ?>/index.php" class="nav-link">Inicio</a>
          <a href="<?= APP_URL ?>/index.php#empresas" class="nav-link">Empresas</a>
          <a href="<?= APP_URL ?>/index.php#categorias" class="nav-link">Categorías</a>
          <a href="<?= APP_URL ?>/index.php#contacto" class="nav-link">Contacto</a>
          <?php if (isset($_SESSION['usuario_publico_id'])): ?>
            <a href="<?= APP_URL ?>/mi_cuenta.php" class="nav-link" style="display:flex;align-items:center;gap:8px;">
              <?php if (!empty($_SESSION['usuario_publico_foto'])): ?>
                <img src="<?= APP_URL ?>/assets/img/avatars/<?= htmlspecialchars($_SESSION['usuario_publico_foto']) ?>" alt="Foto perfil"
                  style="width:24px;height:24px;border-radius:50%;object-fit:cover;">
              <?php else: ?>
                👤
              <?php endif; ?>
              <?= htmlspecialchars($_SESSION['usuario_publico_nombre']) ?>
            </a>

          <?php else: ?>
            <a href="<?= APP_URL ?>/login_usuario.php" class="nav-link">Ingresar</a>
          <?php endif; ?>
        </nav>
        <label class="theme-switch">
          <input type="checkbox" id="toggle-theme">
          <span class="slider"></span>
        </label>
      </div>

    </div>
  </header>

  <main>

    <script>
      const navToggle = document.getElementById('nav-toggle');
      const navActions = document.getElementById('nav-actions');
      const navOverlay = document.getElementById('nav-overlay');
      const toggleIcon = navToggle.querySelector('i');

      function abrirMenu() {
        navActions.classList.add('open');
        navOverlay.classList.add('open');
        toggleIcon.className = 'bi bi-x';
        document.body.style.overflow = 'hidden';
      }

      function cerrarMenu() {
        navActions.classList.remove('open');
        navOverlay.classList.remove('open');
        toggleIcon.className = 'bi bi-list';
        document.body.style.overflow = '';
      }

      navToggle.addEventListener('click', () => {
        navActions.classList.contains('open') ? cerrarMenu() : abrirMenu();
      });

      navOverlay.addEventListener('click', cerrarMenu);

      document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', cerrarMenu);
      });
    </script>