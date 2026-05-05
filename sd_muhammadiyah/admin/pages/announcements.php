<?php
// admin/pages/announcements.php — CRUD Pengumuman
require_once __DIR__ . '/../includes/auth.php';

// ── AJAX / POST handler ────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    if (!verify_csrf()) { echo json_encode(['ok'=>false,'msg'=>'Token tidak valid.']); exit; }

    $action = $_POST['action'] ?? '';

    // ── CREATE / UPDATE ──────────────────────────────────────────────────
    if (in_array($action, ['create','update'])) {
        $title     = trim($_POST['title']     ?? '');
        $content   = trim($_POST['content']   ?? '');
        $category  = $_POST['category']  ?? 'umum';
        $isPinned  = isset($_POST['is_pinned'])  ? 1 : 0;
        $isPub     = isset($_POST['is_published']) ? 1 : 0;
        $pubDate   = $_POST['published_at'] ?? date('Y-m-d H:i:s');
        $authorId  = $_SESSION['admin_id'];

        if (!$title || !$content) {
            echo json_encode(['ok'=>false,'msg'=>'Judul dan konten wajib diisi.']); exit;
        }

        $baseSlug = slug($title);
        $slugFinal = $baseSlug;

        // Handle thumbnail upload
        $thumbnail = $_POST['existing_thumbnail'] ?? null;
        if (!empty($_FILES['thumbnail']['name'])) {
            $uploaded = uploadFile($_FILES['thumbnail'], 'announcements');
            if ($uploaded) $thumbnail = $uploaded;
            else { echo json_encode(['ok'=>false,'msg'=>'Gagal upload gambar (max 5MB, jpg/png/webp).']); exit; }
        }

        try {
            if ($action === 'create') {
                // Unique slug
                $i = 0;
                while (db()->prepare("SELECT id FROM announcements WHERE slug=?")->execute([$slugFinal]) 
                       && db()->query("SELECT id FROM announcements WHERE slug='$slugFinal'")->fetch()) {
                    $slugFinal = $baseSlug . '-' . (++$i);
                }
                $stmt = db()->prepare(
                    "INSERT INTO announcements (title,slug,content,category,thumbnail,author_id,is_pinned,is_published,published_at)
                     VALUES (?,?,?,?,?,?,?,?,?)"
                );
                $stmt->execute([$title,$slugFinal,$content,$category,$thumbnail,$authorId,$isPinned,$isPub,$pubDate]);
                $newId = db()->lastInsertId();
                echo json_encode(['ok'=>true,'msg'=>'Pengumuman berhasil ditambahkan.','id'=>$newId]); exit;
            } else {
                $id = (int)($_POST['id'] ?? 0);
                if (!$id) { echo json_encode(['ok'=>false,'msg'=>'ID tidak valid.']); exit; }

                // Regenerate slug if title changed
                $old = db()->prepare("SELECT slug,title FROM announcements WHERE id=?")->execute([$id])
                       ? db()->prepare("SELECT slug,title FROM announcements WHERE id=?") : null;
                $oldStmt = db()->prepare("SELECT slug,title FROM announcements WHERE id=?");
                $oldStmt->execute([$id]);
                $oldRow = $oldStmt->fetch();
                if ($oldRow && $oldRow['title'] !== $title) {
                    $i = 0;
                    while (true) {
                        $check = db()->prepare("SELECT id FROM announcements WHERE slug=? AND id!=?");
                        $check->execute([$slugFinal,$id]);
                        if (!$check->fetch()) break;
                        $slugFinal = $baseSlug . '-' . (++$i);
                    }
                } else {
                    $slugFinal = $oldRow['slug'] ?? $baseSlug;
                }

                $stmt = db()->prepare(
                    "UPDATE announcements SET title=?,slug=?,content=?,category=?,thumbnail=?,
                     is_pinned=?,is_published=?,published_at=?,updated_at=NOW()
                     WHERE id=?"
                );
                $stmt->execute([$title,$slugFinal,$content,$category,$thumbnail,$isPinned,$isPub,$pubDate,$id]);
                echo json_encode(['ok'=>true,'msg'=>'Pengumuman berhasil diperbarui.']); exit;
            }
        } catch (Exception $e) {
            echo json_encode(['ok'=>false,'msg'=>'DB Error: '.$e->getMessage()]); exit;
        }
    }

    // ── DELETE ───────────────────────────────────────────────────────────
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) { echo json_encode(['ok'=>false,'msg'=>'ID tidak valid.']); exit; }
        try {
            db()->prepare("DELETE FROM announcements WHERE id=?")->execute([$id]);
            echo json_encode(['ok'=>true,'msg'=>'Pengumuman berhasil dihapus.']); exit;
        } catch (Exception $e) {
            echo json_encode(['ok'=>false,'msg'=>'Gagal menghapus.']); exit;
        }
    }

    // ── TOGGLE PUBLISH ───────────────────────────────────────────────────
    if ($action === 'toggle_publish') {
        $id = (int)($_POST['id'] ?? 0);
        try {
            db()->prepare("UPDATE announcements SET is_published = NOT is_published WHERE id=?")->execute([$id]);
            $row = db()->prepare("SELECT is_published FROM announcements WHERE id=?");
            $row->execute([$id]); $r = $row->fetch();
            echo json_encode(['ok'=>true,'status'=>(int)$r['is_published']]); exit;
        } catch (Exception $e) {
            echo json_encode(['ok'=>false,'msg'=>'Gagal.']); exit;
        }
    }

    // ── GET SINGLE (for edit modal) ──────────────────────────────────────
    if ($action === 'get') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = db()->prepare("SELECT * FROM announcements WHERE id=?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        echo json_encode($row ?: ['ok'=>false]); exit;
    }

    echo json_encode(['ok'=>false,'msg'=>'Action tidak dikenal.']); exit;
}

