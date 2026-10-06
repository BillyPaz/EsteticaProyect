<?php
/**
 * modulos/mantenimiento/horario-estetica/php/editar_cierre.php
 * Modifica un cierre programado existente. Responde SIEMPRE en JSON.
 *
 * - Sin horas (NULL)  → cierre de día completo (o de varios días).
 * - Con horas         → cierre parcial, solo si fechaInicio = fechaFin.
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

if (!tienePermiso('horario-estetica', 'editar')) {
    responder(403, false, 'No tienes permiso para modificar cierres de la estética.');
}

// -----------------------------------------------------
// 4. Constantes y helpers
// -----------------------------------------------------
const TIPOS_CIERRE = [
    'vacaciones'   => 'Vacaciones',
    'remodelacion' => 'Remodelación',
    'imprevisto'   => 'Imprevisto',
    'feriado'      => 'Feriado',
    'otro'         => 'Otro',
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

function describirCierre(array $r): string
{
    $tipo = TIPOS_CIERRE[$r['tipo']] ?? (string) $r['tipo'];

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
$idCierre      = filter_var($_POST['id_cierre'] ?? '', FILTER_VALIDATE_INT);
$tipo          = (string) ($_POST['tipo'] ?? '');
$fechaInicio   = trim((string) ($_POST['fechaInicio'] ?? ''));
$fechaFin      = trim((string) ($_POST['fechaFin'] ?? ''));
$horaInicio    = trim((string) ($_POST['horaInicio'] ?? ''));
$horaFin       = trim((string) ($_POST['horaFin'] ?? ''));
$observaciones = trim((string) ($_POST['observaciones'] ?? ''));
$estado        = (string) ($_POST['estado'] ?? '');

if ($idCierre === false || $idCierre < 1) {
    responder(422, false, 'No se pudo identificar el cierre que quieres editar.');
}
if (!array_key_exists($tipo, TIPOS_CIERRE)) {
    responder(422, false, 'Selecciona un tipo de cierre válido.');
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

$conHoras = ($horaInicio !== '' || $horaFin !== '');

if ($conHoras) {
    if ($horaInicio === '' || $horaFin === '') {
        responder(422, false, 'Indica la hora de inicio y la hora de fin, o deja ambas vacías si es todo el día.');
    }
    if (!preg_match(PATRON_HORA, $horaInicio) || !preg_match(PATRON_HORA, $horaFin)) {
        responder(422, false, 'El formato de las horas no es válido.');
    }
    if ($fechaInicio !== $fechaFin) {
        responder(422, false, 'El horario solo se puede indicar cuando el cierre es de un solo día.');
    }
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

    $st = $conn->prepare(
        "SELECT id_cierre_estetica FROM cierres_estetica
         WHERE id_cierre_estetica = :id
         FOR UPDATE"
    );
    $st->execute([':id' => $idCierre]);
    if (!$st->fetch()) {
        $conn->rollBack();
        responder(404, false, 'Este cierre ya no existe. Recarga el módulo para ver la lista actualizada.');
    }

    // Choque con otro cierre ACTIVO (sin contar el que se edita).
    // Solo se valida si este cierre queda activo.
    if ($estado === 1) {
        if ($conHoras) {
            $st = $conn->prepare(
                "SELECT tipo, fechaInicio, fechaFin, horaInicio, horaFin
                 FROM cierres_estetica
                 WHERE estado             = 1
                   AND id_cierre_estetica <> :id
                   AND fechaInicio        <= :fin
                   AND fechaFin           >= :inicio
                   AND (horaInicio IS NULL
                        OR (horaInicio < :horaFin AND horaFin > :horaInicio))
                 ORDER BY fechaInicio ASC
                 LIMIT 1"
            );
            $st->execute([
                ':id'         => $idCierre,
                ':fin'        => $fechaFin,
                ':inicio'     => $fechaInicio,
                ':horaFin'    => $horaFinBD,
                ':horaInicio' => $horaInicioBD,
            ]);
        } else {
            $st = $conn->prepare(
                "SELECT tipo, fechaInicio, fechaFin, horaInicio, horaFin
                 FROM cierres_estetica
                 WHERE estado             = 1
                   AND id_cierre_estetica <> :id
                   AND fechaInicio        <= :fin
                   AND fechaFin           >= :inicio
                 ORDER BY fechaInicio ASC
                 LIMIT 1"
            );
            $st->execute([
                ':id'     => $idCierre,
                ':fin'    => $fechaFin,
                ':inicio' => $fechaInicio,
            ]);
        }

        $choque = $st->fetch();
        if ($choque) {
            $conn->rollBack();
            responder(
                409,
                false,
                'Ya existe otro cierre activo que se cruza con esas fechas u horas: '
                . describirCierre($choque) . '.'
            );
        }
    }

    // No se toca id_usuario_registro ni fechaRegistro
    $st = $conn->prepare(
        "UPDATE cierres_estetica
         SET fechaInicio   = :inicio,
             fechaFin      = :fin,
             horaInicio    = :horaInicio,
             horaFin       = :horaFin,
             tipo          = :tipo,
             observaciones = :observaciones,
             estado        = :estado
         WHERE id_cierre_estetica = :id"
    );
    $st->execute([
        ':inicio'        => $fechaInicio,
        ':fin'           => $fechaFin,
        ':horaInicio'    => $horaInicioBD,
        ':horaFin'       => $horaFinBD,
        ':tipo'          => $tipo,
        ':observaciones' => $observaciones,
        ':estado'        => $estado,
        ':id'            => $idCierre,
    ]);

    $conn->commit();

    responder(200, true, 'El cierre se actualizó correctamente.');

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    error_log('[horario-estetica/editar_cierre] ' . $e->getMessage());
    responder(500, false, 'No se pudo actualizar el cierre. Intenta de nuevo.');
}