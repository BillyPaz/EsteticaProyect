<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Europa · Reservar cita</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
   <link rel="stylesheet" href="css/reservarcita.css" />
 
</head>
<body>

<!-- ====== HEADER ====== -->
<div class="page-header">
  <div class="left">
    <h1>Reserva tu <em>cita</em></h1>
    <p>Elige un combo o servicios individuales. Tú decides qué incluir.</p>
  </div>
  <div class="right">
    <span class="badge-info"><i class="fas fa-clock"></i> Duración estimada: <strong id="badgeDuracion">0 min</strong></span>
  </div>
</div>

<!-- ====== LAYOUT ====== -->
<div class="reserva-layout">

  <!-- COLUMNA IZQUIERDA -->
  <div>

    <!-- Stepper -->
    <div class="stepper">
      <div class="step active" id="step1">
        <span class="num">1</span>
        <span>Servicios</span>
      </div>
      <div class="step-divider"></div>
      <div class="step" id="step2">
        <span class="num">2</span>
        <span>Fecha y hora</span>
      </div>
      <div class="step-divider"></div>
      <div class="step" id="step3">
        <span class="num">3</span>
        <span>Confirmar</span>
      </div>
    </div>

    <!-- SECCIÓN 1: SERVICIOS -->
    <div class="seccion">
      <div class="seccion-header">
        <h2><i class="fas fa-spa"></i> Elige tus servicios</h2>
        <span class="sub">Elegí un combo ya armado o servicios individuales</span>
      </div>

      <!-- Tabs -->
      <div class="tabs">
        <div class="tab active" data-tab="combos" onclick="cambiarTab('combos')">
          <i class="fas fa-gift"></i> Combos
        </div>
        <div class="tab" data-tab="individuales" onclick="cambiarTab('individuales')">
          <i class="fas fa-cut"></i> Servicios individuales
        </div>
      </div>

      <!-- Contenido: Combos -->
      <div id="tabCombos">
        <div class="combos-grid" id="gridCombos">
          <!-- Generado por JS -->
        </div>
      </div>

      <!-- Contenido: Servicios individuales -->
      <div id="tabIndividuales" style="display:none;">
        <div class="servicios-grid" id="gridServicios">
          <!-- Generado por JS -->
        </div>
      </div>
    </div>

    <!-- SECCIÓN 2: FECHA Y HORA -->
    <div class="seccion">
      <div class="seccion-header">
        <h2><i class="fas fa-calendar-alt"></i> Fecha y hora</h2>
        <span class="sub">Selecciona cuándo quieres tu cita</span>
      </div>

      <div class="fecha-hora-grid">
        <div class="form-group" id="groupFecha">
          <label><i class="fas fa-calendar-day"></i> Fecha <span class="required">*</span></label>
          <input type="date" id="fechaCita" onchange="cargarHorarios()">
          <div class="error-msg" id="errorFecha">Selecciona una fecha</div>
        </div>
        <div class="form-group" id="groupBarbero">
          <label><i class="fas fa-user-tie"></i> Barbero / Estilista <span class="required">*</span></label>
          <select id="barberoSelect" onchange="cargarHorarios()">
            <option value="">Seleccionar profesional...</option>
            <option value="1">Otto Mérida</option>
            <option value="2">Kevin Solís</option>
            <option value="3">María Fernanda López</option>
            <option value="4">Lucia Herrera</option>
          </select>
          <div class="error-msg" id="errorBarbero">Selecciona un profesional</div>
        </div>
      </div>

      <div class="form-group" style="margin-top:16px;" id="groupHorario">
        <label><i class="fas fa-clock"></i> Horario disponible <span class="required">*</span></label>
        <div class="horarios-grid" id="horariosGrid">
          <div style="grid-column: 1/-1; text-align:center; padding:20px; color:#aaa; font-size:13px;">
            Selecciona fecha y profesional para ver horarios disponibles
          </div>
        </div>
        <div class="error-msg" id="errorHorario">Selecciona un horario</div>
      </div>
    </div>

  </div>

  <!-- COLUMNA DERECHA: RESUMEN -->
  <div class="resumen-panel">
    <div class="panel-title">
      <i class="fas fa-receipt"></i> Resumen de tu reserva
    </div>

    <!-- Estado vacío -->
    <div class="resumen-vacio" id="resumenVacio">
      <i class="fas fa-spa"></i>
      <p>Aún no has elegido servicios</p>
    </div>

    <!-- Lista de items -->
    <div class="resumen-lista" id="resumenLista" style="display:none;">
      <!-- Generado por JS -->
    </div>

    <!-- Totales -->
    <div id="resumenTotales" style="display:none;">
      <div class="resumen-linea">
        <span>Servicios</span>
        <span id="resumenServicios">Q 0.00</span>
      </div>
      <div class="resumen-linea">
        <span>Descuento combo</span>
        <span style="color:#2e7d4f;" id="resumenDescuento">- Q 0.00</span>
      </div>
      <div class="resumen-linea total">
        <span class="label">TOTAL</span>
        <span id="resumenTotal">Q 0.00</span>
      </div>
    </div>

    <!-- Botón -->
    <button class="btn-reservar" id="btnReservar" disabled onclick="procesarReserva()">
      <i class="fas fa-calendar-check"></i> Reservar cita
    </button>

    <div style="margin-top:14px; padding-top:14px; border-top:1px solid #f0ebe5; font-size:11px; color:#999; text-align:center;">
      <i class="fas fa-shield-alt"></i> Sin pagos anticipados. Paga al finalizar tu cita.
    </div>
  </div>

