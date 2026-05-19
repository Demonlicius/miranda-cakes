<?php
require_once '../includes/config.php';
requireLogin();
$db = getDB();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'crear_pedido':
        crearPedido($db);
        break;
    case 'pagar_anticipo':
        pagarAnticipo($db);
        break;
    case 'liquidar_pedido':
        liquidarPedido($db);
        break;
    default:
        jsonResponse(['error' => 'Acción no válida'], 400);
}

function crearPedido($db) {
    $userId = $_SESSION['usuario_id'];
    $precio = floatval($_POST['precio_total'] ?? 0);
    $anticipo = floatval($_POST['anticipo_monto'] ?? 0);

    if ($precio <= 0) { jsonResponse(['success'=>false,'message'=>'Precio inválido']); }

    $db->beginTransaction();
    try {
        // Insertar pedido
        $stmt = $db->prepare("INSERT INTO pedidos (id_usuario, precio_total, anticipo_monto, saldo_final, estado) VALUES (?,?,?,?,'pendiente')");
        $stmt->execute([$userId, $precio, $anticipo, $precio - $anticipo]);
        $pedidoId = $db->lastInsertId();

        // Insertar detalle
        $nombres_betun = ['fondant'=>'Fondant','crema_mantequilla'=>'Crema Mantequilla','chantilly'=>'Chantilly','ganache'=>'Ganache'];
        $stmt = $db->prepare("INSERT INTO detalle_pedido (id_pedido, id_pastel, nombre_pastel, sabor, tamano, personas, forma, tipo_betun, colores, decoracion, decoracion_3d, mensaje_decoracion, precio_total) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $pasteId = intval($_POST['id_pastel'] ?? 0) ?: null;
        $pasteNombre = $pasteId ? (function() use ($db, $pasteId) {
            $s = $db->prepare("SELECT nombre FROM pasteles WHERE id_pastel = ?");
            $s->execute([$pasteId]); $r = $s->fetch(); return $r ? $r['nombre'] : 'Personalizado';
        })() : 'Pastel Personalizado';
        $betun = sanitize($_POST['betun'] ?? 'fondant');
        $stmt->execute([
            $pedidoId, $pasteId, $pasteNombre, '', sanitize($_POST['tamano'] ?? 'mediano'),
            intval($_POST['personas'] ?? 10), sanitize($_POST['forma'] ?? 'redondo'),
            $betun, sanitize($_POST['colores'] ?? ''),
            sanitize($_POST['decoracion'] ?? ''), isset($_POST['decoracion_3d']) ? 1 : 0,
            sanitize($_POST['mensaje'] ?? ''), $precio
        ]);

        $db->commit();
        jsonResponse(['success' => true, 'pedido_id' => $pedidoId]);
    } catch (Exception $e) {
        $db->rollBack();
        jsonResponse(['success' => false, 'message' => 'Error al crear pedido: ' . $e->getMessage()]);
    }
}

function pagarAnticipo($db) {
    $pedidoId = intval($_POST['pedido_id'] ?? 0);
    $stmt = $db->prepare("SELECT * FROM pedidos WHERE id_pedido = ? AND id_usuario = ?");
    $stmt->execute([$pedidoId, $_SESSION['usuario_id']]);
    $pedido = $stmt->fetch();
    if (!$pedido) { jsonResponse(['success'=>false,'message'=>'Pedido no encontrado']); }
    if ($pedido['anticipo_pagado']) { jsonResponse(['success'=>false,'message'=>'El anticipo ya fue pagado']); }

    $db->beginTransaction();
    try {
        $db->prepare("UPDATE pedidos SET anticipo_pagado = 1, estado = 'en_proceso' WHERE id_pedido = ?")->execute([$pedidoId]);
        $db->prepare("INSERT INTO pagos (id_pedido, tipo, monto, metodo_pago) VALUES (?,?,?,?)")->execute([$pedidoId,'anticipo',$pedido['anticipo_monto'],'efectivo']);
        $db->commit();
        jsonResponse(['success' => true]);
    } catch (Exception $e) {
        $db->rollBack();
        jsonResponse(['success' => false, 'message' => $e->getMessage()]);
    }
}

function liquidarPedido($db) {
    $pedidoId = intval($_POST['pedido_id'] ?? 0);
    $stmt = $db->prepare("SELECT * FROM pedidos WHERE id_pedido = ? AND id_usuario = ?");
    $stmt->execute([$pedidoId, $_SESSION['usuario_id']]);
    $pedido = $stmt->fetch();
    if (!$pedido) { jsonResponse(['success'=>false,'message'=>'Pedido no encontrado']); }
    if (!$pedido['anticipo_pagado']) { jsonResponse(['success'=>false,'message'=>'Primero debes pagar el anticipo']); }

    // Check no existing receipt
    $existing = $db->prepare("SELECT id_recibo FROM recibos WHERE id_pedido = ?");
    $existing->execute([$pedidoId]);
    if ($existing->fetch()) { jsonResponse(['success'=>false,'message'=>'Este pedido ya fue liquidado']); }

    $saldo = $pedido['precio_total'] - $pedido['anticipo_monto'];
    $codigo = generateUniqueCode();

    $db->beginTransaction();
    try {
        $db->prepare("UPDATE pedidos SET estado = 'entregado' WHERE id_pedido = ?")->execute([$pedidoId]);
        $db->prepare("INSERT INTO pagos (id_pedido, tipo, monto, metodo_pago) VALUES (?,?,?,?)")->execute([$pedidoId,'final',$saldo,'efectivo']);
        $db->prepare("INSERT INTO recibos (id_pedido, codigo_confirmacion, total_pagado) VALUES (?,?,?)")->execute([$pedidoId, $codigo, $pedido['precio_total']]);
        $reciboId = $db->lastInsertId();
        $db->commit();
        jsonResponse(['success' => true, 'codigo' => $codigo, 'recibo_id' => $reciboId]);
    } catch (Exception $e) {
        $db->rollBack();
        jsonResponse(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>
