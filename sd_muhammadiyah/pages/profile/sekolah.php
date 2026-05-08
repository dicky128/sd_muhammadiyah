<?php
require_once __DIR__ . '/../../includes/config.php';
$pageTitle  = 'Profil Sekolah';
$activePage = 'sekolah';
require_once ROOT_PATH . '../../includes/header.php';

try {
    $p = db()->query("SELECT * FROM school_profile LIMIT 1")->fetch() ?: [];
} catch(Exception $e){ $p = []; }
?>

<main class="pt-20 min-h-screen bg-gradient-to-b from-black via-zinc-950 to-black">
  <!-- Page Hero -->
  <section class="page-hero py-24 relative">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative">
      <div class="flex items-center gap-3 mb-4 reveal">
        <div class="h-px w-10 bg-gold-400/70"></div>
        <span class="text-gold-300 text-xs tracking-[.25em] uppercase font-light">Tentang Kami</span>
      </div>
      <h1 class="font-display text-5xl lg:text-6xl font-light text-white reveal">Profil <em class="text-gold-shimmer not-italic">Sekolah</em></h1>
      <p class="text-white/50 mt-4 max-w-xl font-light reveal"><?= e($p['school_name'] ?? APP_NAME) ?> — Akreditasi <?= e($p['akreditasi']??'A') ?> · Berdiri <?= e($p['tahun_berdiri']??'1962') ?></p>
    </div>
  </section>

  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-20 space-y-16">

    <!-- Info Grid -->
    <div class="grid lg:grid-cols-3 gap-8 reveal">
      <div class="lg:col-span-2 glass rounded-3xl p-8 space-y-5">
        <h2 class="font-display text-2xl text-white font-light">Identitas Sekolah</h2>
        <?php
        $rows = [
          ['NPSN',           $p['npsn']??'-'],
          ['Nama Sekolah',   $p['school_name']??APP_NAME],
          ['Status',         'Swasta / Muhammadiyah'],
          ['Akreditasi',     $p['akreditasi']??'A'],
          ['Tahun Berdiri',  $p['tahun_berdiri']??'1962'],
          ['Desa/Kelurahan', $p['village']??'Gentasari'],
          ['Kecamatan',      $p['district']??'Kroya'],
          ['Kabupaten/Kota', $p['city']??'Cilacap'],
          ['Provinsi',       $p['province']??'Jawa Tengah'],
          ['Alamat',         $p['address']??'-'],
          ['Telepon',        $p['phone']??'-'],
          ['Email',          $p['email']??'-'],
          ['Website',        $p['website']??'-'],
        ];
        foreach ($rows as $r): ?>
        <div class="flex items-start gap-4 py-3 border-b border-white/[0.06] last:border-0">
          <span class="text-xs tracking-widest uppercase text-white/35 w-40 flex-shrink-0 pt-0.5"><?= $r[0] ?></span>
          <span class="text-sm text-white/75"><?= e($r[1]) ?></span>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="space-y-4">
        <!-- Quick stats -->
        <?php
        $qs = [
          ['users','Siswa Aktif',setting('stats_students','380').'+'],
          ['graduation-cap','Tenaga Pendidik',setting('stats_teachers','24')],
          ['sparkles','Ekstrakurikuler',setting('stats_ekskul','12')],
          ['trophy','Tahun Pengalaman',setting('stats_years','60').'+'],
        ];
        foreach ($qs as $q): ?>
        <div class="glass rounded-2xl p-5 flex items-center gap-4 hover:-translate-y-0.5 transition-transform">
          <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0" style="background:rgba(212,170,58,.12)">
            <i data-lucide="<?= $q[0] ?>" class="w-5 h-5 text-gold-400"></i>
          </div>
          <div>
            <div class="font-display text-2xl text-white font-light"><?= e($q[2]) ?></div>
            <div class="text-xs text-white/40"><?= $q[1] ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Visi -->
    <?php if (!empty($p['visi'])): ?>
    <div class="glass rounded-3xl p-8 lg:p-10 reveal">
      <div class="flex items-start gap-5">
        <div class="w-14 h-14 rounded-2xl flex items-center justify-center flex-shrink-0" style="background:rgba(212,170,58,.1);border:1px solid rgba(212,170,58,.25)">
          <i data-lucide="eye" class="w-6 h-6 text-gold-400"></i>
        </div>
        <div>
          <h2 class="font-display text-3xl text-white font-light mb-3">Visi</h2>
          <p class="text-white/60 font-light leading-relaxed"><?= nl2br(e($p['visi'])) ?></p>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Misi -->
    <?php if (!empty($p['misi'])): ?>
    <div class="glass rounded-3xl p-8 lg:p-10 reveal">
      <div class="flex items-start gap-5">
        <div class="w-14 h-14 rounded-2xl flex items-center justify-center flex-shrink-0" style="background:rgba(96,165,250,.1);border:1px solid rgba(96,165,250,.25)">
          <i data-lucide="target" class="w-6 h-6 text-blue-400"></i>
        </div>
        <div>
          <h2 class="font-display text-3xl text-white font-light mb-3">Misi</h2>
          <p class="text-white/60 font-light leading-relaxed"><?= nl2br(e($p['misi'])) ?></p>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Sejarah -->
    <?php if (!empty($p['sejarah'])): ?>
    <div class="glass rounded-3xl p-8 lg:p-10 reveal">
      <h2 class="font-display text-3xl text-white font-light mb-6 flex items-center gap-3">
        <i data-lucide="book-open" class="w-6 h-6 text-gold-400"></i> Sejarah Singkat
      </h2>
      <div class="prose-custom text-white/60 font-light leading-relaxed"><?= nl2br(e($p['sejarah'])) ?></div>
    </div>
    <?php endif; ?>

    <!-- Maps -->
    <?php if (!empty($p['maps_embed'])): ?>
    <div class="reveal">
      <h2 class="font-display text-2xl text-white font-light mb-5 flex items-center gap-3">
        <i data-lucide="map-pin" class="w-5 h-5 text-gold-400"></i> Lokasi Kami
      </h2>
      <div class="rounded-3xl overflow-hidden" style="height:380px">
        <?= $p['maps_embed'] ?>
      </div>
    </div>
    <?php endif; ?>

  </div>
</main>

<?php require_once ROOT_PATH . '../../includes/footer.php'; ?>
