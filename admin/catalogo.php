<?php
require_once '../includes/config.php';
requireAdmin();
$db = getDB();
$msg = ''; $err = '';

// CRUD actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'agregar' || $action === 'editar') {
        $nombre = sanitize($_POST['nombre'] ?? '');
        $desc = sanitize($_POST['descripcion'] ?? '');
        $precio = floatval($_POST['precio_base'] ?? 0);
        $disponible = isset($_POST['disponible']) ? 1 : 0;
        $imagen_url = null;

        if (!empty($_FILES['imagen']['name'])) {
            $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','webp'];
            if (!in_array($ext, $allowed)) { $err = 'Formato no permitido. Solo JPG, PNG, WEBP.'; }
            elseif ($_FILES['imagen']['size'] > 5 * 1024 * 1024) { $err = 'Imagen muy grande (máx 5MB).'; }
            else {
                $filename = uniqid('cake_') . '.' . $ext;
                $target = UPLOAD_DIR . $filename;
                if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $target)) $imagen_url = $filename;
                else $err = 'Error al subir imagen.';
            }
        }

        if (!$err) {
            if ($action === 'agregar') {
                $stmt = $db->prepare("INSERT INTO pasteles (nombre, descripcion, precio_base, imagen_url, disponible) VALUES (?,?,?,?,?)");
                $stmt->execute([$nombre, $desc, $precio, $imagen_url, $disponible]);
                $msg = 'Pastel agregado correctamente.';
            } else {
                $id = intval($_POST['id_pastel']);
                if ($imagen_url) {
                    $stmt = $db->prepare("UPDATE pasteles SET nombre=?, descripcion=?, precio_base=?, imagen_url=?, disponible=? WHERE id_pastel=?");
                    $stmt->execute([$nombre,$desc,$precio,$imagen_url,$disponible,$id]);
                } else {
                    $stmt = $db->prepare("UPDATE pasteles SET nombre=?, descripcion=?, precio_base=?, disponible=? WHERE id_pastel=?");
                    $stmt->execute([$nombre,$desc,$precio,$disponible,$id]);
                }
                $msg = 'Pastel actualizado.';
            }
        }
    } elseif ($action === 'eliminar') {
        $id = intval($_POST['id_pastel']);
        // Remove image
        $p = $db->prepare("SELECT imagen_url FROM pasteles WHERE id_pastel=?");
        $p->execute([$id]); $row = $p->fetch();
        if ($row && $row['imagen_url']) @unlink(UPLOAD_DIR . $row['imagen_url']);
        $db->prepare("DELETE FROM pasteles WHERE id_pastel=?")->execute([$id]);
        $msg = 'Pastel eliminado.';
    } elseif ($action === 'toggle') {
        $id = intval($_POST['id_pastel']);
        $db->prepare("UPDATE pasteles SET disponible = NOT disponible WHERE id_pastel=?")->execute([$id]);
        $msg = 'Disponibilidad actualizada.';
    }
}

