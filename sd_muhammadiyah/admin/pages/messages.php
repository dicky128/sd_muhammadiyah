<?php
require_once __DIR__ . '/../includes/auth.php';
$activeSidebar='messages'; $pageTitle='Pesan Masuk';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    header('Content-Type: application/json');
    if (!verify_csrf()){echo json_encode(['ok'=>false,'msg'=>'Token tidak valid.']);exit;}
    $action=$_POST['action']??'';
    if ($action==='get'){
        $r=db()->prepare("SELECT * FROM contact_messages WHERE id=?");$r->execute([(int)$_POST['id']]);$row=$r->fetch();
        if ($row) db()->prepare("UPDATE contact_messages SET is_read=1 WHERE id=?")->execute([$row['id']]);
        echo json_encode($row);exit;
    }
    if ($action==='delete'){db()->prepare("DELETE FROM contact_messages WHERE id=?")->execute([(int)$_POST['id']]);echo json_encode(['ok'=>true,'msg'=>'Pesan dihapus.']);exit;}
    if ($action==='mark_all_read'){db()->exec("UPDATE contact_messages SET is_read=1");echo json_encode(['ok'=>true,'msg'=>'Semua pesan ditandai sudah dibaca.']);exit;}
    echo json_encode(['ok'=>false,'msg'=>'Unknown']);exit;
}

$filter=$_GET['filter']??'';
$search=trim($_GET['q']??'');
$page=max(1,(int)($_GET['p']??1));$perPage=15;$offset=($page-1)*$perPage;
$where='1=1';$params=[];
if ($filter==='unread'){$where.=' AND is_read=0';}
if ($search){$where.=' AND (name LIKE ? OR subject LIKE ? OR email LIKE ?)';$params[]="%$search%";$params[]="%$search%";$params[]="%$search%";}
try {
    $cTotal=db()->prepare("SELECT COUNT(*) FROM contact_messages WHERE $where");$cTotal->execute($params);$total=(int)$cTotal->fetchColumn();
    $totalPages=max(1,(int)ceil($total/$perPage));
    $stmt=db()->prepare("SELECT * FROM contact_messages WHERE $where ORDER BY is_read ASC, created_at DESC LIMIT $perPage OFFSET $offset");
    $stmt->execute($params);$rows=$stmt->fetchAll();
    $unreadCount=(int)db()->query("SELECT COUNT(*) FROM contact_messages WHERE is_read=0")->fetchColumn();
} catch(Exception $e){$rows=[];$total=0;$totalPages=1;$unreadCount=0;}
$csrf=csrf_token();
require_once __DIR__ . '/../includes/admin_head.php';
?>
<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
  <div class="flex gap-3">
    <a href="?" class="px-4 py-2 rounded-xl text-xs flex items-center gap-2 transition-all <?=$filter===''?'text-black font-medium':'glass text-white/60 hover:bg-white/10'?>" style="<?=$filter===''?'background:linear-gradient(135deg,#d4aa3a,#e8c860)':''?>">Semua <span class="text-[10px] px-1.5 py-0.5 rounded-full" style="background:rgba(255,255,255,.15)"><?=$total?></span></a>
    <a href="?filter=unread" class="px-4 py-2 rounded-xl text-xs flex items-center gap-2 transition-all <?=$filter==='unread'?'text-black font-medium':'glass text-white/60 hover:bg-white/10'?>" style="<?=$filter==='unread'?'background:linear-gradient(135deg,#d4aa3a,#e8c860)':''?>">Belum Dibaca <?php if($unreadCount): ?><span class="text-[10px] px-1.5 py-0.5 rounded-full" style="background:rgba(239,68,68,.25);color:#fca5a5"><?=$unreadCount?></span><?php endif; ?></a>
  </div>
  <div class="flex gap-3">
    <form method="GET" class="flex gap-2">
      <?php if($filter): ?><input type="hidden" name="filter" value="<?=e($filter)?>"> <?php endif; ?>
      <div class="relative"><i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" style="width:13px;height:13px;color:rgba(255,255,255,.3)"></i>
        <input name="q" value="<?=e($search)?>" placeholder="Cari pesan…" class="input-g pl-8" style="width:180px;padding:8px 12px 8px 30px"></div>
      <button type="submit" class="px-3 py-2 glass rounded-xl text-xs text-white/60 hover:bg-white/10 transition-all">Cari</button>
    </form>
    <?php if($unreadCount>0): ?>
    <button onclick="markAllRead()" class="px-4 py-2 glass rounded-xl text-xs text-white/60 hover:text-white hover:bg-white/10 transition-all flex items-center gap-1.5"><i data-lucide="check-check" style="width:13px;height:13px"></i> Tandai Semua</button>
    <?php endif; ?>
  </div>
