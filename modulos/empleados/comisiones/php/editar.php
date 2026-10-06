<?php
/**
 * modulos/empleados/comisiones/php/editar.php
 * Modifica una configuración de comisión existente.
 * Responde SIEMPRE en JSON.
 *
 * - fechaFin vacía (NULL) = sin fecha de cierre.
 * - Un empleado no puede tener dos configuraciones ACTIVAS con vigencias que se crucen.
 * - No cambia la fecha de creación.
 * - Las comisiones ya generadas no se recalculan: guardan su monto.
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

if (!tienePermiso('emp-comisiones', 'editar')) {
    responder(403, false, 'No tienes permiso para modificar configuraciones de comisión.');
}

// -----------------------------------------------------
// 4. Constantes y helpers
// -----------------------------------------------------
const ID_USUARIO_SISTEMA = 1;
const PORCENTAJE_MAXIMO  = 99.0;
// Tope usado para comparar rangos cuando una configuración no tiene fecha fin
const SIN_FIN = '9999-12-31';

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

// Texto para el mensaje de choque: "15.00 % del 01/03/2026 al 31/12/2026"
function describirConfiguracion(array $r): string
{
    $pct = number_format((float) $r['porcentaje'], 2, '.', '') . ' %';

    if ($r['fechaFin'] === null) {
        return $pct . ' desde el ' . fechaDMA($r['fechaInicio']) . ' (sin fecha fin)';
    }
    return $pct . ' del ' . fechaDMA($r['fechaInicio']) . ' al ' . fechaDMA($r['fechaFin']);
}

// -----------------------------------------------------
// 5. Validación de datos
// -----------------------------------------------------
$idComision  = filter_var($_POST['id_comision'] ?? '', FILTER_VALIDATE_INT);
$idUsuario   = filter_var($_POST['id_usuario'] ?? '', FILTER_VALIDATE_INT);
$porcentTxt  = trim(str_replace(',', '.', (string) ($_POST['porcentaje'] ?? '')));
$fechaInicio = trim((string) ($_POST['fechaInicio'] ?? ''));
$fechaFin    = trim((string) ($_POST['fechaFin'] ?? ''));
$estado      = (string) ($_POST['estado'] ?? '');

if ($idComision === false || $idComision < 1) {
    responder(422, false, 'No se pudo identificar la configuración que quieres editar.');
}
if ($idUsuario === false || $idUsuario < 1 || $idUsuario === ID_USUARIO_SISTEMA) {
    responder(422, false, 'Selecciona un empleado.');
}

// Porcentaje: hasta 2 dígitos enteros y 2 decimales, mayor que 0 y máximo 99
if (!preg_match('/^\d{1,2}(\.\d{1,2})?$/', $porcentTxt)) {
    responder(422, false, 'El porcentaje no es válido. Usa un número de 0.01 a 99, con máximo dos decimales.');
}
$porcentaje = (float) $porcentTxt;
if ($porcentaje <= 0 || $porcentaje > PORCENTAJE_MAXIMO) {
    responder(422, false, 'El porcentaje debe ser mayor que 0 y como máximo 99.');
}
$porcentajeBD = number_format($porcentaje, 2, '.', '');

if (!fechaValida($fechaInicio)) {
    responder(422, false, 'La fecha de inicio no es válida.');
}

if ($fechaFin === '') {
    $fechaFinBD  = null;         // sin fecha de cierre
    $finComparar = SIN_FIN;
} else {
    if (!fechaValida($fechaFin)) {
        responder(422, false, 'La fecha de fin no es válida.');
    }
    if ($fechaFin < $fechaInicio) {
        responder(422, false, 'La fecha de fin no puede ser menor que la fecha de inicio.');
    }
    $fechaFinBD  = $fechaFin;
    $finComparar = $fechaFin;
}

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

    // Luego la configuración que se está editando
    $st = $conn->prepare(
        "SELECT id_usuario FROM asignacioncomision
         WHERE id_asignacion_comision = :id
         FOR UPDATE"
    );
    $st->execute([':id' => $idComision]);
    $actual = $st->fetch();

    if (!$actual) {
        $conn->rollBack();
        responder(404, false, 'Esta configuración ya no existe. Recarga el módulo para ver la lista actualizada.');
    }

    // Un empleado inactivo solo se admite si la configuración ya era suya
    // (permite corregir datos o inactivar la configuración de alguien dado de baja).
    if ((int) $empleado['estado'] !== 1 && (int) $actual['id_usuario'] !== $idUsuario) {
        $conn->rollBack();
        responder(422, false, 'No puedes asignar la configuración a un empleado inactivo.');
    }

    // Choque de vigencias con otra configuración ACTIVA del mismo empleado
    // (sin contar la que se edita). Solo se valida si esta queda activa.
    // Los rangos incluyen ambos extremos.
    if ($estado === 1) {
        $st = $conn->prepare(
            "SELECT porcentaje, fechaInicio, fechaFin
             FROM asignacioncomision
             WHERE id_usuario              = :usuario
               AND estado                  = 1
               AND id_asignacion_comision <> :id
               AND fechaInicio            <= :fin
               AND (fechaFin IS NULL OR fechaFin >= :inicio)
             ORDER BY fechaInicio ASC
             LIMIT 1"
        );
        $st->execute([
            ':usuario' => $idUsuario,
            ':id'      => $idComision,
            ':fin'     => $finComparar,
            ':inicio'  => $fechaInicio,
        ]);

        $choque = $st->fetch();
        if ($choque) {
            $conn->rollBack();
            responder(
                409,
                false,
                'Este empleado ya tiene otra configuración activa que se cruza con esas fechas: '
                . describirConfiguracion($choque)
                . '. Ponle fecha fin a esa configuración o ajusta las fechas de esta.'
            );
        }
    }

    // No se toca fechaRegistro
    $st = $conn->prepare(
        "UPDATE asignacioncomision
         SET id_usuario  = :usuario,
             porcentaje  = :porcentaje,
             fechaInicio = :inicio,
             fechaFin    = :fin,
             estado      = :estado
         WHERE id_asignacion_comision = :id"
    );
    $st->execute([
        ':usuario'    => $idUsuario,
        ':porcentaje' => $porcentajeBD,
        ':inicio'     => $fechaInicio,
        ':fin'        => $fechaFinBD,
        ':estado'     => $estado,
        ':id'         => $idComision,
    ]);

    $conn->commit();

    responder(200, true, 'La configuración de comisión se actualizó correctamente.');

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    error_log('[comisiones/editar] ' . $e->getMessage());
    responder(500, false, 'No se pudo actualizar la configuración. Intenta de nuevo.');
}