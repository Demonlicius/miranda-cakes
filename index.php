<?php
$pageTitle = 'Inicio';
require_once 'includes/header.php';
$db = getDB();
$pasteles = $db->query("SELECT * FROM pasteles WHERE disponible = 1 LIMIT 6")->fetchAll();
?>

<!-- HERO -->
<section class="hero">
  <div class="container position-relative">
    <div class="row align-items-center g-5">
      <div class="col-lg-6">
        <div class="animate-up">
          <div class="hero-badge">✨ Pasteles Artesanales</div>
          <h1 class="hero-title">
            Cada pastel,<br>
            una <em>historia</em><br>
            dulce
          </h1>
          <p class="hero-subtitle mt-3 mb-4">
            Creamos pasteles personalizados que hacen que cada celebración sea única e inolvidable. Diseñados con amor, hechos para ti.
          </p>
          <div class="d-flex gap-3 flex-wrap">
            <a href="/cotizador.php" class="btn-mc-primary">
              <i class="fa fa-magic me-2"></i>Cotiza tu pastel
            </a>
            <a href="/catalogo.php" class="btn-mc-outline">Ver catálogo</a>
          </div>
        </div>
      </div>
      <div class="col-lg-6 text-center">
        <div class="animate-up" style="animation-delay:0.3s">
          <div style="background:linear-gradient(135deg,var(--blush) 0%,var(--rose-nude) 100%); border-radius:30px; padding:3rem; display:inline-block; position:relative;">
            <div style="font-size:8rem; line-height:1;">🎂</div>
            <div style="position:absolute;top:-15px;right:-15px;background:white;border-radius:50%;width:70px;height:70px;display:flex;align-items:center;justify-content:center;font-size:1.8rem;box-shadow:0 5px 20px var(--shadow)">🌸</div>
            <div style="position:absolute;bottom:-10px;left:-10px;background:var(--rose-deep);border-radius:50%;width:50px;height:50px;display:flex;align-items:center;justify-content:center;font-size:1.3rem">✨</div>
            <!-- Stats -->
            <div style="position:absolute;top:50%;right:-130px;transform:translateY(-50%);background:white;border-radius:16px;padding:1rem 1.5rem;box-shadow:0 10px 30px var(--shadow);text-align:center;display:none" class="d-lg-block">
              <div style="font-family:'Playfair Display',serif;font-size:1.8rem;color:var(--rose-deep);font-weight:600">200+</div>
              <div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:1px;color:var(--text-light)">Pasteles</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- STATS -->
<section style="background:var(--blush); padding:2.5rem 0; border-top:1px solid var(--border); border-bottom:1px solid var(--border);">
  <div class="container">
    <div class="row text-center g-4">
      <?php foreach ([['🎂','200+','Pasteles creados'],['⭐','100%','Clientes felices'],['🌟','5+','Años de experiencia'],['🎨','∞','Diseños únicos']] as $s): ?>
        <div class="col-6 col-md-3 animate-up">
          <div style="font-size:2rem"><?= $s[0] ?></div>
          <div style="font-family:'Playfair Display',serif;font-size:2rem;font-weight:600;color:var(--brown-dark)"><?= $s[1] ?></div>
          <div style="font-size:0.82rem;text-transform:uppercase;letter-spacing:1.5px;color:var(--text-light)"><?= $s[2] ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- CATÁLOGO PREVIEW -->
