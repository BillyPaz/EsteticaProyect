/**
 * modales.js
 * Abre / cierra modales de forma global usando delegación de eventos.
 * Funciona con modales presentes en la página y con modales cargados vía AJAX.
 */

document.addEventListener('click', (e) => {

  // 1. Abrir modal (botón con data-modal="idModal")
  const abridor = e.target.closest('[data-modal]');
  if (abridor) {
    const modal = document.getElementById(abridor.dataset.modal);
    if (modal) {
      modal.classList.add('abierto');
      // Evitamos que el mismo clic cierre el modal recién abierto
      e.stopPropagation();
      return;
    }
  }

  // 2. Cerrar al hacer clic fuera de la caja del modal
  if (e.target.classList.contains('modal-panel')) {
    e.target.classList.remove('abierto');
    return;
  }

  // 3. Cerrar con botón [data-cerrar]
  const cerrar = e.target.closest('[data-cerrar]');
  if (cerrar) {
    const modal = cerrar.closest('.modal-panel');
    if (modal) modal.classList.remove('abierto');
  }

});