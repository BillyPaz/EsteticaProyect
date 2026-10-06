/**
 * navegacion.js
 * Carga dinámicamente el contenido de cada módulo vía AJAX.
 * El HTML devuelto se inyecta en #contenido.
 */

document.addEventListener('DOMContentLoaded', () => {

  const contenedor = document.getElementById('contenido');
  if (!contenedor) return;

  const botones = document.querySelectorAll('.side-nav button[data-seccion]');

  // ---------- Cargar un módulo ----------
  async function cargarModulo(codigo, ruta, boton) {

    contenedor.innerHTML = '<div class="cargando-modulo"><p>Cargando...</p></div>';

    try {
      const url = '/Peluqueria/' + ruta.replace(/^\/+/, '');

      const resp = await fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });

      if (resp.status === 403) {
        contenedor.innerHTML = `
          <div class="error-modulo">
            <h2>Acceso denegado</h2>
            <p>No tienes permiso para ver este módulo.</p>
          </div>`;
        return;
      }

      if (resp.status === 404) {
        contenedor.innerHTML = `
          <div class="error-modulo">
            <h2>Módulo no encontrado</h2>
            <p>El módulo "${codigo}" aún no está creado.</p>
          </div>`;
        return;
      }

      if (!resp.ok) throw new Error('Error ' + resp.status);

      const html = await resp.text();
      contenedor.innerHTML = html;

      window.scrollTo({ top: 0, behavior: 'smooth' });

      document.dispatchEvent(new CustomEvent('moduloCargado', {
        detail: { codigo, contenedor }
      }));

    } catch (err) {
      contenedor.innerHTML = `
        <div class="error-modulo">
          <h2>Error al cargar</h2>
          <p>${err.message}</p>
        </div>`;
    }
  }

  botones.forEach(b => {
    b.addEventListener('click', () => {
      cargarModulo(b.dataset.seccion, b.dataset.ruta, b);
    });
  });

  const primero = document.querySelector('.side-nav button[data-seccion]');
  if (primero) primero.click();

});