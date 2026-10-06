/**
 * filtros.js
 * Filtros de tablas. Se reinicializa cada vez que se carga un módulo por AJAX.
 * Por ahora solo maneja el filtro de "Citas" (tabla + conteo de barberos).
 * Iremos agregando más filtros específicos aquí.
 */

function inicializarFiltros() {

  // ---------- Filtro de citas por barbero + búsqueda ----------
  const tablaCitas = document.getElementById('tablaCitas');
  if (tablaCitas) {
    const filas   = [...tablaCitas.querySelectorAll('tbody tr')];
    const filtro  = document.getElementById('filtroBarbero');
    const buscar  = document.getElementById('buscarCita');

    function aplicar() {
      const b = filtro?.value || '';
      const t = buscar?.value.trim().toLowerCase() || '';

      filas.forEach(f => {
        const coincideB = !b || f.dataset.barbero === b;
        const coincideT = !t || f.textContent.toLowerCase().includes(t);
        f.style.display = (coincideB && coincideT) ? '' : 'none';
      });

      // Actualizar contadores
      const visibles = filas.filter(f => f.style.display !== 'none');
      const cuenta = n => visibles.filter(f => f.dataset.barbero === n).length;

      const cOtto  = document.getElementById('cortesOtto');
      const cKevin = document.getElementById('cortesKevin');
      const cSin   = document.getElementById('cortesSin');

      if (cOtto)  cOtto.textContent  = cuenta('Otto Mérida');
      if (cKevin) cKevin.textContent = cuenta('Kevin Solís');
      if (cSin)   cSin.textContent   = cuenta('Nada');
    }

    filtro?.addEventListener('change', aplicar);
    buscar?.addEventListener('input', aplicar);
    aplicar();
  }

  // ---------- Buscador de citas pagas ----------
  const tablaPagas = document.getElementById('tablaCitasPagas');
  const buscarPaga = document.getElementById('buscarCitaPaga');
  if (tablaPagas && buscarPaga) {
    const filasPaga = [...tablaPagas.querySelectorAll('tbody tr')];
    buscarPaga.addEventListener('input', () => {
      const t = buscarPaga.value.trim().toLowerCase();
      filasPaga.forEach(f => {
        f.style.display = (!t || f.textContent.toLowerCase().includes(t)) ? '' : 'none';
      });
    });
  }

}

// Inicializa al cargar la página del dashboard
document.addEventListener('DOMContentLoaded', inicializarFiltros);

// Reinicializa cuando se carga un módulo por AJAX
document.addEventListener('moduloCargado', inicializarFiltros);