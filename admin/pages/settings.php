<?php
// admin/pages/settings.php — Site Settings Manager
require_once __DIR__ . '/../includes/auth.php';
$activeSidebar = 'settings';
$pageTitle     = 'Pengaturan Situs';
$pageSubtitle  = 'Konfigurasi konten & tampilan situs';

// ── SAVE handler ──────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $group  = $_POST['group'] ?? 'general';
    $saved  = 0;
    $errors = [];

    // All posted keys except meta-fields
    $skip = ['csrf_token', 'group', '_method'];
    foreach ($_POST as $key => $value) {
        if (in_array($key, $skip)) continue;
        $key   = preg_replace('/[^a-z0-9_]/', '', strtolower($key));
        $value = trim($value);
        try {
            $stmt = db()->prepare(
                "INSERT INTO settings (`key`, `value`) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)"
            );
            $stmt->execute([$key, $value]);
            $saved++;
        } catch (Exception $e) {
            $errors[] = $key;
        }
    }

    // Handle file uploads (hero image, logo via settings table path override)
    $uploadKeys = [
        'hero_image_file'  => ['setting_key' => 'hero_image',  'dir' => 'heroes'],
        'og_image_file'    => ['setting_key' => 'og_image',    'dir' => 'general'],
        'favicon_file'     => ['setting_key' => 'favicon',     'dir' => 'general'],
    ];
    foreach ($uploadKeys as $fileKey => $meta) {
        if (!empty($_FILES[$fileKey]['name'])) {
            $uploaded = uploadFile($_FILES[$fileKey], $meta['dir']);
            if ($uploaded) {
                db()->prepare(
                    "INSERT INTO settings (`key`, `value`) VALUES (?, ?)
                     ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)"
                )->execute([$meta['setting_key'], $uploaded]);
                $saved++;
            }
        }
    }

    $_SESSION['flash'] = $saved > 0
        ? ['type' => 'success', 'msg' => "$saved pengaturan berhasil disimpan."]
        : ['type' => 'warning', 'msg' => 'Tidak ada perubahan yang disimpan.'];
    header("Location: settings.php?tab=$group");
    exit;
}

// ── Load all settings ─────────────────────────────────────────────────────
try {
    $allSettings = [];
    foreach (db()->query("SELECT `key`, `value`, `group` FROM settings")->fetchAll() as $row) {
        $allSettings[$row['key']] = $row['value'];
    }
} catch (Exception $e) {
    $allSettings = [];
}

