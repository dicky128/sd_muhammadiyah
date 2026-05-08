<?php
require_once __DIR__ . '/../../includes/config.php';
$pageTitle  = 'Fasilitas Sekolah';
$activePage = 'fasilitas';
require_once ROOT_PATH . '../../includes/header.php';

try {
    $facilities = db()->query("SELECT * FROM facilities ORDER BY sort_order,name")->fetchAll();
} catch(Exception $e){ $facilities=[]; }

$condLabel=['baik'=>'Baik','cukup'=>'Cukup','rusak_ringan'=>'Perlu Perbaikan','rusak_berat'=>'Rusak Berat'];
$condColor=['baik'=>'text-green-300 bg-green-500/15','cukup'=>'text-yellow-300 bg-yellow-500/15','rusak_ringan'=>'text-orange-300 bg-orange-500/15','rusak_berat'=>'text-red-300 bg-red-500/15'];
?>

<main class="pt-20 min-h-screen bg-gradient-to-b from-black via-zinc-950 to-black">
  <section class="page-hero py-24">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center gap-3 mb-4 reveal"><div class="h-px w-10 bg-gold-400/70"></div><span class="text-gold-300 text-xs tracking-[.25em] uppercase">Sarana &amp; Prasarana</span></div>
      <h1 class="font-display text-5xl lg:text-6xl font-light text-white reveal">Fasilitas <em class="text-gold-shimmer not-italic">Sekolah</em></h1>
      <p class="text-white/50 mt-4 font-light reveal"><?= count($facilities) ?> jenis fasilitas tersedia</p>
    </div>
  </section>

  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
    <?php if (empty($facilities)): ?>
    <div class="glass rounded-3xl p-16 text-center"><i data-lucide="layout-grid" class="w-10 h-10 mx-auto mb-3" style="color:rgba(255,255,255,.2)"></i><p class="text-white/30">Data fasilitas belum tersedia.</p></div>
    <?php else: ?>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach($facilities as $i=>$f): ?>
      <div class="glass rounded-2xl overflow-hidden group hover:-translate-y-1 hover:bg-white/10 transition-all duration-300 reveal" style="animation-delay:<?= $i*.05 ?>s">
        <?php if (!empty($f['image'])): ?>
        <div class="h-48 overflow-hidden">
          <img src="<?= UPLOAD_URL ?>facilities/<?= e($f['image']) ?>" alt="<?= e($f['name']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
        </div>
        <?php endif; ?>
        <div class="p-6">
          <div class="flex items-start gap-4 mb-4">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" style="background:rgba(212,170,58,.1);border:1px solid rgba(212,170,58,.2)">
              <i data-lucide="<?= e($f['icon']??'square') ?>" class="w-5 h-5 text-gold-400"></i>
            </div>
            <div class="flex-1">
              <h3 class="font-display text-xl text-white font-light"><?= e($f['name']) ?></h3>
              <div class="flex items-center gap-3 mt-1">
                <span class="text-white/40 text-xs"><?= $f['count'] ?> unit</span>
                <span class="text-[10px] px-2 py-0.5 rounded-full <?= $condColor[$f['condition']]??'text-white/40 bg-white/5' ?>"><?= $condLabel[$f['condition']]??ucfirst($f['condition']) ?></span>
              </div>
            </div>
          </div>
          <?php if (!empty($f['description'])): ?>
          <p class="text-white/45 text-sm leading-relaxed"><?= e($f['description']) ?></p>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</main>

<?php require_once ROOT_PATH . '../../includes/footer.php'; ?>
