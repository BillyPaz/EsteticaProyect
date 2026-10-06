<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Europa — Peluquería & Estética</title>
  <link rel="stylesheet" href="css/style.css" />
  <link rel="stylesheet" href="chatbot/chatbot.css" />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Inter:wght@300;400;500&display=swap" rel="stylesheet"/>
</head>
<body>

  <!-- NAVBAR -->
  <nav class="navbar">
  <div class="nav-links">
    <a href="#servicios">Servicios</a>
    <a href="#equipo">Equipo</a>
    <a href="#galeria">Galería</a>
    <a href="https://wa.me/50200000000" target="_blank" class="nav-whatsapp" title="WhatsApp">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
        <path d="M12 0C5.373 0 0 5.373 0 12c0 2.123.554 4.17 1.523 5.955L.057 23.428a.75.75 0 0 0 .921.921l5.473-1.466A11.943 11.943 0 0 0 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.75a9.694 9.694 0 0 1-4.947-1.354l-.355-.211-3.676.985.985-3.598-.23-.371A9.696 9.696 0 0 1 2.25 12C2.25 6.615 6.615 2.25 12 2.25S21.75 6.615 21.75 12 17.385 21.75 12 21.75z"/>
      </svg>
    </a>
  </div>
  <div class="nav-brand">Peluqueria y Estética Europa</div>
  <div class="nav-cta">
    <a href="citas.php" class="btn-reservar">Reservar una cita</a>
  </div>
