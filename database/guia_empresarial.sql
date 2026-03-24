SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `banner_carrusel` (
  `id_banner` int(11) NOT NULL,
  `imagen` varchar(255) NOT NULL,
  `orden` int(11) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `tiempo_ms` int(11) NOT NULL DEFAULT 5000,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `banner_carrusel` (`id_banner`, `imagen`, `orden`, `activo`, `tiempo_ms`, `creado_en`) VALUES
(16, 'banner_1773942209_34cd198b.jpeg', 1, 1, 5000, '2026-03-19 17:43:29'),
(17, 'banner_1773942391_99c8f743.jpg', 2, 1, 5000, '2026-03-19 17:46:31'),
(18, 'banner_1773942664_88eca44a.jpg', 3, 1, 5000, '2026-03-19 17:51:04');

CREATE TABLE `categorias` (
  `id_categoria` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `icono` varchar(50) DEFAULT 'bi-briefcase'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `categorias` (`id_categoria`, `nombre`, `descripcion`, `icono`) VALUES
(1, 'Tecnología', 'Empresas relacionadas con software, hardware y servicios digitales', 'bi-cpu'),
(2, 'Restaurante', 'Restaurantes y negocios de comida', 'bi-egg-fried'),
(8, 'Ferreteria', NULL, 'bi-tools'),
(9, 'Tienda', NULL, 'bi-shop'),
(10, 'Comercio', NULL, 'bi-building'),
(11, 'Salud', NULL, 'bi-heart-pulse');

CREATE TABLE `empresas` (
  `id_empresa` int(11) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `nombre` varchar(150) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` varchar(200) DEFAULT NULL,
  `id_categoria` int(11) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `horario` varchar(100) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `ubicacion_link` varchar(500) DEFAULT NULL,
  `link_empresa` varchar(255) DEFAULT NULL,
  `vistas` int(11) DEFAULT 0,
  `destacada` tinyint(1) DEFAULT 0,
  `facebook` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `empresas` (`id_empresa`, `logo`, `nombre`, `email`, `telefono`, `direccion`, `id_categoria`, `fecha_registro`, `horario`, `descripcion`, `ubicacion_link`, `link_empresa`, `vistas`, `destacada`, `facebook`) VALUES
(12, '69b2e6c81a9ce_RosalRestaurant.jpg', 'El Rosal Restaurant', NULL, '977411702', 'Jr. Salaverry (8va cuadra), Huacho', 2, '2026-03-09 17:12:50', 'de 12 a 10 pm', 'La tradición de Huacho en tu paladar desde 1960. 🥘✨', 'https://www.google.com/maps/place/El+Rosal/@-11.1101762,-77.6124137,17z/data=!3m1!4b1!4m6!3m5!1s0x9106df0b73d6425f:0x6e3e28e69603d472!8m2!3d-11.1101762!4d-77.6124137!16s%2Fg%2F11bwpd_yh9?entry=ttu&g_ep=EgoyMDI2MDMwOC4wIKXMDSoASAFQAw%3D%3D', NULL, 0, 1, NULL),
(13, '69b2eb9d7814f_chifaespaña.jpg', 'Chifa España', NULL, '937 245 536', 'Av. 28 de Julio 544 – Huacho', 2, '2026-03-12 16:36:45', NULL, '¡EL AUTENTICO SABOR ORIENTAL TE ESPERA AQUI!', 'https://www.google.com/maps/place/Chifa+Espa%C3%B1a/@-11.1075533,-77.6095983,19z/data=!4m6!3m5!1s0x9106df7536dc3f1b:0x75454f4e78f60660!8m2!3d-11.1073572!4d-77.6095098!16s%2Fg%2F1tjs5_st?entry=ttu&g_ep=EgoyMDI2MDMxMS4wIKXMDSoASAFQAw%3D%3D', NULL, 0, 1, NULL),
(14, '69b82b6ce767e_lachutana.jpg', 'La Chutana-Lubricentro', NULL, '994337831', 'Av. Cruz Blanca 1890 Santa María, Huaura, Peru, 15138', 8, '2026-03-16 16:10:20', NULL, 'En La Chutana Lubricentro engreimos a tu fierro con productos de la mejor calidad y productos :)', 'https://www.google.com/maps/place/Av.+Cruz+Blanca+1890,+Huacho+15137/@-11.0985195,-77.5959335,17z/data=!3m1!4b1!4m5!3m4!1s0x9106df907e002ea1:0x3e3fe5d41e672a7e!8m2!3d-11.0985195!4d-77.5959335?entry=ttu&g_ep=EgoyMDI2MDMxMS4wIKXMDSoASAFQAw%3D%3D', NULL, 1, 1, 'https://www.facebook.com/lachutana.huacho'),
(15, '69c2b957bfeb7_Odontologia.jpg', 'Cruzado Odontologia Especializada', NULL, '945 651 054', 'Prologación Miguel Grau 162 - 2do piso, Huacho, Peru', 11, '2026-03-24 16:18:09', NULL, 'Instalaciones modernas, alta calidad de equipos e insumos que en combinación con la ética profesional, brinda atención óptima y segura.', 'https://www.google.com/maps/place/Cruzado+Odontolog%C3%ADa+Especializada/@-11.1080604,-77.604148,18z/data=!4m6!3m5!1s0x9106df557a2f5c59:0x5c3aa558e2691d18!8m2!3d-11.1080578!4d-77.6043518!16s%2Fg%2F11fk1b8y7c?entry=ttu&g_ep=EgoyMDI2MDMxOC4xIKXMDSoASAFQAw%3D%3D', NULL, 0, 0, 'https://www.facebook.com/cruzadoodontologia');

CREATE TABLE `empresa_galeria` (
  `id_foto` int(11) NOT NULL,
  `id_empresa` int(11) NOT NULL,
  `foto` varchar(255) NOT NULL,
  `orden` int(11) DEFAULT 0,
  `fecha_subida` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

INSERT INTO `empresa_galeria` (`id_foto`, `id_empresa`, `foto`, `orden`, `fecha_subida`) VALUES
(1, 12, '1773329527_1.jpg', 0, '2026-03-12 15:32:07'),
(2, 12, '1773329527_2.jpg', 1, '2026-03-12 15:32:07'),
(3, 12, '1773329527_3.jpg', 2, '2026-03-12 15:32:07'),
(21, 12, '69b2e364614fd_4.jpg', 3, '2026-03-12 16:01:40'),
(29, 13, '69b2eb9d8494b_c4.jpg', 0, '2026-03-12 16:36:45'),
(30, 13, '69b2eb9d87203_c5.jpg', 0, '2026-03-12 16:36:45'),
(31, 13, '69b2eb9d883a0_c1.jpg', 0, '2026-03-12 16:36:45'),
(32, 13, '69b2eb9d8945f_c2.jpg', 0, '2026-03-12 16:36:45'),
(33, 13, '69b2eb9d8a625_c3.jpg', 0, '2026-03-12 16:36:45'),
(34, 14, '69b82c5be7b8f_L1.jpg', 0, '2026-03-16 16:14:19'),
(35, 14, '69b82c5bf22e9_L2.jpg', 1, '2026-03-16 16:14:19'),
(36, 14, '69b82c5bf3fc7_L3.jpg', 2, '2026-03-16 16:14:20'),
(37, 14, '69b82c5c03b96_L4.jpg', 3, '2026-03-16 16:14:20'),
(38, 15, '69c2b94139fe8_dientesblancos.jpg', 0, '2026-03-24 16:18:09'),
(39, 15, '69c2b9413df77_alineadores.jpg', 1, '2026-03-24 16:18:09'),
(40, 15, '69c2b9413ff1c_dolor.jpg', 2, '2026-03-24 16:18:09'),
(41, 15, '69c2b941414b9_sonrisa.jpg', 3, '2026-03-24 16:18:09');

CREATE TABLE `resenas` (
  `id_resena` int(11) NOT NULL,
  `id_empresa` int(11) NOT NULL,
  `nombre_autor` varchar(100) NOT NULL,
  `estrellas` tinyint(4) NOT NULL CHECK (`estrellas` between 1 and 5),
  `comentario` text NOT NULL,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

CREATE TABLE `sobre_info` (
  `id` int(11) NOT NULL,
  `clave` varchar(50) NOT NULL,
  `valor` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `sobre_info` (`id`, `clave`, `valor`) VALUES
(1, 'quienes_somos', 'Somos una plataforma digital dedicada a impulsar negocios locales de la región. Creemos que cada empresa merece visibilidad real, sin importar su tamaño'),
(2, 'mision', 'Conectar empresas locales con sus clientes de forma simple y efectiva, siendo el puente digital entre negocios y comunidad.'),
(3, 'vision', 'Convertirnos en la guía empresarial más completa y confiable de la región, siendo referente para quienes buscan negocios locales.'),
(4, 'por_que_1_titulo', '📍 Presencia Local'),
(5, 'por_que_1_texto', 'Tu negocio aparece cuando alguien busca productos o servicios en tu zona. Visibilidad donde más importa.'),
(6, 'por_que_2_titulo', '📱 Acceso Directo'),
(7, 'por_que_2_texto', 'Tus clientes te encuentran con un clic — WhatsApp, ubicación en Maps y galería de fotos en un solo lugar.'),
(8, 'por_que_3_titulo', '🚀 Fácil y Rápido'),
(9, 'por_que_3_texto', 'Registramos tu negocio por ti. Sin complicaciones técnicas, sin costos ocultos. Solo visibilidad real.');

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `contraseña_hash` varchar(255) NOT NULL,
  `rol` enum('admin','editor','viewer') DEFAULT 'viewer',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `email`, `contraseña_hash`, `rol`, `fecha_registro`) VALUES
(1, 'admin', '', '$2y$10$UWtdCE/XaEpLeuOeEFO9qu84jCuVLNxH6s0NnbdZXY20zEFzkgRZy', 'admin', '2026-02-19 17:21:26'),
(2, 'Editor1', NULL, '$2y$10$2fg4A5tyjju4Olxs3vAcle3.99zhD.XaRX.1./YbWvw/C6oZFeVR2', 'editor', '2026-02-19 17:38:36'),
(6, 'Editor2', NULL, '$2y$10$VBGGvidqG4ce09vTU4Vkg.goi0jvi9q4BnNQ9UlKShBVZ7TXQ5Gvm', 'editor', '2026-02-20 16:18:48');


ALTER TABLE `banner_carrusel`
  ADD PRIMARY KEY (`id_banner`),
  ADD KEY `idx_banner_orden` (`orden`,`activo`);

ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categoria`);

ALTER TABLE `empresas`
  ADD PRIMARY KEY (`id_empresa`),
  ADD KEY `id_categoria` (`id_categoria`),
  ADD KEY `idx_empresa_nombre` (`nombre`);

ALTER TABLE `empresa_galeria`
  ADD PRIMARY KEY (`id_foto`),
  ADD KEY `id_empresa` (`id_empresa`);

ALTER TABLE `resenas`
  ADD PRIMARY KEY (`id_resena`),
  ADD KEY `id_empresa` (`id_empresa`);

ALTER TABLE `sobre_info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `clave` (`clave`);

ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `email_2` (`email`);


ALTER TABLE `banner_carrusel`
  MODIFY `id_banner` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

ALTER TABLE `categorias`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

ALTER TABLE `empresas`
  MODIFY `id_empresa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

ALTER TABLE `empresa_galeria`
  MODIFY `id_foto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

ALTER TABLE `resenas`
  MODIFY `id_resena` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

ALTER TABLE `sobre_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;


ALTER TABLE `empresas`
  ADD CONSTRAINT `empresas_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`);

ALTER TABLE `empresa_galeria`
  ADD CONSTRAINT `empresa_galeria_ibfk_1` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id_empresa`) ON DELETE CASCADE;

ALTER TABLE `resenas`
  ADD CONSTRAINT `resenas_ibfk_1` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id_empresa`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
