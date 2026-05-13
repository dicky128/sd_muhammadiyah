<?php
require_once __DIR__.'/../../includes/config.php';
$pageTitle=$pageHeroTitle='Form Pengaduan';
$pageHeroLabel='Layanan Aduan';
$pageHeroSub='Sampaikan keluhan atau saran Anda. Setiap pengaduan mendapat nomor tiket dan diproses serius.';
$pageHeroColor='pink'; $activePage='pengaduan'; $breadcrumbParent='Interaksi';

$success=''; $error=''; $ticketNo='';
if($_SERVER['REQUEST_METHOD']==='POST' && verify_csrf()){
    $name=trim($_POST['name']??''); $email=trim($_POST['email']??'');
    $phone=trim($_POST['phone']??''); $category=$_POST['category']??'lainnya';
    $subject=trim($_POST['subject']??''); $message=trim($_POST['message']??'');

    if(!$name||!$subject||!$message){ $error='Nama, subjek, dan isi pengaduan wajib diisi.'; }
    else {
        $ticket='PGD-'.date('Ymd').'-'.strtoupper(substr(uniqid(),5,4));
        $attachment=null;
        if(!empty($_FILES['attachment']['name'])){
            $allowed=['image/jpeg','image/png','image/gif','image/webp','application/pdf'];
            if(in_array($_FILES['attachment']['type'],$allowed)&&$_FILES['attachment']['size']<=5*1024*1024){
                $ext=pathinfo($_FILES['attachment']['name'],PATHINFO_EXTENSION);
                $fname=$ticket.'.'.strtolower($ext);
                $dest=UPLOAD_PATH.'complaints/'.$fname;
                if(!is_dir(UPLOAD_PATH.'complaints/')) mkdir(UPLOAD_PATH.'complaints/',0755,true);
                if(move_uploaded_file($_FILES['attachment']['tmp_name'],$dest)) $attachment=$fname;
            }
        }
        try {
            db()->prepare("INSERT INTO complaints (ticket_no,name,email,phone,category,subject,message,attachment) VALUES (?,?,?,?,?,?,?,?)")
               ->execute([$ticket,$name,$email,$phone,$category,$subject,$message,$attachment]);
            $ticketNo=$ticket; $success=true;
        } catch(Exception $e){ $error='Gagal menyimpan pengaduan. Silakan coba lagi.'; }
    }
}

require_once ROOT_PATH.'header.php';
?>

