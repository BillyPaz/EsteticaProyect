<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
     <section class="seccion" id="sec-inventario">
      <div class="panel-head">
        <div>
          <span class="eyebrow-dark">Control de stock</span>
          <h1>Inventario y <em>stock</em></h1>
          <p>Tablas presentacion y presentacionProd — existencias y precios por presentación.</p>
        </div>
        <span class="chip-rol">Admin</span>
      </div>

      <div class="metricas">
        <div class="metrica"><strong>4</strong><span>Presentaciones activas</span></div>
        <div class="metrica"><strong>2</strong><span>Stock bajo</span></div>
        <div class="metrica"><strong>Q 4,860</strong><span>Valor inventario</span></div>
      </div>

      <div class="bloque">
        <div class="bloque-top">
          <div><h2>Presentaciones por producto</h2><p class="sub">Tabla presentacionProd</p></div>
          <button class="btn-oro" data-modal="modalPresentacionProd">+ Nueva presentación</button>
        </div>
        <div class="tabla-scroll">
          <table class="tabla-panel">
            <thead>
              <tr><th>id_presentacion_prod</th><th>id_producto</th><th>id_presentacion</th><th>Precio compra</th><th>Precio venta</th><th>Stock</th><th>Fecha registro</th><th>Activo</th><th class="col-acc">Acciones</th></tr>
            </thead>
            <tbody>
              <tr><td>1</td><td class="principal">1 — Shampoo Keratina</td><td>1 — Botella 500ml</td><td>Q 95.00</td><td>Q 145.00</td><td><span class="stock-num">24</span></td><td>05/01/2026 10:20</td><td><span class="badge activo">Activo</span></td>
                <td class="col-acc"><button class="btn-accion editar">Editar</button><button class="btn-accion eliminar">Eliminar</button></td></tr>
              <tr><td>2</td><td class="principal">2 — Tinte profesional</td><td>2 — Caja 60ml</td><td>Q 60.00</td><td>Q 98.00</td><td><span class="stock-num">6</span></td><td>05/01/2026 10:22</td><td><span class="badge activo">Activo</span></td>
                <td class="col-acc"><button class="btn-accion editar">Editar</button><button class="btn-accion eliminar">Eliminar</button></td></tr>
              <tr><td>3</td><td class="principal">3 — Cera moldeadora</td><td>3 — Tarro 100g</td><td>Q 45.00</td><td>Q 75.00</td><td><span class="stock-num">31</span></td><td>05/01/2026 10:25</td><td><span class="badge activo">Activo</span></td>
                <td class="col-acc"><button class="btn-accion editar">Editar</button><button class="btn-accion eliminar">Eliminar</button></td></tr>
              <tr><td>4</td><td class="principal">4 — Mascarilla facial</td><td>4 — Sobre 30g</td><td>Q 70.00</td><td>Q 120.00</td><td><span class="stock-num">4</span></td><td>05/01/2026 10:28</td><td><span class="badge activo">Activo</span></td>
                <td class="col-acc"><button class="btn-accion editar">Editar</button><button class="btn-accion eliminar">Eliminar</button></td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>
    
</body>
</html>