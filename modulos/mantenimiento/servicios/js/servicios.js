/**
 * modulos/mantenimiento/servicios/js/servicios.js
 * Lógica del módulo Servicios:
 * - Toggle de pestañas (servicios / combos)
 * - CRUD de servicios (modal)
 * - CRUD de combos (vista embebida)
 */

(function () {
  'use strict';

  const RUTA_MODULO = '/Peluqueria/modulos/mantenimiento/servicios';

  // =====================================================
  // ESTADO GLOBAL DEL FORMULARIO DE COMBO
  // =====================================================
  const combo = {
    idCombo: null,            // null = nuevo, numero = editando
    servicios: [],            // [{ id_servicio, nombreServicio, costoServicio, duracion }]
  };

  // =====================================================
  // UTILIDADES
  // =====================================================

  function formatearQ(monto) {
    return 'Q ' + Number(monto).toFixed(2);
  }

  function limpiarFormCombo() {
    combo.idCombo = null;
    combo.servicios = [];

    const form = document.getElementById('formCombo');
    if (!form) return;

    form.reset();
    document.getElementById('inputIdCombo').value = '';
    document.getElementById('inputNombreCombo').value = '';
    document.getElementById('inputDescripcionCombo').value = '';
    document.getElementById('inputPrecioCombo').value = '';

    document.getElementById('resultadosServicios').innerHTML = '';
    renderCuadroCombo();
    calcularPrecios();
  }

  // =====================================================
  // 1. TOGGLE DE PESTAÑAS
  // =====================================================
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.toggle-btn');
    if (!btn) return;

    const tab = btn.dataset.tab;

    // Actualizar botones
    document.querySelectorAll('.toggle-btn').forEach(b => {
      b.classList.toggle('activo', b === btn);
    });

    // Mostrar pestaña correspondiente
    document.querySelectorAll('.tab-catalogo').forEach(t => {
      t.classList.toggle('activo', t.id === 'tab-' + tab);
    });

    // Ocultar formulario embebido (si estaba abierto)
    const formWrap = document.getElementById('formComboWrap');
    if (formWrap) formWrap.classList.remove('activo');
  });

  // =====================================================
  // 2. CRUD DE SERVICIOS
  // =====================================================

  // --- 2.1 Abrir modal "Nuevo servicio" ---
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('#btnNuevoServicio');
    if (!btn) return;

    limpiarFormServicio();
    document.getElementById('modalServicioTitulo').textContent = 'Nuevo servicio';
    document.getElementById('modalServicio').classList.add('abierto');
  });

  // --- 2.2 Abrir modal "Editar servicio" (desde botón editar) ---
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-accion.editar[data-servicio]');
    if (!btn) return;

    const id       = btn.dataset.servicio;
    const nombre   = btn.dataset.nombre || '';
    const costo    = btn.dataset.costo || '';
    const duracion = btn.dataset.duracion || '';
    const activo   = btn.dataset.activo || '1';

    document.getElementById('inputIdServicio').value      = id;
    document.getElementById('inputNombreServicio').value  = nombre;
    document.getElementById('inputCostoServicio').value   = costo;
    document.getElementById('inputDuracionServicio').value = duracion;
    document.getElementById('inputActivoServicio').value  = activo;

    document.getElementById('modalServicioTitulo').textContent = 'Editar servicio';
    document.getElementById('modalServicio').classList.add('abierto');
  });

  function limpiarFormServicio() {
    const form = document.getElementById('formServicio');
    if (!form) return;
    form.reset();
    document.getElementById('inputIdServicio').value = '';
  }

  // --- 2.3 Submit del formulario de servicio ---
  document.addEventListener('submit', async (e) => {
    const form = e.target;
    if (form.id !== 'formServicio') return;

    e.preventDefault();

    const idServicio = document.getElementById('inputIdServicio').value;
    const esEdicion  = idServicio && parseInt(idServicio) > 0;

    const url = esEdicion
      ? `${RUTA_MODULO}/php/editar_servicio.php`
      : `${RUTA_MODULO}/php/ingresar_servicio.php`;

    const formData = new FormData(form);

    Swal.fire({
      title: 'Guardando...',
      allowOutsideClick: false,
      allowEscapeKey: false,
      didOpen: () => Swal.showLoading()
    });

    try {
      const resp = await fetch(url, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });

      const data = await resp.json();

      if (data.success) {
        document.getElementById('modalServicio').classList.remove('abierto');
        form.reset();

        await Swal.fire({
          icon: 'success',
          title: esEdicion ? 'Servicio actualizado' : 'Servicio creado',
          text: data.message,
          timer: 1800,
          showConfirmButton: false
        });

        recargarModulo();
        return;
      }

      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: data.message || 'Ocurrió un error al guardar.'
      });

    } catch (err) {
      Swal.fire({
        icon: 'error',
        title: 'Error de conexión',
        text: 'No se pudo conectar con el servidor.'
      });
    }
  });

  // --- 2.4 Eliminar servicio ---
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.btn-accion.eliminar[data-servicio]');
    if (!btn) return;

    const id = btn.dataset.servicio;

    const confirm = await Swal.fire({
      icon: 'warning',
      title: '¿Eliminar servicio?',
      text: 'Esta acción no se puede deshacer.',
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#d9534f',
    });

    if (!confirm.isConfirmed) return;

    Swal.fire({
      title: 'Eliminando...',
      allowOutsideClick: false,
      allowEscapeKey: false,
      didOpen: () => Swal.showLoading()
    });

    try {
      const fd = new FormData();
      fd.append('id_servicio', id);

      const resp = await fetch(`${RUTA_MODULO}/php/eliminar_servicio.php`, {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      const data = await resp.json();

      if (data.success) {
        await Swal.fire({
          icon: 'success',
          title: 'Eliminado',
          text: data.message,
          timer: 1800,
          showConfirmButton: false
        });
        recargarModulo();
        return;
      }

      Swal.fire({
        icon: 'error',
        title: 'No se puede eliminar',
        text: data.message
      });

    } catch (err) {
      Swal.fire({
        icon: 'error',
        title: 'Error de conexión',
        text: 'No se pudo conectar con el servidor.'
      });
    }
  });

  // --- 2.5 Buscador de servicios (tabla) ---
  document.addEventListener('input', (e) => {
    if (e.target.id !== 'buscarServicio') return;

    const tabla = document.getElementById('tablaServicios');
    if (!tabla) return;

    const t = e.target.value.trim().toLowerCase();
    tabla.querySelectorAll('tbody tr').forEach(tr => {
      const coincide = !t || tr.textContent.toLowerCase().includes(t);
      tr.style.display = coincide ? '' : 'none';
    });
  });

  // =====================================================
  // 3. CRUD DE COMBOS (vista embebida)
  // =====================================================

  // --- 3.1 Abrir vista embebida "Nuevo combo" ---
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('#btnNuevoCombo');
    if (!btn) return;

    limpiarFormCombo();
    document.getElementById('formComboTitulo').innerHTML = 'Crear <em>combo</em>';
    mostrarFormCombo();
  });

  // --- 3.2 Abrir vista embebida "Editar combo" ---
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.btn-accion.editar[data-combo]');
    if (!btn) return;

    const id = btn.dataset.combo;

    Swal.fire({
      title: 'Cargando combo...',
      allowOutsideClick: false,
      allowEscapeKey: false,
      didOpen: () => Swal.showLoading()
    });

    try {
      const resp = await fetch(`${RUTA_MODULO}/php/obtener_combo.php?id_combo=${id}`);
      const data = await resp.json();

      if (!data.success) {
        Swal.fire({ icon: 'error', title: 'Error', text: data.message });
        return;
      }

      const c = data.combo;

      // Rellenar formulario
      combo.idCombo = c.idCombo;
      combo.servicios = c.servicios || [];

      document.getElementById('inputIdCombo').value           = c.idCombo;
      document.getElementById('inputNombreCombo').value       = c.nombre || '';
      document.getElementById('inputDescripcionCombo').value  = c.descripcion || '';
      document.getElementById('inputPrecioCombo').value       = c.precioCombo || '';
      document.getElementById('inputIncluyeBebida').value     = c.incluyeBebida || '0';
      document.getElementById('inputActivoCombo').value       = c.activo || '1';

      document.getElementById('formComboTitulo').innerHTML = 'Editar <em>combo</em>';

      renderCuadroCombo();
      calcularPrecios();
      mostrarFormCombo();
      Swal.close();

    } catch (err) {
      Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo cargar el combo.' });
    }
  });

  function mostrarFormCombo() {
    document.getElementById('formComboWrap').classList.add('activo');
    document.querySelectorAll('.tab-catalogo').forEach(t => t.classList.remove('activo'));
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  // --- 3.3 Volver a la lista de combos ---
  document.addEventListener('click', (e) => {
    if (e.target.id !== 'btnVolverCombos' && e.target.id !== 'btnCancelarCombo') return;

    document.getElementById('formComboWrap').classList.remove('activo');

    // Reactivar la pestaña de combos
    document.querySelectorAll('.toggle-btn').forEach(b => {
      b.classList.toggle('activo', b.dataset.tab === 'combos');
    });
    document.querySelectorAll('.tab-catalogo').forEach(t => {
      t.classList.toggle('activo', t.id === 'tab-combos');
    });

    limpiarFormCombo();
  });

  // --- 3.4 Buscador de servicios para el combo ---
  let timeoutBusquedaCombo = null;

  document.addEventListener('input', (e) => {
    if (e.target.id !== 'buscarServicioCombo') return;

    clearTimeout(timeoutBusquedaCombo);
    const q = e.target.value.trim();

    timeoutBusquedaCombo = setTimeout(() => buscarServiciosCombo(q), 300);
  });

  document.addEventListener('click', (e) => {
    if (e.target.id !== 'btnMostrarTodos') return;
    buscarServiciosCombo('');
  });

  async function buscarServiciosCombo(q) {
    try {
      const resp = await fetch(`${RUTA_MODULO}/php/buscar_servicios.php?q=${encodeURIComponent(q)}`);
      const data = await resp.json();

      if (!data.success) return;

      // Filtrar los que ya están agregados
      const yaAgregados = combo.servicios.map(s => parseInt(s.id_servicio));
      const disponibles = data.servicios.filter(s => !yaAgregados.includes(parseInt(s.id_servicio)));

      renderResultados(disponibles);

    } catch (err) {
      // Silencioso
    }
  }

  function renderResultados(servicios) {
    const cont = document.getElementById('resultadosServicios');
    if (!cont) return;

    if (servicios.length === 0) {
      cont.innerHTML = '';
      return;
    }

    cont.innerHTML = servicios.map(s => `
      <div class="resultado-item" data-id="${s.id_servicio}">
        <div class="info">
          <strong>${escapeHtml(s.nombreServicio)}</strong>
          <small>${formatearQ(s.costoServicio)} · ${s.duracion} min</small>
        </div>
        <button type="button" class="btn-add" title="Agregar al combo">+</button>
      </div>
    `).join('');
  }

  // --- 3.5 Agregar servicio al combo ---
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.resultado-item .btn-add');
    if (!btn) return;

    const item = btn.closest('.resultado-item');
    const id = item.dataset.id;

    // Buscar el servicio en los datos del último render
    // (Para simplificar, pedimos los datos al vuelo)
    fetch(`${RUTA_MODULO}/php/buscar_servicios.php?q=`)
      .then(r => r.json())
      .then(data => {
        if (!data.success) return;
        const s = data.servicios.find(x => parseInt(x.id_servicio) === parseInt(id));
        if (!s) return;

        // Verificar duplicado
        if (combo.servicios.some(x => parseInt(x.id_servicio) === parseInt(s.id_servicio))) {
          return;
        }

        combo.servicios.push(s);

        // Quitar de los resultados
        item.remove();

        renderCuadroCombo();
        calcularPrecios();
      });
  });

  // --- 3.6 Quitar servicio del combo ---
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.item-agregado .btn-remove');
    if (!btn) return;

    const id = btn.dataset.id;

    combo.servicios = combo.servicios.filter(s => parseInt(s.id_servicio) !== parseInt(id));

    renderCuadroCombo();
    calcularPrecios();

    // Refrescar el buscador si hay texto
    const input = document.getElementById('buscarServicioCombo');
    if (input && input.value.trim() !== '') {
      buscarServiciosCombo(input.value.trim());
    }
  });

  // --- 3.7 Render del cuadro del combo ---
  function renderCuadroCombo() {
    const cont = document.getElementById('cuadroCombo');
    if (!cont) return;

    if (combo.servicios.length === 0) {
      cont.innerHTML = '<p class="cuadro-vacio" id="cuadroVacio">Aún no has agregado servicios al combo.</p>';
      cont.classList.remove('tiene-items');
      return;
    }

    cont.classList.add('tiene-items');
    cont.innerHTML = combo.servicios.map(s => `
      <div class="item-agregado">
        <div class="info">
          <strong>${escapeHtml(s.nombreServicio)}</strong>
          <span class="precio">${formatearQ(s.costoServicio)} · ${s.duracion} min</span>
        </div>
        <button type="button" class="btn-remove" data-id="${s.id_servicio}" title="Quitar">×</button>
      </div>
    `).join('');
  }

  // --- 3.8 Calcular precios en vivo ---
  function calcularPrecios() {
    const totalOriginal = combo.servicios.reduce((sum, s) => sum + parseFloat(s.costoServicio), 0);
    const totalDuracion = combo.servicios.reduce((sum, s) => sum + parseInt(s.duracion), 0);

    const inputPrecio = document.getElementById('inputPrecioCombo');
    const precioCombo = parseFloat(inputPrecio?.value || 0) || 0;
    const ahorro = Math.max(0, totalOriginal - precioCombo);

    const elPrecioOriginal = document.getElementById('inputPrecioOriginal');
    const elAhorro         = document.getElementById('inputAhorro');
    const elDuracion       = document.getElementById('inputDuracionTotal');

    if (elPrecioOriginal) elPrecioOriginal.value = formatearQ(totalOriginal);
    if (elAhorro)         elAhorro.value         = formatearQ(ahorro);
    if (elDuracion)       elDuracion.value       = totalDuracion + ' min';
  }

  // Recalcular cuando cambia el precio del combo
  document.addEventListener('input', (e) => {
    if (e.target.id === 'inputPrecioCombo') calcularPrecios();
  });

  // --- 3.9 Guardar combo ---
  document.addEventListener('submit', async (e) => {
    const form = e.target;
    if (form.id !== 'formCombo') return;

    e.preventDefault();

    // Validaciones
    if (combo.servicios.length === 0) {
      Swal.fire({
        icon: 'warning',
        title: 'Sin servicios',
        text: 'Debes agregar al menos un servicio al combo.'
      });
      return;
    }

    const nombre = document.getElementById('inputNombreCombo').value.trim();
    const precio = parseFloat(document.getElementById('inputPrecioCombo').value);

    if (!nombre) {
      Swal.fire({ icon: 'warning', title: 'Falta el nombre', text: 'El nombre del combo es obligatorio.' });
      return;
    }

    if (!precio || precio <= 0) {
      Swal.fire({ icon: 'warning', title: 'Precio inválido', text: 'El precio del combo debe ser mayor a 0.' });
      return;
    }

    const esEdicion = combo.idCombo !== null;

    const payload = {
      idCombo: esEdicion ? combo.idCombo : 0,
      nombre: nombre,
      descripcion: document.getElementById('inputDescripcionCombo').value.trim(),
      precioCombo: precio,
      incluyeBebida: parseInt(document.getElementById('inputIncluyeBebida').value),
      activo: parseInt(document.getElementById('inputActivoCombo').value),
      servicios: combo.servicios.map(s => parseInt(s.id_servicio)),
    };

    const url = esEdicion
      ? `${RUTA_MODULO}/php/editar_combo.php`
      : `${RUTA_MODULO}/php/ingresar_combo.php`;

    Swal.fire({
      title: 'Guardando...',
      allowOutsideClick: false,
      allowEscapeKey: false,
      didOpen: () => Swal.showLoading()
    });

    try {
      const resp = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(payload)
      });

      const data = await resp.json();

      if (data.success) {
        await Swal.fire({
          icon: 'success',
          title: esEdicion ? 'Combo actualizado' : 'Combo creado',
          text: data.message,
          timer: 1800,
          showConfirmButton: false
        });

        recargarModulo();
        return;
      }

      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: data.message || 'Ocurrió un error al guardar.'
      });

    } catch (err) {
      Swal.fire({
        icon: 'error',
        title: 'Error de conexión',
        text: 'No se pudo conectar con el servidor.'
      });
    }
  });

  // --- 3.10 Eliminar combo ---
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.btn-accion.eliminar[data-combo]');
    if (!btn) return;

    const id = btn.dataset.combo;

    const confirm = await Swal.fire({
      icon: 'warning',
      title: '¿Eliminar combo?',
      text: 'Esta acción no se puede deshacer.',
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#d9534f',
    });

    if (!confirm.isConfirmed) return;

    Swal.fire({
      title: 'Eliminando...',
      allowOutsideClick: false,
      allowEscapeKey: false,
      didOpen: () => Swal.showLoading()
    });

    try {
      const fd = new FormData();
      fd.append('id_combo', id);

      const resp = await fetch(`${RUTA_MODULO}/php/eliminar_combo.php`, {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      const data = await resp.json();

      if (data.success) {
        await Swal.fire({
          icon: 'success',
          title: 'Eliminado',
          text: data.message,
          timer: 1800,
          showConfirmButton: false
        });
        recargarModulo();
        return;
      }

      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: data.message
      });

    } catch (err) {
      Swal.fire({
        icon: 'error',
        title: 'Error de conexión',
        text: 'No se pudo conectar con el servidor.'
      });
    }
  });

  // --- 3.11 Buscador de combos (grid) ---
  document.addEventListener('input', (e) => {
    if (e.target.id !== 'buscarCombo') return;

    const grid = document.getElementById('gridCombos');
    if (!grid) return;

    const t = e.target.value.trim().toLowerCase();
    grid.querySelectorAll('.card-combo').forEach(card => {
      const coincide = !t || card.textContent.toLowerCase().includes(t);
      card.style.display = coincide ? '' : 'none';
    });
  });

  // =====================================================
  // 4. UTILIDADES
  // =====================================================
  function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str == null ? '' : str;
    return div.innerHTML;
  }

  function recargarModulo() {
    const botonActivo = document.querySelector('.side-nav button.activo[data-seccion]');
    if (botonActivo) {
      botonActivo.click();
    } else {
      window.location.reload();
    }
  }

})();