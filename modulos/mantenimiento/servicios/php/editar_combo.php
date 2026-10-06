<?php
/**
 * modulos/mantenimiento/servicios/php/editar_combo.php
 * Actualiza un combo existente y reemplaza sus servicios.
 */

if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../../dashboard/includes/auth.php';
require_once __DIR__ . '/../../../../dashboard/includes/permisos.php';
require_once __DIR__ . '/../../../../config/conexion.php';

if (!tienePermiso('servicios', 'editar')) {
    echo json_encode(['success' => false, 'message' => 'No tienes permiso para editar combos.']);
    exit;
}

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);

if (!$input || !is_array($input)) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
    exit;
}

$idCombo       = (int) ($input['idCombo'] ?? 0);
$nombre        = trim($input['nombre'] ?? '');
$descripcion   = trim($input['descripcion'] ?? '');
$precioCombo   = (float) ($input['precioCombo'] ?? 0);
$incluyeBebida = (int) ($input['incluyeBebida'] ?? 0);
$activo        = (int) ($input['activo'] ?? 1);
$servicios     = $input['servicios'] ?? [];

$errores = [];
if ($idCombo <= 0) $errores[] = 'ID de combo inválido.';
if ($nombre === '') $errores[] = 'El nombre del combo es obligatorio.';
if ($precioCombo <= 0) $errores[] = 'El precio del combo debe ser mayor a 0.';
if (!is_array($servicios) || count($servicios) === 0) $errores[] = 'Debes agregar al menos un servicio al combo.';
if (!in_array($incluyeBebida, [0, 1], true)) $incluyeBebida = 0;
if (!in_array($activo, [0, 1], true)) $activo = 1;

if (!empty($errores)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errores)]);
    exit;
}

try {
    $conn = conexionBD();

    // Verificar que el combo existe
    $check = $conn->prepare("SELECT idCombo FROM combos WHERE idCombo = :id LIMIT 1");
    $check->execute([':id' => $idCombo]);
    if (!$check->fetch()) {
        echo json_encode(['success' => false, 'message' => 'El combo no existe.']);
        exit;
    }

    $conn->beginTransaction();

    // 1. Actualizar el combo
    $stmt = $conn->prepare("UPDATE combos
                            SET nombre = :n, descripcion = :d, precioCombo = :p,
                                incluyeBebida = :b, activo = :a
                            WHERE idCombo = :id");
    $stmt->execute([
        ':n'  => $nombre,
        ':d'  => $descripcion ?: null,
        ':p'  => $precioCombo,
        ':b'  => $incluyeBebida,
        ':a'  => $activo,
        ':id' => $idCombo,
    ]);

    // 2. Eliminar los servicios actuales
    $del = $conn->prepare("DELETE FROM combo_detalle WHERE idCombo = :id");
    $del->execute([':id' => $idCombo]);

    // 3. Insertar los nuevos servicios
    $stmtS = $conn->prepare("INSERT INTO combo_detalle (idCombo, idServicio) VALUES (:idCombo, :idServicio)");
    foreach ($servicios as $idServicio) {
        $idServicio = (int) $idServicio;
        if ($idServicio <= 0) continue;
        $stmtS->execute([':idCombo' => $idCombo, ':idServicio' => $idServicio]);
    }

    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Combo actualizado correctamente.']);

} catch (PDOException $e) {
    if (isset($conn) && $conn->inTransaction()) $conn->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error al actualizar en la base de datos.']);
}