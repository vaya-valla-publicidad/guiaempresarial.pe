SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE IF NOT EXISTS `categorias` (
  `id_categoria` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  PRIMARY KEY (`id_categoria`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `categorias` (`id_categoria`, `nombre`, `descripcion`) VALUES
(1, 'Tecnología', 'Empresas relacionadas con software, hardware y servicios digitales'),
(2, 'Gastronomía', 'Restaurantes y negocios de comida');

CREATE TABLE IF NOT EXISTS `empresas` (
  `id_empresa` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` varchar(200) DEFAULT NULL,
  `id_categoria` int(11) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `horario` varchar(100) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `latitud` decimal(10,8) DEFAULT NULL,
  `longitud` decimal(11,8) DEFAULT NULL,
  `link_empresa` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_empresa`),
  KEY `id_categoria` (`id_categoria`),
  KEY `idx_empresa_nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `empresas` (`id_empresa`, `nombre`, `email`, `telefono`, `direccion`, `id_categoria`, `fecha_registro`, `horario`, `descripcion`, `latitud`, `longitud`, `link_empresa`) VALUES
(3, 'Tech Solutions Perú', 'info@techsolutions.pe', '912345678', 'Av. Innovación 321, Huacho', 1, '2026-02-18 16:29:03', NULL, NULL, NULL, NULL, NULL),
(6, 'Restaurante El Sabor', 'contacto@elsabor.com', '988222333', 'Jr. Central 456, Huacho', 2, '2026-02-18 16:32:21', NULL, NULL, NULL, NULL, NULL),
(7, 'InnovaTech S.A.', 'contacto@innovatech.com', '987111222', 'Av. Tecnología 100, Lima', 1, '2026-02-19 14:47:46', NULL, NULL, NULL, NULL, NULL),
(9, 'GlobalSoft Corp.', 'info@globalsoft.mx', '987555666', 'Av. Digital 300, Ciudad de México', 1, '2026-02-19 14:47:46', NULL, NULL, NULL, NULL, NULL),
(11, 'TechSoluciones Perú', NULL, '987226299', 'Av. Javier Prado Este 1234, San Isidro, Lima', 1, '2026-02-24 17:03:08', 'Lunes a Viernes 09:00 - 18:00 / Sábados 09:00 - 13:00', 'Soporte técnico empresarial, desarrollo web y mantenimiento de equipos informáticos.', -12.09750000, -77.03650000, 'https://techsoluciones.pe');

CREATE TABLE IF NOT EXISTS `usuarios` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `contraseña_hash` varchar(255) NOT NULL,
  `rol` enum('admin','editor','viewer') DEFAULT 'viewer',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `email_2` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `email`, `contraseña_hash`, `rol`, `fecha_registro`) VALUES
(1, 'admin', '', '$2y$10$UWtdCE/XaEpLeuOeEFO9qu84jCuVLNxH6s0NnbdZXY20zEFzkgRZy', 'admin', '2026-02-19 17:21:26'),
(2, 'Editor1', NULL, '$2y$10$2fg4A5tyjju4Olxs3vAcle3.99zhD.XaRX.1./YbWvw/C6oZFeVR2', 'editor', '2026-02-19 17:38:36'),
(6, 'Editor2', NULL, '$2y$10$VBGGvidqG4ce09vTU4Vkg.goi0jvi9q4BnNQ9UlKShBVZ7TXQ5Gvm', 'editor', '2026-02-20 16:18:48');


ALTER TABLE `empresas`
  ADD CONSTRAINT `empresas_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
