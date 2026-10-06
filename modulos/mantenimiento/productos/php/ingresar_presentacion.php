<?php
/**
 * modulos/mantenimiento/productos/php/ingresar_presentacion.php
 * Crea una presentación nueva desde el modal rápido.
 * Devuelve la presentación creada para autoseleccionarla en el select.
 */

if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../../dashboard/includes/auth.php';
require_once __DIR__ . '/../../../../dashboard/includes/permisos.php';
require_once __DIR__ . '/../../../../config/conexion.php';

if (!tienePermiso('productos', 'crear')) {
    echo json_encode(['success' => false, 'message' => 'No tienes permiso para crear presentaciones.']);
    exit;
}

// =====================================================
// 1. LEER POST
// =====================================================
$nombrePresentacion = trim($_POST['nombrePresentacion'] ?? '');

// =====================================================
// 2. VALIDAR
// =====================================================
if ($nombrePresentacion === '') {
    echo json_encode(['success' => false, 'message' => 'El nombre de la presentación es obligatorio.']);
    exit;
}

if (mb_strlen($nombrePresentacion) > 50) {
    echo json_encode(['success' => false, 'message' => 'El nombre no puede superar 50 caracteres.']);
    exit;
}

try {
    $conn = conexionBD();

    // =====================================================
    // 3. VERIFICAR DUPLICADO
    // =====================================================
    $check = $conn->prepare("
        SELECT id_presentacion FROM presentacion
        WHERE nombrePresentacion = :n
        LIMIT 1
    ");
    $check->execute([':n' => $nombrePresentacion]);
    if ($check->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'Ya existe una presentación con ese nombre.'
        ]);
        exit;
    }

    // =====================================================
    // 4. INSERTAR
    // =====================================================
    $stmt = $conn->prepare("
        INSERT INTO presentacion (nombrePresentacion)
        VALUES (:n)
    ");
    $stmt->execute([':n' => $nombrePresentacion]);
    $idPresentacion = (int) $conn->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'Presentación creada correctamente.',
        'presentacion' => [
            'id_presentacion'    => $idPresentacion,
            'nombrePresentacion' => $nombrePresentacion,
        ],
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al guardar en la base de datos.'
    ]);
}