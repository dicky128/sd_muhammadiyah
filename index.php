<?php
require_once __DIR__ . '/includes/config.php';

// 1. Ambil data database
try {
    $profile       = db()->query("SELECT * FROM school_profile LIMIT 1")->fetch() ?: [];
    
    // PERBAIKAN: Gunakan fetchColumn() untuk langsung mengambil angka (total)
    $siswa         = db()->query("SELECT SUM(`count`) FROM student_stats")->fetchColumn() ?: 0;
    
    // PERBAIKAN: Hitung jumlah guru dari tabel teachers yang statusnya aktif
    $guru          = db()->query("SELECT COUNT(*) FROM teachers WHERE is_active = 1")->fetchColumn() ?: 0;
    
    $announcements = db()->query("SELECT * FROM announcements WHERE is_published=1 ORDER BY is_pinned DESC, published_at DESC LIMIT 4")->fetchAll();
    $facilities    = db()->query("SELECT * FROM facilities WHERE `condition`='baik' ORDER BY sort_order LIMIT 6")->fetchAll();
    $ekskuls       = db()->query("SELECT * FROM extracurricular WHERE is_active=1 ORDER BY sort_order LIMIT 6")->fetchAll();
} catch (Exception $e) { 
    $profile = $announcements = $facilities = $ekskuls = []; 
    $siswa = $guru = 0; // Pastikan nilai default angka diatur jika database error
}

// =========================================================================
// 2. SET VARIABEL HEADER (SANGAT PENTING AGAR LAYOUT TIDAK RUSAK)
// =========================================================================
$activePage   = 'beranda'; // Ini akan memberitahu header.php untuk menghapus padding
$pageTitle    = 'Beranda';
// =========================================================================

$siteName     = $profile['school_name'] ?? APP_NAME;
$heroTitle    = setting('hero_title','Sekolah Dasar Islam Unggulan');
$heroSub      = setting('hero_subtitle','Membentuk Generasi Cerdas Berakhlak Mulia');
$TeksTombol1 = setting('hero_cta_primary','Tentang Kami');
$TeksTombol2 = setting('hero_cta_secondary','Kontak Kami');

// PERBAIKAN: Langsung masukkan angka dari variabel yang sudah di-query di atas
$statStudents = $siswa;
$statTeachers = $guru;

// Mengurangi Tahun Saat Ini dengan Tahun Berdiri yang sudah ada di variabel $profile
$statYears = !empty($profile['tahun_berdiri']) ? (date('Y') - $profile['tahun_berdiri']) : 0;
$statEkskul   = $ekskuls ? count($ekskuls) : 0;
$logoSrc      = !empty($profile['logo']) ? UPLOAD_URL.'logos/'.$profile['logo'] : null;

// 3. Logika Hero Image
$heroSetting = setting('hero_image');
$heroProfile = $profile['hero_image'] ?? null;

if (!empty($heroProfile)) {
    $heroImageUrl = UPLOAD_URL . 'heroes/' . $heroProfile;
} elseif (!empty($heroSetting) && $heroSetting !== 'assets/images/hero-default.jpg') {
    $heroImageUrl = UPLOAD_URL . 'heroes/' . $heroSetting;
} else {
    $heroImageUrl = UPLOAD_URL . 'heroes/img_6a0412888b3ab7.58594022.jpg';
}

// Rakit style dengan overlay yang transparan agar gambar terlihat
$heroStyle = "background: linear-gradient(135deg, 
    rgba(253,242,248,0.4) 0%, 
    rgba(254,249,231,0.4) 50%, 
    rgba(240,249,255,0.4) 100%), 
    url('" . $heroImageUrl . "') center/cover no-repeat fixed; min-height:100svh";

// 4. PANGGIL HEADER
require_once __DIR__ . '/includes/header.php';

$catLabel = ['penting'=>'section-label-pink','akademik'=>'section-label-sky','kegiatan'=>'section-label-gold','umum'=>'section-label-gold'];
?>

