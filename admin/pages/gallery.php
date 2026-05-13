<?php
require_once __DIR__ . '/../includes/auth.php';
$activeSidebar='gallery'; $pageTitle='Kelola Galeri';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    header('Content-Type: application/json');
    if (!verify_csrf()){echo json_encode(['ok'=>false,'msg'=>'Token tidak valid.']);exit;}
    $action=$_POST['action']??'';

    if ($action==='get'){$r=db()->prepare("SELECT * FROM gallery WHERE id=?");$r->execute([(int)$_POST['id']]);echo json_encode($r->fetch());exit;}
    if ($action==='delete'){
        $r=db()->prepare("SELECT image FROM gallery WHERE id=?")->execute([(int)$_POST['id']]);
        db()->prepare("DELETE FROM gallery WHERE id=?")->execute([(int)$_POST['id']]);
        echo json_encode(['ok'=>true,'msg'=>'Foto dihapus.']);exit;
    }
    if ($action==='toggle_featured'){
        db()->prepare("UPDATE gallery SET is_featured=NOT is_featured WHERE id=?")->execute([(int)$_POST['id']]);
        $r=db()->prepare("SELECT is_featured FROM gallery WHERE id=?");$r->execute([(int)$_POST['id']]);
        echo json_encode(['ok'=>true,'status'=>(int)$r->fetch()['is_featured']]);exit;
    }
    if (in_array($action,['create','update'])) {
        $d=['title'=>trim($_POST['title']??''),'description'=>trim($_POST['description']??''),
            'category_id'=>$_POST['category_id']?:null,'is_featured'=>isset($_POST['is_featured'])?1:0,
            'sort_order'=>(int)($_POST['sort_order']??0)];
        if(!$d['title']){echo json_encode(['ok'=>false,'msg'=>'Judul wajib diisi.']);exit;}
        $image=$_POST['existing_image']??null;
        if ($action==='create' && empty($_FILES['image']['name'])){echo json_encode(['ok'=>false,'msg'=>'Foto wajib diupload.']);exit;}
        if (!empty($_FILES['image']['name'])){$u=uploadFile($_FILES['image'],'gallery');if($u)$image=$u;else{echo json_encode(['ok'=>false,'msg'=>'Gagal upload.']);exit;}}
        $d['image']=$image??'';
        try {
            if ($action==='create'){$cols=implode(',',array_map(fn($k)=>"`$k`",array_keys($d)));$pls=implode(',',array_fill(0,count($d),'?'));db()->prepare("INSERT INTO gallery ($cols) VALUES ($pls)")->execute(array_values($d));}
            else{$id=(int)$_POST['id'];$sets=implode(',',array_map(fn($k)=>"`$k`=?",array_keys($d)));db()->prepare("UPDATE gallery SET $sets WHERE id=$id")->execute(array_values($d));}
            echo json_encode(['ok'=>true,'msg'=>'Berhasil disimpan.']);exit;
        } catch(Exception $e){echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]);exit;}
    }
    echo json_encode(['ok'=>false,'msg'=>'Unknown']);exit;
}

