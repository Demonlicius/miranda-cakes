<?php
$pageTitle = 'Recibo';
require_once '/var/www/html/includes/config.php';
requireLogin();
$db = getDB();
$id = intval($_GET['id'] ?? 0);

// Consulta SQL alineada con la columna inyectada
$stmt = $db->prepare("
    SELECT p.id_pedido, p.fecha_pedido, p.precio_total AS total, p.anticipo_monto AS anticipo, p.saldo_final AS pendiente, p.estado, p.id_usuario, p.codigo_recibo AS codigo_confirmacion, p.fecha_pedido AS fecha_emision,
           u.nombre as cliente_nombre, u.email as cliente_email, u.telefono,
           d.tamano, d.tipo_betun AS betun, d.forma, d.personas, d.colores, d.mensaje_decoracion, d.decoracion_3d, d.nombre_pastel
    FROM pedidos p
    JOIN usuarios u ON u.id_usuario = p.id_usuario
    LEFT JOIN detalle_pedido d ON d.id_pedido = p.id_pedido
    WHERE p.id_pedido = ?
");
$stmt->execute([$id]);
$r = $stmt->fetch();

if (!$r) {
    include '/var/www/html/includes/header.php';
    echo '<div class="container py-5 text-center"><h3>Recibo no encontrado en la base de datos.</h3><a href="/admin/pedidos.php">Volver</a></div>';
    require_once '/var/www/html/includes/footer.php';
    exit;
}

include '/var/www/html/includes/header.php';
?>

<section class="page-section" style="background:var(--blush)">
    <div class="container">
        <div class="recibo-container">
            <div class="recibo-header text-center my-4">
                <div style="font-family:'Playfair Display',serif;font-size:2rem;font-weight:600;color:var(--brown-dark)">
                    Miranda <span style="color:var(--rose-deep)">Cakes</span>
                </div>
                <div style="font-size:0.78rem;letter-spacing:2px;text-transform:uppercase;color:var(--text-light);margin-top:0.3rem">Recibo de Pago</div>
                <div style="font-size:0.85rem;color:var(--text-light);margin-top:0.5rem"><?= date('d/m/Y H:i', strtotime($r['fecha_emision'])) ?></div>
            </div>

            <div class="codigo-box text-center my-4 p-3" style="border:2px dashed var(--rose-deep); background:#fff">
                <div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:2px;color:var(--text-light);margin-bottom:0.5rem">Código de Confirmación</div>
                <div class="codigo-text" style="font-size:1.8rem; font-weight:bold; color:var(--brown-dark)"><?= htmlspecialchars($r['codigo_confirmacion'] ?? 'SIN CÓDIGO') ?></div>
                <div style="font-size:0.8rem;color:var(--text-mid);margin-top:0.5rem">Presenta este código al recoger tu pastel</div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5>Detalles del Cliente</h5>
                    <p><strong>Nombre:</strong> <?= htmlspecialchars($r['cliente_nombre']) ?><br>
                    <strong>Correo:</strong> <?= htmlspecialchars($r['cliente_email']) ?><br>
                    <strong>Teléfono:</strong> <?= htmlspecialchars($r['telefono'] ?? 'N/A') ?></p>
                    <hr>
                    <h5>Especificaciones del Pastel</h5>
                    <p><strong>Pastel:</strong> <?= htmlspecialchars($r['nombre_pastel'] ?? 'Personalizado') ?><br>
                    <strong>Tamaño:</strong> <?= htmlspecialchars($r['tamano'] ?? 'N/A') ?> (<?= htmlspecialchars($r['personas'] ?? '0') ?> personas)<br>
                    <strong>Betún:</strong> <?= htmlspecialchars($r['betun'] ?? 'N/A') ?><br>
                    <strong>Forma:</strong> <?= htmlspecialchars($r['forma'] ?? 'N/A') ?><br>
                    <strong>Colores:</strong> <?= htmlspecialchars($r['colores'] ?? 'N/A') ?><br>
                    <strong>Decoración:</strong> <?= htmlspecialchars($r['mensaje_decoracion'] ?? 'Sin mensaje') ?></p>
                    <hr>
                    <h5>Información Financiera</h5>
                    <table class="table table-sm table-borderless">
                        <tr><td><strong>Total del Pedido:</strong></td><td class="text-end">$<?= number_format($r['total'], 2) ?></td></tr>
                        <tr><td><strong>Anticipo Pagado:</strong></td><td class="text-end text-success">$<?= number_format($r['anticipo'], 2) ?></td></tr>
                        <tr class="border-top"><td><strong>Saldo Pendiente:</strong></td><td class="text-end text-danger"><strong>$<?= number_format($r['pendiente'], 2) ?></strong></td></tr>
                    </table>
                    <div class="text-center mt-3">
                        <span class="badge bg-dark p-2"><?= strtoupper($r['estado']) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php 
include '/var/www/html/includes/footer.php';
?>
