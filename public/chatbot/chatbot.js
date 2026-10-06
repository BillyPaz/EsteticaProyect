/* =====================================================
   ASISTENTE EUROPA — LÓGICA DEL CLIENTE (Paso 2.1)
   Respuestas simuladas en JS. Sin conexión a BD.
   ===================================================== */

(function () {
  'use strict';

  // =====================================================
  // CONFIGURACIÓN
  // =====================================================
  const STORAGE_KEY = 'europa_chat_history';
  const MAX_HISTORY = 30;
  const DELAY_MIN = 600;
  const DELAY_MAX = 1200;

  // =====================================================
  // REFERENCIAS DOM
  // =====================================================
  const widget       = document.getElementById('chatbotWidget');
  const bubble       = document.getElementById('chatbotBubble');
  const bubbleClose  = document.getElementById('chatbotBubbleClose');
  const windowEl     = document.getElementById('chatbotWindow');
  const closeBtn     = document.getElementById('chatbotClose');
  const bodyEl       = document.getElementById('chatbotBody');
  const form         = document.getElementById('chatbotForm');
  const input        = document.getElementById('chatbotInput');

  if (!widget || !bubble || !windowEl || !bodyEl || !form || !input) {
    console.warn('[Chatbot] Faltan elementos del DOM.');
    return;
  }

  // =====================================================
  // ÍCONOS SVG (outline, sin emojis)
  // =====================================================
  const ICONOS = {
    horarios: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>',
    servicios: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><line x1="20" y1="4" x2="8.12" y2="15.88"/><line x1="14.47" y1="14.48" x2="20" y2="20"/><line x1="8.12" y1="8.12" x2="12" y2="12"/></svg>',
    productos: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M9 3h6l1 5H8z"/><path d="M8 8h8v11a2 2 0 0 1-2 2h-4a2 2 0 0 1-2-2z"/><path d="M10 12h4"/></svg>',
    ubicacion: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-7-5.5-7-11a7 7 0 0 1 14 0c0 5.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>',
    reservar: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18"/><path d="M8 3v4"/><path d="M16 3v4"/><path d="M9 15l2 2 4-4"/></svg>'
  };

  // =====================================================
  // ESTADO
  // =====================================================
  let history = [];
  let isOpen = false;
  let isTyping = false;
  let lastIntent = null;
  let chatState = null; 
  let cerradoPorDespedida = false;

  // =====================================================
  // UTILIDADES
  // =====================================================
  function ahora() {
    return new Date().toLocaleTimeString('es-GT', { hour: '2-digit', minute: '2-digit' });
  }

  function escapeHTML(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }

  function randomDelay() {
    return Math.floor(Math.random() * (DELAY_MAX - DELAY_MIN + 1)) + DELAY_MIN;
  }

  function scrollToBottom() {
    bodyEl.scrollTop = bodyEl.scrollHeight;
  }

  function guardarHistorial() {
  try {
    sessionStorage.setItem(STORAGE_KEY, JSON.stringify(history.slice(-MAX_HISTORY)));
    sessionStorage.setItem(STORAGE_KEY + '_state', chatState || 'null');
  } catch (e) { /* ignore */ }
}

  function cargarHistorial() {
    try {
      const raw = sessionStorage.getItem(STORAGE_KEY);
      if (!raw) return [];
      const parsed = JSON.parse(raw);
      return Array.isArray(parsed) ? parsed : [];
    } catch (e) {
      return [];
    }
  }

  // =====================================================
  // RENDER DE MENSAJES
  // =====================================================
  function agregarMensaje(rol, texto, opciones = {}) {
    const esBot = rol === 'bot';
    const hora = opciones.time || ahora();

    const msg = document.createElement('div');
    msg.className = 'chatbot-msg ' + (esBot ? 'chatbot-msg-bot' : 'chatbot-msg-user');

    const avatar = document.createElement('div');
    avatar.className = 'chatbot-msg-avatar';
    avatar.textContent = esBot ? 'E' : 'Tú';

    const content = document.createElement('div');
    content.className = 'chatbot-msg-content';

    const parrafos = String(texto).split('\n').map(linea =>
      linea.trim() === '' ? '<br>' : '<p>' + escapeHTML(linea) + '</p>'
    ).join('');
    content.innerHTML = parrafos;

    const time = document.createElement('span');
    time.className = 'chatbot-msg-time';
    time.textContent = hora;

    content.appendChild(time);
    msg.appendChild(avatar);
    msg.appendChild(content);
    bodyEl.appendChild(msg);

    scrollToBottom();
    return msg;
  }

  function mostrarEscribiendo() {
    const msg = document.createElement('div');
    msg.className = 'chatbot-msg chatbot-msg-bot chatbot-typing';
    msg.id = 'chatbotTypingIndicator';

    const avatar = document.createElement('div');
    avatar.className = 'chatbot-msg-avatar';
    avatar.textContent = 'E';

    const content = document.createElement('div');
    content.className = 'chatbot-msg-content';
    content.innerHTML = `
      <span class="chatbot-typing-dot"></span>
      <span class="chatbot-typing-dot"></span>
      <span class="chatbot-typing-dot"></span>
    `;

    msg.appendChild(avatar);
    msg.appendChild(content);
    bodyEl.appendChild(msg);
    scrollToBottom();
  }

  function ocultarEscribiendo() {
    const el = document.getElementById('chatbotTypingIndicator');
    if (el) el.remove();
  }

  // =====================================================
  // BOTONES RÁPIDOS (inyectados dinámicamente)
  // =====================================================
  function eliminarBotonesRapidos() {
    const existentes = bodyEl.querySelector('.chatbot-quick-actions');
    if (existentes) existentes.remove();
  }

  function mostrarBotonesRapidos() {
    eliminarBotonesRapidos();

    const cont = document.createElement('div');
    cont.className = 'chatbot-quick-actions';

    const botones = [
      { accion: 'horarios',  label: 'Horarios',              icono: ICONOS.horarios },
      { accion: 'servicios', label: 'Servicios',             icono: ICONOS.servicios },
      { accion: 'productos', label: 'Productos',             icono: ICONOS.productos },
      { accion: 'ubicacion', label: 'Ubicación y contacto',  icono: ICONOS.ubicacion },
      { accion: 'reservar',  label: 'Cómo reservar cita',    icono: ICONOS.reservar }
    ];

    botones.forEach(b => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'chatbot-quick-btn';
      btn.dataset.action = b.accion;
      btn.innerHTML = b.icono + '<span>' + escapeHTML(b.label) + '</span>';
      cont.appendChild(btn);
    });

    bodyEl.appendChild(cont);
    scrollToBottom();
  }

  function manejarClickBotonRapido(e) {
    const btn = e.target.closest('.chatbot-quick-btn');
    if (!btn) return;
    const etiqueta = btn.querySelector('span') ? btn.querySelector('span').textContent : 'Consulta';
    enviarMensaje(etiqueta);
  }

  // =====================================================
// BOTÓN "FINALIZAR CONVERSACIÓN"
// =====================================================
function eliminarBotonFinalizar() {
  const existente = bodyEl.querySelector('.chatbot-finalizar');
  if (existente) existente.remove();
}

function mostrarBotonFinalizar() {
  eliminarBotonFinalizar();

  const cont = document.createElement('div');
  cont.className = 'chatbot-finalizar';

  const btn = document.createElement('button');
  btn.type = 'button';
  btn.className = 'chatbot-finalizar-btn';
  btn.innerHTML = `
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="1.8" fill="none" stroke-linecap="round" stroke-linejoin="round">
      <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
      <polyline points="16 17 21 12 16 7"/>
      <line x1="21" y1="12" x2="9" y2="12"/>
    </svg>
    <span>Finalizar conversación</span>
  `;
  btn.addEventListener('click', manejarFinalizar);

  cont.appendChild(btn);
  bodyEl.appendChild(cont);
  scrollToBottom();
}

function manejarFinalizar() {
  if (isTyping) return;
  enviarMensaje('adiós');
}

  // =====================================================
  // RESPUESTAS SIMULADAS
  // =====================================================
  const RESPUESTAS = {
    saludo: [
      '¡Hola! 👋 Soy el Asistente de Europa. Estoy acá para ayudarte con información sobre la estética: horarios, servicios, productos, ubicación y cómo reservar tu cita. ¿Sobre qué te gustaría saber?',
      '¡Hola! 😊 ¿Cómo estás? Soy el Asistente de Europa. Puedo contarte sobre horarios, servicios, productos, ubicación o cómo reservar una cita. ¿Qué necesitás?',
      '¡Buenas! ✨ Soy el Asistente de Europa. ¿En qué te puedo ayudar hoy?'
    ],
    proposito: [
      'Mi propósito es ayudarte con información sobre Peluquería y Estética Europa. 😊 Puedo contarte sobre:\n\n• Horarios de atención\n• Servicios y precios\n• Productos disponibles\n• Ubicación y contacto\n• Cómo reservar una cita\n\n¿Sobre qué te gustaría saber?',
      'Fui creado para que puedas consultar de forma rápida información sobre la estética. 💫 Manejo estos temas:\n\n• Horarios\n• Servicios\n• Productos\n• Ubicación\n• Cómo reservar\n\n¿Qué te interesa?'
    ],
    gracias: [
      '¡Con gusto! 😊 Si necesitás algo más, aquí estoy.',
      '¡De nada! 🙌 Cualquier cosa, me escribís.',
      '¡Un placer ayudarte! ✨ ¿Algo más en lo que pueda colaborar?'
    ],
    despedida: [
      '¡Hasta luego! 👋 Que tengas un lindo día.',
      '¡Nos vemos! 😊 Gracias por escribirnos.',
      '¡Chau! ✨ Espero verte pronto por Europa.'
    ],
    noEntiende: [
      'Mmm, no estoy seguro de haber entendido. 😅 Por ahora puedo ayudarte con: horarios, servicios, productos, ubicación y cómo reservar una cita. ¿Sobre cuál te gustaría saber?',
      'Perdón, todavía estoy aprendiendo. 🙈 Puedo darte info sobre horarios, servicios, productos, ubicación o cómo reservar. ¿Qué te gustaría consultar?'
    ],
    botonProximamente: [
      '¡Muy pronto podré ayudarte con eso! 🚧 Por ahora estoy en modo de prueba. Mientras tanto, podés escribirme tu consulta y vemos qué puedo hacer. 😊'
    ]
  };

  function elegirAleatoria(arr) {
    return arr[Math.floor(Math.random() * arr.length)];
  }

  // =====================================================
  // DETECCIÓN DE INTENCIÓN
  // =====================================================
  function normalizar(texto) {
    return texto.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim();
  }

  function detectarIntencion(texto) {
    const t = normalizar(texto);

    if (/^(hola|buenas|buenos dias|buenas tardes|buenas noches|hey|que tal|holi|saludos)\b/.test(t)) return 'saludo';
    if (/(quien sos|quien eres|para que|proposito|que podes hacer|que sabes|en que me puedes ayudar|en que me podes ayudar|que informacion|ayuda|funcion|para que fuiste)/.test(t)) return 'proposito';
    if (/(gracias|mil gracias|te agradezco|muchas gracias)/.test(t)) return 'gracias';
    if (/(adios|chau|chao|hasta luego|nos vemos|bye|me voy|hasta pronto)/.test(t)) return 'despedida';
    if (/(horario|hora|abren|cierran|servicio|precio|producto|ubicacion|direccion|telefono|reservar|cita|turno)/.test(t)) return 'botonProximamente';

    return 'noEntiende';
  }

  // =====================================================
  // ENVIAR MENSAJE
  // =====================================================
  async function enviarMensaje(texto) {
  const limpio = texto.trim();
  if (!limpio || isTyping) return;

  // 1) Mostrar mensaje del usuario
  agregarMensaje('user', limpio);
  history.push({ role: 'user', text: limpio, time: ahora() });
  guardarHistorial();

  eliminarBotonesRapidos();
  eliminarBotonFinalizar();

  input.value = '';
  input.focus();

  // 2) Mostrar "escribiendo…"
  isTyping = true;
  mostrarEscribiendo();

  // 3) Enviar al backend
  const ENDPOINT = '/Peluqueria/api/chatbot.php';

  try {
    const response = await fetch(ENDPOINT, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        message: limpio,
        state: chatState,
        history: history.slice(-6)
      })
    });

    const data = await response.json();
    const intencion = data.intent || 'noEntiende';
    const respuesta = data.reply || 'Lo siento, no pude procesar tu mensaje.';

    // 4) Guardar state nuevo (siempre, incluso si es null)
    chatState = data.state || null;

    // 5) Simular delay humano
    const delay = randomDelay();
    setTimeout(() => {
      ocultarEscribiendo();
      isTyping = false;

      agregarMensaje('bot', respuesta);
      history.push({ role: 'bot', text: respuesta, time: ahora() });
      guardarHistorial();

      // 6) Mostrar botones rápidos según intención
      const intencionesConBotones = ['saludo', 'proposito', 'noEntiende', 'proximamente'];
      if (intencionesConBotones.includes(intencion)) {
        mostrarBotonesRapidos();
      }

      // 7) Mostrar botón "Finalizar conversación" si NO está esperando sí/no
      const estadosEsperandoSiNo = ['esperando_precios', 'esperando_duracion', 'esperando_algo_mas'];
      if (!estadosEsperandoSiNo.includes(chatState)) {
        mostrarBotonFinalizar();
      }

      // 8) Si fue despedida, cerrar el chat suavemente
      if (intencion === 'despedida' || intencion === 'despedida_suave') {
        cerradoPorDespedida = true;
        setTimeout(() => {
          cerrarChat();
        }, 1800);
      }

    }, delay);

  } catch (error) {
    console.error('[Chatbot] Error al consultar el backend:', error);
    const delay = randomDelay();
    setTimeout(() => {
      ocultarEscribiendo();
      isTyping = false;
      const respuesta = 'Lo siento, tuve un problema al procesar tu mensaje. Por favor intentá de nuevo. 🙏';
      agregarMensaje('bot', respuesta);
      history.push({ role: 'bot', text: respuesta, time: ahora() });
      guardarHistorial();
      mostrarBotonFinalizar();
    }, delay);
  }
}

  // =====================================================
  // ABRIR / CERRAR
  // =====================================================
  function abrirChat() {
  if (isOpen) return;
  isOpen = true;

  // Si venía de una despedida, limpiar el historial
  if (cerradoPorDespedida) {
    history = [];
    chatState = null;
    cerradoPorDespedida = false;
    try { sessionStorage.removeItem(STORAGE_KEY); } catch (e) {}
    bodyEl.innerHTML = '';
  }

  widget.classList.add('is-open');
  windowEl.setAttribute('aria-hidden', 'false');
  setTimeout(() => input.focus(), 300);

  // Si no hay historial, saludar
  if (history.length === 0) {
    isTyping = true;
    mostrarEscribiendo();
    setTimeout(() => {
      ocultarEscribiendo();
      isTyping = false;
      const saludo = '¡Hola! 👋 Soy el Asistente de Europa. Estoy acá para ayudarte con información sobre la estética: horarios, servicios, productos, ubicación y cómo reservar tu cita. ¿Sobre qué te gustaría saber?';
      agregarMensaje('bot', saludo);
      history.push({ role: 'bot', text: saludo, time: ahora() });
      guardarHistorial();
      mostrarBotonesRapidos();
    }, 800);
  } else {
    mostrarBotonesRapidos();
    mostrarBotonFinalizar();
  }
}

