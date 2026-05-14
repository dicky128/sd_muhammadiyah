<?php
require_once __DIR__ . '/includes/config.php';
try {
    $profile       = db()->query("SELECT * FROM school_profile LIMIT 1")->fetch() ?: [];
    $announcements = db()->query("SELECT * FROM announcements WHERE is_published=1 ORDER BY is_pinned DESC, published_at DESC LIMIT 4")->fetchAll();
    $facilities    = db()->query("SELECT * FROM facilities WHERE `condition`='baik' ORDER BY sort_order LIMIT 6")->fetchAll();
    $ekskuls       = db()->query("SELECT * FROM extracurricular WHERE is_active=1 ORDER BY sort_order LIMIT 6")->fetchAll();
} catch (Exception $e) { $profile=$announcements=$facilities=$ekskuls=[]; }

$siteName     = $profile['school_name'] ?? APP_NAME;
$heroTitle    = setting('hero_title','Sekolah Dasar Islam Unggulan');
$heroSub      = setting('hero_subtitle','Membentuk Generasi Cerdas Berakhlak Mulia');
$statStudents = setting('stats_students','380');
$statTeachers = setting('stats_teachers','24');
$statYears    = setting('stats_years','62');
$statEkskul   = setting('stats_ekskul','12');
$logoSrc      = !empty($profile['logo']) ? UPLOAD_URL.'logos/'.$profile['logo'] : null;

// Hero image: check settings table first, then school_profile table
$heroImageFile = setting('hero_image');
if ($heroImageFile) {
    // settings table stores just filename, saved to heroes/ subdir
    $heroImageUrl = UPLOAD_URL.'heroes/'.$heroImageFile;
} elseif (!empty($profile['hero_image'])) {
    // school_profile table also has hero_image column
    $heroImageUrl = UPLOAD_URL.'heroes/'.$profile['hero_image'];
} else {
    $heroImageUrl = null;
}
$catLabel     = ['penting'=>'section-label-pink','akademik'=>'section-label-sky','kegiatan'=>'section-label-gold','umum'=>'section-label-gold'];
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta name="description" content="<?= e($heroSub) ?>">
<title><?= e($siteName) ?> — Website Resmi</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config={theme:{extend:{fontFamily:{display:['"Playfair Display"','serif'],body:['"Plus Jakarta Sans"','sans-serif']},
colors:{pink:{50:'#fdf2f8',100:'#fce7f3',200:'#fbcfe8',300:'#f9a8d4',400:'#f472b6',500:'#ec4899',600:'#db2777'},
gold:{100:'#fef9e7',200:'#fef3c7',300:'#f0d898',400:'#e8c860',500:'#d4aa3a',600:'#b8921e'},
sky:{50:'#f0f9ff',100:'#e0f2fe',200:'#bae6fd',300:'#7dd3fc',400:'#38bdf8',500:'#0ea5e9'},
cream:'#fefcf9'}}}}
</script>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
<link rel="stylesheet" href="assets/css/light3d.css">
<style>
*{font-family:'Plus Jakarta Sans',sans-serif}
h1,h2,h3,.font-display{font-family:'Playfair Display',serif}
.dropdown-menu-light{opacity:0;visibility:hidden;transform:translateY(6px);transition:all .22s cubic-bezier(.16,1,.3,1)}
.dropdown:hover .dropdown-menu-light,.dropdown:focus-within .dropdown-menu-light{opacity:1;visibility:visible;transform:translateY(0)}
#mobile-menu{transform:translateX(100%);transition:transform .38s cubic-bezier(.16,1,.3,1)}
#mobile-menu.open{transform:translateX(0)}
.lift-card{transition:transform .35s cubic-bezier(.16,1,.3,1),box-shadow .35s ease}
.lift-card:hover{transform:translateY(-8px) scale(1.01);box-shadow:0 24px 60px rgba(244,114,182,.2),0 8px 20px rgba(0,0,0,.06)}
.ann-card-light{transition:transform .3s ease,background .3s ease,box-shadow .3s ease}
.ann-card-light:hover{transform:translateX(8px);background:rgba(253,242,248,.9)!important;box-shadow:4px 0 0 0 #f472b6}
[data-parallax]{will-change:transform}
@keyframes fadeUp{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:translateY(0)}}
@keyframes scroll-dot{0%,100%{transform:translateY(0);opacity:1}80%{transform:translateY(6px);opacity:0}}
</style>
</head>
<body class="light-mode bg-cream overflow-x-hidden">

