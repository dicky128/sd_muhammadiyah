<?php
require_once __DIR__ . '/../includes/auth.php';
$activeSidebar='complaints'; $pageTitle='Kelola Pengaduan';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    header('Content-Type: application/json');
    if (!verify_csrf()){echo json_encode(['ok'=>false,'msg'=>'Token tidak valid.']);exit;}
    $action=$_POST['action']??'';
    if ($action==='get'){$r=db()->prepare("SELECT * FROM complaints WHERE id=?");$r->execute([(int)$_POST['id']]);echo json_encode($r->fetch());exit;}
    if ($action==='update_status') {
        $id=(int)$_POST['id']; $status=$_POST['status']??''; $note=trim($_POST['admin_note']??'');
        $valid=['masuk','diproses','selesai','ditutup'];
        if (!in_array($status,$valid)){echo json_encode(['ok'=>false,'msg'=>'Status tidak valid.']);exit;}
        db()->prepare("UPDATE complaints SET status=?,admin_note=?,responded_by=?,responded_at=NOW() WHERE id=?")
           ->execute([$status,$note,$_SESSION['admin_id'],$id]);
        echo json_encode(['ok'=>true,'msg'=>'Pengaduan berhasil diperbarui.']);exit;
    }
    if ($action==='delete'){db()->prepare("DELETE FROM complaints WHERE id=?")->execute([(int)$_POST['id']]);echo json_encode(['ok'=>true,'msg'=>'Pengaduan dihapus.']);exit;}
    echo json_encode(['ok'=>false,'msg'=>'Unknown']);exit;
}

$status=trim($_GET['status']??'');
$search=trim($_GET['q']??'');
$page=max(1,(int)($_GET['p']??1));$perPage=15;$offset=($page-1)*$perPage;
$where='1=1';$params=[];
if ($status){$where.=' AND status=?';$params[]=$status;}
if ($search){$where.=' AND (name LIKE ? OR subject LIKE ? OR ticket_no LIKE ?)';$params[]="%$search%";$params[]="%$search%";$params[]="%$search%";}
try {
    $total=(int)db()->prepare("SELECT COUNT(*) FROM complaints WHERE $where")->execute($params)? db()->prepare("SELECT COUNT(*) FROM complaints WHERE $where")->execute($params) && ($countStmt=db()->prepare("SELECT COUNT(*) FROM complaints WHERE $where")) && $countStmt->execute($params) ? (int)$countStmt->fetchColumn() : 0 : 0;
    $countStmt2=db()->prepare("SELECT COUNT(*) FROM complaints WHERE $where");$countStmt2->execute($params);$total=(int)$countStmt2->fetchColumn();
    $totalPages=max(1,(int)ceil($total/$perPage));
    $stmt=db()->prepare("SELECT c.*,u.full_name as responder FROM complaints c LEFT JOIN admin_users u ON c.responded_by=u.id WHERE $where ORDER BY FIELD(c.status,'masuk','diproses','selesai','ditutup'),c.created_at DESC LIMIT $perPage OFFSET $offset");
    $stmt->execute($params);$rows=$stmt->fetchAll();
    // counts per status
    $counts=[];foreach(['masuk','diproses','selesai','ditutup'] as $s){$cs=db()->prepare("SELECT COUNT(*) FROM complaints WHERE status=?");$cs->execute([$s]);$counts[$s]=(int)$cs->fetchColumn();}
} catch(Exception $e){$rows=[];$total=0;$totalPages=1;$counts=array_fill_keys(['masuk','diproses','selesai','ditutup'],0);}
$csrf=csrf_token();
$stColor=['masuk'=>'text-red-300 bg-red-500/15 border-red-500/30','diproses'=>'text-yellow-300 bg-yellow-500/15 border-yellow-500/30','selesai'=>'text-green-300 bg-green-500/15 border-green-500/30','ditutup'=>'text-white/40 bg-white/5 border-white/15'];
require_once __DIR__ . '/../includes/admin_head.php';
?>
<!-- Status tabs -->
<div class="flex flex-wrap gap-3 mb-6">
  <a href="?" class="px-4 py-2 rounded-xl text-xs transition-all flex items-center gap-2 <?=$status===''?'text-black font-medium':'glass text-white/60 hover:bg-white/10'?>" style="<?=$status===''?'background:linear-gradient(135deg,#d4aa3a,#e8c860)':''?>">Semua <span class="text-[10px] px-1.5 py-0.5 rounded-full" style="background:rgba(255,255,255,.15)"><?=array_sum($counts)?></span></a>
  <?php foreach(['masuk'=>'Masuk','diproses'=>'Diproses','selesai'=>'Selesai','ditutup'=>'Ditutup'] as $v=>$l): ?>
  <a href="?status=<?=$v?>" class="px-4 py-2 rounded-xl text-xs transition-all flex items-center gap-2 <?=$status===$v?'text-black font-medium':'glass text-white/60 hover:bg-white/10'?>" style="<?=$status===$v?'background:linear-gradient(135deg,#d4aa3a,#e8c860)':''?>">
    <?=$l?> <?php if($counts[$v]): ?><span class="text-[10px] px-1.5 py-0.5 rounded-full" style="background:rgba(255,255,255,.15)"><?=$counts[$v]?></span><?php endif; ?>
  </a>
  <?php endforeach; ?>

  <form method="GET" class="ml-auto flex gap-2">
    <?php if($status): ?><input type="hidden" name="status" value="<?=e($status)?>"> <?php endif; ?>
    <div class="relative"><i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" style="width:13px;height:13px;color:rgba(255,255,255,.3)"></i>
      <input name="q" value="<?=e($search)?>" placeholder="Cari tiket/nama…" class="input-g pl-8" style="width:180px;padding:8px 12px 8px 30px"></div>
    <button type="submit" class="px-3 py-2 glass rounded-xl text-xs text-white/60 hover:bg-white/10 transition-all">Cari</button>
  </form>
