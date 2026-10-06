<?php
/**
 * modulos/mantenimiento/productos/php/ingresar_categoria.php
 * Crea una categoría nueva desde el modal rápido.
 * Devuelve la categoría creada para autoseleccionarla en el select.
 */

if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../../dashboard/includes/auth.php';
require_once __DIR__ . '/../../../../dashboard/includes/permisos.php';
require_once __DIR__ . '/../../../../config/conexion.php';

if (!tienePermiso('productos', 'crear')) {
    echo json_encode(['success' => false, 'message' => 'No tienes permiso para crear categorías.']);
    exit;
}

// =====================================================
// 1. LEER POST
// =====================================================
$nombreCategoria = trim($_POST['nombreCategoria'] ?? '');

// =====================================================
// 2. VALIDAR
// =====================================================
if ($nombreCategoria === '') {
    echo json_encode(['success' => false, 'message' => 'El nombre de la categoría es obligatorio.']);
    exit;
}

if (mb_strlen($nombreCategoria) > 50) {
    echo json_encode(['success' => false, 'message' => 'El nombre no puede superar 50 caracteres.']);
    exit;
}

try {
    $conn = conexionBD();

    // =====================================================
    // 3. VERIFICAR DUPLICADO
    // =====================================================
    $check = $conn->prepare("
        SELECT id_categoria FROM categorias
        WHERE nombreCategoria = :n
        LIMIT 1
    ");
    $check->execute([':n' => $nombreCategoria]);
    if ($check->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'Ya existe una categoría con ese nombre.'
        ]);
        exit;
    }

    // =====================================================
    // 4. INSERTAR
    // =====================================================
    $stmt = $conn->prepare("
        INSERT INTO categorias (nombreCategoria, activo)
        VALUES (:n, 1)
    ");
    $stmt->execute([':n' => $nombreCategoria]);
    $idCategoria = (int) $conn->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'Categoría creada correctamente.',
        'categoria' => [
            'id_categoria'    => $idCategoria,
            'nombreCategoria' => $nombreCategoria,
        ],
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al guardar en la base de datos.'
    ]);
}