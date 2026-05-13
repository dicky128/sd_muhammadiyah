<?php
require_once __DIR__.'/../../includes/config.php';
$pageTitle=$pageHeroTitle='Data Siswa';
$pageHeroLabel='Peserta Didik';
$pageHeroSub='Rekap jumlah siswa per kelas dan kumpulan prestasi membanggakan yang telah diraih.';
$pageHeroColor='gold'; $activePage='siswa'; $breadcrumbParent='Profil';
require_once ROOT_PATH.'header.php';

try {
    $latestYear=db()->query("SELECT MAX(academic_year) FROM student_stats")->fetchColumn();
    $statsArr=[];
    if($latestYear){
        $rows=db()->prepare("SELECT grade,gender,count FROM student_stats WHERE academic_year=? ORDER BY grade,gender");
        $rows->execute([$latestYear]);
        foreach($rows->fetchAll() as $r) $statsArr[$r['grade']][$r['gender']]=$r['count'];
    }
    $achievements=db()->query("SELECT * FROM student_achievements ORDER BY year DESC, level DESC LIMIT 12")->fetchAll();
} catch(Exception $e){ $statsArr=[]; $achievements=[]; $latestYear=''; }

$levelLabel=['sekolah'=>'Sekolah','kecamatan'=>'Kecamatan','kabupaten'=>'Kabupaten','provinsi'=>'Provinsi','nasional'=>'Nasional','internasional'=>'Internasional'];
$levelClass=['sekolah'=>'section-label-gold','kecamatan'=>'section-label-sky','kabupaten'=>'section-label-sky','provinsi'=>'section-label-gold','nasional'=>'section-label-pink','internasional'=>'section-label-pink'];
?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-20 space-y-16">

  <!-- Stats Table -->
  <?php if(!empty($statsArr)): ?>
  <div class="reveal-3d">
    <div class="flex items-center gap-4 mb-8">
      <div class="icon-badge icon-badge-gold"><i data-lucide="bar-chart-2" class="w-5 h-5"></i></div>
      <div>
        <h2 class="font-display font-bold text-gray-800 text-2xl">Rekap Per Kelas</h2>
        <?php if($latestYear): ?><p class="text-gray-400 text-sm">Tahun Ajaran <?=e($latestYear)?></p><?php endif; ?>
      </div>
    </div>
    <div class="glass-card rounded-2xl overflow-hidden" style="box-shadow:0 8px 40px rgba(212,170,58,.1)">
      <table class="w-full">
        <thead>
          <tr style="background:linear-gradient(135deg,rgba(253,242,248,.8),rgba(254,249,231,.8));border-bottom:1px solid rgba(244,114,182,.1)">
            <?php foreach(['Kelas','Laki-laki','Perempuan','Total'] as $h): ?>
            <th class="px-6 py-4 text-left" style="font-size:.68rem;letter-spacing:.15em;text-transform:uppercase;color:#be185d;font-weight:700"><?=$h?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody class="divide-y divide-pink-50">
          <?php $tL=0;$tP=0;
          foreach($statsArr as $g=>$gd): $l=$gd['L']??0; $p_=$gd['P']??0; $tL+=$l; $tP+=$p_; ?>
          <tr class="hover:bg-pink-50/50 transition-colors">
            <td class="px-6 py-4 text-sm font-semibold text-gray-700">Kelas <?=e($g)?></td>
            <td class="px-6 py-4"><span class="font-semibold text-sky-500"><?=$l?></span></td>
            <td class="px-6 py-4"><span class="font-semibold text-pink-500"><?=$p_?></span></td>
            <td class="px-6 py-4"><span class="font-display font-bold text-xl text-gray-800"><?=$l+$p_?></span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr style="background:rgba(253,242,248,.6);border-top:2px solid rgba(244,114,182,.15)">
            <td class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-pink-500">Total</td>
            <td class="px-6 py-4 font-display font-bold text-xl text-sky-500"><?=$tL?></td>
            <td class="px-6 py-4 font-display font-bold text-xl text-pink-500"><?=$tP?></td>
            <td class="px-6 py-4 font-display font-bold text-2xl text-gradient-pink-gold"><?=$tL+$tP?></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <!-- Achievements -->
  <?php if(!empty($achievements)): ?>
  <div>
    <div class="flex items-center gap-4 mb-4 reveal-fade">
      <div class="ornament-line"></div>
      <span class="section-label section-label-pink">Kebanggaan Kami</span>
    </div>
    <h2 class="font-display font-bold text-gray-800 mb-10 reveal-heading" style="font-size:clamp(1.6rem,3vw,2.5rem)">
      Prestasi <em class="text-gradient-pink-gold not-italic">Siswa</em>
    </h2>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 stagger-grid">
      <?php foreach($achievements as $i=>$ach): ?>
      <div class="tilt-card glass-card rounded-2xl p-5 lift-card" style="animation-delay:<?=$i*.06?>s">
        <div class="tilt-inner"><div class="tilt-shine"></div>
          <div class="flex items-start justify-between mb-3">
            <span class="section-label <?=$levelClass[$ach['level']]??'section-label-gold'?>"><?=$levelLabel[$ach['level']]??ucfirst($ach['level'])?></span>
            <?php if($ach['year']): ?><span class="text-gray-400 text-xs font-semibold"><?=$ach['year']?></span><?php endif; ?>
          </div>
          <h3 class="text-gray-700 font-semibold text-sm leading-snug mb-2"><?=e($ach['title'])?></h3>
          <?php if(!empty($ach['student_name'])): ?>
          <p class="text-pink-500 text-xs font-semibold"><?=e($ach['student_name'])?><?php if($ach['grade']): ?> · <?=e($ach['grade'])?><?php endif; ?></p>
          <?php endif; ?>
          <?php if(!empty($ach['description'])): ?>
          <p class="text-gray-400 text-xs mt-2 leading-relaxed"><?=e(mb_substr($ach['description'],0,100)).(mb_strlen($ach['description'])>100?'…':'')?></p>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <?php if(empty($statsArr)&&empty($achievements)): ?>
  <div class="glass-card rounded-3xl p-16 text-center">
    <i data-lucide="graduation-cap" class="w-10 h-10 mx-auto mb-3 text-pink-200"></i>
    <p class="text-gray-400">Data siswa belum tersedia.</p>
  </div>
  <?php endif; ?>
</div>

<?php require_once ROOT_PATH.'footer.php'; ?>