</div>

<?php if(empty($rows)): ?>
<div class="glass rounded-3xl p-16 text-center"><i data-lucide="mail-open" style="width:36px;height:36px;color:rgba(255,255,255,.15);margin:0 auto 12px"></i><p style="color:rgba(255,255,255,.3)">Tidak ada pesan.</p></div>
<?php else: ?>
<div class="glass rounded-2xl overflow-hidden">
  <table class="w-full"><thead><tr style="border-bottom:1px solid rgba(255,255,255,.07)">
    <?php foreach(['','Pengirim','Subjek','Tanggal',''] as $h): ?><th class="px-5 py-3 text-left" style="font-size:.65rem;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.3);font-weight:400"><?=$h?></th><?php endforeach; ?>
  </tr></thead>
  <tbody class="divide-y divide-white/[.04]">
  <?php foreach($rows as $m): ?>
  <tr class="group hover:bg-white/[.02] transition-colors <?=!$m['is_read']?'':'opacity-60'?>">
    <td class="px-5 py-3 w-6">
      <?php if(!$m['is_read']): ?><div class="w-2 h-2 rounded-full" style="background:#d4aa3a"></div><?php endif; ?>
    </td>
    <td class="px-5 py-3">
      <p class="text-sm <?=$m['is_read']?'text-white/60':'text-white/90 font-medium'?>"><?=e($m['name'])?></p>
      <p class="text-xs text-white/30"><?=e($m['email'])?></p>
    </td>
    <td class="px-5 py-3 text-sm <?=$m['is_read']?'text-white/50':'text-white/75'?> max-w-[220px] truncate"><?=e($m['subject'])?></td>
    <td class="px-5 py-3 text-xs text-white/30"><?=date('d/m/Y H:i',strtotime($m['created_at']))?></td>
    <td class="px-5 py-3">
      <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
        <button onclick="viewMsg(<?=$m['id']?>)" class="w-7 h-7 glass rounded-lg flex items-center justify-center hover:bg-blue-500/20 transition-all"><i data-lucide="eye" style="width:12px;height:12px;color:#93c5fd"></i></button>
        <button onclick="delMsg(<?=$m['id']?>,`<?=addslashes(e($m['name']))?>`)" class="w-7 h-7 glass rounded-lg flex items-center justify-center hover:bg-red-500/20 transition-all"><i data-lucide="trash-2" style="width:12px;height:12px;color:#f87171"></i></button>
      </div>
    </td>
  </tr>
  <?php endforeach; ?></tbody></table>
</div>
<?php if($totalPages>1): ?>
<div class="flex justify-center gap-2 mt-5">
  <?php for($i=1;$i<=$totalPages;$i++): ?>
  <a href="?p=<?=$i?>&filter=<?=urlencode($filter)?>&q=<?=urlencode($search)?>" class="w-9 h-9 rounded-xl flex items-center justify-center text-sm transition-all <?=$i===$page?'text-black font-medium':'glass text-white/50 hover:text-white hover:bg-white/10'?>" style="<?=$i===$page?'background:linear-gradient(135deg,#d4aa3a,#e8c860)':''?>"><?=$i?></a>
  <?php endfor; ?>
</div>
<?php endif; ?>
<?php endif; ?>

