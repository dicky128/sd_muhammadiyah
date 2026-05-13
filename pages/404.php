<?php
require_once __DIR__.'/../includes/config.php';
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>404 — Halaman Tidak Ditemukan | <?=e(APP_NAME)?></title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
<link rel="stylesheet" href="<?=APP_URL?>/assets/css/light3d.css">
<style>
*{font-family:'Plus Jakarta Sans',sans-serif}h1,h2{font-family:'Playfair Display',serif}
@keyframes float-y{0%,100%{transform:translateY(0)}50%{transform:translateY(-20px)}}
@keyframes fadeUp{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:translateY(0)}}
.float-anim{animation:float-y 6s ease-in-out infinite}
</style>
</head>
<body class="light-mode bg-cream min-h-screen flex flex-col">

<!-- Minimal navbar -->
<nav class="navbar-light">
  <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
    <a href="<?=APP_URL?>" class="flex items-center gap-3">
      <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background:linear-gradient(135deg,#fbcfe8,#fef3c7);border:1px solid rgba(244,114,182,.3)">
        <span style="font-family:'Playfair Display',serif;color:#be185d;font-weight:700">ص</span>
      </div>
      <span class="text-sm font-semibold text-gray-700">SD Muhammadiyah 1</span>
    </a>
    <a href="<?=APP_URL?>" class="btn-outline-light !px-4 !py-2 !text-xs">
      <i data-lucide="home" class="w-3.5 h-3.5"></i> Beranda
    </a>
  </div>
</nav>

<main class="flex-1 flex items-center justify-center px-4 py-20">
  <!-- Orbs -->
  <div class="orb orb-pink w-72 h-72 opacity-40" style="position:fixed;top:-5%;right:-5%"></div>
  <div class="orb orb-sky w-56 h-56 opacity-30" style="position:fixed;bottom:10%;left:-5%"></div>

  <div class="relative z-10 text-center max-w-lg mx-auto">

    <!-- Animated 404 number -->
    <div class="float-anim mb-8" style="animation-delay:.2s;opacity:0;animation:float-y 6s .2s ease-in-out infinite, fadeUp .8s .1s ease forwards">
      <div class="font-display font-bold leading-none select-none" style="font-size:clamp(6rem,20vw,12rem)">
        <span class="text-gradient-pink-gold">4</span><span style="color:#e8c860">0</span><span class="text-gradient-sky-pink">4</span>
      </div>
    </div>

    <!-- Glass card message -->
    <div class="tilt-card glass-card rounded-3xl p-8 mb-8" style="opacity:0;animation:fadeUp .8s .3s ease forwards;box-shadow:0 20px 60px rgba(244,114,182,.15)">
      <div class="tilt-inner"><div class="tilt-shine"></div>
        <div class="icon-badge icon-badge-pink mx-auto mb-4">
          <i data-lucide="map-pin-off" class="w-5 h-5"></i>
        </div>
        <h1 class="font-display font-bold text-gray-800 text-2xl mb-2">Halaman Tidak Ditemukan</h1>
        <p class="text-gray-500 text-sm leading-relaxed">
          Halaman yang Anda cari tidak ada, mungkin telah dipindahkan, atau URL yang Anda masukkan salah.
        </p>
      </div>
    </div>

    <!-- Actions -->
    <div class="flex flex-wrap justify-center gap-4" style="opacity:0;animation:fadeUp .8s .5s ease forwards">
      <a href="<?=APP_URL?>" class="btn-primary-light">
        <i data-lucide="home" class="w-4 h-4"></i> Ke Beranda
      </a>
      <a href="javascript:history.back()" class="btn-outline-light">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
      </a>
      <a href="<?=APP_URL?>/pages/interaksi/kontak.php" class="btn-outline-light">
        <i data-lucide="mail" class="w-4 h-4"></i> Laporkan
      </a>
    </div>

    <!-- Quick links -->
    <div class="mt-10" style="opacity:0;animation:fadeUp .8s .65s ease forwards">
      <p class="text-gray-400 text-xs uppercase tracking-widest font-semibold mb-4">Mungkin Anda mencari</p>
      <div class="flex flex-wrap justify-center gap-2">
        <?php foreach([
          ['pages/profile/sekolah.php','building-2','Profil'],
          ['pages/media/galeri.php','image','Galeri'],
          ['pages/aktivitas/pengumuman.php','bell','Pengumuman'],
          ['pages/aktivitas/ekskul.php','sparkles','Ekskul'],
          ['pages/interaksi/kontak.php','mail','Kontak'],
        ] as $l): ?>
        <a href="<?=APP_URL?>/<?=$l[0]?>" class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold text-gray-500 hover:text-pink-600 hover:bg-pink-50 transition-all glass-card">
          <i data-lucide="<?=$l[1]?>" class="w-3.5 h-3.5 text-pink-400"></i><?=$l[2]?>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</main>

<script>lucide.createIcons()</script>
</body>
</html>