$pasteles = $db->query("SELECT * FROM pasteles ORDER BY fecha_creacion DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Catálogo | Admin Miranda Cakes</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/css/style.css">
</head>
<body style="background:#FAF6F4">
<?php include '_sidebar.php'; ?>
<main class="admin-content">
  <div class="admin-header">
    <h4 style="font-family:'Playfair Display',serif;color:var(--brown-dark);margin:0">
      <i class="fa fa-images me-2" style="color:var(--rose-deep)"></i>Gestión de Catálogo
    </h4>
    <button class="btn-mc-primary" style="padding:0.5rem 1.5rem;font-size:0.85rem" onclick="document.getElementById('modal-agregar').style.display='flex'">
      <i class="fa fa-plus me-2"></i>Agregar Pastel
    </button>
  </div>

  <?php if ($msg): ?>
    <div class="alert" style="background:#D4EDDA;border:1px solid #C3E6CB;color:#155724;border-radius:10px;padding:0.8rem 1rem;margin-bottom:1rem"><i class="fa fa-check me-2"></i><?= sanitize($msg) ?></div>
  <?php endif; ?>
  <?php if ($err): ?>
    <div class="alert" style="background:#F8D7DA;border:1px solid #F5C6CB;color:#721C24;border-radius:10px;padding:0.8rem 1rem;margin-bottom:1rem"><i class="fa fa-exclamation me-2"></i><?= sanitize($err) ?></div>
  <?php endif; ?>

  <!-- GRID DE PASTELES -->
  <div class="row g-3">
    <?php foreach ($pasteles as $p): ?>
      <div class="col-md-6 col-xl-4">
        <div class="stat-card" style="padding:0;overflow:hidden">
          <?php if ($p['imagen_url']): ?>
            <div style="height:180px;overflow:hidden">
              <img src="<?= sanitize(UPLOAD_URL . $p['imagen_url']) ?>" style="width:100%;height:100%;object-fit:cover" alt="<?= sanitize($p['nombre']) ?>">
            </div>
          <?php else: ?>
            <div style="height:180px;background:linear-gradient(135deg,var(--blush),var(--rose-nude));display:flex;align-items:center;justify-content:center;font-size:4rem">🎂</div>
          <?php endif; ?>
          <div style="padding:1.2rem">
            <div class="d-flex justify-content-between align-items-start mb-1">
              <h6 style="font-family:'Playfair Display',serif;color:var(--brown-dark);margin:0"><?= sanitize($p['nombre']) ?></h6>
              <span style="font-weight:700;color:var(--rose-deep)">$<?= number_format($p['precio_base'],2) ?></span>
            </div>
            <p style="font-size:0.82rem;color:var(--text-light);margin-bottom:1rem;min-height:36px"><?= sanitize(substr($p['descripcion'],0,70)) ?>...</p>
            <div class="d-flex justify-content-between align-items-center">
              <form method="POST" style="margin:0">
                <input type="hidden" name="action" value="toggle">
                <input type="hidden" name="id_pastel" value="<?= $p['id_pastel'] ?>">
                <button type="submit" style="background:none;border:none;font-size:0.8rem;cursor:pointer;color:<?= $p['disponible'] ? '#388E3C' : 'var(--text-light)' ?>">
                  <i class="fa <?= $p['disponible'] ? 'fa-toggle-on' : 'fa-toggle-off' ?> me-1"></i>
                  <?= $p['disponible'] ? 'Disponible' : 'Oculto' ?>
                </button>
              </form>
              <div class="d-flex gap-2">
                <button onclick='editarPastel(<?= json_encode($p) ?>)' style="background:none;border:none;color:var(--rose-deep);cursor:pointer"><i class="fa fa-edit"></i></button>
                <form method="POST" style="margin:0" onsubmit="return confirm('¿Eliminar este pastel?')">
                  <input type="hidden" name="action" value="eliminar">
                  <input type="hidden" name="id_pastel" value="<?= $p['id_pastel'] ?>">
                  <button type="submit" style="background:none;border:none;color:#DC3545;cursor:pointer"><i class="fa fa-trash"></i></button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</main>

<!-- MODAL AGREGAR/EDITAR -->
<div id="modal-agregar" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;padding:1rem">
  <div style="background:white;border-radius:20px;padding:2rem;width:100%;max-width:500px;max-height:90vh;overflow-y:auto">
    <h5 id="modal-title" style="font-family:'Playfair Display',serif;color:var(--brown-dark);margin-bottom:1.5rem">Agregar Pastel</h5>
    <form method="POST" enctype="multipart/form-data" class="form-mc" id="pastel-form">
      <input type="hidden" name="action" id="form-action" value="agregar">
      <input type="hidden" name="id_pastel" id="form-id">
      <div class="mb-3">
        <label>Nombre del pastel *</label>
        <input type="text" name="nombre" id="form-nombre" class="form-control" required>
      </div>
      <div class="mb-3">
        <label>Descripción</label>
        <textarea name="descripcion" id="form-desc" class="form-control" rows="3"></textarea>
      </div>
      <div class="mb-3">
        <label>Precio base *</label>
        <div style="position:relative">
          <span style="position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:var(--text-light)">$</span>
          <input type="number" name="precio_base" id="form-precio" class="form-control" step="0.01" min="0" required style="padding-left:2rem">
        </div>
      </div>
      <div class="mb-3">
        <label>Imagen</label>
        <div class="upload-area" onclick="document.getElementById('img-input').click()">
          <i class="fa fa-cloud-upload-alt" style="font-size:2rem;color:var(--rose-deep);margin-bottom:0.5rem;display:block"></i>
          <div style="font-size:0.9rem;color:var(--text-mid)">Haz clic para subir imagen</div>
          <div style="font-size:0.78rem;color:var(--text-light)">JPG, PNG, WEBP — máx 5MB</div>
          <div id="img-preview" style="margin-top:0.5rem"></div>
        </div>
        <input type="file" name="imagen" id="img-input" accept="image/*" style="display:none" onchange="previewImg(this)">
      </div>
      <div class="mb-3 form-check">
        <input type="checkbox" name="disponible" id="form-disponible" class="form-check-input" checked style="accent-color:var(--rose-deep)">
        <label class="form-check-label" for="form-disponible" style="font-size:0.9rem;color:var(--text-mid)">Disponible en catálogo</label>
      </div>
      <div class="d-flex gap-2">
        <button type="submit" class="btn-mc-primary flex-fill" style="border-radius:10px">Guardar</button>
        <button type="button" class="btn-mc-outline" onclick="cerrarModal()" style="border-radius:10px;padding:0.7rem 1.5rem">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
function previewImg(input) {
  const preview = document.getElementById('img-preview');
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      preview.innerHTML = `<img src="${e.target.result}" style="max-height:100px;border-radius:8px;margin-top:0.5rem">`;
    };
    reader.readAsDataURL(input.files[0]);
  }
}
function editarPastel(p) {
  document.getElementById('modal-title').textContent = 'Editar Pastel';
  document.getElementById('form-action').value = 'editar';
  document.getElementById('form-id').value = p.id_pastel;
  document.getElementById('form-nombre').value = p.nombre;
  document.getElementById('form-desc').value = p.descripcion || '';
  document.getElementById('form-precio').value = p.precio_base;
  document.getElementById('form-disponible').checked = p.disponible == 1;
  document.getElementById('modal-agregar').style.display = 'flex';
}
function cerrarModal() {
  document.getElementById('modal-agregar').style.display = 'none';
  document.getElementById('form-action').value = 'agregar';
  document.getElementById('form-id').value = '';
  document.getElementById('pastel-form').reset();
  document.getElementById('modal-title').textContent = 'Agregar Pastel';
  document.getElementById('img-preview').innerHTML = '';
}
document.getElementById('modal-agregar').addEventListener('click', function(e) {
  if (e.target === this) cerrarModal();
});
</script>
</body>
</html>
