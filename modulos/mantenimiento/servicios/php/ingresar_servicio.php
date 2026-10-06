<?php
/**
 * modulos/mantenimiento/servicios/php/ingresar_servicio.php
 * Crea un servicio nuevo.
 */

if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../../dashboard/includes/auth.php';
require_once __DIR__ . '/../../../../dashboard/includes/permisos.php';
require_once __DIR__ . '/../../../../config/conexion.php';

if (!tienePermiso('servicios', 'crear')) {
    echo json_encode(['success' => false, 'message' => 'No tienes permiso para crear servicios.']);
    exit;
}

$nombreServicio = trim($_POST['nombreServicio'] ?? '');
$costoServicio  = (float) ($_POST['costoServicio'] ?? 0);
$duracion       = (int) ($_POST['duracion'] ?? 0);
$activo         = (int) ($_POST['activo'] ?? 1);

$errores = [];
if ($nombreServicio === '') $errores[] = 'El nombre es obligatorio.';
if (mb_strlen($nombreServicio) > 50) $errores[] = 'El nombre no puede tener más de 50 caracteres.';
if ($costoServicio <= 0) $errores[] = 'El costo debe ser mayor a 0.';
if ($duracion <= 0) $errores[] = 'La duración debe ser mayor a 0.';
if (!in_array($activo, [0, 1], true)) $activo = 1;

if (!empty($errores)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errores)]);
    exit;
}

try {
    $conn = conexionBD();

    // Verificar que no exista uno con el mismo nombre
    $check = $conn->prepare("SELECT id_servicio FROM servicios WHERE nombreServicio = :n LIMIT 1");
    $check->execute([':n' => $nombreServicio]);
    if ($check->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Ya existe un servicio con ese nombre.']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO servicios (nombreServicio, costoServicio, duracion, activo)
                            VALUES (:n, :c, :d, :a)");
    $stmt->execute([
        ':n' => $nombreServicio,
        ':c' => $costoServicio,
        ':d' => $duracion,
        ':a' => $activo,
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Servicio creado correctamente.',
        'id_servicio' => (int) $conn->lastInsertId()
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error al guardar en la base de datos.']);
}