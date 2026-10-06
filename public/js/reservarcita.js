 // ====== CATÁLOGO DE SERVICIOS (precios sueltos) ======
const SERVICIOS = {
  'Corte de cabello caballero':   { precio: 60,  duracion: 30 },
  'Corte de cabello dama':        { precio: 80,  duracion: 40 },
  'Tallado de barba':             { precio: 50,  duracion: 20 },
  'Lavado de cabello':            { precio: 35,  duracion: 15 },
  'Depilación de cejas con navaja': { precio: 25, duracion: 10 },
  'Depilación de cejas con cera':   { precio: 35, duracion: 15 },

  // Servicios adicionales (por si luego se usan sueltos)
  'Corte y peinado':              { precio: 120, duracion: 45 },
  'Afeitado clásico':             { precio: 70,  duracion: 25 },
  'Diseño de barba':              { precio: 60,  duracion: 20 },
  'Aislado permanente':           { precio: 280, duracion: 90 },
  'Maquillaje profesional':       { precio: 250, duracion: 60 },
  'Peinado de gala':              { precio: 200, duracion: 45 },
  'Planchado':                    { precio: 130, duracion: 40 },
  'Cepillado':                    { precio: 70,  duracion: 20 },
  'Tratamiento capilar':          { precio: 180, duracion: 50 },
  'Mascarilla hidratante':        { precio: 120, duracion: 30 },
  'Depilación con cera':          { precio: 80,  duracion: 20 },
  'Manicure':                     { precio: 110, duracion: 40 },
  'Pedicure':                     { precio: 130, duracion: 45 },
  'Tinte completo':               { precio: 280, duracion: 90 },
  'Mechas / Highlights':          { precio: 350, duracion: 120 }
};

// ====== COMBOS (precio fijo ya con descuento) ======
const COMBOS = [
  // ---------- CABALLERO ----------
  {
    id: 'combo-cab-1',
    nombre: 'Combo Caballero 1',
    desc: 'Corte de cabello + bebida',
    icono: 'fa-user-tie',
    popular: false,
    incluyeBebida: true,
    precioCombo: 50,
    servicios: ['Corte de cabello caballero']
  },
  {
    id: 'combo-cab-2',
    nombre: 'Combo Caballero 2',
    desc: 'Corte + barba + cejas + lavado + bebida',
    icono: 'fa-user-tie',
    popular: true,
    incluyeBebida: true,
    precioCombo: 125,
    servicios: [
      'Corte de cabello caballero',
      'Tallado de barba',
      'Depilación de cejas con navaja',
      'Lavado de cabello'
    ]
  },
  {
    id: 'combo-cab-3',
    nombre: 'Combo Caballero 3',
    desc: 'Corte + tallado de barba + lavado + bebida',
    icono: 'fa-user-tie',
    popular: false,
    incluyeBebida: true,
    precioCombo: 100,
    servicios: [
      'Corte de cabello caballero',
      'Tallado de barba',
      'Lavado de cabello'
    ]
  },
  {
    id: 'combo-cab-4',
    nombre: 'Combo Caballero 4',
    desc: 'Corte + depilación de cejas + bebida',
    icono: 'fa-user-tie',
    popular: false,
    incluyeBebida: true,
    precioCombo: 75,
    servicios: [
      'Corte de cabello caballero',
      'Depilación de cejas con navaja'
    ]
  },

  // ---------- DAMA ----------
  {
    id: 'combo-dama-1',
    nombre: 'Combo Dama 1',
    desc: 'Corte de cabello para dama + bebida',
    icono: 'fa-crown',
    popular: false,
    incluyeBebida: true,
    precioCombo: 75,
    servicios: ['Corte de cabello dama']
  },
  {
    id: 'combo-dama-2',
    nombre: 'Combo Dama 2',
    desc: 'Corte + lavado de cabello + bebida',
    icono: 'fa-crown',
    popular: true,
    incluyeBebida: true,
    precioCombo: 100,
    servicios: [
      'Corte de cabello dama',
      'Lavado de cabello'
    ]
  }
];

// ====== ESTADO ======
let tabActual = 'combos';
let seleccionados = [];
let comboEditando = null;

// Horarios ocupados simulados
const horariosOcupados = {
  '1': ['09:00', '10:00', '14:00'],
  '2': ['08:00', '11:00', '15:00', '16:00'],
  '3': ['09:30', '12:00', '13:00'],
  '4': ['10:00', '14:30', '17:00']
};

