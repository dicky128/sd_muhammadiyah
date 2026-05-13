<?php
require_once __DIR__ . '/../includes/auth.php';
$activeSidebar='profile'; $pageTitle='Profil Sekolah'; $pageSubtitle='Edit informasi utama sekolah';

$msg=''; $msgType='';
if ($_SERVER['REQUEST_METHOD']==='POST' && verify_csrf()) {
    $fields=['school_name','npsn','address','village','district','city','province','postal_code','phone','email','website','visi','misi','sejarah','akreditasi','tahun_berdiri','facebook','instagram','youtube','maps_embed'];
    $data=[];
    foreach($fields as $f) $data[$f]=trim($_POST[$f]??'');

    // Logo upload
    if (!empty($_FILES['logo']['name'])) {
        $u=uploadFile($_FILES['logo'],'logos');
        if($u) $data['logo']=$u;
    }
    // Hero upload
    if (!empty($_FILES['hero_image']['name'])) {
        $u=uploadFile($_FILES['hero_image'],'heroes');
        if($u) $data['hero_image']=$u;
    }

    try {
        $check=db()->query("SELECT id FROM school_profile LIMIT 1")->fetch();
        if ($check) {
            $sets=implode(',',array_map(fn($k)=>"`$k`=?",array_keys($data)));
            $stmt=db()->prepare("UPDATE school_profile SET $sets WHERE id=".$check['id']);
            $stmt->execute(array_values($data));
        } else {
            $cols=implode(',',array_map(fn($k)=>"`$k`",array_keys($data)));
            $pls=implode(',',array_fill(0,count($data),'?'));
            db()->prepare("INSERT INTO school_profile ($cols) VALUES ($pls)")->execute(array_values($data));
        }
        $msg='Profil berhasil disimpan.'; $msgType='success';
    } catch(Exception $e){ $msg='Gagal: '.$e->getMessage(); $msgType='error'; }
}

try { $p=db()->query("SELECT * FROM school_profile LIMIT 1")->fetch()??[]; }
catch(Exception $e){ $p=[]; }

require_once __DIR__ . '/../includes/admin_head.php';
?>
<script>
function showToast(icon,title){Swal.fire({toast:true,position:'top-end',icon,title,showConfirmButton:false,timer:3200,timerProgressBar:true,background:'rgba(15,15,15,.97)',color:'#fff',customClass:{popup:'rounded-2xl'}})}
<?php if($msg): ?>showToast('<?= $msgType ?>','<?= addslashes($msg) ?>');<?php endif; ?>
</script>

<form method="POST" enctype="multipart/form-data" class="space-y-6 max-w-4xl">
  <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

  <!-- Identity -->
  <div class="glass rounded-2xl p-6">
    <h3 class="font-display text-xl text-white/80 font-light mb-5">Identitas Sekolah</h3>
    <div class="grid sm:grid-cols-2 gap-4">
      <?php
      $fields=[
        ['school_name','Nama Sekolah','text',2],['npsn','NPSN','text',1],
        ['akreditasi','Akreditasi','text',1],['tahun_berdiri','Tahun Berdiri','number',1],
        ['address','Alamat','text',2],['village','Desa/Kelurahan','text',1],
        ['district','Kecamatan','text',1],['city','Kabupaten/Kota','text',1],
        ['province','Provinsi','text',1],['postal_code','Kode Pos','text',1],
        ['phone','Telepon','text',1],['email','Email','email',1],
        ['website','Website','url',2],
      ];
      foreach($fields as [$n,$l,$t,$span]): ?>
      <div class="<?= $span===2?'sm:col-span-2':'' ?>">
        <label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40"><?= $l ?></label>
        <input type="<?= $t ?>" name="<?= $n ?>" value="<?= e($p[$n]??'') ?>" class="input-g">
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Media -->
  <div class="glass rounded-2xl p-6">
    <h3 class="font-display text-xl text-white/80 font-light mb-5">Media</h3>
    <div class="grid sm:grid-cols-2 gap-6">
      <div>
        <label class="block text-xs tracking-widest uppercase mb-2 text-white/40">Logo Sekolah</label>
        <?php if (!empty($p['logo'])): ?><img src="<?= UPLOAD_URL ?>logos/<?= e($p['logo']) ?>" class="w-20 h-20 object-contain mb-2 rounded-lg" style="background:rgba(255,255,255,.05)"><?php endif; ?>
        <input type="file" name="logo" accept="image/*" class="input-g" style="padding:6px 12px">
      </div>
      <div>
        <label class="block text-xs tracking-widest uppercase mb-2 text-white/40">Gambar Hero</label>
        <?php if (!empty($p['hero_image'])): ?><img src="<?= UPLOAD_URL ?>heroes/<?= e($p['hero_image']) ?>" class="w-full h-20 object-cover mb-2 rounded-lg"><?php endif; ?>
        <input type="file" name="hero_image" accept="image/*" class="input-g" style="padding:6px 12px">
      </div>
    </div>
  </div>

  <!-- Visi Misi -->
  <div class="glass rounded-2xl p-6 space-y-4">
    <h3 class="font-display text-xl text-white/80 font-light">Visi &amp; Misi</h3>
    <div><label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40">Visi</label><textarea name="visi" rows="3" class="input-g resize-none"><?= e($p['visi']??'') ?></textarea></div>
    <div><label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40">Misi</label><textarea name="misi" rows="4" class="input-g resize-none"><?= e($p['misi']??'') ?></textarea></div>
    <div><label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40">Sejarah Singkat</label><textarea name="sejarah" rows="6" class="input-g resize-none"><?= e($p['sejarah']??'') ?></textarea></div>
  </div>

  <!-- Social & Maps -->
  <div class="glass rounded-2xl p-6 space-y-4">
    <h3 class="font-display text-xl text-white/80 font-light">Sosial Media &amp; Peta</h3>
    <?php foreach([['instagram','Instagram URL'],['facebook','Facebook URL'],['youtube','YouTube URL']] as [$n,$l]): ?>
    <div><label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40"><?= $l ?></label><input type="url" name="<?= $n ?>" value="<?= e($p[$n]??'') ?>" class="input-g" placeholder="https://..."></div>
    <?php endforeach; ?>
    <div><label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40">Google Maps Embed Code</label><textarea name="maps_embed" rows="3" class="input-g resize-none" placeholder='&lt;iframe src="..."&gt;&lt;/iframe&gt;'><?= e($p['maps_embed']??'') ?></textarea></div>
  </div>

  <button type="submit" class="px-8 py-3 rounded-2xl font-medium text-sm text-black transition-all hover:scale-105 hover:shadow-[0_8px_30px_rgba(212,170,58,.4)] flex items-center gap-2" style="background:linear-gradient(135deg,#d4aa3a,#e8c860)">
    <i data-lucide="save" style="width:16px;height:16px"></i> Simpan Perubahan
  </button>
</form>

</div></main></div>
<script>lucide.createIcons();</script>
</body></html>
