<?php
$pageTitle = 'Mis Pedidos';
require_once '/var/www/html/includes/config.php';
requireLogin();

$db = getDB();
$id_usuario = $_SESSION['usuario_id'];

// Obtener todos los pedidos del usuario autenticado
$stmt = $db->prepare("SELECT id_pedido, fecha_pedido, precio_total AS total, anticipo_monto, anticipo_pagado, estado FROM pedidos WHERE id_usuario = ? ORDER BY id_pedido DESC");
$stmt->execute([$id_usuario]);
$pedidos = $stmt->fetchAll();

include '/var/www/html/includes/header.php';
?>

<div class="container my-5" style="min-height: 70vh;">
    <h2 class="mb-4" style="font-family:'Playfair Display', serif; color: var(--brown-dark);">Mis Pedidos</h2>
    
    <?php if (empty($pedidos)): ?>
        <div class="alert alert-info text-center">
            No has realizado ningún pedido aún. <br><br>
            <a href="/cotizar.php" class="btn btn-primary">Cotizar un Pastel</a>
        </div>
    <?php else: ?>
        <div class="table-responsive bg-white p-4 rounded shadow-sm">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID Pedido</th>
                        <th>Fecha</th>
                        <th>Total</th>
                        <th>Anticipo Requerido</th>
                        <th>Estado de Pago</th>
                        <th>Estado de Entrega</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pedidos as $p): ?>
                    <tr>
                        <td>#<?php echo $p['id_pedido']; ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($p['fecha_pedido'])); ?></td>
                        <td>$<?php echo number_format($p['total'], 2); ?></td>
                        <td>$<?php echo number_format($p['anticipo_monto'], 2); ?></td>
                        <td>
                            <span class="badge <?php echo $p['anticipo_pagado'] ? 'bg-success' : 'bg-warning text-dark'; ?>">
                                <?php echo $p['anticipo_pagado'] ? 'Anticipo Confirmado' : 'Pendiente Anticipo'; ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?php 
                                echo $p['estado'] === 'entregado' ? 'bg-success' : 
                                    ($p['estado'] === 'listo' ? 'bg-info' : 
                                    ($p['estado'] === 'cancelado' ? 'bg-danger' : 'bg-secondary')); 
                            ?>">
                                <?php echo strtoupper($p['estado']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-2 flex-wrap">
                                <?php if (!$p['anticipo_pagado'] && $p['estado'] !== 'cancelado'): ?>
                                    <button class="btn btn-sm btn-primary" onclick="pagarAnticipo(<?php echo $p['id_pedido']; ?>, <?php echo $p['anticipo_monto']; ?>)">
                                        <i class="fa fa-credit-card me-1"></i>Dar Anticipo
                                    </button>
                                <?php elseif ($p['anticipo_pagado']): ?>
                                    <a href="/recibo.php?id=<?php echo $p['id_pedido']; ?>" class="btn btn-sm text-white" style="background-color: var(--brown-dark);">
                                        <i class="fa fa-receipt me-1"></i>Ver Recibo
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted" style="font-size: 0.85rem;">Sin acciones</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php 
include '/var/www/html/includes/footer.php';
?>
