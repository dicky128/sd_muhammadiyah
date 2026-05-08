<?php
require_once __DIR__ . '/../../includes/config.php';
$pageTitle  = 'Galeri Foto';
$activePage = 'galeri';
require_once ROOT_PATH . '../../includes/header.php';

$catSlug = $_GET['cat'] ?? '';
try {
    $categories = db()->query("SELECT * FROM gallery_categories ORDER BY sort_order")->fetchAll();
    $where = $catSlug ? "WHERE c.slug = ?" : "";
    $params = $catSlug ? [$catSlug] : [];
    $stmt = db()->prepare("SELECT g.*, gc.name AS cat_name, gc.slug AS cat_slug FROM gallery g LEFT JOIN gallery_categories gc ON g.category_id=gc.id $where ORDER BY g.is_featured DESC, g.sort_order, g.created_at DESC");
    $stmt->execute($params);
    $photos = $stmt->fetchAll();
} catch(Exception $e){ $categories=[]; $photos=[]; }
?>

<style>
  #lightbox{display:none;position:fixed;inset:0;z-index:200;background:rgba(0,0,0,.95);backdrop-filter:blur(20px);align-items:center;justify-content:center;padding:20px}
  #lightbox.open{display:flex}
  #lb-img{max-height:85vh;max-width:90vw;object-fit:contain;border-radius:12px;border:1px solid rgba(255,255,255,.1)}
  .gallery-item img{transition:transform .4s ease,filter .4s ease}
  .gallery-item:hover img{transform:scale(1.06);filter:brightness(.85)}
</style>

<main class="pt-20 min-h-screen bg-gradient-to-b from-black via-zinc-950 to-black">
  <section class="page-hero py-24">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center gap-3 mb-4 reveal"><div class="h-px w-10 bg-gold-400/70"></div><span class="text-gold-300 text-xs tracking-[.25em] uppercase">Media Visual</span></div>
      <h1 class="font-display text-5xl lg:text-6xl font-light text-white reveal">Galeri <em class="text-gold-shimmer not-italic">Foto</em></h1>
      <p class="text-white/50 mt-4 font-light reveal"><?= count($photos) ?> foto<?= $catSlug ? ' dalam kategori ini' : '' ?></p>
    </div>
  </section>

  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Category filter -->
    <?php if (!empty($categories)): ?>
    <div class="flex flex-wrap gap-3 mb-10 reveal">
      <a href="?cat=" class="px-4 py-2 rounded-full text-xs tracking-wide transition-all <?= !$catSlug ? 'text-black font-medium' : 'glass text-white/60 hover:text-white hover:bg-white/12' ?>" style="<?= !$catSlug ? 'background:linear-gradient(135deg,#d4aa3a,#e8c860)' : '' ?>">Semua</a>
      <?php foreach ($categories as $cat): ?>
      <a href="?cat=<?= urlencode($cat['slug']) ?>" class="px-4 py-2 rounded-full text-xs tracking-wide transition-all <?= $catSlug===$cat['slug'] ? 'text-black font-medium' : 'glass text-white/60 hover:text-white hover:bg-white/12' ?>" style="<?= $catSlug===$cat['slug'] ? 'background:linear-gradient(135deg,#d4aa3a,#e8c860)' : '' ?>">
        <?= e($cat['name']) ?>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (empty($photos)): ?>
    <div class="glass rounded-3xl p-16 text-center"><i data-lucide="image" class="w-10 h-10 mx-auto mb-3" style="color:rgba(255,255,255,.2)"></i><p class="text-white/30">Belum ada foto dalam kategori ini.</p></div>
    <?php else: ?>
    <!-- Masonry-style grid -->
    <div class="columns-2 md:columns-3 lg:columns-4 gap-4 space-y-4">
      <?php foreach ($photos as $i=>$ph):
        $imgSrc = UPLOAD_URL . 'gallery/' . $ph['image'];
      ?>
      <div class="gallery-item break-inside-avoid rounded-2xl overflow-hidden cursor-pointer group relative reveal"
           data-src="<?= e($imgSrc) ?>" data-title="<?= e($ph['title']) ?>" data-caption="<?= e($ph['cat_name']??'') ?>"
           onclick="openLightbox(this)">
        <img src="<?= e($imgSrc) ?>" alt="<?= e($ph['title']) ?>" loading="lazy" class="w-full object-cover" style="min-height:120px">
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-4">
          <div>
            <p class="text-white text-sm font-medium line-clamp-1"><?= e($ph['title']) ?></p>
            <?php if (!empty($ph['cat_name'])): ?><p class="text-gold-300 text-xs"><?= e($ph['cat_name']) ?></p><?php endif; ?>
          </div>
          <div class="ml-auto"><i data-lucide="zoom-in" class="w-5 h-5 text-white/80"></i></div>
        </div>
        <?php if ($ph['is_featured']): ?><div class="absolute top-3 left-3"><span class="text-[10px] px-2 py-0.5 rounded-full" style="background:rgba(212,170,58,.3);color:#f0d898;border:1px solid rgba(212,170,58,.4)">★ Unggulan</span></div><?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</main>

<!-- Lightbox -->
<div id="lightbox">
  <button onclick="closeLightbox()" class="absolute top-5 right-5 w-10 h-10 glass rounded-xl flex items-center justify-center hover:bg-white/20 transition-all z-10">
    <i data-lucide="x" class="w-5 h-5"></i>
  </button>
  <button id="lb-prev" onclick="navLb(-1)" class="absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 glass rounded-xl flex items-center justify-center hover:bg-white/20 transition-all">
    <i data-lucide="chevron-left" class="w-6 h-6"></i>
  </button>
  <div class="flex flex-col items-center gap-3 max-w-5xl mx-auto">
    <img id="lb-img" src="" alt="">
    <div class="text-center">
      <p id="lb-title" class="text-white font-medium"></p>
      <p id="lb-caption" class="text-gold-400 text-sm"></p>
      <p id="lb-counter" class="text-white/30 text-xs mt-1"></p>
    </div>
  </div>
  <button id="lb-next" onclick="navLb(1)" class="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 glass rounded-xl flex items-center justify-center hover:bg-white/20 transition-all">
    <i data-lucide="chevron-right" class="w-6 h-6"></i>
  </button>
</div>

<script>
let lbItems=[], lbIdx=0;
function openLightbox(el){
  lbItems=Array.from(document.querySelectorAll('.gallery-item'));
  lbIdx=lbItems.indexOf(el);
  showLb();
  document.getElementById('lightbox').classList.add('open');
  document.body.style.overflow='hidden';
}
function showLb(){
  const el=lbItems[lbIdx];
  document.getElementById('lb-img').src=el.dataset.src;
  document.getElementById('lb-title').textContent=el.dataset.title||'';
  document.getElementById('lb-caption').textContent=el.dataset.caption||'';
  document.getElementById('lb-counter').textContent=(lbIdx+1)+' / '+lbItems.length;
}
function navLb(dir){lbIdx=(lbIdx+dir+lbItems.length)%lbItems.length;showLb();}
function closeLightbox(){document.getElementById('lightbox').classList.remove('open');document.body.style.overflow='';}
document.getElementById('lightbox').addEventListener('click',e=>{if(e.target===document.getElementById('lightbox'))closeLightbox();});
document.addEventListener('keydown',e=>{const lb=document.getElementById('lightbox');if(!lb.classList.contains('open'))return;if(e.key==='Escape')closeLightbox();if(e.key==='ArrowLeft')navLb(-1);if(e.key==='ArrowRight')navLb(1);});
</script>

<?php require_once ROOT_PATH . '../../includes/footer.php'; ?>
