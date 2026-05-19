<?php
require_once '../includes/config.php';
requireAdmin();

// Solo superadmin puede acceder
if ($_SESSION['rol'] !== 'superadmin') {
  http_response_code(403);
  die('Acceso no autorizado.');
}

// Usar la carpeta de backups fuera del webroot (ruta en servidor)
$uploadsDir = '/home/ubuntu/backups';
if (!is_dir($uploadsDir)) {
  $msg = 'Directorio de backups no encontrado en el servidor.';
}

// CSRF token para acciones sensibles
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf_token'];

// Manejar subida
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['backup'])) {
  $f = $_FILES['backup'];
  if ($f['error'] === UPLOAD_ERR_OK) {
    $name = basename($f['name']);
    // Permitir solo nombres sencillos
    if (!preg_match('/^[a-zA-Z0-9._-]+$/', $name)) {
      $msg = 'Nombre de fichero no permitido.';
    } else {
      $target = $uploadsDir . '/' . time() . '_' . $name;
      if (move_uploaded_file($f['tmp_name'], $target)) {
        $msg = 'Backup subido correctamente.';
      } else {
        $msg = 'Error al mover el fichero.';
      }
    }
  } else {
    $msg = 'Error en la subida.';
  }
}

// Nota: la eliminación se gestiona vía POST en admin/delete_backup.php

$files = array_values(array_filter(scandir($uploadsDir), function($f) use($uploadsDir){
  return is_file($uploadsDir.'/'.$f) && $f !== '.' && $f !== '..';
}));
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Backups | Miranda Cakes</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="/css/style.css">
</head>
<body style="background:#FAF6F4">
  <?php include __DIR__ . '/_sidebar.php'; ?>
  <main class="admin-content">
    <div class="admin-header">
      <div>
        <h4 style="font-family:'Playfair Display',serif;color:var(--brown-dark);margin:0">Backups</h4>
        <div style="font-size:0.82rem;color:var(--text-light)"><?= date('l, d F Y') ?></div>
      </div>
    </div>

    <div class="stat-card">
      <?php if (!empty($msg)): ?>
        <div class="alert alert-info"><?= sanitize($msg) ?></div>
      <?php endif; ?>

      <form method="post" enctype="multipart/form-data" class="mb-3">
        <div class="mb-2">Subir backup (.sql, .zip):</div>
        <input type="file" name="backup" accept=".sql,.zip" required class="form-control mb-2" />
        <button class="btn btn-primary">Subir</button>
      </form>

      <h5>Backups existentes</h5>
      <?php if (empty($files)): ?>
        <div style="color:var(--text-light)">No hay backups en la carpeta uploads/backups.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table">
            <thead><tr><th>Fichero</th><th>Tamaño</th><th>Modificado</th><th>Acción</th></tr></thead>
            <tbody>
              <?php foreach ($files as $f): ?>
                <tr>
                  <td><?= sanitize($f) ?></td>
                  <td><?= number_format(filesize($uploadsDir.'/'.$f)/1024,2) ?> KB</td>
                  <td><?= date('d/m/Y H:i', filemtime($uploadsDir.'/'.$f)) ?></td>
                  <td>
                    <a href="/admin/download_backup.php?file=<?= rawurlencode($f) ?>" class="btn btn-sm btn-outline-secondary">Descargar</a>
                    <form method="post" action="/admin/delete_backup.php" style="display:inline-block;margin:0">
                      <input type="hidden" name="file" value="<?= sanitize($f) ?>">
                      <input type="hidden" name="token" value="<?= $csrf ?>">
                      <button class="btn btn-sm btn-danger" onclick="return confirm('Borrar backup?')">Borrar</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </main>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
