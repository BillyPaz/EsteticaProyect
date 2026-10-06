/**
 * modulos/empleados/registrar/js/empleados.js
 * Lógica del módulo "Registrar empleado":
 * - Enviar el formulario de "Nuevo usuario" por AJAX
 * - Mostrar mensajes con SweetAlert2
 * - Recargar el módulo tras éxito
 */

(function () {
  'use strict';

  // =====================================================
  // URL base del módulo (para no repetir rutas)
  // =====================================================
  const RUTA_MODULO = '/Peluqueria/modulos/empleados/registrar';

  // =====================================================
  // 1. SUBMIT del formulario "Nuevo usuario"
  // =====================================================
  document.addEventListener('submit', async (e) => {
    const form = e.target;

    // Solo manejamos el formulario de nuevo usuario
    if (form.id !== 'formNuevoUsuario') return;

    e.preventDefault();

    // Recoger datos del formulario
    const formData = new FormData(form);

    // Mostrar loading mientras se procesa
    Swal.fire({
      title: 'Guardando...',
      html: 'Por favor espera',
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

      // La respuesta SIEMPRE es JSON (incluso en 403)
      const data = await resp.json();

      // ----- Éxito -----
      if (data.success) {
        // Cerrar modal
        const modal = form.closest('.modal-panel');
        if (modal) modal.classList.remove('abierto');

        // Limpiar formulario
        form.reset();

        // Mostrar alerta de éxito
        await Swal.fire({
          icon: 'success',
          title: 'Usuario creado',
          text: data.message || 'El empleado se registró correctamente.',
          timer: 1800,
          showConfirmButton: false
        });

        // Recargar el módulo para que la tabla muestre el nuevo usuario
        recargarModulo();
        return;
      }

      // ----- Error controlado -----
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
  // 2. Recargar el módulo actual (reenvía el clic al sidebar)
  // =====================================================
  function recargarModulo() {
    const botonActivo = document.querySelector('.side-nav button.activo[data-seccion]');
    if (botonActivo) {
      botonActivo.click();
    } else {
      // Fallback: recargar la página completa
      window.location.reload();
    }
  }

  // =====================================================
  // 3. Búsqueda local en la tabla de usuarios
  // =====================================================
  document.addEventListener('input', (e) => {
    if (e.target.id !== 'buscarUsuario') return;

    const tabla = document.getElementById('tablaUsuarios');
    if (!tabla) return;

    const texto = e.target.value.trim().toLowerCase();
    const filas = tabla.querySelectorAll('tbody tr');

    filas.forEach(fila => {
      const coincide = !texto || fila.textContent.toLowerCase().includes(texto);
      fila.style.display = coincide ? '' : 'none';
    });
  });

})();