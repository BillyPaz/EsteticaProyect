<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
        <section class="seccion" id="sec-horario-estetica">
      <div class="panel-head">
        <div>
          <span class="eyebrow-dark">Configuración</span>
          <h1>Horario de la <em>estética</em></h1>
          <p>Tabla horarios_estetica — apertura y cierre por día.</p>
        </div>
        <span class="chip-rol">Admin</span>
      </div>

      <div class="bloque">
        <div class="bloque-top">
          <div><h2>Horarios de atención</h2></div>
          <button class="btn-oro" data-modal="modalHorarioEst">+ Nuevo horario</button>
        </div>
        <div class="tabla-scroll">
          <table class="tabla-panel">
            <thead>
              <tr><th>id_horario</th><th>Día semana</th><th>Hora apertura</th><th>Hora cierre</th><th>Estado</th><th class="col-acc">Acciones</th></tr>
            </thead>
            <tbody>
              <tr><td>1</td><td class="principal">1 — Lunes</td><td>08:00</td><td>18:00</td><td><span class="badge activo">Activo</span></td>
                <td class="col-acc"><button class="btn-accion editar">Editar</button><button class="btn-accion eliminar">Eliminar</button></td></tr>
              <tr><td>2</td><td class="principal">2 — Martes</td><td>08:00</td><td>18:00</td><td><span class="badge activo">Activo</span></td>
                <td class="col-acc"><button class="btn-accion editar">Editar</button><button class="btn-accion eliminar">Eliminar</button></td></tr>
              <tr><td>3</td><td class="principal">6 — Sábado</td><td>09:00</td><td>16:00</td><td><span class="badge activo">Activo</span></td>
                <td class="col-acc"><button class="btn-accion editar">Editar</button><button class="btn-accion eliminar">Eliminar</button></td></tr>
              <tr><td>4</td><td class="principal">7 — Domingo</td><td>00:00</td><td>00:00</td><td><span class="badge inactivo">Inactivo</span></td>
                <td class="col-acc"><button class="btn-accion editar">Editar</button><button class="btn-accion eliminar">Eliminar</button></td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>
</body>
</html>