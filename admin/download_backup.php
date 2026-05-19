<?php
require_once '../includes/config.php';
requireAdmin();
if ($_SESSION['rol'] !== 'superadmin') { http_response_code(403); exit; }

$uploadsDir = '/home/ubuntu/backups';
$file = isset($_GET['file']) ? basename($_GET['file']) : '';
$path = realpath($uploadsDir . '/' . $file);
if (!$path || !str_starts_with($path, realpath($uploadsDir)) || !is_file($path)) {
  http_response_code(404);
  exit;
}

$mime = mime_content_type($path) ?: 'application/octet-stream';
header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($path));
header('Content-Disposition: attachment; filename="' . $file . '"');
readfile($path);
exit;