<section class="page-section">
  <div class="container">
    <div class="text-center mb-5 animate-up">
      <div class="section-subtitle">Nuestros Pasteles</div>
      <h2 class="section-title">Delicias que enamoran</h2>
      <div class="divider-rose"></div>
    </div>
    <div class="row g-4">
      <?php foreach ($pasteles as $i => $p): ?>
        <div class="col-md-6 col-lg-4 animate-up" style="animation-delay:<?= $i * 0.1 ?>s">
          <div class="cake-card">
            <?php if ($p['imagen_url']): ?>
              <div style="overflow:hidden; height:240px;">
                <img src="<?= sanitize(UPLOAD_URL . $p['imagen_url']) ?>" class="card-img-top" alt="<?= sanitize($p['nombre']) ?>" style="width:100%;height:100%;object-fit:cover;">
              </div>
            <?php else: ?>
              <div class="cake-placeholder">🎂</div>
            <?php endif; ?>
            <div class="card-body">
              <h5 class="card-title"><?= sanitize($p['nombre']) ?></h5>
              <p style="color:var(--text-light);font-size:0.9rem;margin-bottom:1rem"><?= sanitize(substr($p['descripcion'],0,80)) ?>...</p>
              <div class="d-flex justify-content-between align-items-center">
                <span class="cake-price">Desde $<?= number_format($p['precio_base'],2) ?></span>
                <a href="/cotizador.php?base=<?= $p['id_pastel'] ?>" class="btn-mc-primary" style="padding:0.4rem 1.2rem;font-size:0.8rem">Pedir</a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-5 animate-up">
      <a href="/catalogo.php" class="btn-mc-outline">Ver catálogo completo <i class="fa fa-arrow-right ms-2"></i></a>
    </div>
  </div>
</section>

<!-- PROCESO -->
<section class="page-section" style="background:linear-gradient(135deg,#FDF5F2 0%,var(--blush) 100%)">
  <div class="container">
    <div class="text-center mb-5 animate-up">
      <div class="section-subtitle">¿Cómo funciona?</div>
      <h2 class="section-title">De tu sueño a tu mesa</h2>
      <div class="divider-rose"></div>
    </div>
    <div class="row g-4">
      <?php foreach ([
        ['1','fa-magic','Cotiza','Usa nuestro cotizador para personalizar tu pastel ideal'],
        ['2','fa-check-circle','Confirma','Revisa y confirma tu pedido, da tu anticipo del 50%'],
        ['3','fa-clock','Creamos','Nuestras pasteleras crean tu pastel con dedicación'],
        ['4','fa-gift','¡Disfruta!','Liquida, obtén tu código único y recoge tu pastel'],
      ] as $i => $step): ?>
        <div class="col-md-6 col-lg-3 animate-up text-center" style="animation-delay:<?= $i*0.15 ?>s">
          <div style="background:white;border-radius:20px;padding:2rem 1.5rem;border:1px solid var(--border);height:100%">
            <div style="width:60px;height:60px;background:var(--blush);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:1.4rem;color:var(--rose-deep)"><i class="fa <?= $step[1] ?>"></i></div>
            <div style="font-family:'Playfair Display',serif;font-size:0.8rem;background:var(--rose-deep);color:white;width:26px;height:26px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 0.8rem"><?= $step[0] ?></div>
            <h5 style="font-family:'Playfair Display',serif;color:var(--brown-dark)"><?= $step[2] ?></h5>
            <p style="color:var(--text-light);font-size:0.9rem"><?= $step[3] ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="page-section" style="background:var(--brown-dark)">
  <div class="container text-center">
    <div class="animate-up">
      <div style="font-size:0.78rem;letter-spacing:3px;text-transform:uppercase;color:var(--rose-nude);margin-bottom:1rem">¿Lista para tu pastel?</div>
      <h2 style="font-family:'Playfair Display',serif;font-size:2.8rem;color:white;margin-bottom:1.5rem">Crea tu pastel <em>perfecto</em></h2>
      <p style="color:rgba(255,255,255,0.6);margin-bottom:2rem;max-width:450px;margin-left:auto;margin-right:auto">Personaliza cada detalle: sabor, forma, colores, decoración 3D y más.</p>
      <a href="/cotizador.php" class="btn-mc-primary" style="font-size:1rem;padding:1rem 3rem">
        <i class="fa fa-magic me-2"></i>Comenzar cotización
      </a>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
