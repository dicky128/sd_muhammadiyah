<?php
/**
 * includes/functions.php
 * ─────────────────────────────────────────────────────────────────────────────
 * SD Muhammadiyah 1 Gentasari — Central Helper Library
 * All reusable functions beyond config.php primitives
 * ─────────────────────────────────────────────────────────────────────────────
 */

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// ── Auto-load config if not already loaded ───────────────────────────────────
if (!function_exists('db')) {
    require_once ROOT_PATH . '/includes/config.php';
}

/* ══════════════════════════════════════════════════════════
   1. DATE & TIME HELPERS
══════════════════════════════════════════════════════════ */

/**
 * Format date in Indonesian locale
 * @param string $datetime  MySQL datetime string
 * @param string $format    'full'|'short'|'time'|'relative'
 */
function formatDate(string $datetime, string $format = 'full'): string {
    if (!$datetime) return '—';
    $ts = strtotime($datetime);
    if (!$ts) return '—';

    $months = ['','Januari','Februari','Maret','April','Mei','Juni',
               'Juli','Agustus','September','Oktober','November','Desember'];
    $days   = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];

    switch ($format) {
        case 'full':
            return $days[date('w',$ts)].', '.date('j',$ts).' '.$months[(int)date('n',$ts)].' '.date('Y',$ts);
        case 'short':
            return date('j',$ts).' '.$months[(int)date('n',$ts)].' '.date('Y',$ts);
        case 'time':
            return date('j',$ts).' '.$months[(int)date('n',$ts)].' '.date('Y, H:i',$ts).' WIB';
        case 'relative':
            return timeAgo($datetime);
        default:
            return date($format, $ts);
    }
}

/**
 * Check if datetime is within $days days from now
 */
function isRecent(string $datetime, int $days = 7): bool {
    return (time() - strtotime($datetime)) < ($days * 86400);
}

/* ══════════════════════════════════════════════════════════
   2. STRING HELPERS
══════════════════════════════════════════════════════════ */

/**
 * Truncate string to $length chars with ellipsis, strip HTML
 */
