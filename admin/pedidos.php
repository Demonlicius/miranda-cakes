<?php
ob_start();
require_once '/var/www/html/includes/config.php';

if (!function_exists('sanitize')) {
    function sanitize($input) {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
}

requireAdmin();
$db = getDB();

// Actualizar estado si se manda POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_estado') {
        $stmt = $db->prepare("UPDATE pedidos SET estado = ?, fecha_entrega_estimada = ? WHERE id_pedido = ?");
        $stmt->execute([sanitize($_POST['estado']), $_POST['fecha_entrega'] ?: null, intval($_POST['id_pedido'])]);
        header('Location: /admin/pedidos.php?ok=1'); exit;
    }
    if ($_POST['action'] === 'confirmar_anticipo') {
        $db->prepare("UPDATE pedidos SET anticipo_pagado = 1, estado = 'en_proceso' WHERE id_pedido = ?")->execute([intval($_POST['id_pedido'])]);
        header('Location: /admin/pedidos.php?ok=1'); exit;
    }
}

// Cargar pedido individual
$viewId = intval($_GET['id'] ?? 0);
$pedido = null;
if ($viewId) {
    $stmt = $db->prepare("SELECT p.*, u.nombre as cliente_nombre, u.email, u.telefono,
        d.tamano, d.tipo_betun AS betun, d.forma, d.personas, d.colores, d.decoracion, d.decoracion_3d, d.mensaje_decoracion, d.nombre_pastel
        FROM pedidos p JOIN usuarios u ON u.id_usuario = p.id_usuario
        LEFT JOIN detalle_pedido d ON d.id_pedido = p.id_pedido
        WHERE p.id_pedido = ?");
    $stmt->execute([$viewId]);
    $pedido = $stmt->fetch();
}

$filtro = $_GET['estado'] ?? '';
$where = $filtro ? "WHERE p.estado = '$filtro'" : '';
$pedidos = $db->query("SELECT p.*, u.nombre as cliente_nombre FROM pedidos p JOIN usuarios u ON u.id_usuario = p.id_usuario $where ORDER BY p.fecha_pedido DESC")->fetchAll();

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
  <title>Pedidos | Admin Miranda Cakes</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/css/style.css">
</head>
<body style="background:#FAF6F4">

<?php include '_sidebar.php'; ?>

<main class="admin-content">
  <div class="admin-header">
    <h4 style="font-family:'Playfair Display',serif;color:var(--brown-dark);margin:0">
      <i class="fa fa-birthday-cake me-2" style="color:var(--rose-deep)"></i>Gestión de Pedidos
    </h4>
  </div>

  <?php if (isset($_GET['ok'])): ?>
    <div class="alert" style="background:#D4EDDA;border:1px solid #C3E6CB;color:#155724;border-radius:10px;padding:0.8rem 1rem;margin-bottom:1rem">
      <i class="fa fa-check me-2"></i>Cambios guardados correctamente.
    </div>
  <?php endif; ?>

  <!-- Filtros -->
  <div style="display:flex;gap:0.5rem;margin-bottom:1.5rem;flex-wrap:wrap">
    <a href="/admin/pedidos.php" class="badge-estado <?= !$filtro ? 'badge-listo' : '' ?>" style="text-decoration:none;cursor:pointer;<?= !$filtro ? '' : 'background:var(--blush);color:var(--text-mid)' ?>">Todos</a>
    <?php foreach ($estados as $k => $e): ?>
      <a href="?estado=<?= $k ?>" class="badge-estado <?= $filtro===$k ? $e[1] : '' ?>" style="text-decoration:none;cursor:pointer;<?= $filtro===$k ? '' : 'background:var(--blush);color:var(--text-mid)' ?>"><?= $e[0] ?></a>
    <?php endforeach; ?>
  </div>

  <?php if ($pedido): ?>
    <!-- DETALLE INDIVIDUAL -->
    <div class="stat-card mb-4">
      <div class="d-flex justify-content-between align-items-start mb-3">
        <h5 style="font-family:'Playfair Display',serif;color:var(--brown-dark)">Pedido #<?= $pedido['id_pedido'] ?></h5>
        <a href="/admin/pedidos.php" style="color:var(--text-light);font-size:0.85rem"><i class="fa fa-arrow-left me-1"></i>Volver</a>
      </div>
      <div class="row g-4">
        <div class="col-md-6">
          <div style="background:var(--blush);border-radius:12px;padding:1.2rem">
            <div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:1.5px;color:var(--text-light);margin-bottom:0.8rem">Cliente</div>
            <div style="font-weight:600;color:var(--brown-dark)"><?= sanitize($pedido['cliente_nombre']) ?></div>
            <div style="font-size:0.88rem;color:var(--text-light)"><?= sanitize($pedido['email']) ?></div>
            <?php if ($pedido['telefono']): ?><div style="font-size:0.88rem;color:var(--text-light)"><?= sanitize($pedido['telefono']) ?></div><?php endif; ?>
          </div>
        </div>
        <div class="col-md-6">
          <div style="background:var(--blush);border-radius:12px;padding:1.2rem">
            <div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:1.5px;color:var(--text-light);margin-bottom:0.8rem">Pastel</div>
            <?php foreach ([
              ['Nombre', $pedido['nombre_pastel'] ?: 'Personalizado'],
              ['Tamaño', ucfirst($pedido['tamano'])],
              ['Forma', ucfirst($pedido['forma'])],
              ['Betún', str_replace('_',' ',ucfirst($pedido['betun']))],
              ['Personas', $pedido['personas']],
              ['Dec. 3D', $pedido['decoracion_3d'] ? '✨ Sí' : 'No'],
            ] as $r): ?>
              <div style="display:flex;justify-content:space-between;font-size:0.88rem;padding:0.25rem 0">
                <span style="color:var(--text-light)"><?= $r[0] ?></span>
                <span style="font-weight:500"><?= sanitize((string)$r[1]) ?></span>
              </div>
            <?php endforeach; ?>
            <?php if ($pedido['colores']): ?>
              <div style="margin-top:0.5rem;font-size:0.85rem;color:var(--text-mid)">Colores: <?= sanitize($pedido['colores']) ?></div>
            <?php endif; ?>
          </div>
        </div>
        <div class="col-12">
          <form method="POST" class="form-mc" style="background:white;border:1px solid var(--border);border-radius:12px;padding:1.5rem">
            <input type="hidden" name="action" value="update_estado">
            <input type="hidden" name="id_pedido" value="<?= $pedido['id_pedido'] ?>">
            <div class="row g-3">
              <div class="col-md-4">
                <label>Estado del Pedido</label>
                <select name="estado" class="form-select">
                  <?php foreach ($estados as $k => $e): ?>
                    <option value="<?= $k ?>" <?= $pedido['estado']===$k ? 'selected' : '' ?>><?= $e[0] ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-4">
                <label>Fecha estimada de entrega</label>
                <input type="date" name="fecha_entrega" class="form-control" value="<?= $pedido['fecha_entrega_estimada'] ?>">
              </div>
              <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn-mc-primary w-100" style="border-radius:10px">
                  <i class="fa fa-save me-2"></i>Guardar cambios
                </button>
              </div>
            </div>
          </form>
        </div>
        <?php if (!$pedido['anticipo_pagado']): ?>
        <div class="col-12">
          <form method="POST" class="form-mc">
            <input type="hidden" name="action" value="confirmar_anticipo">
            <input type="hidden" name="id_pedido" value="<?= $pedido['id_pedido'] ?>">
            <button type="submit" class="btn-mc-outline" onclick="return confirm('¿Confirmar anticipo pagado?')">
              <i class="fa fa-check me-2"></i>Marcar anticipo como recibido ($<?= number_format($pedido['anticipo_monto'],2) ?>)
            </button>
          </form>
        </div>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- TABLA PEDIDOS -->
  <div class="stat-card">
    <div class="table-responsive">
      <table class="table-mc table">
        <thead>
          <tr><th>#</th><th>Cliente</th><th>Pastel/Total</th><th>Anticipo</th><th>Estado</th><th>Fecha</th><th>Entrega</th><th>Acciones</th></tr>
        </thead>
        <tbody>
          <?php foreach ($pedidos as $p):
            $e = $estados[$p['estado']] ?? ['?',''];
          ?>
            <tr>
              <td><span style="color:var(--text-light)">#<?= $p['id_pedido'] ?></span></td>
              <td><?= sanitize($p['cliente_nombre']) ?></td>
              <td style="font-weight:600;color:var(--brown-dark)">$<?= number_format($p['precio_total'],2) ?></td>
              <td><?= $p['anticipo_pagado'] ? '<span style="color:#388E3C"><i class="fa fa-check"></i> Pagado</span>' : '<span style="color:var(--text-light)">Pendiente</span>' ?></td>
              <td><span class="badge-estado <?= $e[1] ?>"><?= $e[0] ?></span></td>
              <td style="color:var(--text-light);font-size:0.85rem"><?= date('d/m/Y', strtotime($p['fecha_pedido'])) ?></td>
              <td style="color:var(--text-light);font-size:0.85rem"><?= $p['fecha_entrega_estimada'] ? date('d/m/Y', strtotime($p['fecha_entrega_estimada'])) : '—' ?></td>
              <td>
                <a href="?id=<?= $p['id_pedido'] ?>" style="color:var(--rose-deep)" title="Ver detalles"><i class="fa fa-eye"></i></a>
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
