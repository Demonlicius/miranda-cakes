<?php
require_once '../includes/config.php';
requireAdmin();
if ($_SESSION['rol'] !== 'superadmin') { header('Location: /admin/backups.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: /admin/backups.php');
  exit;
}

$token = $_POST['token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
  http_response_code(403);
  die('Token inválido');
}

$file = isset($_POST['file']) ? basename($_POST['file']) : '';
$uploadsDir = '/home/ubuntu/backups';
$path = realpath($uploadsDir . '/' . $file);
if ($path && str_starts_with($path, realpath($uploadsDir)) && is_file($path)) {
  @unlink($path);
}

header('Location: /admin/backups.php');
exit;
