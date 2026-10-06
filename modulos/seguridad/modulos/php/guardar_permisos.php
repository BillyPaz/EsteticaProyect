<?php
/**
 * modulos/seguridad/modulos/php/guardar_permisos.php
 * Guarda los permisos de un rol en la tabla accion_rol.
 * Recibe JSON: { id_usuario, id_rol, permisos: { codigoAccion: {ver, crear, editar, eliminar} } }
 * Devuelve JSON.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../../dashboard/includes/auth.php';
require_once __DIR__ . '/../../../../dashboard/includes/permisos.php';
require_once __DIR__ . '/../../../../config/conexion.php';

// 1. Validar permiso
if (!tienePermiso('modulos', 'editar')) {
    echo json_encode([
        'success' => false,
        'message' => 'No tienes permiso para asignar módulos.'
    ]);
    exit;
}

// 2. Leer JSON del body
$raw = file_get_contents('php://input');
$input = json_decode($raw, true);

if (!$input || !is_array($input)) {
    echo json_encode([
        'success' => false,
        'message' => 'Datos inválidos.'
    ]);
    exit;
}

$idUsuario = (int) ($input['id_usuario'] ?? 0);
$idRol     = (int) ($input['id_rol']     ?? 0);
$permisos  = $input['permisos']          ?? [];

// 3. Validaciones
if ($idUsuario <= 0 || $idRol <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Usuario o rol inválido.'
    ]);
    exit;
}

if (!is_array($permisos)) {
    echo json_encode([
        'success' => false,
        'message' => 'El formato de permisos es inválido.'
    ]);
    exit;
}

try {
    $conn = conexionBD();

    // 4. Verificar que el usuario exista y tenga ese rol
    $stmtU = $conn->prepare("SELECT ru.id_rol_usuario
                             FROM rol_usuario ru
                             WHERE ru.id_usuario = :id AND ru.id_rol = :idRol AND ru.estado = 1
                             LIMIT 1");
    $stmtU->execute([':id' => $idUsuario, ':idRol' => $idRol]);

    if (!$stmtU->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'El usuario no tiene asignado ese rol.'
        ]);
        exit;
    }

    // 5. Obtener un mapa codigoAccion => id_accion (para traducir el payload)
    $stmtA = $conn->query("SELECT id_accion, codigo FROM acciones WHERE estado = 1");
    $mapaAcciones = [];
    while ($row = $stmtA->fetch()) {
        $mapaAcciones[$row['codigo']] = (int) $row['id_accion'];
    }

    // 6. Eliminar los permisos actuales del rol
    $del = $conn->prepare("DELETE FROM accion_rol WHERE id_rol = :idRol");
    $del->execute([':idRol' => $idRol]);

    // 7. Insertar los nuevos permisos
    $insert = $conn->prepare("INSERT INTO accion_rol
                                (id_accion, id_rol, puedeCrear, puedeModificar, puedeConsultar, puedeEliminar, estado)
                              VALUES
                                (:idAccion, :idRol, :crear, :modificar, :consultar, :eliminar, 1)");

    $totalInsertados = 0;

    foreach ($permisos as $codigo => $p) {
        // Saltar si la acción no existe en la tabla
        if (!isset($mapaAcciones[$codigo])) continue;

        $ver      = !empty($p['ver'])      ? 1 : 0;
        $crear    = !empty($p['crear'])    ? 1 : 0;
        $editar   = !empty($p['editar'])   ? 1 : 0;
        $eliminar = !empty($p['eliminar']) ? 1 : 0;

        // Si no tiene ningún permiso, no lo insertamos
        if (!$ver && !$crear && !$editar && !$eliminar) continue;

        $insert->execute([
            ':idAccion'  => $mapaAcciones[$codigo],
            ':idRol'     => $idRol,
            ':crear'     => $crear,
            ':modificar' => $editar,
            ':consultar' => $ver,
            ':eliminar'  => $eliminar,
        ]);

        $totalInsertados++;
    }

    echo json_encode([
        'success' => true,
        'message' => "Permisos guardados correctamente ($totalInsertados módulos).",
        'total'   => $totalInsertados
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al guardar en la base de datos: ' . $e->getMessage()
    ]);
}