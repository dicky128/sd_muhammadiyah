<?php
// includes/header.php — Light mode shared header
// Set before including: $pageTitle, $pageDesc, $activePage, $pageHeroTitle, $pageHeroSub, $pageHeroLabel
if(!defined('ROOT_PATH')) define('ROOT_PATH',dirname(__DIR__));
// Auto-load helper functions
if(!function_exists('formatDate')) require_once ROOT_PATH.'functions.php';
$pageTitle      = $pageTitle      ?? APP_NAME;
$pageDesc       = $pageDesc       ?? 'Website resmi '.APP_NAME;
$activePage     = $activePage     ?? '';
$pageHeroTitle  = $pageHeroTitle  ?? $pageTitle;
$pageHeroSub    = $pageHeroSub    ?? '';
$pageHeroLabel  = $pageHeroLabel  ?? '';
$pageHeroColor  = $pageHeroColor  ?? 'sky'; // pink | gold | sky

try {
    $profileData = db()->query("SELECT * FROM school_profile LIMIT 1")->fetch() ?: [];
    $logoSrc = !empty($profileData['logo']) ? UPLOAD_URL.'logos/'.$profileData['logo'] : null;
    $siteName = $profileData['school_name'] ?? APP_NAME;
} catch(Exception $e){ $profileData=[]; $logoSrc=null; $siteName=APP_NAME; }

$labelClass = ['pink'=>'section-label-pink','gold'=>'section-label-gold','sky'=>'section-label-sky'];
$gradClass  = ['pink'=>'text-gradient-pink-gold','gold'=>'text-gradient-pink-gold','sky'=>'text-gradient-sky-pink'];
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta name="description" content="<?=e($pageDesc)?>">
<title><?=e($pageTitle)?> — <?=e($siteName)?></title>
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
<link rel="stylesheet" href="<?=APP_URL?>/assets/css/light3d.css">
<style>
*{font-family:'Plus Jakarta Sans',sans-serif}
h1,h2,h3,.font-display{font-family:'Playfair Display',serif}
.dropdown-menu-light{opacity:0;visibility:hidden;transform:translateY(6px);transition:all .22s cubic-bezier(.16,1,.3,1)}
.dropdown:hover .dropdown-menu-light,.dropdown:focus-within .dropdown-menu-light{opacity:1;visibility:visible;transform:translateY(0)}
#mobile-menu{transform:translateX(100%);transition:transform .38s cubic-bezier(.16,1,.3,1)}
#mobile-menu.open{transform:translateX(0)}
.lift-card{transition:transform .35s cubic-bezier(.16,1,.3,1),box-shadow .35s ease}
.lift-card:hover{transform:translateY(-6px) scale(1.01);box-shadow:0 20px 50px rgba(244,114,182,.18)}
@keyframes fadeUp{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:translateY(0)}}
</style>
</head>
<body class="light-mode bg-cream overflow-x-hidden">
<canvas id="three-canvas"></canvas>

