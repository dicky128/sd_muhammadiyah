<?php
// ============================================================
//  config.php — Central Configuration & PDO Bootstrap
// ============================================================

// ---- Environment ----
define('APP_ENV', 'development'); 
define('APP_NAME', 'SD Muhammadiyah 1 Gentasari');

// Solusi Auto-Detect URL agar port tidak bentrok
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host     = $_SERVER['HTTP_HOST']; // Mengambil localhost:8080 secara otomatis
$baseDir  = '/sd_muhammadiyah';    // Folder proyek Anda di htdocs

define('APP_URL', $protocol . "://" . $host . $baseDir);
define('APP_VERSION', '1.0.0');

// ---- Database ----
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'sd_muhammadiyah');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ---- Paths ----
define('ROOT_PATH', dirname(__FILE__) . '/');
define('UPLOAD_PATH', ROOT_PATH . '/../assets/images/uploads/');
define('UPLOAD_URL',  APP_URL . '/assets/images/uploads/');

// ---- Session ----
define('SESSION_NAME', 'sdmuh_session');
define('SESSION_LIFETIME', 7200); // 2 hours

// ---- Security ----
define('CSRF_TOKEN_KEY', 'csrf_token');

// ============================================================
//  PDO Singleton
// ============================================================
class Database {
    private static ?PDO $instance = null;

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s',
                DB_HOST, DB_PORT, DB_NAME, DB_CHARSET);
            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                ]);
            } catch (PDOException $e) {
                if (APP_ENV === 'development') {
                    die('<pre style="color:red">DB Error: ' . $e->getMessage() . '</pre>');
                }
                die('Service temporarily unavailable. Please try again later.');
            }
        }
        return self::$instance;
    }

    private function __construct() {}
    private function __clone() {}
}

// Convenience shorthand
function db(): PDO { return Database::getInstance(); }

// ============================================================
//  Session Bootstrap
// ============================================================
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
session_name(SESSION_NAME);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================================
//  CSRF Helpers
// ============================================================
function csrf_token(): string {
    if (empty($_SESSION[CSRF_TOKEN_KEY])) {
        $_SESSION[CSRF_TOKEN_KEY] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_KEY];
}
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}
function verify_csrf(): bool {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return hash_equals(csrf_token(), $token);
}

// ============================================================
//  Utility Helpers
// ============================================================
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}
function slug(string $text): string {
    $text = mb_strtolower($text, 'UTF-8');
    $text = preg_replace('/[^a-z0-9\s-]/u', '', $text);
    return trim(preg_replace('/[\s-]+/', '-', $text), '-');
}
function timeAgo(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60)   return 'Baru saja';
    if ($diff < 3600) return floor($diff/60) . ' menit lalu';
    if ($diff < 86400) return floor($diff/3600) . ' jam lalu';
    if ($diff < 604800) return floor($diff/86400) . ' hari lalu';
    return date('d M Y', strtotime($datetime));
}
function isNew(string $datetime, int $days = 7): bool {
    return (time() - strtotime($datetime)) < ($days * 86400);
}
function setting(string $key, string $default = ''): string {
    static $cache = [];
    if (!isset($cache[$key])) {
        try {
            $stmt = db()->prepare("SELECT `value` FROM settings WHERE `key` = ?");
            $stmt->execute([$key]);
            $row = $stmt->fetch();
            $cache[$key] = $row ? $row['value'] : $default;
        } catch (Exception $e) {
            return $default;
        }
    }
    return $cache[$key] ?? $default;
}
function uploadFile(array $file, string $dir = ''): string|false {
    $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
    if (!in_array($file['type'], $allowed)) return false;
    if ($file['size'] > 5 * 1024 * 1024) return false; // 5MB
    $ext  = pathinfo($file['name'], PATHINFO_EXTENSION);
    $name = uniqid('img_', true) . '.' . strtolower($ext);
    $dest = UPLOAD_PATH . ($dir ? $dir . '/' : '') . $name;
    if (!is_dir(dirname($dest))) mkdir(dirname($dest), 0755, true);
    return move_uploaded_file($file['tmp_name'], $dest) ? $name : false;
}

// Auto-load extended helper library
if (file_exists(ROOT_PATH . 'functions.php') && !function_exists('formatDate')) {
    require_once ROOT_PATH . 'functions.php';
}