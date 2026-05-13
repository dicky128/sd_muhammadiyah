<?php
require_once __DIR__.'/../../includes/config.php';
$pageTitle     = 'Profil Sekolah';
$pageHeroLabel = 'Tentang Kami';
$pageHeroTitle = 'Profil Sekolah';
$pageHeroSub   = 'Mengenal lebih dekat SD Muhammadiyah 1 Gentasari — sejarah, visi, misi, dan identitas kami.';
$pageHeroColor = 'pink';
$activePage    = 'sekolah';
$breadcrumbParent = 'Profil';
require_once ROOT_PATH.'header.php';

try { $p=db()->query("SELECT * FROM school_profile LIMIT 1")->fetch()??[]; }
catch(Exception $e){ $p=[]; }
?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-20 space-y-16">

  <!-- Identity + Quick Stats -->
  <div class="grid lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2 glass-card rounded-3xl p-8 reveal-3d">
      <h2 class="font-display font-bold text-gray-800 text-2xl mb-6 flex items-center gap-3">
        <div class="icon-badge icon-badge-pink"><i data-lucide="building-2" class="w-5 h-5"></i></div>
        Identitas Sekolah
      </h2>
      <?php foreach([['NPSN',$p['npsn']??'-'],['Nama Sekolah',$p['school_name']??APP_NAME],['Status','Swasta / Muhammadiyah'],['Akreditasi',$p['akreditasi']??'A'],['Tahun Berdiri',$p['tahun_berdiri']??'1962'],['Desa/Kelurahan',$p['village']??'Gentasari'],['Kecamatan',$p['district']??'Kroya'],['Kab/Kota',$p['city']??'Cilacap'],['Provinsi',$p['province']??'Jawa Tengah'],['Alamat',$p['address']??'-'],['Telepon',$p['phone']??'-'],['Email',$p['email']??'-']] as $r): ?>
      <div class="flex items-start gap-4 py-3" style="border-bottom:1px solid rgba(244,114,182,.08)">
        <span class="text-xs tracking-widest uppercase text-gray-400 w-36 flex-shrink-0 pt-0.5 font-semibold"><?=$r[0]?></span>
        <span class="text-gray-700 text-sm"><?=e($r[1])?></span>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="space-y-4 stagger-grid">
      <?php foreach([['users','Siswa Aktif',setting('stats_students','380').'+','icon-badge-pink'],['graduation-cap','Tenaga Pendidik',setting('stats_teachers','24'),'icon-badge-sky'],['sparkles','Ekstrakurikuler',setting('stats_ekskul','12'),'icon-badge-gold'],['trophy','Tahun Pengalaman',setting('stats_years','60').'+','icon-badge-pink']] as $s): ?>
      <div class="tilt-card glass-card rounded-2xl p-5 lift-card">
        <div class="tilt-inner flex items-center gap-4"><div class="tilt-shine"></div>
          <div class="<?=$s[3]?> icon-badge flex-shrink-0"><i data-lucide="<?=$s[0]?>" class="w-5 h-5"></i></div>
          <div><div class="font-display font-bold text-2xl text-gray-800"><?=$s[2]?></div><div class="text-xs text-gray-400"><?=$s[1]?></div></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Visi -->
  <?php if(!empty($p['visi'])): ?>
  <div class="tilt-card glass-card glass-card-pink rounded-3xl p-8 lg:p-10 reveal-3d">
    <div class="tilt-inner"><div class="tilt-shine"></div>
      <div class="flex items-start gap-5">
        <div class="icon-badge icon-badge-pink flex-shrink-0 mt-1"><i data-lucide="eye" class="w-5 h-5"></i></div>
        <div><h2 class="font-display text-2xl font-semibold text-pink-700 mb-3">Visi</h2>
        <p class="text-gray-600 font-light leading-relaxed"><?=nl2br(e($p['visi']))?></p></div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Misi -->
  <?php if(!empty($p['misi'])): ?>
  <div class="tilt-card glass-card glass-card-sky rounded-3xl p-8 lg:p-10 reveal-3d">
    <div class="tilt-inner"><div class="tilt-shine"></div>
      <div class="flex items-start gap-5">
        <div class="icon-badge icon-badge-sky flex-shrink-0 mt-1"><i data-lucide="target" class="w-5 h-5"></i></div>
        <div><h2 class="font-display text-2xl font-semibold text-sky-700 mb-3">Misi</h2>
        <p class="text-gray-600 font-light leading-relaxed"><?=nl2br(e($p['misi']))?></p></div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Sejarah -->
  <?php if(!empty($p['sejarah'])): ?>
  <div class="glass-card rounded-3xl p-8 lg:p-10 reveal-3d">
    <h2 class="font-display text-2xl font-semibold text-gray-800 mb-5 flex items-center gap-3">
      <div class="icon-badge icon-badge-gold"><i data-lucide="book-open" class="w-5 h-5"></i></div>Sejarah Singkat
    </h2>
    <div class="text-gray-600 font-light leading-relaxed prose-custom"><?=nl2br(e($p['sejarah']))?></div>
  </div>
  <?php endif; ?>

  <!-- Maps -->
  <?php if(!empty($p['maps_embed'])): ?>
  <div class="reveal-3d">
    <h2 class="font-display text-2xl font-semibold text-gray-800 mb-5 flex items-center gap-3">
      <div class="icon-badge icon-badge-pink"><i data-lucide="map-pin" class="w-5 h-5"></i></div>Lokasi Kami
    </h2>
    <div class="rounded-3xl overflow-hidden" style="height:380px;box-shadow:0 12px 40px rgba(244,114,182,.15)"><?=$p['maps_embed']?></div>
  </div>
  <?php endif; ?>
</div>

<?php require_once ROOT_PATH.'footer.php'; ?>