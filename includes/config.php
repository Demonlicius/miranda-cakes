<?php
// ============================================
// MIRANDA CAKES - Configuración
// ============================================

define('DB_HOST', 'localhost');
define('DB_USER', 'miranda_user');
define('DB_PASS', 'MirandaCakes2024!');
define('DB_NAME', 'miranda_cakes');
define('SITE_NAME', 'Miranda Cakes');
define('SITE_URL', 'http://18.191.163.178/'); // Cambiar por tu dominio en AWS
define('UPLOAD_DIR', __DIR__ . '/../uploads/catalogo/');
define('UPLOAD_URL', SITE_URL . '/uploads/catalogo/');

// Conexión PDO
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            die(json_encode(['error' => 'Error de conexión: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}

// Inicio de sesión segura
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 7200,
        'path' => '/',
        'secure' => false, // true en producción con HTTPS
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

// Helpers
function isLoggedIn() {
    return isset($_SESSION['usuario_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /login.php');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if (!in_array($_SESSION['rol'], ['admin', 'superadmin'])) {
        header('Location: /index.php');
        exit;
    }
}

function requireSuperAdmin() {
    requireLogin();
    if ($_SESSION['rol'] !== 'superadmin') {
        header('Location: /admin/dashboard.php');
        exit;
    }
}

function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function jsonResponse($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function generateCode() {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $code = 'MC-';
    for ($i = 0; $i < 8; $i++) {
        $code .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $code;
}

function generateUniqueCode() {
    $db = getDB();
    do {
        $code = generateCode();
        $stmt = $db->prepare("SELECT id_recibo FROM recibos WHERE codigo_confirmacion = ?");
        $stmt->execute([$code]);
    } while ($stmt->fetch());
    return $code;
}
?>
