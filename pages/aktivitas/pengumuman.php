<?php
require_once __DIR__.'/../../includes/config.php';

// ── Single post ─────────────────────────────────────────────────────
$id=(int)($_GET['id']??0);
if($id){
    try {
        $ann=db()->prepare("SELECT a.*,u.full_name AS author FROM announcements a LEFT JOIN admin_users u ON a.author_id=u.id WHERE a.id=? AND a.is_published=1");
        $ann->execute([$id]); $ann=$ann->fetch();
        if($ann) db()->prepare("UPDATE announcements SET views=views+1 WHERE id=?")->execute([$id]);
    } catch(Exception $e){ $ann=null; }

    $pageTitle=$ann?$ann['title']:'Tidak Ditemukan';
    $pageHeroTitle=$ann?$ann['title']:'404';
    $pageHeroLabel='Pengumuman'; $pageHeroColor='pink';
    $activePage='pengumuman'; $breadcrumbParent='Aktivitas';
    require_once ROOT_PATH.'header.php';

    $catClass=['penting'=>'section-label-pink','akademik'=>'section-label-sky','kegiatan'=>'section-label-gold','umum'=>'section-label-gold'];
    if(!$ann): ?>
    <div class="max-w-4xl mx-auto px-4 py-20 text-center">
      <p class="font-display text-5xl font-bold text-pink-200 mb-4">404</p>
      <p class="text-gray-500 mb-6">Pengumuman tidak ditemukan.</p>
      <a href="pengumuman.php" class="btn-outline-light">← Kembali</a>
    </div>
    <?php else: ?>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-12">
      <a href="pengumuman.php" class="inline-flex items-center gap-2 text-sm text-gray-400 hover:text-pink-500 transition-colors mb-8 font-medium">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Pengumuman
      </a>

      <?php if(!empty($ann['thumbnail'])): ?>
      <div class="rounded-2xl overflow-hidden mb-8 h-64" style="box-shadow:0 12px 40px rgba(244,114,182,.2)">
        <img src="<?=UPLOAD_URL?>announcements/<?=e($ann['thumbnail'])?>" class="w-full h-full object-cover" alt="">
      </div>
      <?php endif; ?>

      <div class="flex flex-wrap items-center gap-2 mb-4">
        <span class="section-label <?=$catClass[$ann['category']]??'section-label-gold'?>"><?=ucfirst(e($ann['category']))?></span>
        <?php if(isNew($ann['published_at'])): ?><span class="badge-new-light">✦ Terbaru</span><?php endif; ?>
        <?php if($ann['is_pinned']): ?><span class="section-label section-label-gold">📌 Disematkan</span><?php endif; ?>
      </div>

      <h1 class="font-display font-bold text-gray-800 mb-5" style="font-size:clamp(1.8rem,4vw,3rem);line-height:1.2">
        <?=e($ann['title'])?>
      </h1>

      <div class="flex flex-wrap items-center gap-4 text-sm text-gray-400 mb-10 pb-8" style="border-bottom:1px solid rgba(244,114,182,.12)">
        <span class="flex items-center gap-1.5 font-medium"><i data-lucide="user" class="w-3.5 h-3.5 text-pink-400"></i><?=e($ann['author']??'Admin')?></span>
        <span class="flex items-center gap-1.5"><i data-lucide="calendar" class="w-3.5 h-3.5 text-pink-400"></i><?=date('d F Y, H:i',strtotime($ann['published_at']))?></span>
        <span class="flex items-center gap-1.5"><i data-lucide="eye" class="w-3.5 h-3.5 text-pink-400"></i><?=number_format($ann['views'])?> kali dilihat</span>
      </div>

      <div class="prose-custom text-gray-600 font-light leading-relaxed text-base">
        <?=$ann['content']?>
      </div>

      <div class="mt-12 pt-6" style="border-top:1px solid rgba(244,114,182,.12)">
        <a href="pengumuman.php" class="btn-outline-light">
          <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Daftar
        </a>
      </div>
    </div>
    <?php endif;
    require_once ROOT_PATH.'footer.php';
    exit;
}

// ── List view ────────────────────────────────────────────────────────
$page=max(1,(int)($_GET['p']??1)); $perPage=9;
$cat=$_GET['cat']??''; $q=trim($_GET['q']??'');
$offset=($page-1)*$perPage;
$where="WHERE a.is_published=1"; $params=[];
if($cat){ $where.=" AND a.category=?"; $params[]=$cat; }
if($q)  { $where.=" AND (a.title LIKE ? OR a.content LIKE ?)"; $params[]="%$q%"; $params[]="%$q%"; }

try {
    $cStmt=db()->prepare("SELECT COUNT(*) FROM announcements a $where");
    $cStmt->execute($params); $total=(int)$cStmt->fetchColumn();
    $totalPages=max(1,(int)ceil($total/$perPage));
    $stmt=db()->prepare("SELECT a.*,u.full_name AS author FROM announcements a LEFT JOIN admin_users u ON a.author_id=u.id $where ORDER BY a.is_pinned DESC,a.published_at DESC LIMIT $perPage OFFSET $offset");
    $stmt->execute($params); $anns=$stmt->fetchAll();
} catch(Exception $e){ $anns=[]; $total=0; $totalPages=1; }

