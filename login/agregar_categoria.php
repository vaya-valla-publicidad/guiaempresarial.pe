<?php require_once __DIR__ . '/proteger.php'; ?>
<?php
include '../db.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST['nombre']);
    $icono = trim($_POST['icono']) ?: 'bi-briefcase';

    if (!empty($nombre)) {
        $stmt = $conexion->prepare("INSERT INTO categorias (nombre, icono) VALUES (?, ?)");
        $stmt->bind_param("ss", $nombre, $icono);
        if (!$stmt->execute()) {
            $error = "Error: " . $stmt->error;
        } else {
            $success = "Categoría agregada correctamente ✅";
        }
        $stmt->close();
    } else {
        $error = "El nombre no puede estar vacío.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Categoría</title>
    <link rel="stylesheet" href="../assets/css/login.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>
    <div class="panel-container">
        <section class="panel">
            <h1 class="panel-title">Agregar Categoría</h1>
            <div class="form-container">

                <?php if ($error): ?>
                    <p style="color:red;text-align:center;"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>
                <?php if ($success): ?>
                    <p style="color:green;text-align:center;"><?= htmlspecialchars($success) ?></p>
                <?php endif; ?>

                <form method="post">
                    <div class="form-group">
                        <label>Nombre de la categoría</label>
                        <input type="text" name="nombre" required>
                    </div>

                    <div class="form-group">
                        <label>Icono</label>
                        <input type="text" name="icono" id="icono-input" placeholder="ej: bi-shop" value="bi-briefcase"
                            oninput="actualizarPreview(this.value)">

                        <div class="icono-preview">
                            <i class="bi bi-briefcase" id="icono-preview-icon"></i>
                            <span id="icono-preview-nombre">Vista previa</span>
                        </div>

                        <input type="text" class="icono-buscador" id="icono-buscador"
                            placeholder="🔍 Busca un icono... ej: comida, salud, auto, ropa...">

                        <div class="iconos-grid" id="iconos-grid"></div>
                    </div>

                    <button type="submit" class="btn">Agregar Categoría</button>
                </form>

                <a href="admin.php" class="btn btn-danger">Volver al Panel</a>
            </div>
        </section>
    </div>

    <script>
        const ICONOS = [
            { clase: 'bi-shop', nombre: 'Tienda/Local', tags: ['tienda', 'local', 'negocio', 'shop'] },
            { clase: 'bi-cup-hot', nombre: 'Café', tags: ['cafe', 'cafeteria', 'bebida', 'coffee'] },
            { clase: 'bi-egg-fried', nombre: 'Comida', tags: ['comida', 'food', 'gastronomia', 'cocina'] },
            { clase: 'bi-cake2', nombre: 'Pastelería', tags: ['pastel', 'torta', 'dulce', 'bakery', 'pasteleria'] },
            { clase: 'bi-basket', nombre: 'Mercado', tags: ['mercado', 'canasta', 'compras', 'market'] },
            { clase: 'bi-cart', nombre: 'Carrito', tags: ['carrito', 'compras', 'cart', 'ventas'] },
            { clase: 'bi-bag', nombre: 'Bolsa', tags: ['bolsa', 'compras', 'bag', 'tienda'] },
            { clase: 'bi-cpu', nombre: 'Tecnología', tags: ['tecnologia', 'tech', 'cpu', 'computadora'] },
            { clase: 'bi-phone', nombre: 'Celulares', tags: ['celular', 'phone', 'movil', 'smartphone'] },
            { clase: 'bi-laptop', nombre: 'Laptop', tags: ['laptop', 'computadora', 'pc', 'tech'] },
            { clase: 'bi-wifi', nombre: 'Internet', tags: ['internet', 'wifi', 'red', 'conectividad'] },
            { clase: 'bi-printer', nombre: 'Imprenta', tags: ['imprenta', 'printer', 'impresion'] },
            { clase: 'bi-camera', nombre: 'Fotografía', tags: ['foto', 'fotografia', 'camara', 'camera'] },
            { clase: 'bi-tv', nombre: 'Electrónica', tags: ['electronica', 'tv', 'television', 'aparatos'] },
            { clase: 'bi-heart-pulse', nombre: 'Salud', tags: ['salud', 'health', 'medico', 'clinica'] },
            { clase: 'bi-hospital', nombre: 'Hospital', tags: ['hospital', 'clinica', 'medico', 'salud'] },
            { clase: 'bi-capsule', nombre: 'Farmacia', tags: ['farmacia', 'medicina', 'drogueria'] },
            { clase: 'bi-eyeglasses', nombre: 'Óptica', tags: ['optica', 'lentes', 'vision', 'ojos'] },
            { clase: 'bi-scissors', nombre: 'Peluquería', tags: ['peluqueria', 'corte', 'belleza', 'salon'] },
            { clase: 'bi-stars', nombre: 'Spa/Bienestar', tags: ['spa', 'bienestar', 'belleza', 'relax'] },
            { clase: 'bi-tools', nombre: 'Ferretería', tags: ['ferreteria', 'herramientas', 'tools', 'hardware'] },
            { clase: 'bi-hammer', nombre: 'Construcción', tags: ['construccion', 'hammer', 'obra', 'martillo'] },
            { clase: 'bi-wrench', nombre: 'Mecánica', tags: ['mecanica', 'taller', 'wrench', 'llave', 'reparacion'] },
            { clase: 'bi-lightbulb', nombre: 'Electricidad', tags: ['electricidad', 'luz', 'electricista'] },
            { clase: 'bi-water', nombre: 'Gasfitería', tags: ['gasfiteria', 'agua', 'plomeria'] },
            { clase: 'bi-car-front', nombre: 'Autos', tags: ['auto', 'carro', 'car', 'vehiculo', 'transporte'] },
            { clase: 'bi-truck', nombre: 'Logística', tags: ['logistica', 'truck', 'envios', 'delivery', 'camion'] },
            { clase: 'bi-bicycle', nombre: 'Bicicletas', tags: ['bicicleta', 'bicycle', 'ciclismo', 'bike'] },
            { clase: 'bi-fuel-pump', nombre: 'Grifo/Gasolinera', tags: ['grifo', 'gasolinera', 'combustible', 'gasolina'] },
            { clase: 'bi-book', nombre: 'Educación', tags: ['educacion', 'libro', 'colegio', 'academia'] },
            { clase: 'bi-mortarboard', nombre: 'Universidad', tags: ['universidad', 'educacion', 'colegio', 'titulo'] },
            { clase: 'bi-pencil', nombre: 'Librería', tags: ['libreria', 'lapiz', 'papeleria', 'utiles'] },
            { clase: 'bi-building', nombre: 'Empresa', tags: ['empresa', 'edificio', 'comercio', 'building'] },
            { clase: 'bi-bank', nombre: 'Banco/Finanzas', tags: ['banco', 'finanzas', 'dinero', 'bank'] },
            { clase: 'bi-cash-coin', nombre: 'Préstamos', tags: ['prestamos', 'dinero', 'cash', 'credito'] },
            { clase: 'bi-receipt', nombre: 'Contabilidad', tags: ['contabilidad', 'factura', 'receipt', 'impuestos'] },
            { clase: 'bi-house', nombre: 'Inmobiliaria', tags: ['inmobiliaria', 'casa', 'house', 'alquiler'] },
            { clase: 'bi-sofa', nombre: 'Muebles', tags: ['muebles', 'sofa', 'hogar', 'furniture'] },
            { clase: 'bi-brush', nombre: 'Pinturas', tags: ['pintura', 'brush', 'decoracion', 'pintor'] },
            { clase: 'bi-handbag', nombre: 'Moda/Ropa', tags: ['moda', 'ropa', 'handbag', 'boutique'] },
            { clase: 'bi-gem', nombre: 'Joyería', tags: ['joyeria', 'joyas', 'gem', 'anillos', 'oro'] },
            { clase: 'bi-shield-check', nombre: 'Seguridad', tags: ['seguridad', 'vigilancia', 'shield', 'proteccion'] },
            { clase: 'bi-megaphone', nombre: 'Publicidad', tags: ['publicidad', 'marketing', 'megafono', 'promocion'] },
            { clase: 'bi-globe', nombre: 'Internet/Web', tags: ['internet', 'web', 'globe', 'digital', 'pagina'] },
            { clase: 'bi-recycle', nombre: 'Reciclaje', tags: ['reciclaje', 'ambiente', 'recycle', 'verde'] },
            { clase: 'bi-trophy', nombre: 'Deportes', tags: ['deportes', 'sport', 'trophy', 'trofeo', 'gym'] },
            { clase: 'bi-music-note', nombre: 'Música', tags: ['musica', 'music', 'audio', 'sonido'] },
            { clase: 'bi-flower1', nombre: 'Flores/Jardín', tags: ['flores', 'jardin', 'plantas', 'vivero'] },
            { clase: 'bi-tree', nombre: 'Agro/Campo', tags: ['agro', 'campo', 'agricultura', 'plantas'] },
            { clase: 'bi-heart', nombre: 'Mascotas', tags: ['mascotas', 'pets', 'animales', 'veterinaria'] },
            { clase: 'bi-person-gear', nombre: 'Servicios', tags: ['servicios', 'profesional', 'persona', 'trabajo'] },
            { clase: 'bi-briefcase', nombre: 'Negocios', tags: ['negocios', 'trabajo', 'oficina', 'empresa'] },
        ];

        const iconosGrid = document.getElementById('iconos-grid');
        const iconoInput = document.getElementById('icono-input');
        const buscador = document.getElementById('icono-buscador');
        const previewIcon = document.getElementById('icono-preview-icon');
        const previewNom = document.getElementById('icono-preview-nombre');

        function renderIconos(lista) {
            iconosGrid.innerHTML = '';
            if (lista.length === 0) {
                iconosGrid.innerHTML = '<p class="iconos-sin-resultados">😕 Sin resultados. Prueba otra palabra.</p>';
                return;
            }
            const actual = iconoInput.value.trim();
            lista.forEach(ic => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'icono-btn' + (actual === ic.clase ? ' activo' : '');
                btn.innerHTML = `<i class="bi ${ic.clase}"></i><span>${ic.nombre}</span>`;
                btn.onclick = () => {
                    iconoInput.value = ic.clase;
                    previewIcon.className = 'bi ' + ic.clase;
                    previewNom.textContent = ic.nombre;
                    document.querySelectorAll('.icono-btn').forEach(b => b.classList.remove('activo'));
                    btn.classList.add('activo');
                };
                iconosGrid.appendChild(btn);
            });
        }

        function actualizarPreview(valor) {
            previewIcon.className = 'bi ' + valor;
        }

        buscador.addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            if (q === '') { renderIconos(ICONOS); return; }
            const filtrados = ICONOS.filter(ic =>
                ic.nombre.toLowerCase().includes(q) ||
                ic.tags.some(t => t.includes(q))
            );
            renderIconos(filtrados);
        });

        renderIconos(ICONOS);
    </script>
</body>

</html>