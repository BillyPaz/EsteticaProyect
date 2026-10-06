<?php
/**
 * modulos/mantenimiento/servicios/php/buscar_servicios.php
 * Busca servicios por nombre. Si no hay búsqueda, devuelve todos los activos.
 * Se usa en el buscador del formulario embebido de combo.
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

$q = trim($_GET['q'] ?? '');

try {
    $conn = conexionBD();

    if ($q === '') {
        $stmt = $conn->query("SELECT id_servicio, nombreServicio, costoServicio, duracion
                              FROM servicios
                              WHERE activo = 1
                              ORDER BY nombreServicio ASC");
    } else {
        $stmt = $conn->prepare("SELECT id_servicio, nombreServicio, costoServicio, duracion
                                FROM servicios
                                WHERE activo = 1 AND nombreServicio LIKE :q
                                ORDER BY nombreServicio ASC");
        $stmt->execute([':q' => '%' . $q . '%']);
    }

    $servicios = $stmt->fetchAll();

    echo json_encode(['success' => true, 'servicios' => $servicios]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error en el servidor.']);
}