function excerpt(string $text, int $length = 150): string {
    $plain = strip_tags($text);
    $plain = html_entity_decode($plain, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $plain = preg_replace('/\s+/', ' ', trim($plain));
    if (mb_strlen($plain) <= $length) return $plain;
    return mb_substr($plain, 0, $length) . '…';
}

/**
 * Generate URL-safe slug from string (Indonesian aware)
 */
function makeSlug(string $text): string {
    $text = mb_strtolower(trim($text), 'UTF-8');
    $map  = ['ä'=>'a','ö'=>'o','ü'=>'u','ñ'=>'n','é'=>'e','è'=>'e','ê'=>'e'];
    $text = strtr($text, $map);
    $text = preg_replace('/[^a-z0-9\s\-]/u', '', $text);
    $text = preg_replace('/[\s\-]+/', '-', $text);
    return trim($text, '-');
}

/**
 * Generate unique slug (checks DB for collisions)
 */
function uniqueSlug(string $text, string $table, string $col = 'slug', int $excludeId = 0): string {
    $base = $i = 0;
    $slug = makeSlug($text);
    $base = $slug;
    while (true) {
        $q = db()->prepare("SELECT id FROM `$table` WHERE `$col` = ?" . ($excludeId ? " AND id != $excludeId" : ''));
        $q->execute([$slug]);
        if (!$q->fetch()) break;
        $slug = $base . '-' . (++$i);
    }
    return $slug;
}

/**
 * Word count of plain text
 */
function wordCount(string $text): int {
    return str_word_count(strip_tags($text));
}

/**
 * Read time estimate (words per minute)
 */
function readTime(string $text, int $wpm = 200): string {
    $mins = max(1, (int)ceil(wordCount($text) / $wpm));
    return $mins . ' mnt baca';
}

/**
 * Format number to Indonesian locale (1234 → 1.234)
 */
function formatNumber(int|float $n, int $decimals = 0): string {
    return number_format($n, $decimals, ',', '.');
}

/* ══════════════════════════════════════════════════════════
   3. PAGINATION
══════════════════════════════════════════════════════════ */

/**
 * Generate pagination array
 * Returns ['total','totalPages','page','perPage','offset']
 */
function paginate(int $total, int $perPage = 12, string $pageKey = 'p'): array {
    $page   = max(1, (int)($_GET[$pageKey] ?? 1));
    $pages  = max(1, (int)ceil($total / $perPage));
    $page   = min($page, $pages);
    return [
        'total'      => $total,
        'totalPages' => $pages,
        'page'       => $page,
        'perPage'    => $perPage,
        'offset'     => ($page - 1) * $perPage,
    ];
}

/**
 * Render pagination HTML (light-mode style)
 */
function paginationHTML(array $p, string $baseUrl = ''): string {
    if ($p['totalPages'] <= 1) return '';
    $url  = $baseUrl ?: strtok($_SERVER['REQUEST_URI'], '?');
    $q    = $_GET; unset($q['p']);
    $qStr = $q ? '?' . http_build_query($q) . '&' : '?';

    $html = '<div class="flex justify-center gap-2 mt-12">';
    for ($i = 1; $i <= $p['totalPages']; $i++) {
        $active = $i === $p['page'];
        $style  = $active ? 'style="background:linear-gradient(135deg,#f472b6,#d4aa3a)"' : '';
        $cls    = $active
            ? 'w-10 h-10 rounded-xl flex items-center justify-center text-sm font-bold text-white'
            : 'w-10 h-10 rounded-xl flex items-center justify-center text-sm font-semibold text-gray-500 hover:text-pink-500 transition-colors glass-card';
        $html  .= "<a href=\"{$url}{$qStr}p={$i}\" class=\"{$cls}\" {$style}>{$i}</a>";
    }
    $html .= '</div>';
    return $html;
}

/* ══════════════════════════════════════════════════════════
   4. IMAGE HELPERS
══════════════════════════════════════════════════════════ */

/**
 * Return URL for an uploaded file, or a placeholder gradient SVG
 * @param string|null $filename  DB filename (no path)
 * @param string      $subdir    e.g. 'gallery', 'teachers'
 * @param string      $type      'image'|'avatar'
 */
function assetUrl(?string $filename, string $subdir = '', string $type = 'image'): string {
    if (!empty($filename)) {
        $dir = $subdir ? trim($subdir, '/') . '/' : '';
        return UPLOAD_URL . $dir . $filename;
    }
    // Placeholder gradient
    $gradients = [
        'image'  => '%23fdf2f8,%23e0f2fe',
        'avatar' => '%23fbcfe8,%23fef3c7',
    ];
    $g = $gradients[$type] ?? $gradients['image'];
    return "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='300'%3E%3Cdefs%3E%3ClinearGradient id='g' x1='0' y1='0' x2='1' y2='1'%3E%3Cstop offset='0' stop-color='{$g}'/%3E%3C/linearGradient%3E%3C/defs%3E%3Crect fill='url(%23g)' width='400' height='300'/%3E%3C/svg%3E";
}

/**
 * Resize/crop image after upload (requires GD)
 * @param string $src   Absolute file path
 * @param int    $maxW  Max width
 * @param int    $maxH  Max height
 */
function resizeImage(string $src, int $maxW = 1200, int $maxH = 900): bool {
    if (!function_exists('imagecreatefromjpeg')) return false;
    $info = @getimagesize($src);
    if (!$info) return false;

    [$origW, $origH, $type] = $info;

    // Skip if already within bounds
    if ($origW <= $maxW && $origH <= $maxH) return true;

    $ratio  = min($maxW / $origW, $maxH / $origH);
    $newW   = (int)round($origW * $ratio);
    $newH   = (int)round($origH * $ratio);

    $src_img = match($type) {
        IMAGETYPE_JPEG => @imagecreatefromjpeg($src),
        IMAGETYPE_PNG  => @imagecreatefrompng($src),
        IMAGETYPE_WEBP => @imagecreatefromwebp($src),
        default        => false,
    };
    if (!$src_img) return false;

    $dst_img = imagecreatetruecolor($newW, $newH);
    // Preserve transparency
    if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_WEBP) {
        imagealphablending($dst_img, false);
        imagesavealpha($dst_img, true);
    }

    imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

    $ok = match($type) {
        IMAGETYPE_JPEG => imagejpeg($dst_img, $src, 88),
        IMAGETYPE_PNG  => imagepng($dst_img, $src, 7),
        IMAGETYPE_WEBP => imagewebp($dst_img, $src, 88),
        default        => false,
    };

    imagedestroy($src_img);
    imagedestroy($dst_img);
    return (bool)$ok;
}

