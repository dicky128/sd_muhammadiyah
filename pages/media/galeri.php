<?php
require_once __DIR__.'/../../includes/config.php';
$pageTitle=$pageHeroTitle='Galeri Foto';
$pageHeroLabel='Media Visual';
$pageHeroSub='Kumpulan momen berharga kegiatan belajar, prestasi, dan kehidupan sekolah kami.';
$pageHeroColor='pink'; $activePage='galeri'; $breadcrumbParent='Media';
require_once ROOT_PATH.'header.php';

$catSlug=$_GET['cat']??'';
try {
    $categories=db()->query("SELECT * FROM gallery_categories ORDER BY sort_order")->fetchAll();
    $where=$catSlug?"WHERE c.slug=?":"";
    $params=$catSlug?[$catSlug]:[];
    $stmt=db()->prepare("SELECT g.*,gc.name AS cat_name,gc.slug AS cat_slug FROM gallery g LEFT JOIN gallery_categories gc ON g.category_id=gc.id $where ORDER BY g.is_featured DESC,g.sort_order,g.created_at DESC");
    $stmt->execute($params); $photos=$stmt->fetchAll();
} catch(Exception $e){ $categories=[]; $photos=[]; }
?>

<style>
#lightbox{display:none;position:fixed;inset:0;z-index:200;background:rgba(254,252,249,.96);backdrop-filter:blur(28px);align-items:center;justify-content:center;padding:20px}
#lightbox.open{display:flex;animation:fadeIn .25s ease}
#lb-img{max-height:82vh;max-width:88vw;object-fit:contain;border-radius:16px;box-shadow:0 20px 80px rgba(244,114,182,.25),0 5px 20px rgba(0,0,0,.08);border:1px solid rgba(244,114,182,.2)}
.gallery-item img{transition:transform .45s ease,filter .4s ease}
.gallery-item:hover img{transform:scale(1.07);filter:brightness(.9)}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
</style>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

  <!-- Category Filter -->
  <?php if(!empty($categories)): ?>
  <div class="flex flex-wrap gap-3 mb-10">
    <a href="?" class="px-4 py-2 rounded-full text-xs font-semibold transition-all <?=!$catSlug?'text-white':'glass-card text-gray-500 hover:text-pink-500'?>"
       style="<?=!$catSlug?'background:linear-gradient(135deg,#f472b6,#d4aa3a)':''?>">Semua</a>
    <?php foreach($categories as $cat): ?>
    <a href="?cat=<?=urlencode($cat['slug'])?>"
       class="px-4 py-2 rounded-full text-xs font-semibold transition-all <?=$catSlug===$cat['slug']?'text-white':'glass-card text-gray-500 hover:text-pink-500'?>"
       style="<?=$catSlug===$cat['slug']?'background:linear-gradient(135deg,#f472b6,#d4aa3a)':''?>">
      <?=e($cat['name'])?>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?php if(empty($photos)): ?>
  <div class="glass-card rounded-3xl p-16 text-center">
    <i data-lucide="image" class="w-10 h-10 mx-auto mb-3 text-pink-200"></i>
    <p class="text-gray-400">Belum ada foto<?=$catSlug?' dalam kategori ini':''?>.</p>
  </div>
  <?php else: ?>

  <!-- Masonry Grid -->
  <div class="columns-2 md:columns-3 lg:columns-4 gap-4 space-y-4">
    <?php foreach($photos as $i=>$ph):
      $imgSrc=UPLOAD_URL.'gallery/'.$ph['image'];
    ?>
    <div class="gallery-item break-inside-avoid rounded-2xl overflow-hidden cursor-pointer group relative glass-card"
         style="animation-delay:<?=$i*.04?>s"
         data-src="<?=e($imgSrc)?>" data-title="<?=e($ph['title'])?>" data-caption="<?=e($ph['cat_name']??'')?>"
         onclick="openLightbox(this)">
      <img src="<?=e($imgSrc)?>" alt="<?=e($ph['title'])?>" loading="lazy" class="w-full object-cover" style="min-height:120px">
      <!-- Hover overlay -->
      <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-3"
           style="background:linear-gradient(to top,rgba(244,114,182,.6),transparent)">
        <div class="flex-1">
          <p class="text-white text-xs font-semibold line-clamp-1"><?=e($ph['title'])?></p>
          <?php if(!empty($ph['cat_name'])): ?><p class="text-white/75 text-[10px]"><?=e($ph['cat_name'])?></p><?php endif; ?>
        </div>
        <div class="w-7 h-7 rounded-lg flex items-center justify-center ml-2 flex-shrink-0" style="background:rgba(255,255,255,.25);backdrop-filter:blur(8px)">
          <i data-lucide="zoom-in" class="w-3.5 h-3.5 text-white"></i>
        </div>
      </div>
      <?php if($ph['is_featured']): ?>
      <div class="absolute top-2 left-2">
        <span class="text-[10px] px-2 py-0.5 rounded-full font-bold" style="background:rgba(212,170,58,.25);color:#92660a;border:1px solid rgba(212,170,58,.4)">★</span>
      </div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<!-- Lightbox -->
