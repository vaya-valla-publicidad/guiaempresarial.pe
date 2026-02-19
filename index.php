<?php include 'includes/header.php'; ?>

<main>
    <h1>Guía Empresarial</h1>

    <section>
        <h2>Bienvenido a Guía Empresarial</h2>
        <p style="text-align:center; font-style:italic;">
            Tu espacio para descubrir y conectar con negocios locales.
        </p>
        <p style="text-align:center; font-style:italic;">
            "Impulsando negocios locales con visibilidad real"
        </p>
    </section>

    <section>
        <h2>Buscar Empresas</h2>
        <form onsubmit="return false;">
            <input type="text" id="buscar" name="buscar" placeholder="Buscar empresa...">
            <button type="button">Buscar</button>
        </form>
        <div id="resultados" class="empresas-grid"></div>

        <p><a href="empresas.php">Ver todas las empresas</a></p>
    </section>
</main>

<?php include 'includes/footer.php'; ?>

<script>
document.getElementById('buscar').addEventListener('keyup', function() {
    let query = this.value;
    let resultadosDiv = document.getElementById('resultados');

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
</script>
