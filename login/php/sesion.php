<?php
session_start();  
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Acceso administradores — Peluquería & Estética Europa</title>
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="../css/sesion.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Inter:wght@300;400;500&display=swap" rel="stylesheet"/>
</head>
<body class="sesion-body">

  <!-- NAVBAR -->
  <nav class="navbar">
    <div class="nav-links">
      <a href="../../public/index.php#servicios">Servicios</a>
      <a href="../../public/index.php#equipo">Equipo</a>
      <a href="../../public/index.php#galeria">Galería</a>
    </div>
    <div class="nav-brand">Peluqueria y Estética Europa</div>
    <div class="nav-cta">
      <a href="../../public/index.php" class="btn-reservar">← Volver al inicio</a>
    </div>
  </nav>

  <!-- HERO CON LOGIN -->
  <section class="hero sesion-hero">
    <div class="hero-text login-wrap">
      <span class="hero-eyebrow fade-up">Panel interno</span>
      <h2 class="login-titulo fade-up delay-1">Acceso de <em>administradores</em></h2>
      <p class="login-sub fade-up delay-2">Ingresa tus credenciales para gestionar citas, horarios y servicios del salón.</p>

      <form class="login-form fade-up delay-3" id="formLogin">
        <div class="campo-login">
          <label for="usuario">Usuario</label>
          <div class="input-icono">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
            </svg>
            <input type="text" id="usuario" name="usuario" placeholder="admin.europa" autocomplete="username" />
          </div>
        </div>

        <div class="campo-login">
          <label for="password">Contraseña</label>
          <div class="input-icono">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
              <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
            <input type="password" id="password" name="password" placeholder="••••••••" autocomplete="current-password" />
            <button type="button" class="ver-pass" id="verPass" aria-label="Mostrar contraseña">Ver</button>
          </div>
        </div>

        <div class="login-extra">
          <label class="recordar">
            <input type="checkbox" /> <span>Recordarme</span>
          </label>
          <a href="#" class="link-recuperar">¿Olvidaste tu contraseña?</a>
        </div>

        <button type="submit" class="btn-dark btn-login">Ingresar</button>

        <p class="login-nota">Acceso exclusivo para el personal autorizado de Europa.</p>
      </form>
    </div>

    <div class="hero-image-wrap">
      <div class="hero-arch fade-in-scale">
        <img src="../img/fondo.jpg" alt="Salón Europa" class="hero-img"/>
      </div>
    </div>
  </section>

  <footer class="footer sesion-footer">
    <p class="footer-copy">© 2026 Peluquería y Estética Europa — Panel administrativo</p>
  </footer>

</body>
  <script src="https://code.jquery.com/jquery-4.0.0.js" integrity="sha256-9fsHeVnKBvqh3FB2HYu7g2xseAZ5MlN6Kz/qnkASV8U=" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="../js/app.js" ></script> 
</html>
