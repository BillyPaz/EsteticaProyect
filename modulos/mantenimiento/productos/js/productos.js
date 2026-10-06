/**
 * modulos/mantenimiento/productos/js/productos.js
 * Lógica del módulo Productos:
 * - Vista embebida: crear producto (nuevo / existente)
 * - Modales: editar, stock, desactivar, categoría, presentación
 * - Filtros: tarjetas de alertas, categoría, buscador unificado
 * - Delegación de eventos: funciona aunque la vista se recargue
 */
(function () {
  'use strict';

  const RUTA_MODULO = '/Peluqueria/modulos/mantenimiento/productos';

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

  function abrirModal(id) {
    const m = document.getElementById(id);
    if (m) m.classList.add('abierto');
  }

  function cerrarModal(id) {
    const m = document.getElementById(id);
    if (m) m.classList.remove('abierto');
  }

  // =====================================================
  // 1. VISTA EMBEBIDA: CREAR PRODUCTO
  // =====================================================

  // --- 1.1 Abrir vista embebida ---
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('#btnNuevoProducto');
    if (!btn) return;

    limpiarFormProducto();
    document.getElementById('crearProductoWrap').classList.add('activo');
    document.getElementById('productosListado').style.display = 'none';
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });

  // --- 1.2 Cerrar vista embebida (volver / cancelar) ---
  document.addEventListener('click', (e) => {
    if (e.target.id !== 'btnVolverProductos' && e.target.id !== 'btnCancelarProducto') return;

    document.getElementById('crearProductoWrap').classList.remove('activo');
    document.getElementById('productosListado').style.display = '';
    limpiarFormProducto();
  });

  // --- 1.3 Cambiar entre modo "nuevo" y "existente" ---
  document.addEventListener('change', (e) => {
    if (e.target.id !== 'inputModo') return;

    const modo = e.target.value;
    const bloqueNuevo     = document.getElementById('bloqueDatosProducto');
    const bloqueExistente = document.getElementById('bloqueProductoExistente');
    const inputNombre     = document.getElementById('inputNombreProducto');
    const inputCategoria  = document.getElementById('inputCategoria');
    const inputProducto   = document.getElementById('inputProductoExistente');

    if (modo === 'nuevo') {
      bloqueNuevo.style.display = '';
      bloqueExistente.style.display = 'none';
      inputNombre.required = true;
      inputCategoria.required = true;
      inputProducto.required = false;
      inputProducto.value = '';
    } else {
      bloqueNuevo.style.display = 'none';
      bloqueExistente.style.display = '';
      inputNombre.required = false;
      inputCategoria.required = false;
      inputProducto.required = true;
      inputNombre.value = '';
      inputCategoria.value = '';
    }
  });

  // --- 1.4 Limpiar formulario ---
  function limpiarFormProducto() {
    const form = document.getElementById('formProducto');
    if (!form) return;

    form.reset();
    document.getElementById('inputModo').value = 'nuevo';

    // Resetear visibilidad de bloques
    document.getElementById('bloqueDatosProducto').style.display = '';
    document.getElementById('bloqueProductoExistente').style.display = 'none';

    // Resetear requireds
    document.getElementById('inputNombreProducto').required = true;
    document.getElementById('inputCategoria').required = true;
    document.getElementById('inputProductoExistente').required = false;

    document.getElementById('inputStockMinimo').value = 2;
  }

  // --- 1.5 Submit del formulario de crear producto ---
  document.addEventListener('submit', async (e) => {
    const form = e.target;
    if (form.id !== 'formProducto') return;

    e.preventDefault();

    const formData = new FormData(form);

    Swal.fire({
      title: 'Guardando producto...',
      allowOutsideClick: false,
      allowEscapeKey: false,
      didOpen: () => Swal.showLoading()
    });

    try {
      const resp = await fetch(`${RUTA_MODULO}/php/ingresar.php`, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });

      const data = await resp.json();

      if (data.success) {
        await Swal.fire({
          icon: 'success',
          title: 'Producto creado',
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

  // =====================================================
  // 2. EDITAR PRODUCTO
  // =====================================================

  // --- 2.1 Abrir modal de editar (carga datos vía AJAX) ---
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.btn-accion.editar[data-pp]');
    if (!btn) return;

    const id = btn.dataset.pp;

    Swal.fire({
      title: 'Cargando...',
      allowOutsideClick: false,
      allowEscapeKey: false,
      didOpen: () => Swal.showLoading()
    });

    try {
      const resp = await fetch(`${RUTA_MODULO}/php/obtener.php?id=${encodeURIComponent(id)}`);
      const data = await resp.json();

      if (!data.success) {
        Swal.fire({ icon: 'error', title: 'Error', text: data.message });
        return;
      }

      const p = data.producto;

      document.getElementById('editIdPp').value            = p.id_presentacion_prod;
      document.getElementById('editNombreProducto').value  = p.nombreProducto;
      document.getElementById('editCategoria').value       = p.id_categoria || '';
      document.getElementById('editObservaciones').value   = p.observaciones || '';
      document.getElementById('editPrecioCompra').value    = p.precioCompra;
      document.getElementById('editPrecioVenta').value     = p.precioVenta;
      document.getElementById('editStockMinimo').value     = p.stockMinimo;
      document.getElementById('editCodigoBarra').value     = p.codigoBarra || '';

      Swal.close();
      abrirModal('modalEditarProducto');

    } catch (err) {
      Swal.fire({
        icon: 'error',
        title: 'Error de conexión',
        text: 'No se pudieron cargar los datos del producto.'
      });
    }
  });

  // --- 2.2 Submit de editar producto ---
  document.addEventListener('submit', async (e) => {
    const form = e.target;
    if (form.id !== 'formEditarProducto') return;

    e.preventDefault();

    const formData = new FormData(form);

    Swal.fire({
      title: 'Guardando cambios...',
      allowOutsideClick: false,
      allowEscapeKey: false,
      didOpen: () => Swal.showLoading()
    });

    try {
      const resp = await fetch(`${RUTA_MODULO}/php/editar.php`, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });

      const data = await resp.json();

      if (data.success) {
        cerrarModal('modalEditarProducto');
        await Swal.fire({
          icon: 'success',
          title: 'Producto actualizado',
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
        text: data.message || 'No se pudo actualizar.'
      });

    } catch (err) {
      Swal.fire({
        icon: 'error',
        title: 'Error de conexión',
        text: 'No se pudo conectar con el servidor.'
      });
    }
  });

  // =====================================================
  // 3. MOVIMIENTO DE STOCK (ENTRADA / AJUSTE)
  // =====================================================

  // --- 3.1 Abrir modal de stock ---
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-accion.stock[data-pp]');
    if (!btn) return;

    const id     = btn.dataset.pp;
    const nombre = btn.dataset.nombre || '';

    document.getElementById('stockIdPp').value = id;
    document.getElementById('stockSubtitulo').textContent = 'Producto: ' + nombre;
    document.getElementById('formStock').reset();
    document.getElementById('stockIdPp').value = id;
    document.getElementById('stockTipo').value = 'entrada';
    document.getElementById('stockMotivoOtroWrap').style.display = 'none';
    actualizarCamposStock();

    abrirModal('modalStock');
  });

  // --- 3.2 Cambiar tipo de movimiento (entrada/ajuste) ---
  document.addEventListener('change', (e) => {
    if (e.target.id !== 'stockTipo') return;
    actualizarCamposStock();
  });

  function actualizarCamposStock() {
    const tipo = document.getElementById('stockTipo').value;
    const camposEntrada = document.querySelectorAll('.campo-stock-entrada');
    const ayudaCantidad = document.getElementById('stockAyudaCantidad');

    if (tipo === 'entrada') {
      camposEntrada.forEach(c => c.style.display = '');
      ayudaCantidad.textContent = 'Cantidad que ingresa (mayor a 0).';
    } else {
      camposEntrada.forEach(c => c.style.display = 'none');
      ayudaCantidad.textContent = 'Positivo para sumar, negativo para restar.';
    }
  }

  // --- 3.3 Cambiar motivo (mostrar/ocultar "otro") ---
  document.addEventListener('change', (e) => {
    if (e.target.id !== 'stockMotivoSelect') return;

    const wrap = document.getElementById('stockMotivoOtroWrap');
    if (e.target.value === 'otro') {
      wrap.style.display = '';
    } else {
      wrap.style.display = 'none';
      document.getElementById('stockMotivoOtro').value = '';
    }
  });

  // --- 3.4 Submit movimiento de stock ---
  document.addEventListener('submit', async (e) => {
    const form = e.target;
    if (form.id !== 'formStock') return;

    e.preventDefault();

    const formData = new FormData(form);

    Swal.fire({
      title: 'Registrando...',
      allowOutsideClick: false,
      allowEscapeKey: false,
      didOpen: () => Swal.showLoading()
    });

    try {
      const resp = await fetch(`${RUTA_MODULO}/php/movimiento_stock.php`, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });

      const data = await resp.json();

      if (data.success) {
        cerrarModal('modalStock');
        await Swal.fire({
          icon: 'success',
          title: 'Movimiento registrado',
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
        text: data.message || 'No se pudo registrar el movimiento.'
      });

    } catch (err) {
      Swal.fire({
        icon: 'error',
        title: 'Error de conexión',
        text: 'No se pudo conectar con el servidor.'
      });
    }
  });

    // =====================================================
  // 4. ACTIVAR / DESACTIVAR PRODUCTO
  // =====================================================

  // --- 4.1 Click en botón "Desactivar" o "Activar" ---
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.btn-accion.desactivar[data-pp], .btn-accion.activar[data-pp]');
    if (!btn) return;

    const id     = btn.dataset.pp;
    const nombre = btn.dataset.nombre || '';
    const accion = btn.dataset.accion; // 'activar' | 'desactivar'

    const esActivar = (accion === 'activar');

    const confirm = await Swal.fire({
      icon: esActivar ? 'question' : 'warning',
      title: esActivar ? '¿Activar producto?' : '¿Desactivar producto?',
      text: esActivar
        ? `"${nombre}" volverá a estar disponible en el catálogo.`
        : `"${nombre}" dejará de aparecer para la venta, pero no se elimina. Podrás reactivarlo luego.`,
      showCancelButton: true,
      confirmButtonText: esActivar ? 'Sí, activar' : 'Sí, desactivar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: esActivar ? '#28a745' : '#d9534f'
    });

    if (!confirm.isConfirmed) return;

    Swal.fire({
      title: esActivar ? 'Activando...' : 'Desactivando...',
      allowOutsideClick: false,
      allowEscapeKey: false,
      didOpen: () => Swal.showLoading()
    });

    try {
      const fd = new FormData();
      fd.append('id_presentacion_prod', id);
      fd.append('accion', accion);

      const resp = await fetch(`${RUTA_MODULO}/php/cambiar_estado.php`, {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      const data = await resp.json();

      if (data.success) {
        await Swal.fire({
          icon: 'success',
          title: esActivar ? 'Producto activado' : 'Producto desactivado',
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
        text: data.message || 'No se pudo cambiar el estado.'
      });

    } catch (err) {
      Swal.fire({
        icon: 'error',
        title: 'Error de conexión',
        text: 'No se pudo conectar con el servidor.'
      });
    }
  });

  // =====================================================
  // 5. MODAL RÁPIDO: NUEVA CATEGORÍA
  // =====================================================

  // --- 5.1 Abrir modal de categoría ---
  document.addEventListener('click', (e) => {
    if (e.target.id !== 'btnNuevaCategoria') return;

    document.getElementById('formCategoria').reset();
    abrirModal('modalCategoria');
  });

  // --- 5.2 Submit de categoría ---
  document.addEventListener('submit', async (e) => {
    const form = e.target;
    if (form.id !== 'formCategoria') return;

    e.preventDefault();

    const formData = new FormData(form);

    Swal.fire({
      title: 'Guardando categoría...',
      allowOutsideClick: false,
      allowEscapeKey: false,
      didOpen: () => Swal.showLoading()
    });

    try {
      const resp = await fetch(`${RUTA_MODULO}/php/ingresar_categoria.php`, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });

      const data = await resp.json();

      if (data.success) {
        // Agregar la opción al select de categorías del form de crear producto
        const select = document.getElementById('inputCategoria');
        if (select) {
          const opt = document.createElement('option');
          opt.value = data.categoria.id_categoria;
          opt.textContent = data.categoria.nombreCategoria;
          select.appendChild(opt);
          select.value = data.categoria.id_categoria;
        }

        cerrarModal('modalCategoria');

        Swal.fire({
          icon: 'success',
          title: 'Categoría creada',
          text: data.message,
          timer: 1500,
          showConfirmButton: false
        });
        return;
      }

      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: data.message || 'No se pudo crear la categoría.'
      });

    } catch (err) {
      Swal.fire({
        icon: 'error',
        title: 'Error de conexión',
        text: 'No se pudo conectar con el servidor.'
      });
    }
  });

  // =====================================================
  // 6. MODAL RÁPIDO: NUEVA PRESENTACIÓN
  // =====================================================

  // --- 6.1 Abrir modal de presentación ---
  document.addEventListener('click', (e) => {
    if (e.target.id !== 'btnNuevaPresentacion') return;

    document.getElementById('formPresentacion').reset();
    abrirModal('modalPresentacion');
  });

  // --- 6.2 Submit de presentación ---
  document.addEventListener('submit', async (e) => {
    const form = e.target;
    if (form.id !== 'formPresentacion') return;

    e.preventDefault();

    const formData = new FormData(form);

    Swal.fire({
      title: 'Guardando presentación...',
      allowOutsideClick: false,
      allowEscapeKey: false,
      didOpen: () => Swal.showLoading()
    });

    try {
      const resp = await fetch(`${RUTA_MODULO}/php/ingresar_presentacion.php`, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });

      const data = await resp.json();

      if (data.success) {
        const select = document.getElementById('inputPresentacion');
        if (select) {
          const opt = document.createElement('option');
          opt.value = data.presentacion.id_presentacion;
          opt.textContent = data.presentacion.nombrePresentacion;
          select.appendChild(opt);
          select.value = data.presentacion.id_presentacion;
        }

        cerrarModal('modalPresentacion');

        Swal.fire({
          icon: 'success',
          title: 'Presentación creada',
          text: data.message,
          timer: 1500,
          showConfirmButton: false
        });
        return;
      }

      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: data.message || 'No se pudo crear la presentación.'
      });

    } catch (err) {
      Swal.fire({
        icon: 'error',
        title: 'Error de conexión',
        text: 'No se pudo conectar con el servidor.'
      });
    }
  });

  // =====================================================
  // 7. FILTROS: TARJETAS DE ALERTA
  // =====================================================

  document.addEventListener('click', (e) => {
    const card = e.target.closest('.metrica.clicable[data-filtro]');
    if (!card) return;

    const filtro = card.dataset.filtro;

    // Toggle activo
    const yaActiva = card.classList.contains('activa');

    document.querySelectorAll('.metrica.clicable').forEach(c => c.classList.remove('activa'));

    if (yaActiva) {
      aplicarFiltros('', '', '');
      return;
    }

    card.classList.add('activa');

    // Resetear otros filtros visuales
    document.getElementById('buscarProducto').value = '';
    document.getElementById('filtroCategoria').value = '';

    // Aplicar filtro de tarjeta
    aplicarFiltroTarjeta(filtro);
  });

  function aplicarFiltroTarjeta(filtro) {
    const tabla = document.getElementById('tablaProductos');
    if (!tabla) return;

    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);

    const filas = tabla.querySelectorAll('tbody tr');

    filas.forEach(tr => {
      const stock       = parseInt(tr.dataset.stock || 0, 10);
      const stockMin    = parseInt(tr.dataset.stockmin || 0, 10);
      const activo      = tr.dataset.activo === '1';
      const vencimiento = tr.dataset.vencimiento || '';

      let mostrar = false;

      if (!activo) {
        mostrar = false;
      } else if (filtro === 'stock-bajo') {
        mostrar = stock <= stockMin;
      } else if (filtro === 'por-vencer') {
        if (!vencimiento) {
          mostrar = false;
        } else {
          const fv = new Date(vencimiento + 'T00:00:00');
          const limite = new Date(hoy);
          limite.setDate(limite.getDate() + 30);
          mostrar = fv >= hoy && fv <= limite;
        }
      } else if (filtro === 'mas-vendidos' || filtro === 'menos-vendidos') {
        // TODO: cuando ventas esté integrado, filtrar por ranking real
        mostrar = false;
      }

      tr.style.display = mostrar ? '' : 'none';
    });
  }

  // =====================================================
  // 8. FILTROS: BUSCADOR Y CATEGORÍA
  // =====================================================

  function aplicarFiltros() {
    const texto     = document.getElementById('buscarProducto').value.trim().toLowerCase();
    const categoria = document.getElementById('filtroCategoria').value;

    const tabla = document.getElementById('tablaProductos');
    if (!tabla) return;

    // Quitar filtro de tarjeta al usar buscador o categoría
    document.querySelectorAll('.metrica.clicable').forEach(c => c.classList.remove('activa'));

    const filas = tabla.querySelectorAll('tbody tr');

    filas.forEach(tr => {
      const nombre  = (tr.querySelector('.principal')?.textContent || '').toLowerCase();
      const codigo  = (tr.children[0]?.textContent || '').toLowerCase();
      const cat     = tr.dataset.categoria || '';

      let mostrar = true;

      if (texto && !nombre.includes(texto) && !codigo.includes(texto)) {
        mostrar = false;
      }
      if (categoria && cat !== categoria) {
        mostrar = false;
      }

      tr.style.display = mostrar ? '' : 'none';
    });
  }

  document.addEventListener('input', (e) => {
    if (e.target.id !== 'buscarProducto') return;
    aplicarFiltros();
  });

  document.addEventListener('change', (e) => {
    if (e.target.id !== 'filtroCategoria') return;
    aplicarFiltros();
  });

  // =====================================================
  // 9. CERRAR MODALES CON [data-cerrar] O CLICK FUERA
  // =====================================================
  document.addEventListener('click', (e) => {
    if (e.target.closest('[data-cerrar]')) {
      const modal = e.target.closest('.modal-panel');
      if (modal) modal.classList.remove('abierto');
      return;
    }

    // Click en el fondo del modal (fuera de .modal-caja)
    if (e.target.classList.contains('modal-panel')) {
      e.target.classList.remove('abierto');
    }
  });

  // Cerrar con ESC
  document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return;
    document.querySelectorAll('.modal-panel.abierto').forEach(m => m.classList.remove('abierto'));
  });

  // =====================================================
  // 10. REINICIALIZAR AL CARGAR MÓDULO
  // =====================================================
  document.addEventListener('moduloCargado', (e) => {
    if (e.detail.codigo !== 'productos') return;

    // Cerrar modales que hayan quedado abiertos
    document.querySelectorAll('.modal-panel.abierto').forEach(m => m.classList.remove('abierto'));

    // Resetear vista embebida
    const wrap = document.getElementById('crearProductoWrap');
    if (wrap) wrap.classList.remove('activo');

    const listado = document.getElementById('productosListado');
    if (listado) listado.style.display = '';
  });

})();