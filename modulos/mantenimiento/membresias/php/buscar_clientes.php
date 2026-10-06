<?php
/**
 * modulos/mantenimiento/membresias/php/buscar_clientes.php
 * Busca clientes por nombre, apellido, correo o teléfono.
 * Solo devuelve clientes activos.
 */

if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../../dashboard/includes/auth.php';
require_once __DIR__ . '/../../../../dashboard/includes/permisos.php';
require_once __DIR__ . '/../../../../config/conexion.php';

if (!tienePermiso('membresias', 'ver')) {
    echo json_encode(['success' => false, 'message' => 'Sin permiso.']);
    exit;
}

$q = trim($_GET['q'] ?? '');

if (mb_strlen($q) < 2) {
    echo json_encode(['success' => true, 'clientes' => []]);
    exit;
}

try {
    $conn = conexionBD();

    $stmt = $conn->prepare("SELECT id_cliente, nombreCliente, apellidoCliente, telefono, correo
                        FROM clientes
                        WHERE estado = 1
                          AND (
                              nombreCliente   LIKE :q1
                              OR apellidoCliente LIKE :q2
                              OR correo          LIKE :q3
                              OR telefono        LIKE :q4
                          )
                        ORDER BY nombreCliente ASC
                        LIMIT 15");
$like = '%' . $q . '%';
$stmt->execute([
    ':q1' => $like,
    ':q2' => $like,
    ':q3' => $like,
    ':q4' => $like,
]);
    $clientes = $stmt->fetchAll();

    echo json_encode(['success' => true, 'clientes' => $clientes]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}