<!-- NAVBAR -->
<nav id="navbar" class="navbar-light">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between h-16 md:h-20">
      <a href="<?=APP_URL?>/index.php" class="flex items-center gap-3 group flex-shrink-0">
        <?php if($logoSrc): ?>
        <img src="<?=e($logoSrc)?>" alt="Logo" class="w-10 h-10 object-contain group-hover:scale-110 transition-transform">
        <?php else: ?>
        <div class="w-10 h-10 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform" style="background:linear-gradient(135deg,#fbcfe8,#fef3c7);border:1px solid rgba(244,114,182,.3)">
          <span style="font-family:'Playfair Display',serif;color:#be185d;font-weight:700;font-size:1.1rem">ص</span>
        </div>
        <?php endif; ?>
        <div><div class="text-sm font-semibold text-gray-800"><?php $pageTitle ?></div>
        <div class="text-[10px] tracking-widest uppercase font-medium text-pink-400">Gentasari · Cilacap</div></div>
      </a>

      <div class="hidden lg:flex items-center gap-8">
        <a href="<?=APP_URL?>/index.php" class="nav-link-light <?=$activePage==='beranda'?'active':''?>">Beranda</a>
        <?php $drops=[
          ['Profil',['sekolah','guru','siswa'],[['pages/profile/sekolah.php','building-2','Profil Sekolah'],['pages/profile/guru-staff.php','users','Guru &amp; Staff'],['pages/profile/siswa.php','graduation-cap','Siswa']]],
          ['Media',['galeri','fasilitas'],[['pages/media/galeri.php','image','Galeri Foto'],['pages/media/fasilitas.php','layout-grid','Fasilitas']]],
          ['Aktivitas',['ekskul','pengumuman'],[['pages/aktivitas/ekskul.php','sparkles','Ekstrakurikuler'],['pages/aktivitas/pengumuman.php','bell','Pengumuman']]],
          ['Interaksi',['pengaduan','kontak'],[['pages/interaksi/pengaduan.php','message-square-warning','Pengaduan'],['pages/interaksi/kontak.php','mail','Kontak']]],
        ];
        foreach($drops as [$lbl,$pages,$links]): ?>
        <div class="dropdown relative">
          <button class="nav-link-light flex items-center gap-1 <?=in_array($activePage,$pages)?'active':''?>"><?=$lbl?> <i data-lucide="chevron-down" class="w-3 h-3 opacity-50 mt-0.5"></i></button>
          <div class="dropdown-menu-light absolute top-full left-1/2 -translate-x-1/2 mt-3 w-48">
            <div class="rounded-2xl overflow-hidden shadow-xl py-1" style="background:rgba(255,255,255,.95);backdrop-filter:blur(20px);border:1px solid rgba(244,114,182,.15)">
              <?php foreach($links as $l): ?>
              <a href="<?=APP_URL.'/'.$l[0]?>" class="flex items-center gap-3 px-4 py-3 text-sm text-gray-600 hover:text-pink-600 hover:bg-pink-50 transition-all">
                <i data-lucide="<?=$l[1]?>" class="w-4 h-4 text-pink-400"></i><?=$l[2]?>
              </a>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
        <a href="<?=APP_URL?>/pages/interaksi/pengaduan.php" class="btn-primary-light !px-5 !py-2.5 !text-xs ml-2">
          <i data-lucide="send" class="w-3.5 h-3.5"></i> Pengaduan
        </a>
      </div>

      <button id="hamburger" class="lg:hidden w-10 h-10 rounded-xl flex items-center justify-center" style="background:rgba(244,114,182,.08);border:1px solid rgba(244,114,182,.2)">
        <i data-lucide="menu" class="w-5 h-5 text-pink-500"></i>
      </button>
    </div>
  </div>
</nav>

<!-- Mobile Menu -->
<div id="mobile-menu" class="fixed inset-y-0 right-0 w-80 z-[60] flex flex-col shadow-2xl" style="background:rgba(255,255,255,.97);backdrop-filter:blur(24px);border-left:1px solid rgba(244,114,182,.15)">
  <div class="flex items-center justify-between px-6 py-5" style="border-bottom:1px solid rgba(244,114,182,.12)">
    <span style="font-family:'Playfair Display',serif;font-size:1.25rem;color:#be185d">Menu</span>
    <button id="close-menu" class="w-9 h-9 rounded-xl flex items-center justify-center" style="background:rgba(244,114,182,.1)"><i data-lucide="x" class="w-4 h-4 text-pink-500"></i></button>
  </div>
  <nav class="flex-1 overflow-y-auto px-4 py-6 space-y-1">
    <?php foreach([[APP_URL.'/index.php','home','Beranda'],[APP_URL.'/pages/profile/sekolah.php','building-2','Profil Sekolah'],[APP_URL.'/pages/profile/guru-staff.php','users','Guru & Staff'],[APP_URL.'/pages/profile/siswa.php','graduation-cap','Siswa'],[APP_URL.'/pages/media/galeri.php','image','Galeri Foto'],[APP_URL.'/pages/media/fasilitas.php','layout-grid','Fasilitas'],[APP_URL.'/pages/aktivitas/ekskul.php','sparkles','Ekstrakurikuler'],[APP_URL.'/pages/aktivitas/pengumuman.php','bell','Pengumuman'],[APP_URL.'/pages/interaksi/pengaduan.php','message-square-warning','Pengaduan'],[APP_URL.'/pages/interaksi/kontak.php','mail','Kontak Kami']] as $l): ?>
    <a href="<?=$l[0]?>" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm text-gray-600 hover:text-pink-600 hover:bg-pink-50 transition-all font-medium">
      <i data-lucide="<?=$l[1]?>" class="w-4 h-4 text-pink-400"></i><?=$l[2]?>
    </a>
    <?php endforeach; ?>
  </nav>
