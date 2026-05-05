<?php
// includes/header.php — shared navbar + head for all public pages
// Usage: require_once ROOT_PATH . '/includes/header.php';
// Before including, set: $pageTitle, $pageDesc (optional), $activePage (optional)

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}
$pageTitle = $pageTitle ?? APP_NAME;
$pageDesc  = $pageDesc  ?? 'Website resmi ' . APP_NAME;
$activePage = $activePage ?? '';

try {
    $profileData = db()->query("SELECT * FROM school_profile LIMIT 1")->fetch() ?: [];
    $logoSrc = !empty($profileData['logo']) ? UPLOAD_URL . $profileData['logo'] : null;
    $siteName = $profileData['school_name'] ?? APP_NAME;
} catch (Exception $e) {
    $profileData = []; $logoSrc = null; $siteName = APP_NAME;
}
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?> — <?= e($siteName) ?></title>
  <meta name="description" content="<?= e($pageDesc) ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: { extend: {
        fontFamily: { display:['"Cormorant Garamond"','serif'], body:['"DM Sans"','sans-serif'] },
        colors: { gold: { 300:'#f0d898',400:'#e8c860',500:'#d4aa3a',600:'#b8921e' } }
      }}
    }
  </script>
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
  <style>
    *{font-family:'DM Sans',sans-serif} h1,h2,h3,.font-display{font-family:'Cormorant Garamond',serif}
    ::-webkit-scrollbar{width:5px}::-webkit-scrollbar-track{background:#0a0a0a}::-webkit-scrollbar-thumb{background:#d4aa3a;border-radius:99px}
    .glass{background:rgba(255,255,255,.07);backdrop-filter:blur(16px) saturate(1.8);-webkit-backdrop-filter:blur(16px) saturate(1.8);border:1px solid rgba(255,255,255,.13)}
    .glass-dark{background:rgba(0,0,0,.4);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.08)}
    .glass-strong{background:rgba(255,255,255,.14);backdrop-filter:blur(24px) saturate(2);-webkit-backdrop-filter:blur(24px) saturate(2);border:1px solid rgba(255,255,255,.22)}
    .text-gold-shimmer{background:linear-gradient(135deg,#f0d898,#d4aa3a,#f0d898,#b8921e);background-size:200%;-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;animation:shimmer 4s linear infinite}
    @keyframes shimmer{to{background-position:-200% 0}}
    .nav-link{position:relative;font-size:.78rem;letter-spacing:.1em;font-weight:400;text-transform:uppercase;color:rgba(255,255,255,.82);transition:color .25s;padding:.25rem 0}
    .nav-link::after{content:'';position:absolute;bottom:-2px;left:0;right:0;height:1px;background:#d4aa3a;transform:scaleX(0);transform-origin:center;transition:transform .3s cubic-bezier(.16,1,.3,1)}
    .nav-link:hover{color:#fff}.nav-link:hover::after,.nav-link.active::after{transform:scaleX(1)}.nav-link.active{color:#f0d898}
    .dropdown-menu{opacity:0;visibility:hidden;transform:translateY(6px);transition:all .22s cubic-bezier(.16,1,.3,1)}
    .dropdown:hover .dropdown-menu,.dropdown:focus-within .dropdown-menu{opacity:1;visibility:visible;transform:translateY(0)}
    #mobile-menu{transform:translateX(100%);transition:transform .38s cubic-bezier(.16,1,.3,1)}
    #mobile-menu.open{transform:translateX(0)}
    .reveal{opacity:0;transform:translateY(28px);transition:opacity .7s ease,transform .7s ease}
    .reveal.visible{opacity:1;transform:translateY(0)}
    .page-hero{background:linear-gradient(135deg,rgba(0,0,0,.82) 0%,rgba(0,0,0,.55) 60%,rgba(0,0,0,.75) 100%),url('https://images.unsplash.com/photo-1580582932707-520aed937b7b?w=1920&q=70') center/cover fixed}
    .badge-new{background:rgba(212,170,58,.18);border:1px solid rgba(212,170,58,.4);color:#f0d898;animation:badgePulse 2.5s infinite}
    @keyframes badgePulse{0%,100%{box-shadow:0 0 0 0 rgba(212,170,58,.4)}50%{box-shadow:0 0 0 5px rgba(212,170,58,0)}}
  </style>
</head>
<body class="bg-black text-white overflow-x-hidden">

<!-- NAVBAR -->
<nav id="navbar" class="fixed top-0 left-0 right-0 z-50 transition-all duration-500">
  <div class="glass border-b border-white/[0.08]" id="navbar-inner">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16 md:h-20">
        <a href="<?= APP_URL ?>/index.php" class="flex items-center gap-3 group flex-shrink-0">
          <?php if ($logoSrc): ?>
            <img src="<?= e($logoSrc) ?>" alt="Logo" class="w-10 h-10 object-contain transition-transform group-hover:scale-110">
          <?php else: ?>
            <div class="w-10 h-10 rounded-xl glass-strong flex items-center justify-center transition-all group-hover:scale-110">
              <span class="text-gold-400 font-display font-bold text-lg">ص</span>
            </div>
          <?php endif; ?>
          <div class="leading-tight">
            <div class="text-white font-medium text-sm tracking-wide">SD Muhammadiyah 1</div>
            <div class="text-gold-400 text-[10px] tracking-[0.18em] uppercase font-light">Gentasari · Cilacap</div>
          </div>
        </a>

        <div class="hidden lg:flex items-center gap-8">
          <a href="<?= APP_URL ?>/index.php" class="nav-link <?= $activePage==='beranda'?'active':'' ?>">Beranda</a>

          <div class="dropdown relative">
            <button class="nav-link flex items-center gap-1 <?= in_array($activePage,['sekolah','guru','siswa'])?'active':'' ?>">Profil <i data-lucide="chevron-down" class="w-3 h-3 opacity-60 mt-0.5"></i></button>
            <div class="dropdown-menu absolute top-full left-1/2 -translate-x-1/2 mt-3 w-48">
              <div class="glass-dark rounded-2xl overflow-hidden py-1 shadow-2xl">
                <a href="<?= APP_URL ?>/pages/profile/sekolah.php" class="flex items-center gap-3 px-4 py-3 text-sm text-white/80 hover:text-white hover:bg-white/10 transition-all"><i data-lucide="building-2" class="w-4 h-4 text-gold-400"></i> Profil Sekolah</a>
                <a href="<?= APP_URL ?>/pages/profile/guru-staff.php" class="flex items-center gap-3 px-4 py-3 text-sm text-white/80 hover:text-white hover:bg-white/10 transition-all"><i data-lucide="users" class="w-4 h-4 text-gold-400"></i> Guru &amp; Staff</a>
                <a href="<?= APP_URL ?>/pages/profile/siswa.php" class="flex items-center gap-3 px-4 py-3 text-sm text-white/80 hover:text-white hover:bg-white/10 transition-all"><i data-lucide="graduation-cap" class="w-4 h-4 text-gold-400"></i> Siswa</a>
              </div>
            </div>
          </div>

          <div class="dropdown relative">
            <button class="nav-link flex items-center gap-1 <?= in_array($activePage,['galeri','fasilitas'])?'active':'' ?>">Media <i data-lucide="chevron-down" class="w-3 h-3 opacity-60 mt-0.5"></i></button>
            <div class="dropdown-menu absolute top-full left-1/2 -translate-x-1/2 mt-3 w-44">
              <div class="glass-dark rounded-2xl overflow-hidden py-1 shadow-2xl">
                <a href="<?= APP_URL ?>/pages/media/galeri.php" class="flex items-center gap-3 px-4 py-3 text-sm text-white/80 hover:text-white hover:bg-white/10 transition-all"><i data-lucide="image" class="w-4 h-4 text-gold-400"></i> Galeri Foto</a>
                <a href="<?= APP_URL ?>/pages/media/fasilitas.php" class="flex items-center gap-3 px-4 py-3 text-sm text-white/80 hover:text-white hover:bg-white/10 transition-all"><i data-lucide="layout-grid" class="w-4 h-4 text-gold-400"></i> Fasilitas</a>
              </div>
            </div>
          </div>

          <div class="dropdown relative">
            <button class="nav-link flex items-center gap-1 <?= in_array($activePage,['ekskul','pengumuman'])?'active':'' ?>">Aktivitas <i data-lucide="chevron-down" class="w-3 h-3 opacity-60 mt-0.5"></i></button>
            <div class="dropdown-menu absolute top-full left-1/2 -translate-x-1/2 mt-3 w-48">
              <div class="glass-dark rounded-2xl overflow-hidden py-1 shadow-2xl">
                <a href="<?= APP_URL ?>/pages/aktivitas/ekskul.php" class="flex items-center gap-3 px-4 py-3 text-sm text-white/80 hover:text-white hover:bg-white/10 transition-all"><i data-lucide="sparkles" class="w-4 h-4 text-gold-400"></i> Ekstrakurikuler</a>
                <a href="<?= APP_URL ?>/pages/aktivitas/pengumuman.php" class="flex items-center gap-3 px-4 py-3 text-sm text-white/80 hover:text-white hover:bg-white/10 transition-all"><i data-lucide="bell" class="w-4 h-4 text-gold-400"></i> Pengumuman</a>
              </div>
            </div>
          </div>

          <div class="dropdown relative">
            <button class="nav-link flex items-center gap-1 <?= in_array($activePage,['pengaduan','kontak'])?'active':'' ?>">Interaksi <i data-lucide="chevron-down" class="w-3 h-3 opacity-60 mt-0.5"></i></button>
            <div class="dropdown-menu absolute top-full left-1/2 -translate-x-1/2 mt-3 w-44">
              <div class="glass-dark rounded-2xl overflow-hidden py-1 shadow-2xl">
                <a href="<?= APP_URL ?>/pages/interaksi/pengaduan.php" class="flex items-center gap-3 px-4 py-3 text-sm text-white/80 hover:text-white hover:bg-white/10 transition-all"><i data-lucide="message-square-warning" class="w-4 h-4 text-gold-400"></i> Pengaduan</a>
                <a href="<?= APP_URL ?>/pages/interaksi/kontak.php" class="flex items-center gap-3 px-4 py-3 text-sm text-white/80 hover:text-white hover:bg-white/10 transition-all"><i data-lucide="mail" class="w-4 h-4 text-gold-400"></i> Kontak</a>
              </div>
            </div>
          </div>
        </div>

        <button id="hamburger" class="lg:hidden w-10 h-10 rounded-xl glass flex items-center justify-center">
          <i data-lucide="menu" class="w-5 h-5"></i>
        </button>
      </div>
    </div>
  </div>
</nav>

<!-- Mobile Menu -->
<div id="mobile-menu" class="fixed inset-y-0 right-0 w-80 z-[60] glass-dark shadow-2xl flex flex-col">
  <div class="flex items-center justify-between px-6 py-5 border-b border-white/10">
    <span class="font-display text-xl text-gold-300">Menu</span>
    <button id="close-menu" class="w-9 h-9 rounded-xl glass flex items-center justify-center"><i data-lucide="x" class="w-4 h-4"></i></button>
  </div>
  <nav class="flex-1 overflow-y-auto px-4 py-6 space-y-1">
    <a href="<?= APP_URL ?>/index.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/75 hover:bg-white/10 text-sm transition-all"><i data-lucide="home" class="w-4 h-4 text-gold-400/70"></i> Beranda</a>
    <div class="px-4 py-2 text-[10px] tracking-widest uppercase text-white/30">Profil</div>
    <a href="<?= APP_URL ?>/pages/profile/sekolah.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/75 hover:bg-white/10 text-sm transition-all"><i data-lucide="building-2" class="w-4 h-4 text-gold-400/70"></i> Profil Sekolah</a>
    <a href="<?= APP_URL ?>/pages/profile/guru-staff.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/75 hover:bg-white/10 text-sm transition-all"><i data-lucide="users" class="w-4 h-4 text-gold-400/70"></i> Guru &amp; Staff</a>
    <a href="<?= APP_URL ?>/pages/profile/siswa.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/75 hover:bg-white/10 text-sm transition-all"><i data-lucide="graduation-cap" class="w-4 h-4 text-gold-400/70"></i> Siswa</a>
    <div class="px-4 py-2 text-[10px] tracking-widest uppercase text-white/30">Media</div>
    <a href="<?= APP_URL ?>/pages/media/galeri.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/75 hover:bg-white/10 text-sm transition-all"><i data-lucide="image" class="w-4 h-4 text-gold-400/70"></i> Galeri Foto</a>
    <a href="<?= APP_URL ?>/pages/media/fasilitas.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/75 hover:bg-white/10 text-sm transition-all"><i data-lucide="layout-grid" class="w-4 h-4 text-gold-400/70"></i> Fasilitas</a>
    <div class="px-4 py-2 text-[10px] tracking-widest uppercase text-white/30">Aktivitas</div>
    <a href="<?= APP_URL ?>/pages/aktivitas/ekskul.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/75 hover:bg-white/10 text-sm transition-all"><i data-lucide="sparkles" class="w-4 h-4 text-gold-400/70"></i> Ekstrakurikuler</a>
    <a href="<?= APP_URL ?>/pages/aktivitas/pengumuman.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/75 hover:bg-white/10 text-sm transition-all"><i data-lucide="bell" class="w-4 h-4 text-gold-400/70"></i> Pengumuman</a>
    <div class="px-4 py-2 text-[10px] tracking-widest uppercase text-white/30">Interaksi</div>
    <a href="<?= APP_URL ?>/pages/interaksi/pengaduan.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/75 hover:bg-white/10 text-sm transition-all"><i data-lucide="message-square-warning" class="w-4 h-4 text-gold-400/70"></i> Pengaduan</a>
    <a href="<?= APP_URL ?>/pages/interaksi/kontak.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/75 hover:bg-white/10 text-sm transition-all"><i data-lucide="mail" class="w-4 h-4 text-gold-400/70"></i> Kontak</a>
  </nav>
</div>
<div id="menu-overlay" class="fixed inset-0 bg-black/50 z-[55] hidden backdrop-blur-sm"></div>

<script>
  const hamburger=document.getElementById('hamburger'),closeMenuBtn=document.getElementById('close-menu'),mobileMenu=document.getElementById('mobile-menu'),menuOverlay=document.getElementById('menu-overlay');
  function openMob(){mobileMenu.classList.add('open');menuOverlay.classList.remove('hidden');document.body.style.overflow='hidden'}
  function closeMob(){mobileMenu.classList.remove('open');menuOverlay.classList.add('hidden');document.body.style.overflow=''}
  hamburger.addEventListener('click',openMob);closeMenuBtn.addEventListener('click',closeMob);menuOverlay.addEventListener('click',closeMob);
  window.addEventListener('scroll',()=>{const ni=document.getElementById('navbar-inner');if(window.scrollY>40){ni.style.background='rgba(0,0,0,.88)';ni.style.backdropFilter='blur(24px)'}else{ni.style.background='';ni.style.backdropFilter=''}},{passive:true});
  document.addEventListener('DOMContentLoaded',()=>{lucide.createIcons();const revEls=document.querySelectorAll('.reveal');const obs=new IntersectionObserver(entries=>{entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add('visible');obs.unobserve(e.target)}})},{threshold:.1,rootMargin:'0 0 -40px 0'});revEls.forEach(el=>obs.observe(el))});
</script>
