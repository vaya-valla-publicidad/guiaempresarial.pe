<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Contacto</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <h1>Contacto</h1>
    <form>
        <label>Nombre de la empresa:</label><br>
        <input type="text" name="nombre"><br><br>

        <label>Email:</label><br>
        <input type="email" name="email"><br><br>

        <button type="submit">Enviar</button>
    </form>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
f