// ====== HELPERS ======
function formatQ(n) {
  return 'Q ' + n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function calcularDuracionTotal() {
  let total = 0;
  seleccionados.forEach(item => {
    item.servicios.forEach(s => {
      if (SERVICIOS[s.nombre]) total += SERVICIOS[s.nombre].duracion;
    });
  });
  return total;
}

// ====== RENDER COMBOS ======
function renderCombos() {
  const grid = document.getElementById('gridCombos');
  let html = '';

  COMBOS.forEach(c => {
    // Precio original = suma de servicios sueltos
    const precioOriginal = c.servicios.reduce((sum, s) => sum + (SERVICIOS[s]?.precio || 0), 0);
    const precioFinal = c.precioCombo;
    const ahorro = Math.max(0, precioOriginal - precioFinal);
    const duracion = c.servicios.reduce((sum, s) => sum + (SERVICIOS[s]?.duracion || 0), 0);
    const yaSeleccionado = seleccionados.some(s => s.id === c.id);

    html += `
      <div class="combo-card ${yaSeleccionado ? 'selected' : ''}" onclick="abrirModalCombo('${c.id}')">
        ${c.popular ? '<span class="badge-popular">Popular</span>' : ''}
        <div class="icono-combo"><i class="fas ${c.icono}"></i></div>
        <h3>${c.nombre}</h3>
        <p class="desc">${c.desc}</p>
        <div class="servicios-incluidos">
          ${c.servicios.map(s => `
            <div class="servicio-linea">
              <i class="fas fa-circle"></i>
              <span>${s}</span>
            </div>
          `).join('')}
          ${c.incluyeBebida ? `
            <div class="servicio-linea bebida">
              <i class="fas fa-coffee"></i>
              <span>Bebida incluida</span>
            </div>
          ` : ''}
        </div>
        <div class="footer-combo">
          <div class="precio-combo">
            <span class="precio">${formatQ(precioFinal)}</span>
            ${ahorro > 0 ? `<span class="precio-original">${formatQ(precioOriginal)}</span>` : ''}
            ${ahorro > 0 ? `<span class="ahorro">Ahorras ${formatQ(ahorro)}</span>` : ''}
          </div>
          <div class="duracion"><i class="fas fa-clock"></i> ${duracion} min</div>
        </div>
      </div>
    `;
  });

  grid.innerHTML = html;
}

// ====== RENDER SERVICIOS INDIVIDUALES ======
function renderServiciosIndividuales() {
  const grid = document.getElementById('gridServicios');
  let html = '';

  Object.entries(SERVICIOS).forEach(([nombre, data]) => {
    const seleccionado = seleccionados.some(s =>
      s.tipo === 'individual' && s.servicios.some(serv => serv.nombre === nombre)
    );

    html += `
      <div class="servicio-card ${seleccionado ? 'selected' : ''}" onclick="toggleServicioIndividual('${nombre}')">
        <div class="checkbox"></div>
        <div class="info-servicio">
          <span class="nombre">${nombre}</span>
          <div class="meta">
            <span class="duracion"><i class="fas fa-clock"></i> ${data.duracion} min</span>
          </div>
        </div>
        <span class="precio-servicio">${formatQ(data.precio)}</span>
      </div>
    `;
  });

  grid.innerHTML = html;
}

// ====== TOGGLE SERVICIO INDIVIDUAL ======
function toggleServicioIndividual(nombre) {
  const data = SERVICIOS[nombre];

  const idx = seleccionados.findIndex(s =>
    s.tipo === 'individual' && s.servicios.some(serv => serv.nombre === nombre)
  );

  if (idx !== -1) {
    seleccionados.splice(idx, 1);
    mostrarToast(`🗑️ ${nombre} eliminado`);
  } else {
    seleccionados.push({
      tipo: 'individual',
      id: 'ind-' + Date.now() + '-' + Math.random().toString(36).substr(2, 5),
      nombre: nombre,
      servicios: [{ nombre, precio: data.precio, duracion: data.duracion }],
      precioOriginal: data.precio,
      precioFinal: data.precio,
      descuento: 0
    });
    mostrarToast(`✅ ${nombre} agregado`);
  }

  renderTodo();
}

// ====== MODAL DE COMBO (solo detalle) ======
function abrirModalCombo(comboId) {
  const combo = COMBOS.find(c => c.id === comboId);
  if (!combo) return;

  comboEditando = combo;

  document.getElementById('modalComboNombre').textContent = combo.nombre;
  document.getElementById('modalComboDesc').textContent = combo.desc;
  document.getElementById('modalComboIcono').innerHTML = `<i class="fas ${combo.icono}"></i>`;

  renderModalServicios();

  // Precio fijo del combo
  document.getElementById('modalComboTotal').textContent = formatQ(combo.precioCombo);

  // Bloque bebida
  const bloqueBebida = document.getElementById('modalBebida');
  bloqueBebida.style.display = combo.incluyeBebida ? 'flex' : 'none';

  document.getElementById('modalCombo').classList.add('active');
  document.body.style.overflow = 'hidden';
}

function cerrarModalCombo() {
  document.getElementById('modalCombo').classList.remove('active');
  document.body.style.overflow = '';
  comboEditando = null;
}

function renderModalServicios() {
  const container = document.getElementById('modalServiciosCombo');
  if (!comboEditando) return;

  let html = '';
  comboEditando.servicios.forEach(s => {
    const data = SERVICIOS[s];
    if (!data) return;

    html += `
      <div class="servicio-combo-item activo">
        <div class="info">
          <span class="nombre">${s}</span>
          <div class="meta">
            <span><i class="fas fa-clock" style="color:#b8860b; font-size:9px;"></i> ${data.duracion} min</span>
          </div>
        </div>
        <span class="precio">${formatQ(data.precio)}</span>
      </div>
    `;
  });

  container.innerHTML = html;
}

function confirmarCombo() {
  if (!comboEditando) return;

  const combo = comboEditando;

  const precioOriginal = combo.servicios.reduce((sum, s) => sum + (SERVICIOS[s]?.precio || 0), 0);
  const precioFinal = combo.precioCombo;
  const descuento = Math.max(0, precioOriginal - precioFinal);

  // Eliminar si ya estaba
  seleccionados = seleccionados.filter(s => s.id !== combo.id);

  seleccionados.push({
    tipo: 'combo',
    id: combo.id,
    nombre: combo.nombre,
    servicios: combo.servicios.map(s => ({
      nombre: s,
      precio: SERVICIOS[s]?.precio || 0,
      duracion: SERVICIOS[s]?.duracion || 0
    })),
    incluyeBebida: combo.incluyeBebida,
    precioOriginal,
    precioFinal,
    descuento
  });

  mostrarToast(`✅ ${combo.nombre} agregado`);
  cerrarModalCombo();
  renderTodo();
}

// ====== TABS ======
function cambiarTab(tab) {
  tabActual = tab;
  document.querySelectorAll('.tab').forEach(t => {
    t.classList.toggle('active', t.dataset.tab === tab);
  });
  document.getElementById('tabCombos').style.display = tab === 'combos' ? 'block' : 'none';
  document.getElementById('tabIndividuales').style.display = tab === 'individuales' ? 'block' : 'none';
}

// ====== RENDER RESUMEN ======
function renderResumen() {
  const vacio = document.getElementById('resumenVacio');
  const lista = document.getElementById('resumenLista');
  const totales = document.getElementById('resumenTotales');

  if (seleccionados.length === 0) {
    vacio.style.display = 'block';
    lista.style.display = 'none';
    totales.style.display = 'none';
    document.getElementById('btnReservar').disabled = true;
    return;
  }

  vacio.style.display = 'none';
  lista.style.display = 'flex';
  totales.style.display = 'block';

  let html = '';
  seleccionados.forEach((item, idx) => {
    const esCombo = item.tipo === 'combo';
    const detalle = esCombo
      ? `${item.servicios.length} servicio(s)${item.incluyeBebida ? ' + bebida' : ''}`
      : `${item.servicios[0].duracion} min`;

    html += `
      <div class="resumen-item">
        <div class="info">
          <span class="nombre">${esCombo ? '🎁 ' : ''}${item.nombre}</span>
          <span class="detalle">${detalle}</span>
        </div>
        <div style="display:flex; align-items:center; gap:6px;">
          <span class="precio">${formatQ(item.precioFinal)}</span>
          <button class="btn-quitar" onclick="quitarItem(${idx})" title="Quitar">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>
    `;
  });

  lista.innerHTML = html;

  // Totales
  const totalServicios = seleccionados.reduce((sum, item) => sum + item.precioOriginal, 0);
  const totalFinal = seleccionados.reduce((sum, item) => sum + item.precioFinal, 0);
  const totalDescuento = totalServicios - totalFinal;

  document.getElementById('resumenServicios').textContent = formatQ(totalServicios);
  document.getElementById('resumenDescuento').textContent = '- ' + formatQ(totalDescuento);
  document.getElementById('resumenTotal').textContent = formatQ(totalFinal);

  document.getElementById('btnReservar').disabled = false;
}

function quitarItem(idx) {
  const item = seleccionados[idx];
  seleccionados.splice(idx, 1);
  mostrarToast(`🗑️ ${item.nombre} eliminado`);
  renderTodo();
}

// ====== RENDER TODO ======
function renderTodo() {
  renderCombos();
  renderServiciosIndividuales();
  renderResumen();
  document.getElementById('badgeDuracion').textContent = calcularDuracionTotal() + ' min';
}

// ====== HORARIOS ======
function cargarHorarios() {
  const fecha = document.getElementById('fechaCita').value;
  const barbero = document.getElementById('barberoSelect').value;
  const grid = document.getElementById('horariosGrid');
  const errorFecha = document.getElementById('errorFecha');
  const errorBarbero = document.getElementById('errorBarbero');

  document.getElementById('groupFecha').classList.remove('error');
  document.getElementById('groupBarbero').classList.remove('error');
  errorFecha.classList.remove('show');
  errorBarbero.classList.remove('show');

  if (!fecha || !barbero) {
    grid.innerHTML = `<div style="grid-column: 1/-1; text-align:center; padding:20px; color:#aaa; font-size:13px;">
      Selecciona fecha y profesional para ver horarios disponibles
    </div>`;
    return;
  }

  const horas = [];
  for (let h = 8; h <= 19; h++) {
    horas.push(String(h).padStart(2, '0') + ':00');
    if (h < 19) horas.push(String(h).padStart(2, '0') + ':30');
  }

  const ocupados = horariosOcupados[barbero] || [];

  let html = '';
  horas.forEach(hora => {
    const ocupado = ocupados.includes(hora);
    html += `
      <button class="horario-btn"
              ${ocupado ? 'disabled' : ''}
              onclick="seleccionarHorario('${hora}', this)">
        ${hora}
      </button>
    `;
  });
  grid.innerHTML = html;
}

let horarioSeleccionado = null;
function seleccionarHorario(hora, btn) {
  horarioSeleccionado = hora;
  document.querySelectorAll('.horario-btn').forEach(b => b.classList.remove('selected'));
  btn.classList.add('selected');
  document.getElementById('groupHorario').classList.remove('error');
  document.getElementById('errorHorario').classList.remove('show');
}

// ====== PROCESAR RESERVA ======
function procesarReserva() {
  abrirModalCliente();
}

// ====== TOAST ======
function mostrarToast(msg, esError = false) {
  const toast = document.getElementById('toast');
  const msgEl = document.getElementById('toastMsg');
  msgEl.textContent = msg;
  toast.classList.toggle('error', esError);
  toast.classList.add('show');
  clearTimeout(toast._timeout);
  toast._timeout = setTimeout(() => {
    toast.classList.remove('show');
  }, 3000);
}

// ====== MODAL: DATOS DEL CLIENTE ======
let reservaPendiente = null; // guarda { items, fecha, barbero, hora }

function abrirModalCliente() {
  // Validar antes de abrir
  if (seleccionados.length === 0) {
    mostrarToast('❌ Debés elegir al menos un servicio', true);
    return;
  }
  const fecha = document.getElementById('fechaCita').value;
  const barbero = document.getElementById('barberoSelect').value;

  if (!fecha) {
    document.getElementById('groupFecha').classList.add('error');
    document.getElementById('errorFecha').classList.add('show');
    mostrarToast('❌ Seleccioná una fecha', true);
    return;
  }
  if (!barbero) {
    document.getElementById('groupBarbero').classList.add('error');
    document.getElementById('errorBarbero').classList.add('show');
    mostrarToast('❌ Seleccioná un profesional', true);
    return;
  }
  if (!horarioSeleccionado) {
    document.getElementById('groupHorario').classList.add('error');
    document.getElementById('errorHorario').classList.add('show');
    mostrarToast('❌ Seleccioná un horario', true);
    return;
  }

  // Guardamos los datos de la reserva en curso
  reservaPendiente = {
    items: [...seleccionados],
    fecha,
    barberoId: barbero,
    barberoNombre: document.getElementById('barberoSelect').options[
      document.getElementById('barberoSelect').selectedIndex
    ].text,
    hora: horarioSeleccionado
  };

  // Limpiar errores previos
  limpiarErroresCliente();

  document.getElementById('modalCliente').classList.add('active');
  document.body.style.overflow = 'hidden';

  // Foco automático al primer campo
  setTimeout(() => document.getElementById('cliNombre').focus(), 200);
}

function cerrarModalCliente() {
  document.getElementById('modalCliente').classList.remove('active');
  document.body.style.overflow = '';
}

function limpiarErroresCliente() {
  ['cliNombre', 'cliApellido', 'cliTelefono', 'cliCorreo'].forEach(id => {
    const field = document.getElementById(id).closest('.form-field');
    if (field) field.classList.remove('error');
  });
}

// Teléfono: solo dígitos, máximo 8
document.getElementById('cliTelefono').addEventListener('input', function (e) {
  this.value = this.value.replace(/\D/g, '').slice(0, 8);
});

function validarDatosCliente() {
  let valido = true;

  const nombre   = document.getElementById('cliNombre').value.trim();
  const apellido = document.getElementById('cliApellido').value.trim();
  const telefono = document.getElementById('cliTelefono').value.trim();
  const correo   = document.getElementById('cliCorreo').value.trim();

  limpiarErroresCliente();

  if (nombre.length < 2) {
    document.getElementById('cliNombre').closest('.form-field').classList.add('error');
    valido = false;
  }
  if (apellido.length < 2) {
    document.getElementById('cliApellido').closest('.form-field').classList.add('error');
    valido = false;
  }
  if (!/^\d{8}$/.test(telefono)) {
    document.getElementById('cliTelefono').closest('.form-field').classList.add('error');
    valido = false;
  }
  // Correo opcional: solo validar si hay algo
  if (correo !== '' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo)) {
    document.getElementById('cliCorreo').closest('.form-field').classList.add('error');
    valido = false;
  }

  return valido ? { nombre, apellido, telefono, correo } : null;
}