</div>
<div id="menu-overlay" class="fixed inset-0 bg-black/20 z-[55] hidden backdrop-blur-sm"></div>

<div class="page-wrapper">

<!-- PAGE HERO -->
<section class="page-hero-light relative overflow-hidden">
  <div class="grid-lines opacity-50"></div>
  <div class="orb orb-pink w-72 h-72 opacity-40 animate-float-y" style="top:-20%;right:-5%"></div>
  <div class="orb orb-sky w-48 h-48 opacity-30 animate-float-diagonal" style="bottom:-10%;left:5%;animation-delay:2s"></div>
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs text-gray-400 mb-6" style="opacity:0;animation:fadeUp .6s .05s ease forwards">
      <a href="<?=APP_URL?>/index.php" class="hover:text-pink-500 transition-colors">Beranda</a>
      <i data-lucide="chevron-right" class="w-3 h-3"></i>
      <?php if(!empty($breadcrumbParent)): ?>
      <span class="text-gray-400"><?=$breadcrumbParent?></span>
      <i data-lucide="chevron-right" class="w-3 h-3"></i>
      <?php endif; ?>
      <span class="text-pink-500 font-medium"><?=e($pageTitle)?></span>
    </nav>
    <?php if($pageHeroLabel): ?>
    <div class="mb-4" style="opacity:0;animation:fadeUp .6s .1s ease forwards">
      <span class="section-label <?=$labelClass[$pageHeroColor]??'section-label-pink'?>"><?=e($pageHeroLabel)?></span>
    </div>
    <?php endif; ?>
    <h1 class="font-display font-bold text-gray-800 mb-4" style="font-size:clamp(2rem,5vw,4rem);line-height:1.1;opacity:0;animation:fadeUp .7s .15s ease forwards">
      <?=e($pageHeroTitle)?>
    </h1>
    <?php if($pageHeroSub): ?>
    <p class="text-gray-500 font-light max-w-2xl leading-relaxed" style="opacity:0;animation:fadeUp .7s .25s ease forwards"><?=e($pageHeroSub)?></p>
    <?php endif; ?>
  </div>
</section>

<main>
<script>
const hamburger=document.getElementById('hamburger'),closeBtn=document.getElementById('close-menu'),mobileMenu=document.getElementById('mobile-menu'),overlay=document.getElementById('menu-overlay');
function openMenu(){mobileMenu.classList.add('open');overlay.classList.remove('hidden');document.body.style.overflow='hidden'}
function closeMenu(){mobileMenu.classList.remove('open');overlay.classList.add('hidden');document.body.style.overflow=''}
hamburger.addEventListener('click',openMenu);closeBtn.addEventListener('click',closeMenu);overlay.addEventListener('click',closeMenu);
document.addEventListener('keydown',e=>{if(e.key==='Escape')closeMenu()});
window.addEventListener('scroll',()=>document.getElementById('navbar').classList.toggle('scrolled',window.scrollY>40),{passive:true});
</script>
<script src="<?=APP_URL?>/assets/js/animations.js" defer></script>
