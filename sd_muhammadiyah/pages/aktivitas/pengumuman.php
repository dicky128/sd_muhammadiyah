<?php
require_once __DIR__ . '/../../includes/config.php';
$pageTitle  = 'Pengumuman';
$activePage = 'pengumuman';

// Single post view
$id = (int)($_GET['id'] ?? 0);
if ($id) {
    try {
        $ann = db()->prepare("SELECT a.*,u.full_name AS author FROM announcements a LEFT JOIN admin_users u ON a.author_id=u.id WHERE a.id=? AND a.is_published=1");
        $ann->execute([$id]); $ann = $ann->fetch();
        if ($ann) { db()->prepare("UPDATE announcements SET views=views+1 WHERE id=?")->execute([$id]); }
    } catch(Exception $e){ $ann=null; }

    $pageTitle = $ann ? $ann['title'] : '404';
    require_once ROOT_PATH . '/includes/header.php';

    if (!$ann): ?>
    <main class="pt-36 min-h-screen flex items-center justify-center bg-black">
      <div class="text-center"><p class="font-display text-6xl text-white/20 font-light">404</p><p class="text-white/40 mt-2">Pengumuman tidak ditemukan.</p><a href="pengumuman.php" class="mt-5 inline-block text-gold-400 text-sm hover:text-gold-300 transition-colors">← Kembali</a></div>
    </main>
    <?php else:
      $catColor=['penting'=>'text-red-300 bg-red-500/15','akademik'=>'text-blue-300 bg-blue-500/15','kegiatan'=>'text-green-300 bg-green-500/15','umum'=>'text-white/40 bg-white/5'];
    ?>
    <main class="pt-20 min-h-screen bg-gradient-to-b from-black via-zinc-950 to-black">
      <article class="max-w-3xl mx-auto px-4 sm:px-6 py-20">
        <a href="pengumuman.php" class="flex items-center gap-2 text-white/40 hover:text-gold-300 transition-colors text-sm mb-8"><i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Pengumuman</a>

        <?php if (!empty($ann['thumbnail'])): ?>
        <div class="rounded-2xl overflow-hidden mb-8 h-64"><img src="<?= UPLOAD_URL ?>announcements/<?= e($ann['thumbnail']) ?>" alt="<?= e($ann['title']) ?>" class="w-full h-full object-cover"></div>
        <?php endif; ?>

        <div class="flex flex-wrap items-center gap-3 mb-4">
          <span class="text-[11px] px-2.5 py-1 rounded-full <?= $catColor[$ann['category']]??'text-white/40 bg-white/5' ?>"><?= ucfirst(e($ann['category'])) ?></span>
          <?php if (isNew($ann['published_at'])): ?><span class="badge-new text-[11px] px-2.5 py-1 rounded-full">✦ Terbaru</span><?php endif; ?>
          <?php if ($ann['is_pinned']): ?><span class="text-[11px] px-2.5 py-1 rounded-full bg-white/5 text-white/35">📌 Disematkan</span><?php endif; ?>
        </div>

        <h1 class="font-display text-4xl lg:text-5xl text-white font-light leading-tight mb-5"><?= e($ann['title']) ?></h1>

        <div class="flex items-center gap-4 text-sm text-white/35 mb-10 pb-8 border-b border-white/[.07]">
          <span class="flex items-center gap-1.5"><i data-lucide="user" class="w-3 h-3"></i><?= e($ann['author']??'Admin') ?></span>
          <span class="flex items-center gap-1.5"><i data-lucide="calendar" class="w-3 h-3"></i><?= date('d F Y, H:i', strtotime($ann['published_at'])) ?></span>
          <span class="flex items-center gap-1.5"><i data-lucide="eye" class="w-3 h-3"></i><?= number_format($ann['views']) ?> kali dilihat</span>
        </div>

        <div class="prose-custom text-white/65 font-light leading-relaxed text-base" style="line-height:1.85">
          <?= $ann['content'] ?>
        </div>
      </article>
    </main>
    <?php endif;
    require_once ROOT_PATH . '/includes/footer.php';
    exit;
} // end single view

// ── LIST VIEW ─────────────────────────────────────────────────────────────
$page    = max(1,(int)($_GET['p']??1));
$perPage = 9;
$cat     = $_GET['cat'] ?? '';
$q       = trim($_GET['q'] ?? '');
$offset  = ($page-1)*$perPage;

$where = "WHERE a.is_published=1";
$params = [];
if ($cat)  { $where .= " AND a.category=?"; $params[]=$cat; }
if ($q)    { $where .= " AND (a.title LIKE ? OR a.content LIKE ?)"; $params[]="%$q%"; $params[]="%$q%"; }

try {
    $totalAnn   = db()->prepare("SELECT COUNT(*) FROM announcements a $where");
    $totalAnn->execute($params); $total=(int)$totalAnn->fetchColumn();
    $totalPages = (int)ceil($total/$perPage);
    $stmt = db()->prepare("SELECT a.*,u.full_name AS author FROM announcements a LEFT JOIN admin_users u ON a.author_id=u.id $where ORDER BY a.is_pinned DESC,a.published_at DESC LIMIT $perPage OFFSET $offset");
    $stmt->execute($params); $anns=$stmt->fetchAll();
} catch(Exception $e){ $anns=[]; $total=0; $totalPages=1; }