</div>

<?php if(empty($rows)): ?>
<div class="glass rounded-3xl p-16 text-center"><i data-lucide="inbox" style="width:36px;height:36px;color:rgba(255,255,255,.15);margin:0 auto 12px"></i><p style="color:rgba(255,255,255,.3)">Tidak ada pengaduan ditemukan.</p></div>
<?php else: ?>
<div class="glass rounded-2xl overflow-hidden">
  <table class="w-full"><thead><tr style="border-bottom:1px solid rgba(255,255,255,.07)">
    <?php foreach(['Tiket','Pelapor','Subjek','Kategori','Status','Tanggal',''] as $h): ?><th class="px-5 py-3 text-left" style="font-size:.65rem;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.3);font-weight:400"><?=$h?></th><?php endforeach; ?>
  </tr></thead><tbody class="divide-y divide-white/[.04]">
  <?php foreach($rows as $c): ?>
  <tr class="group hover:bg-white/[.02] transition-colors">
    <td class="px-5 py-3 font-mono text-xs text-gold-400/80"><?=e($c['ticket_no'])?></td>
    <td class="px-5 py-3"><p class="text-sm text-white/80"><?=e($c['name'])?></p><?php if($c['phone']): ?><p class="text-xs text-white/30"><?=e($c['phone'])?></p><?php endif; ?></td>
    <td class="px-5 py-3 text-sm text-white/65 max-w-[180px] truncate"><?=e($c['subject'])?></td>
    <td class="px-5 py-3"><span class="text-[10px] px-2 py-0.5 rounded-full bg-white/5 text-white/40"><?=ucfirst($c['category'])?></span></td>
    <td class="px-5 py-3"><span class="text-[11px] px-2.5 py-1 rounded-full border <?=$stColor[$c['status']]??'text-white/40 bg-white/5 border-white/15'?>"><?=ucfirst($c['status'])?></span></td>
    <td class="px-5 py-3 text-xs text-white/30"><?=date('d/m/Y', strtotime($c['created_at']))?></td>
    <td class="px-5 py-3">
      <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
        <button onclick="viewComplaint(<?=$c['id']?>)" class="w-7 h-7 glass rounded-lg flex items-center justify-center hover:bg-blue-500/20 transition-all"><i data-lucide="eye" style="width:12px;height:12px;color:#93c5fd"></i></button>
        <button onclick="delRow(<?=$c['id']?>,`<?=addslashes(e($c['ticket_no']))?>`)" class="w-7 h-7 glass rounded-lg flex items-center justify-center hover:bg-red-500/20 transition-all"><i data-lucide="trash-2" style="width:12px;height:12px;color:#f87171"></i></button>
      </div>
    </td>
  </tr>
  <?php endforeach; ?></tbody></table>
</div>

<?php if($totalPages>1): ?>
<div class="flex justify-center gap-2 mt-5">
  <?php for($i=1;$i<=$totalPages;$i++): ?>
  <a href="?p=<?=$i?>&status=<?=urlencode($status)?>&q=<?=urlencode($search)?>" class="w-9 h-9 rounded-xl flex items-center justify-center text-sm transition-all <?=$i===$page?'text-black font-medium':'glass text-white/50 hover:text-white hover:bg-white/10'?>" style="<?=$i===$page?'background:linear-gradient(135deg,#d4aa3a,#e8c860)':''?>"><?=$i?></a>
  <?php endfor; ?>
</div>
<?php endif; ?>
<?php endif; ?>

