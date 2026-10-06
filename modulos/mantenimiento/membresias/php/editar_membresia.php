<?php
/**
 * modulos/mantenimiento/membresias/php/editar_membresia.php
 * Actualiza una membresía existente.
 */

if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../../dashboard/includes/auth.php';
require_once __DIR__ . '/../../../../dashboard/includes/permisos.php';
require_once __DIR__ . '/../../../../config/conexion.php';

if (!tienePermiso('membresias', 'editar')) {
    echo json_encode(['success' => false, 'message' => 'No tienes permiso para editar membresías.']);
    exit;
}

$idMembresia         = (int) ($_POST['id_membresia'] ?? 0);
$codigo              = trim($_POST['codigo'] ?? '');
$nombre              = trim($_POST['nombre'] ?? '');
$porcentajeDescuento = (float) ($_POST['porcentajeDescuento'] ?? 0);
$estado              = (int) ($_POST['estado'] ?? 1);

$errores = [];
if ($idMembresia <= 0) $errores[] = 'ID inválido.';
if ($codigo === '') $errores[] = 'El código es obligatorio.';
if ($nombre === '') $errores[] = 'El nombre es obligatorio.';
if ($porcentajeDescuento <= 0 || $porcentajeDescuento > 100) {
    $errores[] = 'El porcentaje debe estar entre 0.01 y 100.';
}
if (!in_array($estado, [0, 1], true)) $estado = 1;

if (!empty($errores)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errores)]);
    exit;
}

try {
    $conn = conexionBD();

    // Verificar que existe
    $check = $conn->prepare("SELECT id_membresia FROM membresias WHERE id_membresia = :id LIMIT 1");
    $check->execute([':id' => $idMembresia]);
    if (!$check->fetch()) {
        echo json_encode(['success' => false, 'message' => 'La membresía no existe.']);
        exit;
    }

    // Verificar que no haya OTRA con el mismo código
    $checkC = $conn->prepare("SELECT id_membresia FROM membresias WHERE codigo = :c AND id_membresia != :id LIMIT 1");
    $checkC->execute([':c' => $codigo, ':id' => $idMembresia]);
    if ($checkC->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Ya existe otra membresía con ese código.']);
        exit;
    }

    // Verificar que no haya OTRA con el mismo nombre
    $checkN = $conn->prepare("SELECT id_membresia FROM membresias WHERE nombre = :n AND id_membresia != :id LIMIT 1");
    $checkN->execute([':n' => $nombre, ':id' => $idMembresia]);
    if ($checkN->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Ya existe otra membresía con ese nombre.']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE membresias
                            SET codigo = :c, nombre = :n, porcentajeDescuento = :p, estado = :e
                            WHERE id_membresia = :id");
    $stmt->execute([
        ':c'  => $codigo,
        ':n'  => $nombre,
        ':p'  => $porcentajeDescuento,
        ':e'  => $estado,
        ':id' => $idMembresia,
    ]);

    echo json_encode(['success' => true, 'message' => 'Membresía actualizada correctamente.']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar en la base de datos.']);
}