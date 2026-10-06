<?php
/**
 * modulos/mantenimiento/servicios/php/ingresar_combo.php
 * Crea un combo nuevo con sus servicios.
 * Recibe JSON: { nombre, descripcion, precioCombo, incluyeBebida, activo, servicios: [id, id, ...] }
 */

if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../../dashboard/includes/auth.php';
require_once __DIR__ . '/../../../../dashboard/includes/permisos.php';
require_once __DIR__ . '/../../../../config/conexion.php';

if (!tienePermiso('servicios', 'crear')) {
    echo json_encode(['success' => false, 'message' => 'No tienes permiso para crear combos.']);
    exit;
}

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);

if (!$input || !is_array($input)) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
    exit;
}

$nombre        = trim($input['nombre'] ?? '');
$descripcion   = trim($input['descripcion'] ?? '');
$precioCombo   = (float) ($input['precioCombo'] ?? 0);
$incluyeBebida = (int) ($input['incluyeBebida'] ?? 0);
$activo        = (int) ($input['activo'] ?? 1);
$servicios     = $input['servicios'] ?? [];

$errores = [];
if ($nombre === '') $errores[] = 'El nombre del combo es obligatorio.';
if (mb_strlen($nombre) > 100) $errores[] = 'El nombre no puede tener más de 100 caracteres.';
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
    $conn->beginTransaction();

    // 1. Insertar el combo
    $stmt = $conn->prepare("INSERT INTO combos (nombre, descripcion, precioCombo, incluyeBebida, activo)
                            VALUES (:n, :d, :p, :b, :a)");
    $stmt->execute([
        ':n' => $nombre,
        ':d' => $descripcion ?: null,
        ':p' => $precioCombo,
        ':b' => $incluyeBebida,
        ':a' => $activo,
    ]);

    $idCombo = (int) $conn->lastInsertId();

    // 2. Insertar los servicios del combo
    $stmtS = $conn->prepare("INSERT INTO combo_detalle (idCombo, idServicio) VALUES (:idCombo, :idServicio)");

    foreach ($servicios as $idServicio) {
        $idServicio = (int) $idServicio;
        if ($idServicio <= 0) continue;
        $stmtS->execute([':idCombo' => $idCombo, ':idServicio' => $idServicio]);
    }

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Combo creado correctamente.',
        'idCombo' => $idCombo
    ]);

} catch (PDOException $e) {
    if (isset($conn) && $conn->inTransaction()) $conn->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error al guardar en la base de datos.']);
}