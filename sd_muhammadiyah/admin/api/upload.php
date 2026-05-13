<?php
/**
 * admin/api/upload.php
 * Secure AJAX file upload endpoint for all admin CMS uploads
 * Returns JSON { ok, url, filename, msg }
 */
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once ROOT_PATH . '/includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

// Only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed.');
}
requireCsrf();

// ── Config ────────────────────────────────────────────────
$allowedTypes = [
    'image/jpeg', 'image/jpg', 'image/png',
    'image/gif', 'image/webp'
];
$maxSize      = 5 * 1024 * 1024; // 5MB
$validDirs    = [
    'announcements', 'gallery', 'teachers', 'staff',
    'facilities', 'ekskul', 'achievements', 'logos',
    'heroes', 'general', 'complaints',
];

// ── Validate inputs ───────────────────────────────────────
$dir = trim($_POST['dir'] ?? 'general');
if (!in_array($dir, $validDirs)) {
    jsonResponse(false, 'Upload directory tidak valid.');
}

if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $errMap = [
        UPLOAD_ERR_INI_SIZE   => 'File terlalu besar (melebihi batas server).',
        UPLOAD_ERR_FORM_SIZE  => 'File terlalu besar.',
        UPLOAD_ERR_PARTIAL    => 'File tidak terupload sempurna.',
        UPLOAD_ERR_NO_FILE    => 'Tidak ada file yang dipilih.',
        UPLOAD_ERR_NO_TMP_DIR => 'Folder temp tidak ditemukan.',
        UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk.',
        UPLOAD_ERR_EXTENSION  => 'Upload dibatalkan oleh ekstensi PHP.',
    ];
    $code = $_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE;
    jsonResponse(false, $errMap[$code] ?? 'Error upload tidak diketahui.');
}

$file = $_FILES['file'];

// Validate MIME type (re-check server-side)
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime  = $finfo->file($file['tmp_name']);
if (!in_array($mime, $allowedTypes)) {
    jsonResponse(false, 'Tipe file tidak diizinkan. Gunakan JPG, PNG, GIF, atau WebP.');
}

// Validate size
if ($file['size'] > $maxSize) {
    jsonResponse(false, 'Ukuran file melebihi batas 5MB.');
}

// ── Save file ─────────────────────────────────────────────
$ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$safeExt  = in_array($ext, ['jpg','jpeg','png','gif','webp']) ? $ext : 'jpg';
$filename = uniqid('img_', true) . '.' . $safeExt;
$destDir  = UPLOAD_PATH . $dir . '/';
$destPath = $destDir . $filename;

if (!is_dir($destDir)) {
    if (!mkdir($destDir, 0755, true)) {
        jsonResponse(false, 'Gagal membuat direktori upload.');
    }
}

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    jsonResponse(false, 'Gagal memindahkan file. Periksa permission folder.');
}

// ── Resize (optional, keeps aspect ratio) ────────────────
$shouldResize = in_array($dir, ['gallery','facilities','announcements','ekskul','achievements','heroes']);
if ($shouldResize) {
    resizeImage($destPath, 1400, 1050);
}

// ── Return success ────────────────────────────────────────
$url = UPLOAD_URL . $dir . '/' . $filename;

jsonResponse(true, 'File berhasil diupload.', [
    'filename' => $filename,
    'url'      => $url,
    'dir'      => $dir,
    'size'     => filesize($destPath),
    'mime'     => $mime,
]);