</nav>

  <!-- HERO -->
  <section class="hero">
    <div class="hero-text">
      <span class="hero-eyebrow fade-up">Peluquería & Estética</span>
      <h1 class="fade-up delay-1">Bienvenidos a<br/><em>Peluquería Europa</em></h1>
      <p class="fade-up delay-2">Donde el estilo se encuentra con el cuidado. Cortes, color y tratamientos con precisión y elegancia.</p>
      <a href="#servicios" class="btn-dark fade-up delay-3">Conoce nuestros servicios →</a>
    </div>
    <div class="hero-image-wrap">
      <div class="hero-arch fade-in-scale">
        <img src="fondo.jpg" alt="Modelo Europa" class="hero-img" id="logo"/>
      </div>
    </div>
  </section>

  <!-- FILOSOFÍA DE COLORES -->
  <section class="colores" id="colores">
    <h2 class="section-title reveal">El significado de nuestros colores</h2>
    <p class="section-sub reveal">Cada tono de Europa fue elegido con un propósito</p>
    <div class="colores-grid">
      <article class="color-card reveal">
        <span class="color-dot dot-negro"></span>
        <h3>Negro — Elegancia</h3>
        <p>Representa la sobriedad y el carácter atemporal de nuestro salón. Es la base sobre la que construimos cada estilo: firme, seguro y siempre de buen gusto.</p>
      </article>
      <article class="color-card reveal delay-1">
        <span class="color-dot dot-oro"></span>
        <h3>Oro — Distinción</h3>
        <p>Es el detalle que marca la diferencia. Simboliza el trato exclusivo, la calidad de nuestros productos y el brillo con el que queremos que salgas de aquí.</p>
      </article>
      <article class="color-card reveal delay-2">
        <span class="color-dot dot-blanco"></span>
        <h3>Blanco — Pureza</h3>
        <p>Habla de limpieza, higiene y claridad. Un espacio impecable donde puedes relajarte con la confianza de estar en manos profesionales.</p>
      </article>
    </div>
  </section>

  <!-- SERVICIOS -->
  <section class="servicios" id="servicios">
    <h2 class="section-title reveal">Nuestros servicios</h2>
    <div class="servicios-grid">

      <div class="servicio-card reveal">
        <h3>Cortes y estilismo</h3>
        <p>Diseño personalizado según tu estilo y tipo de cabello.</p>
        <a href="citas.php" class="btn-dark small">Ver más →</a>
      </div>

      <div class="servicio-card reveal delay-1">
        <h3>Corrección de color</h3>
        <p>Técnicas modernas para lograr el tono que siempre quisiste.</p>
        <a href="citas.php" class="btn-dark small">Ver más →</a>
      </div>

      <div class="servicio-card card-img reveal delay-2">
        <img src="mujer.jpg" alt="Servicio Europa"/>
      </div>

      <div class="servicio-card reveal delay-3">
        <h3>Tratamientos para el cabello</h3>
        <p>Nutrición, hidratación y reparación profunda.</p>
        <a href="citas.php" class="btn-dark small">Ver más →</a>
      </div>

      <div class="servicio-card reveal delay-1">
        <h3>Estética facial</h3>
        <p>Limpieza, masajes y cuidado profesional para tu piel.</p>
        <a href="citas.php" class="btn-dark small">Ver más →</a>
      </div>

    </div>
  </section>

  <!-- EQUIPO -->
  <section class="equipo" id="equipo">
    <h2 class="section-title light reveal">Nuestro equipo</h2>
    <p class="section-sub light reveal">Las manos detrás de cada transformación</p>
    <div class="equipo-grid">

      <article class="equipo-card reveal">
        <div class="equipo-foto">
          <img src="barbero1.jpg" alt="Integrante del equipo Europa"/>
        </div>
        <h3>Nombre del empleado</h3>
        <span class="equipo-rol">Estilista principal</span>
        <p>Especialista en cortes de precisión y asesoría de imagen personalizada.</p>
      </article>

      <article class="equipo-card reveal delay-1">
        <div class="equipo-foto">
          <img src="barbero2.jpg" alt="Integrante del equipo Europa"/>
        </div>
        <h3>Nombre del empleado</h3>
        <span class="equipo-rol">Colorista</span>
        <p>Experta en corrección de color, mechas y técnicas de iluminación.</p>
      </article>

      <article class="equipo-card reveal delay-2">
        <div class="equipo-foto">
          <img src="barbero3.jpg" alt="Integrante del equipo Europa"/>
        </div>
        <h3>Nombre del empleado</h3>
        <span class="equipo-rol">Estética facial</span>
        <p>Tratamientos faciales, maquillaje profesional y cuidado de la piel.</p>
      </article>

    </div>
  </section>

  <!-- GALERÍA CON FORMAS -->
  <section class="galeria" id="galeria">
    <h2 class="section-title light reveal">Nuestra estética</h2>
    <p class="section-sub light reveal">Un vistazo a nuestro espacio y trabajo</p>
    <div class="galeria-grid">
      <div class="gal-item shape-tall reveal">
        <img src="imagen3.jpg" alt="Estética Europa"/>
      </div>
      <div class="gal-item shape-circle reveal delay-1">
        <img src="imagen4.jpg" alt="Estética Europa"/>
      </div>
      <div class="gal-item shape-wide reveal delay-2">
        <img src="imagen5.jpg" alt="Estética Europa"/>
      </div>
      <div class="gal-item shape-circle reveal delay-1">
        <img src="imagen6.jpg" alt="Estética Europa"/>
      </div>
      <div class="gal-item shape-tall reveal delay-2">
        <img src="imagen7.png" alt="Estética Europa"/>
      </div>
    </div>
  </section>

  <?php
// =====================================================
// SECCIÓN PÚBLICA DE HORARIOS
// Conexión a BD (solo lectura) y render dinámico.
// =====================================================

// Días en orden correcto (coinciden EXACTAMENTE con el ENUM de la BD)
$DIAS_ORDEN = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'];

$horarios   = [];        // filas válidas para renderizar
$errorHorarios = false;  // control de error para mostrar mensaje neutro

// Ruta a la conexión
$rutaConexion = __DIR__ . '/../config/conexion.php';

if (!file_exists($rutaConexion)) {
    $errorHorarios = true;
} else {
    try {
        // PDO propio (no usamos conexionBD() para no romper la página con su exit())
        $conn = new PDO(
            "mysql:host=127.0.0.1;port=3306;dbname=estetica;charset=utf8mb4",
            "root",
            "",
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );

        $sql = "SELECT dias, horaApertura, horaCierre, estado
                FROM horarios_estetica
                ORDER BY FIELD(dias, 'Lunes','Martes','Miércoles','Jueves','Viernes','Sabado','Domingo')";

        $stmt = $conn->query($sql);
        $horarios = $stmt->fetchAll();

    } catch (PDOException $e) {
        // Silencioso para el público. Solo activamos bandera.
        $errorHorarios = true;
    }
}

// Helper local para formatear hora TIME (HH:MM:SS) → HH:MM
if (!function_exists('formatoHoraHM')) {
    function formatoHoraHM(?string $hora): string {
        if (!$hora) return '';
        return substr($hora, 0, 5);
    }
}

