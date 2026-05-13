<?php
require_once __DIR__.'/../../includes/config.php';
$pageTitle=$pageHeroTitle='Fasilitas Sekolah';
$pageHeroLabel='Sarana & Prasarana';
$pageHeroSub='Berbagai fasilitas modern yang kami sediakan untuk mendukung proses belajar mengajar yang optimal.';
$pageHeroColor='sky'; $activePage='fasilitas'; $breadcrumbParent='Media';
require_once ROOT_PATH.'header.php';

try { $facilities=db()->query("SELECT * FROM facilities ORDER BY sort_order,name")->fetchAll(); }
catch(Exception $e){ $facilities=[]; }

$condLabel=['baik'=>'Baik','cukup'=>'Cukup','rusak_ringan'=>'Perlu Perbaikan','rusak_berat'=>'Rusak Berat'];
$condClass=['baik'=>'section-label-sky','cukup'=>'section-label-gold','rusak_ringan'=>'section-label-pink','rusak_berat'=>'section-label-pink'];
$cardColors=['baik'=>'icon-badge-sky','cukup'=>'icon-badge-gold','rusak_ringan'=>'icon-badge-pink','rusak_berat'=>'icon-badge-pink'];
?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
  <?php if(empty($facilities)): ?>
  <div class="glass-card rounded-3xl p-16 text-center">
    <i data-lucide="layout-grid" class="w-10 h-10 mx-auto mb-3 text-pink-200"></i>
    <p class="text-gray-400">Data fasilitas belum tersedia.</p>
  </div>
  <?php else: ?>

  <!-- Summary Cards -->
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-16 stagger-grid">
    <?php
    $total=count($facilities);
    $totalUnits=array_sum(array_column($facilities,'count'));
    $baik=count(array_filter($facilities,fn($f)=>$f['condition']==='baik'));
    ?>
    <?php foreach([
      ['layout-grid','Total Jenis',$total,'icon-badge-pink'],
      ['package','Total Unit',$totalUnits,'icon-badge-sky'],
      ['check-circle','Kondisi Baik',$baik,'icon-badge-gold'],
      ['percent','% Baik',round($total>0?($baik/$total)*100:0).'%','icon-badge-sky'],
    ] as $s): ?>
    <div class="tilt-card glass-card rounded-2xl p-5 lift-card">
      <div class="tilt-inner"><div class="tilt-shine"></div>
        <div class="<?=$s[3]?> icon-badge mb-3"><i data-lucide="<?=$s[0]?>" class="w-5 h-5"></i></div>
        <div class="font-display font-bold text-2xl text-gray-800"><?=$s[2]?></div>
        <div class="text-gray-400 text-xs mt-1"><?=$s[1]?></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Facilities Grid -->
  <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 stagger-grid">
    <?php foreach($facilities as $i=>$f): ?>
    <div class="tilt-card glass-card rounded-2xl overflow-hidden lift-card reveal-fade" style="animation-delay:<?=$i*.05?>s">
      <div class="tilt-inner h-full"><div class="tilt-shine"></div>
        <?php if(!empty($f['image'])): ?>
        <div class="h-44 overflow-hidden">
          <img src="<?=UPLOAD_URL?>facilities/<?=e($f['image'])?>" alt="<?=e($f['name'])?>"
               class="w-full h-full object-cover hover:scale-105 transition-transform duration-500" loading="lazy">
        </div>
        <?php else: ?>
        <div class="h-28 flex items-center justify-center" style="background:linear-gradient(135deg,#fdf2f8,#f0f9ff)">
          <i data-lucide="<?=e($f['icon']??'square')?>" class="w-10 h-10 text-pink-200"></i>
        </div>
        <?php endif; ?>
        <div class="p-5">
          <div class="flex items-start gap-4 mb-3">
            <div class="<?=$cardColors[$f['condition']]??'icon-badge-pink'?> icon-badge flex-shrink-0">
              <i data-lucide="<?=e($f['icon']??'square')?>" class="w-5 h-5"></i>
            </div>
            <div class="flex-1">
              <h3 class="font-display font-semibold text-gray-800 text-lg leading-tight"><?=e($f['name'])?></h3>
              <div class="flex items-center gap-2 mt-1">
                <span class="text-gray-400 text-xs"><?=$f['count']?> unit</span>
                <span class="section-label <?=$condClass[$f['condition']]??'section-label-gold'?>"><?=$condLabel[$f['condition']]??ucfirst($f['condition'])?></span>
              </div>
            </div>
          </div>
          <?php if(!empty($f['description'])): ?>
          <p class="text-gray-500 text-sm leading-relaxed"><?=e($f['description'])?></p>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<?php require_once ROOT_PATH.'footer.php'; ?>