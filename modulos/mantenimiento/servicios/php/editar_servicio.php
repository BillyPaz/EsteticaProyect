<?php
/**
 * modulos/mantenimiento/servicios/php/editar_servicio.php
 * Actualiza un servicio existente.
 */

if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../../dashboard/includes/auth.php';
require_once __DIR__ . '/../../../../dashboard/includes/permisos.php';
require_once __DIR__ . '/../../../../config/conexion.php';

if (!tienePermiso('servicios', 'editar')) {
    echo json_encode(['success' => false, 'message' => 'No tienes permiso para editar servicios.']);
    exit;
}

$idServicio     = (int) ($_POST['id_servicio'] ?? 0);
$nombreServicio = trim($_POST['nombreServicio'] ?? '');
$costoServicio  = (float) ($_POST['costoServicio'] ?? 0);
$duracion       = (int) ($_POST['duracion'] ?? 0);
$activo         = (int) ($_POST['activo'] ?? 1);

$errores = [];
if ($idServicio <= 0) $errores[] = 'ID de servicio inválido.';
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

    // Verificar que existe
    $check = $conn->prepare("SELECT id_servicio FROM servicios WHERE id_servicio = :id LIMIT 1");
    $check->execute([':id' => $idServicio]);
    if (!$check->fetch()) {
        echo json_encode(['success' => false, 'message' => 'El servicio no existe.']);
        exit;
    }

    // Verificar que no haya OTRO con el mismo nombre
    $check2 = $conn->prepare("SELECT id_servicio FROM servicios WHERE nombreServicio = :n AND id_servicio != :id LIMIT 1");
    $check2->execute([':n' => $nombreServicio, ':id' => $idServicio]);
    if ($check2->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Ya existe otro servicio con ese nombre.']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE servicios
                            SET nombreServicio = :n, costoServicio = :c, duracion = :d, activo = :a
                            WHERE id_servicio = :id");
    $stmt->execute([
        ':n'  => $nombreServicio,
        ':c'  => $costoServicio,
        ':d'  => $duracion,
        ':a'  => $activo,
        ':id' => $idServicio,
    ]);

    echo json_encode(['success' => true, 'message' => 'Servicio actualizado correctamente.']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar en la base de datos.']);
}