function cerrarChat() {
  if (!isOpen) return;
  isOpen = false;
  widget.classList.remove('is-open');
  windowEl.setAttribute('aria-hidden', 'true');
}

  // =====================================================
  // RESTAURAR HISTORIAL
  // =====================================================
  function restaurarHistorial() {
  const guardado = cargarHistorial();
  if (!guardado.length) return;

  history = guardado;
  guardado.forEach(m => agregarMensaje(m.role, m.text, { time: m.time }));

  // Restaurar state si estaba guardado
  try {
    const savedState = sessionStorage.getItem(STORAGE_KEY + '_state');
    if (savedState && savedState !== 'null') chatState = savedState;
  } catch (e) {}

  mostrarBotonesRapidos();
  mostrarBotonFinalizar();
}

  // =====================================================
  // EVENTOS
  // =====================================================
  bubble.addEventListener('click', (e) => {
    if (e.target.closest('.chatbot-bubble-close')) return;
    abrirChat();
  });

  bubble.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      abrirChat();
    }
  });

  bubbleClose.addEventListener('click', (e) => {
    e.stopPropagation();
    ocultarBocadillo();
  });

  closeBtn.addEventListener('click', cerrarChat);

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && isOpen) cerrarChat();
  });

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    enviarMensaje(input.value);
  });

  bodyEl.addEventListener('click', manejarClickBotonRapido);

  // =====================================================
  // INICIALIZACIÓN
  // =====================================================
  restaurarHistorial();
})();