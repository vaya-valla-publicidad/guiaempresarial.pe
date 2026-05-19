<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/security.php';
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

$user_favs = [];
if (isset($_SESSION['usuario_publico_id'])) {
  $id_u_fav = intval($_SESSION['usuario_publico_id']);
  $stmt_favs = $conexion->prepare("SELECT id_empresa FROM favoritos WHERE id_usuario_publico = ?");
  $stmt_favs->bind_param("i", $id_u_fav);
  $stmt_favs->execute();
  $stmt_favs = $stmt_favs->get_result();
  if ($stmt_favs) {
    while ($rf = $stmt_favs->fetch_assoc()) {
      $user_favs[] = (int) $rf['id_empresa'];
    }
  }
}
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

  <link rel="stylesheet"
    href="<?= APP_URL ?>/assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
  <link rel="stylesheet"
    href="<?= APP_URL ?>/assets/css/mi_cuenta.css?v=<?= filemtime(__DIR__ . '/../assets/css/mi_cuenta.css') ?>">
  <link rel="icon" href="<?= APP_URL ?>/assets/img/image.png" type="image/png">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="manifest" href="<?= APP_URL ?>/manifest.json">
  <meta name="theme-color" content="#0d6efd">
  <script>
    (function () {
      const theme = localStorage.getItem('theme');
      if (theme === 'dark') {
        document.documentElement.classList.add('dark-mode');
      }
    })();
    window.csrfToken = '<?php echo function_exists('generarTokenCSRF') ? generarTokenCSRF() : ""; ?>';
  </script>
  <script src="<?= APP_URL ?>/assets/js/toast.js"></script>
</head>

<body
  class="<?php echo (isset($_COOKIE['theme']) && htmlspecialchars($_COOKIE['theme'], ENT_QUOTES, 'UTF-8') === 'dark') ? 'dark-mode' : ''; ?>">
  <script>
    if (localStorage.getItem('theme') === 'dark') {
      document.body.classList.add('dark-mode');
    }
  </script>

  <div id="nprogress-bar"></div>

  <div class="nav-overlay" id="nav-overlay"></div>

  <header class="main-header">
    <div class="nav-container">

      <a href="<?= APP_URL ?>/" class="brand" style="text-decoration:none;">
        <img src="<?= APP_URL ?>/assets/img/image.png" alt="Guía Empresarial" class="logo">
        <span class="brand-name">GUÍA EMPRESARIAL</span>
      </a>

      <button class="nav-toggle" id="nav-toggle" aria-label="Abrir menú">
        <i class="bi bi-list"></i>
      </button>

      <div class="nav-actions" id="nav-actions">
        <?php
        if (session_status() === PHP_SESSION_NONE)
          session_start();
        ?>
        <nav class="nav-links">
          <a href="<?= APP_URL ?>/" class="nav-link">Inicio</a>
          <a href="<?= APP_URL ?>/?jump=empresas" class="nav-link"
            onclick="return handleNavClick(event, 'empresas')">Empresas</a>
          <a href="<?= APP_URL ?>/?jump=categorias" class="nav-link"
            onclick="return handleNavClick(event, 'categorias')">Categorías</a>
          <a href="<?= APP_URL ?>/?jump=contacto" class="nav-link"
            onclick="return handleNavClick(event, 'contacto')">Contacto</a>
          <?php if (isset($_SESSION['usuario_publico_id'])): ?>
            <a href="<?= APP_URL ?>/mi_cuenta" class="nav-link" style="display:flex;align-items:center;gap:8px;">
              <?php if (!empty($_SESSION['usuario_publico_foto'])): ?>
                <img src="<?= APP_URL ?>/assets/img/avatars/<?= htmlspecialchars($_SESSION['usuario_publico_foto']) ?>"
                  alt="Foto perfil" style="width:24px;height:24px;border-radius:50%;object-fit:cover;">
              <?php else: ?>
                👤
              <?php endif; ?>
              <?= htmlspecialchars($_SESSION['usuario_publico_nombre']) ?>
            </a>

          <?php else: ?>
            <a href="<?= APP_URL ?>/login_usuario" class="nav-link">Ingresar</a>
          <?php endif; ?>
        </nav>
        <label class="theme-switch" for="toggle-theme">
          <input type="checkbox" id="toggle-theme">
          <span class="slider">
            <i class="bi bi-sun-fill"></i>
            <i class="bi bi-moon-fill"></i>
          </span>
        </label>
      </div>

    </div>
  </header>

  <main>

    <script>
      const navToggle = document.getElementById('nav-toggle');
      const navActions = document.getElementById('nav-actions');
      const navOverlay = document.getElementById('nav-overlay');
      const toggleIcon = navToggle ? navToggle.querySelector('i') : null;

      function abrirMenu() {
        if (!navActions || !navOverlay || !toggleIcon) return;
        navActions.classList.add('open');
        navOverlay.classList.add('open');
        toggleIcon.className = 'bi bi-x';
        document.body.classList.add('menu-abierto');
      }

      function cerrarMenu() {
        if (!navActions || !navOverlay || !toggleIcon) return;
        navActions.classList.remove('open');
        navOverlay.classList.remove('open');
        toggleIcon.className = 'bi bi-list';
        document.body.classList.remove('menu-abierto');
      }

      if (navToggle) {
        navToggle.addEventListener('click', () => {
          navActions.classList.contains('open') ? cerrarMenu() : abrirMenu();
        });
      }

      if (navOverlay) {
        navOverlay.addEventListener('click', cerrarMenu);
      }

      document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', cerrarMenu);
      });

      window.addEventListener('resize', () => {
        if (window.innerWidth > 900) {
          if (navActions && navActions.classList.contains('open')) {
            cerrarMenu();
          }
        }
      });

      function handleNavClick(e, targetId) {
        const section = document.getElementById(targetId);
        if (section) {
          e.preventDefault();
          cerrarMenu();
          section.scrollIntoView({ behavior: 'smooth' });
          if (window.location.search.includes('jump=')) {
            window.history.replaceState({}, document.title, window.location.pathname);
          }
          return false;
        }
        return true;
      }

      window.addEventListener('load', () => {
        const params = new URLSearchParams(window.location.search);
        const jumpId = params.get('jump');
        if (jumpId) {
          const section = document.getElementById(jumpId);
          if (section) {
            setTimeout(() => {
              section.scrollIntoView({ behavior: 'smooth' });
              window.history.replaceState({}, document.title, window.location.pathname);
            }, 300);
          }
        }
      });
    </script>