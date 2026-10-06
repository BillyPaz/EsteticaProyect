<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
        <section class="seccion" id="sec-membresias">
      <div class="panel-head">
        <div>
          <span class="eyebrow-dark">Programa de fidelidad</span>
          <h1>Membresías y <em>descuentos</em></h1>
          <p>Tablas membresias y membresiaCliente.</p>
        </div>
        <span class="chip-rol">Admin</span>
      </div>

      <div class="bloque">
        <div class="bloque-top">
          <div><h2>Membresías</h2><p class="sub">3 registradas</p></div>
          <button class="btn-oro" data-modal="modalMembresia">+ Nueva membresía</button>
        </div>

        <div class="tabla-scroll">
          <table class="tabla-panel">
            <thead>
              <tr><th>id_membresia</th><th>Código</th><th>Nombre</th><th>Porcentaje descuento</th><th>Fecha registro</th><th class="col-acc">Acciones</th></tr>
            </thead>
            <tbody>
              <tr><td>1</td><td class="principal">2002</td><td>Platino</td><td>5.00 %</td><td>10/01/2026 09:00</td>
                <td class="col-acc"><button class="btn-accion editar">Editar</button><button class="btn-accion eliminar">Eliminar</button></td></tr>
              <tr><td>2</td><td class="principal">3003</td><td>Oro</td><td>10.00 %</td><td>10/01/2026 09:05</td>
                <td class="col-acc"><button class="btn-accion editar">Editar</button><button class="btn-accion eliminar">Eliminar</button></td></tr>
              <tr><td>3</td><td class="principal">4004</td><td>Diamante</td><td>15.00 %</td><td>10/01/2026 09:10</td>
                <td class="col-acc"><button class="btn-accion editar">Editar</button><button class="btn-accion eliminar">Eliminar</button></td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="bloque">
        <div class="bloque-top">
          <div><h2>Membresías por cliente</h2><p class="sub">Tabla membresiaCliente</p></div>
          <button class="btn-oro" data-modal="modalMembresiaCliente">+ Asignar membresía</button>
        </div>

        <div class="tabla-scroll">
          <table class="tabla-panel">
            <thead>
              <tr><th>id_membresia_cliente</th><th>id_cliente</th><th>id_membresia</th><th>id_usuario</th><th>Fecha registro</th><th class="col-acc">Acciones</th></tr>
            </thead>
            <tbody>
              <tr><td>1</td><td class="principal">1 — María Fernanda López</td><td>3 — Diamante</td><td>4 — Alejandra Pineda</td><td>12/02/2026 10:44</td>
                <td class="col-acc"><button class="btn-accion editar">Editar</button><button class="btn-accion eliminar">Eliminar</button></td></tr>
              <tr><td>2</td><td class="principal">3 — Gabriela Morales</td><td>1 — Platino</td><td>4 — Alejandra Pineda</td><td>03/03/2026 15:20</td>
                <td class="col-acc"><button class="btn-accion editar">Editar</button><button class="btn-accion eliminar">Eliminar</button></td></tr>
              <tr><td>3</td><td class="principal">5 — Josué Ramírez</td><td>2 — Oro</td><td>1 — Roberto Espinoza</td><td>18/05/2026 11:02</td>
                <td class="col-acc"><button class="btn-accion editar">Editar</button><button class="btn-accion eliminar">Eliminar</button></td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>
</body>
</html>