<section class="relative" id="hero" style="<?= $heroStyle ?>">
  <div class="grid-lines"></div>
  <div class="orb orb-pink w-96 h-96 orb-parallax animate-float-y" data-speed="0.12" style="top:-10%;right:-5%"></div>
  <div class="orb orb-gold w-64 h-64 orb-parallax animate-float-diagonal" data-speed="0.08" style="bottom:15%;left:-5%;animation-delay:1.5s"></div>
  <div class="orb orb-sky w-72 h-72 orb-parallax animate-float-y" data-speed="0.18" style="top:45%;right:22%;opacity:.5;animation-delay:3s"></div>

  <div class="absolute pointer-events-none" data-parallax="0.14" style="top:22%;left:7%">
    <div class="w-12 h-12 rounded-xl border-2 border-pink-300 rotate-12 animate-float-y opacity-40" style="animation-delay:.5s"></div>
  </div>
  <div class="absolute pointer-events-none" data-parallax="0.24" style="top:65%;right:7%">
    <div class="w-8 h-8 rounded-full border-2 border-gold-400 animate-float-diagonal opacity-40" style="animation-delay:1s"></div>
  </div>
  <div class="absolute pointer-events-none" data-parallax="0.19" style="bottom:28%;left:18%">
    <div class="w-6 h-6 rotate-45 border-2 border-sky-400 animate-float-y opacity-35" style="animation-delay:2s"></div>
  </div>

  <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full pt-28 pb-36"
       style="min-height:100svh;display:flex;align-items:center">
    <div class="grid lg:grid-cols-2 gap-16 items-center w-full">

      <div>
        <div class="flex items-center gap-3 mb-6" style="opacity:0;animation:fadeUp .8s .1s ease forwards">
          <span class="section-label section-label-pink"><i data-lucide="star" class="w-3 h-3"></i> Akreditasi A · Est. <?=e($profile['tahun_berdiri']??'1962')?></span>
        </div>
        <h1 class="font-display font-bold text-gray-800 mb-2" style="font-size:clamp(2.4rem,6vw,4.8rem);line-height:1.05;opacity:0;animation:fadeUp .8s .2s ease forwards">
          <?=e($heroTitle)?>
        </h1>
        <h1 class="font-display font-bold mb-8" style="font-size:clamp(2.4rem,6vw,4.8rem);line-height:1.05;opacity:0;animation:fadeUp .8s .3s ease forwards">
          <em class="text-gradient-pink-gold not-italic">Terpercaya.</em>
        </h1>
        <p class="text-gray-500 text-lg font-light leading-relaxed max-w-lg mb-10"
           style="opacity:0;animation:fadeUp .8s .4s ease forwards">
          <?=e($heroSub)?> — di bawah naungan <span class="text-gray-700 font-semibold">Persyarikatan Muhammadiyah</span>.
        </p>
        <div class="flex flex-wrap gap-4" style="opacity:0;animation:fadeUp .8s .5s ease forwards">
          <a href="pages/profile/sekolah.php" class="btn-primary-light group">
            <?= e($TeksTombol1) ?> <i data-lucide="arrow-right" class="w-4 h-4 transition-transform group-hover:translate-x-1"></i>
          </a>
          <a href="pages/interaksi/kontak.php" class="btn-outline-light">
            <i data-lucide="phone" class="w-4 h-4"></i> <?= e($TeksTombol2) ?>
          </a>
        </div>
      </div>

      <div class="relative hidden lg:block" style="height:440px;opacity:0;animation:fadeUp 1s .55s ease forwards">
        <div class="tilt-card absolute" style="width:280px;top:60px;left:90px;transform:rotate(-7deg)">
          <div class="tilt-inner glass-card glass-card-sky p-5 rounded-2xl">
            <div class="tilt-shine"></div>
            <div class="icon-badge icon-badge-sky mb-3"><i data-lucide="graduation-cap" class="w-5 h-5"></i></div>
            <p class="text-sky-700 font-semibold text-sm">Program Unggulan</p>
            <p class="text-gray-400 text-xs mt-1">Kurikulum integratif islami</p>
          </div>
        </div>
        <div class="tilt-card absolute" style="width:300px;top:100px;left:10px;transform:rotate(4deg)">
          <div class="tilt-inner glass-card glass-card-gold p-5 rounded-2xl">
            <div class="tilt-shine"></div>
            <div class="icon-badge icon-badge-gold mb-3"><i data-lucide="trophy" class="w-5 h-5"></i></div>
            <p class="text-yellow-700 font-semibold text-sm">Prestasi Nasional</p>
            <p class="text-gray-400 text-xs mt-1">Juara olimpiade sains &amp; seni</p>
          </div>
        </div>
        <div class="tilt-card absolute" style="width:320px;top:5px;left:45px">
          <div class="tilt-inner glass-card p-6 rounded-2xl" style="box-shadow:0 20px 60px rgba(244,114,182,.28)">
            <div class="tilt-shine"></div>
            <div class="icon-badge icon-badge-pink mb-4"><i data-lucide="heart" class="w-5 h-5"></i></div>
            <p class="font-display text-xl font-semibold text-gray-800 mb-1"><?=e($siteName)?></p>
            <p class="text-gray-400 text-xs mb-4">Gentasari, Kroya, Cilacap</p>
            <div class="cyber-line mb-3"></div>
            <div class="flex items-center justify-between">
              <span class="text-xs text-gray-400">Siswa Aktif</span>
              <span class="font-display font-bold text-2xl text-gradient-pink-gold"><?=e($statStudents)?>+</span>
            </div>
          </div>
        </div>
        <div class="absolute animate-float-y" style="top:0;right:0;animation-delay:.8s">
          <div class="glass-card px-3 py-2 rounded-full text-xs font-bold text-pink-600" style="background:rgba(253,242,248,.95);border-color:rgba(244,114,182,.25)">✦ Akreditasi A</div>
        </div>
        <div class="absolute animate-float-diagonal" style="bottom:50px;right:5px;animation-delay:2s">
          <div class="glass-card px-3 py-2 rounded-full text-xs font-bold text-sky-600" style="background:rgba(240,249,255,.95);border-color:rgba(56,189,248,.25)">⭐ Pilihan Keluarga</div>
        </div>
      </div>

    </div>
  </div>

  <div class="absolute bottom-0 left-0 right-0 z-10"
       style="background:rgba(255,255,255,.75);backdrop-filter:blur(20px);border-top:1px solid rgba(244,114,182,.12)">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-2 lg:grid-cols-4 divide-y lg:divide-y-0 lg:divide-x divide-pink-100">
        <?php foreach([
          ['users',         $statStudents,'Siswa Aktif',    '+'],
          ['graduation-cap',$statTeachers,'Tenaga Pendidik',''],
          ['calendar',      $statYears,   'Tahun Berdiri',  ' Thn'],
          ['sparkles',      $statEkskul,  'Ekstrakurikuler',''],
        ] as $s): ?>
        <div class="flex items-center gap-4 px-6 py-5 group cursor-default hover:bg-pink-50/60 transition-colors">
          <div class="icon-badge icon-badge-pink group-hover:scale-110 transition-transform flex-shrink-0">
            <i data-lucide="<?=$s[0]?>" class="w-5 h-5"></i>
          </div>
          <div>
            <div class="font-display font-bold text-2xl text-gray-800 leading-none">
              <span data-count="<?=e($s[1])?>">0</span><span class="text-pink-400"><?=e($s[3])?></span>
            </div>
            <div class="text-gray-400 text-xs mt-1"><?=e($s[2])?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="absolute bottom-28 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 z-10" style="opacity:.4;animation:fadeUp 1s 1s ease forwards">
    <span class="text-[10px] tracking-widest uppercase text-gray-400">Scroll</span>
    <div class="w-5 h-8 rounded-full border-2 border-pink-300 flex justify-center pt-1.5">
      <div class="w-1 h-2 bg-pink-400 rounded-full" style="animation:scroll-dot 2s ease-in-out infinite"></div>
    </div>
  </div>
