<?php
/**
 * modulos/mantenimiento/servicios/php/listar_combos.php
 * Devuelve el listado de combos con sus servicios en JSON.
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

    // Combos
    $stmtC = $conn->query("SELECT idCombo, nombre, descripcion, precioCombo, incluyeBebida, activo, fechaRegistro
                           FROM combos
                           ORDER BY idCombo DESC");
    $combos = $stmtC->fetchAll();

    // Detalles de todos los combos
    $stmtD = $conn->query("SELECT cd.idCombo, s.id_servicio, s.nombreServicio, s.costoServicio, s.duracion
                           FROM combo_detalle cd
                           INNER JOIN servicios s ON s.id_servicio = cd.idServicio
                           ORDER BY cd.id_combo_detalle ASC");
    $detalles = $stmtD->fetchAll();

    // Agrupar detalles por combo
    $detallePorCombo = [];
    foreach ($detalles as $d) {
        $detallePorCombo[$d['idCombo']][] = $d;
    }

    // Adjuntar servicios a cada combo
    foreach ($combos as &$c) {
        $c['servicios'] = $detallePorCombo[$c['idCombo']] ?? [];
    }
    unset($c);

    echo json_encode(['success' => true, 'combos' => $combos]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error en el servidor.']);
}