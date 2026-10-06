<?php
/**
 * modulos/mantenimiento/productos/php/obtener.php
 * Devuelve los datos de un producto-presentación para precargar el modal de editar.
 */

if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../../dashboard/includes/auth.php';
require_once __DIR__ . '/../../../../dashboard/includes/permisos.php';
require_once __DIR__ . '/../../../../config/conexion.php';

if (!tienePermiso('productos', 'ver')) {
    echo json_encode(['success' => false, 'message' => 'No tienes permiso para ver productos.']);
    exit;
}

// =====================================================
// 1. LEER GET
// =====================================================
$idPresentacionProd = (int) ($_GET['id'] ?? 0);

if ($idPresentacionProd <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID no válido.']);
    exit;
}

try {
    $conn = conexionBD();

    $stmt = $conn->prepare("
        SELECT
            pp.id_presentacion_prod,
            pp.codigoBarra,
            pp.precioCompra,
            pp.precioVenta,
            pp.stock,
            pp.stockMinimo,
            pp.activo,
            p.id_producto,
            p.nombreProducto,
            p.id_categoria,
            p.observaciones,
            pr.nombrePresentacion
        FROM presentacionprod pp
        INNER JOIN productos p     ON p.id_producto      = pp.id_producto
        INNER JOIN presentacion pr ON pr.id_presentacion = pp.id_presentacion
        WHERE pp.id_presentacion_prod = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $idPresentacionProd]);
    $fila = $stmt->fetch();

    if (!$fila) {
        echo json_encode(['success' => false, 'message' => 'El producto no existe.']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'producto' => [
            'id_presentacion_prod' => (int) $fila['id_presentacion_prod'],
            'id_producto'          => (int) $fila['id_producto'],
            'nombreProducto'       => $fila['nombreProducto'],
            'id_categoria'         => (int) ($fila['id_categoria'] ?? 0),
            'observaciones'        => $fila['observaciones'] ?? '',
            'nombrePresentacion'   => $fila['nombrePresentacion'],
            'codigoBarra'          => $fila['codigoBarra'] ?? '',
            'precioCompra'         => (float) $fila['precioCompra'],
            'precioVenta'          => (float) $fila['precioVenta'],
            'stock'                => (int) $fila['stock'],
            'stockMinimo'          => (int) $fila['stockMinimo'],
            'activo'               => (int) $fila['activo'],
        ],
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener el producto.'
    ]);
}