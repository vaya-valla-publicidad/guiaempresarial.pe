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

        <form id="formBuscar">
            <input type="text" id="buscar" name="buscar" placeholder="Buscar empresa..." autocomplete="off">

            <!-- RESULTADOS ARRIBA DEL BOTÓN -->
            <div id="resultados" class="empresas-grid"></div>

            <button type="submit">Buscar</button>
        </form>

        <p><a href="empresas.php">Ver todas las empresas</a></p>
    </section>
</main>

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