<style>
.input-light{width:100%;padding:.875rem 1rem;border-radius:12px;background:rgba(255,255,255,.8);border:1.5px solid rgba(212,170,58,.2);color:#1a1228;font-family:'Plus Jakarta Sans',sans-serif;font-size:.9rem;transition:all .2s}
.input-light:focus{outline:none;border-color:#f472b6;box-shadow:0 0 0 3px rgba(244,114,182,.12)}
.input-light::placeholder{color:#9ca3af}
.input-light option{background:#fff}
label.label-light{display:block;font-size:.7rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#9ca3af;margin-bottom:.5rem}
</style>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
  <div class="grid lg:grid-cols-3 gap-8">

    <!-- Info panel -->
    <div class="space-y-4">
      <?php foreach([
        ['clock','Waktu Respons','Kami berusaha merespons setiap pengaduan dalam 1×24 jam kerja.','icon-badge-gold'],
        ['shield','Kerahasiaan','Identitas pelapor dijaga kerahasiaannya sesuai kebijakan sekolah.','icon-badge-sky'],
        ['ticket','Tiket Otomatis','Setiap pengaduan mendapat nomor tiket unik untuk kemudahan pelacakan.','icon-badge-pink'],
      ] as $info): ?>
      <div class="tilt-card glass-card rounded-2xl p-5 reveal-3d">
        <div class="tilt-inner"><div class="tilt-shine"></div>
          <div class="flex items-start gap-3">
            <div class="<?=$info[3]?> icon-badge flex-shrink-0"><i data-lucide="<?=$info[0]?>" class="w-4 h-4"></i></div>
            <div><h3 class="text-sm text-gray-700 font-semibold mb-1"><?=$info[1]?></h3>
            <p class="text-xs text-gray-400 leading-relaxed"><?=$info[2]?></p></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>

      <!-- Cek Status -->
      <div class="glass-card rounded-2xl p-5">
        <h3 class="text-sm text-gray-700 font-semibold mb-3 flex items-center gap-2">
          <i data-lucide="search" class="w-4 h-4 text-pink-400"></i> Cek Status Tiket
        </h3>
        <form method="GET" class="flex gap-2">
          <input type="hidden" name="action" value="check">
          <input type="text" name="ticket" placeholder="PGD-..." class="flex-1 text-xs px-3 py-2 rounded-xl input-light" style="padding:8px 12px">
          <button type="submit" class="px-3 py-2 rounded-xl text-xs font-bold text-white" style="background:linear-gradient(135deg,#f472b6,#d4aa3a)">Cek</button>
        </form>
        <?php
        if(($_GET['action']??'')==='check'&&!empty($_GET['ticket'])){
            try {
                $tc=db()->prepare("SELECT ticket_no,subject,status,admin_note,created_at FROM complaints WHERE ticket_no=?");
                $tc->execute([strtoupper(trim($_GET['ticket']))]); $tc=$tc->fetch();
            } catch(Exception $e){ $tc=null; }
            $stClass=['masuk'=>'section-label-pink','diproses'=>'section-label-gold','selesai'=>'section-label-sky','ditutup'=>'section-label-gold'];
            if($tc): ?>
        <div class="mt-3 p-3 rounded-xl" style="background:rgba(253,242,248,.6);border:1px solid rgba(244,114,182,.15)">
          <div class="text-[10px] tracking-widest uppercase text-pink-400 font-bold mb-1"><?=e($tc['ticket_no'])?></div>
          <p class="text-xs text-gray-600 font-medium line-clamp-1"><?=e($tc['subject'])?></p>
          <div class="flex items-center justify-between mt-2">
            <span class="section-label <?=$stClass[$tc['status']]??'section-label-gold'?>"><?=ucfirst($tc['status'])?></span>
            <span class="text-[10px] text-gray-400"><?=date('d/m/Y',strtotime($tc['created_at']))?></span>
          </div>
          <?php if($tc['admin_note']): ?><p class="text-xs text-gray-400 mt-2 italic">"<?=e($tc['admin_note'])?>"</p><?php endif; ?>
        </div>
            <?php else: ?><p class="mt-2 text-xs text-gray-400">Nomor tiket tidak ditemukan.</p><?php endif; ?>
        <?php } ?>
      </div>
    </div>

    <!-- Form -->
    <div class="lg:col-span-2">
      <?php if($success): ?>
      <div class="glass-card rounded-3xl p-10 text-center reveal-3d" style="box-shadow:0 12px 40px rgba(244,114,182,.15)">
        <div class="w-16 h-16 rounded-full mx-auto mb-5 flex items-center justify-center" style="background:rgba(74,222,128,.12);border:1px solid rgba(74,222,128,.3)">
          <i data-lucide="check" class="w-8 h-8 text-green-500"></i>
        </div>
        <h2 class="font-display font-bold text-gray-800 text-2xl mb-2">Pengaduan Terkirim!</h2>
        <p class="text-gray-500 mb-6">Terima kasih telah menyampaikan pengaduan Anda.</p>
        <div class="inline-flex items-center gap-3 px-6 py-3 rounded-2xl mb-6" style="background:rgba(212,170,58,.1);border:1px solid rgba(212,170,58,.25)">
          <i data-lucide="ticket" class="w-5 h-5 text-gold-500"></i>
          <div class="text-left">
            <div class="text-[10px] text-gray-400 uppercase tracking-widest font-bold">Nomor Tiket Anda</div>
            <div class="font-mono font-bold text-gray-800 text-lg"><?=e($ticketNo)?></div>
          </div>
        </div>
        <p class="text-gray-400 text-sm mb-6">Simpan nomor tiket ini untuk memantau status pengaduan Anda. Kami akan merespons dalam 3 - 5 hari kerja.</p>
        <a href="pengaduan.php" class="btn-outline-light"><i data-lucide="plus" class="w-4 h-4"></i> Buat Pengaduan Baru</a>
      </div>

      <?php else: ?>
      <div class="glass-card rounded-3xl p-8 reveal-3d">
        <h2 class="font-display font-bold text-gray-800 text-2xl mb-6">Kirim Pengaduan</h2>
        <?php if($error): ?>
        <div class="px-4 py-3 rounded-2xl flex items-center gap-2 text-sm mb-5" style="background:rgba(239,68,68,.06);border:1px solid rgba(239,68,68,.2);color:#dc2626">
          <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i><?=e($error)?>
        </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="space-y-4">
          <input type="hidden" name="csrf_token" value="<?=csrf_token()?>">
          <div class="grid sm:grid-cols-2 gap-4">
            <div><label class="label-light">Nama Lengkap *</label><input type="text" name="name" value="<?=e($_POST['name']??'')?>" required placeholder="Nama Anda" class="input-light"></div>
            <div><label class="label-light">Nomor HP</label><input type="tel" name="phone" value="<?=e($_POST['phone']??'')?>" placeholder="08xx-xxxx-xxxx" class="input-light"></div>
          </div>
          <div><label class="label-light">Email</label><input type="email" name="email" value="<?=e($_POST['email']??'')?>" placeholder="email@anda.com" class="input-light"></div>
          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <label class="label-light">Kategori</label>
              <select name="category" class="input-light">
                <?php foreach(['fasilitas'=>'Fasilitas','pembelajaran'=>'Pembelajaran','administrasi'=>'Administrasi','keamanan'=>'Keamanan','lainnya'=>'Lainnya'] as $v=>$l): ?>
                <option value="<?=$v?>" <?=($_POST['category']??'')===$v?'selected':''?>><?=$l?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div><label class="label-light">Subjek *</label><input type="text" name="subject" value="<?=e($_POST['subject']??'')?>" required placeholder="Topik singkat" class="input-light"></div>
          </div>
          <div><label class="label-light">Isi Pengaduan *</label>
            <textarea name="message" required rows="5" placeholder="Uraikan pengaduan Anda secara detail…" class="input-light" style="resize:none"><?=e($_POST['message']??'')?></textarea>
          </div>
          <div><label class="label-light">Lampiran (opsional, max 5MB)</label>
            <input type="file" name="attachment" accept="image/*,.pdf" class="input-light" style="padding:10px 14px">
            <p class="text-gray-400 text-xs mt-1">Format: JPG, PNG, PDF</p>
          </div>
          <button type="submit" class="w-full btn-primary-light justify-center !rounded-2xl !py-4">
            <i data-lucide="send" class="w-4 h-4"></i> Kirim Pengaduan
          </button>
        </form>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php 
// AJAX endpoint
if(isset($_GET['ajax'])&&$_GET['ajax']==='cek'){
  header('Content-Type: application/json');
  $t=trim($_GET['ticket']??'');
  $row=dbRow("SELECT ticket_no,subject,status,response FROM complaints WHERE ticket_no=?",[$t]);
  if($row) echo json_encode(['found'=>true,'ticket'=>$row['ticket_no'],'subject'=>$row['subject'],'status'=>$row['status'],'response'=>$row['response']]);
  else     echo json_encode(['found'=>false]);
  exit;
}
require_once ROOT_PATH.'footer.php'; 
?>