function confirmarDatosCliente() {
  const datos = validarDatosCliente();
  if (!datos) {
    mostrarToast('❌ Revisá los campos marcados', true);
    return;
  }

  // Armamos el objeto final de la reserva
  const items = reservaPendiente.items;
  const total = items.reduce((sum, it) => sum + it.precioFinal, 0);
  const servicios = items.map(it => ({
    nombre: it.nombre,
    precio: it.precioFinal
  }));

  const reservaFinal = {
    cliente: datos,
    servicios,
    total,
    fecha: reservaPendiente.fecha,
    hora: reservaPendiente.hora,
    barbero: reservaPendiente.barberoNombre
  };

  console.log('Reserva final:', reservaFinal);

    // Cerramos el modal del cliente
  cerrarModalCliente();

  // Pequeño delay para que no se solapen los modales
  setTimeout(() => {
    abrirConfirmacion(reservaFinal);
  }, 180);
}

// ====== SWEETALERT: CONFIRMACIÓN DE RESERVA ======
function abrirConfirmacion(reserva) {
  // Formatear fecha en texto largo (ej: "miércoles, 18 de septiembre de 2026")
  const fechaTxt = formatearFechaLarga(reserva.fecha);

  // Armar filas de servicios (solo títulos para combos, nombre completo para individuales)
  const filasServicios = reserva.servicios.map(s => `
    <li style="
      padding:6px 0;
      display:flex;
      justify-content:space-between;
      border-bottom:1px solid #2a2a2a;
      font-size:14px;
      gap:14px;
    ">
      <span style="text-align:left;">• ${s.nombre}</span>
      <span style="white-space:nowrap;">Q${s.precio.toFixed(2)}</span>
    </li>
  `).join('');

  Swal.fire({
    icon: 'success',
    title: '¡Tu cita ha sido reservada!',

    html: `
      <p style="margin-bottom:14px">
        Gracias <strong>${reserva.cliente.nombre} ${reserva.cliente.apellido}</strong>.
      </p>

      <div style="margin-bottom:14px; text-align:left;">
        <p style="margin-bottom:8px;"><strong>Servicios:</strong></p>
        <ul style="list-style:none; padding:0; margin:0;">
          ${filasServicios}
        </ul>
        <p style="
          margin-top:10px;
          padding-top:10px;
          border-top:2px solid #c9a96e;
          display:flex;
          justify-content:space-between;
          font-weight:bold;
          font-size:16px;
        ">
          <span>Total</span>
          <span>Q${reserva.total.toFixed(2)}</span>
        </p>
      </div>

      <p style="margin-bottom:12px;">
        Fecha: <strong>${fechaTxt}</strong><br>
        Hora: <strong>${reserva.hora}</strong>
      </p>

            <p style="margin-bottom:12px;">
        En breve le llamaremos al
        <strong>+502 ${reserva.cliente.telefono}</strong>
        para confirmar su cita.
      </p>

      <button
        type="button"
        id="btnDescargarTicket"
        style="
          margin-top:6px;
          width:100%;
          padding:11px 15px;
          border:none;
          border-radius:100px;
          background:#c9a96e;
          color:#111;
          font-weight:600;
          cursor:pointer;
          font-size:14px;
          font-family:'Inter', sans-serif;
          transition:transform .2s, box-shadow .2s;
        "
      >
        🎟️ Descargar ticket
      </button>
    `,

    confirmButtonText: 'Perfecto',
    confirmButtonColor: '#c9a96e',

    background: '#0f0f0f',
    color: '#f8f8f6',

    customClass: {
      popup: 'swal-europa',
      title: 'swal-europa-title'
    },

        didOpen: () => {
      const btn = document.getElementById('btnDescargarTicket');
      if (!btn) return;

      btn.addEventListener('click', async () => {
        btn.disabled = true;
        btn.textContent = '⏳ Generando ticket...';

        try {
          // Adaptamos los datos al formato que espera generarTicketPDF()
          const datosPDF = {
            nombre:    reserva.cliente.nombre,
            apellido:  reserva.cliente.apellido,
            telefono:  reserva.cliente.telefono,
            correo:    reserva.cliente.correo,
            servicios: reserva.servicios, // ya viene como [{ nombre, precio }]
            total:     reserva.total,
            hora:      reserva.hora,
            barbero:   reserva.barbero
          };

          const fechaTxt = formatearFechaLarga(reserva.fecha);

          await generarTicketPDF(datosPDF, fechaTxt);

          btn.textContent = '🎟️ Descargar ticket';
          btn.disabled = false;
        } catch (err) {
          console.error('Error al generar ticket:', err);
          btn.textContent = '❌ Error, reintentar';
          btn.disabled = false;
        }
      });
    },

    willClose: () => {
      // Resetear toda la reserva al cerrar
      resetReserva();
    }
  });
}

