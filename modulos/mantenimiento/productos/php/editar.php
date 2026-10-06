<?php
/**
 * modulos/mantenimiento/productos/php/editar.php
 * Edita los datos de un producto-presentación existente.
 * NO toca stock (eso se maneja desde agregar_stock.php / ajustar.php).
 */

if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../../dashboard/includes/auth.php';
require_once __DIR__ . '/../../../../dashboard/includes/permisos.php';
require_once __DIR__ . '/../../../../config/conexion.php';

if (!tienePermiso('productos', 'editar')) {
    echo json_encode(['success' => false, 'message' => 'No tienes permiso para editar productos.']);
    exit;
}

// =====================================================
// 1. LEER POST
// =====================================================
$idPresentacionProd = (int) ($_POST['id_presentacion_prod'] ?? 0);
$nombreProducto     = trim($_POST['nombreProducto'] ?? '');
$idCategoria        = (int) ($_POST['id_categoria'] ?? 0);
$observaciones      = trim($_POST['observaciones'] ?? '');
$precioCompra       = (float) ($_POST['precioCompra'] ?? 0);
$precioVenta        = (float) ($_POST['precioVenta'] ?? 0);
$stockMinimo        = (int) ($_POST['stockMinimo'] ?? 5);
$codigoBarra        = trim($_POST['codigoBarra'] ?? '');

// =====================================================
// 2. VALIDACIONES
// =====================================================
$errores = [];

if ($idPresentacionProd <= 0)    $errores[] = 'Producto no válido.';
if ($nombreProducto === '')      $errores[] = 'El nombre del producto es obligatorio.';
if (mb_strlen($nombreProducto) > 50) $errores[] = 'El nombre del producto no puede tener más de 50 caracteres.';
if ($precioCompra < 0)           $errores[] = 'El precio de compra no puede ser negativo.';
if ($precioVenta <= 0)           $errores[] = 'El precio de venta debe ser mayor a 0.';
if ($stockMinimo < 0)            $errores[] = 'El stock mínimo no puede ser negativo.';
if (mb_strlen($observaciones) > 100) $errores[] = 'Las observaciones no pueden superar 100 caracteres.';
if (mb_strlen($codigoBarra) > 50)    $errores[] = 'El código de barras no puede superar 50 caracteres.';

if (!empty($errores)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errores)]);
    exit;
}

try {
    $conn = conexionBD();

    // =====================================================
    // 3. VERIFICAR QUE EXISTA EL PRODUCTO-PRESENTACIÓN
    // =====================================================
    $stmt = $conn->prepare("
        SELECT pp.id_producto
        FROM presentacionprod pp
        WHERE pp.id_presentacion_prod = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $idPresentacionProd]);
    $fila = $stmt->fetch();

    if (!$fila) {
        echo json_encode(['success' => false, 'message' => 'El producto no existe.']);
        exit;
    }

    $idProducto = (int) $fila['id_producto'];

    // =====================================================
    // 4. VERIFICAR CÓDIGO DE BARRAS ÚNICO (si cambió)
    // =====================================================
    if ($codigoBarra !== '') {
        $checkCb = $conn->prepare("
            SELECT id_presentacion_prod FROM presentacionprod
            WHERE codigoBarra = :cb AND id_presentacion_prod <> :id
            LIMIT 1
        ");
        $checkCb->execute([':cb' => $codigoBarra, ':id' => $idPresentacionProd]);
        if ($checkCb->fetch()) {
            echo json_encode([
                'success' => false,
                'message' => 'Ya existe otro producto con ese código de barras.'
            ]);
            exit;
        }
    }

    // =====================================================
    // 5. TRANSACCIÓN (por seguridad)
    // =====================================================
    $conn->beginTransaction();

    // 5.1 Actualizar tabla productos
    $stmt = $conn->prepare("
        UPDATE productos
        SET nombreProducto = :n,
            id_categoria   = :c,
            observaciones  = :o
        WHERE id_producto = :id
    ");
    $stmt->execute([
        ':n'  => $nombreProducto,
        ':c'  => ($idCategoria > 0 ? $idCategoria : null),
        ':o'  => ($observaciones === '' ? null : $observaciones),
        ':id' => $idProducto,
    ]);

    // 5.2 Actualizar tabla presentacionprod
    $stmt = $conn->prepare("
        UPDATE presentacionprod
        SET precioCompra = :pc,
            precioVenta  = :pv,
            stockMinimo  = :sm,
            codigoBarra  = :cb
        WHERE id_presentacion_prod = :id
    ");
    $stmt->execute([
        ':pc' => $precioCompra,
        ':pv' => $precioVenta,
        ':sm' => $stockMinimo,
        ':cb' => ($codigoBarra === '' ? null : $codigoBarra),
        ':id' => $idPresentacionProd,
    ]);

    // 5.3 COMMIT
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Producto actualizado correctamente.'
    ]);

} catch (PDOException $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => 'Error al actualizar en la base de datos.'
    ]);
}