</section>

<?php if (!empty($profile['visi']) || !empty($profile['misi'])): ?>
<section class="depth-section py-28 relative overflow-hidden" data-burst
         style="background:linear-gradient(180deg,#fefcf9 0%,#fdf2f8 100%)">
  <div class="orb orb-pink w-72 h-72 orb-parallax opacity-30" data-speed="0.06" style="top:-5%;right:5%"></div>
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
    <div class="text-center mb-16">
      <div class="flex items-center justify-center gap-4 mb-5 reveal-fade">
        <div class="ornament-line"></div>
        <span class="section-label section-label-pink">Landasan Kami</span>
        <div class="ornament-line"></div>
      </div>
      <h2 class="font-display font-bold text-gray-800 reveal-heading" style="font-size:clamp(2rem,4vw,3.5rem)">
        Visi &amp; <em class="text-gradient-pink-gold not-italic">Misi</em>
      </h2>
    </div>
    <div class="grid lg:grid-cols-2 gap-8 stagger-grid">
      <?php foreach([
        ['visi','eye','Visi','glass-card-pink','icon-badge-pink','#be185d'],
        ['misi','target','Misi','glass-card-sky','icon-badge-sky','#0369a1'],
      ] as $vm): if(empty($profile[$vm[0]])) continue; ?>
      <div class="tilt-card glass-card <?=$vm[3]?> rounded-3xl p-8 lg:p-10">
        <div class="tilt-inner">
          <div class="tilt-shine"></div>
          <div class="flex items-start gap-5">
            <div class="icon-badge <?=$vm[4]?> flex-shrink-0 mt-1"><i data-lucide="<?=$vm[1]?>" class="w-5 h-5"></i></div>
            <div>
              <h3 class="font-display text-2xl font-semibold mb-3" style="color:<?=$vm[5]?>"><?=$vm[2]?></h3>
              <p class="text-gray-600 font-light leading-relaxed text-sm"><?=nl2br(e($profile[$vm[0]]))?></p>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="depth-section py-28 relative" style="background:#fefcf9">
  <div class="orb orb-gold w-60 h-60 orb-parallax opacity-25" data-speed="0.1" style="bottom:0;left:5%"></div>
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
    <div class="flex items-end justify-between mb-14">
      <div>
        <span class="section-label section-label-gold mb-3 inline-flex reveal-fade">Info Terkini</span>
        <h2 class="font-display font-bold text-gray-800 reveal-heading" style="font-size:clamp(1.8rem,3.5vw,3rem)">Pengumuman</h2>
      </div>
      <a href="pages/aktivitas/pengumuman.php" class="hidden sm:flex items-center gap-2 text-sm font-semibold text-pink-500 hover:text-pink-600 transition-colors group reveal-fade">
        Lihat Semua <i data-lucide="arrow-right" class="w-4 h-4 transition-transform group-hover:translate-x-1"></i>
      </a>
    </div>
    <?php if(empty($announcements)): ?>
    <div class="glass-card rounded-3xl p-16 text-center">
      <i data-lucide="bell-off" class="w-10 h-10 mx-auto mb-3 text-pink-200"></i>
      <p class="text-gray-400 text-sm">Belum ada pengumuman saat ini.</p>
    </div>
    <?php else: ?>
    <div class="space-y-4 stagger-grid">
      <?php foreach($announcements as $i=>$ann):
        $isNew=isNew($ann['published_at']);
        $cc=$catLabel[$ann['category']]??'section-label-gold';
      ?>
      <a href="pages/aktivitas/pengumuman.php?id=<?=$ann['id']?>"
         class="ann-card-light glass-card block rounded-2xl p-5 reveal-fade" style="animation-delay:<?=$i*.08?>s">
        <div class="flex items-center gap-5">
          <div class="font-display text-3xl text-pink-100 font-bold w-10 text-center flex-shrink-0"><?=str_pad($i+1,2,'0',STR_PAD_LEFT)?></div>
          <div class="w-px self-stretch" style="background:linear-gradient(180deg,#f9a8d4,#e8c860)"></div>
          <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-1.5">
              <span class="section-label <?=$cc?>"><?=ucfirst(e($ann['category']))?></span>
              <?php if($isNew): ?><span class="badge-new-light">✦ Terbaru</span><?php endif; ?>
              <?php if($ann['is_pinned']): ?><span class="section-label section-label-gold">📌 Pin</span><?php endif; ?>
            </div>
            <h3 class="text-gray-700 font-semibold text-sm line-clamp-1"><?=e($ann['title'])?></h3>
          </div>
          <div class="flex-shrink-0 text-right">
            <div class="text-gray-400 text-xs"><?=date('d M Y',strtotime($ann['published_at']))?></div>
            <i data-lucide="chevron-right" class="w-4 h-4 text-pink-300 ml-auto mt-1"></i>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php if(!empty($facilities)): ?>
