/**
 * modulos/mantenimiento/membresias/js/membresias.js
 * Lógica del módulo Membresías:
 * - Toggle de pestañas
 * - CRUD de membresías (modal)
 * - Asignación de membresías a clientes (modal con buscador)
 */

(function () {
  'use strict';

  const RUTA_MODULO = '/Peluqueria/modulos/mantenimiento/membresias';

  // =====================================================
  // ESTADO DEL BUSCADOR DE CLIENTES
  // =====================================================
  const busquedaCliente = {
    timeout: null,
    clienteSeleccionado: null,   // { id_cliente, nombreCliente, apellidoCliente, correo }
  };

  // =====================================================
  // UTILIDADES
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

  // =====================================================
  // 1. TOGGLE DE PESTAÑAS
  // =====================================================
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.toggle-btn');
    if (!btn) return;

    const tab = btn.dataset.tab;

    document.querySelectorAll('.toggle-btn').forEach(b => {
      b.classList.toggle('activo', b === btn);
    });
    document.querySelectorAll('.tab-catalogo').forEach(t => {
      t.classList.toggle('activo', t.id === 'tab-' + tab);
    });
  });

  // =====================================================
  // 2. CRUD DE MEMBRESÍAS
  // =====================================================

  // --- 2.1 Abrir modal "Nueva membresía" ---
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('#btnNuevaMembresia');
    if (!btn) return;

    limpiarFormMembresia();
    document.getElementById('modalMembresiaTitulo').textContent = 'Nueva membresía';
    document.getElementById('modalMembresia').classList.add('abierto');
  });

  // --- 2.2 Abrir modal "Editar membresía" ---
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-accion.editar[data-membresia]');
    if (!btn) return;

    document.getElementById('inputIdMembresia').value = btn.dataset.membresia;
    document.getElementById('inputCodigo').value = btn.dataset.codigo || '';
    document.getElementById('inputNombreMembresia').value = btn.dataset.nombre || '';
    document.getElementById('inputPorcentaje').value = btn.dataset.porcentaje || '';
    document.getElementById('inputEstadoMembresia').value = btn.dataset.estado || '1';

    document.getElementById('modalMembresiaTitulo').textContent = 'Editar membresía';
    document.getElementById('modalMembresia').classList.add('abierto');
  });

  function limpiarFormMembresia() {
    const form = document.getElementById('formMembresia');
    if (!form) return;
    form.reset();
    document.getElementById('inputIdMembresia').value = '';
  }

  // --- 2.3 Submit del formulario de membresía ---
  document.addEventListener('submit', async (e) => {
    const form = e.target;
    if (form.id !== 'formMembresia') return;

    e.preventDefault();

    const idMembresia = document.getElementById('inputIdMembresia').value;
    const esEdicion = idMembresia && parseInt(idMembresia) > 0;

    const url = esEdicion
      ? `${RUTA_MODULO}/php/editar_membresia.php`
      : `${RUTA_MODULO}/php/ingresar_membresia.php`;

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
        document.getElementById('modalMembresia').classList.remove('abierto');
        form.reset();

        await Swal.fire({
          icon: 'success',
          title: esEdicion ? 'Membresía actualizada' : 'Membresía creada',
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

  // --- 2.4 Eliminar membresía ---
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.btn-accion.eliminar[data-membresia]');
    if (!btn) return;

    const id = btn.dataset.membresia;

    const confirm = await Swal.fire({
      icon: 'warning',
      title: '¿Eliminar membresía?',
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
      fd.append('id_membresia', id);

      const resp = await fetch(`${RUTA_MODULO}/php/eliminar_membresia.php`, {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      const data = await resp.json();

      if (data.success) {
        await Swal.fire({
          icon: 'success',
          title: 'Eliminada',
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

  // --- 2.5 Buscador de membresías ---
  document.addEventListener('input', (e) => {
    if (e.target.id !== 'buscarMembresia') return;

    const tabla = document.getElementById('tablaMembresias');
    if (!tabla) return;

    const t = e.target.value.trim().toLowerCase();
    tabla.querySelectorAll('tbody tr').forEach(tr => {
      const coincide = !t || tr.textContent.toLowerCase().includes(t);
      tr.style.display = coincide ? '' : 'none';
    });
  });

  // =====================================================
  // 3. ASIGNAR MEMBRESÍA A CLIENTE
  // =====================================================

  // --- 3.1 Abrir modal "Asignar membresía" ---
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('#btnAsignarMembresia');
    if (!btn) return;

    limpiarFormAsignar();
    document.getElementById('modalAsignarMembresia').classList.add('abierto');
  });

  function limpiarFormAsignar() {
    const form = document.getElementById('formAsignarMembresia');
    if (!form) return;
    form.reset();
    document.getElementById('inputIdAsignacion').value = '';
    document.getElementById('inputIdCliente').value = '';
    document.getElementById('buscarClienteAsignar').value = '';

    // Ocultar resultados y chip
    const contRes = document.getElementById('resultadosCliente');
    const contChip = document.getElementById('clienteSeleccionado');
    if (contRes) {
      contRes.innerHTML = '';
      contRes.classList.remove('abierto');
    }
    if (contChip) {
      contChip.innerHTML = '';
      contChip.classList.remove('activo');
    }

    busquedaCliente.clienteSeleccionado = null;
  }

  // --- 3.2 Buscador de clientes (autocompletado) ---
  document.addEventListener('input', (e) => {
    if (e.target.id !== 'buscarClienteAsignar') return;

    // Si el usuario empieza a escribir, limpiamos la selección previa
    if (busquedaCliente.clienteSeleccionado) {
      busquedaCliente.clienteSeleccionado = null;
      document.getElementById('inputIdCliente').value = '';
      const chip = document.getElementById('clienteSeleccionado');
      chip.innerHTML = '';
      chip.classList.remove('activo');
    }

    clearTimeout(busquedaCliente.timeout);
    const q = e.target.value.trim();

    if (q.length < 2) {
      const cont = document.getElementById('resultadosCliente');
      cont.innerHTML = '';
      cont.classList.remove('abierto');
      return;
    }

    busquedaCliente.timeout = setTimeout(() => buscarClientes(q), 300);
  });

  async function buscarClientes(q) {
    try {
      const resp = await fetch(`${RUTA_MODULO}/php/buscar_clientes.php?q=${encodeURIComponent(q)}`);
      const data = await resp.json();

      if (!data.success) return;

      renderResultadosClientes(data.clientes);

    } catch (err) {
      // Silencioso
    }
  }

  function renderResultadosClientes(clientes) {
    const cont = document.getElementById('resultadosCliente');
    if (!cont) return;

    if (clientes.length === 0) {
      cont.innerHTML = '<div class="sin-resultados">No se encontraron clientes.</div>';
      cont.classList.add('abierto');
      return;
    }

    cont.innerHTML = clientes.map(c => `
      <div class="resultado-cliente" data-cliente='${JSON.stringify(c).replace(/'/g, "&apos;")}'>
        <div class="info">
          <strong>${escapeHtml(c.nombreCliente + ' ' + c.apellidoCliente)}</strong>
          <small>${escapeHtml(c.correo)} · ${escapeHtml(c.telefono)}</small>
        </div>
      </div>
    `).join('');

    cont.classList.add('abierto');
  }

  // --- 3.3 Seleccionar un cliente ---
  document.addEventListener('click', (e) => {
    const item = e.target.closest('.resultado-cliente');
    if (!item) return;

    let cliente;
    try {
      cliente = JSON.parse(item.dataset.cliente);
    } catch (err) {
      return;
    }

    busquedaCliente.clienteSeleccionado = cliente;

    // Guardar en el input hidden
    document.getElementById('inputIdCliente').value = cliente.id_cliente;

    // Mostrar chip
    const chip = document.getElementById('clienteSeleccionado');
    chip.innerHTML = `
      <div class="info">
        <strong>${escapeHtml(cliente.nombreCliente + ' ' + cliente.apellidoCliente)}</strong>
        <small>${escapeHtml(cliente.correo)}</small>
      </div>
      <button type="button" class="btn-quitar-cliente" title="Quitar">×</button>
    `;
    chip.classList.add('activo');

    // Limpiar input y ocultar dropdown
    document.getElementById('buscarClienteAsignar').value = '';
    const contRes = document.getElementById('resultadosCliente');
    contRes.innerHTML = '';
    contRes.classList.remove('abierto');
  });

  // --- 3.4 Quitar el cliente seleccionado ---
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-quitar-cliente');
    if (!btn) return;

    busquedaCliente.clienteSeleccionado = null;
    document.getElementById('inputIdCliente').value = '';
    const chip = document.getElementById('clienteSeleccionado');
    chip.innerHTML = '';
    chip.classList.remove('activo');
  });

  // --- 3.5 Cerrar dropdown si se hace clic fuera ---
  document.addEventListener('click', (e) => {
    const contRes = document.getElementById('resultadosCliente');
    if (!contRes || !contRes.classList.contains('abierto')) return;

    // Si el clic fue dentro del campo o del dropdown, no cerrar
    if (e.target.closest('#buscarClienteAsignar') || e.target.closest('#resultadosCliente')) {
      return;
    }

    contRes.classList.remove('abierto');
  });

  // --- 3.6 Submit del formulario de asignación ---
  document.addEventListener('submit', async (e) => {
    const form = e.target;
    if (form.id !== 'formAsignarMembresia') return;

    e.preventDefault();

    const idCliente = document.getElementById('inputIdCliente').value;
    const idMembresia = document.getElementById('selectMembresiaAsignar').value;

    if (!idCliente) {
      Swal.fire({
        icon: 'warning',
        title: 'Sin cliente',
        text: 'Debes seleccionar un cliente.'
      });
      return;
    }

    if (!idMembresia) {
      Swal.fire({
        icon: 'warning',
        title: 'Sin membresía',
        text: 'Debes seleccionar una membresía.'
      });
      return;
    }

    const formData = new FormData(form);

    Swal.fire({
      title: 'Guardando...',
      allowOutsideClick: false,
      allowEscapeKey: false,
      didOpen: () => Swal.showLoading()
    });

    try {
      const resp = await fetch(`${RUTA_MODULO}/php/asignar_membresia.php`, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      const data = await resp.json();

      if (data.success) {
        document.getElementById('modalAsignarMembresia').classList.remove('abierto');
        form.reset();

        await Swal.fire({
          icon: 'success',
          title: 'Membresía asignada',
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

  // --- 3.7 Quitar membresía a un cliente ---
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.btn-accion.eliminar[data-asignacion]');
    if (!btn) return;

    const id = btn.dataset.asignacion;

    const confirm = await Swal.fire({
      icon: 'warning',
      title: '¿Quitar membresía?',
      text: 'Se marcará como inactiva. El histórico se conserva.',
      showCancelButton: true,
      confirmButtonText: 'Sí, quitar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#d9534f',
    });

    if (!confirm.isConfirmed) return;

    Swal.fire({
      title: 'Quitando...',
      allowOutsideClick: false,
      allowEscapeKey: false,
      didOpen: () => Swal.showLoading()
    });

    try {
      const fd = new FormData();
      fd.append('id_asignacion', id);

      const resp = await fetch(`${RUTA_MODULO}/php/quitar_membresia.php`, {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      const data = await resp.json();

      if (data.success) {
        await Swal.fire({
          icon: 'success',
          title: 'Quitada',
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

  // --- 3.8 Buscador de asignaciones (tabla) ---
  document.addEventListener('input', (e) => {
    if (e.target.id !== 'buscarAsignada') return;

    const tabla = document.getElementById('tablaAsignadas');
    if (!tabla) return;

    const t = e.target.value.trim().toLowerCase();
    tabla.querySelectorAll('tbody tr').forEach(tr => {
      const coincide = !t || tr.textContent.toLowerCase().includes(t);
      tr.style.display = coincide ? '' : 'none';
    });
  });

})();