<?php
/**
 * header.php
 * Head + apertura del body + apertura del layout.
 * Se incluye al inicio de cada página del dashboard.
 */

// Si no se definió un título, usamos el default
if (!isset($tituloPagina)) {
    $tituloPagina = 'Panel — Peluquería & Estética Europa';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($tituloPagina) ?></title>

  <link rel="stylesheet" href="/Peluqueria/dashboard/css/style.css" />
  <link rel="stylesheet" href="/Peluqueria/dashboard/css/panel.css" />
  <link rel="stylesheet" href="/Peluqueria/dashboard/css/admin.css" />
  <link rel="stylesheet" href="/Peluqueria/modulos/seguridad/modulos/css/modulos.css" />
  <link rel="stylesheet" href="/Peluqueria/modulos/mantenimiento/servicios/css/servicios.css" />
  <link rel="stylesheet" href="/Peluqueria/modulos/mantenimiento/membresias/css/membresias.css" />

  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet" />
</head>
<body class="panel-body">

<div class="panel-layout">