<!-- Three.js canvas -->
<canvas id="three-canvas"></canvas>

<!-- ─── NAVBAR ─────────────────────────────────────────── -->
<nav id="navbar" class="navbar-light">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between h-16 md:h-20">
      <a href="index.php" class="flex items-center gap-3 group flex-shrink-0">
        <?php if($logoSrc): ?>
        <img src="<?=e($logoSrc)?>" alt="Logo" class="w-10 h-10 object-contain group-hover:scale-110 transition-transform">
        <?php else: ?>
        <div class="w-10 h-10 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform"
             style="background:linear-gradient(135deg,#fbcfe8,#fef3c7);border:1px solid rgba(244,114,182,.3)">
          <span style="font-family:'Playfair Display',serif;color:#be185d;font-weight:700;font-size:1.1rem">ص</span>
        </div>
        <?php endif; ?>
        <div><div class="text-sm font-semibold text-gray-800">SD Muhammadiyah 1</div>
        <div class="text-[10px] tracking-widest uppercase font-medium text-pink-400">Gentasari · Cilacap</div></div>
      </a>

      <div class="hidden lg:flex items-center gap-8">
        <a href="index.php" class="nav-link-light active">Beranda</a>

        <?php $dropdowns=[
          ['Profil',[['pages/profile/sekolah.php','building-2','Profil Sekolah'],['pages/profile/guru-staff.php','users','Guru &amp; Staff'],['pages/profile/siswa.php','graduation-cap','Siswa']]],
          ['Media',[['pages/media/galeri.php','image','Galeri Foto'],['pages/media/fasilitas.php','layout-grid','Fasilitas']]],
          ['Aktivitas',[['pages/aktivitas/ekskul.php','sparkles','Ekstrakurikuler'],['pages/aktivitas/pengumuman.php','bell','Pengumuman']]],
          ['Interaksi',[['pages/interaksi/pengaduan.php','message-square-warning','Pengaduan'],['pages/interaksi/kontak.php','mail','Kontak']]],
        ];
        foreach($dropdowns as [$label,$links]): ?>
        <div class="dropdown relative">
          <button class="nav-link-light flex items-center gap-1"><?=$label?> <i data-lucide="chevron-down" class="w-3 h-3 opacity-50 mt-0.5"></i></button>
          <div class="dropdown-menu-light absolute top-full left-1/2 -translate-x-1/2 mt-3 w-48">
            <div class="rounded-2xl overflow-hidden shadow-xl py-1" style="background:rgba(255,255,255,.94);backdrop-filter:blur(20px);border:1px solid rgba(244,114,182,.15)">
              <?php foreach($links as $l): ?>
              <a href="<?=$l[0]?>" class="flex items-center gap-3 px-4 py-3 text-sm text-gray-600 hover:text-pink-600 hover:bg-pink-50 transition-all">
                <i data-lucide="<?=$l[1]?>" class="w-4 h-4 text-pink-400"></i><?=$l[2]?>
              </a>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>

        <a href="pages/interaksi/pengaduan.php" class="btn-primary-light !px-5 !py-2.5 !text-xs ml-2">
          <i data-lucide="send" class="w-3.5 h-3.5"></i> Pengaduan
        </a>
      </div>

      <button id="hamburger" class="lg:hidden w-10 h-10 rounded-xl flex items-center justify-center"
              style="background:rgba(244,114,182,.08);border:1px solid rgba(244,114,182,.2)">
        <i data-lucide="menu" class="w-5 h-5 text-pink-500"></i>
      </button>
    </div>
  </div>
</nav>

