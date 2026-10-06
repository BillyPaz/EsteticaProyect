<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
        <section class="seccion" id="sec-emp-horario">
      <div class="panel-head">
        <div>
          <span class="eyebrow-dark">Empleados</span>
          <h1>Horario de <em>empleado</em></h1>
          <p>Tabla horarios_empleado — día de la semana, entrada y salida.</p>
        </div>
        <span class="chip-rol">Admin</span>
      </div>

      <div class="bloque">
        <div class="bloque-top">
          <div><h2>Horarios asignados</h2></div>
          <div class="acciones-top">
            <input type="search" class="buscador" placeholder="Buscar empleado..." />
            <button class="btn-oro" data-modal="modalHorarioEmp">+ Nuevo horario</button>
          </div>
        </div>

        <div class="tabla-scroll">
          <table class="tabla-panel">
            <thead>
              <tr>
                <th>id_empleado</th><th>Día semana</th>
                <th>Hora entrada</th><th>Hora salida</th><th>Estado</th><th class="col-acc">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <td class="principal">2 — Otto Mérida</td><td>1 — Lunes</td><td>08:00</td><td>17:00</td><td><span class="badge activo">Activo</span></td>
                <td class="col-acc"><button class="btn-accion editar">Editar</button><button class="btn-accion eliminar">Eliminar</button></td></tr>
              <td class="principal">2 — Otto Mérida</td><td>2 — Martes</td><td>08:00</td><td>17:00</td><td><span class="badge activo">Activo</span></td>
                <td class="col-acc"><button class="btn-accion editar">Editar</button><button class="btn-accion eliminar">Eliminar</button></td></tr>
              <td class="principal">3 — Kevin Solís</td><td>3 — Miércoles</td><td>10:00</td><td>19:00</td><td><span class="badge activo">Activo</span></td>
                <td class="col-acc"><button class="btn-accion editar">Editar</button><button class="btn-accion eliminar">Eliminar</button></td></tr>
              <td class="principal">3 — Kevin Solís</td><td>6 — Sábado</td><td>09:00</td><td>15:00</td><td><span class="badge inactivo">Inactivo</span></td>
                <td class="col-acc"><button class="btn-accion editar">Editar</button><button class="btn-accion eliminar">Eliminar</button></td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>
</body>
</html>