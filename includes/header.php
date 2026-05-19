<?php require_once __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? sanitize($pageTitle) . ' | ' : '' ?>Miranda Cakes</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="/css/style.css">
  <link rel="icon" href="/uploads/catalogo/logo.png" type="image/png">
</head>
<body>

<nav class="navbar navbar-mc navbar-expand-lg">
  <div class="container">
    <a href="/index.php" class="brand-text">Miranda <span>Cakes</span></a>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav ms-auto align-items-center gap-1">
        <li class="nav-item"><a href="/index.php" class="nav-link">Inicio</a></li>
        <li class="nav-item"><a href="../uploads/catalogo" class="nav-link">Catálogo</a></li>
        <li class="nav-item"><a href="/cotizador.php" class="nav-link">Cotizar</a></li>
        <?php if (isLoggedIn()): ?>
          <li class="nav-item"><a href="/mis-pedidos.php" class="nav-link">Mis Pedidos</a></li>
          <?php if (in_array($_SESSION['rol'], ['admin','superadmin'])): ?>
            <li class="nav-item"><a href="/admin/dashboard.php" class="nav-link" style="color:var(--rose-deep)!important"><i class="fa fa-crown me-1"></i>Admin</a></li>
          <?php endif; ?>
          <li class="nav-item ms-2">
            <a href="/php/auth.php?action=logout" class="btn-mc-outline" style="padding:0.5rem 1.5rem; font-size:0.8rem">
              <i class="fa fa-sign-out-alt me-1"></i> Salir
            </a>
          </li>
        <?php else: ?>
          <li class="nav-item ms-2"><a href="/login.php" class="btn-mc-primary" style="padding:0.5rem 1.5rem; font-size:0.8rem">Ingresar</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