// ── PAGINATION FETCH ───────────────────────────────────────────────────────
$page     = max(1, (int)($_GET['p'] ?? 1));
$perPage  = 12;
$search   = trim($_GET['q'] ?? '');
$catFilter= $_GET['cat'] ?? '';
$offset   = ($page - 1) * $perPage;

$where  = '1=1';
$params = [];
if ($search)    { $where .= ' AND (title LIKE ? OR content LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($catFilter) { $where .= ' AND category = ?'; $params[] = $catFilter; }

try {
    $countStmt = db()->prepare("SELECT COUNT(*) FROM announcements WHERE $where");
    $countStmt->execute($params);
    $total     = (int)$countStmt->fetchColumn();
    $totalPages= (int)ceil($total / $perPage);

    $listStmt  = db()->prepare(
        "SELECT a.*, u.full_name as author_name 
         FROM announcements a
         LEFT JOIN admin_users u ON a.author_id = u.id
         WHERE $where ORDER BY a.is_pinned DESC, a.published_at DESC 
         LIMIT $perPage OFFSET $offset"
    );
    $listStmt->execute($params);
    $announcements = $listStmt->fetchAll();
} catch (Exception $e) {
    $announcements = []; $total = 0; $totalPages = 1;
}

$csrfToken = csrf_token();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Pengumuman — Admin CMS</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config={theme:{extend:{fontFamily:{display:['"Cormorant Garamond"','serif'],body:['"DM Sans"','sans-serif']}}}}</script>
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
  <!-- Quill rich text editor -->
  <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
  <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
  <style>
    body{font-family:'DM Sans',sans-serif;background:#0a0a0a;color:#fff}
    .glass{background:rgba(255,255,255,.06);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,.1)}
    .sidebar-link{display:flex;align-items:center;gap:12px;padding:10px 16px;border-radius:12px;font-size:.82rem;color:rgba(255,255,255,.55);transition:all .2s;text-decoration:none}
    .sidebar-link:hover,.sidebar-link.active{background:rgba(255,255,255,.08);color:#fff}
    .sidebar-link.active{border-left:2px solid #d4aa3a;padding-left:14px;color:#f0d898}
    .input-g{background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);color:#fff;border-radius:10px;padding:10px 14px;font-size:.85rem;width:100%;transition:all .25s;font-family:'DM Sans',sans-serif}
    .input-g:focus{outline:none;background:rgba(255,255,255,.1);border-color:rgba(212,170,58,.6);box-shadow:0 0 0 3px rgba(212,170,58,.1)}
    .input-g::placeholder{color:rgba(255,255,255,.3)}
    .input-g option{background:#1a1a1a;color:#fff}
    /* Modal */
    #modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:100;backdrop-filter:blur(8px)}
    #modal-overlay.open{display:flex;align-items:flex-start;justify-content:center;padding:40px 16px;overflow-y:auto}
    #modal-box{background:#111;border:1px solid rgba(255,255,255,.1);border-radius:24px;width:100%;max-width:760px;position:relative;animation:modalIn .3s cubic-bezier(.16,1,.3,1)}
    @keyframes modalIn{from{opacity:0;transform:translateY(-20px) scale(.97)}to{opacity:1;transform:none}}
    /* Quill overrides */
    .ql-toolbar.ql-snow{border:1px solid rgba(255,255,255,.12) !important;border-radius:10px 10px 0 0;background:rgba(255,255,255,.05)}
    .ql-container.ql-snow{border:1px solid rgba(255,255,255,.12) !important;border-top:none !important;border-radius:0 0 10px 10px;background:rgba(255,255,255,.04);min-height:200px}
    .ql-editor{color:#fff;font-size:.9rem;min-height:200px}
    .ql-snow .ql-stroke{stroke:rgba(255,255,255,.6) !important}
    .ql-snow .ql-fill{fill:rgba(255,255,255,.6) !important}
    .ql-snow .ql-picker-label{color:rgba(255,255,255,.6) !important}
    /* Badge */
    .badge-new{background:rgba(212,170,58,.18);color:#f0d898;border:1px solid rgba(212,170,58,.35);animation:pulse 2.5s infinite}
    @keyframes pulse{0%,100%{box-shadow:0 0 0 0 rgba(212,170,58,.4)}50%{box-shadow:0 0 0 5px rgba(212,170,58,0)}}
    ::-webkit-scrollbar{width:4px} ::-webkit-scrollbar-track{background:#111} ::-webkit-scrollbar-thumb{background:#333;border-radius:4px}
  </style>
</head>
<body>
<div class="flex h-screen overflow-hidden">

<!-- ── Sidebar (reuse exact same structure) ───────────────────────────── -->
<aside class="w-64 flex-shrink-0 flex flex-col border-r border-white/[0.07] overflow-y-auto" style="background:rgba(0,0,0,0.8)">
  <div class="px-5 py-5 border-b border-white/[0.07]">
    <div class="flex items-center gap-3">
      <div class="w-9 h-9 rounded-xl glass flex items-center justify-center" style="border-color:rgba(212,170,58,.3)">
        <span style="color:#d4aa3a;font-family:'Cormorant Garamond',serif;font-size:1.2rem">ص</span>
      </div>
      <div>
        <div class="text-white text-xs font-medium">SD Muhammadiyah 1</div>
        <div style="color:#d4aa3a;font-size:.65rem;letter-spacing:.15em;text-transform:uppercase">Admin CMS</div>
      </div>
    </div>
  </div>
  <nav class="flex-1 px-3 py-5 space-y-0.5">
    <a href="../index.php"          class="sidebar-link"><i data-lucide="layout-dashboard"></i> Dashboard</a>
    <div class="px-4 pt-4 pb-1" style="font-size:.65rem;letter-spacing:.2em;text-transform:uppercase;color:rgba(255,255,255,.25)">Konten</div>
    <a href="profile.php"           class="sidebar-link"><i data-lucide="building-2"></i> Profil Sekolah</a>
    <a href="teachers.php"          class="sidebar-link"><i data-lucide="users"></i> Guru &amp; Staff</a>
    <a href="students.php"          class="sidebar-link"><i data-lucide="graduation-cap"></i> Data Siswa</a>
    <a href="gallery.php"           class="sidebar-link"><i data-lucide="image"></i> Galeri</a>
    <a href="facilities.php"        class="sidebar-link"><i data-lucide="layout-grid"></i> Fasilitas</a>
    <a href="ekskul.php"            class="sidebar-link"><i data-lucide="sparkles"></i> Ekstrakurikuler</a>
    <a href="announcements.php"     class="sidebar-link active"><i data-lucide="bell"></i> Pengumuman</a>
    <div class="px-4 pt-4 pb-1" style="font-size:.65rem;letter-spacing:.2em;text-transform:uppercase;color:rgba(255,255,255,.25)">Interaksi</div>
    <a href="complaints.php"        class="sidebar-link"><i data-lucide="message-square-warning"></i> Pengaduan</a>
    <a href="messages.php"          class="sidebar-link"><i data-lucide="mail"></i> Pesan Masuk</a>
    <div class="px-4 pt-4 pb-1" style="font-size:.65rem;letter-spacing:.2em;text-transform:uppercase;color:rgba(255,255,255,.25)">Sistem</div>
    <a href="settings.php"          class="sidebar-link"><i data-lucide="settings"></i> Pengaturan</a>
  </nav>
  <div class="px-4 py-4 border-t border-white/[0.07]">
    <div class="flex items-center gap-3">
      <div class="w-9 h-9 rounded-xl glass flex items-center justify-center text-sm font-medium text-white/70">
        <?= strtoupper(substr($_SESSION['admin_name'],0,1)) ?>
      </div>
      <div class="flex-1 min-w-0">
        <div class="text-xs text-white/80 truncate"><?= e($_SESSION['admin_name']) ?></div>
        <div style="font-size:.65rem;color:rgba(255,255,255,.3);text-transform:capitalize"><?= e($_SESSION['admin_role']) ?></div>
      </div>
      <a href="../logout.php" class="w-8 h-8 rounded-lg glass flex items-center justify-center hover:bg-red-500/20 transition-all">
        <i data-lucide="log-out" style="width:14px;height:14px;color:rgba(255,255,255,.4)"></i>
      </a>
    </div>
  </div>
</aside>

<!-- ── Main ─────────────────────────────────────────────────────────────── -->
<main class="flex-1 flex flex-col overflow-hidden">

  <!-- Topbar -->
  <div class="px-8 py-4 border-b border-white/[0.07] flex items-center justify-between flex-shrink-0"
       style="background:rgba(10,10,10,.9);backdrop-filter:blur(16px)">
    <div>
      <h1 style="font-family:'Cormorant Garamond',serif;font-size:1.5rem;font-weight:300">Kelola Pengumuman</h1>
      <p style="font-size:.75rem;color:rgba(255,255,255,.35)"><?= $total ?> total pengumuman</p>
    </div>
    <button onclick="openModal()" 
            class="flex items-center gap-2 px-5 py-2.5 rounded-xl font-medium text-sm text-black transition-all hover:scale-105"
            style="background:linear-gradient(135deg,#d4aa3a,#e8c860)">
      <i data-lucide="plus" style="width:16px;height:16px"></i> Tambah Pengumuman
    </button>
  </div>

  <!-- Toolbar -->
  <div class="px-8 py-4 border-b border-white/[0.06] flex flex-wrap items-center gap-3 flex-shrink-0" style="background:rgba(10,10,10,.5)">
    <form method="GET" class="flex items-center gap-3 flex-wrap flex-1">
      <div class="relative">
        <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" style="width:14px;height:14px;color:rgba(255,255,255,.3)"></i>
        <input type="text" name="q" value="<?= e($search) ?>" placeholder="Cari pengumuman…"
               class="input-g pl-9" style="width:220px;padding:8px 12px 8px 34px">
      </div>
      <select name="cat" class="input-g" style="width:140px">
        <option value="">Semua Kategori</option>
        <?php foreach(['umum','akademik','kegiatan','penting'] as $c): ?>
        <option value="<?= $c ?>" <?= $catFilter===$c?'selected':'' ?>><?= ucfirst($c) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="px-4 py-2 glass rounded-xl text-xs text-white/70 hover:text-white hover:bg-white/10 transition-all">Filter</button>
      <?php if ($search || $catFilter): ?>
      <a href="announcements.php" class="px-3 py-2 text-xs text-white/40 hover:text-white/70 transition-colors flex items-center gap-1">
        <i data-lucide="x" style="width:12px;height:12px"></i> Reset
      </a>
      <?php endif; ?>
    </form>
  </div>

  <!-- Table area -->
  <div class="flex-1 overflow-y-auto p-8">

    <?php if (empty($announcements)): ?>
    <div class="glass rounded-3xl p-16 text-center">
      <i data-lucide="bell-off" style="width:40px;height:40px;color:rgba(255,255,255,.2);margin:0 auto 12px"></i>
      <p style="color:rgba(255,255,255,.3);font-size:.9rem">Belum ada pengumuman<?= $search ? " untuk pencarian \"$search\"" : '' ?>.</p>
      <button onclick="openModal()" class="mt-4 px-5 py-2 rounded-xl text-sm text-black font-medium" style="background:#d4aa3a">+ Tambah Pertama</button>
    </div>

    <?php else: ?>
    <div class="glass rounded-2xl overflow-hidden">
      <table class="w-full">
        <thead>
          <tr style="border-bottom:1px solid rgba(255,255,255,.08)">
            <th class="px-5 py-3.5 text-left" style="font-size:.68rem;letter-spacing:.15em;text-transform:uppercase;color:rgba(255,255,255,.35);font-weight:400">Judul</th>
            <th class="px-5 py-3.5 text-left hidden md:table-cell" style="font-size:.68rem;letter-spacing:.15em;text-transform:uppercase;color:rgba(255,255,255,.35);font-weight:400">Kategori</th>
            <th class="px-5 py-3.5 text-left hidden lg:table-cell" style="font-size:.68rem;letter-spacing:.15em;text-transform:uppercase;color:rgba(255,255,255,.35);font-weight:400">Tanggal</th>
            <th class="px-5 py-3.5 text-center" style="font-size:.68rem;letter-spacing:.15em;text-transform:uppercase;color:rgba(255,255,255,.35);font-weight:400">Status</th>
            <th class="px-5 py-3.5 text-right" style="font-size:.68rem;letter-spacing:.15em;text-transform:uppercase;color:rgba(255,255,255,.35);font-weight:400">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-white/[0.05]">
          <?php foreach ($announcements as $ann):
            $isNew = isNew($ann['published_at']);
            $catColor = match($ann['category']) {
              'penting'  => 'rgba(239,68,68,.15) border-red-500/30 text-red-300',
              'akademik' => 'rgba(59,130,246,.15) border-blue-500/30 text-blue-300',
              'kegiatan' => 'rgba(52,211,153,.15) border-green-500/30 text-green-300',
              default    => 'rgba(255,255,255,.06) border-white/15 text-white/40',
            };
          ?>
          <tr class="group hover:bg-white/[0.03] transition-colors">
            <td class="px-5 py-4">
              <div class="flex items-center gap-3">
                <?php if ($ann['is_pinned']): ?>
                <span title="Disematkan" style="color:#d4aa3a"><i data-lucide="pin" style="width:13px;height:13px"></i></span>
                <?php endif; ?>
                <div>
                  <div class="text-sm text-white/85 line-clamp-1 max-w-xs"><?= e($ann['title']) ?></div>
                  <div class="flex items-center gap-2 mt-1">
                    <?php if ($isNew): ?>
                    <span class="badge-new text-[10px] px-2 py-0.5 rounded-full font-medium">✦ Terbaru</span>
                    <?php endif; ?>
                    <span style="font-size:.7rem;color:rgba(255,255,255,.3)"><?= e($ann['author_name'] ?? 'Admin') ?></span>
                  </div>
                </div>
              </div>
            </td>
            <td class="px-5 py-4 hidden md:table-cell">
              <span class="text-[11px] px-2.5 py-1 rounded-full border" style="background:<?= explode(' ',$catColor)[0] ?>">
                <?= ucfirst(e($ann['category'])) ?>
              </span>
            </td>
            <td class="px-5 py-4 hidden lg:table-cell" style="font-size:.8rem;color:rgba(255,255,255,.4)">
              <?= date('d M Y', strtotime($ann['published_at'])) ?>
            </td>
            <td class="px-5 py-4 text-center">
              <button onclick="togglePublish(<?= $ann['id'] ?>, this)"
                      class="toggle-btn text-[11px] px-3 py-1 rounded-full border transition-all hover:scale-105"
                      data-status="<?= $ann['is_published'] ?>"
                      style="<?= $ann['is_published'] 
                        ? 'background:rgba(52,211,153,.15);border-color:rgba(52,211,153,.35);color:#6ee7b7' 
                        : 'background:rgba(255,255,255,.06);border-color:rgba(255,255,255,.15);color:rgba(255,255,255,.4)' ?>">
                <?= $ann['is_published'] ? 'Publik' : 'Draft' ?>
              </button>
            </td>
            <td class="px-5 py-4">
              <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                <button onclick="editAnn(<?= $ann['id'] ?>)"
                        class="w-8 h-8 glass rounded-lg flex items-center justify-center hover:bg-blue-500/20 hover:border-blue-500/40 transition-all"
                        title="Edit">
                  <i data-lucide="pencil" style="width:13px;height:13px;color:#93c5fd"></i>
                </button>
                <a href="../../pages/aktivitas/pengumuman.php?id=<?= $ann['id'] ?>" target="_blank"
                   class="w-8 h-8 glass rounded-lg flex items-center justify-center hover:bg-white/15 transition-all" title="Lihat">
                  <i data-lucide="external-link" style="width:13px;height:13px;color:rgba(255,255,255,.5)"></i>
                </a>
                <button onclick="deleteAnn(<?= $ann['id'] ?>, '<?= addslashes(e($ann['title'])) ?>')"
                        class="w-8 h-8 glass rounded-lg flex items-center justify-center hover:bg-red-500/20 hover:border-red-500/40 transition-all"
                        title="Hapus">
                  <i data-lucide="trash-2" style="width:13px;height:13px;color:#f87171"></i>
                </button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="flex items-center justify-center gap-2 mt-6">
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
      <a href="?p=<?= $i ?>&q=<?= urlencode($search) ?>&cat=<?= urlencode($catFilter) ?>"
         class="w-9 h-9 rounded-xl flex items-center justify-center text-sm transition-all
                <?= $i === $page ? 'text-black font-medium' : 'glass text-white/50 hover:text-white hover:bg-white/10' ?>"
         style="<?= $i === $page ? 'background:linear-gradient(135deg,#d4aa3a,#e8c860)' : '' ?>">
        <?= $i ?>
      </a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>

  </div><!-- /overflow -->
</main>
</div>

<!-- ══════════════════════════════════════════════════════════
     MODAL: Create / Edit
══════════════════════════════════════════════════════════ -->
<div id="modal-overlay">
  <div id="modal-box">
    <div class="flex items-center justify-between px-7 py-5 border-b border-white/[0.08]">
      <h2 id="modal-title" style="font-family:'Cormorant Garamond',serif;font-size:1.4rem;font-weight:300">Tambah Pengumuman</h2>
      <button onclick="closeModal()" class="w-9 h-9 glass rounded-xl flex items-center justify-center hover:bg-white/15 transition-all">
        <i data-lucide="x" style="width:16px;height:16px"></i>
      </button>
    </div>

    <form id="ann-form" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
      <input type="hidden" name="action" id="form-action" value="create">
      <input type="hidden" name="id" id="form-id" value="">
      <input type="hidden" name="existing_thumbnail" id="existing-thumbnail" value="">

      <div class="px-7 py-6 space-y-5">

        <!-- Title -->
        <div>
          <label class="block text-xs tracking-widest uppercase mb-1.5" style="color:rgba(255,255,255,.4)">Judul *</label>
          <input type="text" name="title" id="f-title" required placeholder="Judul pengumuman…" class="input-g">
        </div>

        <!-- Category + Published At row -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-xs tracking-widest uppercase mb-1.5" style="color:rgba(255,255,255,.4)">Kategori</label>
            <select name="category" id="f-category" class="input-g">
              <option value="umum">Umum</option>
              <option value="akademik">Akademik</option>
              <option value="kegiatan">Kegiatan</option>
              <option value="penting">Penting</option>
            </select>
          </div>
          <div>
            <label class="block text-xs tracking-widest uppercase mb-1.5" style="color:rgba(255,255,255,.4)">Tanggal Publish</label>
            <input type="datetime-local" name="published_at" id="f-pubdate" class="input-g">
          </div>
        </div>

        <!-- Thumbnail -->
        <div>
          <label class="block text-xs tracking-widest uppercase mb-1.5" style="color:rgba(255,255,255,.4)">Thumbnail (opsional)</label>
          <div id="thumb-preview" class="hidden mb-2 relative w-24 h-16 rounded-lg overflow-hidden">
            <img id="thumb-img" src="" alt="" class="w-full h-full object-cover">
            <button type="button" onclick="clearThumb()" class="absolute top-1 right-1 w-5 h-5 bg-black/70 rounded-full flex items-center justify-center">
              <i data-lucide="x" style="width:10px;height:10px;color:#fff"></i>
            </button>
          </div>
          <input type="file" name="thumbnail" id="f-thumbnail" accept="image/*" class="input-g" style="padding:6px 12px" onchange="previewThumb(this)">
        </div>

        <!-- Content (Quill) -->
        <div>
          <label class="block text-xs tracking-widest uppercase mb-1.5" style="color:rgba(255,255,255,.4)">Konten *</label>
          <div id="quill-editor"></div>
          <input type="hidden" name="content" id="f-content">
        </div>

        <!-- Toggles -->
        <div class="flex items-center gap-6">
          <label class="flex items-center gap-2.5 cursor-pointer select-none">
            <input type="checkbox" name="is_published" id="f-published" class="w-4 h-4 accent-yellow-500" checked>
            <span class="text-sm text-white/70">Publikasikan sekarang</span>
          </label>
          <label class="flex items-center gap-2.5 cursor-pointer select-none">
            <input type="checkbox" name="is_pinned" id="f-pinned" class="w-4 h-4 accent-yellow-500">
            <span class="text-sm text-white/70">Sematkan di atas</span>
          </label>
        </div>
      </div>

      <div class="px-7 py-5 border-t border-white/[0.08] flex items-center justify-end gap-3">
        <button type="button" onclick="closeModal()"
                class="px-5 py-2.5 glass rounded-xl text-sm text-white/60 hover:text-white hover:bg-white/10 transition-all">
          Batal
        </button>
        <button type="submit" id="submit-btn"
                class="px-6 py-2.5 rounded-xl text-sm font-medium text-black transition-all hover:scale-105 flex items-center gap-2"
                style="background:linear-gradient(135deg,#d4aa3a,#e8c860)">
          <i data-lucide="save" style="width:14px;height:14px"></i>
          <span id="submit-label">Simpan</span>
        </button>
      </div>
    </form>
  </div>
</div>

<script>
lucide.createIcons();

// ── Quill setup ─────────────────────────────────────────────
const quill = new Quill('#quill-editor', {
  theme: 'snow',
  placeholder: 'Tulis konten pengumuman di sini…',
  modules: { toolbar: [
    [{ header: [2,3,false] }],
    ['bold','italic','underline'],
    [{ list:'ordered' },{ list:'bullet' }],
    ['link'],
    ['clean']
  ]}
});

// ── Modal helpers ────────────────────────────────────────────
function openModal(mode='create') {
  document.getElementById('modal-overlay').classList.add('open');
  document.body.style.overflow = 'hidden';
  if (mode === 'create') {
    document.getElementById('modal-title').textContent = 'Tambah Pengumuman';
    document.getElementById('submit-label').textContent = 'Simpan';
    document.getElementById('ann-form').reset();
    document.getElementById('form-action').value = 'create';
    document.getElementById('form-id').value = '';
    document.getElementById('existing-thumbnail').value = '';
    document.getElementById('thumb-preview').classList.add('hidden');
    quill.setContents([]);
    // Default pub date = now
    const now = new Date(); now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    document.getElementById('f-pubdate').value = now.toISOString().slice(0,16);
    document.getElementById('f-published').checked = true;
    document.getElementById('f-pinned').checked = false;
  }
  lucide.createIcons();
}
function closeModal() {
  document.getElementById('modal-overlay').classList.remove('open');
  document.body.style.overflow = '';
}
document.getElementById('modal-overlay').addEventListener('click', e => {
  if (e.target === document.getElementById('modal-overlay')) closeModal();
});

// ── Thumb preview ────────────────────────────────────────────
function previewThumb(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      document.getElementById('thumb-img').src = e.target.result;
      document.getElementById('thumb-preview').classList.remove('hidden');
    };
    reader.readAsDataURL(input.files[0]);
  }
}
function clearThumb() {
  document.getElementById('f-thumbnail').value = '';
  document.getElementById('existing-thumbnail').value = '';
  document.getElementById('thumb-preview').classList.add('hidden');
}

// ── Load for edit ────────────────────────────────────────────
async function editAnn(id) {
  const res = await fetch('announcements.php', {
    method:'POST',
    body: new URLSearchParams({ action:'get', id, csrf_token:'<?= $csrfToken ?>' })
  });
  const data = await res.json();
  if (!data || !data.id) { showToast('error','Gagal memuat data.'); return; }

  document.getElementById('modal-title').textContent = 'Edit Pengumuman';
  document.getElementById('submit-label').textContent = 'Perbarui';
  document.getElementById('form-action').value = 'update';
  document.getElementById('form-id').value = data.id;
  document.getElementById('f-title').value = data.title;
  document.getElementById('f-category').value = data.category;
  document.getElementById('f-published').checked = !!parseInt(data.is_published);
  document.getElementById('f-pinned').checked = !!parseInt(data.is_pinned);
  document.getElementById('f-pubdate').value = data.published_at?.slice(0,16) || '';

  if (data.thumbnail) {
    document.getElementById('existing-thumbnail').value = data.thumbnail;
    document.getElementById('thumb-img').src = '../../assets/images/uploads/announcements/' + data.thumbnail;
    document.getElementById('thumb-preview').classList.remove('hidden');
  } else {
    document.getElementById('thumb-preview').classList.add('hidden');
  }

  quill.root.innerHTML = data.content || '';
  openModal('edit');
}

// ── Submit ───────────────────────────────────────────────────
document.getElementById('ann-form').addEventListener('submit', async e => {
  e.preventDefault();
  document.getElementById('f-content').value = quill.root.innerHTML;

  const btn = document.getElementById('submit-btn');
  btn.disabled = true;
  btn.innerHTML = '<svg class="animate-spin w-4 h-4 inline mr-1" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="60" stroke-dashoffset="20"/></svg> Menyimpan…';

  const fd = new FormData(e.target);
  try {
    const res = await fetch('announcements.php', { method:'POST', body: fd });
    const data = await res.json();
    if (data.ok) {
      closeModal();
      showToast('success', data.msg);
      setTimeout(()=>location.reload(), 1200);
    } else {
      showToast('error', data.msg || 'Terjadi kesalahan.');
    }
  } catch {
    showToast('error','Koneksi gagal.');
  }
  btn.disabled = false;
  btn.innerHTML = '<i data-lucide="save" style="width:14px;height:14px"></i> <span id="submit-label">Simpan</span>';
  lucide.createIcons();
});

// ── Delete ───────────────────────────────────────────────────
function deleteAnn(id, title) {
  Swal.fire({
    title: 'Hapus Pengumuman?',
    html: `<span style="color:rgba(255,255,255,.6);font-size:.9rem">"${title}"</span><br><span style="font-size:.8rem;color:rgba(255,255,255,.35)">Tindakan ini tidak dapat dibatalkan.</span>`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Ya, Hapus',
    cancelButtonText: 'Batal',
    confirmButtonColor: '#ef4444',
    background: '#111',
    color: '#fff',
    customClass: { popup:'rounded-2xl', confirmButton:'rounded-xl px-5 py-2', cancelButton:'rounded-xl px-5 py-2' }
  }).then(async r => {
    if (!r.isConfirmed) return;
    const res = await fetch('announcements.php', {
      method:'POST',
      body: new URLSearchParams({ action:'delete', id, csrf_token:'<?= $csrfToken ?>' })
    });
    const data = await res.json();
    if (data.ok) { showToast('success',data.msg); setTimeout(()=>location.reload(),1000); }
    else showToast('error', data.msg);
  });
}

// ── Toggle publish ───────────────────────────────────────────
async function togglePublish(id, btn) {
  const res = await fetch('announcements.php', {
    method:'POST',
    body: new URLSearchParams({ action:'toggle_publish', id, csrf_token:'<?= $csrfToken ?>' })
  });
  const data = await res.json();
  if (data.ok) {
    btn.dataset.status = data.status;
    if (data.status == 1) {
      btn.textContent='Publik';
      btn.style.cssText='background:rgba(52,211,153,.15);border:1px solid rgba(52,211,153,.35);color:#6ee7b7;padding:4px 12px;border-radius:9999px;font-size:.69rem;transition:all .2s';
    } else {
      btn.textContent='Draft';
      btn.style.cssText='background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.15);color:rgba(255,255,255,.4);padding:4px 12px;border-radius:9999px;font-size:.69rem;transition:all .2s';
    }
    showToast('success','Status berhasil diperbarui.');
  }
}

// ── Toast helper ─────────────────────────────────────────────
function showToast(icon, title) {
  Swal.fire({ toast:true, position:'top-end', icon, title,
    showConfirmButton:false, timer:3000, timerProgressBar:true,
    background:'rgba(15,15,15,.97)', color:'#fff',
    customClass:{popup:'rounded-2xl'}
  });
}
</script>
</body>
</html>
