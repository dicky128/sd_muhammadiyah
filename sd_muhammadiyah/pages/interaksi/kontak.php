<?php
require_once __DIR__.'/../../includes/config.php';
$pageTitle=$pageHeroTitle='Kontak Kami';
$pageHeroLabel='Hubungi Kami';
$pageHeroSub='Kami siap membantu Anda. Kirim pesan atau kunjungi kami langsung di sekolah.';
$pageHeroColor='sky'; $activePage='kontak'; $breadcrumbParent='Interaksi';

$success=''; $error='';
if($_SERVER['REQUEST_METHOD']==='POST' && verify_csrf()){
    $name=trim($_POST['name']??''); $email=trim($_POST['email']??'');
    $phone=trim($_POST['phone']??''); $subject=trim($_POST['subject']??'');
    $message=trim($_POST['message']??'');
    if(!$name||!$email||!$subject||!$message){ $error='Nama, email, subjek, dan pesan wajib diisi.'; }
    elseif(!filter_var($email,FILTER_VALIDATE_EMAIL)){ $error='Format email tidak valid.'; }
    else {
        try {
            db()->prepare("INSERT INTO contact_messages (name,email,phone,subject,message) VALUES (?,?,?,?,?)")
               ->execute([$name,$email,$phone,$subject,$message]);
            $success=true;
        } catch(Exception $e){ $error='Gagal mengirim pesan. Silakan coba lagi.'; }
    }
}

require_once ROOT_PATH.'header.php';
try { $p=$profileData??db()->query("SELECT * FROM school_profile LIMIT 1")->fetch()??[]; }
catch(Exception $e){ $p=[]; }
?>

<style>
.input-light{width:100%;padding:.875rem 1rem;border-radius:12px;background:rgba(255,255,255,.8);border:1.5px solid rgba(212,170,58,.2);color:#1a1228;font-family:'Plus Jakarta Sans',sans-serif;font-size:.9rem;transition:all .2s}
.input-light:focus{outline:none;border-color:#f472b6;box-shadow:0 0 0 3px rgba(244,114,182,.12)}
.input-light::placeholder{color:#9ca3af}
label.label-light{display:block;font-size:.7rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#9ca3af;margin-bottom:.5rem}
</style>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
  <div class="grid lg:grid-cols-5 gap-10">

    <!-- Contact Info -->
    <div class="lg:col-span-2 space-y-5">
      <?php foreach([
        ['map-pin','Alamat',$p['address']??'-'.', '.($p['village']??'').', '.($p['district']??'').', '.($p['city']??''),'icon-badge-pink'],
        ['phone','Telepon',$p['phone']??'-','icon-badge-sky'],
        ['mail','Email',$p['email']??'-','icon-badge-gold'],
        ['clock','Jam Operasional','Senin – Jumat: 07.00 – 16.00 WIB','icon-badge-pink'],
      ] as $info): ?>
      <div class="tilt-card glass-card rounded-2xl p-5 lift-card reveal-fade">
        <div class="tilt-inner"><div class="tilt-shine"></div>
          <div class="flex items-start gap-4">
            <div class="<?=$info[3]?> icon-badge flex-shrink-0"><i data-lucide="<?=$info[0]?>" class="w-4 h-4"></i></div>
            <div><div class="text-xs text-gray-400 uppercase tracking-widest font-bold mb-1"><?=$info[1]?></div>
            <p class="text-gray-700 text-sm font-medium"><?=e($info[2])?></p></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>

      <?php if(!empty($p['instagram'])||!empty($p['facebook'])||!empty($p['youtube'])): ?>
      <div class="glass-card rounded-2xl p-5 reveal-fade">
        <div class="text-xs text-gray-400 uppercase tracking-widest font-bold mb-3">Media Sosial</div>
        <div class="flex flex-wrap gap-3">
          <?php foreach([[$p['instagram']??'','instagram','Instagram'],[$p['facebook']??'','facebook','Facebook'],[$p['youtube']??'','youtube','YouTube']] as $s): if(!$s[0]) continue; ?>
          <a href="<?=e($s[0])?>" target="_blank"
             class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold text-gray-600 hover:text-pink-600 hover:bg-pink-50 transition-all glass-card">
            <i data-lucide="<?=$s[1]?>" class="w-4 h-4 text-pink-400"></i><?=$s[2]?>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <!-- Form -->
    <div class="lg:col-span-3">
      <?php if($success): ?>
      <div class="glass-card rounded-3xl p-12 text-center reveal-3d">
        <div class="w-16 h-16 rounded-full mx-auto mb-5 flex items-center justify-center" style="background:rgba(74,222,128,.12);border:1px solid rgba(74,222,128,.3)">
          <i data-lucide="check" class="w-8 h-8 text-green-500"></i>
        </div>
        <h2 class="font-display font-bold text-gray-800 text-2xl mb-2">Pesan Terkirim!</h2>
        <p class="text-gray-500 mb-6">Terima kasih menghubungi kami. Tim kami akan segera merespons pesan Anda.</p>
        <a href="kontak.php" class="btn-outline-light">Kirim Pesan Lain</a>
      </div>
      <?php else: ?>
      <div class="glass-card rounded-3xl p-8 reveal-3d">
        <h2 class="font-display font-bold text-gray-800 text-2xl mb-6">Kirim Pesan</h2>
        <?php if($error): ?>
        <div class="px-4 py-3 rounded-2xl flex items-center gap-2 text-sm mb-5" style="background:rgba(239,68,68,.06);border:1px solid rgba(239,68,68,.2);color:#dc2626">
          <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i><?=e($error)?>
        </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
          <input type="hidden" name="csrf_token" value="<?=csrf_token()?>">
          <div class="grid sm:grid-cols-2 gap-4">
            <div><label class="label-light">Nama Lengkap *</label><input type="text" name="name" value="<?=e($_POST['name']??'')?>" required placeholder="Nama Anda" class="input-light"></div>
            <div><label class="label-light">Email *</label><input type="email" name="email" value="<?=e($_POST['email']??'')?>" required placeholder="email@anda.com" class="input-light"></div>
          </div>
          <div class="grid sm:grid-cols-2 gap-4">
            <div><label class="label-light">Telepon</label><input type="tel" name="phone" value="<?=e($_POST['phone']??'')?>" placeholder="08xx-xxxx-xxxx" class="input-light"></div>
            <div><label class="label-light">Subjek *</label><input type="text" name="subject" value="<?=e($_POST['subject']??'')?>" required placeholder="Perihal pesan" class="input-light"></div>
          </div>
          <div><label class="label-light">Pesan *</label>
            <textarea name="message" required rows="5" placeholder="Tulis pesan Anda…" class="input-light" style="resize:none"><?=e($_POST['message']??'')?></textarea>
          </div>
          <button type="submit" class="w-full btn-primary-light justify-center !rounded-2xl !py-4">
            <i data-lucide="send" class="w-4 h-4"></i> Kirim Pesan
          </button>
        </form>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Maps -->
  <?php if(!empty($p['maps_embed'])): ?>
  <div class="mt-12 reveal-fade">
    <h2 class="font-display font-bold text-gray-800 text-xl mb-5 flex items-center gap-3">
      <div class="icon-badge icon-badge-sky"><i data-lucide="map" class="w-4 h-4"></i></div> Lokasi Kami
    </h2>
    <div class="rounded-3xl overflow-hidden" style="height:380px;box-shadow:0 12px 40px rgba(56,189,248,.15)"><?=$p['maps_embed']?></div>
  </div>
  <?php endif; ?>
</div>

<?php require_once ROOT_PATH.'footer.php'; ?>