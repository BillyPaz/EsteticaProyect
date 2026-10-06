<?php
/**
 * modulos/empleados/ausencia/php/editar.php
 * Modifica una ausencia existente. Responde SIEMPRE en JSON.
 *
 * - Sin horas (NULL)  → día completo (o varios días).
 * - Con horas         → ausencia parcial, solo si fechaInicio = fechaFin.
 * - No cambia "registrado por" ni la fecha de creación.
 *
 * No incluye auth.php a propósito: auth.php redirige al login con un
 * header Location y aquí el navegador necesita un JSON, no una página.
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

// -----------------------------------------------------
// Respuesta JSON estándar (termina la ejecución)
// -----------------------------------------------------
function responder(int $codigo, bool $ok, string $mensaje, array $extra = []): void
{
    http_response_code($codigo);
    echo json_encode(
        array_merge(['success' => $ok, 'message' => $mensaje], $extra),
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

// -----------------------------------------------------
// 1. Solo POST y solo peticiones AJAX del propio sistema
// -----------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    responder(405, false, 'Método no permitido.');
}
if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'XMLHttpRequest') {
    responder(400, false, 'Petición no válida.');
}

// -----------------------------------------------------
// 2. Sesión
// -----------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['id_usuario']) || empty($_SESSION['permisos'])) {
    responder(401, false, 'Tu sesión expiró. Inicia sesión de nuevo.', ['sesionExpirada' => true]);
}

// -----------------------------------------------------
// 3. Permiso
// -----------------------------------------------------
require_once __DIR__ . '/../../../../dashboard/includes/permisos.php';
require_once __DIR__ . '/../../../../config/conexion.php';

if (!tienePermiso('emp-ausencia', 'editar')) {
    responder(403, false, 'No tienes permiso para modificar ausencias.');
}

// -----------------------------------------------------
// 4. Constantes y helpers
// -----------------------------------------------------
const ID_USUARIO_SISTEMA = 1;
const TIPOS = [
    'vacaciones' => 'Vacaciones',
    'enfermedad' => 'Enfermedad',
    'permiso'    => 'Permiso',
    'otro'       => 'Otro',
];
const PATRON_HORA = '/^([01]\d|2[0-3]):[0-5]\d$/';

function fechaValida(string $fecha): bool
{
    $d = DateTime::createFromFormat('!Y-m-d', $fecha);
    return $d !== false
        && $d->format('Y-m-d') === $fecha
        && $fecha >= '2000-01-01'
        && $fecha <= '2100-12-31';
}

function fechaDMA(string $fecha): string
{
    return (new DateTime($fecha))->format('d/m/Y');
}

// Texto para el mensaje de choque: "Vacaciones del 01/09/2026 al 07/09/2026"
function describirAusencia(array $r): string
{
    $tipo = TIPOS[$r['motivo']] ?? (string) $r['motivo'];

    if ($r['fechaInicio'] !== $r['fechaFin']) {
        return $tipo . ' del ' . fechaDMA($r['fechaInicio']) . ' al ' . fechaDMA($r['fechaFin']);
    }
    if ($r['horaInicio'] !== null && $r['horaFin'] !== null) {
        return $tipo . ' el ' . fechaDMA($r['fechaInicio'])
            . ' de ' . substr($r['horaInicio'], 0, 5) . ' a ' . substr($r['horaFin'], 0, 5);
    }
    return $tipo . ' el ' . fechaDMA($r['fechaInicio']) . ' (todo el día)';
}

// -----------------------------------------------------
// 5. Validación de datos
// -----------------------------------------------------
$idAusencia    = filter_var($_POST['id_ausencia'] ?? '', FILTER_VALIDATE_INT);
$idUsuario     = filter_var($_POST['id_usuario'] ?? '', FILTER_VALIDATE_INT);
$motivo        = (string) ($_POST['motivo'] ?? '');
$fechaInicio   = trim((string) ($_POST['fechaInicio'] ?? ''));
$fechaFin      = trim((string) ($_POST['fechaFin'] ?? ''));
$horaInicio    = trim((string) ($_POST['horaInicio'] ?? ''));
$horaFin       = trim((string) ($_POST['horaFin'] ?? ''));
$observaciones = trim((string) ($_POST['observaciones'] ?? ''));
$estado        = (string) ($_POST['estado'] ?? '');

if ($idAusencia === false || $idAusencia < 1) {
    responder(422, false, 'No se pudo identificar la ausencia que quieres editar.');
}
if ($idUsuario === false || $idUsuario < 1 || $idUsuario === ID_USUARIO_SISTEMA) {
    responder(422, false, 'Selecciona un empleado.');
}
if (!array_key_exists($motivo, TIPOS)) {
    responder(422, false, 'Selecciona un tipo de ausencia válido.');
}
if (!fechaValida($fechaInicio)) {
    responder(422, false, 'La fecha de inicio no es válida.');
}
if (!fechaValida($fechaFin)) {
    responder(422, false, 'La fecha de fin no es válida.');
}
if ($fechaFin < $fechaInicio) {
    responder(422, false, 'La fecha de fin no puede ser menor que la fecha de inicio.');
}

// Horas: las dos o ninguna. Sin horas = día completo.
$conHoras = ($horaInicio !== '' || $horaFin !== '');

if ($conHoras) {
    if ($horaInicio === '' || $horaFin === '') {
        responder(422, false, 'Indica la hora de inicio y la hora de fin, o deja ambas vacías si es todo el día.');
    }
    if (!preg_match(PATRON_HORA, $horaInicio) || !preg_match(PATRON_HORA, $horaFin)) {
        responder(422, false, 'El formato de las horas no es válido.');
    }
    if ($fechaInicio !== $fechaFin) {
        responder(422, false, 'El horario solo se puede indicar cuando la ausencia es de un solo día.');
    }
    // Formato HH:MM con ceros a la izquierda: la comparación de texto equivale a la de horas
    if ($horaFin <= $horaInicio) {
        responder(422, false, 'La hora de fin debe ser mayor que la hora de inicio.');
    }
    $horaInicioBD = $horaInicio . ':00';
    $horaFinBD    = $horaFin . ':00';
} else {
    $horaInicioBD = null;
    $horaFinBD    = null;
}

if (mb_strlen($observaciones, 'UTF-8') > 100) {
    responder(422, false, 'El motivo no puede tener más de 100 caracteres.');
}
$observaciones = ($observaciones === '') ? null : $observaciones;

if ($estado !== '0' && $estado !== '1') {
    responder(422, false, 'El estado no es válido.');
}
$estado = (int) $estado;

// -----------------------------------------------------
// 6. Actualización (en transacción)
// -----------------------------------------------------
$conn = conexionBD();

try {
    $conn->beginTransaction();

    // Se bloquea primero la fila del empleado destino (mismo orden que ingresar.php)
    $st = $conn->prepare(
        "SELECT estado FROM usuarios
         WHERE id_usuario = :id
         FOR UPDATE"
    );
    $st->execute([':id' => $idUsuario]);
    $empleado = $st->fetch();

    if (!$empleado) {
        $conn->rollBack();
        responder(422, false, 'El empleado seleccionado no existe.');
    }

    // Luego la ausencia que se está editando
    $st = $conn->prepare(
        "SELECT id_usuario FROM ausencia_usuario
         WHERE id_ausencia_usuario = :id
         FOR UPDATE"
    );
    $st->execute([':id' => $idAusencia]);
    $actual = $st->fetch();

    if (!$actual) {
        $conn->rollBack();
        responder(404, false, 'Esta ausencia ya no existe. Recarga el módulo para ver la lista actualizada.');
    }

    // Un empleado inactivo solo se admite si la ausencia ya era suya
    // (permite corregir datos o inactivar la ausencia de alguien dado de baja).
    if ((int) $empleado['estado'] !== 1 && (int) $actual['id_usuario'] !== $idUsuario) {
        $conn->rollBack();
        responder(422, false, 'No puedes asignar la ausencia a un empleado inactivo.');
    }

    // Choque con otra ausencia ACTIVA del mismo empleado (sin contar la que se edita).
    // Solo se valida si esta ausencia queda activa.
    if ($estado === 1) {
        if ($conHoras) {
            // Parcial: choca con una de día completo, o con una parcial cuyo rango se cruce
            $st = $conn->prepare(
                "SELECT motivo, fechaInicio, fechaFin, horaInicio, horaFin
                 FROM ausencia_usuario
                 WHERE id_usuario           = :usuario
                   AND estado               = 1
                   AND id_ausencia_usuario <> :id
                   AND fechaInicio         <= :fin
                   AND fechaFin            >= :inicio
                   AND (horaInicio IS NULL
                        OR (horaInicio < :horaFin AND horaFin > :horaInicio))
                 ORDER BY fechaInicio ASC
                 LIMIT 1"
            );
            $st->execute([
                ':usuario'    => $idUsuario,
                ':id'         => $idAusencia,
                ':fin'        => $fechaFin,
                ':inicio'     => $fechaInicio,
                ':horaFin'    => $horaFinBD,
                ':horaInicio' => $horaInicioBD,
            ]);
        } else {
            // Día completo: choca con cualquier ausencia activa en esas fechas
            $st = $conn->prepare(
                "SELECT motivo, fechaInicio, fechaFin, horaInicio, horaFin
                 FROM ausencia_usuario
                 WHERE id_usuario           = :usuario
                   AND estado               = 1
                   AND id_ausencia_usuario <> :id
                   AND fechaInicio         <= :fin
                   AND fechaFin            >= :inicio
                 ORDER BY fechaInicio ASC
                 LIMIT 1"
            );
            $st->execute([
                ':usuario' => $idUsuario,
                ':id'      => $idAusencia,
                ':fin'     => $fechaFin,
                ':inicio'  => $fechaInicio,
            ]);
        }

        $choque = $st->fetch();
        if ($choque) {
            $conn->rollBack();
            responder(
                409,
                false,
                'Este empleado ya tiene otra ausencia activa que se cruza con esas fechas u horas: '
                . describirAusencia($choque) . '.'
            );
        }
    }

    // No se toca id_usuario_registro ni fechaRegistro
    $st = $conn->prepare(
        "UPDATE ausencia_usuario
         SET id_usuario    = :usuario,
             fechaInicio   = :inicio,
             fechaFin      = :fin,
             horaInicio    = :horaInicio,
             horaFin       = :horaFin,
             motivo        = :motivo,
             observaciones = :observaciones,
             estado        = :estado
         WHERE id_ausencia_usuario = :id"
    );
    $st->execute([
        ':usuario'       => $idUsuario,
        ':inicio'        => $fechaInicio,
        ':fin'           => $fechaFin,
        ':horaInicio'    => $horaInicioBD,
        ':horaFin'       => $horaFinBD,
        ':motivo'        => $motivo,
        ':observaciones' => $observaciones,
        ':estado'        => $estado,
        ':id'            => $idAusencia,
    ]);

    $conn->commit();

    responder(200, true, 'La ausencia se actualizó correctamente.');

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    error_log('[ausencia/editar] ' . $e->getMessage());
    responder(500, false, 'No se pudo actualizar la ausencia. Intenta de nuevo.');
}