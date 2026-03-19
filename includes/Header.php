<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guía Empresarial</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="assets/img/image.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="manifest" href="/guiaempresarial.pe/manifest.json">
    <meta name="theme-color" content="#0d6efd">
</head>

<body>

<div class="nav-overlay" id="nav-overlay"></div>

<header class="main-header">
  <div class="nav-container">

    <div class="brand">
      <img src="assets/img/image.png" alt="Guía Empresarial" class="logo">
      <span class="brand-name">GUÍA EMPRESARIAL</span>
    </div>

    <button class="nav-toggle" id="nav-toggle" aria-label="Abrir menú">
      <i class="bi bi-list"></i>
    </button>

    <div class="nav-actions" id="nav-actions">
      <nav class="nav-links">
        <a href="index.php" class="nav-link">Inicio</a>
        <a href="index.php#empresas" class="nav-link">Empresas</a>
        <a href="index.php#categorias" class="nav-link">Categorías</a>
        <a href="index.php#contacto" class="nav-link">Contacto</a>
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
const navToggle  = document.getElementById('nav-toggle');
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