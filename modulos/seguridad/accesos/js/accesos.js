/**
 * modulos/seguridad/accesos/js/accesos.js
 * Lógica del módulo Accesos:
 * - Crear rol
 * - Asignar rol a usuario
 */

(function () {
  'use strict';

  const RUTA_MODULO = '/Peluqueria/modulos/seguridad/accesos';

  // =====================================================
  // 1. PRECARGA del modal "Asignar rol" desde una fila
  // =====================================================
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-modal="modalAsignarRol"]');
    if (!btn) return;

    const idUsuario = btn.dataset.usuario;
    const idRol     = btn.dataset.rol;

    // Si viene desde un botón de fila (tiene data-usuario), precargamos
    if (idUsuario) {
      const selectUsuario = document.getElementById('selectUsuario');
      const selectRol     = document.getElementById('selectRol');

      if (selectUsuario) selectUsuario.value = idUsuario;
      if (selectRol && idRol && idRol !== '0') selectRol.value = idRol;
    }
  });

  // =====================================================
  // 2. SUBMIT: Crear rol
  // =====================================================
  document.addEventListener('submit', async (e) => {
    const form = e.target;
    if (form.id !== 'formNuevoRol') return;

    e.preventDefault();

    const formData = new FormData(form);

    Swal.fire({
      title: 'Guardando...',
      html: 'Por favor espera',
      allowOutsideClick: false,
      allowEscapeKey: false,
      didOpen: () => Swal.showLoading()
    });

    try {
      const resp = await fetch(`${RUTA_MODULO}/php/crear_rol.php`, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });

      const data = await resp.json();

      if (data.success) {
        const modal = form.closest('.modal-panel');
        if (modal) modal.classList.remove('abierto');
        form.reset();

        await Swal.fire({
          icon: 'success',
          title: 'Rol creado',
          text: data.message || 'El rol se creó correctamente.',
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
        text: 'No se pudo conectar con el servidor. Intenta de nuevo.'
      });
    }
  });

  // =====================================================
  // 3. SUBMIT: Asignar rol
  // =====================================================
  document.addEventListener('submit', async (e) => {
    const form = e.target;
    if (form.id !== 'formAsignarRol') return;

    e.preventDefault();

    const formData = new FormData(form);

    Swal.fire({
      title: 'Guardando...',
      html: 'Por favor espera',
      allowOutsideClick: false,
      allowEscapeKey: false,
      didOpen: () => Swal.showLoading()
    });

    try {
      const resp = await fetch(`${RUTA_MODULO}/php/asignar_rol.php`, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });

      const data = await resp.json();

      if (data.success) {
        const modal = form.closest('.modal-panel');
        if (modal) modal.classList.remove('abierto');
        form.reset();

        await Swal.fire({
          icon: 'success',
          title: 'Rol asignado',
          text: data.message || 'El rol se asignó correctamente.',
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
        text: 'No se pudo conectar con el servidor. Intenta de nuevo.'
      });
    }
  });

  // =====================================================
  // 4. Búsqueda local en la tabla
  // =====================================================
  document.addEventListener('input', (e) => {
    if (e.target.id !== 'buscarAcceso') return;

    const tabla = document.getElementById('tablaAccesos');
    if (!tabla) return;

    const texto = e.target.value.trim().toLowerCase();
    tabla.querySelectorAll('tbody tr').forEach(fila => {
      const coincide = !texto || fila.textContent.toLowerCase().includes(texto);
      fila.style.display = coincide ? '' : 'none';
    });
  });

  // =====================================================
  // 5. Recargar el módulo actual
  // =====================================================
  function recargarModulo() {
    const botonActivo = document.querySelector('.side-nav button.activo[data-seccion]');
    if (botonActivo) {
      botonActivo.click();
    } else {
      window.location.reload();
    }
  }

})();