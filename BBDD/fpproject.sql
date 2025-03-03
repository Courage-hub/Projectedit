-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 27-05-2024 a las 10:01:40
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
  `usuario` mediumtext NOT NULL,
  `instruccion` longtext NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `fpproject`
--

INSERT INTO `fpproject` (`id`, `usuario`, `instruccion`, `date`) VALUES
(1, '', '<h1 style=\"text-align: center; margin-left: 125px;\">&nbsp;Video de IA</h1><p style=\"text-align: center; margin-left: 225px;\">&nbsp;<iframe frameborder=\"0\" src=\"//www.youtube.com/embed/K4xGY3-4d14\" width=\"640\" height=\"360\" class=\"note-video-clip\"></iframe>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;</p>', '2024-05-20 13:56:35');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `fpproject`
--
ALTER TABLE `fpproject`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `fpproject`
--
ALTER TABLE `fpproject`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
