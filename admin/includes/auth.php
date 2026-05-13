<?php
// admin/includes/auth.php — Include at top of every admin page
// Usage: require_once __DIR__ . '/auth.php';

require_once __DIR__ . '/../../includes/config.php';

if (empty($_SESSION['admin_id'])) {
    header('Location: ' . APP_URL . '/admin/login.php');
    exit;
}

// Refresh session lifetime
$_SESSION['last_activity'] = time();

// Role helper
function admin_role(): string { return $_SESSION['admin_role'] ?? 'editor'; }
function is_superadmin(): bool { return admin_role() === 'superadmin'; }
function is_admin(): bool      { return in_array(admin_role(), ['superadmin','admin']); }
function require_admin(): void {
    if (!is_admin()) {
        http_response_code(403);
        die(json_encode(['success'=>false,'message'=>'Akses ditolak.']));
    }
}
