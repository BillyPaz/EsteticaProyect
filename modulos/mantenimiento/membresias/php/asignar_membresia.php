<?php
/**
 * modulos/mantenimiento/membresias/php/asignar_membresia.php
 * Asigna una membresía a un cliente.
 * Regla: un cliente solo puede tener UNA membresía activa.
 * Si tenía otra, se marca como inactiva (no se elimina).
 */

if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../../dashboard/includes/auth.php';
require_once __DIR__ . '/../../../../dashboard/includes/permisos.php';
require_once __DIR__ . '/../../../../config/conexion.php';

if (!tienePermiso('membresias', 'crear')) {
    echo json_encode(['success' => false, 'message' => 'No tienes permiso para asignar membresías.']);
    exit;
}

$idCliente   = (int) ($_POST['id_cliente']   ?? 0);
$idMembresia = (int) ($_POST['id_membresia'] ?? 0);
$idUsuario   = (int) $_SESSION['id_usuario'];   // El admin logueado

$errores = [];
if ($idCliente   <= 0) $errores[] = 'Debes seleccionar un cliente.';
if ($idMembresia <= 0) $errores[] = 'Debes seleccionar una membresía.';
if ($idUsuario   <= 0) $errores[] = 'Usuario no identificado.';

if (!empty($errores)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errores)]);
    exit;
}

try {
    $conn = conexionBD();

    // Verificar que el cliente existe y está activo
    $checkC = $conn->prepare("SELECT id_cliente FROM clientes WHERE id_cliente = :id AND estado = 1 LIMIT 1");
    $checkC->execute([':id' => $idCliente]);
    if (!$checkC->fetch()) {
        echo json_encode(['success' => false, 'message' => 'El cliente no existe o está inactivo.']);
        exit;
    }

    // Verificar que la membresía existe y está activa
    $checkM = $conn->prepare("SELECT id_membresia FROM membresias WHERE id_membresia = :id AND estado = 1 LIMIT 1");
    $checkM->execute([':id' => $idMembresia]);
    if (!$checkM->fetch()) {
        echo json_encode(['success' => false, 'message' => 'La membresía no existe o está inactiva.']);
        exit;
    }

    // Verificar si el cliente ya tiene esa misma membresía activa
    $checkDup = $conn->prepare("SELECT id_membresia_cliente FROM membresiacliente
                                WHERE id_cliente = :c AND id_membresia = :m AND estado = 1 LIMIT 1");
    $checkDup->execute([':c' => $idCliente, ':m' => $idMembresia]);
    if ($checkDup->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Este cliente ya tiene esa membresía activa.']);
        exit;
    }

    $conn->beginTransaction();

    // 1. Desactivar cualquier membresía activa previa del cliente
    $upd = $conn->prepare("UPDATE membresiacliente SET estado = 0 WHERE id_cliente = :c AND estado = 1");
    $upd->execute([':c' => $idCliente]);

    // 2. Insertar la nueva asignación
    $ins = $conn->prepare("INSERT INTO membresiacliente (id_cliente, id_membresia, id_usuario, estado)
                           VALUES (:c, :m, :u, 1)");
    $ins->execute([
        ':c' => $idCliente,
        ':m' => $idMembresia,
        ':u' => $idUsuario,
    ]);

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Membresía asignada correctamente.',
        'id_membresia_cliente' => (int) $conn->lastInsertId()
    ]);

} catch (PDOException $e) {
    if (isset($conn) && $conn->inTransaction()) $conn->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error al guardar en la base de datos.']);
}