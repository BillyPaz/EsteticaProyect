<?php
/**
 * modulos/mantenimiento/productos/php/cambiar_estado.php
 * Activa o desactiva una presentación-producto (soft delete / restore).
 * No elimina registros. El historial queda intacto.
 *
 * Recibe:
 *   - id_presentacion_prod : int
 *   - accion               : 'activar' | 'desactivar'
 */

if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../../dashboard/includes/auth.php';
require_once __DIR__ . '/../../../../dashboard/includes/permisos.php';
require_once __DIR__ . '/../../../../config/conexion.php';

if (!tienePermiso('productos', 'eliminar')) {
    echo json_encode(['success' => false, 'message' => 'No tienes permiso para cambiar el estado de productos.']);
    exit;
}

// =====================================================
// 1. LEER POST
// =====================================================
$idPresentacionProd = (int)  ($_POST['id_presentacion_prod'] ?? 0);
$accion             = trim ($_POST['accion'] ?? '');

// =====================================================
// 2. VALIDAR
// =====================================================
if ($idPresentacionProd <= 0) {
    echo json_encode(['success' => false, 'message' => 'Producto no válido.']);
    exit;
}

if (!in_array($accion, ['activar', 'desactivar'], true)) {
    echo json_encode(['success' => false, 'message' => 'Acción no válida.']);
    exit;
}

try {
    $conn = conexionBD();

    // =====================================================
    // 3. VERIFICAR QUE EXISTA
    // =====================================================
    $stmt = $conn->prepare("
        SELECT activo FROM presentacionprod
        WHERE id_presentacion_prod = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $idPresentacionProd]);
    $fila = $stmt->fetch();

    if (!$fila) {
        echo json_encode(['success' => false, 'message' => 'El producto no existe.']);
        exit;
    }

    $activoActual = (int) $fila['activo'];
    $nuevoEstado  = ($accion === 'activar') ? 1 : 0;

    // =====================================================
    // 4. VALIDAR QUE NO SE HAGA LO MISMO DOS VECES
    // =====================================================
    if ($activoActual === $nuevoEstado) {
        $mensaje = ($accion === 'activar')
            ? 'El producto ya estaba activo.'
            : 'El producto ya estaba desactivado.';
        echo json_encode(['success' => false, 'message' => $mensaje]);
        exit;
    }

    // =====================================================
    // 5. ACTUALIZAR ESTADO
    // =====================================================
    $stmt = $conn->prepare("
        UPDATE presentacionprod
        SET activo = :estado
        WHERE id_presentacion_prod = :id
    ");
    $stmt->execute([
        ':estado' => $nuevoEstado,
        ':id'     => $idPresentacionProd,
    ]);

    $mensaje = ($accion === 'activar')
        ? 'Producto activado correctamente.'
        : 'Producto desactivado correctamente.';

    echo json_encode([
        'success' => true,
        'message' => $mensaje,
        'nuevoEstado' => $nuevoEstado,
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al cambiar el estado en la base de datos.'
    ]);
}