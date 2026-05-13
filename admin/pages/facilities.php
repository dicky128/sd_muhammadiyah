<?php
require_once __DIR__ . '/../includes/auth.php';
$activeSidebar='facilities'; $pageTitle='Kelola Fasilitas';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    header('Content-Type: application/json');
    if (!verify_csrf()){echo json_encode(['ok'=>false,'msg'=>'Token tidak valid.']);exit;}
    $action=$_POST['action']??'';
    if ($action==='get'){$r=db()->prepare("SELECT * FROM facilities WHERE id=?");$r->execute([(int)$_POST['id']]);echo json_encode($r->fetch());exit;}
    if ($action==='delete'){db()->prepare("DELETE FROM facilities WHERE id=?")->execute([(int)$_POST['id']]);echo json_encode(['ok'=>true,'msg'=>'Fasilitas dihapus.']);exit;}
    if (in_array($action,['create','update'])) {
        $d=['name'=>trim($_POST['name']??''),'description'=>trim($_POST['description']??''),
            'icon'=>trim($_POST['icon']??''),'count'=>(int)($_POST['count']??1),
            'condition'=>$_POST['condition']??'baik','sort_order'=>(int)($_POST['sort_order']??0)];
        if(!$d['name']){echo json_encode(['ok'=>false,'msg'=>'Nama wajib diisi.']);exit;}
        $img=$_POST['existing_image']??null;
        if(!empty($_FILES['image']['name'])){$u=uploadFile($_FILES['image'],'facilities');if($u)$img=$u;}
        $d['image']=$img;
        try {
            if ($action==='create'){$cols=implode(',',array_map(fn($k)=>"`$k`",array_keys($d)));$pls=implode(',',array_fill(0,count($d),'?'));db()->prepare("INSERT INTO facilities ($cols) VALUES ($pls)")->execute(array_values($d));}
            else{$id=(int)$_POST['id'];$sets=implode(',',array_map(fn($k)=>"`$k`=?",array_keys($d)));db()->prepare("UPDATE facilities SET $sets WHERE id=$id")->execute(array_values($d));}
            echo json_encode(['ok'=>true,'msg'=>'Berhasil disimpan.']);exit;
        } catch(Exception $e){echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]);exit;}
    }
    echo json_encode(['ok'=>false,'msg'=>'Unknown']);exit;
}

