/**
 * sidebar.js
 * - Colapsar / expandir el sidebar (persistente)
 * - Acordeón de submenús (uno abierto a la vez)
 * - Marcar la opción activa
 */

document.addEventListener('DOMContentLoaded', () => {

  const panelSide   = document.getElementById('panelSide');
  const btnColapsar = document.getElementById('btnColapsar');
  const CLAVE       = 'europaSidebarColapsado';

  if (!panelSide || !btnColapsar) return;

  // ---------- Colapsar / expandir ----------
  function aplicar(colapsado) {
    panelSide.classList.toggle('colapsado', colapsado);
    btnColapsar.setAttribute('aria-label', colapsado ? 'Expandir menú' : 'Contraer menú');
  }

  aplicar(localStorage.getItem(CLAVE) === '1');

  btnColapsar.addEventListener('click', () => {
    const colapsado = !panelSide.classList.contains('colapsado');
    aplicar(colapsado);
    localStorage.setItem(CLAVE, colapsado ? '1' : '0');
    // Al colapsar, cerramos submenús abiertos
    if (colapsado) {
      document.querySelectorAll('.side-grupo.abierto').forEach(g => g.classList.remove('abierto'));
    }
  });

  // ---------- Acordeón de submenús ----------
  document.querySelectorAll('.side-toggle').forEach(t => {
    t.addEventListener('click', () => {
      const grupo = t.parentElement;
      const estabaAbierto = grupo.classList.contains('abierto');

      document.querySelectorAll('.side-grupo').forEach(g => g.classList.remove('abierto'));
      if (!estabaAbierto) grupo.classList.add('abierto');
    });
  });

  // ---------- Marcar botón activo (solo visual, al hacer clic) ----------
  document.querySelectorAll('.side-nav button[data-seccion]').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.side-nav button').forEach(b => b.classList.remove('activo'));
      btn.classList.add('activo');

      // Si el botón está dentro de un submenú, dejamos ese grupo abierto
      const grupoPadre = btn.closest('.side-grupo');
      document.querySelectorAll('.side-grupo').forEach(g => {
        g.classList.toggle('abierto', g === grupoPadre);
      });
    });
  });

});