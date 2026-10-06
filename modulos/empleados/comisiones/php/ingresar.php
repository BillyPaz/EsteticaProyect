<?php
/**
 * modulos/empleados/comisiones/php/ingresar.php
 * Registra una configuración de comisión (empleado + porcentaje + vigencia).
 * Responde SIEMPRE en JSON.
 *
 * - fechaFin vacía (NULL) = sin fecha de cierre.
 * - Un empleado no puede tener dos configuraciones ACTIVAS con vigencias que se crucen.
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

if (!tienePermiso('emp-comisiones', 'crear')) {
    responder(403, false, 'No tienes permiso para registrar configuraciones de comisión.');
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
$idUsuario  = filter_var($_POST['id_usuario'] ?? '', FILTER_VALIDATE_INT);
$porcentTxt = trim(str_replace(',', '.', (string) ($_POST['porcentaje'] ?? '')));
$fechaInicio = trim((string) ($_POST['fechaInicio'] ?? ''));
$fechaFin    = trim((string) ($_POST['fechaFin'] ?? ''));
$estado      = (string) ($_POST['estado'] ?? '1');

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
    $fechaFinBD = null;          // sin fecha de cierre
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
// 6. Guardado (en transacción)
// -----------------------------------------------------
$conn = conexionBD();

try {
    $conn->beginTransaction();

    // Bloquea la fila del empleado: dos peticiones simultáneas se ejecutan una tras otra
    $st = $conn->prepare(
        "SELECT id_usuario FROM usuarios
         WHERE id_usuario = :id AND estado = 1
         FOR UPDATE"
    );
    $st->execute([':id' => $idUsuario]);
    if (!$st->fetch()) {
        $conn->rollBack();
        responder(422, false, 'El empleado seleccionado no existe o está inactivo.');
    }

    // Choque de vigencias con otra configuración ACTIVA del mismo empleado.
    // Solo se valida si la nueva también queda activa.
    // Los rangos incluyen ambos extremos: si una termina el 31/12 y la otra
    // empieza el 31/12, se cruzan; si empieza el 01/01, no.
    if ($estado === 1) {
        $st = $conn->prepare(
            "SELECT porcentaje, fechaInicio, fechaFin
             FROM asignacioncomision
             WHERE id_usuario   = :usuario
               AND estado       = 1
               AND fechaInicio <= :fin
               AND (fechaFin IS NULL OR fechaFin >= :inicio)
             ORDER BY fechaInicio ASC
             LIMIT 1"
        );
        $st->execute([
            ':usuario' => $idUsuario,
            ':fin'     => $finComparar,
            ':inicio'  => $fechaInicio,
        ]);

        $choque = $st->fetch();
        if ($choque) {
            $conn->rollBack();
            responder(
                409,
                false,
                'Este empleado ya tiene una configuración activa que se cruza con esas fechas: '
                . describirConfiguracion($choque)
                . '. Ponle fecha fin a esa configuración o ajusta las fechas de la nueva.'
            );
        }
    }

    $st = $conn->prepare(
        "INSERT INTO asignacioncomision (id_usuario, porcentaje, fechaInicio, fechaFin, estado)
         VALUES (:usuario, :porcentaje, :inicio, :fin, :estado)"
    );
    $st->execute([
        ':usuario'    => $idUsuario,
        ':porcentaje' => $porcentajeBD,
        ':inicio'     => $fechaInicio,
        ':fin'        => $fechaFinBD,
        ':estado'     => $estado,
    ]);

    $idNuevo = (int) $conn->lastInsertId();
    $conn->commit();

    responder(200, true, 'La configuración de comisión se registró correctamente.', ['id' => $idNuevo]);

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    error_log('[comisiones/ingresar] ' . $e->getMessage());
    responder(500, false, 'No se pudo guardar la configuración. Intenta de nuevo.');
}