<?php
/**
 * permisos.php
 * Funciones para verificar permisos sobre módulos usando $_SESSION['permisos'].
 * No consulta la BD: los permisos ya se cargaron en el login.
 */

/**
 * Devuelve true si el usuario logueado tiene el permiso solicitado sobre un módulo.
 *
 * @param string $codigoModulo  Ej: 'servicios', 'ventas-realizar'
 * @param string $accion        'ver' | 'crear' | 'editar' | 'eliminar'
 */
function tienePermiso(string $codigoModulo, string $accion = 'ver'): bool
{
    $mapa = [
        'ver'      => 'puedeConsultar',
        'crear'    => 'puedeCrear',
        'editar'   => 'puedeModificar',
        'eliminar' => 'puedeEliminar',
    ];

    if (!isset($mapa[$accion])) {
        return false;
    }

    $columna = $mapa[$accion];

    foreach ($_SESSION['permisos'] ?? [] as $p) {
        if ($p['accionCodigo'] === $codigoModulo && (int) $p[$columna] === 1) {
            return true;
        }
    }
    return false;
}

/**
 * Corta la ejecución si el usuario no tiene permiso.
 * Devuelve 403 y termina.
 */
function requierePermiso(string $codigoModulo, string $accion = 'ver'): void
{
    if (!tienePermiso($codigoModulo, $accion)) {
        http_response_code(403);
        exit('No tienes permiso para acceder a este módulo.');
    }
}