$pageTitle=$pageHeroTitle='Pengumuman';
$pageHeroLabel='Informasi Terkini';
$pageHeroSub='Ikuti terus informasi terbaru seputar kegiatan, akademik, dan pengumuman resmi sekolah.';
$pageHeroColor='gold'; $activePage='pengumuman'; $breadcrumbParent='Aktivitas';
require_once ROOT_PATH.'header.php';

$catClass=['penting'=>'section-label-pink','akademik'=>'section-label-sky','kegiatan'=>'section-label-gold','umum'=>'section-label-gold'];
$catBg=['penting'=>'glass-card-pink','akademik'=>'glass-card-sky','kegiatan'=>'glass-card-gold','umum'=>''];
?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

  <!-- Filter Bar -->
  <form method="GET" class="flex flex-wrap items-center gap-3 mb-10 reveal-fade">
    <div class="relative flex-1 min-w-[200px] max-w-sm">
      <i data-lucide="search" class="absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none w-4 h-4 text-pink-300"></i>
      <input type="text" name="q" value="<?=e($q)?>" placeholder="Cari pengumuman…"
             class="w-full pl-10 pr-4 py-2.5 rounded-2xl text-sm font-medium input-light">
    </div>
    <?php foreach([''=> 'Semua','umum'=>'Umum','akademik'=>'Akademik','kegiatan'=>'Kegiatan','penting'=>'Penting'] as $v=>$l): ?>
    <a href="?cat=<?=$v?>&q=<?=urlencode($q)?>"
       class="px-4 py-2.5 rounded-full text-xs font-semibold transition-all <?=$cat===$v?'text-white':'glass-card text-gray-500 hover:text-pink-500'?>"
       style="<?=$cat===$v?'background:linear-gradient(135deg,#f472b6,#d4aa3a)':''?>"><?=$l?></a>
    <?php endforeach; ?>
  </form>

  <?php if(empty($anns)): ?>
  <div class="glass-card rounded-3xl p-16 text-center">
    <i data-lucide="bell-off" class="w-10 h-10 mx-auto mb-3 text-pink-200"></i>
    <p class="text-gray-400">Belum ada pengumuman.</p>
  </div>
  <?php else: ?>

  <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 stagger-grid">
    <?php foreach($anns as $i=>$a): ?>
    <a href="?id=<?=$a['id']?>" class="tilt-card glass-card rounded-2xl overflow-hidden lift-card block reveal-3d" style="animation-delay:<?=$i*.06?>s">
      <div class="tilt-inner h-full"><div class="tilt-shine"></div>

        <?php if(!empty($a['thumbnail'])): ?>
        <div class="h-44 overflow-hidden">
          <img src="<?=UPLOAD_URL?>announcements/<?=e($a['thumbnail'])?>" loading="lazy" class="w-full h-full object-cover hover:scale-105 transition-transform duration-500" alt="">
        </div>
        <?php else: ?>
        <div class="h-28 flex items-center justify-center" style="background:linear-gradient(135deg,#fdf2f8,#fef9e7)">
          <i data-lucide="file-text" class="w-8 h-8 text-pink-200"></i>
        </div>
        <?php endif; ?>

        <div class="p-5">
          <div class="flex flex-wrap items-center gap-2 mb-3">
            <?php if($a['is_pinned']): ?><span class="text-gold-500 text-sm">📌</span><?php endif; ?>
            <span class="section-label <?=$catClass[$a['category']]??'section-label-gold'?>"><?=ucfirst(e($a['category']))?></span>
            <?php if(isNew($a['published_at'])): ?><span class="badge-new-light">✦ Terbaru</span><?php endif; ?>
          </div>
          <h3 class="font-display font-semibold text-gray-800 text-base leading-snug line-clamp-2 mb-3"><?=e($a['title'])?></h3>
          <div class="flex items-center justify-between mt-auto pt-3" style="border-top:1px solid rgba(244,114,182,.08)">
            <span class="text-gray-400 text-xs"><?=date('d M Y',strtotime($a['published_at']))?></span>
            <span class="flex items-center gap-1 text-gray-400 text-xs"><i data-lucide="eye" class="w-3 h-3"></i><?=$a['views']?></span>
          </div>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>

  <!-- Pagination -->
  <?php if($totalPages>1): ?>
  <div class="flex justify-center gap-2 mt-12 reveal-fade">
    <?php for($i=1;$i<=$totalPages;$i++): ?>
    <a href="?p=<?=$i?>&cat=<?=urlencode($cat)?>&q=<?=urlencode($q)?>"
       class="w-10 h-10 rounded-xl flex items-center justify-center text-sm font-semibold transition-all <?=$i===$page?'text-white':'glass-card text-gray-400 hover:text-pink-500'?>"
       style="<?=$i===$page?'background:linear-gradient(135deg,#f472b6,#d4aa3a)':''?>"><?=$i?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
  <?php endif; ?>
</div>

<?php require_once ROOT_PATH.'footer.php'; ?>