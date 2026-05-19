<?php
ob_start();
require_once '/var/www/html/includes/config.php';

// Validar credenciales de administración
requireAdmin();
$db = getDB();

// LEFT JOIN para asegurar que se listen todos los pedidos, incluso si el cliente no es localizado
$stmt = $db->query("
    SELECT p.id_pedido, p.fecha_pedido, p.precio_total AS total, p.anticipo_monto AS anticipo, p.saldo_final AS pendiente, p.estado, IFNULL(u.nombre, 'Usuario no encontrado') AS cliente_nombre
    FROM pedidos p
    LEFT JOIN usuarios u ON u.id_usuario = p.id_usuario
    ORDER BY p.id_pedido DESC
");
$pagos = $stmt->fetchAll();

include '/var/www/html/includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-3">
            <?php include '/var/www/html/admin/_sidebar.php'; ?>
        </div>
        <div class="col-md-9">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h4 class="mb-0">Historial Global de Pedidos y Recibos</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID Pedido</th>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th>Total</th>
                                    <th>Anticipo</th>
                                    <th>Saldo Pendiente</th>
                                    <th>Estado</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($pagos)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">No hay registros de pedidos en la base de datos.</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($pagos as $p): ?>
                                    <tr>
                                        <td>#<?php echo $p['id_pedido']; ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($p['fecha_pedido'])); ?></td>
                                        <td><?php echo htmlspecialchars($p['cliente_nombre']); ?></td>
                                        <td>$<?php echo number_format($p['total'], 2); ?></td>
                                        <td class="text-success">$<?php echo number_format($p['anticipo'], 2); ?></td>
                                        <td class="<?php echo $p['pendiente'] > 0 ? 'text-danger' : 'text-muted'; ?>">
                                            $<?php echo number_format($p['pendiente'], 2); ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?php echo strtoupper($p['estado']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="recibos.php?id=<?php echo $p['id_pedido']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fa fa-receipt me-1"></i>Ver Recibo
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
include '/var/www/html/includes/footer.php';
ob_end_flush();
?>