// Helper local para escapar HTML
if (!function_exists('e')) {
    function e($valor): string {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}
?>

<!-- HORARIOS -->
<section class="horarios">
  <div class="horarios-inner">
    <h2 class="section-title reveal">Horarios de atención</h2>

    <div class="horarios-grid">
      <?php if ($errorHorarios): ?>

        <div class="horario-item reveal" style="grid-column: 1 / -1; text-align:center;">
          <span class="dia">Horarios no disponibles por el momento.</span>
        </div>

      <?php elseif (empty($horarios)): ?>

        <div class="horario-item reveal" style="grid-column: 1 / -1; text-align:center;">
          <span class="dia">Aún no hay horarios configurados.</span>
        </div>

      <?php else: ?>

        <?php foreach ($horarios as $h): ?>
          <?php if ((int)$h['estado'] === 1): ?>

            <div class="horario-item reveal">
              <span class="dia"><?= e($h['dias']) ?></span>
              <span class="horas">
                <?= e(formatoHoraHM($h['horaApertura'])) ?> — <?= e(formatoHoraHM($h['horaCierre'])) ?>
              </span>
            </div>

          <?php else: ?>

            <div class="horario-item cerrado reveal">
              <span class="dia"><?= e($h['dias']) ?></span>
              <span class="estado cerrado-tag">Cerrado</span>
            </div>

          <?php endif; ?>
        <?php endforeach; ?>

      <?php endif; ?>
    </div>

    <div class="horarios-cta reveal">
      <a href="reservaciones.php" class="btn-dark">Reservar una cita →</a>
    </div>
  </div>
</section>

  <!-- TICKER -->
  <section class="ticker-wrap">
    <div class="ticker">
      <span>Nos vemos pronto &nbsp;·&nbsp; Reserva tu cita &nbsp;·&nbsp; Peluquería y Estética Europa &nbsp;·&nbsp; Nos vemos pronto &nbsp;·&nbsp; Reserva tu cita &nbsp;·&nbsp; Peluquería y Estética Europa &nbsp;·&nbsp;</span>
      <span aria-hidden="true">Nos vemos pronto &nbsp;·&nbsp; Reserva tu cita &nbsp;·&nbsp; Peluquería y Estética Europa &nbsp;·&nbsp; Nos vemos pronto &nbsp;·&nbsp; Reserva tu cita &nbsp;·&nbsp; Peluquería y Estética Europa &nbsp;·&nbsp;</span>
    </div>
  </section>

  <!-- FOOTER -->
  <footer class="footer">
    <div class="footer-brand">Europa</div>
    <div class="footer-cols">
      <div class="footer-col">
        <h4>Menú</h4>
        <a href="#servicios">Servicios</a>
        <a href="#equipo">Equipo</a>
        <a href="#galeria">Galería</a>
        <a href="citas.html">Reservar</a>
      </div>
      <div class="footer-col">
        <h4>Síguenos</h4>
        <a href="#">Instagram</a>
        <a href="#">Facebook</a>
        <a href="#">TikTok</a>
      </div>
      <div class="footer-col">
        <h4>Contacto</h4>
        <p>+502 0000 0000</p>
        <p>europa@email.com</p>
      </div>
    </div>
    <p class="footer-copy">© 2026 Europa Peluquería & Estética. Todos los derechos reservados.</p>
  </footer>

  <script>
    // Animaciones al hacer scroll
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15 });

    document.querySelectorAll('.reveal').forEach((el) => observer.observe(el));

    // Sombra en la navbar al bajar
    const nav = document.querySelector('.navbar');
    window.addEventListener('scroll', () => {
      nav.classList.toggle('nav-scrolled', window.scrollY > 30);
    });
  </script>

  <script>
        const logo = document.getElementById('logo');
        let clickCount = 0;
        let clickTimer;

        logo.addEventListener('click', () => {
            clickCount++;

            clearTimeout(clickTimer);
            clickTimer = setTimeout(() => {
                clickCount = 0;
            }, 1500);

            if (clickCount === 3) {
                window.location.href = "../login/php/sesion.php";
            }
        });
    </script>
    <?php include __DIR__ . '/chatbot/chatbot.php'; ?>

</body>
</html>