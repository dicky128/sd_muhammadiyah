<?php
// includes/footer.php — shared footer for all public pages
try {
    if (empty($profileData)) $profileData = db()->query("SELECT * FROM school_profile LIMIT 1")->fetch() ?: [];
} catch (Exception $e) { $profileData = []; }
$siteName = $profileData['school_name'] ?? APP_NAME;
?>
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
          <a href="<?= e($profileData['instagram']) ?>" target="_blank" class="w-9 h-9 glass rounded-xl flex items-center justify-center hover:bg-white/15 transition-all hover:scale-110"><i data-lucide="instagram" class="w-4 h-4 text-white/60"></i></a>
          <?php endif; ?>
          <?php if (!empty($profileData['facebook'])): ?>
          <a href="<?= e($profileData['facebook']) ?>" target="_blank" class="w-9 h-9 glass rounded-xl flex items-center justify-center hover:bg-white/15 transition-all hover:scale-110"><i data-lucide="facebook" class="w-4 h-4 text-white/60"></i></a>
          <?php endif; ?>
          <?php if (!empty($profileData['youtube'])): ?>
          <a href="<?= e($profileData['youtube']) ?>" target="_blank" class="w-9 h-9 glass rounded-xl flex items-center justify-center hover:bg-white/15 transition-all hover:scale-110"><i data-lucide="youtube" class="w-4 h-4 text-white/60"></i></a>
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
<script>lucide.createIcons();</script>
</body>
</html>