<div id="lightbox">
  <button onclick="closeLightbox()" class="absolute top-5 right-5 w-11 h-11 rounded-2xl flex items-center justify-center transition-all hover:scale-110 z-10"
          style="background:rgba(244,114,182,.12);border:1px solid rgba(244,114,182,.25)">
    <i data-lucide="x" class="w-5 h-5 text-pink-500"></i>
  </button>
  <button id="lb-prev" onclick="navLb(-1)" class="absolute left-4 top-1/2 -translate-y-1/2 w-11 h-11 rounded-2xl flex items-center justify-center transition-all hover:scale-110"
          style="background:rgba(244,114,182,.1);border:1px solid rgba(244,114,182,.2)">
    <i data-lucide="chevron-left" class="w-5 h-5 text-pink-500"></i>
  </button>
  <div class="flex flex-col items-center gap-4 max-w-5xl mx-auto">
    <img id="lb-img" src="" alt="">
    <div class="text-center">
      <p id="lb-title" class="font-display font-semibold text-gray-800 text-lg"></p>
      <p id="lb-caption" class="text-pink-500 text-sm font-semibold"></p>
      <p id="lb-counter" class="text-gray-400 text-xs mt-1"></p>
    </div>
  </div>
  <button id="lb-next" onclick="navLb(1)" class="absolute right-4 top-1/2 -translate-y-1/2 w-11 h-11 rounded-2xl flex items-center justify-center transition-all hover:scale-110"
          style="background:rgba(244,114,182,.1);border:1px solid rgba(244,114,182,.2)">
    <i data-lucide="chevron-right" class="w-5 h-5 text-pink-500"></i>
  </button>
</div>

<script>
let lbItems=[],lbIdx=0;
function openLightbox(el){lbItems=Array.from(document.querySelectorAll('.gallery-item'));lbIdx=lbItems.indexOf(el);showLb();document.getElementById('lightbox').classList.add('open');document.body.style.overflow='hidden'}
function showLb(){const el=lbItems[lbIdx];document.getElementById('lb-img').src=el.dataset.src;document.getElementById('lb-title').textContent=el.dataset.title||'';document.getElementById('lb-caption').textContent=el.dataset.caption||'';document.getElementById('lb-counter').textContent=(lbIdx+1)+' / '+lbItems.length}
function navLb(dir){lbIdx=(lbIdx+dir+lbItems.length)%lbItems.length;showLb()}
function closeLightbox(){document.getElementById('lightbox').classList.remove('open');document.body.style.overflow=''}
document.getElementById('lightbox').addEventListener('click',e=>{if(e.target===document.getElementById('lightbox'))closeLightbox()});
document.addEventListener('keydown',e=>{const lb=document.getElementById('lightbox');if(!lb.classList.contains('open'))return;if(e.key==='Escape')closeLightbox();if(e.key==='ArrowLeft')navLb(-1);if(e.key==='ArrowRight')navLb(1)});
// Swipe
let tx=null;
document.getElementById('lightbox').addEventListener('touchstart',e=>{tx=e.touches[0].clientX},{passive:true});
document.getElementById('lightbox').addEventListener('touchend',e=>{if(tx===null)return;const d=e.changedTouches[0].clientX-tx;if(Math.abs(d)>50)navLb(d<0?1:-1);tx=null});
</script>

<?php require_once ROOT_PATH.'footer.php'; ?>