<?php
require_once __DIR__ . '/../../includes/config.php';
$pageTitle  = 'Guru & Staff';
$activePage = 'guru';
require_once ROOT_PATH . '/includes/header.php';

try {
    $teachers = db()->query("SELECT * FROM teachers WHERE is_active=1 AND type='guru' ORDER BY sort_order,full_name")->fetchAll();
    $staff    = db()->query("SELECT * FROM teachers WHERE is_active=1 AND type='staff' ORDER BY sort_order,full_name")->fetchAll();
} catch(Exception $e){ $teachers=[]; $staff=[]; }

function renderCard(array $t, string $uploadUrl): void { ?>
<div class="glass rounded-2xl overflow-hidden group hover:-translate-y-1 hover:bg-white/10 transition-all duration-300 reveal">
  <div class="h-48 overflow-hidden relative" style="background:rgba(212,170,58,.06)">
    <?php if (!empty($t['photo'])): ?>
    <img src="<?= $uploadUrl ?>teachers/<?= e($t['photo']) ?>" alt="<?= e($t['full_name']) ?>"
         class="w-full h-full object-cover object-top group-hover:scale-105 transition-transform duration-500">
    <?php else: ?>
    <div class="w-full h-full flex items-center justify-center">
      <span class="font-display text-5xl text-gold-400/40 font-light"><?= strtoupper(substr($t['full_name'],0,1)) ?></span>
    </div>
    <?php endif; ?>
    <?php if (!empty($t['position'])): ?>
    <div class="absolute bottom-3 left-3 right-3">
      <span class="text-[10px] px-2.5 py-1 rounded-full" style="background:rgba(0,0,0,.6);backdrop-filter:blur(8px);color:rgba(255,255,255,.8)"><?= e($t['position']) ?></span>
    </div>
    <?php endif; ?>
  </div>
  <div class="p-5">
    <h3 class="font-display text-lg text-white font-light leading-tight"><?= e($t['full_name']) ?></h3>
    <?php if (!empty($t['subject'])): ?>
    <p class="text-gold-400 text-xs mt-1 tracking-wide"><?= e($t['subject']) ?></p>
    <?php endif; ?>
    <?php if (!empty($t['education'])): ?>
    <p class="text-white/35 text-xs mt-2"><?= e($t['education']) ?></p>
    <?php endif; ?>
    <?php if (!empty($t['nip'])): ?>
    <p class="text-white/25 text-[10px] mt-1">NIP: <?= e($t['nip']) ?></p>
    <?php endif; ?>
  </div>
</div>
<?php } ?>

<main class="pt-20 min-h-screen bg-gradient-to-b from-black via-zinc-950 to-black">
  <section class="page-hero py-24">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center gap-3 mb-4 reveal"><div class="h-px w-10 bg-gold-400/70"></div><span class="text-gold-300 text-xs tracking-[.25em] uppercase">Sumber Daya Manusia</span></div>
      <h1 class="font-display text-5xl lg:text-6xl font-light text-white reveal">Guru &amp; <em class="text-gold-shimmer not-italic">Staff</em></h1>
      <p class="text-white/50 mt-4 font-light reveal"><?= count($teachers) ?> Guru · <?= count($staff) ?> Tenaga Kependidikan</p>
    </div>
  </section>

  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
    <?php if (!empty($teachers)): ?>
    <div class="mb-5 reveal"><div class="flex items-center gap-3 mb-2"><div class="h-px w-8 bg-gold-400/60"></div><span class="text-gold-400 text-xs tracking-[.25em] uppercase">Pendidik</span></div>
      <h2 class="font-display text-3xl text-white font-light">Tenaga <em class="text-gold-shimmer not-italic">Pengajar</em></h2>
    </div>
    <div class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5 mb-16">
      <?php foreach($teachers as $t) renderCard($t, UPLOAD_URL); ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($staff)): ?>
    <div class="mb-5 reveal"><div class="flex items-center gap-3 mb-2"><div class="h-px w-8 bg-gold-400/60"></div><span class="text-gold-400 text-xs tracking-[.25em] uppercase">Kependidikan</span></div>
      <h2 class="font-display text-3xl text-white font-light">Tenaga <em class="text-gold-shimmer not-italic">Staff</em></h2>
    </div>
    <div class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
      <?php foreach($staff as $t) renderCard($t, UPLOAD_URL); ?>
    </div>
    <?php endif; ?>

    <?php if (empty($teachers) && empty($staff)): ?>
    <div class="glass rounded-3xl p-16 text-center">
      <i data-lucide="users" class="w-10 h-10 mx-auto mb-3" style="color:rgba(255,255,255,.2)"></i>
      <p class="text-white/30 text-sm">Data guru &amp; staff belum tersedia.</p>
    </div>
    <?php endif; ?>
  </div>
</main>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
