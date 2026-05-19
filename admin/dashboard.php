<?php
require_once '../includes/config.php';
requireAdmin();
$db = getDB();

$stats = [
    'pedidos' => $db->query("SELECT COUNT(*) FROM pedidos WHERE estado NOT IN ('cancelado')")->fetchColumn(),
    'pendientes' => $db->query("SELECT COUNT(*) FROM pedidos WHERE estado IN ('pendiente','en_proceso')")->fetchColumn(),
    'clientes' => $db->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'cliente'")->fetchColumn(),
    'ingresos' => $db->query("SELECT COALESCE(SUM(monto),0) FROM pagos WHERE MONTH(fecha_pago) = MONTH(NOW())")->fetchColumn(),
];

$pedidos_recientes = $db->query("
  SELECT p.*, u.nombre as cliente_nombre
  FROM pedidos p JOIN usuarios u ON u.id_usuario = p.id_usuario
  ORDER BY p.fecha_pedido DESC LIMIT 8
")->fetchAll();

$estados = [
  'cotizacion'=>['Cotización','badge-cotizacion'],
  'pendiente'=>['Pendiente','badge-pendiente'],
  'en_proceso'=>['En proceso','badge-en_proceso'],
  'listo'=>['Listo','badge-listo'],
  'entregado'=>['Entregado','badge-entregado'],
  'cancelado'=>['Cancelado','badge-cancelado'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin | Miranda Cakes</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/css/style.css">
</head>
<body style="background:#FAF6F4">

<!-- SIDEBAR -->
<aside class="admin-sidebar">
  <a href="/admin/dashboard.php" class="sidebar-brand">
    Miranda <span style="color:var(--rose-nude)">Cakes</span>
    <div style="font-size:0.7rem;color:rgba(255,255,255,0.4);font-family:'Jost',sans-serif;font-weight:300;margin-top:0.2rem">Panel Administrativo</div>
  </a>
  <nav>
    <div style="font-size:0.65rem;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,0.3);padding:0.5rem 1.5rem 0.3rem">Principal</div>
    <a href="/admin/dashboard.php" class="nav-link active"><i class="fa fa-home"></i>Dashboard</a>
    <a href="/admin/pedidos.php" class="nav-link"><i class="fa fa-birthday-cake"></i>Pedidos</a>
    <a href="/admin/catalogo.php" class="nav-link"><i class="fa fa-images"></i>Catálogo</a>
    <div style="font-size:0.65rem;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,0.3);padding:1rem 1.5rem 0.3rem">Gestión</div>
    <a href="/admin/usuarios.php" class="nav-link"><i class="fa fa-users"></i>Usuarios</a>
    <a href="/admin/pagos.php" class="nav-link"><i class="fa fa-money-bill"></i>Pagos</a>
    
    <?php if ($_SESSION['rol'] === 'superadmin'): ?>
    <div style="font-size:0.65rem;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,0.3);padding:1rem 1.5rem 0.3rem">Super Admin</div>
    <a href="/admin/backups.php" class="nav-link"><i class="fa fa-database"></i>Backups</a>
    <a href="/admin/sistema.php" class="nav-link"><i class="fa fa-cog"></i>Sistema</a>
    <?php endif; ?>
    <div style="margin-top:auto;padding:1.5rem">
      <div style="background:rgba(255,255,255,0.08);border-radius:12px;padding:1rem;margin-top:2rem">
        <div style="font-size:0.82rem;color:rgba(255,255,255,0.7)"><?= sanitize($_SESSION['nombre']) ?></div>
        <div style="font-size:0.72rem;color:rgba(255,255,255,0.4);text-transform:capitalize"><?= $_SESSION['rol'] ?></div>
        <a href="/php/auth.php?action=logout" style="font-size:0.78rem;color:var(--rose-nude);text-decoration:none;margin-top:0.5rem;display:block">
          <i class="fa fa-sign-out-alt me-1"></i>Cerrar sesión
        </a>
      </div>
    </div>
  </nav>
</aside>

<main class="admin-content">
  <!-- Header -->
  <div class="admin-header">
    <div>
      <h4 style="font-family:'Playfair Display',serif;color:var(--brown-dark);margin:0">Dashboard</h4>
      <div style="font-size:0.82rem;color:var(--text-light)"><?= date('l, d F Y') ?></div>
    </div>
    <div style="display:flex;align-items:center;gap:1rem">
      <a href="/index.php" class="btn-mc-outline" style="padding:0.4rem 1.2rem;font-size:0.82rem">
        <i class="fa fa-eye me-1"></i>Ver sitio
      </a>
    </div>
  </div>

  <!-- Stats -->
  <div class="row g-3 mb-4">
    <?php foreach ([
      ['fa-birthday-cake','rose',$stats['pedidos'],'Pedidos Totales'],
      ['fa-clock','blue',$stats['pendientes'],'En Proceso'],
      ['fa-users','green',$stats['clientes'],'Clientes'],
      ['fa-money-bill-wave','brown','$'.number_format($stats['ingresos'],2),'Ingresos del Mes'],
    ] as $s): ?>
      <div class="col-6 col-xl-3">
        <div class="stat-card d-flex align-items-center gap-3">
          <div class="stat-icon <?= $s[1] ?>"><i class="fa <?= $s[0] ?>"></i></div>
          <div>
            <div style="font-family:'Playfair Display',serif;font-size:1.6rem;font-weight:600;color:var(--brown-dark);line-height:1"><?= $s[2] ?></div>
            <div style="font-size:0.78rem;text-transform:uppercase;letter-spacing:1px;color:var(--text-light)"><?= $s[3] ?></div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Pedidos recientes -->
  <div class="stat-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 style="font-family:'Playfair Display',serif;color:var(--brown-dark);margin:0">Pedidos Recientes</h5>
      <a href="/admin/pedidos.php" style="font-size:0.85rem;color:var(--rose-deep)">Ver todos <i class="fa fa-arrow-right ms-1"></i></a>
    </div>
    <div class="table-responsive">
      <table class="table-mc table">
        <thead>
          <tr>
            <th>#</th><th>Cliente</th><th>Total</th><th>Anticipo</th><th>Estado</th><th>Fecha</th><th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($pedidos_recientes as $p):
            $e = $estados[$p['estado']] ?? ['?',''];
          ?>
            <tr>
              <td><span style="color:var(--text-light)">#<?= $p['id_pedido'] ?></span></td>
              <td><?= sanitize($p['cliente_nombre']) ?></td>
              <td style="font-weight:600;color:var(--brown-dark)">$<?= number_format($p['precio_total'],2) ?></td>
              <td>
                <?php if ($p['anticipo_pagado']): ?>
                  <span style="color:#388E3C;font-size:0.85rem"><i class="fa fa-check"></i> Pagado</span>
                <?php else: ?>
                  <span style="color:var(--text-light);font-size:0.85rem">Pendiente</span>
                <?php endif; ?>
              </td>
              <td><span class="badge-estado <?= $e[1] ?>"><?= $e[0] ?></span></td>
              <td style="color:var(--text-light);font-size:0.85rem"><?= date('d/m/Y', strtotime($p['fecha_pedido'])) ?></td>
              <td>
                <a href="/admin/pedidos.php?id=<?= $p['id_pedido'] ?>" style="color:var(--rose-deep);font-size:0.85rem">
                  <i class="fa fa-eye"></i>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>
