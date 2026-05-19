<?php
require_once 'includes/config.php';
if (isLoggedIn()) { header('Location: /index.php'); exit; }

$error = '';
$mode = $_GET['mode'] ?? 'login'; // login | register

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getDB();
    if ($_POST['action'] === 'login') {
        $email = sanitize($_POST['email'] ?? '');
        $pass = $_POST['password'] ?? '';
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE email = ? AND activo = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($pass, $user['contrasena'])) {
            $_SESSION['usuario_id'] = $user['id_usuario'];
            $_SESSION['nombre'] = $user['nombre'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['rol'] = $user['rol'];
            header('Location: ' . (in_array($user['rol'],['admin','superadmin']) ? '/admin/dashboard.php' : '/index.php'));
            exit;
        } else {
            $error = 'Credenciales incorrectas.';
        }
    } elseif ($_POST['action'] === 'register') {
        $nombre = sanitize($_POST['nombre'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $tel = sanitize($_POST['telefono'] ?? '');
        $pass = $_POST['password'] ?? '';
        $pass2 = $_POST['password2'] ?? '';
        if (!$nombre || !$email || !$pass) { $error = 'Completa todos los campos.'; }
        elseif ($pass !== $pass2) { $error = 'Las contraseñas no coinciden.'; }
        elseif (strlen($pass) < 6) { $error = 'La contraseña debe tener al menos 6 caracteres.'; }
        else {
            $stmt = $db->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) { $error = 'El email ya está registrado.'; }
            else {
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO usuarios (nombre, email, contrasena, telefono) VALUES (?,?,?,?)");
                $stmt->execute([$nombre, $email, $hash, $tel]);
                $_SESSION['usuario_id'] = $db->lastInsertId();
                $_SESSION['nombre'] = $nombre;
                $_SESSION['email'] = $email;
                $_SESSION['rol'] = 'cliente';
                header('Location: /index.php'); exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $mode === 'register' ? 'Crear cuenta' : 'Ingresar' ?> | Miranda Cakes</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<div class="login-container">
  <div class="login-card">
    <div class="text-center mb-4">
      <a href="/index.php" style="font-family:'Playfair Display',serif;font-size:1.8rem;font-weight:600;color:var(--brown-dark);text-decoration:none">
        Miranda <span style="color:var(--rose-deep)">Cakes</span>
      </a>
      <p style="color:var(--text-light);font-size:0.85rem;letter-spacing:1px;text-transform:uppercase;margin-top:0.5rem">
        <?= $mode === 'register' ? 'Crear cuenta nueva' : 'Bienvenida de vuelta' ?>
      </p>
    </div>

    <?php if ($error): ?>
      <div class="alert" style="background:var(--blush);border:1px solid var(--rose-nude);color:var(--rose-deep);border-radius:10px;font-size:0.9rem;padding:0.8rem 1rem">
        <i class="fa fa-exclamation-circle me-2"></i><?= sanitize($error) ?>
      </div>
    <?php endif; ?>

    <!-- TABS -->
    <div style="display:flex;background:var(--blush);border-radius:12px;padding:4px;margin-bottom:2rem">
      <a href="/login.php" class="text-center flex-fill py-2 rounded-3 text-decoration-none" style="font-size:0.85rem;font-weight:500;letter-spacing:0.5px;<?= $mode!=='register' ? 'background:white;color:var(--rose-deep);box-shadow:0 2px 8px var(--shadow)' : 'color:var(--text-light)' ?>">Ingresar</a>
      <a href="/login.php?mode=register" class="text-center flex-fill py-2 rounded-3 text-decoration-none" style="font-size:0.85rem;font-weight:500;letter-spacing:0.5px;<?= $mode==='register' ? 'background:white;color:var(--rose-deep);box-shadow:0 2px 8px var(--shadow)' : 'color:var(--text-light)' ?>">Registrarse</a>
    </div>

    <form method="POST" class="form-mc">
      <input type="hidden" name="action" value="<?= $mode === 'register' ? 'register' : 'login' ?>">

      <?php if ($mode === 'register'): ?>
        <div class="mb-3">
          <label>Nombre completo</label>
          <input type="text" name="nombre" class="form-control" placeholder="Tu nombre" required>
        </div>
      <?php endif; ?>

      <div class="mb-3">
        <label>Correo electrónico</label>
        <input type="email" name="email" class="form-control" placeholder="hola@email.com" required>
      </div>

      <?php if ($mode === 'register'): ?>
        <div class="mb-3">
          <label>Teléfono <span style="color:var(--text-light);font-weight:400">(opcional)</span></label>
          <input type="tel" name="telefono" class="form-control" placeholder="(834) 000-0000">
        </div>
      <?php endif; ?>

      <div class="mb-3">
        <label>Contraseña</label>
        <div style="position:relative">
          <input type="password" name="password" id="passField" class="form-control" placeholder="••••••••" required>
          <button type="button" onclick="togglePass()" style="position:absolute;right:1rem;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-light);cursor:pointer"><i class="fa fa-eye" id="eyeIcon"></i></button>
        </div>
      </div>

      <?php if ($mode === 'register'): ?>
        <div class="mb-3">
          <label>Confirmar contraseña</label>
          <input type="password" name="password2" class="form-control" placeholder="••••••••" required>
        </div>
      <?php endif; ?>

      <button type="submit" class="btn-mc-primary w-100 mt-3" style="border-radius:10px;padding:0.85rem">
        <?= $mode === 'register' ? '<i class="fa fa-user-plus me-2"></i>Crear cuenta' : '<i class="fa fa-sign-in-alt me-2"></i>Ingresar' ?>
      </button>
    </form>

    <p class="text-center mt-3" style="font-size:0.85rem;color:var(--text-light)">
      <a href="/index.php" style="color:var(--rose-deep)"><i class="fa fa-arrow-left me-1"></i>Volver al inicio</a>
    </p>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
function togglePass() {
  const f = document.getElementById('passField');
  const eye = document.getElementById('eyeIcon');
  if (f.type === 'password') { f.type = 'text'; eye.className = 'fa fa-eye-slash'; }
  else { f.type = 'password'; eye.className = 'fa fa-eye'; }
}
</script>
</body>
</html>
