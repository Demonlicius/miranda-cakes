<aside class="admin-sidebar">
  <a href="/admin/dashboard.php" class="sidebar-brand">
    Miranda <span style="color:var(--rose-nude)">Cakes</span>
    <div style="font-size:0.7rem;color:rgba(255,255,255,0.4);font-family:'Jost',sans-serif;font-weight:300;margin-top:0.2rem">
      <?= $_SESSION['rol'] === 'superadmin' ? 'Super Administrador' : 'Administrador' ?>
    </div>
  </a>
  <nav>
    <div style="font-size:0.65rem;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,0.3);padding:0.5rem 1.5rem 0.3rem">Principal</div>
    <a href="/admin/dashboard.php" class="nav-link <?= basename($_SERVER['PHP_SELF'])==='dashboard.php'?'active':'' ?>"><i class="fa fa-home"></i>Dashboard</a>
    <a href="/admin/pedidos.php" class="nav-link <?= basename($_SERVER['PHP_SELF'])==='pedidos.php'?'active':'' ?>"><i class="fa fa-birthday-cake"></i>Pedidos</a>
    <a href="/admin/catalogo.php" class="nav-link <?= basename($_SERVER['PHP_SELF'])==='catalogo.php'?'active':'' ?>"><i class="fa fa-images"></i>Catálogo</a>
    <div style="font-size:0.65rem;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,0.3);padding:1rem 1.5rem 0.3rem">Gestión</div>
    <a href="/admin/usuarios.php" class="nav-link <?= basename($_SERVER['PHP_SELF'])==='usuarios.php'?'active':'' ?>"><i class="fa fa-users"></i>Usuarios</a>
    <a href="/admin/pagos.php" class="nav-link <?= basename($_SERVER['PHP_SELF'])==='pagos.php'?'active':'' ?>"><i class="fa fa-money-bill"></i>Pagos</a>
    
    <?php if ($_SESSION['rol'] === 'superadmin'): ?>
    <div style="font-size:0.65rem;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,0.3);padding:1rem 1.5rem 0.3rem">Super Admin</div>
    <a href="/admin/backups.php" class="nav-link <?= basename($_SERVER['PHP_SELF'])==='backups.php'?'active':'' ?>"><i class="fa fa-database"></i>Backups</a>
    <a href="/admin/sistema.php" class="nav-link <?= basename($_SERVER['PHP_SELF'])==='sistema.php'?'active':'' ?>"><i class="fa fa-cog"></i>Sistema</a>
    <?php endif; ?>
    <div style="padding:1.5rem;margin-top:1rem">
      <div style="background:rgba(255,255,255,0.08);border-radius:12px;padding:1rem">
        <div style="font-size:0.82rem;color:rgba(255,255,255,0.7)"><?= sanitize($_SESSION['nombre']) ?></div>
        <div style="font-size:0.72rem;color:rgba(255,255,255,0.4);text-transform:capitalize;margin-bottom:0.5rem"><?= $_SESSION['rol'] ?></div>
        <a href="/php/auth.php?action=logout" style="font-size:0.78rem;color:var(--rose-nude);text-decoration:none">
          <i class="fa fa-sign-out-alt me-1"></i>Cerrar sesión
        </a>
      </div>
    </div>
  </nav>
</aside>
