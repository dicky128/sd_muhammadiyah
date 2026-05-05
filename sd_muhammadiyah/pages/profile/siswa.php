<?php
require_once __DIR__ . '/../../includes/config.php';
$pageTitle  = 'Data Siswa';
$activePage = 'siswa';
require_once ROOT_PATH . '/includes/header.php';

try {
    $latestYear = db()->query("SELECT MAX(academic_year) FROM student_stats")->fetchColumn();
    $stats      = [];
    if ($latestYear) {
        $rows = db()->prepare("SELECT grade,gender,count FROM student_stats WHERE academic_year=? ORDER BY grade,gender");
        $rows->execute([$latestYear]);
        foreach ($rows->fetchAll() as $r) {
            $stats[$r['grade']][$r['gender']] = $r['count'];
        }
    }
    $achievements = db()->query("SELECT * FROM student_achievements ORDER BY year DESC, level DESC LIMIT 12")->fetchAll();
} catch(Exception $e){ $stats=[]; $achievements=[]; $latestYear=''; }

$levelLabel = ['sekolah'=>'Tingkat Sekolah','kecamatan'=>'Tingkat Kecamatan','kabupaten'=>'Tingkat Kabupaten','provinsi'=>'Tingkat Provinsi','nasional'=>'Tingkat Nasional','internasional'=>'Internasional'];
$levelColor = ['sekolah'=>'text-white/40 bg-white/5','kecamatan'=>'text-blue-300 bg-blue-500/15','kabupaten'=>'text-green-300 bg-green-500/15','provinsi'=>'text-yellow-300 bg-yellow-500/15','nasional'=>'text-orange-300 bg-orange-500/15','internasional'=>'text-red-300 bg-red-500/15'];
?>

<main class="pt-20 min-h-screen bg-gradient-to-b from-black via-zinc-950 to-black">
  <section class="page-hero py-24">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center gap-3 mb-4 reveal"><div class="h-px w-10 bg-gold-400/70"></div><span class="text-gold-300 text-xs tracking-[.25em] uppercase">Peserta Didik</span></div>
      <h1 class="font-display text-5xl lg:text-6xl font-light text-white reveal">Data <em class="text-gold-shimmer not-italic">Siswa</em></h1>
      <?php if ($latestYear): ?><p class="text-white/50 mt-4 font-light reveal">Tahun Ajaran <?= e($latestYear) ?></p><?php endif; ?>
    </div>
  </section>

  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-20 space-y-16">

    <!-- Stats Table -->
    <?php if (!empty($stats)): ?>
    <div class="reveal">
      <h2 class="font-display text-3xl text-white font-light mb-8">Rekap <em class="text-gold-shimmer not-italic">Per Kelas</em></h2>
      <div class="glass rounded-2xl overflow-hidden">
        <table class="w-full">
          <thead>
            <tr style="border-bottom:1px solid rgba(255,255,255,.08)">
              <?php foreach(['Kelas','Laki-laki','Perempuan','Total'] as $h): ?>
              <th class="px-6 py-4 text-left" style="font-size:.68rem;letter-spacing:.15em;text-transform:uppercase;color:rgba(255,255,255,.35);font-weight:400"><?= $h ?></th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/[0.05]">
            <?php
            $totalL=0; $totalP=0;
            foreach ($stats as $grade => $genders):
              $l = $genders['L'] ?? 0; $p_ = $genders['P'] ?? 0;
              $totalL+=$l; $totalP+=$p_;
            ?>
            <tr class="hover:bg-white/[.03] transition-colors">
              <td class="px-6 py-4 text-white/80 text-sm font-medium"><?= e($grade) ?></td>
              <td class="px-6 py-4"><span class="text-blue-300 text-sm"><?= $l ?></span></td>
              <td class="px-6 py-4"><span class="text-pink-300 text-sm"><?= $p_ ?></span></td>
              <td class="px-6 py-4 font-display text-xl text-white font-light"><?= $l+$p_ ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr style="border-top:1px solid rgba(255,255,255,.1)">
              <td class="px-6 py-4 text-gold-400 text-xs uppercase tracking-widest font-medium">Total</td>
              <td class="px-6 py-4 font-display text-xl text-blue-300 font-light"><?= $totalL ?></td>
              <td class="px-6 py-4 font-display text-xl text-pink-300 font-light"><?= $totalP ?></td>
              <td class="px-6 py-4 font-display text-2xl text-white font-light"><?= $totalL+$totalP ?></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
    <?php endif; ?>

    <!-- Achievements -->
    <?php if (!empty($achievements)): ?>
    <div class="reveal">
      <div class="flex items-center gap-3 mb-2"><div class="h-px w-8 bg-gold-400/60"></div><span class="text-gold-400 text-xs tracking-[.25em] uppercase">Kebanggaan Kami</span></div>
      <h2 class="font-display text-3xl text-white font-light mb-8">Prestasi <em class="text-gold-shimmer not-italic">Siswa</em></h2>
      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <?php foreach($achievements as $i=>$ach): ?>
        <div class="glass rounded-2xl p-5 hover:-translate-y-1 hover:bg-white/10 transition-all reveal" style="animation-delay:<?= $i*.06 ?>s">
          <div class="flex items-start justify-between mb-3">
            <span class="text-[11px] px-2.5 py-1 rounded-full <?= $levelColor[$ach['level']]??'text-white/40 bg-white/5' ?>"><?= $levelLabel[$ach['level']]??ucfirst($ach['level']) ?></span>
            <?php if ($ach['year']): ?><span class="text-white/30 text-xs"><?= $ach['year'] ?></span><?php endif; ?>
          </div>
          <h3 class="text-white/85 font-medium text-sm leading-snug"><?= e($ach['title']) ?></h3>
          <?php if (!empty($ach['student_name'])): ?><p class="text-gold-400 text-xs mt-2"><?= e($ach['student_name']) ?><?php if ($ach['grade']): ?> · <?= e($ach['grade']) ?><?php endif; ?></p><?php endif; ?>
          <?php if (!empty($ach['description'])): ?><p class="text-white/40 text-xs mt-2 leading-relaxed"><?= e(mb_substr($ach['description'],0,100)).(mb_strlen($ach['description'])>100?'…':'') ?></p><?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php if (empty($stats) && empty($achievements)): ?>
    <div class="glass rounded-3xl p-16 text-center"><i data-lucide="graduation-cap" class="w-10 h-10 mx-auto mb-3" style="color:rgba(255,255,255,.2)"></i><p class="text-white/30 text-sm">Data siswa belum tersedia.</p></div>
    <?php endif; ?>
  </div>
</main>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
