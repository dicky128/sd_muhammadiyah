<?php
require_once __DIR__ . '/../includes/auth.php';
$activeSidebar='ekskul'; $pageTitle='Kelola Ekstrakurikuler';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    header('Content-Type: application/json');
    if (!verify_csrf()){echo json_encode(['ok'=>false,'msg'=>'Token tidak valid.']);exit;}
    $action=$_POST['action']??'';
    if ($action==='get'){$r=db()->prepare("SELECT * FROM extracurricular WHERE id=?");$r->execute([(int)$_POST['id']]);echo json_encode($r->fetch());exit;}
    if ($action==='delete'){db()->prepare("DELETE FROM extracurricular WHERE id=?")->execute([(int)$_POST['id']]);echo json_encode(['ok'=>true,'msg'=>'Ekskul dihapus.']);exit;}
    if ($action==='toggle'){db()->prepare("UPDATE extracurricular SET is_active=NOT is_active WHERE id=?")->execute([(int)$_POST['id']]);$r=db()->prepare("SELECT is_active FROM extracurricular WHERE id=?");$r->execute([(int)$_POST['id']]);echo json_encode(['ok'=>true,'status'=>(int)$r->fetch()['is_active']]);exit;}
    if (in_array($action,['create','update'])) {
        $d=['name'=>trim($_POST['name']??''),'description'=>trim($_POST['description']??''),
            'schedule'=>trim($_POST['schedule']??''),'coach'=>trim($_POST['coach']??''),
            'icon'=>trim($_POST['icon']??''),'is_active'=>isset($_POST['is_active'])?1:0,
            'sort_order'=>(int)($_POST['sort_order']??0)];
        if(!$d['name']){echo json_encode(['ok'=>false,'msg'=>'Nama wajib diisi.']);exit;}
        $img=$_POST['existing_image']??null;
        if(!empty($_FILES['image']['name'])){$u=uploadFile($_FILES['image'],'ekskul');if($u)$img=$u;}
        $d['image']=$img;
        try {
            if ($action==='create'){$cols=implode(',',array_map(fn($k)=>"`$k`",array_keys($d)));$pls=implode(',',array_fill(0,count($d),'?'));db()->prepare("INSERT INTO extracurricular ($cols) VALUES ($pls)")->execute(array_values($d));}
            else{$id=(int)$_POST['id'];$sets=implode(',',array_map(fn($k)=>"`$k`=?",array_keys($d)));db()->prepare("UPDATE extracurricular SET $sets WHERE id=$id")->execute(array_values($d));}
            echo json_encode(['ok'=>true,'msg'=>'Berhasil disimpan.']);exit;
        } catch(Exception $e){echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]);exit;}
    }
    echo json_encode(['ok'=>false,'msg'=>'Unknown']);exit;
}
try { $rows=db()->query("SELECT * FROM extracurricular ORDER BY sort_order,name")->fetchAll(); }
catch(Exception $e){ $rows=[]; }
$csrf=csrf_token();
require_once __DIR__ . '/../includes/admin_head.php';
?>
<div class="mb-5 flex justify-end">
  <button onclick="openModal()" class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm text-black font-medium" style="background:linear-gradient(135deg,#d4aa3a,#e8c860)"><i data-lucide="plus" style="width:14px;height:14px"></i> Tambah Ekskul</button>
</div>
<?php if(empty($rows)): ?>
<div class="glass rounded-3xl p-16 text-center"><i data-lucide="sparkles" style="width:36px;height:36px;color:rgba(255,255,255,.15);margin:0 auto 12px"></i><p style="color:rgba(255,255,255,.3)">Belum ada data.</p></div>
<?php else: ?>
<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
  <?php foreach($rows as $e): ?>
  <div class="glass rounded-2xl overflow-hidden group">
    <?php if(!empty($e['image'])): ?><div class="h-36 overflow-hidden"><img src="<?=UPLOAD_URL?>ekskul/<?=e($e['image'])?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform" loading="lazy"></div><?php endif; ?>
    <div class="p-5">
      <div class="flex items-start justify-between mb-2">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:rgba(212,170,58,.1)"><i data-lucide="<?=e($e['icon']??'star')?>" style="width:14px;height:14px;color:#d4aa3a"></i></div>
          <div><p class="text-sm text-white/85 font-medium"><?=e($e['name'])?></p><?php if($e['coach']): ?><p class="text-xs text-white/35"><?=e($e['coach'])?></p><?php endif; ?></div>
        </div>
        <button onclick="toggleRow(<?=$e['id']?>,this)" class="toggle-btn text-[10px] px-2.5 py-1 rounded-full border transition-all" data-status="<?=$e['is_active']?>" style="<?=$e['is_active']?'background:rgba(52,211,153,.15);border-color:rgba(52,211,153,.35);color:#6ee7b7':'background:rgba(255,255,255,.06);border-color:rgba(255,255,255,.15);color:rgba(255,255,255,.4)'?>"><?=$e['is_active']?'Aktif':'Nonaktif'?></button>
      </div>
      <?php if($e['schedule']): ?><p class="text-xs text-white/40 flex items-center gap-1.5 mt-2"><i data-lucide="clock" style="width:11px;height:11px"></i><?=e($e['schedule'])?></p><?php endif; ?>
      <div class="flex items-center gap-2 mt-3 pt-3 border-t border-white/[.07] opacity-0 group-hover:opacity-100 transition-opacity">
        <button onclick="editRow(<?=$e['id']?>)" class="flex-1 py-1.5 glass rounded-lg text-xs text-blue-300 hover:bg-blue-500/15 transition-all flex items-center justify-center gap-1"><i data-lucide="pencil" style="width:11px;height:11px"></i> Edit</button>
        <button onclick="delRow(<?=$e['id']?>,`<?=addslashes(e($e['name']))?>`)" class="flex-1 py-1.5 glass rounded-lg text-xs text-red-300 hover:bg-red-500/15 transition-all flex items-center justify-center gap-1"><i data-lucide="trash-2" style="width:11px;height:11px"></i> Hapus</button>
      </div>
    </div>
  </div>
  <?php endforeach; ?></div>
<?php endif; ?>

<div id="modal-overlay"><div class="modal-box">
  <div class="flex items-center justify-between px-7 py-5 border-b border-white/[.08]">
    <h2 id="modal-title" class="font-display text-2xl text-white font-light">Tambah Ekskul</h2>
    <button onclick="closeModal()" class="w-9 h-9 glass rounded-xl flex items-center justify-center"><i data-lucide="x" style="width:15px;height:15px"></i></button>
  </div>
  <form id="row-form" enctype="multipart/form-data" class="px-7 py-6 space-y-4">
    <input type="hidden" name="csrf_token" value="<?=$csrf?>"><input type="hidden" name="action" id="f-action" value="create"><input type="hidden" name="id" id="f-id"><input type="hidden" name="existing_image" id="f-ei">
    <div><label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40">Nama *</label><input type="text" name="name" id="f-name" required class="input-g"></div>
    <div class="grid grid-cols-2 gap-4">
      <div><label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40">Pembina</label><input type="text" name="coach" id="f-coach" class="input-g"></div>
      <div><label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40">Ikon Lucide</label><input type="text" name="icon" id="f-icon" class="input-g" placeholder="star, music, football..."></div>
    </div>
    <div><label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40">Jadwal</label><input type="text" name="schedule" id="f-sched" class="input-g" placeholder="Selasa & Kamis, 14.00–16.00"></div>
    <div><label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40">Deskripsi</label><textarea name="description" id="f-desc" rows="2" class="input-g resize-none"></textarea></div>
    <div class="grid grid-cols-2 gap-4">
      <div><label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40">Urutan</label><input type="number" name="sort_order" id="f-sort" value="0" class="input-g"></div>
      <div class="flex items-end pb-2"><label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" name="is_active" id="f-active" checked class="w-4 h-4 accent-yellow-500"><span class="text-sm text-white/70">Aktif</span></label></div>
    </div>
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
  const res=await fetch('ekskul.php',{method:'POST',body:new URLSearchParams({action:'get',id,csrf_token:'<?=$csrf?>'})});
  const d=await res.json();if(!d||!d.id)return;
  document.getElementById('modal-title').textContent='Edit Ekskul';document.getElementById('f-action').value='update';document.getElementById('f-id').value=d.id;
  document.getElementById('f-name').value=d.name||'';document.getElementById('f-coach').value=d.coach||'';document.getElementById('f-icon').value=d.icon||'';
  document.getElementById('f-sched').value=d.schedule||'';document.getElementById('f-desc').value=d.description||'';document.getElementById('f-sort').value=d.sort_order||0;
  document.getElementById('f-active').checked=!!parseInt(d.is_active);document.getElementById('f-ei').value=d.image||'';
  if(d.image){document.getElementById('img-el').src='<?=UPLOAD_URL?>ekskul/'+d.image;document.getElementById('img-preview').classList.remove('hidden');}
  else document.getElementById('img-preview').classList.add('hidden');
  openModal();
}
async function toggleRow(id,btn){
  const res=await fetch('ekskul.php',{method:'POST',body:new URLSearchParams({action:'toggle',id,csrf_token:'<?=$csrf?>'})});
  const d=await res.json();if(d.ok){const on=d.status===1;btn.textContent=on?'Aktif':'Nonaktif';btn.style.cssText=on?'background:rgba(52,211,153,.15);border-color:rgba(52,211,153,.35);color:#6ee7b7;padding:4px 10px;border-radius:9999px;font-size:.625rem;border:1px solid;transition:all .2s':'background:rgba(255,255,255,.06);border-color:rgba(255,255,255,.15);color:rgba(255,255,255,.4);padding:4px 10px;border-radius:9999px;font-size:.625rem;border:1px solid;transition:all .2s';showToast('success','Status diperbarui.');}
}
function delRow(id,name){
  Swal.fire({title:'Hapus Ekskul?',html:`"${name}"`,icon:'warning',showCancelButton:true,confirmButtonText:'Hapus',cancelButtonText:'Batal',confirmButtonColor:'#ef4444',background:'#111',color:'#fff',customClass:{popup:'rounded-2xl',confirmButton:'rounded-xl px-5',cancelButton:'rounded-xl px-5'}}).then(async r=>{
    if(!r.isConfirmed)return;
    const res=await fetch('ekskul.php',{method:'POST',body:new URLSearchParams({action:'delete',id,csrf_token:'<?=$csrf?>'})});
    const d=await res.json();if(d.ok){showToast('success',d.msg);setTimeout(()=>location.reload(),900)}else showToast('error',d.msg);
  });
}
document.getElementById('row-form').addEventListener('submit',async e=>{
  e.preventDefault();const res=await fetch('ekskul.php',{method:'POST',body:new FormData(e.target)});
  const d=await res.json();if(d.ok){closeModal();showToast('success',d.msg);setTimeout(()=>location.reload(),900)}else showToast('error',d.msg||'Gagal');
})
</script>
</div></main></div></body></html>
