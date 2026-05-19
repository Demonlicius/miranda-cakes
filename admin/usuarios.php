<?php
ob_start();
require_once '/var/www/html/includes/config.php';

// Validar que el usuario sea administrador
requireAdmin();
$db = getDB();

// Obtener todos los usuarios registrados
$stmt = $db->query("SELECT id_usuario, nombre, email, rol, telefono FROM usuarios ORDER BY id_usuario DESC");
$usuarios = $stmt->fetchAll();

include '/var/www/html/includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-3">
            <?php include '/var/www/html/admin/_sidebar.php'; ?>
        </div>
        <div class="col-md-9">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h4 class="mb-0">Gestión de Usuarios</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Correo Electrónico</th>
                                    <th>Teléfono</th>
                                    <th>Rol</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuarios as $u): ?>
                                <tr>
                                    <td><?php echo $u['id_usuario']; ?></td>
                                    <td><?php echo htmlspecialchars($u['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td><?php echo htmlspecialchars($u['telefono'] ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="badge <?php echo $u['rol'] === 'admin' ? 'bg-danger' : 'bg-primary'; ?>">
                                            <?php echo strtoupper($u['rol']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
include '/var/www/html/includes/footer.php';
ob_end_flush();
?>