<section class="depth-section py-28 relative overflow-hidden"
         style="background:linear-gradient(135deg,#fdf2f8 0%,#fef9e7 50%,#f0f9ff 100%)">
  <div class="orb orb-sky w-64 h-64 orb-parallax opacity-30" data-speed="0.15" style="top:10%;right:5%"></div>
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
    <div class="text-center mb-16">
      <span class="section-label section-label-sky mb-4 inline-flex reveal-fade">Sarana &amp; Prasarana</span>
      <h2 class="font-display font-bold text-gray-800 reveal-heading" style="font-size:clamp(1.8rem,3.5vw,3rem)">
        Fasilitas <em class="text-gradient-sky-pink not-italic">Unggulan</em>
      </h2>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 stagger-grid">
      <?php $fColors=['baik'=>'icon-badge-sky','cukup'=>'icon-badge-gold'];
      foreach($facilities as $i=>$f): ?>
      <div class="tilt-card glass-card rounded-2xl p-5 text-center" style="animation-delay:<?=$i*.06?>s">
        <div class="tilt-inner">
          <div class="tilt-shine"></div>
          <div class="<?=$fColors[$f['condition']]??'icon-badge-pink'?> icon-badge mx-auto mb-3">
            <i data-lucide="<?=e($f['icon']??'square')?>" class="w-5 h-5"></i>
          </div>
          <div class="font-display font-bold text-2xl text-gray-800"><?=e($f['count'])?></div>
          <div class="text-gray-400 text-xs mt-1 leading-tight"><?=e($f['name'])?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-10 reveal-fade">
      <a href="pages/media/fasilitas.php" class="btn-outline-light">
        <i data-lucide="arrow-right" class="w-4 h-4"></i> Lihat Semua Fasilitas
      </a>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if(!empty($ekskuls)): ?>