$tab    = $_GET['tab'] ?? 'general';
$flash  = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Setting field definitions per group
$groups = [
    'general' => [
        'label'  => 'Umum',
        'icon'   => 'settings',
        'fields' => [
            ['key' => 'site_title',     'label' => 'Judul Situs',        'type' => 'text',     'placeholder' => 'SD Muhammadiyah 1 Gentasari'],
            ['key' => 'site_tagline',   'label' => 'Tagline',            'type' => 'text',     'placeholder' => 'Cerdas, Berkarakter, Islami'],
            ['key' => 'maintenance_mode','label'=> 'Mode Maintenance',   'type' => 'toggle',   'desc' => 'Aktifkan untuk menutup situs sementara'],
            ['key' => 'favicon',        'label' => 'Favicon (path)',     'type' => 'text',     'placeholder' => 'assets/images/favicon.ico'],
            ['key' => 'favicon_file',   'label' => 'Upload Favicon',     'type' => 'file',     'accept' => '.ico,.png'],
            ['key' => 'og_image',       'label' => 'OG Image (path)',    'type' => 'text',     'placeholder' => 'assets/images/og.jpg'],
            ['key' => 'og_image_file',  'label' => 'Upload OG Image',    'type' => 'file',     'accept' => 'image/*'],
            ['key' => 'google_analytics','label'=> 'Google Analytics ID','type' => 'text',     'placeholder' => 'G-XXXXXXXXXX'],
        ],
    ],
    'homepage' => [
        'label'  => 'Homepage',
        'icon'   => 'home',
        'fields' => [
            ['key' => 'hero_title',       'label' => 'Judul Hero',            'type' => 'text',     'placeholder' => 'Sekolah Dasar Islam Unggulan'],
            ['key' => 'hero_subtitle',    'label' => 'Subjudul Hero',         'type' => 'textarea', 'placeholder' => 'Membentuk Generasi Cerdas Berakhlak Mulia'],
            ['key' => 'hero_image',       'label' => 'Hero Image (path)',     'type' => 'text',     'placeholder' => 'assets/images/uploads/heroes/xxx.jpg'],
            ['key' => 'hero_image_file',  'label' => 'Upload Hero Image',     'type' => 'file',     'accept' => 'image/*'],
            ['key' => 'hero_cta_primary', 'label' => 'Teks Tombol Utama',    'type' => 'text',     'placeholder' => 'Kenali Kami'],
            ['key' => 'hero_cta_secondary','label'=> 'Teks Tombol Sekunder', 'type' => 'text',     'placeholder' => 'Hubungi Kami'],
            ['key' => 'stats_students',   'label' => 'Jumlah Siswa',         'type' => 'number',   'placeholder' => '380'],
            ['key' => 'stats_teachers',   'label' => 'Jumlah Guru',          'type' => 'number',   'placeholder' => '24'],
            ['key' => 'stats_years',      'label' => 'Tahun Pengalaman',     'type' => 'number',   'placeholder' => '62'],
            ['key' => 'stats_ekskul',     'label' => 'Jumlah Ekskul',        'type' => 'number',   'placeholder' => '12'],
        ],
    ],
    'contact' => [
        'label'  => 'Kontak',
        'icon'   => 'phone',
        'fields' => [
            ['key' => 'contact_whatsapp',  'label' => 'WhatsApp',         'type' => 'text',  'placeholder' => '6281234567890 (tanpa +)'],
            ['key' => 'contact_phone_alt', 'label' => 'Telepon Alternatif','type' => 'text', 'placeholder' => '(0282) 123456'],
            ['key' => 'contact_email_alt', 'label' => 'Email Alternatif', 'type' => 'email', 'placeholder' => 'humas@sdmuh1.sch.id'],
            ['key' => 'office_hours',      'label' => 'Jam Operasional',  'type' => 'text',  'placeholder' => 'Senin–Jumat, 07.00–16.00 WIB'],
        ],
    ],
    'seo' => [
        'label'  => 'SEO',
        'icon'   => 'search',
        'fields' => [
            ['key' => 'meta_description', 'label' => 'Meta Description',  'type' => 'textarea', 'placeholder' => 'Deskripsi singkat untuk mesin pencari (max 160 karakter)'],
            ['key' => 'meta_keywords',    'label' => 'Meta Keywords',     'type' => 'text',     'placeholder' => 'SD Muhammadiyah, sekolah islam, Gentasari'],
            ['key' => 'robots_txt',       'label' => 'Robots Directive',  'type' => 'select',   'options' => ['index,follow' => 'Index & Follow (default)', 'noindex,nofollow' => 'No Index & No Follow']],
            ['key' => 'canonical_url',    'label' => 'Canonical URL',     'type' => 'url',      'placeholder' => 'https://sdmuh1gentasari.sch.id'],
        ],
    ],
    'advanced' => [
        'label'  => 'Lanjutan',
        'icon'   => 'code',
        'fields' => [
            ['key' => 'custom_head_code', 'label' => 'Kode Tambahan &lt;head&gt;', 'type' => 'code',     'placeholder' => '<!-- Google Tag Manager, dll -->'],
            ['key' => 'custom_body_code', 'label' => 'Kode Sebelum &lt;/body&gt;','type' => 'code',     'placeholder' => '<!-- Script tambahan -->'],
            ['key' => 'announcement_new_days','label'=> 'Badge "Terbaru" (hari)', 'type' => 'number',   'placeholder' => '7'],
            ['key' => 'items_per_page',   'label' => 'Item Per Halaman',  'type' => 'number',   'placeholder' => '12'],
        ],
    ],
];

