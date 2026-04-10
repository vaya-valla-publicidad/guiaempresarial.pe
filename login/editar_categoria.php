<?php require_once __DIR__ . '/proteger.php'; ?>
<?php
include '../db.php';
include '../includes/slug_helper.php';


if (!isset($_GET['id'])) {
    header("Location: admin.php");
    exit;
}

$id = intval($_GET['id']);
$error = "";
$success = "";

$stmt = $conexion->prepare("SELECT * FROM categorias WHERE id_categoria=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$cat = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$cat) {
    die("Categoría no encontrada");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST['nombre']);
    $icono = trim($_POST['icono']) ?: 'bi-briefcase';
    $slug = generarSlug($nombre);

    $stmt = $conexion->prepare("UPDATE categorias SET nombre=?, icono=?, slug=? WHERE id_categoria=?");
    $stmt->bind_param("sssi", $nombre, $icono, $slug, $id);
    if ($stmt->execute()) {
        $success = "Categoría actualizada ✅";
        $cat['nombre'] = $nombre;
        $cat['icono'] = $icono;
    } else {
        $error = "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Categoría</title>
    <link rel="stylesheet" href="../assets/css/login.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        #agregar-categoria-page .panel .form-container,
        #agregar-categoria-page .panel form {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
        }

        #agregar-categoria-page .panel .form-group {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: stretch;
        }

        #agregar-categoria-page #iconos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 12px;
            width: 100%;
        }
    </style>
</head>

