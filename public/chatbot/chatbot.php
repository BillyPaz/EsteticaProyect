<?php
/**
 * public/chatbot/chatbot.php
 * Widget visual del Asistente Europa.
 * Los botones rápidos NO van hardcodeados acá: los inserta el JS
 * después del saludo para que aparezcan siempre debajo del último mensaje.
 */
?>

<!-- =====================================================
     ASISTENTE EUROPA - WIDGET DE CHAT
     ===================================================== -->
<div class="chatbot-widget" id="chatbotWidget">

  <!-- ============ BOCADILLO DE INVITACIÓN ============ -->
  <div class="chatbot-bubble" id="chatbotBubble" role="button" tabindex="0" aria-label="Abrir Asistente Europa">
    <div class="chatbot-bubble-avatar">
      <span>E</span>
    </div>
    <div class="chatbot-bubble-text">
      <strong>Asistente Europa</strong>
      <span>Estoy listo para conversar 💬</span>
      <span>¿Te ayudo con algo? ✨</span>
      <span>Preguntame lo que necesites 👋</span>
    </div>
    <button type="button" class="chatbot-bubble-close" id="chatbotBubbleClose" aria-label="Ocultar">
      &times;
    </button>
  </div>

  <!-- ============ VENTANA DEL CHAT ============ -->
  <div class="chatbot-window" id="chatbotWindow" aria-hidden="true" role="dialog" aria-label="Asistente Europa">

    <!-- Cabecera -->
    <div class="chatbot-header">
      <div class="chatbot-header-info">
        <div class="chatbot-header-avatar">E</div>
        <div class="chatbot-header-text">
          <strong>Asistente Europa</strong>
          <span class="chatbot-status">
            <span class="chatbot-status-dot"></span> En línea
          </span>
        </div>
      </div>
      <button type="button" class="chatbot-close" id="chatbotClose" aria-label="Cerrar chat">
        &times;
      </button>
    </div>

    <!-- Cuerpo: solo mensajes. Los botones rápidos los inyecta el JS. -->
    <div class="chatbot-body" id="chatbotBody"></div>

    <!-- Pie: input + botón enviar -->
    <form class="chatbot-footer" id="chatbotForm" autocomplete="off" onsubmit="return false;">
      <input type="text"
             class="chatbot-input"
             id="chatbotInput"
             placeholder="Escribe tu pregunta…"
             maxlength="200"
             aria-label="Escribe tu mensaje" />
      <button type="submit" class="chatbot-send" id="chatbotSend" aria-label="Enviar mensaje">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
          <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
        </svg>
      </button>
    </form>

  </div>
</div>

<script src="chatbot/chatbot.js" defer></script>