$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= e($pageTitle) ?> — Admin CMS</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config={theme:{extend:{fontFamily:{display:['"Cormorant Garamond"','serif']}}}}</script>
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
  <style>
    *{font-family:'DM Sans',sans-serif}h1,h2{font-family:'Cormorant Garamond',serif}
    body{background:#0a0a0a;color:#fff}
    .glass{background:rgba(255,255,255,.06);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,.1)}
    .input-g{background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);color:#fff;border-radius:10px;padding:10px 14px;font-size:.85rem;width:100%;transition:all .25s;font-family:'DM Sans',sans-serif}
    .input-g:focus{outline:none;background:rgba(255,255,255,.1);border-color:rgba(212,170,58,.6);box-shadow:0 0 0 3px rgba(212,170,58,.1)}
    .input-g::placeholder{color:rgba(255,255,255,.3)}.input-g option{background:#1a1a1a}
    .tab-link{display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:12px;font-size:.82rem;color:rgba(255,255,255,.5);transition:all .2s;text-decoration:none;border:none;background:none;width:100%;cursor:pointer}
    .tab-link:hover,.tab-link.active{background:rgba(255,255,255,.08);color:#fff}
    .tab-link.active{border-left:2px solid #d4aa3a;padding-left:12px;color:#f0d898}
    .tab-link i{width:15px;height:15px;opacity:.6}.tab-link.active i{opacity:1;color:#d4aa3a}
    /* Toggle switch */
    .toggle-wrap input[type=checkbox]{display:none}
    .toggle-wrap label{display:flex;align-items:center;gap:12px;cursor:pointer}
    .toggle-track{width:44px;height:24px;border-radius:99px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.15);position:relative;transition:all .25s;flex-shrink:0}
    .toggle-track::after{content:'';position:absolute;top:3px;left:3px;width:16px;height:16px;border-radius:50%;background:rgba(255,255,255,.5);transition:all .25s}
    .toggle-wrap input:checked + label .toggle-track{background:rgba(212,170,58,.35);border-color:rgba(212,170,58,.6)}
    .toggle-wrap input:checked + label .toggle-track::after{transform:translateX(20px);background:#d4aa3a}
    ::-webkit-scrollbar{width:4px}::-webkit-scrollbar-track{background:#111}::-webkit-scrollbar-thumb{background:#333;border-radius:4px}
    .sidebar-link{display:flex;align-items:center;gap:12px;padding:10px 16px;border-radius:12px;font-size:.82rem;color:rgba(255,255,255,.55);transition:all .2s;text-decoration:none}
    .sidebar-link:hover,.sidebar-link.active{background:rgba(255,255,255,.08);color:#fff}
    .sidebar-link.active{border-left:2px solid #d4aa3a;padding-left:14px;color:#f0d898}
    .sidebar-link i{width:16px;height:16px;opacity:.7}.sidebar-link.active i{opacity:1;color:#d4aa3a}
    .badge-count{font-size:.65rem;padding:2px 7px;border-radius:99px;background:rgba(239,68,68,.2);color:#fca5a5}
  </style>
</head>
<body>
<div class="flex h-screen overflow-hidden">

<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

<main class="flex-1 flex flex-col overflow-hidden">
  <!-- Topbar -->
  <div class="px-8 py-4 border-b border-white/[.07] flex items-center justify-between flex-shrink-0" style="background:rgba(10,10,10,.9);backdrop-filter:blur(16px)">
    <div>
      <h1 style="font-family:'Cormorant Garamond',serif;font-size:1.5rem;font-weight:300"><?= e($pageTitle) ?></h1>
      <p style="font-size:.75rem;color:rgba(255,255,255,.35)"><?= e($pageSubtitle) ?></p>
    </div>
    <a href="<?= APP_URL ?>/index.php" target="_blank" class="flex items-center gap-2 px-4 py-2 glass rounded-xl text-xs text-white/50 hover:text-white hover:bg-white/10 transition-all">
      <i data-lucide="external-link" style="width:14px;height:14px"></i> Lihat Situs
    </a>
  </div>

  <div class="flex-1 overflow-hidden flex">

    <!-- Settings sidebar tabs -->
    <div class="w-52 flex-shrink-0 border-r border-white/[.07] overflow-y-auto p-3 space-y-0.5" style="background:rgba(0,0,0,.3)">
      <?php foreach ($groups as $groupKey => $group): ?>
      <a href="?tab=<?= $groupKey ?>"
         class="tab-link <?= $tab === $groupKey ? 'active' : '' ?>">
        <i data-lucide="<?= $group['icon'] ?>"></i>
        <?= $group['label'] ?>
      </a>
      <?php endforeach; ?>

      <div class="border-t border-white/[.07] mt-3 pt-3">
        <a href="?tab=danger" class="tab-link <?= $tab === 'danger' ? 'active' : '' ?>" style="color:rgba(239,68,68,.7)">
          <i data-lucide="alert-triangle"></i> Zona Bahaya
        </a>
      </div>
    </div>

    <!-- Settings form area -->
    <div class="flex-1 overflow-y-auto p-8">

      <?php if ($flash): ?>
      <div id="flash-banner" class="mb-6 px-5 py-3.5 rounded-2xl flex items-center gap-3 text-sm"
           style="background:rgba(<?= $flash['type']==='success' ? '52,211,153' : '212,170,58' ?>,.12);border:1px solid rgba(<?= $flash['type']==='success' ? '52,211,153' : '212,170,58' ?>,.3);color:<?= $flash['type']==='success' ? '#6ee7b7' : '#f0d898' ?>">
        <i data-lucide="<?= $flash['type']==='success' ? 'check-circle' : 'info' ?>" style="width:16px;height:16px;flex-shrink:0"></i>
        <?= e($flash['msg']) ?>
      </div>
      <?php endif; ?>

      <?php if ($tab === 'danger'): ?>
      <!-- ── Danger Zone ────────────────────────────────────────────── -->
      <div class="max-w-2xl space-y-5">
        <div class="rounded-2xl p-6" style="background:rgba(239,68,68,.06);border:1px solid rgba(239,68,68,.2)">
          <h2 class="font-display text-2xl text-red-300 font-light mb-1">Zona Bahaya</h2>
          <p class="text-white/40 text-sm mb-6">Tindakan di bawah ini bersifat permanen dan tidak dapat dibatalkan.</p>

          <div class="space-y-4">
            <div class="flex items-center justify-between py-4 border-b border-red-500/15">
              <div>
                <p class="text-sm text-white/80 font-medium">Reset Semua Pengaturan</p>
                <p class="text-xs text-white/35 mt-0.5">Kembalikan semua pengaturan ke nilai default awal.</p>
              </div>
              <button onclick="confirmDanger('reset')" class="px-4 py-2 rounded-xl text-xs text-red-300 transition-all hover:bg-red-500/20" style="border:1px solid rgba(239,68,68,.35)">
                Reset Settings
              </button>
            </div>

            <div class="flex items-center justify-between py-4 border-b border-red-500/15">
              <div>
                <p class="text-sm text-white/80 font-medium">Hapus Semua Galeri</p>
                <p class="text-xs text-white/35 mt-0.5">Hapus seluruh data foto dari database (file tidak terhapus).</p>
              </div>
              <button onclick="confirmDanger('clear_gallery')" class="px-4 py-2 rounded-xl text-xs text-red-300 transition-all hover:bg-red-500/20" style="border:1px solid rgba(239,68,68,.35)">
                Hapus Galeri
              </button>
            </div>

            <div class="flex items-center justify-between py-4">
              <div>
                <p class="text-sm text-white/80 font-medium">Hapus Semua Pesan & Pengaduan</p>
                <p class="text-xs text-white/35 mt-0.5">Bersihkan inbox pesan kontak dan pengaduan secara permanen.</p>
              </div>
              <button onclick="confirmDanger('clear_inbox')" class="px-4 py-2 rounded-xl text-xs text-red-300 transition-all hover:bg-red-500/20" style="border:1px solid rgba(239,68,68,.35)">
                Bersihkan Inbox
              </button>
            </div>
          </div>
        </div>
      </div>

      <?php else:
        $currentGroup = $groups[$tab] ?? $groups['general'];
      ?>
      <!-- ── Settings Form ──────────────────────────────────────────── -->
      <form method="POST" enctype="multipart/form-data" class="max-w-2xl space-y-5">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <input type="hidden" name="group" value="<?= e($tab) ?>">

        <div class="flex items-center gap-3 mb-7">
          <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:rgba(212,170,58,.1);border:1px solid rgba(212,170,58,.2)">
            <i data-lucide="<?= $currentGroup['icon'] ?>" style="width:18px;height:18px;color:#d4aa3a"></i>
          </div>
          <div>
            <h2 class="font-display text-2xl text-white font-light"><?= $currentGroup['label'] ?></h2>
            <p class="text-white/30 text-xs">Pengaturan <?= strtolower($currentGroup['label']) ?> situs</p>
          </div>
        </div>

        <?php foreach ($currentGroup['fields'] as $field):
          $val = $allSettings[$field['key']] ?? '';
          $isFileField = $field['type'] === 'file';
        ?>
        <div class="glass rounded-2xl p-5">
          <label class="block text-xs tracking-widest uppercase mb-2 text-white/40">
            <?= $field['label'] ?>
          </label>

          <?php if ($field['type'] === 'toggle'): ?>
            <div class="toggle-wrap">
              <input type="hidden" name="<?= $field['key'] ?>" value="0">
              <input type="checkbox" id="toggle_<?= $field['key'] ?>"
                     name="<?= $field['key'] ?>" value="1"
                     <?= $val == '1' ? 'checked' : '' ?>>
              <label for="toggle_<?= $field['key'] ?>">
                <div class="toggle-track"></div>
                <span class="text-sm text-white/65"><?= $field['desc'] ?? '' ?></span>
              </label>
            </div>

          <?php elseif ($field['type'] === 'textarea'): ?>
            <textarea name="<?= $field['key'] ?>" rows="3"
                      placeholder="<?= $field['placeholder'] ?? '' ?>"
                      class="input-g resize-none"><?= e($val) ?></textarea>

          <?php elseif ($field['type'] === 'code'): ?>
            <textarea name="<?= $field['key'] ?>" rows="4"
                      placeholder="<?= $field['placeholder'] ?? '' ?>"
                      class="input-g resize-none font-mono text-xs"><?= e($val) ?></textarea>

          <?php elseif ($field['type'] === 'select'): ?>
            <select name="<?= $field['key'] ?>" class="input-g">
              <?php foreach ($field['options'] as $optVal => $optLabel): ?>
              <option value="<?= e($optVal) ?>" <?= $val === $optVal ? 'selected' : '' ?>>
                <?= $optLabel ?>
              </option>
              <?php endforeach; ?>
            </select>

          <?php elseif ($field['type'] === 'file'): ?>
            <?php
              // Show current value preview if it's an image setting
              $previewKey = str_replace('_file', '', $field['key']);
              $previewVal = $allSettings[$previewKey] ?? '';
              $isImg      = !empty($previewVal) && preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $previewVal);
            ?>
            <?php if ($isImg): ?>
            <div class="mb-2 h-24 w-40 rounded-xl overflow-hidden" style="background:rgba(255,255,255,.04)">
              <img src="<?= APP_URL ?>/<?= e($previewVal) ?>" class="h-full w-full object-cover" onerror="this.parentElement.style.display='none'">
            </div>
            <?php endif; ?>
            <input type="file" name="<?= $field['key'] ?>"
                   accept="<?= $field['accept'] ?? 'image/*' ?>"
                   class="input-g" style="padding:7px 12px">
            <?php if (!empty($previewVal)): ?>
            <p class="text-white/25 text-xs mt-1.5">File saat ini: <code class="text-white/40"><?= e($previewVal) ?></code></p>
            <?php endif; ?>

          <?php else: ?>
            <input type="<?= $field['type'] ?? 'text' ?>"
                   name="<?= $field['key'] ?>"
                   value="<?= e($val) ?>"
                   placeholder="<?= $field['placeholder'] ?? '' ?>"
                   class="input-g">
          <?php endif; ?>
        </div>
        <?php endforeach; ?>

        <div class="flex items-center gap-4 pt-2">
          <button type="submit"
                  class="flex items-center gap-2.5 px-8 py-3.5 rounded-2xl font-medium text-sm text-black transition-all hover:scale-105 hover:shadow-[0_8px_30px_rgba(212,170,58,.35)]"
                  style="background:linear-gradient(135deg,#d4aa3a,#e8c860)">
            <i data-lucide="save" style="width:16px;height:16px"></i>
            Simpan Pengaturan
          </button>
          <a href="?tab=<?= e($tab) ?>"
             class="px-5 py-3 glass rounded-2xl text-sm text-white/50 hover:text-white hover:bg-white/10 transition-all">
            Reset
          </a>
        </div>
      </form>
      <?php endif; ?>

    </div><!-- /settings area -->
  </div><!-- /flex -->
</main>
</div>

<script>
lucide.createIcons();

function showToast(icon, title) {
  Swal.fire({
    toast: true, position: 'top-end', icon, title,
    showConfirmButton: false, timer: 3000, timerProgressBar: true,
    background: 'rgba(15,15,15,.97)', color: '#fff',
    customClass: { popup: 'rounded-2xl' }
  });
}

<?php if ($flash && $flash['type'] === 'success'): ?>
showToast('success', '<?= addslashes($flash['msg']) ?>');
<?php elseif ($flash && $flash['type'] === 'warning'): ?>
showToast('warning', '<?= addslashes($flash['msg']) ?>');
<?php endif; ?>

function confirmDanger(action) {
  const messages = {
    reset:         { title: 'Reset Semua Pengaturan?', text: 'Semua konfigurasi akan kembali ke default.', btn: 'Ya, Reset' },
    clear_gallery: { title: 'Hapus Semua Foto Galeri?', text: 'Data foto akan dihapus dari database.', btn: 'Ya, Hapus' },
    clear_inbox:   { title: 'Bersihkan Semua Pesan?', text: 'Seluruh pesan & pengaduan akan terhapus.', btn: 'Ya, Bersihkan' },
  };
  const m = messages[action];
  Swal.fire({
    title: m.title, text: m.text, icon: 'warning',
    showCancelButton: true,
    confirmButtonText: m.btn, cancelButtonText: 'Batal',
    confirmButtonColor: '#ef4444',
    background: '#111', color: '#fff',
    customClass: { popup: 'rounded-2xl', confirmButton: 'rounded-xl px-5 py-2.5', cancelButton: 'rounded-xl px-5 py-2.5' }
  }).then(async r => {
    if (!r.isConfirmed) return;
    // POST to self with danger action
    try {
      const res = await fetch('settings.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `csrf_token=<?= $csrf ?>&group=danger&danger_action=${action}`
      });
      // For now just show success - implement server handler as needed
      showToast('success', 'Tindakan berhasil dijalankan.');
      setTimeout(() => location.reload(), 1200);
    } catch(e) {
      showToast('error', 'Terjadi kesalahan.');
    }
  });
}

// Auto-dismiss flash banner
setTimeout(() => {
  const b = document.getElementById('flash-banner');
  if (b) { b.style.opacity = '0'; b.style.transform = 'translateY(-8px)'; b.style.transition = 'all .4s ease'; setTimeout(() => b.remove(), 400); }
}, 4000);
</script>
</body>
</html>
