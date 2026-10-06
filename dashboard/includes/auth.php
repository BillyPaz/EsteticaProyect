<?php
/**
 * auth.php
 * Verifica que exista una sesión activa.
 * Si no, redirige al login.
 * Si sí, deja disponibles las variables del usuario para el resto del dashboard.
 */

// 1. Arrancar sesión si no está arrancada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Si no hay usuario logueado → al login
if (empty($_SESSION['id_usuario'])) {
    header('Location: /Peluqueria/login/php/sesion.php');
    exit;
}

// 3. Datos disponibles para el resto del dashboard
$idUsuario     = (int) $_SESSION['id_usuario'];
$nombreUsuario = $_SESSION['nombre']     ?? 'Usuario';
$correoUsuario = $_SESSION['correo']     ?? '';
$idRol         = (int) ($_SESSION['id_rol'] ?? 0);
$nombreRol     = $_SESSION['nombre_rol'] ?? 'Usuario';
$permisos      = $_SESSION['permisos']   ?? [];

// 4. Si por alguna razón la sesión no tiene permisos, cerramos sesión (usuario sin acceso)
if (empty($permisos)) {
    session_unset();
    session_destroy();
    header('Location: /Peluqueria/login/php/sesion.php');
    exit;
}