require_once ROOT_PATH . '/includes/header.php';

$catColor=['penting'=>'text-red-300 bg-red-500/15','akademik'=>'text-blue-300 bg-blue-500/15','kegiatan'=>'text-green-300 bg-green-500/15','umum'=>'text-white/40 bg-white/5'];
?>

<main class="pt-20 min-h-screen bg-gradient-to-b from-black via-zinc-950 to-black">
  <section class="page-hero py-24">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center gap-3 mb-4 reveal"><div class="h-px w-10 bg-gold-400/70"></div><span class="text-gold-300 text-xs tracking-[.25em] uppercase">Informasi Terkini</span></div>
      <h1 class="font-display text-5xl lg:text-6xl font-light text-white reveal">Pengumuman</h1>
      <p class="text-white/50 mt-4 font-light reveal"><?= $total ?> pengumuman tersedia</p>
    </div>
  </section>

  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    <!-- Filter bar -->
    <form method="GET" class="flex flex-wrap items-center gap-3 mb-10 reveal">
      <div class="relative flex-1 min-w-[180px] max-w-xs">
        <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" style="width:14px;height:14px;color:rgba(255,255,255,.3)"></i>
        <input type="text" name="q" value="<?= e($q) ?>" placeholder="Cari pengumuman…" class="w-full pl-9 pr-4 py-2.5 rounded-xl text-sm font-light" style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);color:#fff">
      </div>
      <?php foreach([''=>'Semua','umum'=>'Umum','akademik'=>'Akademik','kegiatan'=>'Kegiatan','penting'=>'Penting'] as $val=>$lbl): ?>
      <a href="?cat=<?= $val ?>&q=<?= urlencode($q) ?>" class="px-4 py-2.5 rounded-xl text-xs tracking-wide transition-all <?= $cat===$val ? 'text-black font-medium' : 'glass text-white/60 hover:text-white hover:bg-white/12' ?>" style="<?= $cat===$val ? 'background:linear-gradient(135deg,#d4aa3a,#e8c860)' : '' ?>"><?= $lbl ?></a>
      <?php endforeach; ?>
    </form>

    <?php if (empty($anns)): ?>
    <div class="glass rounded-3xl p-16 text-center"><i data-lucide="bell-off" class="w-10 h-10 mx-auto mb-3" style="color:rgba(255,255,255,.2)"></i><p class="text-white/30">Belum ada pengumuman.</p></div>
    <?php else: ?>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach($anns as $i=>$a): ?>
      <a href="?id=<?= $a['id'] ?>" class="glass rounded-2xl overflow-hidden group hover:-translate-y-1 hover:bg-white/10 transition-all duration-300 reveal block" style="animation-delay:<?= $i*.05 ?>s">
        <?php if (!empty($a['thumbnail'])): ?>
        <div class="h-44 overflow-hidden"><img src="<?= UPLOAD_URL ?>announcements/<?= e($a['thumbnail']) ?>" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt=""></div>
        <?php else: ?>
        <div class="h-28 flex items-center justify-center" style="background:rgba(212,170,58,.05)"><i data-lucide="file-text" class="w-8 h-8" style="color:rgba(212,170,58,.25)"></i></div>
        <?php endif; ?>
        <div class="p-5">
          <div class="flex flex-wrap items-center gap-2 mb-3">
            <?php if ($a['is_pinned']): ?><span class="text-[10px] text-gold-400">📌</span><?php endif; ?>
            <span class="text-[10px] px-2 py-0.5 rounded-full <?= $catColor[$a['category']]??'text-white/40 bg-white/5' ?>"><?= ucfirst(e($a['category'])) ?></span>
            <?php if (isNew($a['published_at'])): ?><span class="badge-new text-[10px] px-2 py-0.5 rounded-full font-medium">✦ Terbaru</span><?php endif; ?>
          </div>
          <h3 class="text-white/85 font-medium text-sm leading-snug group-hover:text-white transition-colors line-clamp-2"><?= e($a['title']) ?></h3>
          <div class="flex items-center justify-between mt-4">
            <span class="text-white/30 text-xs"><?= date('d M Y', strtotime($a['published_at'])) ?></span>
            <span class="flex items-center gap-1 text-white/25 text-xs"><i data-lucide="eye" class="w-3 h-3"></i><?= $a['views'] ?></span>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages>1): ?>
    <div class="flex justify-center gap-2 mt-10">
      <?php for($i=1;$i<=$totalPages;$i++): ?>
      <a href="?p=<?= $i ?>&cat=<?= urlencode($cat) ?>&q=<?= urlencode($q) ?>" class="w-10 h-10 rounded-xl flex items-center justify-center text-sm transition-all <?= $i===$page?'text-black font-medium':'glass text-white/50 hover:text-white hover:bg-white/10' ?>" style="<?= $i===$page?'background:linear-gradient(135deg,#d4aa3a,#e8c860)':'' ?>"><?= $i ?></a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
  </div>
</main>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
