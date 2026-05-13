<?php
// admin/includes/sidebar.php — reusable sidebar
// Set $activeSidebar before including, e.g. $activeSidebar = 'announcements';
$activeSidebar = $activeSidebar ?? '';
function sidebarLink(string $href, string $icon, string $label, string $key, string $active, string $badge=''): void {
    $cls = ($active === $key) ? 'sidebar-link active' : 'sidebar-link';
    echo "<a href=\"$href\" class=\"$cls\"><i data-lucide=\"$icon\"></i> $label";
    if ($badge) echo " <span class=\"ml-auto badge-count\">$badge</span>";
    echo "</a>\n";
}
try {
    $pendingComplaints = db()->query("SELECT COUNT(*) FROM complaints WHERE status='masuk'")->fetchColumn();
    $unreadMessages    = db()->query("SELECT COUNT(*) FROM contact_messages WHERE is_read=0")->fetchColumn();
} catch(Exception $e){ $pendingComplaints=0; $unreadMessages=0; }
?>
<style>
.sidebar-link{display:flex;align-items:center;gap:12px;padding:10px 16px;border-radius:12px;font-size:.82rem;color:rgba(255,255,255,.55);transition:all .2s;text-decoration:none}
.sidebar-link:hover,.sidebar-link.active{background:rgba(255,255,255,.08);color:#fff}
.sidebar-link.active{border-left:2px solid #d4aa3a;padding-left:14px;color:#f0d898}
.sidebar-link i{width:16px;height:16px;opacity:.7}.sidebar-link.active i{opacity:1;color:#d4aa3a}
.badge-count{font-size:.65rem;padding:2px 7px;border-radius:99px;background:rgba(239,68,68,.2);color:#fca5a5}
</style>
<aside class="w-64 flex-shrink-0 flex flex-col border-r border-white/[0.07] overflow-y-auto" style="background:rgba(0,0,0,.82)">
  <div class="px-5 py-5 border-b border-white/[0.07]">
    <a href="<?= APP_URL ?>/admin/index.php" class="flex items-center gap-3">
      <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background:rgba(212,170,58,.12);border:1px solid rgba(212,170,58,.3)">
        <span style="color:#d4aa3a;font-family:'Cormorant Garamond',serif;font-size:1.2rem">ص</span>
      </div>
      <div>
        <div class="text-white text-xs font-medium">SD Muhammadiyah 1</div>
        <div style="color:#d4aa3a;font-size:.65rem;letter-spacing:.15em;text-transform:uppercase">Admin CMS</div>
      </div>
    </a>
  </div>
  <nav class="flex-1 px-3 py-5 space-y-0.5">
    <?php sidebarLink(APP_URL.'/admin/index.php','layout-dashboard','Dashboard','dashboard',$activeSidebar); ?>
    <div class="px-4 pt-4 pb-1" style="font-size:.65rem;letter-spacing:.2em;text-transform:uppercase;color:rgba(255,255,255,.25)">Konten</div>
    <?php sidebarLink('profile.php','building-2','Profil Sekolah','profile',$activeSidebar);
          sidebarLink('teachers.php','users','Guru &amp; Staff','teachers',$activeSidebar);
          sidebarLink('students.php','graduation-cap','Data Siswa','students',$activeSidebar);
          sidebarLink('gallery.php','image','Galeri','gallery',$activeSidebar);
          sidebarLink('facilities.php','layout-grid','Fasilitas','facilities',$activeSidebar);
          sidebarLink('ekskul.php','sparkles','Ekstrakurikuler','ekskul',$activeSidebar);
          sidebarLink('announcements.php','bell','Pengumuman','announcements',$activeSidebar); ?>
    <div class="px-4 pt-4 pb-1" style="font-size:.65rem;letter-spacing:.2em;text-transform:uppercase;color:rgba(255,255,255,.25)">Interaksi</div>
    <?php sidebarLink('complaints.php','message-square-warning','Pengaduan','complaints',$activeSidebar, $pendingComplaints>0?(string)$pendingComplaints:'');
          sidebarLink('messages.php','mail','Pesan Masuk','messages',$activeSidebar, $unreadMessages>0?(string)$unreadMessages:''); ?>
    <div class="px-4 pt-4 pb-1" style="font-size:.65rem;letter-spacing:.2em;text-transform:uppercase;color:rgba(255,255,255,.25)">Sistem</div>
    <?php sidebarLink('settings.php','settings','Pengaturan','settings',$activeSidebar);
      if (is_superadmin()) sidebarLink('users.php','shield','Manajemen User','users',$activeSidebar); ?>
  </nav>
  <div class="px-4 py-4 border-t border-white/[0.07]">
    <div class="flex items-center gap-3">
      <div class="w-9 h-9 rounded-xl flex items-center justify-center text-sm font-medium" style="background:rgba(255,255,255,.08);color:rgba(255,255,255,.7)">
        <?= strtoupper(substr($_SESSION['admin_name'],0,1)) ?>
      </div>
      <div class="flex-1 min-w-0">
        <div class="text-xs text-white/80 truncate"><?= e($_SESSION['admin_name']) ?></div>
        <div style="font-size:.65rem;color:rgba(255,255,255,.3);text-transform:capitalize"><?= e($_SESSION['admin_role']) ?></div>
      </div>
      <a href="<?= APP_URL ?>/admin/logout.php" title="Logout" class="w-8 h-8 rounded-lg flex items-center justify-center hover:bg-red-500/20 transition-all" style="border:1px solid rgba(255,255,255,.08)">
        <i data-lucide="log-out" style="width:14px;height:14px;color:rgba(255,255,255,.4)"></i>
      </a>
    </div>
  </div>
</aside>
