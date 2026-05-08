<?php
// includes/footer.php — shared footer for all public pages
try {
    if (empty($profileData)) $profileData = db()->query("SELECT * FROM school_profile LIMIT 1")->fetch() ?: [];
} catch (Exception $e) { $profileData = []; }
$siteName = $profileData['school_name'] ?? APP_NAME;
?>
<link rel="stylesheet" href="https://cloudflare.com">
<footer class="bg-black border-t border-white/[0.06] mt-auto">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-10">
      <div class="lg:col-span-2">
        <div class="flex items-center gap-3 mb-5">
          <div class="w-10 h-10 rounded-xl glass-strong flex items-center justify-center">
            <span class="text-gold-400 font-display font-bold text-lg">ص</span>
          </div>
          <div>
            <div class="text-white font-medium text-sm">SD Muhammadiyah 1</div>
            <div class="text-gold-400 text-[10px] tracking-widest uppercase font-light">Gentasari · Cilacap</div>
          </div>
        </div>
        <p class="text-white/35 font-light text-sm leading-relaxed max-w-xs">
          Menjadi sekolah Islam unggulan yang membentuk generasi cerdas berkarakter dan berakhlak mulia.
        </p> 
        <div class="flex gap-3 mt-5">
          <?php if (!empty($profileData['instagram'])): ?>
          <a href="<?= e($profileData['instagram']) ?>" target="_blank" class="w-9 h-9 glass rounded-xl flex items-center justify-center hover:bg-white/15 transition-all hover:scale-110">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide-instagram">
            <rect width="20" height="20" x="2" y="2" rx="5" ry="5"/>
            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/>
            <line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/></svg>
          </a>
          <?php endif; ?>
          <?php if (!empty($profileData['facebook'])): ?>
          <a href="<?= e($profileData['facebook']) ?>" target="_blank" class="w-9 h-9 glass rounded-xl flex items-center justify-center hover:bg-white/15 transition-all hover:scale-110">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>
          </svg></a>
          <?php endif; ?>
          <?php if (!empty($profileData['youtube'])): ?>
          <a href="<?= e($profileData['youtube']) ?>" target="_blank" class="w-9 h-9 glass rounded-xl flex items-center justify-center hover:bg-white/15 transition-all hover:scale-110"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"/>
          <polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"/>
          </svg></a>
          <?php endif; ?>
        </div>
      </div>
      <div>
        <h4 class="text-white/80 text-xs tracking-[0.2em] uppercase mb-5 font-medium">Navigasi</h4>
        <ul class="space-y-3 text-sm">
          <?php foreach ([
            [APP_URL.'/pages/profile/sekolah.php','Profil Sekolah'],
            [APP_URL.'/pages/profile/guru-staff.php','Guru &amp; Staff'],
            [APP_URL.'/pages/media/galeri.php','Galeri Foto'],
            [APP_URL.'/pages/aktivitas/pengumuman.php','Pengumuman'],
            [APP_URL.'/pages/interaksi/kontak.php','Kontak Kami'],
          ] as $l): ?>
          <li><a href="<?= $l[0] ?>" class="text-white/35 hover:text-gold-300 transition-colors flex items-center gap-2">
            <i data-lucide="chevron-right" class="w-3 h-3"></i><?= $l[1] ?>
          </a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div>
        <h4 class="text-white/80 text-xs tracking-[0.2em] uppercase mb-5 font-medium">Kontak</h4>
        <ul class="space-y-4 text-sm">
          <?php if (!empty($profileData['address'])): ?>
          <li class="flex gap-3 text-white/35"><i data-lucide="map-pin" class="w-4 h-4 text-gold-400/60 flex-shrink-0 mt-0.5"></i><span><?= e($profileData['address'].', '.($profileData['district']??'').', '.($profileData['city']??'')) ?></span></li>
          <?php endif; ?>
          <?php if (!empty($profileData['phone'])): ?>
          <li class="flex gap-3 text-white/35"><i data-lucide="phone" class="w-4 h-4 text-gold-400/60 flex-shrink-0"></i><span><?= e($profileData['phone']) ?></span></li>
          <?php endif; ?>
          <?php if (!empty($profileData['email'])): ?>
          <li class="flex gap-3 text-white/35"><i data-lucide="mail" class="w-4 h-4 text-gold-400/60 flex-shrink-0"></i><span><?= e($profileData['email']) ?></span></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
    <div class="mt-14 pt-8 border-t border-white/[0.06] flex flex-col sm:flex-row items-center justify-between gap-4">
      <p class="text-white/20 text-xs">&copy; <?= date('Y') ?> <?= e($siteName) ?>. All rights reserved.</p>
      <a href="<?= APP_URL ?>/admin/login.php" class="text-white/15 hover:text-white/40 transition-colors text-xs flex items-center gap-1.5">
        <i data-lucide="lock" class="w-3 h-3"></i> Admin Panel
      </a>
    </div>
  </div>
</footer>
</body>
</html>
