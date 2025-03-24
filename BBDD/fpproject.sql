-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 05-03-2025 a las 12:13:38
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `fpproject`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fpproject`
--

CREATE TABLE `fpproject` (
  `id` int(11) NOT NULL,
  `instruccion` longtext NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `fpproject`
--

INSERT INTO `fpproject` (`id`, `instruccion`, `date`) VALUES
(1, '<p>Prueba</p>', '2025-03-05 12:12:59');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tb_data`
--

CREATE TABLE `tb_data` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `comment` varchar(150) NOT NULL,
  `date` varchar(50) NOT NULL,
  `reply_id` int(11) NOT NULL,
  `titulo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tb_data`
--

INSERT INTO `tb_data` (`id`, `name`, `comment`, `date`, `reply_id`, `titulo`) VALUES
(45, 'Courage', 'Me complace darles la bienvenida a este foro. Aquí, tenemos la oportunidad de compartir ideas, aprender unos de otros y construir una comunidad fuerte', ' Apr 26 2024, 10:15:23 AM', 0, '¡Hola a todos!'),
(46, 'Paco', 'No es lo que pedia', ' Apr 27 2024, 12:37:00 AM', 45, 'No me gusta');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellido` varchar(50) NOT NULL,
  `departamento` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contraseña` varchar(255) NOT NULL,
  `rol` enum('admin','usuario','invitado') DEFAULT 'usuario',
  `aprobado` tinyint(1) DEFAULT 0,
  `token_recuperacion` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `apellido`, `departamento`, `email`, `contraseña`, `rol`, `aprobado`, `token_recuperacion`) VALUES
(1, 'Courage', 'Egharevba', 'IT department', 'courageegharevba1@gmail.com', '$2y$10$iJsUFqZaWuMXbpngG.TcjOKrvVDp.ZzZAaAV5jEu/P/JsJgVzD9Xq', 'admin', 1, NULL),
(2, 'Courage', 'Egharevba', 'IT department', 'courage.egharevba@forvia.com', '$2y$10$dfOHrEbLcUlCJhCFPEJ53e6/l9cYWwT/5vwf7ucNjEll.DODWcvP2', 'usuario', 1, 'd4e93bf9186e8afb511494db5081094f6367389b8a3a6399aa3e9e7c5d0e4a2b');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `fpproject`
--
ALTER TABLE `fpproject`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tb_data`
--
ALTER TABLE `tb_data`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `fpproject`
--
ALTER TABLE `fpproject`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tb_data`
--
ALTER TABLE `tb_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
