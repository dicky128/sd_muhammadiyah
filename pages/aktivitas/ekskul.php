<?php
require_once __DIR__.'/../../includes/config.php';
$pageTitle=$pageHeroTitle='Ekstrakurikuler';
$pageHeroLabel='Pengembangan Diri';
$pageHeroSub='Wadah bagi siswa untuk mengembangkan bakat, minat, dan potensi diri di luar jam pelajaran.';
$pageHeroColor='gold'; $activePage='ekskul'; $breadcrumbParent='Aktivitas';
require_once ROOT_PATH.'header.php';

try { $ekskuls=db()->query("SELECT * FROM extracurricular WHERE is_active=1 ORDER BY sort_order,name")->fetchAll(); }
catch(Exception $e){ $ekskuls=[]; }
$cardColors=['icon-badge-pink','icon-badge-gold','icon-badge-sky'];
?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-20">

  <!-- Count badge -->
  <?php if(!empty($ekskuls)): ?>
  <div class="flex items-center gap-4 mb-12">
    <div class="glass-card rounded-2xl px-5 py-3 flex items-center gap-3">
      <div class="icon-badge icon-badge-gold"><i data-lucide="sparkles" class="w-4 h-4"></i></div>
      <span class="font-display font-semibold text-gray-700 text-lg"><?=count($ekskuls)?> Kegiatan Aktif</span>
    </div>
  </div>

  <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 stagger-grid">
    <?php foreach($ekskuls as $i=>$e): ?>
    <div class="tilt-card glass-card rounded-2xl overflow-hidden lift-card reveal-3d" style="animation-delay:<?=$i*.07?>s">
      <div class="tilt-inner h-full"><div class="tilt-shine"></div>

        <?php if(!empty($e['image'])): ?>
        <div class="h-44 overflow-hidden relative">
          <img src="<?=UPLOAD_URL?>ekskul/<?=htmlspecialchars($e['image'])?>" alt="<?=htmlspecialchars($e['name'])?>"
               loading="lazy" class="w-full h-full object-cover hover:scale-105 transition-transform duration-500">
          <div class="absolute inset-0" style="background:linear-gradient(to top,rgba(244,114,182,.3),transparent)"></div>
        </div>
        <?php else: ?>
        <div class="h-28 flex items-center justify-center" style="background:linear-gradient(135deg,<?=['#fdf2f8,#fce7f3','#fef9e7,#fef3c7','#f0f9ff,#e0f2fe'][$i%3]?>)">
          <i data-lucide="<?=e($e['icon']??'star')?>" class="w-10 h-10 <?=['text-pink-300','text-yellow-400','text-sky-400'][$i%3]?>"></i>
        </div>
        <?php endif; ?>

        <div class="p-5">
          <div class="flex items-start justify-between gap-3 mb-3">
            <div class="flex items-center gap-3">
              <div class="<?=$cardColors[$i%3]?> icon-badge flex-shrink-0">
                <i data-lucide="<?=e($e['icon']??'star')?>" class="w-4 h-4"></i>
              </div>
              <div>
                <h3 class="font-display font-semibold text-gray-800 leading-tight"><?=e($e['name'])?></h3>
                <?php if(!empty($e['coach'])): ?><p class="text-gray-400 text-xs mt-0.5">Pembina: <?=e($e['coach'])?></p><?php endif; ?>
              </div>
            </div>
          </div>

          <?php if(!empty($e['description'])): ?>
          <p class="text-gray-500 text-sm leading-relaxed mb-3"><?=e($e['description'])?></p>
          <?php endif; ?>

          <?php if(!empty($e['schedule'])): ?>
          <div class="flex items-center gap-2 mt-3 pt-3" style="border-top:1px solid rgba(244,114,182,.1)">
            <div class="icon-badge icon-badge-pink w-7 h-7 rounded-lg flex-shrink-0" style="min-width:28px;min-height:28px">
              <i data-lucide="clock" class="w-3.5 h-3.5"></i>
            </div>
            <span class="text-xs text-gray-400 font-medium"><?=e($e['schedule'])?></span>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="glass-card rounded-3xl p-16 text-center">
    <i data-lucide="sparkles" class="w-10 h-10 mx-auto mb-3 text-pink-200"></i>
    <p class="text-gray-400">Data ekstrakurikuler belum tersedia.</p>
  </div>
  <?php endif; ?>
</div>

<?php require_once ROOT_PATH.'footer.php'; ?>