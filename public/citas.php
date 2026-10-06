<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Reservar una cita — Peluquería & Estética Europa</title>
  <link rel="stylesheet" href="css/style.css" />
  <link rel="stylesheet" href="css/citas.css" />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Inter:wght@300;400;500&display=swap" rel="stylesheet"/>
</head>
<body class="citas-body">

  <!-- NAVBAR -->
  <nav class="navbar">
    <div class="nav-links">
      <a href="index.php#servicios">Servicios</a>
      <a href="index.php#equipo">Equipo</a>
      <a href="index.php#galeria">Galería</a>
    </div>
    <div class="nav-brand">Peluqueria y Estética Europa</div>
    <div class="nav-cta">
      <a href="index.php" class="btn-reservar">← Volver al inicio</a>
    </div>
  </nav>

  <!-- ENCABEZADO -->
  <header class="citas-hero">
    <span class="hero-eyebrow fade-up">Agenda</span>
    <h1 class="fade-up delay-1">Reserva tu <em>cita</em></h1>
    <p class="fade-up delay-2">Seleccioná el día que prefieras y te llevaremos a completar tu reserva.</p>
  </header>

  <!-- CALENDARIO -->
  <main class="calendario-wrap">
    <div class="calendario">
      

      <div class="cal-header">

        <button class="cal-nav" id="prevMes" aria-label="Mes anterior">‹</button>
        <h2 id="mesTitulo">Mes</h2>
        <button class="cal-nav" id="nextMes" aria-label="Mes siguiente">›</button>
      </div>

      <div class="cal-dias-semana">
        <span>Lun</span><span>Mar</span><span>Mié</span><span>Jue</span><span>Vie</span><span>Sáb</span><span>Dom</span>
      </div>

      <div class="cal-grid" id="calGrid"></div>      

    </div>
  </main>
  
  <!-- FOOTER -->
  <footer class="footer">
    <div class="footer-brand">Europa</div>
    <p class="footer-copy">© 2026 Europa Peluquería & Estética. Todos los derechos reservados.</p>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    const MESES = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
const hoy = new Date(); hoy.setHours(0,0,0,0);
let vista = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
let fechaSeleccionada = null;

// Días ocupados demo (formato YYYY-MM-DD)
const diasOcupados = new Set([
  `${hoy.getFullYear()}-09-16`,
  `${hoy.getFullYear()}-09-23`
]);

// Helper: convierte Date → "YYYY-MM-DD"
function fechaISO(fecha) {
  const y = fecha.getFullYear();
  const m = String(fecha.getMonth() + 1).padStart(2, '0');
  const d = String(fecha.getDate()).padStart(2, '0');
  return `${y}-${m}-${d}`;
}

// Helper: nombre del mes para mostrar
function formatearFechaLarga(fecha) {
  return fecha.toLocaleDateString('es-GT', {
    weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
  });
}

const grid = document.getElementById('calGrid');
const titulo = document.getElementById('mesTitulo');

// Lunes cerrado (getDay() === 1)
const esCerrado = (d) => d.getDay() === 1;

function render() {
  titulo.textContent = MESES[vista.getMonth()] + ' ' + vista.getFullYear();
  grid.innerHTML = '';

  const primero = new Date(vista.getFullYear(), vista.getMonth(), 1);
  const offset = (primero.getDay() + 6) % 7;
  const diasMes = new Date(vista.getFullYear(), vista.getMonth() + 1, 0).getDate();

  // Celdas vacías antes del día 1
  for (let i = 0; i < offset; i++) {
    const v = document.createElement('span');
    v.className = 'cal-dia vacio';
    grid.appendChild(v);
  }

  // Días del mes
  for (let d = 1; d <= diasMes; d++) {
    const fecha = new Date(vista.getFullYear(), vista.getMonth(), d);
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'cal-dia';
    btn.textContent = d;
    btn.style.animationDelay = (d * 12) + 'ms';

    const pasado = fecha < hoy;
    if (fecha.getTime() === hoy.getTime()) btn.classList.add('hoy');

    const iso = fechaISO(fecha);
    const ocupado = diasOcupados.has(iso);

    if (pasado || esCerrado(fecha)) {
      btn.classList.add('deshabilitado');
      btn.disabled = true;
      btn.title = esCerrado(fecha) ? 'Cerrado los lunes' : 'Fecha pasada';
    } else if (ocupado) {
      btn.classList.add('ocupado');
      btn.title = 'Día lleno';
      btn.addEventListener('click', () => mostrarDiaLleno(fecha));
    } else {
      btn.title = 'Disponible';
      btn.addEventListener('click', () => seleccionarDia(fecha));
    }

    grid.appendChild(btn);
  }
}

document.getElementById('prevMes').addEventListener('click', () => {
  vista = new Date(vista.getFullYear(), vista.getMonth() - 1, 1);
  render();
});
document.getElementById('nextMes').addEventListener('click', () => {
  vista = new Date(vista.getFullYear(), vista.getMonth() + 1, 1);
  render();
});

// ====== CLICK EN DÍA DISPONIBLE ======
function seleccionarDia(fecha) {
  // Guardamos la fecha para que reservacita.php la lea
  localStorage.setItem('fechaSeleccionada', fechaISO(fecha));

  // Redirigimos
  window.location.href = 'reservarcita.php';
}

// ====== CLICK EN DÍA LLENO ======
function mostrarDiaLleno(fecha) {
  Swal.fire({
    icon: 'info',
    title: 'Día lleno',
    html: `
      <p style="margin-bottom:8px;">
        El <strong>${formatearFechaLarga(fecha)}</strong> ya no tiene horarios disponibles.
      </p>
      <p style="opacity:.8; font-size:.9rem;">
        Probá con otro día del calendario.
      </p>
    `,
    confirmButtonText: 'Entendido',
    confirmButtonColor: '#c9a96e',
    background: '#0f0f0f',
    color: '#f8f8f6',
    customClass: {
      popup: 'swal-europa',
      title: 'swal-europa-title'
    }
  });
}

// ====== INICIALIZAR ======
render();
  </script>
</body>
</html>