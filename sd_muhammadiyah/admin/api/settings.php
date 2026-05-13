<?php
/**
 * admin/api/settings.php
 * AJAX settings read & write endpoint
 * GET  ?key=xxx            → { ok, key, value }
 * GET  ?keys=a,b,c         → { ok, settings: {k:v,...} }
 * POST { key, value }      → { ok, msg }
 * POST { settings: [...] } → { ok, msg, saved }
 * POST { action: danger }  → { ok, msg }
 */
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once ROOT_PATH . '/includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

// ── GET: read one or multiple settings ───────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!empty($_GET['key'])) {
        $key = preg_replace('/[^a-z0-9_]/', '', strtolower($_GET['key']));
        jsonResponse(true, '', ['key' => $key, 'value' => setting($key)]);
    }

    if (!empty($_GET['keys'])) {
        $keys   = explode(',', $_GET['keys']);
        $result = [];
        foreach ($keys as $k) {
            $k = preg_replace('/[^a-z0-9_]/', '', strtolower(trim($k)));
            if ($k) $result[$k] = setting($k);
        }
        jsonResponse(true, '', ['settings' => $result]);
    }

    // Return all settings grouped
    try {
        $rows = db()->query("SELECT `key`,`value`,`group`,`label` FROM settings ORDER BY `group`,`key`")->fetchAll();
        $grouped = [];
        foreach ($rows as $r) {
            $grouped[$r['group']][$r['key']] = ['value' => $r['value'], 'label' => $r['label']];
        }
        jsonResponse(true, '', ['settings' => $grouped]);
    } catch (Exception $e) {
        jsonResponse(false, 'Database error: ' . $e->getMessage());
    }
}

// ── POST: write ───────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed.');
}
requireCsrf();

$body = [];
$raw  = file_get_contents('php://input');
if ($raw && str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
    $body = json_decode($raw, true) ?? [];
}
$data = array_merge($_POST, $body);

$action = $data['action'] ?? '';

// ── Danger zone actions ───────────────────────────────────
if ($action === 'danger') {
    if (!is_superadmin()) {
        jsonResponse(false, 'Hanya superadmin yang dapat melakukan tindakan ini.');
    }

    $dangerAction = $data['danger_action'] ?? '';
    try {
        switch ($dangerAction) {
            case 'reset':
                // Reset settings to defaults (keep structure, clear custom values)
                $defaults = [
                    'hero_title'    => 'Sekolah Dasar Islam Unggulan',
                    'hero_subtitle' => 'Membentuk Generasi Cerdas Berakhlak Mulia',
                    'stats_students'=> '380',
                    'stats_teachers'=> '24',
                    'stats_years'   => '62',
                    'stats_ekskul'  => '12',
                    'site_title'    => APP_NAME,
                    'site_tagline'  => 'Cerdas, Berkarakter, Islami',
                    'maintenance_mode' => '0',
                    'google_analytics' => '',
                    'meta_description' => '',
                    'meta_keywords'    => '',
                ];
                $stmt = db()->prepare("UPDATE settings SET `value`=? WHERE `key`=?");
                foreach ($defaults as $k => $v) $stmt->execute([$v, $k]);
                jsonResponse(true, count($defaults) . ' pengaturan berhasil direset ke default.');

            case 'clear_gallery':
                $count = (int)db()->query("SELECT COUNT(*) FROM gallery")->fetchColumn();
                db()->exec("TRUNCATE TABLE gallery");
                jsonResponse(true, "$count foto galeri berhasil dihapus dari database.");

            case 'clear_inbox':
                $msgs  = (int)db()->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();
                $comps = (int)db()->query("SELECT COUNT(*) FROM complaints")->fetchColumn();
                db()->exec("TRUNCATE TABLE contact_messages");
                db()->exec("TRUNCATE TABLE complaints");
                jsonResponse(true, "Berhasil menghapus $msgs pesan dan $comps pengaduan.");

            default:
                jsonResponse(false, 'Tindakan tidak dikenal.');
        }
    } catch (Exception $e) {
        jsonResponse(false, 'Error: ' . $e->getMessage());
    }
}

// ── Batch settings save ───────────────────────────────────
if (!empty($data['settings']) && is_array($data['settings'])) {
    if (!can('write')) jsonResponse(false, 'Akses ditolak.');
    $saved = 0;
    try {
        $stmt = db()->prepare(
            "INSERT INTO settings (`key`, `value`) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)"
        );
        foreach ($data['settings'] as $setting) {
            $key = preg_replace('/[^a-z0-9_]/', '', strtolower($setting['key'] ?? ''));
            if (!$key) continue;
            $stmt->execute([$key, $setting['value'] ?? '']);
            $saved++;
        }
        jsonResponse(true, "$saved pengaturan berhasil disimpan.", ['saved' => $saved]);
    } catch (Exception $e) {
        jsonResponse(false, 'Error: ' . $e->getMessage());
    }
}

// ── Single key/value save ─────────────────────────────────
if (!empty($data['key'])) {
    if (!can('write')) jsonResponse(false, 'Akses ditolak.');
    $key   = preg_replace('/[^a-z0-9_]/', '', strtolower($data['key']));
    $value = trim($data['value'] ?? '');

    if (!$key) jsonResponse(false, 'Key tidak valid.');
    try {
        db()->prepare(
            "INSERT INTO settings (`key`, `value`) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)"
        )->execute([$key, $value]);
        jsonResponse(true, "Pengaturan '$key' berhasil disimpan.", ['key' => $key, 'value' => $value]);
    } catch (Exception $e) {
        jsonResponse(false, 'Error: ' . $e->getMessage());
    }
}

jsonResponse(false, 'Request tidak valid. Sertakan key/value atau settings array.');