<?php
/**
 * Lista maestra de módulos del sistema.
 * Fuente de verdad para sidebar, permisos y enrutador.
 *
 * Estructura:
 *   - id      : identificador único (coincide con ?seccion= y con data-seccion del sidebar)
 *   - nombre  : etiqueta visible
 *   - icono   : SVG inline (copiado literal del admin.html original)
 *   - grupo   : grupo al que pertenece (o null si es módulo suelto)
 *   - visible : si aparece en el sidebar (todos true por ahora)
 */

$GRUPOS = [
    'ventas'         => ['nombre' => 'Ventas',         'icono' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="6" rx="8" ry="3"/><path d="M4 6v6c0 1.66 3.58 3 8 3s8-1.34 8-3V6"/><path d="M4 12v6c0 1.66 3.58 3 8 3s8-1.34 8-3v-6"/></svg>'],
    'empleados'      => ['nombre' => 'Empleados',      'icono' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.2"/><path d="M3.5 19c0-3.3 2.6-5.5 5.5-5.5s5.5 2.2 5.5 5.5"/><circle cx="17.5" cy="8.5" r="2.4"/><path d="M15.7 13.7c2.2.3 3.8 2.2 3.8 4.5"/></svg>'],
    'mantenimientos' => ['nombre' => 'Mantenimientos', 'icono' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a4 4 0 0 0-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 0 0 5.4-5.4l-2.5 2.5-2-2 2.5-2.5z"/></svg>'],
    'caja'           => ['nombre' => 'Caja',           'icono' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="9" width="18" height="11" rx="2"/><path d="M7 9V7a5 5 0 0 1 10 0v2"/><path d="M12 13v3"/></svg>'],
    'ajustes'        => ['nombre' => 'Ajustes',        'icono' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3.2"/><path d="M19.4 13a1.7 1.7 0 0 0 .34 1.87l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.7 1.7 0 0 0-1.87-.34 1.7 1.7 0 0 0-1 1.55V19a2 2 0 1 1-4 0v-.09a1.7 1.7 0 0 0-1-1.55 1.7 1.7 0 0 0-1.87.34l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.7 1.7 0 0 0 .34-1.87 1.7 1.7 0 0 0-1.55-1H4a2 2 0 1 1 0-4h.09a1.7 1.7 0 0 0 1.55-1 1.7 1.7 0 0 0-.34-1.87l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.7 1.7 0 0 0 1.87.34H10a1.7 1.7 0 0 0 1-1.55V4a2 2 0 1 1 4 0v.09a1.7 1.7 0 0 0 1 1.55 1.7 1.7 0 0 0 1.87-.34l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.7 1.7 0 0 0-.34 1.87V10a1.7 1.7 0 0 0 1.55 1H20a2 2 0 1 1 0 4h-.09a1.7 1.7 0 0 0-1.55 1z"/></svg>'],
];

/**
 * Módulos del sistema. Orden = orden de aparición en el sidebar.
 */
$MODULOS = [
    // ---- Citas (suelto) ----
    'citas' => [
        'nombre' => 'Citas',
        'grupo'  => null,
        'icono'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/></svg>',
    ],

    // ---- Ventas ----
    'ventas-realizar' => [
        'nombre' => 'Realizar ventas',
        'grupo'  => 'ventas',
        'icono'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3h9l3 3v15H6z"/><path d="M15 3v3h3"/><path d="M9 12h6M9 16h6"/></svg>',
    ],
    'ventas-historial' => [
        'nombre' => 'Historial de ventas',
        'grupo'  => 'ventas',
        'icono'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v4h4"/><path d="M12 8v4l3 2"/></svg>',
    ],
    'citas-pagas' => [
        'nombre' => 'Citas pagas',
        'grupo'  => 'ventas',
        'icono'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/><path d="M8.5 15.5l2 2 4-4"/></svg>',
    ],

    // ---- Empleados ----
    'emp-registrar' => [
        'nombre' => 'Registrar empleados',
        'grupo'  => 'empleados',
        'icono'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.2"/><path d="M3.5 19c0-3.3 2.6-5.5 5.5-5.5s5.5 2.2 5.5 5.5"/><path d="M17 8h4M19 6v4"/></svg>',
    ],
    'emp-horario' => [
        'nombre' => 'Horario de empleado',
        'grupo'  => 'empleados',
        'icono'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/></svg>',
    ],
    'emp-ausencia' => [
        'nombre' => 'Ausencia empleado',
        'grupo'  => 'empleados',
        'icono'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/><path d="M9 15l3 3 3-3"/></svg>',
    ],
    'emp-comisiones' => [
        'nombre' => 'Comisiones empleados',
        'grupo'  => 'empleados',
        'icono'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M19 5 5 19"/><circle cx="7.5" cy="7.5" r="2.5"/><circle cx="16.5" cy="16.5" r="2.5"/></svg>',
    ],

    // ---- Mantenimientos ----
    'servicios' => [
        'nombre' => 'Servicios',
        'grupo'  => 'mantenimientos',
        'icono'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="6" cy="6" r="2.4"/><circle cx="6" cy="18" r="2.4"/><path d="M8.5 8 20 19M20 5 8.5 16"/></svg>',
    ],
    'horario-estetica' => [
        'nombre' => 'Horario estética',
        'grupo'  => 'mantenimientos',
        'icono'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/></svg>',
    ],
    'inventario' => [
        'nombre' => 'Inventario',
        'grupo'  => 'mantenimientos',
        'icono'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7l9-4 9 4-9 4-9-4z"/><path d="M3 7v10l9 4 9-4V7"/><path d="M12 11v10"/></svg>',
    ],
    'productos' => [
        'nombre' => 'Productos',
        'grupo'  => 'mantenimientos',
        'icono'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12.6 3H5a2 2 0 0 0-2 2v7.6c0 .5.2 1 .6 1.4l8.4 8.4a2 2 0 0 0 2.8 0l6.6-6.6a2 2 0 0 0 0-2.8L13 3.6a2 2 0 0 0-1.4-.6z"/><circle cx="8" cy="8" r="1.4"/></svg>',
    ],
    'membresias' => [
        'nombre' => 'Membresías',
        'grupo'  => 'mantenimientos',
        'icono'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2.5" y="5" width="19" height="14" rx="2.2"/><path d="M2.5 10h19"/><path d="M6 15h5"/></svg>',
    ],
    'clientes' => [
        'nombre' => 'Clientes',
        'grupo'  => 'mantenimientos',
        'icono'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="3.5"/><path d="M4.5 20c0-4.1 3.4-7 7.5-7s7.5 2.9 7.5 7"/></svg>',
    ],

    // ---- Caja ----
    'caja-apertura' => [
        'nombre' => 'Aperturar caja',
        'grupo'  => 'caja',
        'icono'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V7a4 4 0 0 1 7.6-1.8"/></svg>',
    ],
    'caja-cierre' => [
        'nombre' => 'Cierre de caja',
        'grupo'  => 'caja',
        'icono'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>',
    ],

    // ---- Ajustes ----
    'accesos' => [
        'nombre' => 'Accesos',
        'grupo'  => 'ajustes',
        'icono'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>',
    ],
    'modulos' => [
        'nombre' => 'Módulos',
        'grupo'  => 'ajustes',
        'icono'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>',
    ],
    'auditoria' => [
        'nombre' => 'Auditoría',
        'grupo'  => 'ajustes',
        'icono'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M7 3h7l4 4v14H7z"/><path d="M14 3v4h4"/><path d="M9.5 12h5M9.5 16h5"/></svg>',
    ],
];

/** Módulo por defecto al entrar sin ?seccion= */
$MODULO_DEFECTO = 'citas';