<!-- Mobile Menu -->
<div id="mobile-menu" class="fixed inset-y-0 right-0 w-80 z-[60] flex flex-col shadow-2xl"
     style="background:rgba(255,255,255,.97);backdrop-filter:blur(24px);border-left:1px solid rgba(244,114,182,.15)">
  <div class="flex items-center justify-between px-6 py-5" style="border-bottom:1px solid rgba(244,114,182,.12)">
    <span style="font-family:'Playfair Display',serif;font-size:1.25rem;color:#be185d">Menu</span>
    <button id="close-menu" class="w-9 h-9 rounded-xl flex items-center justify-center" style="background:rgba(244,114,182,.1)">
      <i data-lucide="x" class="w-4 h-4 text-pink-500"></i></button>
  </div>
  <nav class="flex-1 overflow-y-auto px-4 py-6 space-y-1">
    <?php foreach([['index.php','home','Beranda'],['pages/profile/sekolah.php','building-2','Profil Sekolah'],['pages/profile/guru-staff.php','users','Guru & Staff'],['pages/profile/siswa.php','graduation-cap','Siswa'],['pages/media/galeri.php','image','Galeri Foto'],['pages/media/fasilitas.php','layout-grid','Fasilitas'],['pages/aktivitas/ekskul.php','sparkles','Ekstrakurikuler'],['pages/aktivitas/pengumuman.php','bell','Pengumuman'],['pages/interaksi/pengaduan.php','message-square-warning','Pengaduan'],['pages/interaksi/kontak.php','mail','Kontak Kami']] as $l): ?>
    <a href="<?=$l[0]?>" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm text-gray-600 hover:text-pink-600 hover:bg-pink-50 transition-all font-medium">
      <i data-lucide="<?=$l[1]?>" class="w-4 h-4 text-pink-400"></i><?=$l[2]?>
    </a>
    <?php endforeach; ?>
  </nav>
</div>
<div id="menu-overlay" class="fixed inset-0 bg-black/20 z-[55] hidden backdrop-blur-sm"></div>

<div class="page-wrapper">

<!-- ─── HERO ─────────────────────────────────────────────── -->
<?php
// Build hero background style
if ($heroImageUrl) {
    // Image uploaded: show with light gradient overlay so text stays readable
    $heroStyle = "background: linear-gradient(135deg, rgba(253,242,248,0.82) 0%, rgba(254,249,231,0.75) 50%, rgba(240,249,255,0.82) 100%), url('" . e($heroImageUrl) . "') center/cover no-repeat fixed; min-height:100svh";
} else {
    // No image: pure gradient fallback
    $heroStyle = "background: linear-gradient(135deg, #fdf2f8 0%, #fef9e7 50%, #f0f9ff 100%); min-height:100svh";
}
?>
<section class="relative" id="hero" style="<?= $heroStyle ?>">
  <div class="grid-lines"></div>
  <div class="orb orb-pink w-96 h-96 orb-parallax animate-float-y" data-speed="0.12" style="top:-10%;right:-5%"></div>
  <div class="orb orb-gold w-64 h-64 orb-parallax animate-float-diagonal" data-speed="0.08" style="bottom:15%;left:-5%;animation-delay:1.5s"></div>
  <div class="orb orb-sky w-72 h-72 orb-parallax animate-float-y" data-speed="0.18" style="top:45%;right:22%;opacity:.5;animation-delay:3s"></div>

  <!-- Floating geo shapes -->
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

      <!-- Text -->
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
            Kenali Kami <i data-lucide="arrow-right" class="w-4 h-4 transition-transform group-hover:translate-x-1"></i>
          </a>
          <a href="pages/interaksi/kontak.php" class="btn-outline-light">
            <i data-lucide="phone" class="w-4 h-4"></i> Hubungi Kami
          </a>
        </div>
      </div>

      <!-- 3D Cards Stack -->
      <div class="relative hidden lg:block" style="height:440px;opacity:0;animation:fadeUp 1s .55s ease forwards">
        <!-- back -->
        <div class="tilt-card absolute" style="width:280px;top:60px;left:90px;transform:rotate(-7deg)">
          <div class="tilt-inner glass-card glass-card-sky p-5 rounded-2xl">
            <div class="tilt-shine"></div>
            <div class="icon-badge icon-badge-sky mb-3"><i data-lucide="graduation-cap" class="w-5 h-5"></i></div>
            <p class="text-sky-700 font-semibold text-sm">Program Unggulan</p>
            <p class="text-gray-400 text-xs mt-1">Kurikulum integratif islami</p>
          </div>
        </div>
        <!-- mid -->
        <div class="tilt-card absolute" style="width:300px;top:100px;left:10px;transform:rotate(4deg)">
          <div class="tilt-inner glass-card glass-card-gold p-5 rounded-2xl">
            <div class="tilt-shine"></div>
            <div class="icon-badge icon-badge-gold mb-3"><i data-lucide="trophy" class="w-5 h-5"></i></div>
            <p class="text-yellow-700 font-semibold text-sm">Prestasi Nasional</p>
            <p class="text-gray-400 text-xs mt-1">Juara olimpiade sains &amp; seni</p>
          </div>
        </div>
        <!-- front -->
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
        <!-- floating labels -->
        <div class="absolute animate-float-y" style="top:0;right:0;animation-delay:.8s">
          <div class="glass-card px-3 py-2 rounded-full text-xs font-bold text-pink-600" style="background:rgba(253,242,248,.95);border-color:rgba(244,114,182,.25)">✦ Akreditasi A</div>
        </div>
        <div class="absolute animate-float-diagonal" style="bottom:50px;right:5px;animation-delay:2s">
          <div class="glass-card px-3 py-2 rounded-full text-xs font-bold text-sky-600" style="background:rgba(240,249,255,.95);border-color:rgba(56,189,248,.25)">⭐ Pilihan Keluarga</div>
        </div>
      </div>

    </div>
  </div>

  <!-- Stats bar -->
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

  <!-- Scroll hint -->
  <div class="absolute bottom-28 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 z-10" style="opacity:.4;animation:fadeUp 1s 1s ease forwards">
    <span class="text-[10px] tracking-widest uppercase text-gray-400">Scroll</span>
    <div class="w-5 h-8 rounded-full border-2 border-pink-300 flex justify-center pt-1.5">
      <div class="w-1 h-2 bg-pink-400 rounded-full" style="animation:scroll-dot 2s ease-in-out infinite"></div>
    </div>
  </div>
