-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 03-10-2026 a las 20:17:13
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
-- Base de datos: `estetica`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `acciones`
--

CREATE TABLE `acciones` (
  `id_accion` int(11) NOT NULL,
  `id_modulo` int(11) NOT NULL,
  `codigo` varchar(50) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `icono` varchar(50) DEFAULT NULL,
  `ruta` varchar(150) DEFAULT NULL,
  `orden` int(11) NOT NULL DEFAULT 0,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `fechaRegistro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `acciones`
--

INSERT INTO `acciones` (`id_accion`, `id_modulo`, `codigo`, `nombre`, `icono`, `ruta`, `orden`, `estado`, `fechaRegistro`) VALUES
(1, 1, 'emp-registrar', 'Registrar empleado', 'empleado-add', 'modulos/empleados/registrar/php/vista.php', 1, 1, '2026-09-03 23:21:38'),
(2, 1, 'emp-horario', 'Horario de empleado', 'reloj', 'modulos/empleados/horario/php/vista.php', 2, 1, '2026-09-04 00:22:50'),
(3, 1, 'emp-ausencia', 'Ausencia de empleado', 'calendario-x', 'modulos/empleados/ausencia/php/vista.php', 3, 1, '2026-09-04 22:45:31'),
(4, 1, 'emp-comisiones', 'Comisiones de empleado', 'porcentaje', 'modulos/empleados/comisiones/php/vista.php', 4, 1, '2026-09-04 22:46:03'),
(5, 2, 'servicios', 'Servicios', 'tijeras', 'modulos/mantenimiento/servicios/php/vista.php', 1, 1, '2026-09-04 22:52:42'),
(6, 3, 'inventario', 'Inventario', 'caja', 'modulos/mantenimiento/inventario/php/vista.php', 1, 1, '2026-09-04 22:58:15'),
(7, 2, 'horario-estetica', 'Horario estética', 'reloj', 'modulos/mantenimiento/horario-estetica/php/vista.php', 2, 1, '2026-09-04 23:07:49'),
(8, 2, 'productos', 'Productos', 'etiqueta', 'modulos/mantenimiento/productos/php/vista.php', 3, 1, '2026-09-04 23:07:54'),
(9, 2, 'membresias', 'Membresias', 'tarjeta', 'modulos/mantenimiento/membresias/php/vista.php', 4, 1, '2026-09-04 23:08:00'),
(10, 2, 'clientes', 'Clientes', 'usuario', 'modulos/mantenimiento/clientes/php/vista.php', 5, 1, '2026-09-04 23:08:04'),
(11, 4, 'ventas-realizar', 'Realizar venta', 'venta-add', 'modulos/ventas/realizar/php/vista.php', 1, 1, '2026-09-04 23:11:47'),
(12, 4, 'ventas-historial', 'Historial de ventas', 'historial', 'modulos/ventas/historial/php/vista.php', 2, 1, '2026-09-04 23:11:56'),
(13, 4, 'citas-pagas', 'Citas pagas', 'check-cita', 'modulos/ventas/citas-pagas/php/vista.php', 3, 1, '2026-09-04 23:12:03'),
(14, 5, 'citas', 'Citas', 'calendario', 'modulos/citas/php/vista.php', 1, 1, '2026-09-04 23:27:51'),
(15, 6, 'caja-apertura', 'Aperturar caja', 'caja-abrir', 'modulos/caja/apertura/php/vista.php', 1, 1, '2026-09-16 10:14:13'),
(16, 6, 'caja-cierre', 'Cierre de caja', 'caja-cerrar', 'modulos/caja/cierre/php/vista.php', 2, 1, '2026-09-16 10:14:13'),
(17, 7, 'accesos', 'Accesos', 'llave', 'modulos/seguridad/accesos/php/vista.php', 1, 1, '2026-09-16 10:14:13'),
(18, 7, 'modulos', 'Módulos', 'grid', 'modulos/seguridad/modulos/php/vista.php', 2, 1, '2026-09-16 10:14:13'),
(19, 7, 'auditoria', 'Auditoría', 'documento', 'modulos/seguridad/auditoria/php/vista.php', 3, 1, '2026-09-16 10:14:13');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `accion_rol`
--

CREATE TABLE `accion_rol` (
  `id_accion_rol` int(11) NOT NULL,
  `id_accion` int(11) NOT NULL,
  `id_rol` int(11) NOT NULL,
  `puedeCrear` tinyint(1) DEFAULT 0,
  `puedeModificar` tinyint(1) DEFAULT 0,
  `puedeConsultar` tinyint(1) DEFAULT 0,
  `puedeEliminar` tinyint(1) DEFAULT 0,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `fechaRegistro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `accion_rol`
--

INSERT INTO `accion_rol` (`id_accion_rol`, `id_accion`, `id_rol`, `puedeCrear`, `puedeModificar`, `puedeConsultar`, `puedeEliminar`, `estado`, `fechaRegistro`) VALUES
(1, 1, 1, 1, 1, 1, 1, 1, '2026-09-03 23:25:10'),
(2, 2, 1, 1, 1, 1, 1, 1, '2026-09-04 00:25:02'),
(3, 3, 1, 1, 1, 1, 1, 1, '2026-09-04 22:47:03'),
(4, 4, 1, 1, 1, 1, 1, 1, '2026-09-04 22:47:05'),
(7, 5, 1, 1, 1, 1, 1, 1, '2026-09-04 22:54:08'),
(9, 6, 1, 1, 1, 1, 1, 1, '2026-09-04 22:58:56'),
(10, 7, 1, 1, 1, 1, 1, 1, '2026-09-04 23:09:06'),
(11, 8, 1, 1, 1, 1, 1, 1, '2026-09-04 23:09:10'),
(12, 9, 1, 1, 1, 1, 1, 1, '2026-09-04 23:09:14'),
(13, 10, 1, 1, 1, 1, 1, 1, '2026-09-04 23:09:17'),
(14, 11, 1, 1, 1, 1, 1, 1, '2026-09-04 23:12:28'),
(15, 12, 1, 1, 1, 1, 1, 1, '2026-09-04 23:12:30'),
(16, 13, 1, 1, 1, 1, 1, 1, '2026-09-04 23:12:31'),
(18, 14, 1, 1, 1, 1, 1, 1, '2026-09-04 23:28:13'),
(19, 15, 1, 1, 1, 1, 1, 1, '2026-09-16 10:15:27'),
(20, 16, 1, 1, 1, 1, 1, 1, '2026-09-16 10:15:27'),
(21, 17, 1, 1, 1, 1, 1, 1, '2026-09-16 10:15:27'),
(22, 18, 1, 1, 1, 1, 1, 1, '2026-09-16 10:15:27'),
(23, 19, 1, 1, 1, 1, 1, 1, '2026-09-16 10:15:27'),
(35, 14, 2, 0, 0, 1, 0, 1, '2026-09-22 19:36:53'),
(36, 7, 2, 0, 0, 1, 0, 1, '2026-09-22 19:36:53'),
(37, 6, 2, 0, 0, 1, 0, 1, '2026-09-22 19:36:53'),
(38, 5, 2, 0, 0, 1, 0, 1, '2026-09-22 19:36:53'),
(39, 8, 2, 0, 0, 1, 0, 1, '2026-09-22 19:36:53'),
(40, 2, 2, 0, 0, 1, 0, 1, '2026-09-22 19:36:53'),
(41, 2, 4, 0, 0, 1, 0, 1, '2026-09-22 22:06:31'),
(42, 14, 4, 0, 0, 1, 0, 1, '2026-09-22 22:06:31'),
(43, 7, 4, 0, 0, 1, 0, 1, '2026-09-22 22:06:31'),
(44, 14, 5, 0, 0, 1, 0, 1, '2026-10-02 19:45:12'),
(45, 11, 5, 1, 0, 1, 0, 1, '2026-10-02 19:45:12'),
(46, 12, 5, 0, 0, 1, 0, 1, '2026-10-02 19:45:12'),
(47, 15, 5, 0, 0, 1, 0, 1, '2026-10-02 19:45:12'),
(48, 16, 5, 0, 0, 1, 0, 1, '2026-10-02 19:45:12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignacioncomision`
--

CREATE TABLE `asignacioncomision` (
  `id_asignacion_comision` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `porcentaje` decimal(5,2) NOT NULL,
  `fechaInicio` date NOT NULL,
  `fechaFin` date DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `fechaRegistro` datetime DEFAULT current_timestamp()
) ;

--
-- Volcado de datos para la tabla `asignacioncomision`
--

INSERT INTO `asignacioncomision` (`id_asignacion_comision`, `id_usuario`, `porcentaje`, `fechaInicio`, `fechaFin`, `estado`, `fechaRegistro`) VALUES
(1, 3, 10.00, '2026-09-28', '2026-12-30', 1, '2026-09-23 23:37:31'),
(2, 4, 25.00, '2026-09-28', NULL, 1, '2026-09-23 23:38:01'),
(3, 5, 5.00, '2026-10-01', '2026-11-30', 1, '2026-09-25 22:16:02'),
(4, 6, 20.00, '2026-10-05', '2026-12-07', 1, '2026-10-02 19:47:48');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ausencia_usuario`
--

CREATE TABLE `ausencia_usuario` (
  `id_ausencia_usuario` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fechaInicio` date NOT NULL,
  `fechaFin` date NOT NULL,
  `horaInicio` time DEFAULT NULL,
  `horaFin` time DEFAULT NULL,
  `motivo` enum('vacaciones','enfermedad','permiso','otro') NOT NULL,
  `observaciones` varchar(100) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `id_usuario_registro` int(11) NOT NULL,
  `fechaRegistro` datetime DEFAULT current_timestamp()
) ;

--
-- Volcado de datos para la tabla `ausencia_usuario`
--

INSERT INTO `ausencia_usuario` (`id_ausencia_usuario`, `id_usuario`, `fechaInicio`, `fechaFin`, `horaInicio`, `horaFin`, `motivo`, `observaciones`, `estado`, `id_usuario_registro`, `fechaRegistro`) VALUES
(1, 2, '2026-09-24', '2026-09-24', '10:00:00', '13:00:00', 'permiso', 'Tramite personal', 1, 1, '2026-09-23 22:40:11'),
(2, 3, '2026-09-28', '2026-10-05', NULL, NULL, 'vacaciones', 'Descanso', 1, 1, '2026-09-23 22:41:04'),
(3, 6, '2026-10-05', '2026-10-09', NULL, NULL, 'vacaciones', 'vacaciones', 1, 1, '2026-10-02 19:49:12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `caja`
--

CREATE TABLE `caja` (
  `id_caja` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha_apertura` datetime DEFAULT current_timestamp(),
  `fecha_cierre` datetime DEFAULT NULL,
  `montoInicial` decimal(12,2) NOT NULL,
  `montoFinal` decimal(12,2) DEFAULT NULL,
  `estado` enum('abierta','cerrada') NOT NULL DEFAULT 'abierta',
  `observaciones` varchar(75) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `caja_cierre`
--

CREATE TABLE `caja_cierre` (
  `id_cierre` int(11) NOT NULL,
  `id_caja` int(11) NOT NULL,
  `id_usuario_cierre` int(11) NOT NULL,
  `fecha_cierre` datetime NOT NULL DEFAULT current_timestamp(),
  `ventasTurno` decimal(12,2) NOT NULL DEFAULT 0.00,
  `montoEsperado` decimal(12,2) NOT NULL DEFAULT 0.00,
  `montoContado` decimal(12,2) NOT NULL DEFAULT 0.00,
  `diferencia` decimal(12,2) NOT NULL DEFAULT 0.00,
  `observaciones` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `caja_cierre_denominacion`
--

CREATE TABLE `caja_cierre_denominacion` (
  `id_detalle_cierre` int(11) NOT NULL,
  `id_cierre` int(11) NOT NULL,
  `valorDenominacion` decimal(6,2) NOT NULL,
  `cantidad` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `caja_detalle_denominacion`
--

CREATE TABLE `caja_detalle_denominacion` (
  `id_detalle_caja` int(11) NOT NULL,
  `id_caja` int(11) NOT NULL,
  `valorDenominacion` decimal(6,2) NOT NULL,
  `cantidad` int(11) NOT NULL
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id_categoria` int(11) NOT NULL,
  `nombreCategoria` varchar(50) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fechaRegistro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id_categoria`, `nombreCategoria`, `activo`, `fechaRegistro`) VALUES
(1, 'Cuerpo', 1, '2026-10-01 01:13:18'),
(2, 'Cabello', 1, '2026-10-01 11:05:37');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cierres_estetica`
--

CREATE TABLE `cierres_estetica` (
  `id_cierre_estetica` int(11) NOT NULL,
  `fechaInicio` date NOT NULL,
  `fechaFin` date NOT NULL,
  `horaInicio` time DEFAULT NULL,
  `horaFin` time DEFAULT NULL,
  `tipo` enum('vacaciones','remodelacion','imprevisto','feriado','otro') NOT NULL,
  `observaciones` varchar(100) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `id_usuario_registro` int(11) NOT NULL,
  `fechaRegistro` datetime DEFAULT current_timestamp()
) ;

--
-- Volcado de datos para la tabla `cierres_estetica`
--

INSERT INTO `cierres_estetica` (`id_cierre_estetica`, `fechaInicio`, `fechaFin`, `horaInicio`, `horaFin`, `tipo`, `observaciones`, `estado`, `id_usuario_registro`, `fechaRegistro`) VALUES
(1, '2026-10-19', '2026-10-23', NULL, NULL, 'remodelacion', 'Pintar por dentro', 1, 1, '2026-09-24 17:16:39');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `citas`
--

CREATE TABLE `citas` (
  `id_cita` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `estado` enum('reservada','confirmada','completada','cancelada') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cita_servicio`
--

CREATE TABLE `cita_servicio` (
  `id_cita_detalle` int(11) NOT NULL,
  `id_cita` int(11) NOT NULL,
  `id_servicio` int(11) NOT NULL,
  `costoServicio` decimal(12,2) NOT NULL,
  `fechaRegistro` datetime DEFAULT current_timestamp(),
  `id_combo` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id_cliente` int(11) NOT NULL,
  `nombreCliente` varchar(50) NOT NULL,
  `apellidoCliente` varchar(50) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `telefono2` varchar(20) DEFAULT NULL,
  `correo` varchar(75) DEFAULT NULL,
  `genero` enum('MASCULINO','FEMENINO','OTRO','NO_ESPECIFICADO') NOT NULL DEFAULT 'NO_ESPECIFICADO',
  `fechaRegistro` datetime DEFAULT current_timestamp(),
  `fechaActualizacion` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id_cliente`, `nombreCliente`, `apellidoCliente`, `telefono`, `telefono2`, `correo`, `genero`, `fechaRegistro`, `fechaActualizacion`, `estado`) VALUES
(1, 'Maria', 'Castillo', '1212 1212', NULL, 'castilla@gmail.com', 'FEMENINO', '2026-09-24 22:15:28', '2026-09-24 22:36:35', 1),
(2, 'Eduardo', 'Jerez', '0020 0202', '1020 2323', 'eduard4@gmail.com', 'MASCULINO', '2026-09-24 22:24:15', '2026-09-24 22:24:15', 1),
(3, 'arnoldo', 'gomez', '4512 4512', NULL, NULL, 'NO_ESPECIFICADO', '2026-09-25 22:20:46', '2026-09-25 22:20:46', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `combos`
--

CREATE TABLE `combos` (
  `idCombo` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `descripcion` varchar(150) DEFAULT NULL,
  `precioCombo` decimal(12,2) NOT NULL DEFAULT 0.00,
  `incluyeBebida` tinyint(1) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fechaRegistro` datetime DEFAULT current_timestamp(),
  `fechaActualizacion` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `combos`
--

INSERT INTO `combos` (`idCombo`, `nombre`, `descripcion`, `precioCombo`, `incluyeBebida`, `activo`, `fechaRegistro`, `fechaActualizacion`) VALUES
(1, 'Combo caballero 1', 'Corte+bebida', 50.00, 1, 1, '2026-09-24 16:58:20', '2026-09-24 16:58:20'),
(2, 'Combo Cabellero 2', 'Corte + lavado + bebida', 60.00, 1, 1, '2026-09-25 01:48:50', '2026-09-25 01:48:50'),
(3, 'Comno caballero 3', 'corte + tallado de barba + lavado', 100.00, 1, 1, '2026-09-25 22:18:46', '2026-09-25 22:18:46'),
(4, 'Combo Europa', 'Mascarilla facial express + Corte +', 85.00, 1, 1, '2026-10-02 19:54:02', '2026-10-02 19:54:02');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `combo_detalle`
--

CREATE TABLE `combo_detalle` (
  `id_combo_detalle` int(11) NOT NULL,
  `idCombo` int(11) DEFAULT NULL,
  `idServicio` int(11) DEFAULT NULL,
  `fechaRegistro` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `combo_detalle`
--

INSERT INTO `combo_detalle` (`id_combo_detalle`, `idCombo`, `idServicio`, `fechaRegistro`) VALUES
(1, 1, 3, '2026-09-24 22:58:20'),
(2, 2, 3, '2026-09-25 07:48:50'),
(3, 2, 4, '2026-09-25 07:48:50'),
(4, 3, 3, '2026-09-26 04:18:46'),
(5, 3, 5, '2026-09-26 04:18:46'),
(6, 3, 4, '2026-09-26 04:18:46'),
(7, 4, 8, '2026-10-03 01:54:02'),
(8, 4, 3, '2026-10-03 01:54:02');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comisiones`
--

CREATE TABLE `comisiones` (
  `id_comision` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_ventas_detalle_servicio` int(11) DEFAULT NULL,
  `montoComision` decimal(10,2) NOT NULL,
  `estado` enum('pendiente','pagada','confirmada') DEFAULT NULL,
  `fechaRegistro` datetime DEFAULT current_timestamp(),
  `fechaPago` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras`
--

CREATE TABLE `compras` (
  `id_compras` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fechaCompra` datetime DEFAULT current_timestamp(),
  `noSerie` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detallecompra`
--

CREATE TABLE `detallecompra` (
  `id_detalle_compra` int(11) NOT NULL,
  `id_compra` int(11) NOT NULL,
  `id_presentacion_prod` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precioUnitario` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horarios_empleados`
--

CREATE TABLE `horarios_empleados` (
  `id_horario_usuario` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `dias` enum('Lunes','Martes','Miércoles','Jueves','Viernes','Sabado','Domingo') DEFAULT NULL,
  `horaInicio` time DEFAULT NULL,
  `horaFin` time DEFAULT NULL,
  `fechaRegistro` datetime DEFAULT current_timestamp(),
  `estado` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `horarios_empleados`
--

INSERT INTO `horarios_empleados` (`id_horario_usuario`, `id_usuario`, `dias`, `horaInicio`, `horaFin`, `fechaRegistro`, `estado`) VALUES
(1, 5, 'Lunes', '10:00:00', '20:00:00', '2026-09-23 12:21:44', 1),
(2, 5, 'Martes', '12:00:00', '19:00:00', '2026-09-23 12:22:20', 1),
(3, 5, 'Miércoles', '08:00:00', '17:00:00', '2026-09-23 12:22:56', 1),
(4, 5, 'Jueves', '13:00:00', '20:00:00', '2026-09-23 12:23:41', 1),
(5, 5, 'Viernes', '08:00:00', '17:00:00', '2026-09-23 12:23:55', 1),
(6, 2, 'Lunes', '13:30:00', '20:30:00', '2026-09-23 12:25:04', 1),
(7, 2, 'Miércoles', '08:00:00', '17:00:00', '2026-09-23 16:40:18', 1),
(8, 6, 'Lunes', '09:00:00', '20:00:00', '2026-10-02 19:45:58', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horarios_estetica`
--

CREATE TABLE `horarios_estetica` (
  `id_horario_estetica` int(11) NOT NULL,
  `dias` enum('Lunes','Martes','Miércoles','Jueves','Viernes','Sabado','Domingo') NOT NULL,
  `horaApertura` time NOT NULL,
  `horaCierre` time NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `fechaRegistro` datetime DEFAULT current_timestamp()
) ;

--
-- Volcado de datos para la tabla `horarios_estetica`
--

INSERT INTO `horarios_estetica` (`id_horario_estetica`, `dias`, `horaApertura`, `horaCierre`, `estado`, `fechaRegistro`) VALUES
(1, 'Lunes', '09:00:00', '20:00:00', 1, '2026-09-24 17:13:39'),
(2, 'Martes', '09:00:00', '20:00:00', 1, '2026-09-24 17:14:10'),
(3, 'Miércoles', '08:00:00', '19:00:00', 1, '2026-09-25 10:39:18'),
(4, 'Jueves', '09:00:00', '19:00:00', 1, '2026-09-25 10:39:31'),
(5, 'Viernes', '09:00:00', '20:00:00', 1, '2026-09-25 10:40:06'),
(6, 'Sabado', '10:00:00', '18:00:00', 1, '2026-09-25 22:38:24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lotes`
--

CREATE TABLE `lotes` (
  `id_lote` int(11) NOT NULL,
  `id_detalle_compra` int(11) NOT NULL,
  `id_presentacion_prod` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `costoUnitario` decimal(10,2) NOT NULL,
  `fechaRegistro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `membresiacliente`
--

CREATE TABLE `membresiacliente` (
  `id_membresia_cliente` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `id_membresia` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fechaRegistro` datetime DEFAULT current_timestamp(),
  `estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `membresiacliente`
--

INSERT INTO `membresiacliente` (`id_membresia_cliente`, `id_cliente`, `id_membresia`, `id_usuario`, `fechaRegistro`, `estado`) VALUES
(1, 2, 2, 1, '2026-09-24 23:04:34', 0),
(2, 2, 3, 1, '2026-09-24 23:06:31', 1),
(3, 1, 1, 1, '2026-09-24 23:11:18', 1),
(4, 3, 1, 1, '2026-09-25 22:21:04', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `membresias`
--

CREATE TABLE `membresias` (
  `id_membresia` int(11) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `porcentajeDescuento` decimal(5,2) NOT NULL,
  `fechaRegistro` datetime DEFAULT current_timestamp(),
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `fechaActualizacion` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `membresias`
--

INSERT INTO `membresias` (`id_membresia`, `codigo`, `nombre`, `porcentajeDescuento`, `fechaRegistro`, `estado`, `fechaActualizacion`) VALUES
(1, '2601', 'Diamante', 14.00, '2026-09-24 22:51:51', 1, '2026-09-24 22:52:04'),
(2, '2602', 'Platino', 10.00, '2026-09-24 22:52:26', 1, '2026-09-24 22:52:26'),
(3, '2603', 'Oro', 5.00, '2026-09-24 22:52:38', 1, '2026-09-24 22:52:38');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modulo`
--

CREATE TABLE `modulo` (
  `id_modulo` int(11) NOT NULL,
  `codigo` varchar(50) DEFAULT NULL,
  `nombre` varchar(20) NOT NULL,
  `icono` varchar(50) DEFAULT NULL,
  `orden` int(11) NOT NULL DEFAULT 0,
  `estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `modulo`
--

INSERT INTO `modulo` (`id_modulo`, `codigo`, `nombre`, `icono`, `orden`, `estado`) VALUES
(1, 'empleados', 'empleados', 'empleado-add', 3, 1),
(2, 'mantenimientos', 'mantenimientos', 'tijeras', 4, 1),
(3, 'inventario', 'inventario', 'inventario', 5, 1),
(4, 'ventas', 'ventas', 'venta-add', 2, 1),
(5, 'citas', 'citas', 'calendario', 1, 1),
(6, 'caja', 'caja', 'caja-abrir', 6, 1),
(7, 'ajustes', 'ajustes', 'ajustes', 7, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos_inventario`
--

CREATE TABLE `movimientos_inventario` (
  `id_movimiento` int(11) NOT NULL,
  `id_presentacion_prod` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `tipo` enum('entrada','salida','ajuste') NOT NULL,
  `cantidad` int(11) NOT NULL,
  `costoUnitario` decimal(12,2) DEFAULT NULL,
  `fechaVencimiento` date DEFAULT NULL,
  `motivo` varchar(150) DEFAULT NULL,
  `referenciaTipo` varchar(30) DEFAULT NULL,
  `referenciaId` int(11) DEFAULT NULL,
  `fechaMovimiento` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `movimientos_inventario`
--

INSERT INTO `movimientos_inventario` (`id_movimiento`, `id_presentacion_prod`, `id_usuario`, `tipo`, `cantidad`, `costoUnitario`, `fechaVencimiento`, `motivo`, `referenciaTipo`, `referenciaId`, `fechaMovimiento`) VALUES
(1, 1, 1, 'entrada', 7, 30.00, '2028-06-20', 'Stock inicial', 'producto_inicial', NULL, '2026-10-01 01:14:38'),
(2, 1, 1, 'entrada', 5, 0.00, '2027-11-09', 'reabastecimiento', 'reabastecimiento', NULL, '2026-10-01 11:00:24'),
(3, 2, 1, 'entrada', 4, 80.00, '2027-06-16', 'Stock inicial', 'producto_inicial', NULL, '2026-10-01 11:06:43'),
(4, 3, 1, 'entrada', 2, 75.00, '2026-10-08', 'Stock inicial', 'producto_inicial', NULL, '2026-10-01 11:56:21'),
(5, 4, 1, 'entrada', 7, 75.00, '2026-10-12', 'Stock inicial', 'producto_inicial', NULL, '2026-10-01 11:58:33'),
(6, 5, 1, 'entrada', 4, 50.00, '2026-10-05', 'Stock inicial', 'producto_inicial', NULL, '2026-10-02 19:58:45');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presentacion`
--

CREATE TABLE `presentacion` (
  `id_presentacion` int(11) NOT NULL,
  `nombrePresentacion` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `presentacion`
--

INSERT INTO `presentacion` (`id_presentacion`, `nombrePresentacion`) VALUES
(1, '100 ml'),
(2, '200 ml'),
(3, '250 ml'),
(4, '90 ml');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presentacionprod`
--

CREATE TABLE `presentacionprod` (
  `id_presentacion_prod` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `id_presentacion` int(11) NOT NULL,
  `codigoBarra` varchar(50) DEFAULT NULL,
  `precioCompra` decimal(12,2) NOT NULL,
  `precioVenta` decimal(12,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `stockMinimo` int(11) NOT NULL DEFAULT 2,
  `fechaRegistro` datetime DEFAULT current_timestamp(),
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `presentacionprod`
--

INSERT INTO `presentacionprod` (`id_presentacion_prod`, `id_producto`, `id_presentacion`, `codigoBarra`, `precioCompra`, `precioVenta`, `stock`, `stockMinimo`, `fechaRegistro`, `activo`) VALUES
(1, 1, 1, '4512454511', 30.00, 65.00, 12, 4, '2026-10-01 01:14:38', 1),
(2, 2, 2, NULL, 80.00, 125.00, 4, 2, '2026-10-01 11:06:43', 1),
(3, 3, 3, NULL, 75.00, 120.00, 2, 2, '2026-10-01 11:56:21', 1),
(4, 4, 3, NULL, 75.00, 150.00, 7, 2, '2026-10-01 11:58:33', 1),
(5, 5, 4, NULL, 50.00, 100.00, 4, 2, '2026-10-02 19:58:45', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id_producto` int(11) NOT NULL,
  `id_categoria` int(11) DEFAULT NULL,
  `nombreProducto` varchar(50) NOT NULL,
  `observaciones` varchar(100) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fechaRegistro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id_producto`, `id_categoria`, `nombreProducto`, `observaciones`, `activo`, `fechaRegistro`) VALUES
(1, 1, 'Crema Men para el cuerpo', NULL, 1, '2026-10-01 01:14:38'),
(2, 2, 'Roz Salt', NULL, 1, '2026-10-01 11:06:43'),
(3, 2, 'Shampoo Briogeo', 'Alivio de la caspa', 1, '2026-10-01 11:56:21'),
(4, 2, 'Shampoo CeraVe Oil', NULL, 1, '2026-10-01 11:58:33'),
(5, 2, 'Hairlosoph gotas', NULL, 1, '2026-10-02 19:58:45');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `id_rol` int(11) NOT NULL,
  `nombreRol` varchar(25) NOT NULL,
  `descripcion` varchar(75) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `esSistema` tinyint(1) NOT NULL DEFAULT 0,
  `fechaRegistro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`id_rol`, `nombreRol`, `descripcion`, `estado`, `esSistema`, `fechaRegistro`) VALUES
(1, 'Administrador', NULL, 1, 1, '2026-08-26 06:14:30'),
(2, 'Barbero', 'Encargado de cortes', 1, 0, '2026-09-22 17:03:27'),
(3, 'Cajera', 'Encargado de cobrar citas y productos', 1, 0, '2026-09-22 17:05:10'),
(4, 'Estilista', 'Encargado de cambio de look', 1, 0, '2026-09-22 22:04:13'),
(5, 'Cajero', 'encargado del dinero', 1, 0, '2026-10-02 19:41:55');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol_usuario`
--

CREATE TABLE `rol_usuario` (
  `id_rol_usuario` int(11) NOT NULL,
  `id_rol` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `fechaRegistro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `rol_usuario`
--

INSERT INTO `rol_usuario` (`id_rol_usuario`, `id_rol`, `id_usuario`, `estado`, `fechaRegistro`) VALUES
(1, 1, 1, 1, '2026-08-26 06:14:53'),
(2, 2, 3, 1, '2026-09-22 17:03:41'),
(3, 2, 2, 1, '2026-09-22 17:05:21'),
(4, 4, 4, 1, '2026-09-22 19:39:41'),
(5, 4, 5, 1, '2026-09-22 22:04:26'),
(6, 5, 6, 1, '2026-10-02 19:42:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `serviciocitavalidacion`
--

CREATE TABLE `serviciocitavalidacion` (
  `id_servicio_cita_validacion` int(11) NOT NULL,
  `id_cita` int(11) NOT NULL,
  `id_servicio` int(11) NOT NULL,
  `idUsuario` int(11) NOT NULL,
  `EstadoServicio` tinyint(1) DEFAULT 1,
  `fechaValidacion` datetime DEFAULT current_timestamp(),
  `costoFinal` decimal(12,2) NOT NULL,
  `observaciones` varchar(75) DEFAULT NULL,
  `id_combo` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios`
--

CREATE TABLE `servicios` (
  `id_servicio` int(11) NOT NULL,
  `nombreServicio` varchar(50) NOT NULL,
  `costoServicio` decimal(12,2) NOT NULL,
  `duracion` int(10) UNSIGNED NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fechaRegistro` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `servicios`
--

INSERT INTO `servicios` (`id_servicio`, `nombreServicio`, `costoServicio`, `duracion`, `activo`, `fechaRegistro`) VALUES
(1, 'Alisado permanente', 350.00, 90, 1, '2026-09-24 18:28:10'),
(2, 'Mechas', 150.00, 65, 1, '2026-09-24 18:28:33'),
(3, 'Corte caballero completo', 50.00, 30, 1, '2026-09-24 22:54:29'),
(4, 'Lavado', 25.00, 15, 1, '2026-09-24 22:56:29'),
(5, 'Tallado de barba', 35.00, 15, 1, '2026-09-24 22:56:52'),
(6, 'Depilación de cejas', 25.00, 10, 1, '2026-09-24 22:59:32'),
(7, 'colochos', 300.00, 90, 1, '2026-09-26 04:17:25'),
(8, 'mascarilla facial', 40.00, 20, 1, '2026-10-03 01:50:47');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `usuariorolview`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `usuariorolview` (
`id_usuario` int(11)
,`usuario` varchar(151)
,`telefono` varchar(20)
,`correo` varchar(75)
,`estado` tinyint(1)
,`nombreRol` varchar(25)
,`ultimoAcceso` datetime
);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombres` varchar(75) NOT NULL,
  `apellidos` varchar(75) NOT NULL,
  `correo` varchar(75) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `direccion` varchar(100) DEFAULT NULL,
  `passwordHash` varchar(255) NOT NULL,
  `fechaRegistro` datetime DEFAULT current_timestamp(),
  `fechaActualizacion` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ultimoAcceso` datetime NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombres`, `apellidos`, `correo`, `telefono`, `direccion`, `passwordHash`, `fechaRegistro`, `fechaActualizacion`, `ultimoAcceso`, `estado`) VALUES
(1, 'Billy', 'Paz', 'billyfrancopaz@gmail.com', '42867897', 'Quetzaltenango', 'Sistema', '2026-08-24 23:40:02', '2026-10-02 19:39:39', '2026-10-02 19:39:39', 1),
(2, 'juana', 'juana', 'juana@gmail.com', '45421211', 'Coatepeque', '$2y$10$x3Z2tSOap7iTTs/MWFG76.nqc/TMaMtXufynYmdss4ErchSfxDuV.', '2026-09-22 11:58:07', '2026-09-22 11:58:07', '2026-09-22 11:58:07', 1),
(3, 'kevin', 'leonel', 'kevin@gmail.com', '52021212', 'Coatepeque', '$2y$10$66ruVZgdXkY4XWm4GqJ9O.0hjvK6dSwdGgBXcGvRrM7ZtakVF720G', '2026-09-22 12:10:05', '2026-09-22 12:10:05', '2026-09-22 12:10:05', 1),
(4, 'Javier', 'Perez', 'javiperez@gmail.com', '45124877', 'Coatepeque', '$2y$10$0vCJKvD36fOuLxugVrlscupDL8OhdQOSRh4Vgjgu5LkXJR7jXc0eS', '2026-09-22 19:39:12', '2026-09-22 19:40:00', '2026-09-22 19:40:00', 1),
(5, 'Geovani', 'Escobar', 'geo@gmail.com', '56232345', 'Coatepeque', '$2y$10$lCCutt63J9RKVjnVf7L6kuf3Iz36N487dYhMSPZjK519HnaLkXNPm', '2026-09-22 22:03:16', '2026-09-23 16:31:13', '2026-09-23 16:31:13', 1),
(6, 'mario', 'jerea', 'mario1@gmail.com', '00110022', 'Coatepeque', '$2y$10$3DsQglzKYrrPaNyZRPBwsukMYrpBqjpOaMVVP2XQhMfuxRvBQFmsG', '2026-10-02 19:41:01', '2026-10-02 20:03:59', '2026-10-02 20:03:59', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_servicios`
--

CREATE TABLE `usuario_servicios` (
  `id_empleado_servicios` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_servicio` int(11) NOT NULL,
  `estado` tinyint(1) DEFAULT 1,
  `fechaRegistro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id_venta` int(11) NOT NULL,
  `numeroVenta` varchar(20) NOT NULL,
  `id_cliente` int(11) DEFAULT NULL,
  `id_cita` int(11) DEFAULT NULL,
  `id_usuarioCaja` int(11) NOT NULL,
  `fechaVenta` datetime DEFAULT current_timestamp(),
  `observaciones` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventasdetalleproductos`
--

CREATE TABLE `ventasdetalleproductos` (
  `id_ventas_detalle_productos` int(11) NOT NULL,
  `id_venta` int(11) NOT NULL,
  `id_lote` int(11) NOT NULL,
  `observaciones` varchar(75) DEFAULT NULL,
  `cantidad` int(11) NOT NULL,
  `precioUnitario` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `fechaRegistro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventasdetalleservicio`
--

CREATE TABLE `ventasdetalleservicio` (
  `id_ventas_detalle_servicio` int(11) NOT NULL,
  `id_servicio_cita_validacion` int(11) NOT NULL,
  `id_venta` int(11) NOT NULL,
  `subtotalSinDescuento` decimal(12,2) NOT NULL,
  `descuento` decimal(12,2) NOT NULL DEFAULT 0.00,
  `subtotalConDescuento` decimal(12,2) NOT NULL,
  `observaciones` varchar(100) DEFAULT NULL,
  `fechaRegistro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `view_usuario_modulos`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `view_usuario_modulos` (
`id_usuario` int(11)
,`usuario` varchar(151)
,`id_rol` int(11)
,`nombreRol` varchar(25)
,`id_modulo` int(11)
,`moduloCodigo` varchar(50)
,`moduloNombre` varchar(20)
,`moduloIcono` varchar(50)
,`moduloOrden` int(11)
,`id_accion` int(11)
,`accionCodigo` varchar(50)
,`accionNombre` varchar(100)
,`accionIcono` varchar(50)
,`accionRuta` varchar(150)
,`accionOrden` int(11)
,`puedeCrear` tinyint(1)
,`puedeModificar` tinyint(1)
,`puedeConsultar` tinyint(1)
,`puedeEliminar` tinyint(1)
);

-- --------------------------------------------------------

--
-- Estructura para la vista `usuariorolview`
--
DROP TABLE IF EXISTS `usuariorolview`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `usuariorolview`  AS SELECT `us`.`id_usuario` AS `id_usuario`, concat(`us`.`nombres`,' ',`us`.`apellidos`) AS `usuario`, `us`.`telefono` AS `telefono`, `us`.`correo` AS `correo`, `us`.`estado` AS `estado`, `r`.`nombreRol` AS `nombreRol`, `us`.`ultimoAcceso` AS `ultimoAcceso` FROM ((`rol_usuario` `ru` join `usuarios` `us` on(`ru`.`id_usuario` = `us`.`id_usuario`)) join `rol` `r` on(`ru`.`id_rol` = `r`.`id_rol`)) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `view_usuario_modulos`
--
DROP TABLE IF EXISTS `view_usuario_modulos`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_usuario_modulos`  AS SELECT `u`.`id_usuario` AS `id_usuario`, concat(`u`.`nombres`,' ',`u`.`apellidos`) AS `usuario`, `r`.`id_rol` AS `id_rol`, `r`.`nombreRol` AS `nombreRol`, `m`.`id_modulo` AS `id_modulo`, `m`.`codigo` AS `moduloCodigo`, `m`.`nombre` AS `moduloNombre`, `m`.`icono` AS `moduloIcono`, `m`.`orden` AS `moduloOrden`, `a`.`id_accion` AS `id_accion`, `a`.`codigo` AS `accionCodigo`, `a`.`nombre` AS `accionNombre`, `a`.`icono` AS `accionIcono`, `a`.`ruta` AS `accionRuta`, `a`.`orden` AS `accionOrden`, `ar`.`puedeCrear` AS `puedeCrear`, `ar`.`puedeModificar` AS `puedeModificar`, `ar`.`puedeConsultar` AS `puedeConsultar`, `ar`.`puedeEliminar` AS `puedeEliminar` FROM (((((`usuarios` `u` join `rol_usuario` `ru` on(`ru`.`id_usuario` = `u`.`id_usuario` and `ru`.`estado` = 1)) join `rol` `r` on(`r`.`id_rol` = `ru`.`id_rol` and `r`.`estado` = 1)) join `accion_rol` `ar` on(`ar`.`id_rol` = `r`.`id_rol` and `ar`.`estado` = 1)) join `acciones` `a` on(`a`.`id_accion` = `ar`.`id_accion` and `a`.`estado` = 1)) join `modulo` `m` on(`m`.`id_modulo` = `a`.`id_modulo` and `m`.`estado` = 1)) WHERE `u`.`estado` = 1 ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `acciones`
--
ALTER TABLE `acciones`
  ADD PRIMARY KEY (`id_accion`),
  ADD UNIQUE KEY `uq_acciones_codigo` (`codigo`),
  ADD KEY `fk_modulo_accion` (`id_modulo`);

--
-- Indices de la tabla `accion_rol`
--
ALTER TABLE `accion_rol`
  ADD PRIMARY KEY (`id_accion_rol`),
  ADD UNIQUE KEY `uq_accion_rol` (`id_accion`,`id_rol`),
  ADD KEY `fk_accion_accionRol` (`id_accion`),
  ADD KEY `fk_rol_accionRol` (`id_rol`);

--
-- Indices de la tabla `asignacioncomision`
--
ALTER TABLE `asignacioncomision`
  ADD PRIMARY KEY (`id_asignacion_comision`),
  ADD KEY `fk_comision_usuario` (`id_usuario`);

--
-- Indices de la tabla `ausencia_usuario`
--
ALTER TABLE `ausencia_usuario`
  ADD PRIMARY KEY (`id_ausencia_usuario`),
  ADD KEY `fk_ausencia_usuario` (`id_usuario`),
  ADD KEY `fk_ausencia_registro` (`id_usuario_registro`);

--
-- Indices de la tabla `caja`
--
ALTER TABLE `caja`
  ADD PRIMARY KEY (`id_caja`),
  ADD KEY `fk_caja_usuario` (`id_usuario`);

--
-- Indices de la tabla `caja_cierre`
--
ALTER TABLE `caja_cierre`
  ADD PRIMARY KEY (`id_cierre`),
  ADD UNIQUE KEY `uq_caja_cierre` (`id_caja`),
  ADD KEY `fk_cierre_usuario` (`id_usuario_cierre`);

--
-- Indices de la tabla `caja_cierre_denominacion`
--
ALTER TABLE `caja_cierre_denominacion`
  ADD PRIMARY KEY (`id_detalle_cierre`),
  ADD KEY `fk_detalle_cierre` (`id_cierre`);

--
-- Indices de la tabla `caja_detalle_denominacion`
--
ALTER TABLE `caja_detalle_denominacion`
  ADD PRIMARY KEY (`id_detalle_caja`),
  ADD KEY `fk_detalle_caja` (`id_caja`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categoria`),
  ADD UNIQUE KEY `uk_categoria_nombre` (`nombreCategoria`);

--
-- Indices de la tabla `cierres_estetica`
--
ALTER TABLE `cierres_estetica`
  ADD PRIMARY KEY (`id_cierre_estetica`),
  ADD KEY `fk_cierre_registro` (`id_usuario_registro`);

--
-- Indices de la tabla `citas`
--
ALTER TABLE `citas`
  ADD PRIMARY KEY (`id_cita`),
  ADD KEY `fk_cita_usuario` (`id_usuario`),
  ADD KEY `fk_citas_cliente` (`id_cliente`);

--
-- Indices de la tabla `cita_servicio`
--
ALTER TABLE `cita_servicio`
  ADD PRIMARY KEY (`id_cita_detalle`),
  ADD KEY `fk_citaServicio_cita` (`id_cita`),
  ADD KEY `fk_citaServicio_servicio` (`id_servicio`),
  ADD KEY `fk_citaServicio_combo` (`id_combo`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_cliente`),
  ADD UNIQUE KEY `uq_cliente_correo` (`correo`),
  ADD KEY `idx_cliente_telefono` (`telefono`),
  ADD KEY `idx_cliente_nombre_apellido` (`nombreCliente`,`apellidoCliente`),
  ADD KEY `idx_cliente_telefono2` (`telefono2`);

--
-- Indices de la tabla `combos`
--
ALTER TABLE `combos`
  ADD PRIMARY KEY (`idCombo`);

--
-- Indices de la tabla `combo_detalle`
--
ALTER TABLE `combo_detalle`
  ADD PRIMARY KEY (`id_combo_detalle`),
  ADD UNIQUE KEY `uq_combo_servicio` (`idCombo`,`idServicio`),
  ADD KEY `fk_combo_servicio` (`idServicio`);

--
-- Indices de la tabla `comisiones`
--
ALTER TABLE `comisiones`
  ADD PRIMARY KEY (`id_comision`);

--
-- Indices de la tabla `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`id_compras`);

--
-- Indices de la tabla `detallecompra`
--
ALTER TABLE `detallecompra`
  ADD PRIMARY KEY (`id_detalle_compra`),
  ADD KEY `fk_compras_detalleCompra` (`id_compra`),
  ADD KEY `fk_compras_presentacionProd` (`id_presentacion_prod`);

--
-- Indices de la tabla `horarios_empleados`
--
ALTER TABLE `horarios_empleados`
  ADD PRIMARY KEY (`id_horario_usuario`),
  ADD KEY `fk_horarios_usuario` (`id_usuario`);

--
-- Indices de la tabla `horarios_estetica`
--
ALTER TABLE `horarios_estetica`
  ADD PRIMARY KEY (`id_horario_estetica`),
  ADD UNIQUE KEY `uq_horario_estetica_dia` (`dias`);

--
-- Indices de la tabla `lotes`
--
ALTER TABLE `lotes`
  ADD PRIMARY KEY (`id_lote`),
  ADD KEY `fk_lotes_detalleCompra` (`id_detalle_compra`),
  ADD KEY `fk_lotes_presentacionProd` (`id_presentacion_prod`);

--
-- Indices de la tabla `membresiacliente`
--
ALTER TABLE `membresiacliente`
  ADD PRIMARY KEY (`id_membresia_cliente`),
  ADD KEY `fk_membresiaCliente_usuario` (`id_usuario`),
  ADD KEY `fk_membresiaCliente_cliente` (`id_cliente`),
  ADD KEY `fk_membresiaCliente_membresia` (`id_membresia`);

--
-- Indices de la tabla `membresias`
--
ALTER TABLE `membresias`
  ADD PRIMARY KEY (`id_membresia`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indices de la tabla `modulo`
--
ALTER TABLE `modulo`
  ADD PRIMARY KEY (`id_modulo`),
  ADD UNIQUE KEY `uq_modulo_codigo` (`codigo`);

--
-- Indices de la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  ADD PRIMARY KEY (`id_movimiento`),
  ADD KEY `idx_mov_pprod` (`id_presentacion_prod`),
  ADD KEY `idx_mov_usuario` (`id_usuario`),
  ADD KEY `idx_mov_tipo` (`tipo`),
  ADD KEY `idx_mov_fecha` (`fechaMovimiento`),
  ADD KEY `idx_mov_vencimiento` (`fechaVencimiento`);

--
-- Indices de la tabla `presentacion`
--
ALTER TABLE `presentacion`
  ADD PRIMARY KEY (`id_presentacion`);

--
-- Indices de la tabla `presentacionprod`
--
ALTER TABLE `presentacionprod`
  ADD PRIMARY KEY (`id_presentacion_prod`),
  ADD UNIQUE KEY `uk_pprod_codigoBarra` (`codigoBarra`),
  ADD KEY `fk_producto_presentacion` (`id_producto`),
  ADD KEY `fk_presentacionProd_presentacion` (`id_presentacion`),
  ADD KEY `idx_pprod_activo` (`activo`),
  ADD KEY `idx_pprod_producto` (`id_producto`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id_producto`),
  ADD KEY `idx_producto_categoria` (`id_categoria`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`id_rol`);

--
-- Indices de la tabla `rol_usuario`
--
ALTER TABLE `rol_usuario`
  ADD PRIMARY KEY (`id_rol_usuario`),
  ADD UNIQUE KEY `uq_rol_usuario` (`id_rol`,`id_usuario`),
  ADD KEY `fk_rol_usuario` (`id_usuario`),
  ADD KEY `fk_rol_rol` (`id_rol`);

--
-- Indices de la tabla `serviciocitavalidacion`
--
ALTER TABLE `serviciocitavalidacion`
  ADD PRIMARY KEY (`id_servicio_cita_validacion`),
  ADD KEY `fk_servicioCitaValidacion_cita` (`id_cita`),
  ADD KEY `fk_servicioCitaValidacion_servicio` (`id_servicio`),
  ADD KEY `fk_servicioCitaValidacion_usuario` (`idUsuario`),
  ADD KEY `fk_servicioCitaValidacion_combo` (`id_combo`);

--
-- Indices de la tabla `servicios`
--
ALTER TABLE `servicios`
  ADD PRIMARY KEY (`id_servicio`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `uq_usuarios_correo` (`correo`),
  ADD KEY `idx_usuarios_nombres_apellidos` (`nombres`,`apellidos`);

--
-- Indices de la tabla `usuario_servicios`
--
ALTER TABLE `usuario_servicios`
  ADD PRIMARY KEY (`id_empleado_servicios`),
  ADD KEY `fk_servicios_usuario` (`id_usuario`),
  ADD KEY `fk_serviciosUsuario_servicio` (`id_servicio`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id_venta`),
  ADD UNIQUE KEY `numeroVenta` (`numeroVenta`),
  ADD KEY `fk_ventas_cliente` (`id_cliente`),
  ADD KEY `fk_ventas_cita` (`id_cita`),
  ADD KEY `fk_ventas_usuario` (`id_usuarioCaja`);

--
-- Indices de la tabla `ventasdetalleproductos`
--
ALTER TABLE `ventasdetalleproductos`
  ADD PRIMARY KEY (`id_ventas_detalle_productos`),
  ADD KEY `fk_ventasDetalleProductos_venta` (`id_venta`),
  ADD KEY `fk_ventasDetalleProductos_lote` (`id_lote`);

--
-- Indices de la tabla `ventasdetalleservicio`
--
ALTER TABLE `ventasdetalleservicio`
  ADD PRIMARY KEY (`id_ventas_detalle_servicio`),
  ADD KEY `fk_ventasDetalleServicio_servicioValidacio` (`id_servicio_cita_validacion`),
  ADD KEY `fk_ventasDetalleServicio_venta` (`id_venta`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `acciones`
--
ALTER TABLE `acciones`
  MODIFY `id_accion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `accion_rol`
--
ALTER TABLE `accion_rol`
  MODIFY `id_accion_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT de la tabla `asignacioncomision`
--
ALTER TABLE `asignacioncomision`
  MODIFY `id_asignacion_comision` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ausencia_usuario`
--
ALTER TABLE `ausencia_usuario`
  MODIFY `id_ausencia_usuario` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `caja`
--
ALTER TABLE `caja`
  MODIFY `id_caja` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `caja_cierre`
--
ALTER TABLE `caja_cierre`
  MODIFY `id_cierre` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `caja_cierre_denominacion`
--
ALTER TABLE `caja_cierre_denominacion`
  MODIFY `id_detalle_cierre` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `caja_detalle_denominacion`
--
ALTER TABLE `caja_detalle_denominacion`
  MODIFY `id_detalle_caja` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `cierres_estetica`
--
ALTER TABLE `cierres_estetica`
  MODIFY `id_cierre_estetica` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `citas`
--
ALTER TABLE `citas`
  MODIFY `id_cita` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cita_servicio`
--
ALTER TABLE `cita_servicio`
  MODIFY `id_cita_detalle` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `combos`
--
ALTER TABLE `combos`
  MODIFY `idCombo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `combo_detalle`
--
ALTER TABLE `combo_detalle`
  MODIFY `id_combo_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `comisiones`
--
ALTER TABLE `comisiones`
  MODIFY `id_comision` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `compras`
--
ALTER TABLE `compras`
  MODIFY `id_compras` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detallecompra`
--
ALTER TABLE `detallecompra`
  MODIFY `id_detalle_compra` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `horarios_empleados`
--
ALTER TABLE `horarios_empleados`
  MODIFY `id_horario_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `horarios_estetica`
--
ALTER TABLE `horarios_estetica`
  MODIFY `id_horario_estetica` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `lotes`
--
ALTER TABLE `lotes`
  MODIFY `id_lote` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `membresiacliente`
--
ALTER TABLE `membresiacliente`
  MODIFY `id_membresia_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `membresias`
--
ALTER TABLE `membresias`
  MODIFY `id_membresia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `modulo`
--
ALTER TABLE `modulo`
  MODIFY `id_modulo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  MODIFY `id_movimiento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `presentacion`
--
ALTER TABLE `presentacion`
  MODIFY `id_presentacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `presentacionprod`
--
ALTER TABLE `presentacionprod`
  MODIFY `id_presentacion_prod` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `rol_usuario`
--
ALTER TABLE `rol_usuario`
  MODIFY `id_rol_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `serviciocitavalidacion`
--
ALTER TABLE `serviciocitavalidacion`
  MODIFY `id_servicio_cita_validacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `servicios`
--
ALTER TABLE `servicios`
  MODIFY `id_servicio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `usuario_servicios`
--
ALTER TABLE `usuario_servicios`
  MODIFY `id_empleado_servicios` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id_venta` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ventasdetalleproductos`
--
ALTER TABLE `ventasdetalleproductos`
  MODIFY `id_ventas_detalle_productos` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ventasdetalleservicio`
--
ALTER TABLE `ventasdetalleservicio`
  MODIFY `id_ventas_detalle_servicio` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `acciones`
--
ALTER TABLE `acciones`
  ADD CONSTRAINT `fk_modulo_accion` FOREIGN KEY (`id_modulo`) REFERENCES `modulo` (`id_modulo`);

--
-- Filtros para la tabla `accion_rol`
--
ALTER TABLE `accion_rol`
  ADD CONSTRAINT `fk_accion_accionRol` FOREIGN KEY (`id_accion`) REFERENCES `acciones` (`id_accion`),
  ADD CONSTRAINT `fk_rol_accionRol` FOREIGN KEY (`id_rol`) REFERENCES `rol` (`id_rol`);

--
-- Filtros para la tabla `asignacioncomision`
--
ALTER TABLE `asignacioncomision`
  ADD CONSTRAINT `fk_comision_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `ausencia_usuario`
--
ALTER TABLE `ausencia_usuario`
  ADD CONSTRAINT `fk_ausencia_registro` FOREIGN KEY (`id_usuario_registro`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `fk_ausencia_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `caja`
--
ALTER TABLE `caja`
  ADD CONSTRAINT `fk_caja_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `caja_cierre`
--
ALTER TABLE `caja_cierre`
  ADD CONSTRAINT `fk_cierre_caja` FOREIGN KEY (`id_caja`) REFERENCES `caja` (`id_caja`),
  ADD CONSTRAINT `fk_cierre_usuario` FOREIGN KEY (`id_usuario_cierre`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `caja_cierre_denominacion`
--
ALTER TABLE `caja_cierre_denominacion`
  ADD CONSTRAINT `fk_detalle_cierre_caja` FOREIGN KEY (`id_cierre`) REFERENCES `caja_cierre` (`id_cierre`) ON DELETE CASCADE;

--
-- Filtros para la tabla `caja_detalle_denominacion`
--
ALTER TABLE `caja_detalle_denominacion`
  ADD CONSTRAINT `fk_detalle_caja` FOREIGN KEY (`id_caja`) REFERENCES `caja` (`id_caja`) ON DELETE CASCADE;

--
-- Filtros para la tabla `cierres_estetica`
--
ALTER TABLE `cierres_estetica`
  ADD CONSTRAINT `fk_cierre_registro` FOREIGN KEY (`id_usuario_registro`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `citas`
--
ALTER TABLE `citas`
  ADD CONSTRAINT `fk_cita_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `fk_citas_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`);

--
-- Filtros para la tabla `cita_servicio`
--
ALTER TABLE `cita_servicio`
  ADD CONSTRAINT `fk_citaServicio_cita` FOREIGN KEY (`id_cita`) REFERENCES `citas` (`id_cita`),
  ADD CONSTRAINT `fk_citaServicio_combo` FOREIGN KEY (`id_combo`) REFERENCES `combos` (`idCombo`),
  ADD CONSTRAINT `fk_citaServicio_servicio` FOREIGN KEY (`id_servicio`) REFERENCES `servicios` (`id_servicio`);

--
-- Filtros para la tabla `combo_detalle`
--
ALTER TABLE `combo_detalle`
  ADD CONSTRAINT `fk_combo_detalle_combo` FOREIGN KEY (`idCombo`) REFERENCES `combos` (`idCombo`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_combo_servicio` FOREIGN KEY (`idServicio`) REFERENCES `servicios` (`id_servicio`);

--
-- Filtros para la tabla `detallecompra`
--
ALTER TABLE `detallecompra`
  ADD CONSTRAINT `fk_compras_detalleCompra` FOREIGN KEY (`id_compra`) REFERENCES `compras` (`id_compras`),
  ADD CONSTRAINT `fk_compras_presentacionProd` FOREIGN KEY (`id_presentacion_prod`) REFERENCES `presentacionprod` (`id_presentacion_prod`);

--
-- Filtros para la tabla `horarios_empleados`
--
ALTER TABLE `horarios_empleados`
  ADD CONSTRAINT `fk_horarios_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `lotes`
--
ALTER TABLE `lotes`
  ADD CONSTRAINT `fk_lotes_detalleCompra` FOREIGN KEY (`id_detalle_compra`) REFERENCES `detallecompra` (`id_detalle_compra`),
  ADD CONSTRAINT `fk_lotes_presentacionProd` FOREIGN KEY (`id_presentacion_prod`) REFERENCES `presentacionprod` (`id_presentacion_prod`);

--
-- Filtros para la tabla `membresiacliente`
--
ALTER TABLE `membresiacliente`
  ADD CONSTRAINT `fk_membresiaCliente_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`),
  ADD CONSTRAINT `fk_membresiaCliente_membresia` FOREIGN KEY (`id_membresia`) REFERENCES `membresias` (`id_membresia`),
  ADD CONSTRAINT `fk_membresiaCliente_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  ADD CONSTRAINT `fk_mov_pprod` FOREIGN KEY (`id_presentacion_prod`) REFERENCES `presentacionprod` (`id_presentacion_prod`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mov_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `presentacionprod`
--
ALTER TABLE `presentacionprod`
  ADD CONSTRAINT `fk_presentacionProd_presentacion` FOREIGN KEY (`id_presentacion`) REFERENCES `presentacion` (`id_presentacion`),
  ADD CONSTRAINT `fk_producto_presentacion` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`);

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `fk_producto_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `rol_usuario`
--
ALTER TABLE `rol_usuario`
  ADD CONSTRAINT `fk_rol_rol` FOREIGN KEY (`id_rol`) REFERENCES `rol` (`id_rol`),
  ADD CONSTRAINT `fk_rol_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `serviciocitavalidacion`
--
ALTER TABLE `serviciocitavalidacion`
  ADD CONSTRAINT `fk_servicioCitaValidacion_cita` FOREIGN KEY (`id_cita`) REFERENCES `citas` (`id_cita`),
  ADD CONSTRAINT `fk_servicioCitaValidacion_combo` FOREIGN KEY (`id_combo`) REFERENCES `combos` (`idCombo`),
  ADD CONSTRAINT `fk_servicioCitaValidacion_servicio` FOREIGN KEY (`id_servicio`) REFERENCES `servicios` (`id_servicio`),
  ADD CONSTRAINT `fk_servicioCitaValidacion_usuario` FOREIGN KEY (`idUsuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `usuario_servicios`
--
ALTER TABLE `usuario_servicios`
  ADD CONSTRAINT `fk_serviciosUsuario_servicio` FOREIGN KEY (`id_servicio`) REFERENCES `servicios` (`id_servicio`),
  ADD CONSTRAINT `fk_servicios_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `fk_ventas_cita` FOREIGN KEY (`id_cita`) REFERENCES `citas` (`id_cita`),
  ADD CONSTRAINT `fk_ventas_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`),
  ADD CONSTRAINT `fk_ventas_usuario` FOREIGN KEY (`id_usuarioCaja`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `ventasdetalleproductos`
--
ALTER TABLE `ventasdetalleproductos`
  ADD CONSTRAINT `fk_ventasDetalleProductos_lote` FOREIGN KEY (`id_lote`) REFERENCES `lotes` (`id_lote`),
  ADD CONSTRAINT `fk_ventasDetalleProductos_venta` FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id_venta`);

--
-- Filtros para la tabla `ventasdetalleservicio`
--
ALTER TABLE `ventasdetalleservicio`
  ADD CONSTRAINT `fk_ventasDetalleServicio_servicioValidacio` FOREIGN KEY (`id_servicio_cita_validacion`) REFERENCES `serviciocitavalidacion` (`id_servicio_cita_validacion`),
  ADD CONSTRAINT `fk_ventasDetalleServicio_venta` FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id_venta`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
