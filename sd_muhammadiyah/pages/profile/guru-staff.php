<?php
require_once __DIR__.'/../../includes/config.php';
$pageTitle=$pageHeroTitle='Guru & Staff';
$pageHeroLabel='Sumber Daya Manusia';
$pageHeroSub='Para pendidik dan tenaga kependidikan berdedikasi yang membimbing generasi penerus bangsa.';
$pageHeroColor='sky'; $activePage='guru'; $breadcrumbParent='Profil';
require_once ROOT_PATH.'header.php';
try {
    $teachers=db()->query("SELECT * FROM teachers WHERE is_active=1 AND type='guru' ORDER BY sort_order,full_name")->fetchAll();
    $staff=db()->query("SELECT * FROM teachers WHERE is_active=1 AND type='staff' ORDER BY sort_order,full_name")->fetchAll();
} catch(Exception $e){ $teachers=$staff=[]; }
?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16 space-y-20">
  <?php foreach([[$teachers,'Tenaga Pengajar','Guru','icon-badge-pink'],[$staff,'Tenaga Staff','Staff','icon-badge-sky']] as [$list,$heading,$type,$badge]):
    if(empty($list)) continue; ?>
  <div>
    <div class="flex items-center gap-4 mb-10 reveal-fade">
      <div class="ornament-line"></div>
      <span class="section-label section-label-pink"><?=$type?></span>
    </div>
    <h2 class="font-display font-bold text-gray-800 mb-10 reveal-heading" style="font-size:clamp(1.6rem,3vw,2.5rem)"><?=$heading?></h2>
    <div class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5 stagger-grid">
      <?php foreach($list as $t): ?>
      <div class="tilt-card glass-card rounded-2xl overflow-hidden lift-card">
        <div class="tilt-inner h-full"><div class="tilt-shine"></div>
          <div class="h-44 relative" style="background:linear-gradient(135deg,#fce7f3,#e0f2fe)">
            <?php if(!empty($t['photo'])): ?>
            <img src="<?=UPLOAD_URL?>teachers/<?=e($t['photo'])?>" class="w-full h-full object-cover object-top" loading="lazy">
            <?php else: ?>
            <div class="w-full h-full flex items-center justify-center">
              <span class="font-display font-bold text-5xl text-pink-200"><?=strtoupper(substr($t['full_name'],0,1))?></span>
            </div>
            <?php endif; ?>
            <?php if(!empty($t['position'])): ?>
            <div class="absolute bottom-3 left-3 right-3">
              <span class="text-[10px] px-2.5 py-1 rounded-full font-semibold" style="background:rgba(255,255,255,.85);backdrop-filter:blur(8px);color:#be185d"><?=e($t['position'])?></span>
            </div>
            <?php endif; ?>
          </div>
          <div class="p-4">
            <h3 class="font-display font-semibold text-gray-800 leading-tight"><?=e($t['full_name'])?></h3>
            <?php if(!empty($t['subject'])): ?><p class="text-pink-500 text-xs mt-1 font-semibold"><?=e($t['subject'])?></p><?php endif; ?>
            <?php if(!empty($t['education'])): ?><p class="text-gray-400 text-xs mt-1"><?=e($t['education'])?></p><?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endforeach; ?>
  <?php if(empty($teachers)&&empty($staff)): ?>
  <div class="glass-card rounded-3xl p-16 text-center">
    <i data-lucide="users" class="w-10 h-10 mx-auto mb-3 text-pink-200"></i>
    <p class="text-gray-400">Data belum tersedia.</p>
  </div>
  <?php endif; ?>
</div>

<?php require_once ROOT_PATH.'footer.php'; ?>