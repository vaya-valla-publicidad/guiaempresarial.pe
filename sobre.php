<?php
include 'db.php';
$seo_title = "Sobre Nosotros - Guía Empresarial";
$seo_description = "Conoce quiénes somos, nuestra misión y visión. Guía Empresarial impulsa y conecta a los negocios locales.";
include 'includes/Header.php';
?>

<?php
$res = $conexion->query("SELECT clave, valor FROM sobre_info");
$info = [];
while ($f = $res->fetch_assoc())
    $info[$f['clave']] = $f['valor'];

$total_empresas_real = $conexion->query("SELECT COUNT(*) as total FROM empresas")->fetch_assoc()['total'];
$total_categorias_real = $conexion->query("SELECT COUNT(*) as total FROM categorias")->fetch_assoc()['total'];

$total_empresas = !empty($info['stat_empresas']) ? $info['stat_empresas'] : $total_empresas_real . "+";
$total_categorias = !empty($info['stat_categorias']) ? $info['stat_categorias'] : $total_categorias_real . "+";
?>

<section class="page-section">
    <div class="container">
        <div class="section-header">
            <h1>Sobre Guía Empresarial</h1>
            <p>Conoce quiénes somos y qué nos mueve</p>
        </div>
        <div class="sobre-grid">
            <div class="info-card">
                <h3>Quiénes Somos</h3>
                <p><?= nl2br(htmlspecialchars($info['quienes_somos'] ?? '')) ?></p>
            </div>
            <div class="info-card">
                <h3>Nuestra Misión</h3>
                <p><?= nl2br(htmlspecialchars($info['mision'] ?? '')) ?></p>
            </div>
            <div class="info-card">
                <h3>Nuestra Visión</h3>
                <p><?= nl2br(htmlspecialchars($info['vision'] ?? '')) ?></p>
            </div>
        </div>
    </div>
</section>

<section class="page-section" style="padding-top: 0;">
    <div class="container">
        <div class="section-header">
            <h1>En Números</h1>
            <p>El crecimiento de nuestra comunidad empresarial</p>
        </div>
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-numero"><?= $total_empresas ?>+</span>
                <span class="stat-label">Empresas registradas</span>
            </div>
            <div class="stat-card">
                <span class="stat-numero"><?= $total_categorias ?>+</span>
                <span class="stat-label">Categorías disponibles</span>
            </div>
            <div class="stat-card">
                <span class="stat-numero">100%</span>
                <span class="stat-label">Negocios locales</span>
            </div>
            <div class="stat-card">
                <span class="stat-numero">24/7</span>
                <span class="stat-label">Visibilidad online</span>
            </div>
        </div>
    </div>
</section>

<section class="page-section" style="padding-top: 0;">
    <div class="container">
        <div class="section-header">
            <h1>¿Por qué elegirnos?</h1>
            <p>Razones para formar parte de Guía Empresarial</p>
        </div>
        <div class="sobre-grid">
            <div class="info-card">
                <h3><?= htmlspecialchars($info['por_que_1_titulo'] ?? '') ?></h3>
                <p><?= nl2br(htmlspecialchars($info['por_que_1_texto'] ?? '')) ?></p>
            </div>
            <div class="info-card">
                <h3><?= htmlspecialchars($info['por_que_2_titulo'] ?? '') ?></h3>
                <p><?= nl2br(htmlspecialchars($info['por_que_2_texto'] ?? '')) ?></p>
            </div>
            <div class="info-card">
                <h3><?= htmlspecialchars($info['por_que_3_titulo'] ?? '') ?></h3>
                <p><?= nl2br(htmlspecialchars($info['por_que_3_texto'] ?? '')) ?></p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
