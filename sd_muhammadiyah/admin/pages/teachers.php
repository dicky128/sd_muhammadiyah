<?php
require_once __DIR__ . '/../includes/auth.php';
$activeSidebar='teachers'; $pageTitle='Guru & Staff';

// ── AJAX handler ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD']==='POST') {
    header('Content-Type: application/json');
    if (!verify_csrf()) { echo json_encode(['ok'=>false,'msg'=>'Token tidak valid.']); exit; }
    $action = $_POST['action'] ?? '';

    if ($action==='get') {
        $row = db()->prepare("SELECT * FROM teachers WHERE id=?");
        $row->execute([(int)$_POST['id']]); echo json_encode($row->fetch()); exit;
    }
    if ($action==='delete') {
        db()->prepare("DELETE FROM teachers WHERE id=?")->execute([(int)$_POST['id']]);
        echo json_encode(['ok'=>true,'msg'=>'Data berhasil dihapus.']); exit;
    }
    if ($action==='toggle') {
        db()->prepare("UPDATE teachers SET is_active = NOT is_active WHERE id=?")->execute([(int)$_POST['id']]);
        $r=db()->prepare("SELECT is_active FROM teachers WHERE id=?");$r->execute([(int)$_POST['id']]);
        echo json_encode(['ok'=>true,'status'=>(int)$r->fetch()['is_active']]); exit;
    }
    if (in_array($action,['create','update'])) {
        $d = [
            'nip'       => trim($_POST['nip']??''),
            'full_name' => trim($_POST['full_name']??''),
            'nickname'  => trim($_POST['nickname']??''),
            'gender'    => $_POST['gender']??'L',
            'education' => trim($_POST['education']??''),
            'subject'   => trim($_POST['subject']??''),
            'position'  => trim($_POST['position']??''),
            'type'      => $_POST['type']??'guru',
            'bio'       => trim($_POST['bio']??''),
            'sort_order'=> (int)($_POST['sort_order']??0),
            'is_active' => isset($_POST['is_active'])?1:0,
        ];
        if (!$d['full_name']) { echo json_encode(['ok'=>false,'msg'=>'Nama wajib diisi.']); exit; }
        $photo = $_POST['existing_photo']??null;
        if (!empty($_FILES['photo']['name'])) {
            $u=uploadFile($_FILES['photo'],'teachers');
            if ($u) $photo=$u; else { echo json_encode(['ok'=>false,'msg'=>'Gagal upload foto.']); exit; }
        }
        $d['photo']=$photo;
        try {
            if ($action==='create') {
                $cols=implode(',',array_map(fn($k)=>"`$k`",array_keys($d)));
                $pls=implode(',',array_fill(0,count($d),'?'));
                db()->prepare("INSERT INTO teachers ($cols) VALUES ($pls)")->execute(array_values($d));
            } else {
                $id=(int)$_POST['id'];
                $sets=implode(',',array_map(fn($k)=>"`$k`=?",array_keys($d)));
                db()->prepare("UPDATE teachers SET $sets WHERE id=$id")->execute(array_values($d));
            }
            echo json_encode(['ok'=>true,'msg'=>'Data berhasil disimpan.']); exit;
        } catch(Exception $e){ echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]); exit; }
    }
    echo json_encode(['ok'=>false,'msg'=>'Unknown action']); exit;
}

