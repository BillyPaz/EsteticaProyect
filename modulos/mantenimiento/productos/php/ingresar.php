<?php
/**
 * modulos/mantenimiento/productos/php/ingresar.php
 * Crea un producto nuevo con su presentación inicial y stock inicial.
 * Usa transacción para no dejar datos huérfanos.
 */

if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../../dashboard/includes/auth.php';
require_once __DIR__ . '/../../../../dashboard/includes/permisos.php';
require_once __DIR__ . '/../../../../config/conexion.php';

if (!tienePermiso('productos', 'crear')) {
    echo json_encode(['success' => false, 'message' => 'No tienes permiso para crear productos.']);
    exit;
}

// =====================================================
// 1. LEER POST
// =====================================================
$modo            = trim($_POST['modo'] ?? 'nuevo'); // 'nuevo' | 'existente'
$idProducto      = (int) ($_POST['id_producto'] ?? 0);
$nombreProducto  = trim($_POST['nombreProducto'] ?? '');
$idCategoria     = (int) ($_POST['id_categoria'] ?? 0);
$observaciones   = trim($_POST['observaciones'] ?? '');

$idPresentacion  = (int) ($_POST['id_presentacion'] ?? 0);
$codigoBarra     = trim($_POST['codigoBarra'] ?? '');
$precioCompra    = (float) ($_POST['precioCompra'] ?? 0);
$precioVenta     = (float) ($_POST['precioVenta'] ?? 0);
$stockInicial    = (int) ($_POST['stockInicial'] ?? 0);
$stockMinimo     = (int) ($_POST['stockMinimo'] ?? 2);
$fechaVencimiento = trim($_POST['fechaVencimiento'] ?? '');

// =====================================================
// 2. VALIDACIONES
// =====================================================
$errores = [];

if ($modo === 'nuevo') {
    if ($nombreProducto === '') {
        $errores[] = 'El nombre del producto es obligatorio.';
    } elseif (mb_strlen($nombreProducto) > 50) {
        $errores[] = 'El nombre del producto no puede tener más de 50 caracteres.';
    }
    if ($idCategoria <= 0) {
        $errores[] = 'Debes seleccionar una categoría.';
    }
} elseif ($modo === 'existente') {
    if ($idProducto <= 0) {
        $errores[] = 'Debes seleccionar un producto existente.';
    }
} else {
    $errores[] = 'Modo de creación no válido.';
}

if ($idPresentacion <= 0)    $errores[] = 'Debes seleccionar una presentación.';
if ($precioCompra < 0)       $errores[] = 'El precio de compra no puede ser negativo.';
if ($precioVenta <= 0)       $errores[] = 'El precio de venta debe ser mayor a 0.';
if ($stockInicial < 0)       $errores[] = 'El stock inicial no puede ser negativo.';
if ($stockMinimo < 0)        $errores[] = 'El stock mínimo no puede ser negativo.';
if (mb_strlen($observaciones) > 100) $errores[] = 'Las observaciones no pueden superar 100 caracteres.';
if (mb_strlen($codigoBarra) > 50)    $errores[] = 'El código de barras no puede superar 50 caracteres.';

if (!empty($errores)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errores)]);
    exit;
}

