<?php
require_once __DIR__ . '/../includes/auth.php';
$activeSidebar='students'; $pageTitle='Data Siswa';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    header('Content-Type: application/json');
    if (!verify_csrf()){echo json_encode(['ok'=>false,'msg'=>'Token tidak valid.']);exit;}
    $action=$_POST['action']??'';

    // ── Stats ──
    if ($action==='save_stats') {
        $year=trim($_POST['academic_year']??'');
        if (!$year){echo json_encode(['ok'=>false,'msg'=>'Tahun ajaran wajib diisi.']);exit;}
        try {
            db()->prepare("DELETE FROM student_stats WHERE academic_year=?")->execute([$year]);
            $grades=['I','II','III','IV','V','VI'];
            foreach ($grades as $g) {
                $l=(int)($_POST["l_$g"]??0); $p=(int)($_POST["p_$g"]??0);
                if($l) db()->prepare("INSERT INTO student_stats (academic_year,grade,gender,count) VALUES (?,?,?,?)")->execute([$year,$g,'L',$l]);
                if($p) db()->prepare("INSERT INTO student_stats (academic_year,grade,gender,count) VALUES (?,?,?,?)")->execute([$year,$g,'P',$p]);
            }
            echo json_encode(['ok'=>true,'msg'=>'Data siswa berhasil disimpan.']);exit;
        } catch(Exception $e){echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]);exit;}
    }

    // ── Achievements ──
    if ($action==='get_ach') {
        $r=db()->prepare("SELECT * FROM student_achievements WHERE id=?");$r->execute([(int)$_POST['id']]);echo json_encode($r->fetch());exit;
    }
    if ($action==='del_ach') {
        db()->prepare("DELETE FROM student_achievements WHERE id=?")->execute([(int)$_POST['id']]);
        echo json_encode(['ok'=>true,'msg'=>'Prestasi dihapus.']);exit;
    }
    if (in_array($action,['create_ach','update_ach'])) {
        $d=['title'=>trim($_POST['title']??''),'description'=>trim($_POST['description']??''),
            'level'=>$_POST['level']??'sekolah','year'=>$_POST['year']??null,
            'student_name'=>trim($_POST['student_name']??''),'grade'=>trim($_POST['grade']??'')];
        if(!$d['title']){echo json_encode(['ok'=>false,'msg'=>'Judul wajib diisi.']);exit;}
        $photo=$_POST['existing_photo']??null;
        if(!empty($_FILES['photo']['name'])){$u=uploadFile($_FILES['photo'],'achievements');if($u)$photo=$u;}
        $d['photo']=$photo;
        try {
            if($action==='create_ach'){
                $cols=implode(',',array_map(fn($k)=>"`$k`",array_keys($d)));
                $pls=implode(',',array_fill(0,count($d),'?'));
                db()->prepare("INSERT INTO student_achievements ($cols) VALUES ($pls)")->execute(array_values($d));
            } else {
                $id=(int)$_POST['id'];
                $sets=implode(',',array_map(fn($k)=>"`$k`=?",array_keys($d)));
                db()->prepare("UPDATE student_achievements SET $sets WHERE id=$id")->execute(array_values($d));
            }
            echo json_encode(['ok'=>true,'msg'=>'Prestasi berhasil disimpan.']);exit;
        } catch(Exception $e){echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]);exit;}
    }
    echo json_encode(['ok'=>false,'msg'=>'Unknown']);exit;
}

try {
    $years=db()->query("SELECT DISTINCT academic_year FROM student_stats ORDER BY academic_year DESC")->fetchAll(PDO::FETCH_COLUMN);
    $selYear=$_GET['year']??($years[0]??date('Y').'/'.(date('Y')+1));
    $statsRows=db()->prepare("SELECT grade,gender,count FROM student_stats WHERE academic_year=? ORDER BY grade,gender");
    $statsRows->execute([$selYear]);$statsArr=[];
    foreach($statsRows->fetchAll() as $r) $statsArr[$r['grade']][$r['gender']]=$r['count'];
    $achievements=db()->query("SELECT * FROM student_achievements ORDER BY year DESC LIMIT 20")->fetchAll();
} catch(Exception $e){$years=[];$statsArr=[];$achievements=[];$selYear=date('Y').'/'.(date('Y')+1);}
$csrf=csrf_token();
require_once __DIR__ . '/../includes/admin_head.php';
?>
<div class="space-y-8">