</div>

<!-- ====== MODAL DE DETALLE DE COMBO ====== -->
<div class="modal-overlay" id="modalCombo">
  <div class="modal">
    <div class="modal-header">
      <div class="info-combo">
        <div class="icono-combo" id="modalComboIcono">
          <i class="fas fa-gift"></i>
        </div>
        <div>
          <h3 id="modalComboNombre">Combo</h3>
          <div class="desc" id="modalComboDesc">Detalle del combo</div>
        </div>
      </div>
      <button class="modal-close" onclick="cerrarModalCombo()"><i class="fas fa-times"></i></button>
    </div>

    <div class="modal-body">
      <div class="aviso">
        <i class="fas fa-info-circle"></i>
        <div>
          <strong>Servicios incluidos</strong><br>
          Este combo ya viene armado con todo lo que necesitás en una sola reserva.
        </div>
      </div>

      <div class="servicios-combo-lista" id="modalServiciosCombo">
        <!-- Generado por JS -->
      </div>

      <!-- Bloque bebida (solo si aplica) -->
      <div class="incluye-bebida" id="modalBebida" style="display:none;">
        <i class="fas fa-coffee"></i>
        <span>Incluye una bebida</span>
      </div>
    </div>

    <div class="modal-footer">
      <div class="precio-total">
        <span class="label">Precio del combo</span>
        <span class="valor" id="modalComboTotal">Q 0.00</span>
      </div>
      <div class="acciones">
        <button class="btn-cancelar" onclick="cerrarModalCombo()">Cancelar</button>
        <button class="btn-confirmar" onclick="confirmarCombo()">
          <i class="fas fa-check"></i> Agregar combo
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ====== MODAL: DATOS DEL CLIENTE ====== -->
<div class="modal-overlay" id="modalCliente">
  <div class="modal modal-cliente">
    <div class="modal-header">
      <div class="info-combo">
        <div class="icono-combo" id="modalClienteIcono">
          <i class="fas fa-user"></i>
        </div>
        <div>
          <h3>Tus datos</h3>
          <div class="desc">Completá tus datos para confirmar la reserva</div>
        </div>
      </div>
      <button class="modal-close" onclick="cerrarModalCliente()"><i class="fas fa-times"></i></button>
    </div>

    <div class="modal-body">
      <form id="formCliente" novalidate>
        <div class="form-grid">
          <!-- Nombre -->
          <div class="form-field">
            <label for="cliNombre">
              <i class="fas fa-user"></i> Nombre <span class="req">*</span>
            </label>
            <input type="text" id="cliNombre" name="nombre" autocomplete="given-name" placeholder="Ej. María">
            <span class="error-msg" id="errNombre">Ingresá tu nombre</span>
          </div>

          <!-- Apellidos -->
          <div class="form-field">
            <label for="cliApellido">
              <i class="fas fa-user"></i> Apellidos <span class="req">*</span>
            </label>
            <input type="text" id="cliApellido" name="apellido" autocomplete="family-name" placeholder="Ej. López">
            <span class="error-msg" id="errApellido">Ingresá tus apellidos</span>
          </div>

          <!-- Teléfono -->
          <div class="form-field">
            <label for="cliTelefono">
              <i class="fas fa-phone"></i> Teléfono <span class="req">*</span>
            </label>
            <div class="input-tel">
              <span class="tel-prefix">
                <span class="flag-gt">
                  <span class="flag-azul"></span>
                  <span class="flag-blanco"></span>
                  <span class="flag-azul"></span>
                </span>
                +502
              </span>
              <input type="tel" id="cliTelefono" name="telefono" inputmode="numeric" maxlength="8" placeholder="5555 5555" autocomplete="tel-national">
            </div>
            <span class="error-msg" id="errTelefono">Ingresá 8 dígitos</span>
          </div>

          <!-- Correo (opcional) -->
          <div class="form-field">
            <label for="cliCorreo">
              <i class="fas fa-envelope"></i> Correo <span class="opcional">(opcional)</span>
            </label>
            <input type="email" id="cliCorreo" name="correo" autocomplete="email" placeholder="tucorreo@ejemplo.com">
            <span class="error-msg" id="errCorreo">Correo inválido</span>
          </div>
        </div>
      </form>
    </div>

    <div class="modal-footer">
      <div class="acciones">
        <button class="btn-cancelar" type="button" onclick="cerrarModalCliente()">Cancelar</button>
        <button class="btn-confirmar" type="button" onclick="confirmarDatosCliente()">
          <i class="fas fa-check"></i> Confirmar reserva
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ====== TOAST ====== -->
<div class="toast" id="toast">
  <i class="fas fa-check-circle"></i>
  <span id="toastMsg">Reserva procesada correctamente</span>
</div>

 <script src="js/reservarcita.js"></script>

</body>
</html>