<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Guía Empresarial</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <h1>Guía Empresarial</h1>

    <section>
        <h2>Bienvenido a Guía Empresarial</h2>
        <p style="text-align:center; font-style:italic; margin-top:10px;">
            Tu espacio para descubrir y conectar con negocios locales.
        </p>

        <p style="text-align:center; font-style:italic; margin-top:10px;">
            "Impulsando negocios locales con visibilidad real"
        </p>
    </section>

    <section>
        <h2>Buscar Empresas</h2>
        <form method="GET" action="empresas.php">
            <input type="text" name="buscar" placeholder="Buscar empresa...">
            <button type="submit">Buscar</button>
        </form>

        <p><a href="empresas.php">Ver todas las empresas</a></p>
    </section>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
