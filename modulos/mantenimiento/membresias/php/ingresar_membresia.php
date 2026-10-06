<?php
/**
 * modulos/mantenimiento/membresias/php/ingresar_membresia.php
 * Crea una membresía nueva.
 */

if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../../dashboard/includes/auth.php';
require_once __DIR__ . '/../../../../dashboard/includes/permisos.php';
require_once __DIR__ . '/../../../../config/conexion.php';

if (!tienePermiso('membresias', 'crear')) {
    echo json_encode(['success' => false, 'message' => 'No tienes permiso para crear membresías.']);
    exit;
}

$codigo              = trim($_POST['codigo'] ?? '');
$nombre              = trim($_POST['nombre'] ?? '');
$porcentajeDescuento = (float) ($_POST['porcentajeDescuento'] ?? 0);
$estado              = (int) ($_POST['estado'] ?? 1);

$errores = [];
if ($codigo === '') $errores[] = 'El código es obligatorio.';
if (mb_strlen($codigo) > 20) $errores[] = 'El código no puede tener más de 20 caracteres.';
if ($nombre === '') $errores[] = 'El nombre es obligatorio.';
if (mb_strlen($nombre) > 50) $errores[] = 'El nombre no puede tener más de 50 caracteres.';
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

    // Verificar que no exista otro con el mismo código
    $check = $conn->prepare("SELECT id_membresia FROM membresias WHERE codigo = :c LIMIT 1");
    $check->execute([':c' => $codigo]);
    if ($check->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Ya existe una membresía con ese código.']);
        exit;
    }

    // Verificar que no exista otro con el mismo nombre
    $check2 = $conn->prepare("SELECT id_membresia FROM membresias WHERE nombre = :n LIMIT 1");
    $check2->execute([':n' => $nombre]);
    if ($check2->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Ya existe una membresía con ese nombre.']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO membresias (codigo, nombre, porcentajeDescuento, estado)
                            VALUES (:c, :n, :p, :e)");
    $stmt->execute([
        ':c' => $codigo,
        ':n' => $nombre,
        ':p' => $porcentajeDescuento,
        ':e' => $estado,
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Membresía creada correctamente.',
        'id_membresia' => (int) $conn->lastInsertId()
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error al guardar en la base de datos.']);
}