<!-- Stats Card -->
<div class="glass rounded-2xl p-6">
  <div class="flex items-center justify-between mb-5">
    <h2 class="font-display text-2xl text-white font-light">Rekap Per Kelas</h2>
    <div class="flex items-center gap-3">
      <select id="year-select" class="input-g" style="width:auto;padding:8px 14px" onchange="location='?year='+this.value">
        <?php foreach($years as $y): ?><option value="<?=e($y)?>" <?=$selYear===$y?'selected':''?>><?=e($y)?></option><?php endforeach; ?>
        <option value="new">+ Tahun Baru</option>
      </select>
      <button onclick="openStatsModal()" class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm text-black font-medium" style="background:linear-gradient(135deg,#d4aa3a,#e8c860)"><i data-lucide="edit" style="width:14px;height:14px"></i> Edit</button>
    </div>
  </div>
  <?php if(empty($statsArr)): ?>
  <div class="text-center py-8 text-white/30 text-sm">Belum ada data untuk tahun ini. Klik Edit untuk menambahkan.</div>
  <?php else: ?>
  <table class="w-full"><thead><tr style="border-bottom:1px solid rgba(255,255,255,.07)">
    <?php foreach(['Kelas','Laki-laki','Perempuan','Total'] as $h): ?><th class="px-4 py-3 text-left" style="font-size:.68rem;letter-spacing:.15em;text-transform:uppercase;color:rgba(255,255,255,.35);font-weight:400"><?=$h?></th><?php endforeach; ?>
  </tr></thead><tbody class="divide-y divide-white/[.05]">
  <?php $tL=0;$tP=0; foreach($statsArr as $g=>$gd):$l=$gd['L']??0;$p_=$gd['P']??0;$tL+=$l;$tP+=$p_; ?>
  <tr class="hover:bg-white/[.02] transition-colors">
    <td class="px-4 py-3 text-sm text-white/80 font-medium">Kelas <?=$g?></td>
    <td class="px-4 py-3 text-blue-300 text-sm"><?=$l?></td>
    <td class="px-4 py-3 text-pink-300 text-sm"><?=$p_?></td>
    <td class="px-4 py-3 font-display text-xl text-white font-light"><?=$l+$p_?></td>
  </tr>
  <?php endforeach; ?>
  </tbody><tfoot><tr style="border-top:1px solid rgba(255,255,255,.1)">
    <td class="px-4 py-3 text-gold-400 text-xs uppercase tracking-widest">Total</td>
    <td class="px-4 py-3 font-display text-xl text-blue-300 font-light"><?=$tL?></td>
    <td class="px-4 py-3 font-display text-xl text-pink-300 font-light"><?=$tP?></td>
    <td class="px-4 py-3 font-display text-2xl text-white font-light"><?=$tL+$tP?></td>
  </tr></tfoot></table>
  <?php endif; ?>
</div>

<!-- Achievements -->
<div class="glass rounded-2xl overflow-hidden">
  <div class="flex items-center justify-between px-6 py-4 border-b border-white/[.07]">
    <h2 class="font-display text-2xl text-white font-light">Prestasi Siswa</h2>
    <button onclick="openAchModal()" class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm text-black font-medium" style="background:linear-gradient(135deg,#d4aa3a,#e8c860)"><i data-lucide="plus" style="width:14px;height:14px"></i> Tambah</button>
  </div>
  <?php if(empty($achievements)): ?>
  <div class="p-12 text-center text-white/30 text-sm">Belum ada data prestasi.</div>
  <?php else: ?>
  <table class="w-full"><thead><tr style="border-bottom:1px solid rgba(255,255,255,.06)">
    <?php foreach(['Judul','Level','Siswa','Tahun',''] as $h): ?><th class="px-5 py-3 text-left" style="font-size:.65rem;letter-spacing:.15em;text-transform:uppercase;color:rgba(255,255,255,.3);font-weight:400"><?=$h?></th><?php endforeach; ?>
  </tr></thead>
  <tbody class="divide-y divide-white/[.04]">
  <?php
  $lc=['sekolah'=>'text-white/40 bg-white/5','kecamatan'=>'text-blue-300 bg-blue-500/15','kabupaten'=>'text-green-300 bg-green-500/15','provinsi'=>'text-yellow-300 bg-yellow-500/15','nasional'=>'text-orange-300 bg-orange-500/15','internasional'=>'text-red-300 bg-red-500/15'];
  foreach($achievements as $a): ?>
  <tr class="group hover:bg-white/[.02] transition-colors">
    <td class="px-5 py-3 text-sm text-white/80 max-w-xs truncate"><?=e($a['title'])?></td>
    <td class="px-5 py-3"><span class="text-[10px] px-2 py-0.5 rounded-full <?=$lc[$a['level']]??'text-white/40 bg-white/5'?>"><?=ucfirst($a['level'])?></span></td>
    <td class="px-5 py-3 text-sm text-white/50"><?=e($a['student_name']??'—')?></td>
    <td class="px-5 py-3 text-sm text-white/35"><?=e($a['year']??'—')?></td>
    <td class="px-5 py-3">
      <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
        <button onclick="editAch(<?=$a['id']?>)" class="w-7 h-7 glass rounded-lg flex items-center justify-center hover:bg-blue-500/20 transition-all"><i data-lucide="pencil" style="width:12px;height:12px;color:#93c5fd"></i></button>
        <button onclick="delAch(<?=$a['id']?>,`<?=addslashes(e($a['title']))?>`)" class="w-7 h-7 glass rounded-lg flex items-center justify-center hover:bg-red-500/20 transition-all"><i data-lucide="trash-2" style="width:12px;height:12px;color:#f87171"></i></button>
      </div>
    </td>
  </tr>
  <?php endforeach; ?></tbody></table>
  <?php endif; ?>
</div>
</div>

<!-- Stats Modal -->
<div id="stats-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:100;backdrop-filter:blur(8px);align-items:center;justify-content:center">
<div style="background:#111;border:1px solid rgba(255,255,255,.1);border-radius:24px;width:560px;max-width:95vw;animation:modalIn .3s cubic-bezier(.16,1,.3,1)">
  <div class="flex items-center justify-between px-7 py-5 border-b border-white/[.08]">
    <h2 class="font-display text-2xl text-white font-light">Edit Rekap Siswa</h2>
    <button onclick="closeStats()" class="w-9 h-9 glass rounded-xl flex items-center justify-center"><i data-lucide="x" style="width:15px;height:15px"></i></button>
  </div>
  <form id="stats-form" class="px-7 py-6">
    <input type="hidden" name="csrf_token" value="<?=$csrf?>"><input type="hidden" name="action" value="save_stats">
    <div class="mb-4"><label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40">Tahun Ajaran</label>
      <input type="text" name="academic_year" id="stats-year" value="<?=e($selYear)?>" placeholder="2024/2025" class="input-g"></div>
    <div class="grid grid-cols-3 gap-2 mb-2" style="font-size:.65rem;letter-spacing:.1em;text-transform:uppercase;color:rgba(255,255,255,.3)">
      <div>Kelas</div><div>Laki-laki</div><div>Perempuan</div>
    </div>
    <?php foreach(['I','II','III','IV','V','VI'] as $g): ?>
    <div class="grid grid-cols-3 gap-2 mb-2">
      <div class="flex items-center text-sm text-white/60 font-medium">Kelas <?=$g?></div>
      <input type="number" name="l_<?=$g?>" value="<?=e($statsArr[$g]['L']??0)?>" min="0" class="input-g text-center" style="padding:8px">
      <input type="number" name="p_<?=$g?>" value="<?=e($statsArr[$g]['P']??0)?>" min="0" class="input-g text-center" style="padding:8px">
    </div>
    <?php endforeach; ?>
    <div class="flex justify-end gap-3 pt-4 border-t border-white/[.08] mt-4">
      <button type="button" onclick="closeStats()" class="px-5 py-2.5 glass rounded-xl text-sm text-white/60 hover:text-white hover:bg-white/10 transition-all">Batal</button>
      <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-medium text-black" style="background:linear-gradient(135deg,#d4aa3a,#e8c860)">Simpan</button>
    </div>
  </form>
</div></div>

<!-- Achievement Modal -->
<div id="ach-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:100;backdrop-filter:blur(8px);align-items:center;justify-content:center">
<div style="background:#111;border:1px solid rgba(255,255,255,.1);border-radius:24px;width:560px;max-width:95vw;animation:modalIn .3s cubic-bezier(.16,1,.3,1)">
  <div class="flex items-center justify-between px-7 py-5 border-b border-white/[.08]">
    <h2 id="ach-title" class="font-display text-2xl text-white font-light">Tambah Prestasi</h2>
    <button onclick="closeAch()" class="w-9 h-9 glass rounded-xl flex items-center justify-center"><i data-lucide="x" style="width:15px;height:15px"></i></button>
  </div>
  <form id="ach-form" enctype="multipart/form-data" class="px-7 py-6 space-y-4">
    <input type="hidden" name="csrf_token" value="<?=$csrf?>">
    <input type="hidden" name="action" id="ach-action" value="create_ach">
    <input type="hidden" name="id" id="ach-id">
    <input type="hidden" name="existing_photo" id="ach-ep">
    <div><label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40">Judul Prestasi *</label><input type="text" name="title" id="ach-ttl" required class="input-g"></div>
    <div class="grid grid-cols-2 gap-4">
      <div><label class="block text-xs tracking-widets uppercase mb-1.5 text-white/40">Level</label>
        <select name="level" id="ach-lvl" class="input-g">
          <?php foreach(['sekolah','kecamatan','kabupaten','provinsi','nasional','internasional'] as $l): ?>
          <option value="<?=$l?>"><?=ucfirst($l)?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div><label class="block text-xs tracking-widets uppercase mb-1.5 text-white/40">Tahun</label><input type="number" name="year" id="ach-yr" class="input-g" placeholder="<?=date('Y')?>"></div>
    </div>
    <div class="grid grid-cols-2 gap-4">
      <div><label class="block text-xs tracking-widets uppercase mb-1.5 text-white/40">Nama Siswa</label><input type="text" name="student_name" id="ach-sn" class="input-g"></div>
      <div><label class="block text-xs tracking-widets uppercase mb-1.5 text-white/40">Kelas</label><input type="text" name="grade" id="ach-gr" class="input-g" placeholder="VI A"></div>
    </div>
    <div><label class="block text-xs tracking-widets uppercase mb-1.5 text-white/40">Deskripsi</label><textarea name="description" id="ach-desc" rows="2" class="input-g resize-none"></textarea></div>
    <div><label class="block text-xs tracking-widets uppercase mb-1.5 text-white/40">Foto</label><input type="file" name="photo" accept="image/*" class="input-g" style="padding:6px 12px"></div>
    <div class="flex justify-end gap-3 pt-2 border-t border-white/[.08]">
      <button type="button" onclick="closeAch()" class="px-5 py-2.5 glass rounded-xl text-sm text-white/60 hover:text-white hover:bg-white/10 transition-all">Batal</button>
      <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-medium text-black" style="background:linear-gradient(135deg,#d4aa3a,#e8c860)">Simpan</button>
    </div>
  </form>
</div></div>

<script>
lucide.createIcons();
function showToast(i,t){Swal.fire({toast:true,position:'top-end',icon:i,title:t,showConfirmButton:false,timer:3000,timerProgressBar:true,background:'rgba(15,15,15,.97)',color:'#fff',customClass:{popup:'rounded-2xl'}})}
function openStatsModal(){document.getElementById('stats-overlay').style.display='flex';document.body.style.overflow='hidden'}
function closeStats(){document.getElementById('stats-overlay').style.display='none';document.body.style.overflow=''}
function openAchModal(mode='create'){document.getElementById('ach-overlay').style.display='flex';document.body.style.overflow='hidden';if(mode==='create'){document.getElementById('ach-title').textContent='Tambah Prestasi';document.getElementById('ach-action').value='create_ach';document.getElementById('ach-id').value='';['ach-ttl','ach-sn','ach-gr','ach-desc'].forEach(id=>document.getElementById(id).value='');document.getElementById('ach-lvl').value='sekolah';document.getElementById('ach-yr').value='';}}
function closeAch(){document.getElementById('ach-overlay').style.display='none';document.body.style.overflow=''}
async function editAch(id){
  const res=await fetch('students.php',{method:'POST',body:new URLSearchParams({action:'get_ach',id,csrf_token:'<?=$csrf?>'})});
  const d=await res.json();if(!d)return;
  document.getElementById('ach-title').textContent='Edit Prestasi';
  document.getElementById('ach-action').value='update_ach';
  document.getElementById('ach-id').value=d.id;
  document.getElementById('ach-ep').value=d.photo||'';
  document.getElementById('ach-ttl').value=d.title||'';
  document.getElementById('ach-lvl').value=d.level||'sekolah';
  document.getElementById('ach-yr').value=d.year||'';
  document.getElementById('ach-sn').value=d.student_name||'';
  document.getElementById('ach-gr').value=d.grade||'';
  document.getElementById('ach-desc').value=d.description||'';
  openAchModal('edit');
}
function delAch(id,name){
  Swal.fire({title:'Hapus Prestasi?',html:`"${name}"`,icon:'warning',showCancelButton:true,confirmButtonText:'Hapus',cancelButtonText:'Batal',confirmButtonColor:'#ef4444',background:'#111',color:'#fff',customClass:{popup:'rounded-2xl',confirmButton:'rounded-xl px-5',cancelButton:'rounded-xl px-5'}}).then(async r=>{
    if(!r.isConfirmed)return;
    const res=await fetch('students.php',{method:'POST',body:new URLSearchParams({action:'del_ach',id,csrf_token:'<?=$csrf?>'})});
    const d=await res.json();if(d.ok){showToast('success',d.msg);setTimeout(()=>location.reload(),900)}else showToast('error',d.msg);
  });
}
document.getElementById('stats-form').addEventListener('submit',async e=>{
  e.preventDefault();
  const res=await fetch('students.php',{method:'POST',body:new FormData(e.target)});
  const d=await res.json();if(d.ok){closeStats();showToast('success',d.msg);setTimeout(()=>location.reload(),900)}else showToast('error',d.msg);
})
document.getElementById('ach-form').addEventListener('submit',async e=>{
  e.preventDefault();
  const res=await fetch('students.php',{method:'POST',body:new FormData(e.target)});
  const d=await res.json();if(d.ok){closeAch();showToast('success',d.msg);setTimeout(()=>location.reload(),900)}else showToast('error',d.msg);
})
</script>
</div></main></div></body></html>
