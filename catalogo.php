<?php
$pageTitle = 'Catálogo';
require_once 'includes/header.php';
$db = getDB();
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;

$total = $db->query("SELECT COUNT(*) FROM pasteles WHERE disponible = 1")->fetchColumn();
$pasteles = $db->prepare("SELECT * FROM pasteles WHERE disponible = 1 ORDER BY fecha_creacion DESC LIMIT ? OFFSET ?");
$pasteles->bindValue(1, $perPage, PDO::PARAM_INT);
$pasteles->bindValue(2, $offset, PDO::PARAM_INT);
$pasteles->execute();
$pasteles = $pasteles->fetchAll();
$pages = max(1, ceil($total / $perPage));
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Catálogo | Miranda Cakes</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="/css/style.css">
</head>
<body>
  <main class="page-section">
    <div class="container">
      <div class="text-center mb-5 animate-up">
        <div class="section-subtitle">Catálogo</div>
        <h2 class="section-title">Todos nuestros Pasteles</h2>
        <div class="divider-rose"></div>
      </div>

      <div class="row g-4">
        <?php if (empty($pasteles)): ?>
          <div class="col-12 text-center" style="color:var(--text-light)">No hay pasteles disponibles en este momento.</div>
        <?php else: ?>
          <?php foreach ($pasteles as $p): ?>
            <div class="col-md-6 col-lg-4 animate-up">
              <div class="cake-card">
                <?php if ($p['imagen_url']): ?>
                  <div style="overflow:hidden; height:260px">
                    <img src="<?= sanitize(UPLOAD_URL . $p['imagen_url']) ?>" alt="<?= sanitize($p['nombre']) ?>" style="width:100%;height:100%;object-fit:cover">
                  </div>
                <?php else: ?>
                  <div class="cake-placeholder" style="height:260px">🎂</div>
                <?php endif; ?>
                <div class="card-body">
                  <h5 class="card-title"><?= sanitize($p['nombre']) ?></h5>
                  <p style="color:var(--text-light)"><?= sanitize($p['descripcion']) ?></p>
                  <div class="d-flex justify-content-between align-items-center">
                    <span class="cake-price">Desde $<?= number_format($p['precio_base'],2) ?></span>
                    <a href="/cotizador.php?base=<?= $p['id_pastel'] ?>" class="btn-mc-primary">Pedir</a>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <?php if ($pages > 1): ?>
        <nav class="mt-4" aria-label="Paginación">
          <ul class="pagination justify-content-center">
            <?php for ($i=1;$i<=$pages;$i++): ?>
              <li class="page-item <?= $i==$page? 'active' : '' ?>">
                <a class="page-link" href="/catalogo.php?page=<?= $i ?>"><?= $i ?></a>
              </li>
            <?php endfor; ?>
          </ul>
        </nav>
      <?php endif; ?>
    </div>
  </main>
  <?php require_once 'includes/footer.php'; ?>
</body>
</html>
