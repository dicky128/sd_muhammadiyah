<?php
// includes/footer.php — Light mode shared footer
try { if(empty($profileData)) $profileData=db()->query("SELECT * FROM school_profile LIMIT 1")->fetch()??[]; }
catch(Exception $e){ $profileData=[]; }
$siteName = $profileData['school_name'] ?? APP_NAME;
?>
</main>

<footer class="footer-light mt-auto">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-10">
      <div class="lg:col-span-2">
        <div class="flex items-center gap-3 mb-5">
          <div class="w-10 h-10 rounded-2xl flex items-center justify-center" style="background:linear-gradient(135deg,#fbcfe8,#fef3c7);border:1px solid rgba(244,114,182,.3)">
            <span style="font-family:'Playfair Display',serif;color:#be185d;font-weight:700;font-size:1rem">ص</span>
          </div>
          <div><div class="text-sm font-semibold text-gray-700">SD Muhammadiyah 1</div>
          <div class="text-[10px] tracking-widest uppercase font-medium text-pink-400">Gentasari · Cilacap</div></div>
        </div>
        <p class="text-gray-400 text-sm font-light leading-relaxed max-w-xs">Menjadi sekolah Islam unggulan yang membentuk generasi cerdas berkarakter dan berakhlak mulia.</p>
        <div class="flex gap-3 mt-5">
          <?php
          $socialIcons = [
            'instagram' => '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>',
            'facebook'  => '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>',
            'youtube'   => '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46a2.78 2.78 0 0 0-1.95 1.96A29 29 0 0 0 1 12a29 29 0 0 0 .46 5.58A2.78 2.78 0 0 0 3.41 19.6C5.12 20 12 20 12 20s6.88 0 8.59-.46a2.78 2.78 0 0 0 1.95-1.95A29 29 0 0 0 23 12a29 29 0 0 0-.46-5.58z"/><polygon points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02"/></svg>',
          ];
          $socials = [
            ['instagram', $profileData['instagram'] ?? ''],
            ['facebook',  $profileData['facebook']  ?? ''],
            ['youtube',   $profileData['youtube']   ?? ''],
          ];
          foreach ($socials as [$key, $url]):
            if (!$url) continue;
          ?>
          <a href="<?=e($url)?>" target="_blank" rel="noopener noreferrer"
             class="w-9 h-9 rounded-xl flex items-center justify-center hover:scale-110 transition-all duration-200 text-pink-400 hover:text-pink-600 hover:bg-pink-100"
             style="background:rgba(244,114,182,.1);border:1px solid rgba(244,114,182,.2)"
             aria-label="<?=ucfirst($key)?>">
            <?= $socialIcons[$key] ?>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
      <div>
        <h4 class="text-xs tracking-widest uppercase font-semibold text-gray-400 mb-5">Navigasi</h4>
        <ul class="space-y-3">
          <?php foreach([[APP_URL.'/pages/profile/sekolah.php','Profil Sekolah'],[APP_URL.'/pages/profile/guru-staff.php','Guru & Staff'],[APP_URL.'/pages/media/galeri.php','Galeri Foto'],[APP_URL.'/pages/aktivitas/pengumuman.php','Pengumuman'],[APP_URL.'/pages/interaksi/kontak.php','Kontak Kami']] as $l): ?>
          <li><a href="<?=$l[0]?>" class="text-gray-400 hover:text-pink-500 transition-colors text-sm flex items-center gap-2">
            <i data-lucide="chevron-right" class="w-3 h-3"></i><?=$l[1]?>
          </a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div>
        <h4 class="text-xs tracking-widest uppercase font-semibold text-gray-400 mb-5">Kontak</h4>
        <ul class="space-y-4">
          <?php if(!empty($profileData['address'])): ?><li class="flex gap-3 text-sm text-gray-400"><i data-lucide="map-pin" class="w-4 h-4 text-pink-400 flex-shrink-0 mt-0.5"></i><span><?=e($profileData['address'].', '.($profileData['district']??'').', '.($profileData['city']??''))?></span></li><?php endif; ?>
          <?php if(!empty($profileData['phone'])): ?><li class="flex gap-3 text-sm text-gray-400"><i data-lucide="phone" class="w-4 h-4 text-pink-400 flex-shrink-0"></i><?=e($profileData['phone'])?></li><?php endif; ?>
          <?php if(!empty($profileData['email'])): ?><li class="flex gap-3 text-sm text-gray-400"><i data-lucide="mail" class="w-4 h-4 text-pink-400 flex-shrink-0"></i><?=e($profileData['email'])?></li><?php endif; ?>
        </ul>
      </div>
    </div>
    <div class="mt-12 pt-8 flex flex-col sm:flex-row items-center justify-between gap-4" style="border-top:1px solid rgba(244,114,182,.12)">
      <p class="text-gray-300 text-xs">&copy; <?=date('Y')?> <?=e($siteName)?>. All rights reserved.</p>
      <a href="<?=APP_URL?>/admin/login.php" class="text-gray-300 hover:text-gray-500 text-xs flex items-center gap-1.5 transition-colors">
        <i data-lucide="lock" class="w-3 h-3"></i> Admin Panel
      </a>
    </div>
  </div>
</footer>

</div><!-- /page-wrapper -->

<script src="<?=APP_URL?>/assets/js/scroll3d.js" defer></script>
<script src="<?=APP_URL?>/assets/js/animations.js" defer></script>
<script>document.addEventListener('DOMContentLoaded',()=>lucide.createIcons())</script>
</body></html>