$type   = $_GET['type'] ?? '';
$search = trim($_GET['q'] ?? '');
$where  = '1=1'; $params=[];
if ($type)   { $where.=' AND type=?'; $params[]=$type; }
if ($search) { $where.=' AND full_name LIKE ?'; $params[]="%$search%"; }
try {
    $rows = db()->prepare("SELECT * FROM teachers WHERE $where ORDER BY sort_order,full_name");
    $rows->execute($params); $teachers=$rows->fetchAll();
} catch(Exception $e){ $teachers=[]; }
$csrf=csrf_token();
require_once __DIR__ . '/../includes/admin_head.php';
?>
<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
  <form method="GET" class="flex gap-3 flex-wrap">
    <div class="relative"><i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" style="width:14px;height:14px;color:rgba(255,255,255,.3)"></i>
      <input name="q" value="<?=e($search)?>" placeholder="Cari nama…" class="input-g pl-9" style="width:200px;padding:8px 12px 8px 34px">
    </div>
    <?php foreach([''=> 'Semua','guru'=>'Guru','staff'=>'Staff'] as $v=>$l): ?>
    <a href="?type=<?=$v?>&q=<?=urlencode($search)?>" class="px-4 py-2 rounded-xl text-xs transition-all <?=$type===$v?'text-black font-medium':'glass text-white/60 hover:bg-white/10'?>" style="<?=$type===$v?'background:linear-gradient(135deg,#d4aa3a,#e8c860)':''?>"><?=$l?></a>
    <?php endforeach; ?>
  </form>
  <button onclick="openModal()" class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm text-black font-medium hover:scale-105 transition-all" style="background:linear-gradient(135deg,#d4aa3a,#e8c860)">
    <i data-lucide="plus" style="width:15px;height:15px"></i> Tambah
  </button>
</div>

<?php if(empty($teachers)): ?>
<div class="glass rounded-3xl p-16 text-center"><i data-lucide="users" style="width:36px;height:36px;color:rgba(255,255,255,.15);margin:0 auto 12px"></i><p style="color:rgba(255,255,255,.3)">Belum ada data.</p></div>
<?php else: ?>
<div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
  <?php foreach($teachers as $t): ?>
  <div class="glass rounded-2xl overflow-hidden group">
    <div class="h-36 relative" style="background:rgba(212,170,58,.05)">
      <?php if(!empty($t['photo'])): ?><img src="<?=UPLOAD_URL?>teachers/<?=e($t['photo'])?>" class="w-full h-full object-cover object-top" loading="lazy">
      <?php else: ?><div class="w-full h-full flex items-center justify-center"><span class="font-display text-4xl text-gold-400/30"><?=strtoupper(substr($t['full_name'],0,1))?></span></div><?php endif; ?>
      <div class="absolute top-2 right-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
        <button onclick="editRow(<?=$t['id']?>)" class="w-7 h-7 rounded-lg flex items-center justify-center hover:bg-blue-500/30 transition-all" style="background:rgba(0,0,0,.6)"><i data-lucide="pencil" style="width:12px;height:12px;color:#93c5fd"></i></button>
        <button onclick="deleteRow(<?=$t['id']?>,`<?=addslashes(e($t['full_name']))?>`)" class="w-7 h-7 rounded-lg flex items-center justify-center hover:bg-red-500/30 transition-all" style="background:rgba(0,0,0,.6)"><i data-lucide="trash-2" style="width:12px;height:12px;color:#f87171"></i></button>
      </div>
      <?php if(!$t['is_active']): ?><div class="absolute bottom-2 left-2"><span class="text-[10px] px-2 py-0.5 rounded-full" style="background:rgba(239,68,68,.25);color:#fca5a5">Nonaktif</span></div><?php endif; ?>
    </div>
    <div class="p-4">
      <p class="text-sm text-white/85 font-medium truncate"><?=e($t['full_name'])?></p>
      <?php if($t['subject']): ?><p class="text-gold-400 text-xs"><?=e($t['subject'])?></p><?php endif; ?>
      <?php if($t['position']): ?><p class="text-white/35 text-xs mt-1"><?=e($t['position'])?></p><?php endif; ?>
      <div class="flex items-center justify-between mt-2">
        <span class="text-[10px] px-2 py-0.5 rounded-full" style="background:rgba(255,255,255,.07);color:rgba(255,255,255,.4)"><?=ucfirst($t['type'])?></span>
        <span class="text-[10px] text-white/25"><?=$t['gender']==='L'?'Laki-laki':'Perempuan'?></span>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Modal -->
<div id="modal-overlay"><div class="modal-box">
  <div class="flex items-center justify-between px-7 py-5 border-b border-white/[.08]">
    <h2 id="modal-title" class="font-display text-2xl text-white font-light">Tambah Data</h2>
    <button onclick="closeModal()" class="w-9 h-9 glass rounded-xl flex items-center justify-center hover:bg-white/15"><i data-lucide="x" style="width:15px;height:15px"></i></button>
  </div>
  <form id="row-form" enctype="multipart/form-data" class="px-7 py-6 space-y-4">
    <input type="hidden" name="csrf_token" value="<?=$csrf?>">
    <input type="hidden" name="action" id="f-action" value="create">
    <input type="hidden" name="id" id="f-id">
    <input type="hidden" name="existing_photo" id="f-existing-photo">
    <div class="grid sm:grid-cols-2 gap-4">
      <div class="sm:col-span-2"><label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40">Nama Lengkap *</label><input type="text" name="full_name" id="f-name" required class="input-g"></div>
      <div><label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40">NIP</label><input type="text" name="nip" id="f-nip" class="input-g"></div>
      <div><label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40">Nama Panggilan</label><input type="text" name="nickname" id="f-nick" class="input-g"></div>
      <div><label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40">Jenis Kelamin</label>
        <select name="gender" id="f-gender" class="input-g"><option value="L">Laki-laki</option><option value="P">Perempuan</option></select></div>
      <div><label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40">Tipe</label>
        <select name="type" id="f-type" class="input-g"><option value="guru">Guru</option><option value="staff">Staff</option></select></div>
      <div><label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40">Pendidikan</label><input type="text" name="education" id="f-edu" class="input-g" placeholder="S1/S2 ..."></div>
      <div><label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40">Mata Pelajaran</label><input type="text" name="subject" id="f-subject" class="input-g"></div>
      <div class="sm:col-span-2"><label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40">Jabatan</label><input type="text" name="position" id="f-pos" class="input-g" placeholder="Wali Kelas, Kepala Sekolah, dll"></div>
      <div class="sm:col-span-2"><label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40">Bio Singkat</label><textarea name="bio" id="f-bio" rows="2" class="input-g resize-none"></textarea></div>
      <div><label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40">Urutan</label><input type="number" name="sort_order" id="f-sort" value="0" class="input-g"></div>
      <div class="flex items-end pb-2"><label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" name="is_active" id="f-active" checked class="w-4 h-4 accent-yellow-500"><span class="text-sm text-white/70">Aktif</span></label></div>
      <div class="sm:col-span-2"><label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40">Foto</label>
        <div id="photo-preview" class="hidden mb-2 w-20 h-20 rounded-xl overflow-hidden"><img id="photo-img" src="" class="w-full h-full object-cover"></div>
        <input type="file" name="photo" accept="image/*" class="input-g" style="padding:6px 12px" onchange="prevPhoto(this)"></div>
    </div>
    <div class="flex justify-end gap-3 pt-2 border-t border-white/[.08]">
      <button type="button" onclick="closeModal()" class="px-5 py-2.5 glass rounded-xl text-sm text-white/60 hover:text-white hover:bg-white/10 transition-all">Batal</button>
      <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-medium text-black hover:scale-105 transition-all flex items-center gap-2" style="background:linear-gradient(135deg,#d4aa3a,#e8c860)">
        <i data-lucide="save" style="width:14px;height:14px"></i> Simpan
      </button>
    </div>
  </form>
</div></div>

<script>
lucide.createIcons();
function showToast(i,t){Swal.fire({toast:true,position:'top-end',icon:i,title:t,showConfirmButton:false,timer:3000,timerProgressBar:true,background:'rgba(15,15,15,.97)',color:'#fff',customClass:{popup:'rounded-2xl'}})}
function openModal(){document.getElementById('modal-overlay').classList.add('open');document.body.style.overflow='hidden'}
function closeModal(){document.getElementById('modal-overlay').classList.remove('open');document.body.style.overflow=''}
document.getElementById('modal-overlay').addEventListener('click',e=>{if(e.target===document.getElementById('modal-overlay'))closeModal()})
function prevPhoto(i){if(i.files&&i.files[0]){const r=new FileReader();r.onload=e=>{document.getElementById('photo-img').src=e.target.result;document.getElementById('photo-preview').classList.remove('hidden')};r.readAsDataURL(i.files[0])}}
async function editRow(id){
  const res=await fetch('teachers.php',{method:'POST',body:new URLSearchParams({action:'get',id,csrf_token:'<?=$csrf?>'})});
  const d=await res.json(); if(!d||!d.id)return;
  document.getElementById('modal-title').textContent='Edit Data';
  document.getElementById('f-action').value='update';
  document.getElementById('f-id').value=d.id;
  document.getElementById('f-name').value=d.full_name||'';
  document.getElementById('f-nip').value=d.nip||'';
  document.getElementById('f-nick').value=d.nickname||'';
  document.getElementById('f-gender').value=d.gender||'L';
  document.getElementById('f-type').value=d.type||'guru';
  document.getElementById('f-edu').value=d.education||'';
  document.getElementById('f-subject').value=d.subject||'';
  document.getElementById('f-pos').value=d.position||'';
  document.getElementById('f-bio').value=d.bio||'';
  document.getElementById('f-sort').value=d.sort_order||0;
  document.getElementById('f-active').checked=!!parseInt(d.is_active);
  document.getElementById('f-existing-photo').value=d.photo||'';
  if(d.photo){document.getElementById('photo-img').src='<?=UPLOAD_URL?>teachers/'+d.photo;document.getElementById('photo-preview').classList.remove('hidden')}
  else document.getElementById('photo-preview').classList.add('hidden');
  openModal();
}
function deleteRow(id,name){
  Swal.fire({title:'Hapus Data?',html:`<span style="color:rgba(255,255,255,.6)">"${name}"</span>`,icon:'warning',showCancelButton:true,confirmButtonText:'Hapus',cancelButtonText:'Batal',confirmButtonColor:'#ef4444',background:'#111',color:'#fff',customClass:{popup:'rounded-2xl',confirmButton:'rounded-xl px-5',cancelButton:'rounded-xl px-5'}}).then(async r=>{
    if(!r.isConfirmed)return;
    const res=await fetch('teachers.php',{method:'POST',body:new URLSearchParams({action:'delete',id,csrf_token:'<?=$csrf?>'})});
    const d=await res.json();if(d.ok){showToast('success',d.msg);setTimeout(()=>location.reload(),900)}else showToast('error',d.msg);
  })
}
document.getElementById('row-form').addEventListener('submit',async e=>{
  e.preventDefault();
  const btn=e.submitter;btn.disabled=true;btn.textContent='Menyimpan…';
  const res=await fetch('teachers.php',{method:'POST',body:new FormData(e.target)});
  const d=await res.json();
  if(d.ok){closeModal();showToast('success',d.msg);setTimeout(()=>location.reload(),900)}
  else showToast('error',d.msg||'Gagal');
  btn.disabled=false;btn.innerHTML='<i data-lucide="save" style="width:14px;height:14px"></i> Simpan';lucide.createIcons();
})
</script>
</div></main></div></body></html>
