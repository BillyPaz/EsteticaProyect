<?php
/**
 * modulos/mantenimiento/membresias/php/quitar_membresia.php
 * Marca una asignación de membresía como inactiva (no la elimina).
 */

if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../../dashboard/includes/auth.php';
require_once __DIR__ . '/../../../../dashboard/includes/permisos.php';
require_once __DIR__ . '/../../../../config/conexion.php';

if (!tienePermiso('membresias', 'eliminar')) {
    echo json_encode(['success' => false, 'message' => 'No tienes permiso para quitar membresías.']);
    exit;
}

$idAsignacion = (int) ($_POST['id_asignacion'] ?? 0);

if ($idAsignacion <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido.']);
    exit;
}

try {
    $conn = conexionBD();

    // Verificar que existe y está activa
    $check = $conn->prepare("SELECT id_membresia_cliente FROM membresiacliente
                             WHERE id_membresia_cliente = :id AND estado = 1 LIMIT 1");
    $check->execute([':id' => $idAsignacion]);
    if (!$check->fetch()) {
        echo json_encode(['success' => false, 'message' => 'La asignación no existe o ya está inactiva.']);
        exit;
    }

    // Marcar como inactiva
    $upd = $conn->prepare("UPDATE membresiacliente SET estado = 0 WHERE id_membresia_cliente = :id");
    $upd->execute([':id' => $idAsignacion]);

    echo json_encode(['success' => true, 'message' => 'Membresía quitada correctamente.']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar en la base de datos.']);
}