/**
 * Handle file upload + optional resize
 * Returns filename or false
 */
function handleUpload(array $file, string $subdir, bool $resize = true): string|false {
    $filename = uploadFile($file, $subdir);
    if (!$filename) return false;

    if ($resize) {
        $fullPath = UPLOAD_PATH . ($subdir ? trim($subdir,'/') . '/' : '') . $filename;
        resizeImage($fullPath);
    }
    return $filename;
}

/* ══════════════════════════════════════════════════════════
   5. ANNOUNCEMENT HELPERS
══════════════════════════════════════════════════════════ */

/**
 * Return array of recent published announcements
 */
function getAnnouncements(int $limit = 6, string $category = ''): array {
    try {
        $where  = 'WHERE is_published = 1';
        $params = [];
        if ($category) { $where .= ' AND category = ?'; $params[] = $category; }
        $stmt = db()->prepare(
            "SELECT a.*, u.full_name AS author
             FROM announcements a
             LEFT JOIN admin_users u ON a.author_id = u.id
             $where ORDER BY is_pinned DESC, published_at DESC LIMIT $limit"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Badge HTML for "Terbaru" tag (light mode)
 */
function newBadge(string $datetime, int $days = 7): string {
    if (!isRecent($datetime, $days)) return '';
    return '<span class="badge-new-light">✦ Terbaru</span>';
}

/**
 * Category label class (light mode)
 */
function catLabelClass(string $category): string {
    return match($category) {
        'penting'  => 'section-label-pink',
        'akademik' => 'section-label-sky',
        'kegiatan' => 'section-label-gold',
        default    => 'section-label-gold',
    };
}

/* ══════════════════════════════════════════════════════════
   6. SCHOOL DATA HELPERS
══════════════════════════════════════════════════════════ */

/**
 * Get school profile (cached per request)
 */
function getSchoolProfile(): array {
    static $profile = null;
    if ($profile === null) {
        try {
            $profile = db()->query("SELECT * FROM school_profile LIMIT 1")->fetch() ?: [];
        } catch (Exception $e) {
            $profile = [];
        }
    }
    return $profile;
}

/**
 * Get total active student count
 */
function getTotalStudents(): int {
    try {
        $year  = db()->query("SELECT MAX(academic_year) FROM student_stats")->fetchColumn();
        if (!$year) return (int)setting('stats_students', 0);
        $count = db()->prepare("SELECT SUM(count) FROM student_stats WHERE academic_year = ?");
        $count->execute([$year]);
        return (int)$count->fetchColumn();
    } catch (Exception $e) {
        return (int)setting('stats_students', 0);
    }
}

/* ══════════════════════════════════════════════════════════
   7. ADMIN UTILITIES
══════════════════════════════════════════════════════════ */

/**
 * Check if current admin can perform action
 * @param string $action 'read'|'write'|'delete'|'superadmin'
 */
function can(string $action): bool {
    $role = $_SESSION['admin_role'] ?? '';
    return match($action) {
        'read'       => in_array($role, ['superadmin','admin','editor']),
        'write'      => in_array($role, ['superadmin','admin','editor']),
        'delete'     => in_array($role, ['superadmin','admin']),
        'superadmin' => $role === 'superadmin',
        default      => false,
    };
}

/**
 * Set flash message (admin)
 */
function flash(string $type, string $msg): void {
    $_SESSION['flash'] = compact('type', 'msg');
}

/**
 * Get and clear flash message
 */
function getFlash(): array|null {
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

/**
 * Generate a JSON response and exit
 */
function jsonResponse(bool $ok, string $msg = '', array $data = []): never {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge(['ok' => $ok, 'msg' => $msg], $data));
    exit;
}

/**
 * Validate CSRF and return JSON error if invalid (for AJAX endpoints)
 */
function requireCsrf(): void {
    if (!verify_csrf()) {
        jsonResponse(false, 'Token keamanan tidak valid. Refresh halaman dan coba lagi.');
    }
}

/* ══════════════════════════════════════════════════════════
   8. SEO HELPERS
══════════════════════════════════════════════════════════ */

/**
 * Generate <meta> tags for a page
 */
function seoMeta(array $opts = []): string {
    $profile = getSchoolProfile();
    $title   = $opts['title']   ?? ($profile['school_name'] ?? APP_NAME);
    $desc    = $opts['desc']    ?? setting('meta_description', 'Website resmi ' . APP_NAME);
    $image   = $opts['image']   ?? (setting('og_image') ? UPLOAD_URL . 'general/' . setting('og_image') : '');
    $url     = $opts['url']     ?? (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $type    = $opts['type']    ?? 'website';

    $title   = e($title);
    $desc    = e(mb_substr(strip_tags($desc), 0, 160));

    $html  = "<meta property=\"og:title\" content=\"{$title}\">\n";
    $html .= "<meta property=\"og:description\" content=\"{$desc}\">\n";
    $html .= "<meta property=\"og:type\" content=\"{$type}\">\n";
    $html .= "<meta property=\"og:url\" content=\"{$url}\">\n";
    $html .= "<meta property=\"og:site_name\" content=\"{$title}\">\n";
    if ($image) $html .= "<meta property=\"og:image\" content=\"{$image}\">\n";
    $html .= "<meta name=\"twitter:card\" content=\"summary_large_image\">\n";
    $html .= "<meta name=\"twitter:title\" content=\"{$title}\">\n";
    $html .= "<meta name=\"twitter:description\" content=\"{$desc}\">\n";
    return $html;
}

/**
 * Build canonical URL for current page
 */
function canonicalUrl(): string {
    $base = setting('canonical_url') ?: APP_URL;
    $path = $_SERVER['REQUEST_URI'] ?? '/';
    return rtrim($base, '/') . $path;
}

/* ══════════════════════════════════════════════════════════
   9. SECURITY UTILITIES
══════════════════════════════════════════════════════════ */

/**
 * Rate-limit by IP (uses sessions as simple store)
 * @param string $key     Action identifier
 * @param int    $max     Max attempts
 * @param int    $window  Time window in seconds
 */
function rateLimit(string $key, int $max = 5, int $window = 60): bool {
    $now   = time();
    $sKey  = 'rl_' . md5($key . ($_SERVER['REMOTE_ADDR'] ?? ''));
    $data  = $_SESSION[$sKey] ?? ['count' => 0, 'start' => $now];

    if ($now - $data['start'] > $window) {
        $data = ['count' => 0, 'start' => $now];
    }
    $data['count']++;
    $_SESSION[$sKey] = $data;
    return $data['count'] <= $max;
}

/**
 * Sanitize input array (trim + strip tags)
 */
function sanitize(array $data, array $skip = []): array {
    foreach ($data as $key => &$val) {
        if (in_array($key, $skip)) continue;
        $val = is_string($val) ? trim(strip_tags($val)) : $val;
    }
    return $data;
}

/**
 * Validate email format
 */
function validEmail(string $email): bool {
    return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate phone (Indonesian format)
 */
function validPhone(string $phone): bool {
    return (bool)preg_match('/^(\+62|62|0)[0-9]{8,13}$/', preg_replace('/[\s\-\(\)]/', '', $phone));
}

/* ══════════════════════════════════════════════════════════
   10. NOTIFICATION HELPERS
══════════════════════════════════════════════════════════ */

/**
 * Get admin notification counts for sidebar badges
 */
function getAdminNotifCounts(): array {
    try {
        return [
            'complaints' => (int)db()->query("SELECT COUNT(*) FROM complaints WHERE status='masuk'")->fetchColumn(),
            'messages'   => (int)db()->query("SELECT COUNT(*) FROM contact_messages WHERE is_read=0")->fetchColumn(),
        ];
    } catch (Exception $e) {
        return ['complaints' => 0, 'messages' => 0];
    }
}

/**
 * Render SweetAlert2 toast trigger script
 */
function toastScript(string $type, string $msg): string {
    $msg  = addslashes(e($msg));
    $type = in_array($type, ['success','error','warning','info']) ? $type : 'info';
    return "<script>document.addEventListener('DOMContentLoaded',()=>showToast('{$type}','{$msg}'));</script>";
}