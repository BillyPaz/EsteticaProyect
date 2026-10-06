<?php
/**
 * modulos/mantenimiento/productos/php/movimiento_stock.php
 * Registra un movimiento de stock sobre una presentación-producto.
 *
 * Tipos soportados:
 *   - entrada : reabastecimiento. Suma stock. Lleva costo y vencimiento.
 *   - ajuste  : merma / corrección. Cantidad puede ser positiva o negativa.
 *
 * Usa transacción para no desincronizar stock vs movimiento.
 */

if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../../dashboard/includes/auth.php';
require_once __DIR__ . '/../../../../dashboard/includes/permisos.php';
require_once __DIR__ . '/../../../../config/conexion.php';

if (!tienePermiso('productos', 'editar')) {
    echo json_encode(['success' => false, 'message' => 'No tienes permiso para modificar el stock.']);
    exit;
}

// =====================================================
// 1. LEER POST
// =====================================================
$idPresentacionProd = (int)    ($_POST['id_presentacion_prod'] ?? 0);
$tipoMovimiento     = trim    ($_POST['tipoMovimiento'] ?? '');
$cantidad           = (int)    ($_POST['cantidad'] ?? 0);
$costoUnitario      = (float)  ($_POST['costoUnitario'] ?? 0);
$fechaVencimiento   = trim    ($_POST['fechaVencimiento'] ?? '');
$motivoSelect       = trim    ($_POST['motivoSelect'] ?? '');
$motivoOtro         = trim    ($_POST['motivoOtro'] ?? '');

// =====================================================
// 2. VALIDACIONES
// =====================================================
$errores = [];

if ($idPresentacionProd <= 0) {
    $errores[] = 'Producto no válido.';
}

if (!in_array($tipoMovimiento, ['entrada', 'ajuste'], true)) {
    $errores[] = 'Tipo de movimiento no válido.';
}

// Cantidad según tipo
if ($tipoMovimiento === 'entrada') {
    if ($cantidad <= 0) {
        $errores[] = 'La cantidad en una entrada debe ser mayor a 0.';
    }
} elseif ($tipoMovimiento === 'ajuste') {
    if ($cantidad === 0) {
        $errores[] = 'La cantidad del ajuste no puede ser 0.';
    }
}

// Motivo
if ($motivoSelect === '') {
    $errores[] = 'Debes seleccionar un motivo.';
} elseif ($motivoSelect === 'otro' && $motivoOtro === '') {
    $errores[] = 'Debes especificar el motivo.';
}

// Costo y vencimiento solo aplican a entradas
if ($tipoMovimiento === 'entrada') {
    if ($costoUnitario < 0) {
        $errores[] = 'El costo unitario no puede ser negativo.';
    }
} else {
    $costoUnitario    = 0;
    $fechaVencimiento = '';
}

if (!empty($errores)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errores)]);
    exit;
}

// Resolver motivo final
$motivoFinal = ($motivoSelect === 'otro') ? $motivoOtro : $motivoSelect;

try {
    $conn = conexionBD();

    // =====================================================
    // 3. VERIFICAR QUE EL PRODUCTO EXISTA Y ESTÉ ACTIVO
    // =====================================================
    $stmt = $conn->prepare("
        SELECT stock, activo FROM presentacionprod
        WHERE id_presentacion_prod = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $idPresentacionProd]);
    $fila = $stmt->fetch();

    if (!$fila) {
        echo json_encode(['success' => false, 'message' => 'El producto no existe.']);
        exit;
    }

    if ((int) $fila['activo'] === 0) {
        echo json_encode(['success' => false, 'message' => 'No puedes modificar el stock de un producto inactivo.']);
        exit;
    }

    $stockActual = (int) $fila['stock'];

    // =====================================================
    // 4. VALIDAR STOCK SUFICIENTE EN AJUSTE NEGATIVO
    // =====================================================
    if ($tipoMovimiento === 'ajuste' && $cantidad < 0) {
        if ($stockActual + $cantidad < 0) {
            echo json_encode([
                'success' => false,
                'message' => "No puedes restar $cantidad unidades. Solo hay $stockActual en stock."
            ]);
            exit;
        }
    }

    // =====================================================
    // 5. TRANSACCIÓN
    // =====================================================
    $conn->beginTransaction();

    // 5.1 Insertar el movimiento
    $stmt = $conn->prepare("
        INSERT INTO movimientos_inventario
            (id_presentacion_prod, id_usuario, tipo, cantidad, costoUnitario,
             fechaVencimiento, motivo, referenciaTipo)
        VALUES
            (:pp, :u, :tipo, :cant, :costo, :fv, :motivo, :ref)
    ");
    $stmt->execute([
        ':pp'     => $idPresentacionProd,
        ':u'      => $idUsuario,
        ':tipo'   => $tipoMovimiento,
        ':cant'   => $cantidad,
        ':costo'  => ($tipoMovimiento === 'entrada' ? $costoUnitario : null),
        ':fv'     => ($tipoMovimiento === 'entrada' && $fechaVencimiento !== '' ? $fechaVencimiento : null),
        ':motivo' => $motivoFinal,
        ':ref'    => ($tipoMovimiento === 'entrada' ? 'reabastecimiento' : 'ajuste_manual'),
    ]);

    // 5.2 Actualizar stock
    $stmt = $conn->prepare("
        UPDATE presentacionprod
        SET stock = stock + :cant
        WHERE id_presentacion_prod = :id
    ");
    $stmt->execute([
        ':cant' => $cantidad,
        ':id'   => $idPresentacionProd,
    ]);

    // 5.3 Si es entrada con costo, actualizar precioCompra con el último costo real
    if ($tipoMovimiento === 'entrada' && $costoUnitario > 0) {
        $stmt = $conn->prepare("
            UPDATE presentacionprod
            SET precioCompra = :costo
            WHERE id_presentacion_prod = :id
        ");
        $stmt->execute([
            ':costo' => $costoUnitario,
            ':id'    => $idPresentacionProd,
        ]);
    }

    // 5.4 COMMIT
    $conn->commit();

    // 5.5 Devolver el stock nuevo (para actualizar en vivo sin recargar)
    $stmt = $conn->prepare("
        SELECT stock FROM presentacionprod
        WHERE id_presentacion_prod = :id
    ");
    $stmt->execute([':id' => $idPresentacionProd]);
    $stockNuevo = (int) $stmt->fetchColumn();

    echo json_encode([
        'success'    => true,
        'message'    => 'Movimiento registrado correctamente.',
        'stockNuevo' => $stockNuevo,
    ]);

} catch (PDOException $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => 'Error al registrar el movimiento.'
    ]);
}