// ====== HELPERS ======
function formatearFechaLarga(isoFecha) {
  if (!isoFecha) return '';
  const meses = [
    'enero','febrero','marzo','abril','mayo','junio',
    'julio','agosto','septiembre','octubre','noviembre','diciembre'
  ];
  const dias = [
    'domingo','lunes','martes','miércoles',
    'jueves','viernes','sábado'
  ];
  const [y, m, d] = isoFecha.split('-').map(Number);
  const fecha = new Date(y, m - 1, d);
  return `${dias[fecha.getDay()]}, ${d} de ${meses[m - 1]} de ${y}`;
}

function resetReserva() {
  seleccionados = [];
  horarioSeleccionado = null;
  reservaPendiente = null;

  document.getElementById('fechaCita').value = '';
  document.getElementById('barberoSelect').value = '';
  document.getElementById('horariosGrid').innerHTML = `
    <div style="grid-column: 1/-1; text-align:center; padding:20px; color:#aaa; font-size:13px;">
      Selecciona fecha y profesional para ver horarios disponibles
    </div>
  `;
  document.getElementById('formCliente').reset();
  limpiarErroresCliente();
  renderTodo();
}

async function generarTicketPDF(datos, fechaCita) {

    // Obtener jsPDF
    const { jsPDF } = window.jspdf;

    // -----------------------------------------
    // CONFIGURACIÓN DEL TICKET
    // -----------------------------------------

    const ancho = 80;
    const alto = 190; // Aumentado para dar espacio a la lista de servicios

    const pdf = new jsPDF({
        orientation: 'portrait',
        unit: 'mm',
        format: [ancho, alto]
    });

    // Márgenes
    const margen = 6;
    const centro = ancho / 2;

    // -----------------------------------------
    // FECHA Y HORA ACTUAL
    // -----------------------------------------

    const ahora = new Date();

    const fechaGeneracion = ahora.toLocaleDateString('es-GT', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });

    const horaGeneracion = ahora.toLocaleTimeString('es-GT', {
        hour: '2-digit',
        minute: '2-digit'
    });

    // -----------------------------------------
    // LOGO
    // -----------------------------------------

    const logo = new Image();

    logo.src = 'img/logo.png';

    await new Promise((resolve, reject) => {

        logo.onload = resolve;
        logo.onerror = reject;

    });

    // Tamaño del logo
    const logoAncho = 32;
    const logoAlto = 32;

    pdf.addImage(
        logo,
        'PNG',
        centro - (logoAncho / 2),
        5,
        logoAncho,
        logoAlto
    );

    // -----------------------------------------
    // ENCABEZADO
    // -----------------------------------------

    let y = 43;

    pdf.setFont('helvetica', 'bold');
    pdf.setFontSize(13);

    pdf.text(
        'ESTETICA EUROPA',
        centro,
        y,
        { align: 'center' }
    );

    y += 6;

    pdf.setFont('helvetica', 'normal');
    pdf.setFontSize(8);

    pdf.text(
        'TICKET DE RESERVACION',
        centro,
        y,
        { align: 'center' }
    );

    y += 5;

    pdf.text(
        `${fechaGeneracion} - ${horaGeneracion}`,
        centro,
        y,
        { align: 'center' }
    );

    // -----------------------------------------
    // SEPARADOR
    // -----------------------------------------

    y += 5;

    pdf.setLineWidth(0.3);

    pdf.line(
        margen,
        y,
        ancho - margen,
        y
    );

    // -----------------------------------------
    // DATOS DEL CLIENTE
    // -----------------------------------------

    y += 7;

    pdf.setFont('helvetica', 'bold');
    pdf.setFontSize(9);

    pdf.text(
        'DATOS DEL CLIENTE',
        margen,
        y
    );

    y += 6;

    pdf.setFont('helvetica', 'normal');
    pdf.setFontSize(8.5);

    pdf.text(
        `Nombre: ${datos.nombre} ${datos.apellido}`,
        margen,
        y
    );

    y += 5;

    pdf.text(
        `Telefono: ${datos.telefono}`,
        margen,
        y
    );
  
    // -----------------------------------------
    // SEPARADOR
    // -----------------------------------------

    y += 5;

    pdf.line(
        margen,
        y,
        ancho - margen,
        y
    );

    // -----------------------------------------
    // DATOS DE LA CITA
    // -----------------------------------------

    y += 7;

    pdf.setFont('helvetica', 'bold');
    pdf.setFontSize(9);

    pdf.text(
        'DETALLE DE LA CITA',
        margen,
        y
    );

    y += 6;

    pdf.setFont('helvetica', 'normal');
    pdf.setFontSize(8.5);

    pdf.text(
        `Fecha: ${fechaCita}`,
        margen,
        y
    );

    y += 5;

    pdf.text(
        `Hora: ${datos.hora}`,
        margen,
        y
    );

    y += 5;

    pdf.text(
        `Profesional: ${datos.barbero}`,
        margen,
        y
    );   

    // -----------------------------------------
    // SEPARADOR
    // -----------------------------------------

    y += 6;

    pdf.line(
        margen,
        y,
        ancho - margen,
        y
    );

    // -----------------------------------------
    // SERVICIOS SELECCIONADOS
    // -----------------------------------------

    y += 7;

    pdf.setFont('helvetica', 'bold');
    pdf.setFontSize(9);

    pdf.text(
        'SERVICIOS SELECCIONADOS',
        margen,
        y
    );

    y += 6;

    pdf.setFont('helvetica', 'normal');
    pdf.setFontSize(8.5);

    // Mostrar cada servicio con su precio
    datos.servicios.forEach(servicio => {

        // Ajustar el texto para alinear el precio a la derecha
        const nombreServicio = `• ${servicio.nombre}`;
        const precioServicio = `Q${servicio.precio.toFixed(2)}`;

        // Calcular posiciones para alinear a derecha
        const anchoNombre = pdf.getStringUnitWidth(nombreServicio) * pdf.getFontSize() / pdf.internal.scaleFactor;
        const anchoTotal = ancho - (margen * 2);
        const espacioPrecio = anchoTotal - anchoNombre - 2; // Espacio entre nombre y precio

        pdf.text(
            nombreServicio,
            margen,
            y
        );

        pdf.text(
            precioServicio,
            ancho - margen,
            y,
            { align: 'right' }
        );

        y += 5;

    });

    // -----------------------------------------
    // TOTAL
    // -----------------------------------------

    // Línea separadora antes del total
    pdf.line(
        margen,
        y,
        ancho - margen,
        y
    );

    y += 5;

    pdf.setFont('helvetica', 'bold');
    pdf.setFontSize(9);

    // Mostrar total alineado a la derecha
    const textoTotal = 'TOTAL:';
    const valorTotal = `Q${datos.total.toFixed(2)}`;

    pdf.text(
        textoTotal,
        margen,
        y
    );

    pdf.text(
        valorTotal,
        ancho - margen,
        y,
        { align: 'right' }
    );

    y += 7;

    // -----------------------------------------
    // SEPARADOR
    // -----------------------------------------

    pdf.line(
        margen,
        y,
        ancho - margen,
        y
    );

    // -----------------------------------------
    // MENSAJE FINAL
    // -----------------------------------------

    y += 8;

    pdf.setFont('helvetica', 'bold');
    pdf.setFontSize(9);

    pdf.text(
        '¡Gracias por reservar con nosotros!',
        centro,
        y,
        { align: 'center' }
    );

    y += 6;

    pdf.setFont('helvetica', 'normal');
    pdf.setFontSize(7.5);

    pdf.text(
        'Presenta este ticket al llegar a la estetica.',
        centro,
        y,
        { align: 'center' }
    );

    y += 5;

    pdf.text(
        'Te esperamos.',
        centro,
        y,
        { align: 'center' }
    );

    // -----------------------------------------