<section class="depth-section py-28 relative" style="background:#fefcf9">
  <div class="orb orb-pink w-72 h-72 orb-parallax opacity-25" data-speed="0.08" style="top:20%;left:-5%"></div>
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
    <div class="flex items-end justify-between mb-14">
      <div>
        <span class="section-label section-label-pink mb-3 inline-flex reveal-fade">Pengembangan Diri</span>
        <h2 class="font-display font-bold text-gray-800 reveal-heading" style="font-size:clamp(1.8rem,3.5vw,3rem)">
          Ekstra<em class="text-gradient-pink-gold not-italic">kurikuler</em>
        </h2>
      </div>
      <a href="pages/aktivitas/ekskul.php" class="hidden sm:flex items-center gap-2 text-sm font-semibold text-pink-500 hover:text-pink-600 transition-colors group reveal-fade">
        Lihat Semua <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
      </a>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 stagger-grid">
      <?php $eColors=['icon-badge-pink','icon-badge-gold','icon-badge-sky'];
      foreach($ekskuls as $i=>$e): ?>
      <div class="tilt-card glass-card rounded-2xl overflow-hidden lift-card reveal-fade" style="animation-delay:<?=$i*.07?>s">
        <div class="tilt-inner h-full">
          <div class="tilt-shine"></div>
          <?php if(!empty($e['image'])): ?>
          <div class="h-40 overflow-hidden"><img src="<?=UPLOAD_URL?>ekskul/<?=htmlspecialchars($e['image'])?>" loading="lazy" class="w-full h-full object-cover hover:scale-105 transition-transform duration-500" alt=""></div>
          <?php endif; ?>
          <div class="p-5">
            <div class="flex items-start gap-3 mb-2">
              <div class="<?=$eColors[$i%3]?> icon-badge flex-shrink-0">
                <i data-lucide="<?=e($e['icon']??'star')?>" class="w-4 h-4"></i>
              </div>
              <div>
                <h3 class="font-display font-semibold text-gray-800"><?=e($e['name'])?></h3>
                <?php if(!empty($e['coach'])): ?><p class="text-gray-400 text-xs">Pembina: <?=e($e['coach'])?></p><?php endif; ?>
              </div>
            </div>
            <?php if(!empty($e['schedule'])): ?>
            <div class="flex items-center gap-1.5 mt-3 pt-3 border-t border-pink-100">
              <i data-lucide="clock" class="w-3 h-3 text-pink-300"></i>
              <span class="text-xs text-gray-400"><?=e($e['schedule'])?></span>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="py-24 relative overflow-hidden reveal-fade"
         style="background:linear-gradient(135deg,#fce7f3 0%,#fef9e7 50%,#e0f2fe 100%)">
  <div class="cyber-line absolute top-0 left-0 right-0"></div>
  <div class="grid-lines opacity-60"></div>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
    <span class="section-label section-label-pink mb-6 inline-flex">Bergabung Bersama Kami</span>
    <h2 class="font-display font-bold text-gray-800 mb-5" style="font-size:clamp(1.8rem,4vw,3.2rem);line-height:1.15">
      Bersama kami, buah hati Anda akan tumbuh menjadi
      <em class="text-gradient-pink-gold not-italic"> yang terbaik.</em>
    </h2>
    <p class="text-gray-500 font-light mb-10 max-w-2xl mx-auto">Hubungi kami untuk informasi penerimaan siswa baru atau kunjungi sekolah kami secara langsung.</p>
    <div class="flex flex-wrap justify-center gap-4">
      <a href="pages/interaksi/kontak.php" class="btn-primary-light">
        <i data-lucide="phone-call" class="w-4 h-4"></i> Hubungi Sekarang
      </a>
      <a href="pages/aktivitas/pengumuman.php" class="btn-outline-light">
        <i data-lucide="newspaper" class="w-4 h-4"></i> Info Terkini
      </a>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>