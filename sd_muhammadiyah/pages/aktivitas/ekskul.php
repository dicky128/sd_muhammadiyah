<?php
require_once __DIR__ . '/../../includes/config.php';
$pageTitle  = 'Ekstrakurikuler';
$activePage = 'ekskul';
require_once ROOT_PATH . '/includes/header.php';

try {
    $ekskuls = db()->query("SELECT * FROM extracurricular WHERE is_active=1 ORDER BY sort_order,name")->fetchAll();
} catch(Exception $e){ $ekskuls=[]; }
?>

<main class="pt-20 min-h-screen bg-gradient-to-b from-black via-zinc-950 to-black">
  <section class="page-hero py-24">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center gap-3 mb-4 reveal"><div class="h-px w-10 bg-gold-400/70"></div><span class="text-gold-300 text-xs tracking-[.25em] uppercase">Pengembangan Diri</span></div>
      <h1 class="font-display text-5xl lg:text-6xl font-light text-white reveal">Ekstra<em class="text-gold-shimmer not-italic">kurikuler</em></h1>
      <p class="text-white/50 mt-4 font-light reveal"><?= count($ekskuls) ?> kegiatan aktif</p>
    </div>
  </section>

  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
    <?php if (empty($ekskuls)): ?>
    <div class="glass rounded-3xl p-16 text-center"><i data-lucide="sparkles" class="w-10 h-10 mx-auto mb-3" style="color:rgba(255,255,255,.2)"></i><p class="text-white/30">Data ekstrakurikuler belum tersedia.</p></div>
    <?php else: ?>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach($ekskuls as $i=>$e): ?>
      <div class="glass rounded-2xl overflow-hidden group hover:-translate-y-1 hover:bg-white/10 transition-all duration-300 reveal" style="animation-delay:<?= $i*.06 ?>s">
        <?php if (!empty($e['image'])): ?>
        <div class="h-44 overflow-hidden"><img src="<?= UPLOAD_URL ?>ekskul/<?= htmlspecialchars($e['image']) ?>" alt="<?= htmlspecialchars($e['name']) ?>" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"></div>
        <?php endif; ?>
        <div class="p-6">
          <div class="flex items-start gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background:rgba(212,170,58,.1)">
              <i data-lucide="<?= e($e['icon']??'star') ?>" class="w-4 h-4 text-gold-400"></i>
            </div>
            <div>
              <h3 class="font-display text-xl text-white font-light"><?= e($e['name']) ?></h3>
              <?php if (!empty($e['coach'])): ?><p class="text-white/40 text-xs">Pembina: <?= e($e['coach']) ?></p><?php endif; ?>
            </div>
          </div>
          <?php if (!empty($e['description'])): ?><p class="text-white/50 text-sm leading-relaxed mb-3"><?= e($e['description']) ?></p><?php endif; ?>
          <?php if (!empty($e['schedule'])): ?>
          <div class="flex items-center gap-2 mt-3 pt-3 border-t border-white/[.07]">
            <i data-lucide="clock" class="w-3 h-3 text-gold-400/60"></i>
            <span class="text-xs text-white/40"><?= e($e['schedule']) ?></span>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</main>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