// QR DEMOSTRATIVO
// -----------------------------------------

// Ajustar espacio antes del QR
y += 4;

// QR más pequeño para que quepa
const qrSize = 18; // Reducido de 25 a 18mm

try {
    // Usar API pública para generar QR
    const qrData = `CITA-${Date.now()}`;
    const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=${encodeURIComponent(qrData)}`;
    
    const qrImg = new Image();
    qrImg.crossOrigin = 'anonymous';
    qrImg.src = qrUrl;
    
    await new Promise((resolve) => {
        qrImg.onload = () => {
            pdf.addImage(
                qrImg,
                'PNG',
                centro - (qrSize / 2),
                y,
                qrSize,
                qrSize
            );
            resolve();
        };
        qrImg.onerror = () => {
            dibujarQRFallback(pdf, centro, y, qrSize);
            resolve();
        };
        setTimeout(resolve, 3000);
    });
    
} catch (error) {
    dibujarQRFallback(pdf, centro, y, qrSize);
}

y += qrSize + 5; // Espacio después del QR

// Función de fallback
function dibujarQRFallback(pdf, centro, y, size) {
    pdf.setDrawColor(0);
    pdf.setFillColor(240, 240, 240);
    pdf.setLineWidth(0.3);
    pdf.rect(centro - (size/2), y, size, size, 'FD');
    
    // Patrón simple
    pdf.setFillColor(0);
    const celdas = [
        [1,1,1,1,0,1,1,1,1],
        [1,0,0,0,0,0,0,0,1],
        [1,0,1,1,0,1,1,0,1],
        [1,0,1,0,0,0,1,0,1],
        [0,0,0,0,0,0,0,0,0],
        [1,0,0,0,1,0,0,0,1],
        [1,0,0,0,0,0,0,0,1],
        [1,0,0,0,0,0,0,0,1],
        [1,1,1,1,0,1,1,1,1]
    ];
    
    const cellSize = size / 11;
    const offset = size / 11;
    
    celdas.forEach((fila, i) => {
        fila.forEach((valor, j) => {
            if (valor === 1) {
                pdf.rect(
                    (centro - size/2) + (j * cellSize) + offset/2,
                    y + (i * cellSize) + offset/2,
                    cellSize - 0.3,
                    cellSize - 0.3,
                    'F'
                );
            }
        });
    });
    
    pdf.setFont('helvetica', 'normal');
    pdf.setFontSize(4);
    pdf.setTextColor(100);
    pdf.text(
        'QR',
        centro,
        y + (size/2) + 1,
        { align: 'center' }
    );
}    

    // -----------------------------------------
    // DESCARGAR PDF
    // -----------------------------------------

    const nombreArchivo =
        `Ticket-Cita-${datos.nombre}-${datos.apellido}.pdf`
            .replace(/\s+/g, '-');

    pdf.save(nombreArchivo);
}

// ====== LEER FECHA DESDE citas.html ======
const fechaGuardada = localStorage.getItem('fechaSeleccionada');
if (fechaGuardada) {
  document.getElementById('fechaCita').value = fechaGuardada;
  localStorage.removeItem('fechaSeleccionada');
  // Por si querés que se carguen los horarios de una
  if (typeof cargarHorarios === 'function') cargarHorarios();
}

// ====== EVENTOS ======
document.getElementById('modalCombo').addEventListener('click', function(e) {
  if (e.target === this) cerrarModalCombo();
});

document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    if (document.getElementById('modalCombo').classList.contains('active')) {
      cerrarModalCombo();
    }
  }
});

// Cerrar modal cliente clickeando el overlay
document.getElementById('modalCliente').addEventListener('click', function (e) {
  if (e.target === this) cerrarModalCliente();
});

// Cerrar con Escape
document.addEventListener('keydown', function (e) {
  if (e.key === 'Escape') {
    if (document.getElementById('modalCliente').classList.contains('active')) {
      cerrarModalCliente();
    }
  }
});

// ====== INICIALIZAR ======
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', renderTodo);
} else {
  renderTodo();
}