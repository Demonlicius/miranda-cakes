<?php
require_once '../includes/config.php';
$action = $_GET['action'] ?? '';

if ($action === 'logout') {
    session_destroy();
    header('Location: /index.php');
    exit;
}

jsonResponse(['error' => 'Acción no válida'], 400);
?>