$catFilter=(int)($_GET['cat']??0);
$where=$catFilter?'WHERE g.category_id='.$catFilter:'';
try {
    $cats=db()->query("SELECT * FROM gallery_categories ORDER BY sort_order")->fetchAll();
    $photos=db()->query("SELECT g.*,gc.name as cat_name FROM gallery g LEFT JOIN gallery_categories gc ON g.category_id=gc.id $where ORDER BY g.is_featured DESC,g.sort_order,g.created_at DESC")->fetchAll();
} catch(Exception $e){$cats=[];$photos=[];}
$csrf=csrf_token();
require_once __DIR__ . '/../includes/admin_head.php';
?>
<div class="mb-5 flex flex-wrap items-center justify-between gap-4">
  <div class="flex flex-wrap gap-2">
    <a href="?" class="px-3 py-1.5 rounded-xl text-xs transition-all <?=$catFilter===0?'text-black font-medium':'glass text-white/60 hover:bg-white/10'?>" style="<?=$catFilter===0?'background:linear-gradient(135deg,#d4aa3a,#e8c860)':''?>">Semua</a>
    <?php foreach($cats as $c): ?>
    <a href="?cat=<?=$c['id']?>" class="px-3 py-1.5 rounded-xl text-xs transition-all <?=$catFilter===(int)$c['id']?'text-black font-medium':'glass text-white/60 hover:bg-white/10'?>" style="<?=$catFilter===(int)$c['id']?'background:linear-gradient(135deg,#d4aa3a,#e8c860)':''?>"><?=e($c['name'])?></a>
    <?php endforeach; ?>
  </div>
  <button onclick="openModal()" class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm text-black font-medium" style="background:linear-gradient(135deg,#d4aa3a,#e8c860)"><i data-lucide="upload" style="width:14px;height:14px"></i> Upload Foto</button>
</div>

<?php if(empty($photos)): ?>
<div class="glass rounded-3xl p-16 text-center"><i data-lucide="image" style="width:36px;height:36px;color:rgba(255,255,255,.15);margin:0 auto 12px"></i><p style="color:rgba(255,255,255,.3)">Belum ada foto.</p></div>
<?php else: ?>
<div class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
  <?php foreach($photos as $ph): ?>
  <div class="glass rounded-xl overflow-hidden group relative">
    <div class="h-40 overflow-hidden bg-white/[.03]">
      <img src="<?=UPLOAD_URL?>gallery/<?=e($ph['image'])?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-400" loading="lazy">
    </div>
    <div class="absolute top-2 right-2 flex flex-col gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
      <button onclick="toggleFeat(<?=$ph['id']?>,this)" title="<?=$ph['is_featured']?'Unfeature':'Feature'?>" class="w-7 h-7 rounded-lg flex items-center justify-center transition-all" style="background:rgba(0,0,0,.7)"><i data-lucide="star" style="width:12px;height:12px;color:<?=$ph['is_featured']?'#f0d898':'rgba(255,255,255,.5)'?>"></i></button>
      <button onclick="editRow(<?=$ph['id']?>)" class="w-7 h-7 rounded-lg flex items-center justify-center transition-all" style="background:rgba(0,0,0,.7)"><i data-lucide="pencil" style="width:12px;height:12px;color:#93c5fd"></i></button>
      <button onclick="delRow(<?=$ph['id']?>,`<?=addslashes(e($ph['title']))?>`)" class="w-7 h-7 rounded-lg flex items-center justify-center transition-all" style="background:rgba(0,0,0,.7)"><i data-lucide="trash-2" style="width:12px;height:12px;color:#f87171"></i></button>
    </div>
    <?php if($ph['is_featured']): ?><div class="absolute top-2 left-2"><span class="text-[10px] px-2 py-0.5 rounded-full" style="background:rgba(212,170,58,.3);color:#f0d898">★</span></div><?php endif; ?>
    <div class="p-3">
      <p class="text-xs text-white/75 truncate"><?=e($ph['title'])?></p>
      <?php if($ph['cat_name']): ?><p class="text-[10px] text-white/30 mt-0.5"><?=e($ph['cat_name'])?></p><?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Modal -->
<div id="modal-overlay"><div class="modal-box">
  <div class="flex items-center justify-between px-7 py-5 border-b border-white/[.08]">
    <h2 id="modal-title" class="font-display text-2xl text-white font-light">Upload Foto</h2>
    <button onclick="closeModal()" class="w-9 h-9 glass rounded-xl flex items-center justify-center"><i data-lucide="x" style="width:15px;height:15px"></i></button>
  </div>
  <form id="row-form" enctype="multipart/form-data" class="px-7 py-6 space-y-4">
    <input type="hidden" name="csrf_token" value="<?=$csrf?>">
    <input type="hidden" name="action" id="f-action" value="create">
    <input type="hidden" name="id" id="f-id">
    <input type="hidden" name="existing_image" id="f-existing">
    <div><label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40">Judul *</label><input type="text" name="title" id="f-title" required class="input-g"></div>
    <div class="grid grid-cols-2 gap-4">
      <div><label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40">Kategori</label>
        <select name="category_id" id="f-cat" class="input-g">
          <option value="">Tanpa Kategori</option>
          <?php foreach($cats as $c): ?><option value="<?=$c['id']?>"><?=e($c['name'])?></option><?php endforeach; ?>
        </select>
      </div>
      <div><label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40">Urutan</label><input type="number" name="sort_order" id="f-sort" value="0" class="input-g"></div>
    </div>
    <div><label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40">Deskripsi</label><textarea name="description" id="f-desc" rows="2" class="input-g resize-none"></textarea></div>
    <div><label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" name="is_featured" id="f-feat" class="w-4 h-4 accent-yellow-500"><span class="text-sm text-white/70">Jadikan Foto Unggulan</span></label></div>
    <div>
      <label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40">Foto</label>
      <div id="img-preview" class="hidden mb-2 w-full h-40 rounded-xl overflow-hidden"><img id="img-prev-el" src="" class="w-full h-full object-cover"></div>
      <input type="file" name="image" id="f-image" accept="image/*" class="input-g" style="padding:6px 12px" onchange="prevImg(this)">
    </div>
    <div class="flex justify-end gap-3 pt-2 border-t border-white/[.08]">
      <button type="button" onclick="closeModal()" class="px-5 py-2.5 glass rounded-xl text-sm text-white/60 hover:text-white hover:bg-white/10 transition-all">Batal</button>
      <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-medium text-black" style="background:linear-gradient(135deg,#d4aa3a,#e8c860)">Simpan</button>
    </div>
  </form>
</div></div>

<script>
lucide.createIcons();
function showToast(i,t){Swal.fire({toast:true,position:'top-end',icon:i,title:t,showConfirmButton:false,timer:3000,timerProgressBar:true,background:'rgba(15,15,15,.97)',color:'#fff',customClass:{popup:'rounded-2xl'}})}
function openModal(){document.getElementById('modal-overlay').classList.add('open');document.body.style.overflow='hidden';document.getElementById('modal-title').textContent='Upload Foto';document.getElementById('f-action').value='create';document.getElementById('f-id').value='';document.getElementById('row-form').reset();document.getElementById('img-preview').classList.add('hidden');}
function closeModal(){document.getElementById('modal-overlay').classList.remove('open');document.body.style.overflow=''}
document.getElementById('modal-overlay').addEventListener('click',e=>{if(e.target===document.getElementById('modal-overlay'))closeModal()})
function prevImg(i){if(i.files&&i.files[0]){const r=new FileReader();r.onload=e=>{document.getElementById('img-prev-el').src=e.target.result;document.getElementById('img-preview').classList.remove('hidden')};r.readAsDataURL(i.files[0])}}
async function editRow(id){
  const res=await fetch('gallery.php',{method:'POST',body:new URLSearchParams({action:'get',id,csrf_token:'<?=$csrf?>'})});
  const d=await res.json();if(!d||!d.id)return;
  document.getElementById('modal-title').textContent='Edit Foto';
  document.getElementById('f-action').value='update';document.getElementById('f-id').value=d.id;
  document.getElementById('f-title').value=d.title||'';document.getElementById('f-cat').value=d.category_id||'';
  document.getElementById('f-desc').value=d.description||'';document.getElementById('f-sort').value=d.sort_order||0;
  document.getElementById('f-feat').checked=!!parseInt(d.is_featured);
  document.getElementById('f-existing').value=d.image||'';
  if(d.image){document.getElementById('img-prev-el').src='<?=UPLOAD_URL?>gallery/'+d.image;document.getElementById('img-preview').classList.remove('hidden');}
  else document.getElementById('img-preview').classList.add('hidden');
  openModal();document.getElementById('modal-title').textContent='Edit Foto';document.getElementById('f-action').value='update';
}
async function toggleFeat(id,btn){
  const res=await fetch('gallery.php',{method:'POST',body:new URLSearchParams({action:'toggle_featured',id,csrf_token:'<?=$csrf?>'})});
  const d=await res.json();if(d.ok){btn.querySelector('i').style.color=d.status?'#f0d898':'rgba(255,255,255,.5)';showToast('success','Status diperbarui.');}
}
function delRow(id,name){
  Swal.fire({title:'Hapus Foto?',html:`"${name}"`,icon:'warning',showCancelButton:true,confirmButtonText:'Hapus',cancelButtonText:'Batal',confirmButtonColor:'#ef4444',background:'#111',color:'#fff',customClass:{popup:'rounded-2xl',confirmButton:'rounded-xl px-5',cancelButton:'rounded-xl px-5'}}).then(async r=>{
    if(!r.isConfirmed)return;
    const res=await fetch('gallery.php',{method:'POST',body:new URLSearchParams({action:'delete',id,csrf_token:'<?=$csrf?>'})});
    const d=await res.json();if(d.ok){showToast('success',d.msg);setTimeout(()=>location.reload(),900)}else showToast('error',d.msg);
  });
}
document.getElementById('row-form').addEventListener('submit',async e=>{
  e.preventDefault();const res=await fetch('gallery.php',{method:'POST',body:new FormData(e.target)});
  const d=await res.json();if(d.ok){closeModal();showToast('success',d.msg);setTimeout(()=>location.reload(),900)}else showToast('error',d.msg||'Gagal');
})
</script>
</div></main></div></body></html>