<!-- Detail/Respond Modal -->
<div id="modal-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:100;backdrop-filter:blur(8px);align-items:flex-start;justify-content:center;padding:40px 16px;overflow-y:auto">
<div style="background:#111;border:1px solid rgba(255,255,255,.1);border-radius:24px;width:100%;max-width:640px;animation:modalIn .3s cubic-bezier(.16,1,.3,1)">
  <div class="flex items-center justify-between px-7 py-5 border-b border-white/[.08]">
    <div><h2 class="font-display text-2xl text-white font-light">Detail Pengaduan</h2><p id="m-ticket" class="text-xs text-gold-400/70 font-mono mt-0.5"></p></div>
    <button onclick="closeMod()" class="w-9 h-9 glass rounded-xl flex items-center justify-center"><i data-lucide="x" style="width:15px;height:15px"></i></button>
  </div>
  <div id="m-body" class="px-7 py-5 space-y-4">
    <div class="glass rounded-xl p-4"><div class="text-[10px] text-white/30 uppercase tracking-widest mb-1">Nama Pelapor</div><p id="m-name" class="text-sm text-white/80"></p></div>
    <div class="grid grid-cols-2 gap-4">
      <div class="glass rounded-xl p-4"><div class="text-[10px] text-white/30 uppercase tracking-widest mb-1">Email</div><p id="m-mail" class="text-sm text-white/80"></p></div>
      <div class="glass rounded-xl p-4"><div class="text-[10px] text-white/30 uppercase tracking-widest mb-1">Nomor HP</div><p id="m-phone" class="text-sm text-white/80"></p></div>
    </div>
    <div class="glass rounded-xl p-4"><div class="text-[10px] text-white/30 uppercase tracking-widest mb-1">Subjek</div><p id="m-subject" class="text-sm text-white/80"></p></div>
    <div class="glass rounded-xl p-4"><div class="text-[10px] text-white/30 uppercase tracking-widest mb-1">Isi Pengaduan</div><p id="m-message" class="text-sm text-white/65 leading-relaxed"></p></div>
    <form id="respond-form">
      <div><label class="block text-[10px] tracking-widest uppercase mb-1.5 text-white/30">Ubah Status</label>
        <select name="status" id="m-status" class="input-g">
          <option value="masuk">Masuk</option><option value="diproses">Diproses</option>
          <option value="selesai">Selesai</option><option value="ditutup">Ditutup</option>
        </select>
      </div>
      <input type="hidden" name="csrf_token" value="<?=$csrf?>"><input type="hidden" name="action" value="update_status"><input type="hidden" name="id" id="m-id">
      <div class="glass rounded-xl p-4"><div class="text-[10px] text-white/30 uppercase tracking-widest mb-1">Catatan Admin</div>
          <input type="text" name="admin_note" id="m-note" class="input-g" placeholder="Respons singkat…"></div>
      <div class="flex justify-end gap-3 mt-4">
        <button type="button" onclick="closeMod()" class="px-5 py-2.5 glass rounded-xl text-sm text-white/60 hover:text-white transition-all">Tutup</button>
        <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-medium text-black flex items-center gap-2" style="background:linear-gradient(135deg,#d4aa3a,#e8c860)"><i data-lucide="check" style="width:14px;height:14px"></i> Simpan Respons</button>
      </div>
    </form>
  </div>
</div></div>

<script>
lucide.createIcons();
function showToast(i,t){Swal.fire({toast:true,position:'top-end',icon:i,title:t,showConfirmButton:false,timer:3000,timerProgressBar:true,background:'rgba(15,15,15,.97)',color:'#fff',customClass:{popup:'rounded-2xl'}})}
function closeMod(){document.getElementById('modal-overlay').style.display='none';document.body.style.overflow=''}
async function viewComplaint(id){
  const res=await fetch('complaints.php',{method:'POST',body:new URLSearchParams({action:'get',id,csrf_token:'<?=$csrf?>'})});
  const d=await res.json();if(!d||!d.id)return;
  document.getElementById('m-ticket').textContent=d.ticket_no;
  document.getElementById('m-id').value=d.id;
  document.getElementById('m-name').textContent=d.name;
  document.getElementById('m-mail').textContent=(d.email||'');
  document.getElementById('m-phone').textContent=(d.phone||'');
  document.getElementById('m-subject').textContent=d.subject;
  document.getElementById('m-message').textContent=d.message;
  document.getElementById('m-status').value=d.status;
  document.getElementById('m-note').value=d.admin_note||'';
  document.getElementById('modal-overlay').style.display='flex';document.body.style.overflow='hidden';
}
function delRow(id,ticket){
  Swal.fire({title:'Hapus Pengaduan?',html:`Tiket: <code>${ticket}</code>`,icon:'warning',showCancelButton:true,confirmButtonText:'Hapus',cancelButtonText:'Batal',confirmButtonColor:'#ef4444',background:'#111',color:'#fff',customClass:{popup:'rounded-2xl',confirmButton:'rounded-xl px-5',cancelButton:'rounded-xl px-5'}}).then(async r=>{
    if(!r.isConfirmed)return;
    const res=await fetch('complaints.php',{method:'POST',body:new URLSearchParams({action:'delete',id,csrf_token:'<?=$csrf?>'})});
    const d=await res.json();if(d.ok){showToast('success',d.msg);setTimeout(()=>location.reload(),900)}else showToast('error',d.msg);
  });
}
document.getElementById('respond-form').addEventListener('submit',async e=>{
  e.preventDefault();const res=await fetch('complaints.php',{method:'POST',body:new FormData(e.target)});
  const d=await res.json();if(d.ok){closeMod();showToast('success',d.msg);setTimeout(()=>location.reload(),900)}else showToast('error',d.msg);
})
</script>
</div></main></div></body></html>
