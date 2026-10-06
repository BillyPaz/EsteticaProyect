<?php
/**
 * modulos/mantenimiento/servicios/php/listar_servicios.php
 * Devuelve el listado de servicios en JSON.
 */

if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../../dashboard/includes/auth.php';
require_once __DIR__ . '/../../../../dashboard/includes/permisos.php';
require_once __DIR__ . '/../../../../config/conexion.php';

if (!tienePermiso('servicios', 'ver')) {
    echo json_encode(['success' => false, 'message' => 'Sin permiso.']);
    exit;
}

try {
    $conn = conexionBD();
    $stmt = $conn->query("SELECT id_servicio, nombreServicio, costoServicio, duracion, activo, fechaRegistro
                          FROM servicios
                          ORDER BY id_servicio DESC");
    $servicios = $stmt->fetchAll();

    echo json_encode(['success' => true, 'servicios' => $servicios]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error en el servidor.']);
}