</section>

<!-- ─── VISI MISI ──────────────────────────────────────── -->
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

<!-- ─── PENGUMUMAN ────────────────────────────────────── -->
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

<!-- ─── FASILITAS ─────────────────────────────────────── -->
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

<!-- ─── EKSKUL ────────────────────────────────────────── -->
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

<!-- ─── CTA BAND ──────────────────────────────────────── -->
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

<!-- ─── FOOTER ────────────────────────────────────────── -->
<footer class="footer-light">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-10">
      <div class="lg:col-span-2">
        <div class="flex items-center gap-3 mb-5">
          <div class="w-10 h-10 rounded-2xl flex items-center justify-center" style="background:linear-gradient(135deg,#fbcfe8,#fef3c7);border:1px solid rgba(244,114,182,.3)">
            <span style="font-family:'Playfair Display',serif;color:#be185d;font-weight:700;font-size:1rem">ص</span>
          </div>
          <div><div class="text-sm font-semibold text-gray-700">SD Muhammadiyah 1</div>
          <div class="text-[10px] tracking-widest uppercase font-medium text-pink-400">Gentasari · Cilacap</div></div>
        </div>
        <p class="text-gray-400 text-sm font-light leading-relaxed max-w-xs">Menjadi sekolah Islam unggulan yang membentuk generasi cerdas berkarakter dan berakhlak mulia.</p>
        <?php
        $socials = [
          'instagram' => $profile['instagram'] ?? '',
          'facebook'  => $profile['facebook']  ?? '',
          'youtube'   => $profile['youtube']   ?? '',
        ];
        $svgIcons = [
          'instagram' => '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>',
          'facebook'  => '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>',
          'youtube'   => '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46a2.78 2.78 0 0 0-1.95 1.96A29 29 0 0 0 1 12a29 29 0 0 0 .46 5.58A2.78 2.78 0 0 0 3.41 19.6C5.12 20 12 20 12 20s6.88 0 8.59-.46a2.78 2.78 0 0 0 1.95-1.95A29 29 0 0 0 23 12a29 29 0 0 0-.46-5.58z"/><polygon points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02"/></svg>',
        ];
        $hasSocial = array_filter($socials);
        if ($hasSocial): ?>
        <div class="flex gap-3 mt-5">
          <?php foreach ($socials as $key => $url): if (!$url) continue; ?>
          <a href="<?=e($url)?>" target="_blank" rel="noopener noreferrer"
             class="w-9 h-9 rounded-xl flex items-center justify-center hover:scale-110 transition-all duration-200 text-pink-400 hover:text-pink-600 hover:bg-pink-100"
             style="background:rgba(244,114,182,.1);border:1px solid rgba(244,114,182,.2)"
             aria-label="<?=ucfirst($key)?>">
            <?= $svgIcons[$key] ?>
          </a>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
      <div>
        <h4 class="text-xs tracking-widest uppercase font-semibold text-gray-400 mb-5">Navigasi</h4>
        <ul class="space-y-3">
          <?php foreach([['pages/profile/sekolah.php','Profil Sekolah'],['pages/profile/guru-staff.php','Guru & Staff'],['pages/media/galeri.php','Galeri Foto'],['pages/aktivitas/pengumuman.php','Pengumuman'],['pages/interaksi/kontak.php','Kontak Kami']] as $l): ?>
          <li><a href="<?=$l[0]?>" class="text-gray-400 hover:text-pink-500 transition-colors text-sm flex items-center gap-2">
            <i data-lucide="chevron-right" class="w-3 h-3"></i><?=$l[1]?>
          </a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div>
        <h4 class="text-xs tracking-widest uppercase font-semibold text-gray-400 mb-5">Kontak</h4>
        <ul class="space-y-4">
          <?php if(!empty($profile['address'])): ?><li class="flex gap-3 text-sm text-gray-400"><i data-lucide="map-pin" class="w-4 h-4 text-pink-400 flex-shrink-0 mt-0.5"></i><?=e($profile['address'].', '.($profile['district']??'').', '.($profile['city']??''))?></li><?php endif; ?>
          <?php if(!empty($profile['phone'])): ?><li class="flex gap-3 text-sm text-gray-400"><i data-lucide="phone" class="w-4 h-4 text-pink-400 flex-shrink-0"></i><?=e($profile['phone'])?></li><?php endif; ?>
          <?php if(!empty($profile['email'])): ?><li class="flex gap-3 text-sm text-gray-400"><i data-lucide="mail" class="w-4 h-4 text-pink-400 flex-shrink-0"></i><?=e($profile['email'])?></li><?php endif; ?>
        </ul>
      </div>
    </div>
    <div class="mt-12 pt-8 flex flex-col sm:flex-row items-center justify-between gap-4" style="border-top:1px solid rgba(244,114,182,.12)">
      <p class="text-gray-300 text-xs">&copy; <?=date('Y')?> <?=e($siteName)?>. All rights reserved.</p>
      <a href="admin/login.php" class="text-gray-300 hover:text-gray-500 text-xs flex items-center gap-1.5 transition-colors">
        <i data-lucide="lock" class="w-3 h-3"></i> Admin Panel
      </a>
    </div>
  </div>
</footer>

</div><!-- /page-wrapper -->

<script>
// Mobile menu
const hamburger=document.getElementById('hamburger'),closeBtn=document.getElementById('close-menu'),mobileMenu=document.getElementById('mobile-menu'),overlay=document.getElementById('menu-overlay');
function openMenu(){mobileMenu.classList.add('open');overlay.classList.remove('hidden');document.body.style.overflow='hidden'}
function closeMenu(){mobileMenu.classList.remove('open');overlay.classList.add('hidden');document.body.style.overflow=''}
hamburger.addEventListener('click',openMenu);closeBtn.addEventListener('click',closeMenu);overlay.addEventListener('click',closeMenu);
document.addEventListener('keydown',e=>{if(e.key==='Escape')closeMenu()});
// Navbar scroll
window.addEventListener('scroll',()=>document.getElementById('navbar').classList.toggle('scrolled',window.scrollY>40),{passive:true});
</script>
<script src="assets/js/animations.js" defer></script>
<script src="assets/js/scroll3d.js" defer></script>
<script>document.addEventListener('DOMContentLoaded',()=>lucide.createIcons())</script>
</body>
</html>