<body id="agregar-categoria-page">
    <div class="panel-container">
        <section class="panel">
            <h1 class="panel-title">Editar Categoría</h1>
            <div class="form-container">

                <?php if ($error): ?>
                    <p style="color:red;text-align:center;"><?= htmlspecialchars($error) ?></p><?php endif; ?>
                <?php if ($success): ?>
                    <p style="color:green;text-align:center;"><?= htmlspecialchars($success) ?></p><?php endif; ?>

                <form method="post">
                    <div class="form-group">
                        <label>Nombre de la categoría</label>
                        <input type="text" name="nombre" value="<?= htmlspecialchars($cat['nombre']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Icono</label>
                        <input type="text" name="icono" id="icono-input"
                            value="<?= htmlspecialchars($cat['icono'] ?? 'bi-briefcase') ?>"
                            oninput="actualizarPreview(this.value)">

                        <div class="icono-preview">
                            <i class="bi <?= htmlspecialchars($cat['icono'] ?? 'bi-briefcase') ?>"
                                id="icono-preview-icon"></i>
                            <span id="icono-preview-nombre">Vista previa</span>
                        </div>

                        <input type="text" class="icono-buscador" id="icono-buscador"
                            placeholder="🔍 Busca un icono... ej: comida, salud, auto, ropa...">

                        <div class="iconos-grid" id="iconos-grid"></div>
                    </div>

                    <button type="submit" class="btn">Guardar cambios</button>
                </form>

                <a href="admin.php" class="btn btn-danger">Volver al Panel</a>
            </div>
        </section>
    </div>

    <script>
        const ICONOS = [
            { clase: 'bi-shop', nombre: 'Tienda/Local', tags: ['tienda', 'local', 'negocio', 'shop'] },
            { clase: 'bi-shop-window', nombre: 'Vitrina', tags: ['vitrina', 'boutique', 'escaparate', 'exhibicion'] },
            { clase: 'bi-basket', nombre: 'Mercado', tags: ['mercado', 'canasta', 'compras', 'market'] },
            { clase: 'bi-cart', nombre: 'Supermercado', tags: ['carrito', 'compras', 'cart', 'supermercado'] },
            { clase: 'bi-bag', nombre: 'Tienda Online', tags: ['bolsa', 'compras', 'bag', 'ecommerce'] },
            { clase: 'bi-tags', nombre: 'Ofertas/Descuentos', tags: ['oferta', 'descuento', 'etiqueta', 'precio', 'promocion'] },
            { clase: 'bi-cup-hot', nombre: 'Café', tags: ['cafe', 'cafeteria', 'bebida', 'coffee', 'caliente'] },
            { clase: 'bi-cup-straw', nombre: 'Bebidas/Jugos', tags: ['bebida', 'jugo', 'refresco', 'smoothie', 'batido'] },
            { clase: 'bi-egg-fried', nombre: 'Restaurante', tags: ['comida', 'food', 'gastronomia', 'cocina', 'restaurante'] },
            { clase: 'bi-cake2', nombre: 'Pastelería', tags: ['pastel', 'torta', 'dulce', 'bakery', 'pasteleria'] },
            { clase: 'bi-cookie', nombre: 'Panadería', tags: ['pan', 'galleta', 'dulce', 'panaderia', 'postre'] },
            { clase: 'bi-cpu', nombre: 'Tecnología', tags: ['tecnologia', 'tech', 'cpu', 'computadora', 'componentes'] },
            { clase: 'bi-phone', nombre: 'Celulares', tags: ['celular', 'phone', 'movil', 'smartphone'] },
            { clase: 'bi-laptop', nombre: 'Laptop/PC', tags: ['laptop', 'computadora', 'pc', 'tech'] },
            { clase: 'bi-pc-display', nombre: 'Computadoras', tags: ['pc', 'monitor', 'escritorio', 'computo'] },
            { clase: 'bi-smartwatch', nombre: 'Relojes/Wearables', tags: ['reloj', 'smartwatch', 'wearable', 'accesorio'] },
            { clase: 'bi-headphones', nombre: 'Audio/Audífonos', tags: ['audio', 'musica', 'audifonos', 'sonido'] },
            { clase: 'bi-speaker', nombre: 'Parlantes', tags: ['parlante', 'audio', 'bocina', 'altavoz'] },
            { clase: 'bi-wifi', nombre: 'Internet/WiFi', tags: ['internet', 'wifi', 'red', 'conectividad'] },
            { clase: 'bi-router', nombre: 'Redes', tags: ['router', 'internet', 'comunicacion', 'red', 'cableado'] },
            { clase: 'bi-printer', nombre: 'Imprenta', tags: ['imprenta', 'printer', 'impresion', 'copias'] },
            { clase: 'bi-camera', nombre: 'Fotografía', tags: ['foto', 'fotografia', 'camara', 'camera', 'estudio'] },
            { clase: 'bi-camera-video', nombre: 'Video/Producción', tags: ['video', 'grabacion', 'produccion', 'filmacion'] },
            { clase: 'bi-tv', nombre: 'Electrónica/TV', tags: ['electronica', 'tv', 'television', 'aparatos'] },
            { clase: 'bi-heart-pulse', nombre: 'Salud', tags: ['salud', 'health', 'medico', 'clinica', 'corazon'] },
            { clase: 'bi-hospital', nombre: 'Hospital/Clínica', tags: ['hospital', 'clinica', 'medico', 'emergencia'] },
            { clase: 'bi-capsule', nombre: 'Farmacia', tags: ['farmacia', 'medicina', 'drogueria', 'pastilla'] },
            { clase: 'bi-bandaid', nombre: 'Curaciones', tags: ['curacion', 'herida', 'topico', 'primeros auxilios'] },
            { clase: 'bi-eyeglasses', nombre: 'Óptica', tags: ['optica', 'lentes', 'vision', 'ojos'] },
            { clase: 'bi-activity', nombre: 'Laboratorio', tags: ['laboratorio', 'analisis', 'examen', 'resultado'] },
            { clase: 'bi-scissors', nombre: 'Peluquería', tags: ['peluqueria', 'corte', 'belleza', 'salon', 'barberia'] },
            { clase: 'bi-stars', nombre: 'Spa/Bienestar', tags: ['spa', 'bienestar', 'belleza', 'relax', 'masajes'] },
            { clase: 'bi-brush', nombre: 'Maquillaje/Uñas', tags: ['maquillaje', 'unas', 'cosmeticos', 'belleza'] },
            { clase: 'bi-tools', nombre: 'Ferretería', tags: ['ferreteria', 'herramientas', 'tools', 'hardware'] },
            { clase: 'bi-hammer', nombre: 'Construcción', tags: ['construccion', 'hammer', 'obra', 'martillo', 'albañil'] },
            { clase: 'bi-wrench', nombre: 'Mecánica', tags: ['mecanica', 'taller', 'wrench', 'llave', 'reparacion'] },
            { clase: 'bi-nut', nombre: 'Repuestos', tags: ['repuestos', 'tuerca', 'piezas', 'autopartes'] },
            { clase: 'bi-lightbulb', nombre: 'Electricidad', tags: ['electricidad', 'luz', 'electricista', 'iluminacion'] },
            { clase: 'bi-water', nombre: 'Gasfitería', tags: ['gasfiteria', 'agua', 'plomeria', 'tuberia'] },
            { clase: 'bi-paint-bucket', nombre: 'Pinturas', tags: ['pintura', 'color', 'decoracion', 'pintor'] },
            { clase: 'bi-house-gear', nombre: 'Servicios del Hogar', tags: ['hogar', 'servicio', 'reparacion', 'mantenimiento'] },
            { clase: 'bi-key', nombre: 'Cerrajería', tags: ['cerrajeria', 'llave', 'cerradura', 'puerta'] },
            { clase: 'bi-bricks', nombre: 'Materiales', tags: ['materiales', 'ladrillo', 'cemento', 'construccion'] },
            { clase: 'bi-car-front', nombre: 'Autos', tags: ['auto', 'carro', 'car', 'vehiculo', 'transporte'] },
            { clase: 'bi-bus-front', nombre: 'Transporte', tags: ['bus', 'transporte', 'pasajeros', 'viaje'] },
            { clase: 'bi-truck', nombre: 'Logística/Carga', tags: ['logistica', 'truck', 'envios', 'delivery', 'camion', 'mudanza'] },
            { clase: 'bi-bicycle', nombre: 'Bicicletas', tags: ['bicicleta', 'bicycle', 'ciclismo', 'bike'] },
            { clase: 'bi-scooter', nombre: 'Motos/Scooter', tags: ['moto', 'scooter', 'motor', 'motocicleta'] },
            { clase: 'bi-fuel-pump', nombre: 'Grifo/Gasolinera', tags: ['grifo', 'gasolinera', 'combustible', 'gasolina'] },
            { clase: 'bi-droplet-half', nombre: 'Lavado de Autos', tags: ['lavado', 'car wash', 'limpieza', 'auto'] },
            { clase: 'bi-taxi-front', nombre: 'Taxi/Remisse', tags: ['taxi', 'remisse', 'transporte', 'chofer'] },
            { clase: 'bi-book', nombre: 'Educación', tags: ['educacion', 'libro', 'colegio', 'academia', 'escuela'] },
            { clase: 'bi-mortarboard', nombre: 'Universidad', tags: ['universidad', 'educacion', 'instituto', 'titulo'] },
            { clase: 'bi-pencil', nombre: 'Librería/Papelería', tags: ['libreria', 'lapiz', 'papeleria', 'utiles'] },
            { clase: 'bi-pen', nombre: 'Notaría/Legal', tags: ['notaria', 'abogado', 'firma', 'legal', 'tramites'] },
            { clase: 'bi-translate', nombre: 'Idiomas', tags: ['idiomas', 'ingles', 'traduccion', 'cursos'] },
            { clase: 'bi-journal-text', nombre: 'Biblioteca', tags: ['biblioteca', 'lectura', 'libros', 'revista'] },
            { clase: 'bi-building', nombre: 'Empresa/Oficina', tags: ['empresa', 'edificio', 'comercio', 'oficina'] },
            { clase: 'bi-bank', nombre: 'Banco/Finanzas', tags: ['banco', 'finanzas', 'dinero', 'bank', 'caja'] },
            { clase: 'bi-cash-coin', nombre: 'Préstamos', tags: ['prestamos', 'dinero', 'cash', 'credito', 'efectivo'] },
            { clase: 'bi-credit-card', nombre: 'Pagos/Tarjetas', tags: ['pago', 'tarjeta', 'visa', 'mastercard', 'credito'] },
            { clase: 'bi-wallet', nombre: 'Casa de Cambio', tags: ['cambio', 'dolares', 'divisas', 'billetera', 'moneda'] },
            { clase: 'bi-receipt', nombre: 'Contabilidad', tags: ['contabilidad', 'factura', 'receipt', 'impuestos'] },
            { clase: 'bi-house', nombre: 'Inmobiliaria', tags: ['inmobiliaria', 'casa', 'house', 'alquiler', 'venta'] },
            { clase: 'bi-shield-check', nombre: 'Seguridad', tags: ['seguridad', 'vigilancia', 'camaras', 'proteccion'] },
            { clase: 'bi-megaphone', nombre: 'Publicidad', tags: ['publicidad', 'marketing', 'megafono', 'promocion'] },
            { clase: 'bi-globe', nombre: 'Diseño Web', tags: ['internet', 'web', 'globe', 'digital', 'pagina'] },
            { clase: 'bi-briefcase', nombre: 'Negocios', tags: ['negocios', 'trabajo', 'oficina', 'empresa', 'maletin'] },
            { clase: 'bi-person-gear', nombre: 'Servicio Técnico', tags: ['servicios', 'profesional', 'tecnico', 'soporte'] },
            { clase: 'bi-people', nombre: 'RRHH/Agencia', tags: ['personal', 'rrhh', 'personas', 'agencia', 'empleo'] },
            { clase: 'bi-clipboard-data', nombre: 'Consultoría', tags: ['consultoria', 'asesoria', 'datos', 'gestion'] },
            { clase: 'bi-joystick', nombre: 'Videojuegos', tags: ['juegos', 'videojuegos', 'gamer', 'play', 'arcade'] },
            { clase: 'bi-controller', nombre: 'Consolas', tags: ['playstation', 'xbox', 'nintendo', 'juegos'] },
            { clase: 'bi-trophy', nombre: 'Deportes/Gym', tags: ['deportes', 'sport', 'trophy', 'trofeo', 'gym', 'fitness'] },
            { clase: 'bi-music-note', nombre: 'Música', tags: ['musica', 'music', 'audio', 'instrumentos', 'sonido'] },
            { clase: 'bi-mic', nombre: 'Karaoke/Eventos', tags: ['karaoke', 'microfono', 'canto', 'eventos', 'fiesta'] },
            { clase: 'bi-film', nombre: 'Cine/Teatro', tags: ['cine', 'pelicula', 'teatro', 'entretenimiento'] },
            { clase: 'bi-ticket-perforated', nombre: 'Entradas/Boletos', tags: ['ticket', 'boleto', 'eventos', 'concierto'] },
            { clase: 'bi-palette', nombre: 'Arte/Diseño', tags: ['arte', 'diseno', 'pintura', 'creatividad', 'grafico'] },
            { clase: 'bi-balloon', nombre: 'Fiestas/Eventos', tags: ['fiesta', 'evento', 'globo', 'celebracion', 'cumpleanos'] },
            { clase: 'bi-dice-5', nombre: 'Casino/Juegos', tags: ['casino', 'dado', 'juego', 'apuesta'] },
            { clase: 'bi-house-door', nombre: 'Muebles/Hogar', tags: ['muebles', 'sofa', 'hogar', 'furniture', 'sala', 'casa'] },
            { clase: 'bi-lamp', nombre: 'Decoración', tags: ['decoracion', 'lampara', 'hogar', 'adornos', 'iluminacion'] },
            { clase: 'bi-handbag', nombre: 'Moda/Ropa', tags: ['moda', 'ropa', 'handbag', 'boutique', 'vestidos'] },
            { clase: 'bi-gem', nombre: 'Joyería', tags: ['joyeria', 'joyas', 'gem', 'anillos', 'oro', 'plata'] },
            { clase: 'bi-watch', nombre: 'Relojería', tags: ['reloj', 'hora', 'joyeria', 'accesorio'] },
            { clase: 'bi-sunglasses', nombre: 'Accesorios', tags: ['lentes', 'sol', 'accesorio', 'moda'] },
            { clase: 'bi-flower1', nombre: 'Florería', tags: ['flores', 'jardin', 'plantas', 'vivero', 'floreria'] },
            { clase: 'bi-tree', nombre: 'Agro/Campo', tags: ['agro', 'campo', 'agricultura', 'plantas', 'vivero'] },
            { clase: 'bi-heart', nombre: 'Mascotas/Veterinaria', tags: ['mascotas', 'pets', 'animales', 'veterinaria', 'perros'] },
            { clase: 'bi-recycle', nombre: 'Reciclaje/Ecología', tags: ['reciclaje', 'ambiente', 'recycle', 'verde', 'ecologia'] },
            { clase: 'bi-airplane', nombre: 'Aerolínea/Viajes', tags: ['vuelo', 'avion', 'viaje', 'turismo', 'aerolinea'] },
            { clase: 'bi-compass', nombre: 'Turismo', tags: ['turismo', 'viaje', 'excursion', 'guia', 'aventura'] },
            { clase: 'bi-map', nombre: 'Mapas/Guía', tags: ['mapa', 'ubicacion', 'guia', 'direccion'] },
            { clase: 'bi-suitcase-lg', nombre: 'Hotel/Hospedaje', tags: ['hotel', 'hospedaje', 'habitacion', 'alojamiento'] },
            { clase: 'bi-globe-americas', nombre: 'Internacional', tags: ['mundo', 'global', 'exportacion', 'comercio exterior'] },
            { clase: 'bi-clock', nombre: 'Horarios/24h', tags: ['reloj', 'hora', '24 horas', 'tiempo', 'abierto'] },
            { clase: 'bi-geo-alt', nombre: 'Ubicación', tags: ['ubicacion', 'mapa', 'lugar', 'direccion', 'gps'] },
            { clase: 'bi-telephone', nombre: 'Call Center', tags: ['telefono', 'llamada', 'atencion', 'contacto'] },
            { clase: 'bi-envelope', nombre: 'Correo/Mensajería', tags: ['correo', 'email', 'mensaje', 'correspondencia'] },
            { clase: 'bi-box-seam', nombre: 'Paquetería/Envíos', tags: ['paquete', 'envio', 'caja', 'delivery', 'courier'] },
            { clase: 'bi-droplet', nombre: 'Agua/Lavandería', tags: ['agua', 'lavanderia', 'limpieza', 'gota'] },
            { clase: 'bi-fire', nombre: 'Bomberos/Emergencia', tags: ['fuego', 'bombero', 'emergencia', 'incendio'] },
            { clase: 'bi-snow', nombre: 'Refrigeración/Aire', tags: ['frio', 'aire', 'refrigeracion', 'clima'] },
            { clase: 'bi-lightning', nombre: 'Energía/Solar', tags: ['energia', 'solar', 'electrica', 'panel'] },
            { clase: 'bi-plug', nombre: 'Electricista', tags: ['enchufe', 'electricista', 'instalacion', 'voltaje'] },
            { clase: 'bi-gift', nombre: 'Regalos/Souvenirs', tags: ['regalo', 'obsequio', 'souvenir', 'detalle'] },
            { clase: 'bi-cart-check', nombre: 'Ventas al Mayor', tags: ['mayorista', 'distribuidor', 'venta', 'al por mayor'] },
            { clase: 'bi-person-badge', nombre: 'Identificación', tags: ['carnet', 'badge', 'id', 'identificacion', 'fotocheck'] },
            { clase: 'bi-calendar-event', nombre: 'Agenda/Citas', tags: ['cita', 'agenda', 'calendario', 'reserva'] },
            { clase: 'bi-bar-chart', nombre: 'Estadísticas', tags: ['grafico', 'estadistica', 'datos', 'reporte'] },
            { clase: 'bi-qr-code', nombre: 'QR/Digital', tags: ['qr', 'codigo', 'escanear', 'digital'] },
            { clase: 'bi-signpost', nombre: 'Señalización', tags: ['señal', 'cartel', 'letrero', 'rotulo'] },
            { clase: 'bi-door-open', nombre: 'Entrada/Salida', tags: ['puerta', 'entrada', 'salida', 'acceso'] },
            { clase: 'bi-fan', nombre: 'Ventilación/Clima', tags: ['ventilador', 'clima', 'aire', 'ventilacion'] },
            { clase: 'bi-ear', nombre: 'Audiología', tags: ['oido', 'audicion', 'audiologia', 'audifono'] },
            { clase: 'bi-thermometer-half', nombre: 'Calefacción', tags: ['calefaccion', 'temperatura', 'calor', 'clima'] },
            { clase: 'bi-trash', nombre: 'Limpieza/Residuos', tags: ['basura', 'limpieza', 'residuos', 'desechos'] },
            { clase: 'bi-eye', nombre: 'Vigilancia', tags: ['ojo', 'vigilancia', 'monitoreo', 'camara'] },
            { clase: 'bi-chat-dots', nombre: 'Atención al Cliente', tags: ['chat', 'atencion', 'soporte', 'ayuda'] },
            { clase: 'bi-basket2-fill', nombre: 'Fast Food', tags: ['comida rapida', 'fastfood', 'hamburguesa', 'pollo'] },
            { clase: 'bi-fire', nombre: 'Parrillas/BBQ', tags: ['parrilla', 'bbq', 'brasa', 'pollo', 'asado'] },
            { clase: 'bi-lungs', nombre: 'Neumología', tags: ['pulmon', 'respirar', 'neumologia', 'asma'] },
            { clase: 'bi-virus', nombre: 'Laboratorio Clínico', tags: ['virus', 'bacteria', 'laboratorio', 'examen'] },
            { clase: 'bi-prescription2', nombre: 'Receta Médica', tags: ['receta', 'medico', 'prescripcion', 'doctor'] },
            { clase: 'bi-bicycle', nombre: 'Ciclismo', tags: ['ciclismo', 'bicicleta', 'bike', 'ruta'] },
            { clase: 'bi-dribbble', nombre: 'Fútbol/Canchas', tags: ['futbol', 'cancha', 'deporte', 'pelota'] },
            { clase: 'bi-sun', nombre: 'Playa/Verano', tags: ['playa', 'sol', 'verano', 'piscina'] },
            { clase: 'bi-cloud-rain', nombre: 'Clima/Meteorología', tags: ['lluvia', 'clima', 'tiempo', 'pronostico'] },
            { clase: 'bi-tsunami', nombre: 'Piscinas/Agua', tags: ['piscina', 'agua', 'natacion', 'acuatico'] },
            { clase: 'bi-bug', nombre: 'Fumigación/Plagas', tags: ['fumigacion', 'plaga', 'insecto', 'control'] },
            { clase: 'bi-person-workspace', nombre: 'Coworking', tags: ['coworking', 'oficina', 'espacio', 'trabajo'] },
            { clase: 'bi-kanban', nombre: 'Gestión/Proyectos', tags: ['proyecto', 'gestion', 'kanban', 'tarea'] },
            { clase: 'bi-graph-up', nombre: 'Inversiones', tags: ['inversion', 'bolsa', 'acciones', 'trading'] },
            { clase: 'bi-award', nombre: 'Premios/Certificación', tags: ['premio', 'certificacion', 'reconocimiento', 'diploma'] },
            { clase: 'bi-code-slash', nombre: 'Programación/Software', tags: ['programacion', 'software', 'desarrollo', 'app', 'codigo'] },
            { clase: 'bi-robot', nombre: 'Automatización/IA', tags: ['robot', 'ia', 'inteligencia', 'automatizacion'] },
            { clase: 'bi-broadcast', nombre: 'Radio/Podcast', tags: ['radio', 'podcast', 'emisora', 'transmision'] },
            { clase: 'bi-newspaper', nombre: 'Prensa/Noticias', tags: ['diario', 'prensa', 'noticias', 'periodico'] },
            { clase: 'bi-megaphone-fill', nombre: 'Altavoz/Aviso', tags: ['altavoz', 'aviso', 'anuncio', 'comunicado'] },
            { clase: 'bi-shield-lock', nombre: 'Ciberseguridad', tags: ['ciberseguridad', 'datos', 'privacidad', 'proteccion'] },
            { clase: 'bi-flag', nombre: 'Gobierno/Municipal', tags: ['gobierno', 'municipalidad', 'bandera', 'estado'] },
            { clase: 'bi-life-preserver', nombre: 'Emergencias/Rescate', tags: ['emergencia', 'rescate', 'salvavidas', 'socorro'] },
            { clase: 'bi-lamp-fill', nombre: 'Iluminación', tags: ['lampara', 'luz', 'iluminacion', 'foco'] },
            { clase: 'bi-window', nombre: 'Vidriería/Ventanas', tags: ['vidrio', 'ventana', 'cristal', 'vidriera'] },
            { clase: 'bi-door-closed', nombre: 'Puertas/Portones', tags: ['puerta', 'porton', 'garage', 'acceso'] },
            { clase: 'bi-grid-3x3-gap', nombre: 'Pisos/Azulejos', tags: ['piso', 'ceramica', 'azulejo', 'loseta', 'porcelanato'] },
            { clase: 'bi-snow2', nombre: 'Aire Acondicionado', tags: ['aire', 'acondicionado', 'frio', 'split', 'clima'] },
            { clase: 'bi-cup-hot-fill', nombre: 'Cafetería Premium', tags: ['cafe', 'premium', 'barista', 'latte'] },
            { clase: 'bi-droplet-fill', nombre: 'Bebidas/Bar', tags: ['bar', 'bebida', 'licor', 'cerveza', 'vino'] },
            { clase: 'bi-basket3', nombre: 'Bodega/Minimarket', tags: ['bodega', 'minimarket', 'abarrotes', 'tienda'] },
            { clase: 'bi-basket2', nombre: 'Frutería/Verduras', tags: ['fruta', 'verdura', 'mercado', 'organico'] },
            { clase: 'bi-clipboard2-pulse', nombre: 'Cardiología', tags: ['corazon', 'cardiologia', 'cardiologo', 'medico'] },
            { clase: 'bi-emoji-smile', nombre: 'Odontología', tags: ['diente', 'dentista', 'odontologia', 'sonrisa'] },
            { clase: 'bi-person-arms-up', nombre: 'Fisioterapia', tags: ['fisioterapia', 'rehabilitacion', 'terapia', 'ejercicio'] },
            { clase: 'bi-clipboard-heart', nombre: 'Nutrición', tags: ['nutricion', 'dieta', 'nutriologo', 'alimentacion'] },
            { clase: 'bi-person-check', nombre: 'Psicología', tags: ['psicologo', 'psicologia', 'terapia', 'mental'] },
            { clase: 'bi-gender-ambiguous', nombre: 'Ginecología', tags: ['ginecologia', 'mujer', 'maternidad', 'obstetricia'] },
            { clase: 'bi-emoji-sunglasses', nombre: 'Dermatología', tags: ['dermatologia', 'piel', 'dermatologo', 'acne'] },
            { clase: 'bi-piggy-bank', nombre: 'Agropecuaria', tags: ['cerdo', 'granja', 'agropecuaria', 'ganado'] },
            { clase: 'bi-bug-fill', nombre: 'Control de Plagas', tags: ['plaga', 'insecto', 'fumigacion', 'exterminador'] },
            { clase: 'bi-journal-bookmark', nombre: 'Abogados', tags: ['abogado', 'ley', 'juicio', 'legal', 'derecho'] },
            { clase: 'bi-file-earmark-text', nombre: 'Documentos/Trámites', tags: ['documento', 'tramite', 'certificado', 'partida'] },
            { clase: 'bi-building-fill-gear', nombre: 'Municipalidad', tags: ['municipalidad', 'gobierno', 'alcaldia', 'comuna'] },
            { clase: 'bi-person-vcard', nombre: 'Registro Civil', tags: ['registro', 'civil', 'partida', 'nacimiento', 'matrimonio'] },
            { clase: 'bi-gear', nombre: 'Industria', tags: ['industria', 'fabrica', 'manufactura', 'produccion'] },
            { clase: 'bi-gear-fill', nombre: 'Maquinaria', tags: ['maquinaria', 'equipo', 'industrial', 'pesado'] },
            { clase: 'bi-box', nombre: 'Embalaje/Empaque', tags: ['embalaje', 'empaque', 'caja', 'envase'] },
            { clase: 'bi-minecart-loaded', nombre: 'Minería', tags: ['mineria', 'mina', 'mineral', 'extraccion'] },
            { clase: 'bi-scissors', nombre: 'Sastrería/Costura', tags: ['sastre', 'costura', 'tela', 'confeccion', 'modista'] },
            { clase: 'bi-tag', nombre: 'Etiquetas/Marcas', tags: ['marca', 'etiqueta', 'brand', 'logo'] },
            { clase: 'bi-printer-fill', nombre: 'Impresión Digital', tags: ['impresion', 'digital', 'banner', 'poster', 'plotter'] },
            { clase: 'bi-vector-pen', nombre: 'Diseño Gráfico', tags: ['diseno', 'grafico', 'logo', 'vector', 'ilustracion'] },
            { clase: 'bi-images', nombre: 'Galería de Arte', tags: ['galeria', 'arte', 'cuadro', 'exposicion'] },
            { clase: 'bi-calendar-heart', nombre: 'Bodas/Matrimonios', tags: ['boda', 'matrimonio', 'novia', 'wedding'] },
            { clase: 'bi-stars', nombre: 'Quinceañeros', tags: ['quince', 'fiesta', 'celebracion', 'cumple'] },
            { clase: 'bi-music-note-list', nombre: 'DJ/Sonido', tags: ['dj', 'sonido', 'musica', 'fiesta', 'discoteca'] },
            { clase: 'bi-train-front', nombre: 'Tren/Metro', tags: ['tren', 'metro', 'ferrocarril', 'estacion'] },
            { clase: 'bi-ev-front', nombre: 'Autos Eléctricos', tags: ['electrico', 'ev', 'tesla', 'ecologico'] },
            { clase: 'bi-truck-flatbed', nombre: 'Mudanzas/Fletes', tags: ['mudanza', 'flete', 'carga', 'transporte'] },
            { clase: 'bi-ev-station', nombre: 'Estación de Carga', tags: ['carga', 'electrica', 'estacion', 'bateria'] },
            { clase: 'bi-gpu-card', nombre: 'Gaming/Hardware', tags: ['gaming', 'gpu', 'hardware', 'gamer', 'componente'] },
            { clase: 'bi-database', nombre: 'Hosting/Servidores', tags: ['hosting', 'servidor', 'datos', 'nube', 'cloud'] },
            { clase: 'bi-hdd-network', nombre: 'Data Center', tags: ['datacenter', 'red', 'infraestructura', 'servidor'] },
            { clase: 'bi-usb-drive', nombre: 'Accesorios Tech', tags: ['usb', 'pendrive', 'accesorio', 'cable'] },
            { clase: 'bi-projector', nombre: 'Proyectores', tags: ['proyector', 'presentacion', 'pantalla', 'cine'] },
            { clase: 'bi-coin', nombre: 'Criptomonedas', tags: ['cripto', 'bitcoin', 'moneda', 'blockchain'] },
            { clase: 'bi-percent', nombre: 'Créditos/Intereses', tags: ['credito', 'interes', 'porcentaje', 'prestamo'] },
            { clase: 'bi-safe', nombre: 'Caja Fuerte/Bóveda', tags: ['caja fuerte', 'boveda', 'seguridad', 'valores'] },
            { clase: 'bi-cash-stack', nombre: 'Cambista', tags: ['cambio', 'efectivo', 'billetes', 'moneda'] },
            { clase: 'bi-easel', nombre: 'Taller/Curso', tags: ['taller', 'curso', 'capacitacion', 'clase'] },
            { clase: 'bi-person-video3', nombre: 'Clases Online', tags: ['online', 'virtual', 'zoom', 'videoclase'] },
            { clase: 'bi-backpack', nombre: 'Útiles Escolares', tags: ['mochila', 'escolar', 'utiles', 'colegio'] },
            { clase: 'bi-bookmark-star', nombre: 'Academia', tags: ['academia', 'instituto', 'preparatoria', 'examen'] },
            { clase: 'bi-stopwatch', nombre: 'Cronometraje', tags: ['cronometro', 'carrera', 'atletismo', 'maraton'] },
            { clase: 'bi-heart-fill', nombre: 'Yoga/Meditación', tags: ['yoga', 'meditacion', 'zen', 'mindfulness'] },
            { clase: 'bi-lightning-fill', nombre: 'Box/Artes Marciales', tags: ['box', 'karate', 'artes marciales', 'lucha'] },
            { clase: 'bi-heart-arrow', nombre: 'Adopción Animal', tags: ['adopcion', 'animal', 'rescate', 'refugio'] },
            { clase: 'bi-star', nombre: 'Iglesia/Templo', tags: ['iglesia', 'templo', 'religion', 'fe', 'culto'] },
            { clase: 'bi-bell', nombre: 'Campanario', tags: ['campana', 'iglesia', 'aviso', 'parroquia'] },
            { clase: 'bi-tsunami', nombre: 'Pesca/Marítimo', tags: ['pesca', 'mar', 'maritimo', 'embarcacion'] },
            { clase: 'bi-compass', nombre: 'Navegación', tags: ['navegacion', 'barco', 'brujula', 'puerto'] },
            { clase: 'bi-camera-reels', nombre: 'Productora', tags: ['productora', 'cine', 'video', 'filmacion'] },
            { clase: 'bi-display', nombre: 'Pantallas/LED', tags: ['pantalla', 'led', 'display', 'publicidad'] },
            { clase: 'bi-easel2', nombre: 'Señalética', tags: ['señaletica', 'letrero', 'cartel', 'banner'] },
            { clase: 'bi-fingerprint', nombre: 'Biometría', tags: ['huella', 'biometria', 'acceso', 'control'] },
            { clase: 'bi-pass', nombre: 'Membresías/Pases', tags: ['membresia', 'pase', 'tarjeta', 'club'] },
            { clase: 'bi-infinity', nombre: 'Suscripciones', tags: ['suscripcion', 'mensual', 'plan', 'servicio'] },
            { clase: 'bi-archive', nombre: 'Archivos/Almacén', tags: ['archivo', 'almacen', 'bodega', 'guardar'] },
            { clase: 'bi-pin-map', nombre: 'Delivery/Reparto', tags: ['delivery', 'reparto', 'envio', 'domicilio'] },
            { clase: 'bi-speedometer', nombre: 'Velocímetro/Tuning', tags: ['velocimetro', 'tuning', 'auto', 'rendimiento'] },
            { clase: 'bi-vinyl', nombre: 'Disquería/Vinilo', tags: ['disco', 'vinilo', 'musica', 'retro'] },
            { clase: 'bi-postcard', nombre: 'Invitaciones', tags: ['invitacion', 'postal', 'tarjeta', 'papel'] },
            { clase: 'bi-link-45deg', nombre: 'Enlaces/Redes', tags: ['link', 'enlace', 'url', 'conexion'] },
            { clase: 'bi-cloud-upload', nombre: 'Nube/Almacén Digital', tags: ['nube', 'cloud', 'upload', 'almacen'] },
            { clase: 'bi-cast', nombre: 'Streaming/TV', tags: ['streaming', 'tv', 'cast', 'transmision'] },
            { clase: 'bi-paperclip', nombre: 'Papelería/Oficina', tags: ['clip', 'papeleria', 'oficina', 'utiles'] },
            { clase: 'bi-rulers', nombre: 'Topografía/Medición', tags: ['topografia', 'medicion', 'ingenieria', 'plano'] },
            { clase: 'bi-radioactive', nombre: 'Químicos/Laboratorio', tags: ['quimico', 'reactivo', 'laboratorio', 'ciencia'] },
            { clase: 'bi-moisture', nombre: 'Riego/Jardines', tags: ['riego', 'jardin', 'cesped', 'paisajismo'] },
            { clase: 'bi-sunrise', nombre: 'Turismo Rural', tags: ['campo', 'rural', 'amanecer', 'naturaleza'] },
            { clase: 'bi-binoculars', nombre: 'Tours/Excursiones', tags: ['tour', 'excursion', 'guia', 'binoculares'] },
            { clase: 'bi-sign-turn-right', nombre: 'Autoescuela', tags: ['autoescuela', 'brevete', 'licencia', 'conducir'] },
            { clase: 'bi-currency-dollar', nombre: 'Economía/Finanzas', tags: ['dolar', 'economia', 'finanzas', 'dinero'] },
            { clase: 'bi-chat-left-quote', nombre: 'Asesoría Legal', tags: ['asesoria', 'legal', 'consulta', 'abogado'] }, { clase: 'bi-shop', nombre: 'Tienda/Local', tags: ['tienda', 'local', 'negocio', 'shop'] },
            { clase: 'bi-shop-window', nombre: 'Vitrina', tags: ['vitrina', 'boutique', 'escaparate', 'exhibicion'] },
            { clase: 'bi-basket', nombre: 'Mercado', tags: ['mercado', 'canasta', 'compras', 'market'] },
            { clase: 'bi-cart', nombre: 'Supermercado', tags: ['carrito', 'compras', 'cart', 'supermercado'] },
            { clase: 'bi-bag', nombre: 'Tienda Online', tags: ['bolsa', 'compras', 'bag', 'ecommerce'] },
            { clase: 'bi-tags', nombre: 'Ofertas/Descuentos', tags: ['oferta', 'descuento', 'etiqueta', 'precio', 'promocion'] },
            { clase: 'bi-cup-hot', nombre: 'Café', tags: ['cafe', 'cafeteria', 'bebida', 'coffee', 'caliente'] },
            { clase: 'bi-cup-straw', nombre: 'Bebidas/Jugos', tags: ['bebida', 'jugo', 'refresco', 'smoothie', 'batido'] },
            { clase: 'bi-egg-fried', nombre: 'Restaurante', tags: ['comida', 'food', 'gastronomia', 'cocina', 'restaurante'] },
            { clase: 'bi-cake2', nombre: 'Pastelería', tags: ['pastel', 'torta', 'dulce', 'bakery', 'pasteleria'] },
            { clase: 'bi-cookie', nombre: 'Panadería', tags: ['pan', 'galleta', 'dulce', 'panaderia', 'postre'] },
            { clase: 'bi-cpu', nombre: 'Tecnología', tags: ['tecnologia', 'tech', 'cpu', 'computadora', 'componentes'] },
            { clase: 'bi-phone', nombre: 'Celulares', tags: ['celular', 'phone', 'movil', 'smartphone'] },
            { clase: 'bi-laptop', nombre: 'Laptop/PC', tags: ['laptop', 'computadora', 'pc', 'tech'] },
            { clase: 'bi-pc-display', nombre: 'Computadoras', tags: ['pc', 'monitor', 'escritorio', 'computo'] },
            { clase: 'bi-smartwatch', nombre: 'Relojes/Wearables', tags: ['reloj', 'smartwatch', 'wearable', 'accesorio'] },
            { clase: 'bi-headphones', nombre: 'Audio/Audífonos', tags: ['audio', 'musica', 'audifonos', 'sonido'] },
            { clase: 'bi-speaker', nombre: 'Parlantes', tags: ['parlante', 'audio', 'bocina', 'altavoz'] },
            { clase: 'bi-wifi', nombre: 'Internet/WiFi', tags: ['internet', 'wifi', 'red', 'conectividad'] },
            { clase: 'bi-router', nombre: 'Redes', tags: ['router', 'internet', 'comunicacion', 'red', 'cableado'] },
            { clase: 'bi-printer', nombre: 'Imprenta', tags: ['imprenta', 'printer', 'impresion', 'copias'] },
            { clase: 'bi-camera', nombre: 'Fotografía', tags: ['foto', 'fotografia', 'camara', 'camera', 'estudio'] },
            { clase: 'bi-camera-video', nombre: 'Video/Producción', tags: ['video', 'grabacion', 'produccion', 'filmacion'] },
            { clase: 'bi-tv', nombre: 'Electrónica/TV', tags: ['electronica', 'tv', 'television', 'aparatos'] },
            { clase: 'bi-heart-pulse', nombre: 'Salud', tags: ['salud', 'health', 'medico', 'clinica', 'corazon'] },
            { clase: 'bi-hospital', nombre: 'Hospital/Clínica', tags: ['hospital', 'clinica', 'medico', 'emergencia'] },
            { clase: 'bi-capsule', nombre: 'Farmacia', tags: ['farmacia', 'medicina', 'drogueria', 'pastilla'] },
            { clase: 'bi-bandaid', nombre: 'Curaciones', tags: ['curacion', 'herida', 'topico', 'primeros auxilios'] },
            { clase: 'bi-eyeglasses', nombre: 'Óptica', tags: ['optica', 'lentes', 'vision', 'ojos'] },
            { clase: 'bi-activity', nombre: 'Laboratorio', tags: ['laboratorio', 'analisis', 'examen', 'resultado'] },
            { clase: 'bi-scissors', nombre: 'Peluquería', tags: ['peluqueria', 'corte', 'belleza', 'salon', 'barberia'] },
            { clase: 'bi-stars', nombre: 'Spa/Bienestar', tags: ['spa', 'bienestar', 'belleza', 'relax', 'masajes'] },
            { clase: 'bi-brush', nombre: 'Maquillaje/Uñas', tags: ['maquillaje', 'unas', 'cosmeticos', 'belleza'] },
            { clase: 'bi-tools', nombre: 'Ferretería', tags: ['ferreteria', 'herramientas', 'tools', 'hardware'] },
            { clase: 'bi-hammer', nombre: 'Construcción', tags: ['construccion', 'hammer', 'obra', 'martillo', 'albañil'] },
            { clase: 'bi-wrench', nombre: 'Mecánica', tags: ['mecanica', 'taller', 'wrench', 'llave', 'reparacion'] },
            { clase: 'bi-nut', nombre: 'Repuestos', tags: ['repuestos', 'tuerca', 'piezas', 'autopartes'] },
            { clase: 'bi-lightbulb', nombre: 'Electricidad', tags: ['electricidad', 'luz', 'electricista', 'iluminacion'] },
            { clase: 'bi-water', nombre: 'Gasfitería', tags: ['gasfiteria', 'agua', 'plomeria', 'tuberia'] },
            { clase: 'bi-paint-bucket', nombre: 'Pinturas', tags: ['pintura', 'color', 'decoracion', 'pintor'] },
            { clase: 'bi-house-gear', nombre: 'Servicios del Hogar', tags: ['hogar', 'servicio', 'reparacion', 'mantenimiento'] },
            { clase: 'bi-key', nombre: 'Cerrajería', tags: ['cerrajeria', 'llave', 'cerradura', 'puerta'] },
            { clase: 'bi-bricks', nombre: 'Materiales', tags: ['materiales', 'ladrillo', 'cemento', 'construccion'] },
            { clase: 'bi-car-front', nombre: 'Autos', tags: ['auto', 'carro', 'car', 'vehiculo', 'transporte'] },
            { clase: 'bi-bus-front', nombre: 'Transporte', tags: ['bus', 'transporte', 'pasajeros', 'viaje'] },
            { clase: 'bi-truck', nombre: 'Logística/Carga', tags: ['logistica', 'truck', 'envios', 'delivery', 'camion', 'mudanza'] },
            { clase: 'bi-bicycle', nombre: 'Bicicletas', tags: ['bicicleta', 'bicycle', 'ciclismo', 'bike'] },
            { clase: 'bi-scooter', nombre: 'Motos/Scooter', tags: ['moto', 'scooter', 'motor', 'motocicleta'] },
            { clase: 'bi-fuel-pump', nombre: 'Grifo/Gasolinera', tags: ['grifo', 'gasolinera', 'combustible', 'gasolina'] },
            { clase: 'bi-droplet-half', nombre: 'Lavado de Autos', tags: ['lavado', 'car wash', 'limpieza', 'auto'] },
            { clase: 'bi-taxi-front', nombre: 'Taxi/Remisse', tags: ['taxi', 'remisse', 'transporte', 'chofer'] },
            { clase: 'bi-book', nombre: 'Educación', tags: ['educacion', 'libro', 'colegio', 'academia', 'escuela'] },
            { clase: 'bi-mortarboard', nombre: 'Universidad', tags: ['universidad', 'educacion', 'instituto', 'titulo'] },
            { clase: 'bi-pencil', nombre: 'Librería/Papelería', tags: ['libreria', 'lapiz', 'papeleria', 'utiles'] },
            { clase: 'bi-pen', nombre: 'Notaría/Legal', tags: ['notaria', 'abogado', 'firma', 'legal', 'tramites'] },
            { clase: 'bi-translate', nombre: 'Idiomas', tags: ['idiomas', 'ingles', 'traduccion', 'cursos'] },
            { clase: 'bi-journal-text', nombre: 'Biblioteca', tags: ['biblioteca', 'lectura', 'libros', 'revista'] },
            { clase: 'bi-building', nombre: 'Empresa/Oficina', tags: ['empresa', 'edificio', 'comercio', 'oficina'] },
            { clase: 'bi-bank', nombre: 'Banco/Finanzas', tags: ['banco', 'finanzas', 'dinero', 'bank', 'caja'] },
            { clase: 'bi-cash-coin', nombre: 'Préstamos', tags: ['prestamos', 'dinero', 'cash', 'credito', 'efectivo'] },
            { clase: 'bi-credit-card', nombre: 'Pagos/Tarjetas', tags: ['pago', 'tarjeta', 'visa', 'mastercard', 'credito'] },
            { clase: 'bi-wallet', nombre: 'Casa de Cambio', tags: ['cambio', 'dolares', 'divisas', 'billetera', 'moneda'] },
            { clase: 'bi-receipt', nombre: 'Contabilidad', tags: ['contabilidad', 'factura', 'receipt', 'impuestos'] },
            { clase: 'bi-house', nombre: 'Inmobiliaria', tags: ['inmobiliaria', 'casa', 'house', 'alquiler', 'venta'] },
            { clase: 'bi-shield-check', nombre: 'Seguridad', tags: ['seguridad', 'vigilancia', 'camaras', 'proteccion'] },
            { clase: 'bi-megaphone', nombre: 'Publicidad', tags: ['publicidad', 'marketing', 'megafono', 'promocion'] },
            { clase: 'bi-globe', nombre: 'Diseño Web', tags: ['internet', 'web', 'globe', 'digital', 'pagina'] },
            { clase: 'bi-briefcase', nombre: 'Negocios', tags: ['negocios', 'trabajo', 'oficina', 'empresa', 'maletin'] },
            { clase: 'bi-person-gear', nombre: 'Servicio Técnico', tags: ['servicios', 'profesional', 'tecnico', 'soporte'] },
            { clase: 'bi-people', nombre: 'RRHH/Agencia', tags: ['personal', 'rrhh', 'personas', 'agencia', 'empleo'] },
            { clase: 'bi-clipboard-data', nombre: 'Consultoría', tags: ['consultoria', 'asesoria', 'datos', 'gestion'] },
            { clase: 'bi-joystick', nombre: 'Videojuegos', tags: ['juegos', 'videojuegos', 'gamer', 'play', 'arcade'] },
            { clase: 'bi-controller', nombre: 'Consolas', tags: ['playstation', 'xbox', 'nintendo', 'juegos'] },
            { clase: 'bi-trophy', nombre: 'Deportes/Gym', tags: ['deportes', 'sport', 'trophy', 'trofeo', 'gym', 'fitness'] },
            { clase: 'bi-music-note', nombre: 'Música', tags: ['musica', 'music', 'audio', 'instrumentos', 'sonido'] },
            { clase: 'bi-mic', nombre: 'Karaoke/Eventos', tags: ['karaoke', 'microfono', 'canto', 'eventos', 'fiesta'] },
            { clase: 'bi-film', nombre: 'Cine/Teatro', tags: ['cine', 'pelicula', 'teatro', 'entretenimiento'] },
            { clase: 'bi-ticket-perforated', nombre: 'Entradas/Boletos', tags: ['ticket', 'boleto', 'eventos', 'concierto'] },
            { clase: 'bi-palette', nombre: 'Arte/Diseño', tags: ['arte', 'diseno', 'pintura', 'creatividad', 'grafico'] },
            { clase: 'bi-balloon', nombre: 'Fiestas/Eventos', tags: ['fiesta', 'evento', 'globo', 'celebracion', 'cumpleanos'] },
            { clase: 'bi-dice-5', nombre: 'Casino/Juegos', tags: ['casino', 'dado', 'juego', 'apuesta'] },
            { clase: 'bi-house-door', nombre: 'Muebles/Hogar', tags: ['muebles', 'sofa', 'hogar', 'furniture', 'sala', 'casa'] },
            { clase: 'bi-lamp', nombre: 'Decoración', tags: ['decoracion', 'lampara', 'hogar', 'adornos', 'iluminacion'] },
            { clase: 'bi-handbag', nombre: 'Moda/Ropa', tags: ['moda', 'ropa', 'handbag', 'boutique', 'vestidos'] },
            { clase: 'bi-gem', nombre: 'Joyería', tags: ['joyeria', 'joyas', 'gem', 'anillos', 'oro', 'plata'] },
            { clase: 'bi-watch', nombre: 'Relojería', tags: ['reloj', 'hora', 'joyeria', 'accesorio'] },
            { clase: 'bi-sunglasses', nombre: 'Accesorios', tags: ['lentes', 'sol', 'accesorio', 'moda'] },
            { clase: 'bi-flower1', nombre: 'Florería', tags: ['flores', 'jardin', 'plantas', 'vivero', 'floreria'] },
            { clase: 'bi-tree', nombre: 'Agro/Campo', tags: ['agro', 'campo', 'agricultura', 'plantas', 'vivero'] },
            { clase: 'bi-heart', nombre: 'Mascotas/Veterinaria', tags: ['mascotas', 'pets', 'animales', 'veterinaria', 'perros'] },
            { clase: 'bi-recycle', nombre: 'Reciclaje/Ecología', tags: ['reciclaje', 'ambiente', 'recycle', 'verde', 'ecologia'] },
            { clase: 'bi-airplane', nombre: 'Aerolínea/Viajes', tags: ['vuelo', 'avion', 'viaje', 'turismo', 'aerolinea'] },
            { clase: 'bi-compass', nombre: 'Turismo', tags: ['turismo', 'viaje', 'excursion', 'guia', 'aventura'] },
            { clase: 'bi-map', nombre: 'Mapas/Guía', tags: ['mapa', 'ubicacion', 'guia', 'direccion'] },
            { clase: 'bi-suitcase-lg', nombre: 'Hotel/Hospedaje', tags: ['hotel', 'hospedaje', 'habitacion', 'alojamiento'] },
            { clase: 'bi-globe-americas', nombre: 'Internacional', tags: ['mundo', 'global', 'exportacion', 'comercio exterior'] },
            { clase: 'bi-clock', nombre: 'Horarios/24h', tags: ['reloj', 'hora', '24 horas', 'tiempo', 'abierto'] },
            { clase: 'bi-geo-alt', nombre: 'Ubicación', tags: ['ubicacion', 'mapa', 'lugar', 'direccion', 'gps'] },
            { clase: 'bi-telephone', nombre: 'Call Center', tags: ['telefono', 'llamada', 'atencion', 'contacto'] },
            { clase: 'bi-envelope', nombre: 'Correo/Mensajería', tags: ['correo', 'email', 'mensaje', 'correspondencia'] },
            { clase: 'bi-box-seam', nombre: 'Paquetería/Envíos', tags: ['paquete', 'envio', 'caja', 'delivery', 'courier'] },
            { clase: 'bi-droplet', nombre: 'Agua/Lavandería', tags: ['agua', 'lavanderia', 'limpieza', 'gota'] },
            { clase: 'bi-fire', nombre: 'Bomberos/Emergencia', tags: ['fuego', 'bombero', 'emergencia', 'incendio'] },
            { clase: 'bi-snow', nombre: 'Refrigeración/Aire', tags: ['frio', 'aire', 'refrigeracion', 'clima'] },
            { clase: 'bi-lightning', nombre: 'Energía/Solar', tags: ['energia', 'solar', 'electrica', 'panel'] },
            { clase: 'bi-plug', nombre: 'Electricista', tags: ['enchufe', 'electricista', 'instalacion', 'voltaje'] },
            { clase: 'bi-gift', nombre: 'Regalos/Souvenirs', tags: ['regalo', 'obsequio', 'souvenir', 'detalle'] },
            { clase: 'bi-cart-check', nombre: 'Ventas al Mayor', tags: ['mayorista', 'distribuidor', 'venta', 'al por mayor'] },
            { clase: 'bi-person-badge', nombre: 'Identificación', tags: ['carnet', 'badge', 'id', 'identificacion', 'fotocheck'] },
            { clase: 'bi-calendar-event', nombre: 'Agenda/Citas', tags: ['cita', 'agenda', 'calendario', 'reserva'] },
            { clase: 'bi-bar-chart', nombre: 'Estadísticas', tags: ['grafico', 'estadistica', 'datos', 'reporte'] },
            { clase: 'bi-qr-code', nombre: 'QR/Digital', tags: ['qr', 'codigo', 'escanear', 'digital'] },
            { clase: 'bi-signpost', nombre: 'Señalización', tags: ['señal', 'cartel', 'letrero', 'rotulo'] },
            { clase: 'bi-door-open', nombre: 'Entrada/Salida', tags: ['puerta', 'entrada', 'salida', 'acceso'] },
            { clase: 'bi-fan', nombre: 'Ventilación/Clima', tags: ['ventilador', 'clima', 'aire', 'ventilacion'] },
            { clase: 'bi-ear', nombre: 'Audiología', tags: ['oido', 'audicion', 'audiologia', 'audifono'] },
            { clase: 'bi-thermometer-half', nombre: 'Calefacción', tags: ['calefaccion', 'temperatura', 'calor', 'clima'] },
            { clase: 'bi-trash', nombre: 'Limpieza/Residuos', tags: ['basura', 'limpieza', 'residuos', 'desechos'] },
            { clase: 'bi-eye', nombre: 'Vigilancia', tags: ['ojo', 'vigilancia', 'monitoreo', 'camara'] },
            { clase: 'bi-chat-dots', nombre: 'Atención al Cliente', tags: ['chat', 'atencion', 'soporte', 'ayuda'] },
            { clase: 'bi-basket2-fill', nombre: 'Fast Food', tags: ['comida rapida', 'fastfood', 'hamburguesa', 'pollo'] },
            { clase: 'bi-fire', nombre: 'Parrillas/BBQ', tags: ['parrilla', 'bbq', 'brasa', 'pollo', 'asado'] },
            { clase: 'bi-lungs', nombre: 'Neumología', tags: ['pulmon', 'respirar', 'neumologia', 'asma'] },
            { clase: 'bi-virus', nombre: 'Laboratorio Clínico', tags: ['virus', 'bacteria', 'laboratorio', 'examen'] },
            { clase: 'bi-prescription2', nombre: 'Receta Médica', tags: ['receta', 'medico', 'prescripcion', 'doctor'] },
            { clase: 'bi-bicycle', nombre: 'Ciclismo', tags: ['ciclismo', 'bicicleta', 'bike', 'ruta'] },
            { clase: 'bi-dribbble', nombre: 'Fútbol/Canchas', tags: ['futbol', 'cancha', 'deporte', 'pelota'] },
            { clase: 'bi-sun', nombre: 'Playa/Verano', tags: ['playa', 'sol', 'verano', 'piscina'] },
            { clase: 'bi-cloud-rain', nombre: 'Clima/Meteorología', tags: ['lluvia', 'clima', 'tiempo', 'pronostico'] },
            { clase: 'bi-tsunami', nombre: 'Piscinas/Agua', tags: ['piscina', 'agua', 'natacion', 'acuatico'] },
            { clase: 'bi-bug', nombre: 'Fumigación/Plagas', tags: ['fumigacion', 'plaga', 'insecto', 'control'] },
            { clase: 'bi-person-workspace', nombre: 'Coworking', tags: ['coworking', 'oficina', 'espacio', 'trabajo'] },
            { clase: 'bi-kanban', nombre: 'Gestión/Proyectos', tags: ['proyecto', 'gestion', 'kanban', 'tarea'] },
            { clase: 'bi-graph-up', nombre: 'Inversiones', tags: ['inversion', 'bolsa', 'acciones', 'trading'] },
            { clase: 'bi-award', nombre: 'Premios/Certificación', tags: ['premio', 'certificacion', 'reconocimiento', 'diploma'] },
            { clase: 'bi-code-slash', nombre: 'Programación/Software', tags: ['programacion', 'software', 'desarrollo', 'app', 'codigo'] },
            { clase: 'bi-robot', nombre: 'Automatización/IA', tags: ['robot', 'ia', 'inteligencia', 'automatizacion'] },
            { clase: 'bi-broadcast', nombre: 'Radio/Podcast', tags: ['radio', 'podcast', 'emisora', 'transmision'] },
            { clase: 'bi-newspaper', nombre: 'Prensa/Noticias', tags: ['diario', 'prensa', 'noticias', 'periodico'] },
            { clase: 'bi-megaphone-fill', nombre: 'Altavoz/Aviso', tags: ['altavoz', 'aviso', 'anuncio', 'comunicado'] },
            { clase: 'bi-shield-lock', nombre: 'Ciberseguridad', tags: ['ciberseguridad', 'datos', 'privacidad', 'proteccion'] },
            { clase: 'bi-flag', nombre: 'Gobierno/Municipal', tags: ['gobierno', 'municipalidad', 'bandera', 'estado'] },
            { clase: 'bi-life-preserver', nombre: 'Emergencias/Rescate', tags: ['emergencia', 'rescate', 'salvavidas', 'socorro'] },
            { clase: 'bi-lamp-fill', nombre: 'Iluminación', tags: ['lampara', 'luz', 'iluminacion', 'foco'] },
            { clase: 'bi-window', nombre: 'Vidriería/Ventanas', tags: ['vidrio', 'ventana', 'cristal', 'vidriera'] },
            { clase: 'bi-door-closed', nombre: 'Puertas/Portones', tags: ['puerta', 'porton', 'garage', 'acceso'] },
            { clase: 'bi-grid-3x3-gap', nombre: 'Pisos/Azulejos', tags: ['piso', 'ceramica', 'azulejo', 'loseta', 'porcelanato'] },
            { clase: 'bi-snow2', nombre: 'Aire Acondicionado', tags: ['aire', 'acondicionado', 'frio', 'split', 'clima'] },
            { clase: 'bi-cup-hot-fill', nombre: 'Cafetería Premium', tags: ['cafe', 'premium', 'barista', 'latte'] },
            { clase: 'bi-droplet-fill', nombre: 'Bebidas/Bar', tags: ['bar', 'bebida', 'licor', 'cerveza', 'vino'] },
            { clase: 'bi-basket3', nombre: 'Bodega/Minimarket', tags: ['bodega', 'minimarket', 'abarrotes', 'tienda'] },
            { clase: 'bi-basket2', nombre: 'Frutería/Verduras', tags: ['fruta', 'verdura', 'mercado', 'organico'] },
            { clase: 'bi-clipboard2-pulse', nombre: 'Cardiología', tags: ['corazon', 'cardiologia', 'cardiologo', 'medico'] },
            { clase: 'bi-emoji-smile', nombre: 'Odontología', tags: ['diente', 'dentista', 'odontologia', 'sonrisa'] },
            { clase: 'bi-person-arms-up', nombre: 'Fisioterapia', tags: ['fisioterapia', 'rehabilitacion', 'terapia', 'ejercicio'] },
            { clase: 'bi-clipboard-heart', nombre: 'Nutrición', tags: ['nutricion', 'dieta', 'nutriologo', 'alimentacion'] },
            { clase: 'bi-person-check', nombre: 'Psicología', tags: ['psicologo', 'psicologia', 'terapia', 'mental'] },
            { clase: 'bi-gender-ambiguous', nombre: 'Ginecología', tags: ['ginecologia', 'mujer', 'maternidad', 'obstetricia'] },
            { clase: 'bi-emoji-sunglasses', nombre: 'Dermatología', tags: ['dermatologia', 'piel', 'dermatologo', 'acne'] },
            { clase: 'bi-piggy-bank', nombre: 'Agropecuaria', tags: ['cerdo', 'granja', 'agropecuaria', 'ganado'] },
            { clase: 'bi-bug-fill', nombre: 'Control de Plagas', tags: ['plaga', 'insecto', 'fumigacion', 'exterminador'] },
            { clase: 'bi-journal-bookmark', nombre: 'Abogados', tags: ['abogado', 'ley', 'juicio', 'legal', 'derecho'] },
            { clase: 'bi-file-earmark-text', nombre: 'Documentos/Trámites', tags: ['documento', 'tramite', 'certificado', 'partida'] },
            { clase: 'bi-building-fill-gear', nombre: 'Municipalidad', tags: ['municipalidad', 'gobierno', 'alcaldia', 'comuna'] },
            { clase: 'bi-person-vcard', nombre: 'Registro Civil', tags: ['registro', 'civil', 'partida', 'nacimiento', 'matrimonio'] },
            { clase: 'bi-gear', nombre: 'Industria', tags: ['industria', 'fabrica', 'manufactura', 'produccion'] },
            { clase: 'bi-gear-fill', nombre: 'Maquinaria', tags: ['maquinaria', 'equipo', 'industrial', 'pesado'] },
            { clase: 'bi-box', nombre: 'Embalaje/Empaque', tags: ['embalaje', 'empaque', 'caja', 'envase'] },
            { clase: 'bi-minecart-loaded', nombre: 'Minería', tags: ['mineria', 'mina', 'mineral', 'extraccion'] },
            { clase: 'bi-scissors', nombre: 'Sastrería/Costura', tags: ['sastre', 'costura', 'tela', 'confeccion', 'modista'] },
            { clase: 'bi-tag', nombre: 'Etiquetas/Marcas', tags: ['marca', 'etiqueta', 'brand', 'logo'] },
            { clase: 'bi-printer-fill', nombre: 'Impresión Digital', tags: ['impresion', 'digital', 'banner', 'poster', 'plotter'] },
            { clase: 'bi-vector-pen', nombre: 'Diseño Gráfico', tags: ['diseno', 'grafico', 'logo', 'vector', 'ilustracion'] },
            { clase: 'bi-images', nombre: 'Galería de Arte', tags: ['galeria', 'arte', 'cuadro', 'exposicion'] },
            { clase: 'bi-calendar-heart', nombre: 'Bodas/Matrimonios', tags: ['boda', 'matrimonio', 'novia', 'wedding'] },
            { clase: 'bi-stars', nombre: 'Quinceañeros', tags: ['quince', 'fiesta', 'celebracion', 'cumple'] },
            { clase: 'bi-music-note-list', nombre: 'DJ/Sonido', tags: ['dj', 'sonido', 'musica', 'fiesta', 'discoteca'] },
            { clase: 'bi-train-front', nombre: 'Tren/Metro', tags: ['tren', 'metro', 'ferrocarril', 'estacion'] },
            { clase: 'bi-ev-front', nombre: 'Autos Eléctricos', tags: ['electrico', 'ev', 'tesla', 'ecologico'] },
            { clase: 'bi-truck-flatbed', nombre: 'Mudanzas/Fletes', tags: ['mudanza', 'flete', 'carga', 'transporte'] },
            { clase: 'bi-ev-station', nombre: 'Estación de Carga', tags: ['carga', 'electrica', 'estacion', 'bateria'] },
            { clase: 'bi-gpu-card', nombre: 'Gaming/Hardware', tags: ['gaming', 'gpu', 'hardware', 'gamer', 'componente'] },
            { clase: 'bi-database', nombre: 'Hosting/Servidores', tags: ['hosting', 'servidor', 'datos', 'nube', 'cloud'] },
            { clase: 'bi-hdd-network', nombre: 'Data Center', tags: ['datacenter', 'red', 'infraestructura', 'servidor'] },
            { clase: 'bi-usb-drive', nombre: 'Accesorios Tech', tags: ['usb', 'pendrive', 'accesorio', 'cable'] },
            { clase: 'bi-projector', nombre: 'Proyectores', tags: ['proyector', 'presentacion', 'pantalla', 'cine'] },
            { clase: 'bi-coin', nombre: 'Criptomonedas', tags: ['cripto', 'bitcoin', 'moneda', 'blockchain'] },
            { clase: 'bi-percent', nombre: 'Créditos/Intereses', tags: ['credito', 'interes', 'porcentaje', 'prestamo'] },
            { clase: 'bi-safe', nombre: 'Caja Fuerte/Bóveda', tags: ['caja fuerte', 'boveda', 'seguridad', 'valores'] },
            { clase: 'bi-cash-stack', nombre: 'Cambista', tags: ['cambio', 'efectivo', 'billetes', 'moneda'] },
            { clase: 'bi-easel', nombre: 'Taller/Curso', tags: ['taller', 'curso', 'capacitacion', 'clase'] },
            { clase: 'bi-person-video3', nombre: 'Clases Online', tags: ['online', 'virtual', 'zoom', 'videoclase'] },
            { clase: 'bi-backpack', nombre: 'Útiles Escolares', tags: ['mochila', 'escolar', 'utiles', 'colegio'] },
            { clase: 'bi-bookmark-star', nombre: 'Academia', tags: ['academia', 'instituto', 'preparatoria', 'examen'] },
            { clase: 'bi-stopwatch', nombre: 'Cronometraje', tags: ['cronometro', 'carrera', 'atletismo', 'maraton'] },
            { clase: 'bi-heart-fill', nombre: 'Yoga/Meditación', tags: ['yoga', 'meditacion', 'zen', 'mindfulness'] },
            { clase: 'bi-lightning-fill', nombre: 'Box/Artes Marciales', tags: ['box', 'karate', 'artes marciales', 'lucha'] },
            { clase: 'bi-heart-arrow', nombre: 'Adopción Animal', tags: ['adopcion', 'animal', 'rescate', 'refugio'] },
            { clase: 'bi-star', nombre: 'Iglesia/Templo', tags: ['iglesia', 'templo', 'religion', 'fe', 'culto'] },
            { clase: 'bi-bell', nombre: 'Campanario', tags: ['campana', 'iglesia', 'aviso', 'parroquia'] },
            { clase: 'bi-tsunami', nombre: 'Pesca/Marítimo', tags: ['pesca', 'mar', 'maritimo', 'embarcacion'] },
            { clase: 'bi-compass', nombre: 'Navegación', tags: ['navegacion', 'barco', 'brujula', 'puerto'] },
            { clase: 'bi-camera-reels', nombre: 'Productora', tags: ['productora', 'cine', 'video', 'filmacion'] },
            { clase: 'bi-display', nombre: 'Pantallas/LED', tags: ['pantalla', 'led', 'display', 'publicidad'] },
            { clase: 'bi-easel2', nombre: 'Señalética', tags: ['señaletica', 'letrero', 'cartel', 'banner'] },
            { clase: 'bi-fingerprint', nombre: 'Biometría', tags: ['huella', 'biometria', 'acceso', 'control'] },
            { clase: 'bi-pass', nombre: 'Membresías/Pases', tags: ['membresia', 'pase', 'tarjeta', 'club'] },
            { clase: 'bi-infinity', nombre: 'Suscripciones', tags: ['suscripcion', 'mensual', 'plan', 'servicio'] },
            { clase: 'bi-archive', nombre: 'Archivos/Almacén', tags: ['archivo', 'almacen', 'bodega', 'guardar'] },
            { clase: 'bi-pin-map', nombre: 'Delivery/Reparto', tags: ['delivery', 'reparto', 'envio', 'domicilio'] },
            { clase: 'bi-speedometer', nombre: 'Velocímetro/Tuning', tags: ['velocimetro', 'tuning', 'auto', 'rendimiento'] },
            { clase: 'bi-vinyl', nombre: 'Disquería/Vinilo', tags: ['disco', 'vinilo', 'musica', 'retro'] },
            { clase: 'bi-postcard', nombre: 'Invitaciones', tags: ['invitacion', 'postal', 'tarjeta', 'papel'] },
            { clase: 'bi-link-45deg', nombre: 'Enlaces/Redes', tags: ['link', 'enlace', 'url', 'conexion'] },
            { clase: 'bi-cloud-upload', nombre: 'Nube/Almacén Digital', tags: ['nube', 'cloud', 'upload', 'almacen'] },
            { clase: 'bi-cast', nombre: 'Streaming/TV', tags: ['streaming', 'tv', 'cast', 'transmision'] },
            { clase: 'bi-paperclip', nombre: 'Papelería/Oficina', tags: ['clip', 'papeleria', 'oficina', 'utiles'] },
            { clase: 'bi-rulers', nombre: 'Topografía/Medición', tags: ['topografia', 'medicion', 'ingenieria', 'plano'] },
            { clase: 'bi-radioactive', nombre: 'Químicos/Laboratorio', tags: ['quimico', 'reactivo', 'laboratorio', 'ciencia'] },
            { clase: 'bi-moisture', nombre: 'Riego/Jardines', tags: ['riego', 'jardin', 'cesped', 'paisajismo'] },
            { clase: 'bi-sunrise', nombre: 'Turismo Rural', tags: ['campo', 'rural', 'amanecer', 'naturaleza'] },
            { clase: 'bi-binoculars', nombre: 'Tours/Excursiones', tags: ['tour', 'excursion', 'guia', 'binoculares'] },
            { clase: 'bi-sign-turn-right', nombre: 'Autoescuela', tags: ['autoescuela', 'brevete', 'licencia', 'conducir'] },
            { clase: 'bi-currency-dollar', nombre: 'Economía/Finanzas', tags: ['dolar', 'economia', 'finanzas', 'dinero'] },
            { clase: 'bi-chat-left-quote', nombre: 'Asesoría Legal', tags: ['asesoria', 'legal', 'consulta', 'abogado'] },
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