try { $rows=db()->query("SELECT * FROM facilities ORDER BY sort_order,name")->fetchAll(); }
catch(Exception $e){ $rows=[]; }
$csrf=csrf_token();
$condLabel=['baik'=>'Baik','cukup'=>'Cukup','rusak_ringan'=>'Perlu Perbaikan','rusak_berat'=>'Rusak Berat'];
$condColor=['baik'=>'text-green-300 bg-green-500/15','cukup'=>'text-yellow-300 bg-yellow-500/15','rusak_ringan'=>'text-orange-300 bg-orange-500/15','rusak_berat'=>'text-red-300 bg-red-500/15'];
require_once __DIR__ . '/../includes/admin_head.php';
?>
<div class="mb-5 flex justify-end">
  <button onclick="openModal()" class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm text-black font-medium" style="background:linear-gradient(135deg,#d4aa3a,#e8c860)"><i data-lucide="plus" style="width:14px;height:14px"></i> Tambah Fasilitas</button>
</div>
<?php if(empty($rows)): ?>
<div class="glass rounded-3xl p-16 text-center"><i data-lucide="layout-grid" style="width:36px;height:36px;color:rgba(255,255,255,.15);margin:0 auto 12px"></i><p style="color:rgba(255,255,255,.3)">Belum ada data.</p></div>
<?php else: ?>
<div class="glass rounded-2xl overflow-hidden">
  <table class="w-full"><thead><tr style="border-bottom:1px solid rgba(255,255,255,.07)">
    <?php foreach(['Nama','Jumlah','Kondisi','Ikon',''] as $h): ?><th class="px-5 py-3 text-left" style="font-size:.65rem;letter-spacing:.15em;text-transform:uppercase;color:rgba(255,255,255,.3);font-weight:400"><?=$h?></th><?php endforeach; ?>
  </tr></thead><tbody class="divide-y divide-white/[.05]">
  <?php foreach($rows as $f): ?>
  <tr class="group hover:bg-white/[.02] transition-colors">
    <td class="px-5 py-3">
      <div class="flex items-center gap-3">
        <?php if(!empty($f['image'])): ?><img src="<?=UPLOAD_URL?>facilities/<?=e($f['image'])?>" class="w-10 h-10 rounded-lg object-cover"><?php else: ?>
        <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background:rgba(212,170,58,.1)"><i data-lucide="<?=e($f['icon']??'square')?>" style="width:16px;height:16px;color:#d4aa3a"></i></div><?php endif; ?>
        <div><p class="text-sm text-white/80"><?=e($f['name'])?></p><?php if($f['description']): ?><p class="text-xs text-white/30 truncate max-w-xs"><?=e(mb_substr($f['description'],0,50))?></p><?php endif; ?></div>
      </div>
    </td>
    <td class="px-5 py-3 text-sm text-white/60"><?=$f['count']?> unit</td>
    <td class="px-5 py-3"><span class="text-[11px] px-2.5 py-1 rounded-full <?=$condColor[$f['condition']]??'text-white/40 bg-white/5'?>"><?=$condLabel[$f['condition']]??ucfirst($f['condition'])?></span></td>
    <td class="px-5 py-3 text-xs text-white/30"><?=e($f['icon']??'—')?></td>
    <td class="px-5 py-3">
      <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
        <button onclick="editRow(<?=$f['id']?>)" class="w-7 h-7 glass rounded-lg flex items-center justify-center hover:bg-blue-500/20 transition-all"><i data-lucide="pencil" style="width:12px;height:12px;color:#93c5fd"></i></button>
        <button onclick="delRow(<?=$f['id']?>,`<?=addslashes(e($f['name']))?>`)" class="w-7 h-7 glass rounded-lg flex items-center justify-center hover:bg-red-500/20 transition-all"><i data-lucide="trash-2" style="width:12px;height:12px;color:#f87171"></i></button>
      </div>
    </td>
  </tr>
  <?php endforeach; ?></tbody></table>
</div>
<?php endif; ?>

<div id="modal-overlay"><div class="modal-box">
  <div class="flex items-center justify-between px-7 py-5 border-b border-white/[.08]">
    <h2 id="modal-title" class="font-display text-2xl text-white font-light">Tambah Fasilitas</h2>
    <button onclick="closeModal()" class="w-9 h-9 glass rounded-xl flex items-center justify-center"><i data-lucide="x" style="width:15px;height:15px"></i></button>
  </div>
  <form id="row-form" enctype="multipart/form-data" class="px-7 py-6 space-y-4">
    <input type="hidden" name="csrf_token" value="<?=$csrf?>"><input type="hidden" name="action" id="f-action" value="create"><input type="hidden" name="id" id="f-id"><input type="hidden" name="existing_image" id="f-ei">
    <div><label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40">Nama *</label><input type="text" name="name" id="f-name" required class="input-g"></div>
    <div class="grid grid-cols-3 gap-4">
      <div><label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40">Jumlah</label><input type="number" name="count" id="f-count" value="1" min="1" class="input-g"></div>
      <div><label class="block text-xs tracking-widets uppercase mb-1.5 text-white/40">Kondisi</label>
        <select name="condition" id="f-cond" class="input-g">
          <?php foreach($condLabel as $v=>$l): ?><option value="<?=$v?>"><?=$l?></option><?php endforeach; ?>
        </select></div>
      <div><label class="block text-xs tracking-widets uppercase mb-1.5 text-white/40">Urutan</label><input type="number" name="sort_order" id="f-sort" value="0" class="input-g"></div>
    </div>
    <div><label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40">Ikon Lucide (nama)</label><input type="text" name="icon" id="f-icon" class="input-g" placeholder="door-open, library, monitor, ..."></div>
    <div><label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40">Deskripsi</label><textarea name="description" id="f-desc" rows="2" class="input-g resize-none"></textarea></div>
    <div><label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40">Foto</label>
      <div id="img-preview" class="hidden mb-2 h-28 rounded-xl overflow-hidden"><img id="img-el" src="" class="w-full h-full object-cover"></div>
      <input type="file" name="image" accept="image/*" class="input-g" style="padding:6px 12px" onchange="prevImg(this)"></div>
    <div class="flex justify-end gap-3 pt-2 border-t border-white/[.08]">
      <button type="button" onclick="closeModal()" class="px-5 py-2.5 glass rounded-xl text-sm text-white/60 hover:text-white hover:bg-white/10 transition-all">Batal</button>
      <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-medium text-black" style="background:linear-gradient(135deg,#d4aa3a,#e8c860)">Simpan</button>
    </div>
  </form>
</div></div>

<script>
lucide.createIcons();
function showToast(i,t){Swal.fire({toast:true,position:'top-end',icon:i,title:t,showConfirmButton:false,timer:3000,timerProgressBar:true,background:'rgba(15,15,15,.97)',color:'#fff',customClass:{popup:'rounded-2xl'}})}
function openModal(){document.getElementById('modal-overlay').classList.add('open');document.body.style.overflow='hidden';}
function closeModal(){document.getElementById('modal-overlay').classList.remove('open');document.body.style.overflow='';}
document.getElementById('modal-overlay').addEventListener('click',e=>{if(e.target===document.getElementById('modal-overlay'))closeModal()})
function prevImg(i){if(i.files&&i.files[0]){const r=new FileReader();r.onload=e=>{document.getElementById('img-el').src=e.target.result;document.getElementById('img-preview').classList.remove('hidden')};r.readAsDataURL(i.files[0])}}
async function editRow(id){
  const res=await fetch('facilities.php',{method:'POST',body:new URLSearchParams({action:'get',id,csrf_token:'<?=$csrf?>'})});
  const d=await res.json();if(!d||!d.id)return;
  document.getElementById('modal-title').textContent='Edit Fasilitas';document.getElementById('f-action').value='update';document.getElementById('f-id').value=d.id;
  document.getElementById('f-name').value=d.name||'';document.getElementById('f-count').value=d.count||1;
  document.getElementById('f-cond').value=d.condition||'baik';document.getElementById('f-sort').value=d.sort_order||0;
  document.getElementById('f-icon').value=d.icon||'';document.getElementById('f-desc').value=d.description||'';
  document.getElementById('f-ei').value=d.image||'';
  if(d.image){document.getElementById('img-el').src='<?=UPLOAD_URL?>facilities/'+d.image;document.getElementById('img-preview').classList.remove('hidden');}
  else document.getElementById('img-preview').classList.add('hidden');
  openModal();document.getElementById('modal-title').textContent='Edit Fasilitas';document.getElementById('f-action').value='update';
}
function delRow(id,name){
  Swal.fire({title:'Hapus Fasilitas?',html:`"${name}"`,icon:'warning',showCancelButton:true,confirmButtonText:'Hapus',cancelButtonText:'Batal',confirmButtonColor:'#ef4444',background:'#111',color:'#fff',customClass:{popup:'rounded-2xl',confirmButton:'rounded-xl px-5',cancelButton:'rounded-xl px-5'}}).then(async r=>{
    if(!r.isConfirmed)return;
    const res=await fetch('facilities.php',{method:'POST',body:new URLSearchParams({action:'delete',id,csrf_token:'<?=$csrf?>'})});
    const d=await res.json();if(d.ok){showToast('success',d.msg);setTimeout(()=>location.reload(),900)}else showToast('error',d.msg);
  });
}
document.getElementById('row-form').addEventListener('submit',async e=>{
  e.preventDefault();const res=await fetch('facilities.php',{method:'POST',body:new FormData(e.target)});
  const d=await res.json();if(d.ok){closeModal();showToast('success',d.msg);setTimeout(()=>location.reload(),900)}else showToast('error',d.msg||'Gagal');
})
</script>
</div></main></div></body></html>
