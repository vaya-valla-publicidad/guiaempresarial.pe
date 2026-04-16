<?php require_once __DIR__ . '/proteger.php'; ?>
<?php include '../db.php'; ?>

<?php
$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $campos = [
        'quienes_somos',
        'mision',
        'vision',
        'por_que_1_titulo',
        'por_que_1_texto',
        'por_que_2_titulo',
        'por_que_2_texto',
        'por_que_3_titulo',
        'por_que_3_texto',
        'stat_empresas',
        'stat_categorias'
    ];
    foreach ($campos as $campo) {
        $valor = trim($_POST[$campo] ?? '');

        $check = $conexion->prepare("SELECT COUNT(*) as existe FROM sobre_info WHERE clave=?");
        $check->bind_param("s", $campo);
        $check->execute();
        $existe = $check->get_result()->fetch_assoc()['existe'];
        $check->close();

        if ($existe) {
            $stmt = $conexion->prepare("UPDATE sobre_info SET valor=? WHERE clave=?");
            $stmt->bind_param("ss", $valor, $campo);
        } else {
            $stmt = $conexion->prepare("INSERT INTO sobre_info (valor, clave) VALUES (?, ?)");
            $stmt->bind_param("ss", $valor, $campo);
        }
        $stmt->execute();
        $stmt->close();
    }
    $success = "Información actualizada correctamente ✅";
}

$real_stats = [
    'empresas' => $conexion->query("SELECT COUNT(*) as total FROM empresas")->fetch_assoc()['total'],
    'categorias' => $conexion->query("SELECT COUNT(*) as total FROM categorias")->fetch_assoc()['total']
];

$res = $conexion->query("SELECT clave, valor FROM sobre_info");
$info = [];
while ($f = $res->fetch_assoc())
    $info[$f['clave']] = $f['valor'];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Sobre Nosotros</title>
    <link rel="icon" href="<?= APP_URL ?>/assets/img/image.png" type="image/png">
    <link rel="stylesheet" href="../assets/css/login.css">
</head>

<body>
    <div class="panel-container">
        <section class="panel">
            <h1 class="panel-title">Editar — Sobre Nosotros</h1>
            <div class="form-container">

                <?php if ($success): ?>
                    <p style="color:green;text-align:center;"><?= $success ?></p><?php endif; ?>
                <?php if ($error): ?>
                    <p style="color:red;text-align:center;"><?= $error ?></p><?php endif; ?>

                <form method="post">

                    <h3 style="margin-bottom:16px;">Quiénes somos / Misión / Visión</h3>

                    <div class="form-group">
                        <label>Quiénes Somos</label>
                        <textarea name="quienes_somos"
                            rows="4"><?= htmlspecialchars($info['quienes_somos'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Misión</label>
                        <textarea name="mision" rows="4"><?= htmlspecialchars($info['mision'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Visión</label>
                        <textarea name="vision" rows="4"><?= htmlspecialchars($info['vision'] ?? '') ?></textarea>
                    </div>

                    <hr style="margin: 30px 0;">
                    <h3 style="margin-bottom:16px;">¿Por qué elegirnos?</h3>

                    <div class="form-group">
                        <label>Título 1</label>
                        <input type="text" name="por_que_1_titulo"
                            value="<?= htmlspecialchars($info['por_que_1_titulo'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Texto 1</label>
                        <textarea name="por_que_1_texto"
                            rows="3"><?= htmlspecialchars($info['por_que_1_texto'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Título 2</label>
                        <input type="text" name="por_que_2_titulo"
                            value="<?= htmlspecialchars($info['por_que_2_titulo'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Texto 2</label>
                        <textarea name="por_que_2_texto"
                            rows="3"><?= htmlspecialchars($info['por_que_2_texto'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Título 3</label>
                        <input type="text" name="por_que_3_titulo"
                            value="<?= htmlspecialchars($info['por_que_3_titulo'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Texto 3</label>
                        <textarea name="por_que_3_texto"
                            rows="3"><?= htmlspecialchars($info['por_que_3_texto'] ?? '') ?></textarea>
                    </div>

                    <hr style="margin: 30px 0;">
                    <h3 style="margin-bottom:16px;">Contadores (Estadísticas en "Sobre Nosotros")</h3>
                    <p style="font-size: 13px; color: #64748b; margin-bottom: 20px;">Estos valores se mostrarán en la
                        web pública. Puedes poner números con símbolos (ej: 100+).</p>

                    <div class="form-group">
                        <label>Empresas registradas (Texto publico)</label>
                        <div style="display:flex; align-items:center; gap: 15px;">
                            <input type="text" name="stat_empresas" style="flex:1;"
                                value="<?= htmlspecialchars($info['stat_empresas'] ?? '') ?>" placeholder="Ej: 50+">
                            <span
                                style="font-size:12px; white-space:nowrap; background:#f1f5f9; padding:10px; border-radius:8px;">
                                📊 Real en sistema: <strong><?= $real_stats['empresas'] ?></strong>
                            </span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Categorías disponibles (Texto publico)</label>
                        <div style="display:flex; align-items:center; gap: 15px;">
                            <input type="text" name="stat_categorias" style="flex:1;"
                                value="<?= htmlspecialchars($info['stat_categorias'] ?? '') ?>" placeholder="Ej: 15+">
                            <span
                                style="font-size:12px; white-space:nowrap; background:#f1f5f9; padding:10px; border-radius:8px;">
                                🏷 Real en sistema: <strong><?= $real_stats['categorias'] ?></strong>
                            </span>
                        </div>
                    </div>

                    <button type="submit" class="btn">Guardar cambios</button>
                </form>

                <a href="admin.php" class="btn btn-danger">Volver al Panel</a>
            </div>
        </section>
    </div>
</body>

</html>