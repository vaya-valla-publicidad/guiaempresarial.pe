<?php include 'includes/header.php'; ?>

<section class="hero">
    <div class="hero-content">
        <h1>Impulsando Negocios Locales</h1>

        <p class="hero-subtitle">
            Descubre, conecta y potencia empresas de tu región
        </p>

        <p class="hero-tagline">
            Visibilidad real para negocios reales
        </p>

        <div class="hero-actions">
            <a href="empresas.php" class="btn-primary">Ver Empresas</a>
            <a href="categorias.php" class="btn-outline">Explorar Categorías</a>
        </div>
    </div>
</section>

<section class="search-section">
    <div class="search-wrapper">

        <h2 class="search-title">Buscar Empresas</h2>
        <p class="search-description">
            Escribe el nombre de la empresa y encuentra resultados en tiempo real.
        </p>

        <form id="formBuscar" class="search-form">
            <div class="search-box">
                <input 
                    type="text" 
                    id="buscar" 
                    name="buscar" 
                    placeholder="Ejemplo: Restaurante, Ferretería..."
                    autocomplete="off"
                >
                <button type="submit">
                    Buscar
                </button>
            </div>
        </form>

        <div id="resultados" class="empresas-grid resultados-live"></div>

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