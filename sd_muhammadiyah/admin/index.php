<?php
require_once __DIR__ . '/includes/auth.php';

// Fetch dashboard stats
try {
    $totalStudents = db()->query("SELECT SUM(count) FROM student_stats WHERE academic_year = (SELECT MAX(academic_year) FROM student_stats)")->fetchColumn() ?: setting('stats_students','380');
    $totalTeachers = db()->query("SELECT COUNT(*) FROM teachers WHERE is_active=1 AND type='guru'")->fetchColumn() ?: 0;
    $totalAnn      = db()->query("SELECT COUNT(*) FROM announcements WHERE is_published=1")->fetchColumn() ?: 0;
    $totalComplaints = db()->query("SELECT COUNT(*) FROM complaints WHERE status='masuk'")->fetchColumn() ?: 0;
    $totalMessages   = db()->query("SELECT COUNT(*) FROM contact_messages WHERE is_read=0")->fetchColumn() ?: 0;
    $recentAnn       = db()->query("SELECT * FROM announcements ORDER BY created_at DESC LIMIT 5")->fetchAll();
    $recentComplaints= db()->query("SELECT * FROM complaints ORDER BY created_at DESC LIMIT 5")->fetchAll();
} catch (Exception $e) {
    $totalStudents=$totalTeachers=$totalAnn=$totalComplaints=$totalMessages=0;
    $recentAnn=$recentComplaints=[];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard — Admin CMS SD Muhammadiyah 1</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config={theme:{extend:{fontFamily:{display:['"Cormorant Garamond"','serif'],body:['"DM Sans"','sans-serif']}}}}</script>
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
  <style>
    body { font-family:'DM Sans',sans-serif; background:#0a0a0a; color:#fff; }
    .glass { background:rgba(255,255,255,0.06); backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); border:1px solid rgba(255,255,255,0.1); }
    .sidebar-link { display:flex; align-items:center; gap:12px; padding:10px 16px; border-radius:12px; font-size:.82rem; color:rgba(255,255,255,.55); transition:all .2s; text-decoration:none; }
    .sidebar-link:hover, .sidebar-link.active { background:rgba(255,255,255,.08); color:#fff; }
    .sidebar-link.active { border-left:2px solid #d4aa3a; padding-left:14px; color:#f0d898; }
    .sidebar-link i { width:16px; height:16px; opacity:.7; }
    .sidebar-link.active i { opacity:1; color:#d4aa3a; }
    ::-webkit-scrollbar{width:4px} ::-webkit-scrollbar-track{background:#111} ::-webkit-scrollbar-thumb{background:#333;border-radius:4px}
  </style>
</head>
<body>
<div class="flex h-screen overflow-hidden">

  <!-- ── Sidebar ─────────────────────────────────────────── -->
  <aside class="w-64 flex-shrink-0 flex flex-col border-r border-white/[0.07] overflow-y-auto" style="background:rgba(0,0,0,0.8)">
    <!-- Brand -->
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

    <!-- Nav -->
    <nav class="flex-1 px-3 py-5 space-y-0.5">
      <a href="index.php" class="sidebar-link active"><i data-lucide="layout-dashboard"></i> Dashboard</a>

      <div class="px-4 pt-4 pb-1" style="font-size:.65rem;letter-spacing:.2em;text-transform:uppercase;color:rgba(255,255,255,.25)">Konten</div>
      <a href="pages/profile.php" class="sidebar-link"><i data-lucide="building-2"></i> Profil Sekolah</a>
      <a href="pages/teachers.php" class="sidebar-link"><i data-lucide="users"></i> Guru & Staff</a>
      <a href="pages/students.php" class="sidebar-link"><i data-lucide="graduation-cap"></i> Data Siswa</a>
      <a href="pages/gallery.php" class="sidebar-link"><i data-lucide="image"></i> Galeri</a>
      <a href="pages/facilities.php" class="sidebar-link"><i data-lucide="layout-grid"></i> Fasilitas</a>
      <a href="pages/ekskul.php" class="sidebar-link"><i data-lucide="sparkles"></i> Ekstrakurikuler</a>
      <a href="pages/announcements.php" class="sidebar-link"><i data-lucide="bell"></i> Pengumuman</a>

      <div class="px-4 pt-4 pb-1" style="font-size:.65rem;letter-spacing:.2em;text-transform:uppercase;color:rgba(255,255,255,.25)">Interaksi</div>
      <a href="pages/complaints.php" class="sidebar-link">
        <i data-lucide="message-square-warning"></i> Pengaduan
        <?php if ($totalComplaints > 0): ?>
        <span class="ml-auto text-[10px] px-2 py-0.5 rounded-full" style="background:rgba(239,68,68,.2);color:#fca5a5"><?= $totalComplaints ?></span>
        <?php endif; ?>
      </a>
      <a href="pages/messages.php" class="sidebar-link">
        <i data-lucide="mail"></i> Pesan Masuk
        <?php if ($totalMessages > 0): ?>
        <span class="ml-auto text-[10px] px-2 py-0.5 rounded-full" style="background:rgba(59,130,246,.2);color:#93c5fd"><?= $totalMessages ?></span>
        <?php endif; ?>
      </a>

      <div class="px-4 pt-4 pb-1" style="font-size:.65rem;letter-spacing:.2em;text-transform:uppercase;color:rgba(255,255,255,.25)">Sistem</div>
      <a href="pages/settings.php" class="sidebar-link"><i data-lucide="settings"></i> Pengaturan</a>
      <?php if (is_superadmin()): ?>
      <a href="pages/users.php" class="sidebar-link"><i data-lucide="shield"></i> Manajemen User</a>
      <?php endif; ?>
    </nav>

    <!-- User info -->
    <div class="px-4 py-4 border-t border-white/[0.07]">
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl glass flex items-center justify-center text-sm font-medium text-white/70">
          <?= strtoupper(substr($_SESSION['admin_name'], 0, 1)) ?>
        </div>
        <div class="flex-1 min-w-0">
          <div class="text-xs text-white/80 truncate"><?= e($_SESSION['admin_name']) ?></div>
          <div style="font-size:.65rem;color:rgba(255,255,255,.3);text-transform:capitalize"><?= e($_SESSION['admin_role']) ?></div>
        </div>
        <a href="logout.php" title="Logout" class="w-8 h-8 rounded-lg glass flex items-center justify-center hover:bg-red-500/20 transition-all">
          <i data-lucide="log-out" class="text-white/40 hover:text-red-300" style="width:14px;height:14px"></i>
        </a>
      </div>
    </div>
  </aside>

  <!-- ── Main Content ──────────────────────────────────────── -->
  <main class="flex-1 overflow-y-auto">
    <!-- Top bar -->
    <div class="sticky top-0 z-10 px-8 py-4 border-b border-white/[0.07]" style="background:rgba(10,10,10,0.9);backdrop-filter:blur(16px)">
      <div class="flex items-center justify-between">
        <div>
          <h1 style="font-family:'Cormorant Garamond',serif;font-size:1.5rem;font-weight:300;color:#fff">Dashboard</h1>
          <p style="font-size:.75rem;color:rgba(255,255,255,.35)">Selamat datang, <?= e($_SESSION['admin_name']) ?> · <?= date('l, d F Y') ?></p>
        </div>
        <a href="../index.php" target="_blank" class="flex items-center gap-2 px-4 py-2 glass rounded-xl text-xs text-white/50 hover:text-white hover:bg-white/10 transition-all">
          <i data-lucide="external-link" style="width:14px;height:14px"></i> Lihat Situs
        </a>
      </div>
    </div>

    <div class="p-8">
      <!-- Stat Cards -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <?php
        $cards = [
          ['Siswa Aktif',      $totalStudents,  'users',                  '#d4aa3a','rgba(212,170,58,.12)'],
          ['Guru & Staff',     $totalTeachers,  'graduation-cap',         '#60a5fa','rgba(96,165,250,.12)'],
          ['Pengumuman',       $totalAnn,       'bell',                   '#34d399','rgba(52,211,153,.12)'],
          ['Pengaduan Masuk',  $totalComplaints,'message-square-warning', '#f87171','rgba(248,113,113,.12)'],
        ];
        foreach ($cards as $c): ?>
        <div class="glass rounded-2xl p-5">
          <div class="flex items-center justify-between mb-4">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:<?= $c[4] ?>">
              <i data-lucide="<?= $c[2] ?>" style="width:18px;height:18px;color:<?= $c[3] ?>"></i>
            </div>
          </div>
          <div style="font-family:'Cormorant Garamond',serif;font-size:2rem;font-weight:300;color:#fff;line-height:1"><?= $c[1] ?: '—' ?></div>
          <div style="font-size:.75rem;color:rgba(255,255,255,.4);margin-top:4px"><?= $c[0] ?></div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Recent content grid -->
      <div class="grid lg:grid-cols-2 gap-6">
        <!-- Recent Announcements -->
        <div class="glass rounded-2xl overflow-hidden">
          <div class="flex items-center justify-between px-6 py-4 border-b border-white/[0.07]">
            <h2 class="text-sm font-medium text-white/80">Pengumuman Terbaru</h2>
            <a href="pages/announcements.php" class="text-xs" style="color:#d4aa3a">Kelola →</a>
          </div>
          <div class="divide-y divide-white/[0.06]">
            <?php if (empty($recentAnn)): ?>
            <div class="px-6 py-8 text-center text-white/25 text-sm">Belum ada pengumuman.</div>
            <?php else: foreach ($recentAnn as $ann): ?>
            <div class="flex items-center gap-4 px-6 py-4 hover:bg-white/[0.03] transition-colors">
              <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background:rgba(212,170,58,.1)">
                <i data-lucide="file-text" style="width:14px;height:14px;color:#d4aa3a"></i>
              </div>
              <div class="flex-1 min-w-0">
                <div class="text-sm text-white/75 truncate"><?= e($ann['title']) ?></div>
                <div class="text-xs mt-0.5" style="color:rgba(255,255,255,.3)"><?= date('d M Y', strtotime($ann['created_at'])) ?></div>
              </div>
              <span class="text-[10px] px-2 py-0.5 rounded-full <?= $ann['is_published'] ? 'bg-green-500/15 text-green-400' : 'bg-white/5 text-white/30' ?>">
                <?= $ann['is_published'] ? 'Publik' : 'Draft' ?>
              </span>
            </div>
            <?php endforeach; endif; ?>
          </div>
        </div>

        <!-- Recent Complaints -->
        <div class="glass rounded-2xl overflow-hidden">
          <div class="flex items-center justify-between px-6 py-4 border-b border-white/[0.07]">
            <h2 class="text-sm font-medium text-white/80">Pengaduan Terbaru</h2>
            <a href="pages/complaints.php" class="text-xs" style="color:#d4aa3a">Kelola →</a>
          </div>
          <div class="divide-y divide-white/[0.06]">
            <?php if (empty($recentComplaints)): ?>
            <div class="px-6 py-8 text-center text-white/25 text-sm">Belum ada pengaduan.</div>
            <?php else: foreach ($recentComplaints as $c): 
              $statusColor = match($c['status']) {
                'masuk'    => 'bg-red-500/15 text-red-400',
                'diproses' => 'bg-yellow-500/15 text-yellow-400',
                'selesai'  => 'bg-green-500/15 text-green-400',
                default    => 'bg-white/5 text-white/30',
              };
            ?>
            <div class="flex items-center gap-4 px-6 py-4 hover:bg-white/[0.03] transition-colors">
              <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background:rgba(248,113,113,.1)">
                <i data-lucide="alert-triangle" style="width:14px;height:14px;color:#f87171"></i>
              </div>
              <div class="flex-1 min-w-0">
                <div class="text-sm text-white/75 truncate"><?= e($c['subject']) ?></div>
                <div class="text-xs mt-0.5" style="color:rgba(255,255,255,.3)"><?= e($c['name']) ?> · <?= date('d M', strtotime($c['created_at'])) ?></div>
              </div>
              <span class="text-[10px] px-2 py-0.5 rounded-full <?= $statusColor ?>">
                <?= ucfirst($c['status']) ?>
              </span>
            </div>
            <?php endforeach; endif; ?>
          </div>
        </div>
      </div>

    </div><!-- /p-8 -->
  </main>
</div>

<script>
  lucide.createIcons();
  <?php 
  // Show any flash messages
  if (!empty($_SESSION['flash_success'])):
    $msg = addslashes($_SESSION['flash_success']);
    unset($_SESSION['flash_success']);
  ?>
  Swal.fire({ toast:true, position:'top-end', icon:'success', title:'<?= $msg ?>', showConfirmButton:false, timer:3000, timerProgressBar:true, background:'rgba(20,20,20,.95)', color:'#fff' });
  <?php endif; ?>
</script>
</body>
</html>