try {
    $conn = conexionBD();

    // =====================================================
    // 3. VERIFICAR DUPLICADOS
    // =====================================================

    // 3.1 Si el modo es "nuevo", verificar que no exista producto con ese nombre
    if ($modo === 'nuevo') {
        $check = $conn->prepare("
            SELECT id_producto FROM productos
            WHERE nombreProducto = :n AND activo = 1
            LIMIT 1
        ");
        $check->execute([':n' => $nombreProducto]);
        if ($check->fetch()) {
            echo json_encode([
                'success' => false,
                'message' => 'Ya existe un producto con ese nombre. Selecciona "Producto existente" si quieres agregarle una nueva presentación.'
            ]);
            exit;
        }
    } else {
        // 3.2 Si el modo es "existente", verificar que exista
        $check = $conn->prepare("
            SELECT id_producto FROM productos
            WHERE id_producto = :id AND activo = 1
            LIMIT 1
        ");
        $check->execute([':id' => $idProducto]);
        if (!$check->fetch()) {
            echo json_encode([
                'success' => false,
                'message' => 'El producto seleccionado no existe o está inactivo.'
            ]);
            exit;
        }
    }

    // 3.3 Verificar que no exista ya esa presentación para ese producto
    $productoFinalId = ($modo === 'nuevo') ? 0 : $idProducto;
    if ($modo === 'existente') {
        $checkDup = $conn->prepare("
            SELECT id_presentacion_prod FROM presentacionprod
            WHERE id_producto = :pid AND id_presentacion = :pre AND activo = 1
            LIMIT 1
        ");
        $checkDup->execute([':pid' => $productoFinalId, ':pre' => $idPresentacion]);
        if ($checkDup->fetch()) {
            echo json_encode([
                'success' => false,
                'message' => 'Ya existe una presentación igual para este producto. Usa el botón "Stock" en la tabla para agregar más unidades.'
            ]);
            exit;
        }
    }

    // 3.4 Verificar código de barras único (si se proporcionó)
    if ($codigoBarra !== '') {
        $checkCb = $conn->prepare("
            SELECT id_presentacion_prod FROM presentacionprod
            WHERE codigoBarra = :cb
            LIMIT 1
        ");
        $checkCb->execute([':cb' => $codigoBarra]);
        if ($checkCb->fetch()) {
            echo json_encode([
                'success' => false,
                'message' => 'Ya existe otro producto con ese código de barras.'
            ]);
            exit;
        }
    }

    // =====================================================
    // 4. TRANSACCIÓN
    // =====================================================
    $conn->beginTransaction();

    // 4.1 Crear producto si es nuevo
    if ($modo === 'nuevo') {
        $stmt = $conn->prepare("
            INSERT INTO productos (id_categoria, nombreProducto, observaciones, activo)
            VALUES (:cat, :nom, :obs, 1)
        ");
        $stmt->execute([
            ':cat' => $idCategoria,
            ':nom' => $nombreProducto,
            ':obs' => ($observaciones === '' ? null : $observaciones),
        ]);
        $productoFinalId = (int) $conn->lastInsertId();
    }

    // 4.2 Crear la presentación-producto
    $stmt = $conn->prepare("
        INSERT INTO presentacionprod
            (id_producto, id_presentacion, codigoBarra, precioCompra, precioVenta,
             stock, stockMinimo, activo)
        VALUES
            (:pid, :pre, :cb, :pc, :pv, :st, :sm, 1)
    ");
    $stmt->execute([
        ':pid' => $productoFinalId,
        ':pre' => $idPresentacion,
        ':cb'  => ($codigoBarra === '' ? null : $codigoBarra),
        ':pc'  => $precioCompra,
        ':pv'  => $precioVenta,
        ':st'  => $stockInicial,
        ':sm'  => $stockMinimo,
    ]);
    $idPresentacionProd = (int) $conn->lastInsertId();

    // 4.3 Crear el movimiento de entrada (stock inicial)
    if ($stockInicial > 0) {
        $stmt = $conn->prepare("
            INSERT INTO movimientos_inventario
                (id_presentacion_prod, id_usuario, tipo, cantidad, costoUnitario,
                 fechaVencimiento, motivo, referenciaTipo)
            VALUES
                (:pp, :u, 'entrada', :cant, :costo, :fv, 'Stock inicial', 'producto_inicial')
        ");
        $stmt->execute([
            ':pp'    => $idPresentacionProd,
            ':u'     => $idUsuario,
            ':cant'  => $stockInicial,
            ':costo' => $precioCompra,
            ':fv'    => ($fechaVencimiento === '' ? null : $fechaVencimiento),
        ]);
    }

    // 4.4 COMMIT
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Producto creado correctamente.',
        'id_presentacion_prod' => $idPresentacionProd,
    ]);

} catch (PDOException $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => 'Error al guardar en la base de datos.'
    ]);
}