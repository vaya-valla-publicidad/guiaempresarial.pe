SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `categorias` (
  `id_categoria` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `categorias` (`id_categoria`, `nombre`, `descripcion`) VALUES
(1, 'Tecnología', 'Empresas relacionadas con software, hardware y servicios digitales'),
(2, 'Gastronomía', 'Restaurantes y negocios de comida'),
(8, 'Ferreteria', NULL),
(9, 'Tienda', NULL),
(10, 'Comercio', NULL),
(11, 'Salud', NULL);

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
  `link_empresa` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `empresas` (`id_empresa`, `logo`, `nombre`, `email`, `telefono`, `direccion`, `id_categoria`, `fecha_registro`, `horario`, `descripcion`, `ubicacion_link`, `link_empresa`) VALUES
(3, NULL, 'Tech Solutions Perú', 'info@techsolutions.pe', '912345678', 'Av. Innovación 321, Huacho', 1, '2026-02-18 16:29:03', NULL, NULL, NULL, NULL),
(6, NULL, 'Restaurante El Sabor', 'contacto@elsabor.com', '988222333', 'Jr. Central 456, Huacho', 2, '2026-02-18 16:32:21', NULL, NULL, NULL, NULL),
(11, NULL, 'TechSoluciones Perú', NULL, '987226299', 'Av. Javier Prado Este 1234, San Isidro, Lima', 1, '2026-02-24 17:03:08', 'Lunes a Viernes 09:00 - 18:00 / Sábados 09:00 - 13:00', 'Soporte técnico empresarial, desarrollo web y mantenimiento de equipos informáticos.', NULL, 'https://techsoluciones.pe'),
(12, '1773162306_image.png', 'El Rosal Restaurant', NULL, '977411702', 'Jr. Salaverry (8va cuadra), Huacho', 2, '2026-03-09 17:12:50', 'de 12 a 10 pm', 'Si la imagen ya te dio hambre, imagínate el primer bocado. Nuestra Malaya Punto Cuy está lista para ser la protagonista de tu mesa. 🥘✨', 'https://www.google.com/maps/place/El+Rosal/@-11.1101762,-77.6124137,17z/data=!3m1!4b1!4m6!3m5!1s0x9106df0b73d6425f:0x6e3e28e69603d472!8m2!3d-11.1101762!4d-77.6124137!16s%2Fg%2F11bwpd_yh9?entry=ttu&g_ep=EgoyMDI2MDMwOC4wIKXMDSoASAFQAw%3D%3D', NULL);

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


ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categoria`);

ALTER TABLE `empresas`
  ADD PRIMARY KEY (`id_empresa`),
  ADD KEY `id_categoria` (`id_categoria`),
  ADD KEY `idx_empresa_nombre` (`nombre`);

ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `email_2` (`email`);


ALTER TABLE `categorias`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

ALTER TABLE `empresas`
  MODIFY `id_empresa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;


ALTER TABLE `empresas`
  ADD CONSTRAINT `empresas_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
