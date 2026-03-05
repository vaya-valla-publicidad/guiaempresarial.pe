<?php include 'includes/header.php'; ?>

<section class="hero">
    <div class="hero-content">
        <h1>Impulsando Negocios Locales</h1>
        <p class="hero-subtitle">Descubre, conecta y potencia empresas de tu región</p>
        <p class="hero-tagline">Visibilidad real para negocios reales</p>
        <div class="hero-actions">
            <a href="empresas.php" class="btn-primary">Ver Empresas</a>
        </div>
    </div>
</section>

<section class="search-section">
    <div class="search-wrapper">
        <h2 class="search-title">Buscar Empresas</h2>
        <p class="search-description">Escribe el nombre de la empresa y encuentra resultados en tiempo real.</p>
        <form id="formBuscar" class="search-form">
            <div class="search-box">
                <input type="text" id="buscar" name="buscar" placeholder="Ejemplo: Restaurante, Ferretería..." autocomplete="off">
                <button type="submit">Buscar</button>
            </div>
        </form>
        <div id="resultados" class="empresas-grid resultados-live"></div>
    </div>
</section>

<section id="empresas" class="page-section">
    <div class="container">
        <div class="section-header">
            <h1>Empresas</h1>
            <p>Explora negocios locales y descubre nuevas oportunidades</p>
        </div>

        <?php
        include 'db.php';
        $sql = "SELECT e.*, c.nombre AS categoria
                FROM empresas e
                JOIN categorias c ON e.id_categoria = c.id_categoria
                LIMIT 4"; // 👈 aquí limitamos a 4 empresas
        $resultado = $conexion->query($sql);

        if($resultado && $resultado->num_rows > 0):
            while($fila = $resultado->fetch_assoc()):
        ?>
        <div class="empresa-card-modern" onclick="toggleContacto(this)">
            <div class="empresa-header">
                <h3><?= htmlspecialchars($fila['nombre']) ?></h3>
                <span class="categoria-badge"><?= htmlspecialchars($fila['categoria']) ?></span>
            </div>
            <p class="empresa-direccion"><?= htmlspecialchars($fila['direccion'] ?? '') ?></p>
            <div class="empresa-detalle">
                <?php
                $telefono = $fila['telefono'] ?? $fila['celular'] ?? $fila['whatsapp'] ?? null;
                if($telefono):
                    $numero = preg_replace('/[^0-9]/', '', $telefono);
                ?>
                    <a class="btn-gold" href="https://wa.me/<?= $numero ?>" target="_blank">Contactar por WhatsApp</a>
                <?php endif; ?>
                <?php if(!empty($fila['email'])): ?>
                    <p><strong>Email:</strong> <?= htmlspecialchars($fila['email']) ?></p>
                <?php endif; ?>
                <?php if(!empty($fila['direccion'])): ?>
                    <p><strong>Dirección:</strong> <?= htmlspecialchars($fila['direccion']) ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php
            endwhile;
        else:
            echo "<p class='no-results'>No hay resultados disponibles.</p>";
        endif;
        ?>
        <div class="ver-mas">
            <a href="empresas.php" class="btn-primary">Ver todas las empresas</a>
        </div>
    </div>
</section>

<script>
function toggleContacto(elemento) {
    elemento.classList.toggle("activo");
}
</script>

<section id="categorias" class="page-section">
    <div class="container">
        <div class="section-header">
            <h1>Categorías</h1>
            <p>Explora los rubros destacados</p>
        </div>
        <div class="categorias-modern">
            <?php
                include 'db.php';
                $resultado = $conexion->query("SELECT * FROM categorias LIMIT 5"); // 👈 solo 5
                while($fila = $resultado->fetch_assoc()):
            ?>
                <a href="empresas.php?id_categoria=<?= $fila['id_categoria'] ?>" class="categoria-card">
                    <?= htmlspecialchars($fila['nombre']) ?>
                </a>
            <?php endwhile; ?>

            <!-- Sexta tarjeta: Ver más -->
            <a href="categorias.php" class="categoria-card ver-mas-card">
                Ver más categorías
            </a>
        </div>
    </div>
</section>


<section id="sobre" class="page-section">
    <div class="container">
        <div class="section-header">
            <h1>Sobre Guía Empresarial</h1>
            <p>Conoce nuestra misión y visión</p>
        </div>
        <div class="sobre-grid">
            <div class="info-card">
                <h3>Quiénes Somos</h3>
                <p>Impulsamos negocios locales con visibilidad real. Conectamos empresas con clientes mediante web y redes sociales.</p>
            </div>
            <div class="info-card">
                <h3>Nuestra Misión</h3>
                <p>Brindar a las empresas locales una herramienta sencilla y efectiva para darse a conocer.</p>
            </div>
            <div class="info-card">
                <h3>Nuestra Visión</h3>
                <p>Convertirnos en la guía empresarial más confiable y completa de la región.</p>
            </div>
        </div>
    </div>
</section>

<section id="contacto" class="page-section">
    <div class="container">
        <div class="section-header">
            <h1>Contacto Oficial</h1>
            <p>Estas son las únicas vías oficiales para comunicarte con Guía Empresarial.</p>
        </div>
        <div class="contact-grid">
            <a href="https://wa.me/51987226299" target="_blank" class="contact-card whatsapp">
                <div class="contact-icon">📱</div>
                <h3>WhatsApp</h3>
                <p>Comunícate directamente para consultas rápidas o información sobre publicidad.</p>
            </a>
            <a href="https://www.facebook.com/guiaempresarios" target="_blank" class="contact-card facebook">
                <div class="contact-icon">🌐</div>
                <h3>Facebook Oficial</h3>
                <p>Síguenos para conocer novedades, publicaciones y negocios destacados.</p>
            </a>
            <a href="https://m.me/guiaempresarios" target="_blank" class="contact-card messenger">
                <div class="contact-icon">💬</div>
                <h3>Messenger</h3>
                <p>Escríbenos por mensaje directo desde nuestra página oficial.</p>
            </a>
        </div>
        <div class="contact-info-box">
            <h3>Horario de Atención</h3>
            <p>Lunes a Sábado — 9:00 AM a 6:00 PM</p>
            <p>Respondemos mensajes lo más pronto posible.</p>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<script>
const inputBuscar = document.getElementById('buscar');
const resultadosDiv = document.getElementById('resultados');
const formBuscar = document.getElementById('formBuscar');

inputBuscar.addEventListener('keyup', function() {
    let query = this.value.trim();
    if (query.length > 0) {
        fetch('buscar.php?q=' + encodeURIComponent(query))
            .then(response => response.text())
            .then(data => {
                resultadosDiv.innerHTML = data;
            });
    } else {
        resultadosDiv.innerHTML = "";
    }
});

formBuscar.addEventListener('submit', function(e) {
    e.preventDefault();
    let query = inputBuscar.value.trim();
    if (query.length > 0) {
        window.location.href = "empresas.php?buscar=" + encodeURIComponent(query);
    }
});
</script>