<!-- View modal -->
<div id="modal-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:100;backdrop-filter:blur(8px);align-items:center;justify-content:center;padding:20px">
<div style="background:#111;border:1px solid rgba(255,255,255,.1);border-radius:24px;width:100%;max-width:560px;animation:modalIn .3s cubic-bezier(.16,1,.3,1)">
  <div class="flex items-center justify-between px-7 py-5 border-b border-white/[.08]">
    <h2 class="font-display text-2xl text-white font-light">Isi Pesan</h2>
    <button onclick="closeMod()" class="w-9 h-9 glass rounded-xl flex items-center justify-center"><i data-lucide="x" style="width:15px;height:15px"></i></button>
  </div>
  <div class="px-7 py-6 space-y-4">
    <div class="grid grid-cols-2 gap-3">
      <div class="glass rounded-xl p-4"><div class="text-[10px] text-white/30 uppercase tracking-widest mb-1">Dari</div><p id="m-name" class="text-sm text-white/85"></p></div>
      <div class="glass rounded-xl p-4"><div class="text-[10px] text-white/30 uppercase tracking-widest mb-1">Email</div><p id="m-email" class="text-sm text-white/85"></p></div>
    </div>
    <div class="glass rounded-xl p-4"><div class="text-[10px] text-white/30 uppercase tracking-widest mb-1">Subjek</div><p id="m-subject" class="text-sm text-white/85"></p></div>
    <div class="glass rounded-xl p-4"><div class="text-[10px] text-white/30 uppercase tracking-widets mb-1">Pesan</div><p id="m-message" class="text-sm text-white/65 leading-relaxed whitespace-pre-wrap"></p></div>
    <div class="flex items-center justify-between pt-2">
      <p id="m-date" class="text-xs text-white/25"></p>
      <a id="m-reply" href="#" class="flex items-center gap-2 px-4 py-2 glass rounded-xl text-xs text-white/60 hover:text-white hover:bg-white/10 transition-all"><i data-lucide="mail" style="width:13px;height:13px"></i> Balas via Email</a>
    </div>
  </div>
</div></div>

<script>
lucide.createIcons();
function showToast(i,t){Swal.fire({toast:true,position:'top-end',icon:i,title:t,showConfirmButton:false,timer:3000,timerProgressBar:true,background:'rgba(15,15,15,.97)',color:'#fff',customClass:{popup:'rounded-2xl'}})}
function closeMod(){document.getElementById('modal-overlay').style.display='none';document.body.style.overflow=''}
async function viewMsg(id){
  const res=await fetch('messages.php',{method:'POST',body:new URLSearchParams({action:'get',id,csrf_token:'<?=$csrf?>'})});
  const d=await res.json();if(!d||!d.id)return;
  document.getElementById('m-name').textContent=d.name;
  document.getElementById('m-email').textContent=d.email;
  document.getElementById('m-subject').textContent=d.subject;
  document.getElementById('m-message').textContent=d.message;
  document.getElementById('m-date').textContent=new Date(d.created_at).toLocaleString('id-ID');
  document.getElementById('m-reply').href='mailto:'+d.email+'?subject=Re: '+encodeURIComponent(d.subject);
  document.getElementById('modal-overlay').style.display='flex';document.body.style.overflow='hidden';
  // update row visually
  setTimeout(()=>location.reload(),3000);
}
function delMsg(id,name){
  Swal.fire({title:'Hapus Pesan?',html:`Dari: ${name}`,icon:'warning',showCancelButton:true,confirmButtonText:'Hapus',cancelButtonText:'Batal',confirmButtonColor:'#ef4444',background:'#111',color:'#fff',customClass:{popup:'rounded-2xl',confirmButton:'rounded-xl px-5',cancelButton:'rounded-xl px-5'}}).then(async r=>{
    if(!r.isConfirmed)return;
    const res=await fetch('messages.php',{method:'POST',body:new URLSearchParams({action:'delete',id,csrf_token:'<?=$csrf?>'})});
    const d=await res.json();if(d.ok){showToast('success',d.msg);setTimeout(()=>location.reload(),900)}else showToast('error',d.msg);
  });
}
async function markAllRead(){
  const res=await fetch('messages.php',{method:'POST',body:new URLSearchParams({action:'mark_all_read',csrf_token:'<?=$csrf?>'})});
  const d=await res.json();if(d.ok){showToast('success',d.msg);setTimeout(()=>location.reload(),900)}
}
</script>
</div></main></div></body></html>
