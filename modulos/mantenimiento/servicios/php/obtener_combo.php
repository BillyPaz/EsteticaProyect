<?php
/**
 * modulos/mantenimiento/servicios/php/obtener_combo.php
 * Devuelve los datos de un combo específico con sus servicios.
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

$idCombo = (int) ($_GET['id_combo'] ?? 0);

if ($idCombo <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de combo inválido.']);
    exit;
}

try {
    $conn = conexionBD();

    $stmt = $conn->prepare("SELECT idCombo, nombre, descripcion, precioCombo, incluyeBebida, activo
                            FROM combos
                            WHERE idCombo = :id LIMIT 1");
    $stmt->execute([':id' => $idCombo]);
    $combo = $stmt->fetch();

    if (!$combo) {
        echo json_encode(['success' => false, 'message' => 'El combo no existe.']);
        exit;
    }

    // Traer sus servicios
    $stmtS = $conn->prepare("SELECT s.id_servicio, s.nombreServicio, s.costoServicio, s.duracion
                             FROM combo_detalle cd
                             INNER JOIN servicios s ON s.id_servicio = cd.idServicio
                             WHERE cd.idCombo = :id
                             ORDER BY cd.id_combo_detalle ASC");
    $stmtS->execute([':id' => $idCombo]);
    $combo['servicios'] = $stmtS->fetchAll();

    echo json_encode(['success' => true, 'combo' => $combo]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error en el servidor.']);
}