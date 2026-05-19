<?php
$pageTitle = 'Cotizador';
require_once 'includes/header.php';
requireLogin();
$db = getDB();
$pasteles = $db->query("SELECT * FROM pasteles WHERE disponible = 1")->fetchAll();
$base_id = intval($_GET['base'] ?? 0);
?>

<div style="background:var(--blush);padding:3rem 0 1.5rem;border-bottom:1px solid var(--border)">
  <div class="container">
    <div class="section-subtitle">Personaliza</div>
    <h1 class="section-title">Cotiza tu Pastel</h1>
    <p style="color:var(--text-light)">Diseña el pastel de tus sueños. Ajusta cada detalle y conoce el precio al instante.</p>
  </div>
</div>

<section class="cotizador-section">
  <div class="container">
    <div class="row g-4">
      <!-- FORMULARIO -->
      <div class="col-lg-8">
        <form id="cotizador-form" onsubmit="enviarCotizacion(event)" class="form-mc">

          <!-- BASE -->
          <div class="cotizador-card mb-4">
            <h5 style="font-family:'Playfair Display',serif;color:var(--brown-dark);margin-bottom:1.5rem">
              <i class="fa fa-birthday-cake me-2" style="color:var(--rose-deep)"></i>Base del pastel
            </h5>
            <div class="mb-3">
              <label>¿Basado en alguno de nuestros pasteles? <span style="color:var(--text-light)">(opcional)</span></label>
              <select name="id_pastel" class="form-select">
                <option value="">— Seleccionar base —</option>
                <?php foreach ($pasteles as $p): ?>
                  <option value="<?= $p['id_pastel'] ?>" <?= $base_id == $p['id_pastel'] ? 'selected' : '' ?>>
                    <?= sanitize($p['nombre']) ?> — Desde $<?= number_format($p['precio_base'],2) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <!-- PERSONAS Y TAMAÑO -->
          <div class="cotizador-card mb-4">
            <h5 style="font-family:'Playfair Display',serif;color:var(--brown-dark);margin-bottom:1.5rem">
              <i class="fa fa-users me-2" style="color:var(--rose-deep)"></i>Tamaño y personas
            </h5>
            <div class="mb-4">
              <label>¿Para cuántas personas? <span id="personas-val" style="color:var(--rose-deep);font-weight:600">10</span></label>
              <input type="range" id="personas" name="personas" min="5" max="100" value="10" class="form-range" style="accent-color:var(--rose-deep)">
              <div class="d-flex justify-content-between" style="font-size:0.75rem;color:var(--text-light)">
                <span>5 personas</span><span>100 personas</span>
              </div>
            </div>
            <div class="mb-2"><label>Tamaño</label></div>
            <div class="row g-2">
              <?php foreach ([
                ['pequeño','Pequeño','Hasta 10 personas','🍰'],
                ['mediano','Mediano','10-20 personas','🎂'],
                ['grande','Grande','20-40 personas','🎁'],
                ['extra','Extra grande','40+ personas','🏆'],
              ] as $t): ?>
                <div class="col-6 col-md-3">
                  <div class="option-btn <?= $t[0]==='mediano'?'selected':'' ?>" data-grupo="tamano" onclick="seleccionarOpcion('tamano','<?= $t[0] ?>',this)">
                    <i><?= $t[3] ?></i>
                    <div style="font-weight:600;font-size:0.9rem"><?= $t[1] ?></div>
                    <div style="font-size:0.75rem;color:var(--text-light)"><?= $t[2] ?></div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- FORMA -->
          <div class="cotizador-card mb-4">
            <h5 style="font-family:'Playfair Display',serif;color:var(--brown-dark);margin-bottom:1.5rem">
              <i class="fa fa-shapes me-2" style="color:var(--rose-deep)"></i>Forma del pastel
            </h5>
            <div class="row g-3">
              <div class="col-6">
                <div class="option-btn selected" data-grupo="forma" onclick="seleccionarOpcion('forma','redondo',this)">
                  <i>⭕</i>
                  <div style="font-weight:600">Redondo</div>
                  <div style="font-size:0.75rem;color:var(--text-light)">Clásico</div>
                </div>
              </div>
              <div class="col-6">
                <div class="option-btn" data-grupo="forma" onclick="seleccionarOpcion('forma','cuadrado',this)">
                  <i>⬛</i>
                  <div style="font-weight:600">Cuadrado</div>
                  <div style="font-size:0.75rem;color:var(--text-light)">+$50</div>
                </div>
              </div>
            </div>
          </div>

          <!-- BETÚN -->
          <div class="cotizador-card mb-4">
            <h5 style="font-family:'Playfair Display',serif;color:var(--brown-dark);margin-bottom:1.5rem">
              <i class="fa fa-paint-brush me-2" style="color:var(--rose-deep)"></i>Tipo de betún
            </h5>
            <div class="row g-2">
              <?php foreach ([
                ['fondant','Fondant','Liso y perfecto para decorar','🎨'],
                ['crema_mantequilla','Crema Mantequilla','Suave y delicioso','🧈'],
                ['chantilly','Chantilly','Ligero y esponjoso','☁️'],
                ['ganache','Ganache','Intenso chocolate','🍫'],
              ] as $b): ?>
                <div class="col-6 col-md-3">
                  <div class="option-btn <?= $b[0]==='fondant'?'selected':'' ?>" data-grupo="betun" onclick="seleccionarOpcion('betun','<?= $b[0] ?>',this)">
                    <i><?= $b[3] ?></i>
                    <div style="font-weight:600;font-size:0.85rem"><?= $b[1] ?></div>
                    <div style="font-size:0.72rem;color:var(--text-light)"><?= $b[2] ?></div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- COLORES Y DECORACIÓN -->
          <div class="cotizador-card mb-4">
            <h5 style="font-family:'Playfair Display',serif;color:var(--brown-dark);margin-bottom:1.5rem">
              <i class="fa fa-palette me-2" style="color:var(--rose-deep)"></i>Colores y decoración
            </h5>
            <div class="mb-3">
              <label>Colores deseados</label>
              <input type="text" id="colores" name="colores" class="form-control" placeholder="Ej: Rosa pastel, blanco, dorado...">
            </div>
            <div class="mb-3">
              <label>Mensaje en el pastel <span style="color:var(--text-light)">(opcional)</span></label>
              <input type="text" id="mensaje_deco" name="mensaje" class="form-control" placeholder="Ej: ¡Feliz cumpleaños María!">
            </div>
            <div class="mb-3">
              <label>Descripción de la decoración</label>
              <textarea id="descripcion_deco" name="decoracion" class="form-control" rows="3" placeholder="Describe cómo quieres que se vea tu pastel..."></textarea>
            </div>
            <div class="form-check" style="padding-left:0">
              <div style="display:flex;align-items:center;gap:1rem;background:var(--blush);border-radius:12px;padding:1rem 1.2rem;cursor:pointer" onclick="document.getElementById('decoracion_3d').click()">
                <div>
                  <div style="font-weight:600;color:var(--brown-dark)">🌟 Decoración 3D</div>
                  <div style="font-size:0.82rem;color:var(--text-light)">Figuras y elementos tridimensionales (+$150)</div>
                </div>
                <input class="form-check-input ms-auto" type="checkbox" id="decoracion_3d" name="decoracion_3d" style="width:22px;height:22px;accent-color:var(--rose-deep);cursor:pointer" onclick="event.stopPropagation()">
              </div>
            </div>
          </div>

          <button type="submit" class="btn-mc-primary w-100" style="padding:1rem;font-size:1rem;border-radius:14px">
            <i class="fa fa-check me-2"></i>Confirmar y realizar pedido
          </button>
        </form>
      </div>

      <!-- RESUMEN DE PRECIO -->
      <div class="col-lg-4">
        <div style="position:sticky;top:100px">
          <div class="price-display mb-4">
            <p style="font-size:0.78rem;letter-spacing:2px;text-transform:uppercase;opacity:0.8;margin-bottom:0.4rem">Precio estimado</p>
            <div class="total-amount" id="precio-total">$400.00</div>
            <p style="font-size:0.82rem;opacity:0.7;margin-top:0.3rem">Los precios son aproximados</p>
          </div>

          <div class="cotizador-card" style="background:var(--blush)">
            <h6 style="font-family:'Playfair Display',serif;color:var(--brown-dark);margin-bottom:1.2rem">Desglose del anticipo</h6>
            <div style="display:flex;justify-content:space-between;margin-bottom:0.5rem;font-size:0.9rem">
              <span style="color:var(--text-mid)">Precio total</span>
              <span id="precio-total-2" style="font-weight:600;color:var(--brown-dark)">$400.00</span>
            </div>
            <div style="display:flex;justify-content:space-between;padding-top:0.8rem;border-top:1px dashed var(--rose-nude)">
              <span style="color:var(--text-mid)">Anticipo (50%)</span>
              <span id="anticipo-display" style="font-weight:600;color:var(--rose-deep)">$200.00</span>
            </div>
            <p style="font-size:0.78rem;color:var(--text-light);margin-top:0.8rem;margin-bottom:0">El saldo restante se paga al recoger tu pastel.</p>
          </div>

          <div class="cotizador-card mt-3">
            <h6 style="font-family:'Playfair Display',serif;color:var(--brown-dark);margin-bottom:1rem">¿Tienes dudas?</h6>
            <p style="color:var(--text-light);font-size:0.88rem;margin-bottom:1rem">Contáctanos y con gusto te ayudamos a diseñar tu pastel perfecto.</p>
            <a href="https://wa.me/528340000000" target="_blank" class="btn-mc-outline w-100" style="text-align:center">
              <i class="fab fa-whatsapp me-2"></i>Escribir al WhatsApp
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
// Sync price display
const orig = actualizarPrecio;
// Override to also sync secondary display
document.addEventListener('DOMContentLoaded', () => {
  const p2 = document.getElementById('precio-total-2');
  setInterval(() => {
    const main = document.getElementById('precio-total');
    if (p2 && main) p2.textContent = main.textContent;
  }, 200);
});
</script>
<?php require_once 'includes/footer.php'; ?>
