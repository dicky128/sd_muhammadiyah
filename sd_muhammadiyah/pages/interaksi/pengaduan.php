<?php
require_once __DIR__ . '/../../includes/config.php';
$pageTitle  = 'Pengaduan';
$activePage = 'pengaduan';

$success = ''; $error = ''; $ticketNo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $name     = trim($_POST['name']    ?? '');
    $email    = trim($_POST['email']   ?? '');
    $phone    = trim($_POST['phone']   ?? '');
    $category = $_POST['category']     ?? 'lainnya';
    $subject  = trim($_POST['subject'] ?? '');
    $message  = trim($_POST['message'] ?? '');

    if (!$name || !$subject || !$message) {
        $error = 'Nama, subjek, dan isi pengaduan wajib diisi.';
    } else {
        // Auto ticket: PGD-YYYYMMDD-XXXX
        $ticket = 'PGD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(),5,4));
        $attachment = null;

        if (!empty($_FILES['attachment']['name'])) {
            $allowed = ['image/jpeg','image/png','image/gif','image/webp','application/pdf'];
            if (in_array($_FILES['attachment']['type'], $allowed) && $_FILES['attachment']['size'] <= 5*1024*1024) {
                $ext  = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
                $fname = $ticket . '.' . strtolower($ext);
                $dest  = UPLOAD_PATH . 'complaints/' . $fname;
                if (!is_dir(UPLOAD_PATH . 'complaints/')) mkdir(UPLOAD_PATH . 'complaints/', 0755, true);
                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $dest)) $attachment = $fname;
            }
        }

        try {
            $stmt = db()->prepare(
                "INSERT INTO complaints (ticket_no,name,email,phone,category,subject,message,attachment)
                 VALUES (?,?,?,?,?,?,?,?)"
            );
            $stmt->execute([$ticket,$name,$email,$phone,$category,$subject,$message,$attachment]);
            $ticketNo = $ticket;
            $success  = 'Pengaduan berhasil dikirim!';
        } catch(Exception $e) {
            $error = 'Gagal menyimpan pengaduan. Silakan coba lagi.';
        }
    }
}

require_once ROOT_PATH . '../../includes/header.php';
?>

<main class="pt-20 min-h-screen bg-gradient-to-b from-black via-zinc-950 to-black">
  <section class="page-hero py-24">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center gap-3 mb-4 reveal"><div class="h-px w-10 bg-gold-400/70"></div><span class="text-gold-300 text-xs tracking-[.25em] uppercase">Layanan Aduan</span></div>
      <h1 class="font-display text-5xl lg:text-6xl font-light text-white reveal">Form <em class="text-gold-shimmer not-italic">Pengaduan</em></h1>
      <p class="text-white/50 mt-4 font-light reveal">Sampaikan keluhan atau saran Anda. Setiap aduan akan mendapat nomor tiket dan diproses oleh tim kami.</p>
    </div>
  </section>

  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="grid lg:grid-cols-3 gap-8">

      <!-- Info Panel -->
      <div class="space-y-4">
        <?php foreach ([
          ['clock','Waktu Respons','Kami berusaha merespons setiap pengaduan dalam 1×24 jam kerja.'],
          ['shield','Kerahasiaan','Identitas pelapor dijaga kerahasiaannya sesuai kebijakan sekolah.'],
          ['ticket','Tiket Otomatis','Setiap pengaduan mendapat nomor tiket unik untuk kemudahan pelacakan.'],
        ] as $info): ?>
        <div class="glass rounded-2xl p-5 reveal">
          <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:rgba(212,170,58,.1)"><i data-lucide="<?= $info[0] ?>" class="w-4 h-4 text-gold-400"></i></div>
            <div><h3 class="text-sm text-white/80 font-medium mb-1"><?= $info[1] ?></h3><p class="text-xs text-white/40 leading-relaxed"><?= $info[2] ?></p></div>
          </div>
        </div>
        <?php endforeach; ?>

        <!-- Cek Status Tiket -->
        <div class="glass rounded-2xl p-5 reveal">
          <h3 class="text-sm text-white/80 font-medium mb-3 flex items-center gap-2"><i data-lucide="search" class="w-4 h-4 text-gold-400"></i> Cek Status Tiket</h3>
          <form method="GET" action="" class="flex gap-2">
            <input type="hidden" name="action" value="check">
            <input type="text" name="ticket" placeholder="PGD-..." class="flex-1 text-xs px-3 py-2 rounded-lg" style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);color:#fff">
            <button type="submit" class="px-3 py-2 rounded-lg text-xs text-black font-medium" style="background:#d4aa3a">Cek</button>
          </form>
          <?php
          if (($_GET['action']??'')==='check' && !empty($_GET['ticket'])) {
              try {
                  $tickCheck = db()->prepare("SELECT ticket_no,subject,status,admin_note,created_at,responded_at FROM complaints WHERE ticket_no=?");
                  $tickCheck->execute([strtoupper(trim($_GET['ticket']))]);
                  $tc = $tickCheck->fetch();
              } catch(Exception $e){ $tc=null; }
              if ($tc): $stColors=['masuk'=>'text-red-300','diproses'=>'text-yellow-300','selesai'=>'text-green-300','ditutup'=>'text-white/40']; ?>
          <div class="mt-3 p-3 rounded-xl" style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08)">
            <div class="text-[10px] tracking-widest uppercase text-white/30 mb-1"><?= e($tc['ticket_no']) ?></div>
            <p class="text-xs text-white/70 line-clamp-1"><?= e($tc['subject']) ?></p>
            <div class="flex items-center justify-between mt-2">
              <span class="text-xs <?= $stColors[$tc['status']]??'text-white/40' ?> capitalize"><?= e($tc['status']) ?></span>
              <span class="text-[10px] text-white/25"><?= date('d/m/Y', strtotime($tc['created_at'])) ?></span>
            </div>
            <?php if ($tc['admin_note']): ?><p class="text-xs text-white/40 mt-2 italic">"<?= e($tc['admin_note']) ?>"</p><?php endif; ?>
          </div>
          <?php else: ?><p class="mt-2 text-xs text-white/35">Nomor tiket tidak ditemukan.</p><?php endif; ?>
          <?php } ?>
        </div>
      </div>

      <!-- Form -->
      <div class="lg:col-span-2">
        <?php if ($success): ?>
        <div class="glass rounded-3xl p-10 text-center">
          <div class="w-16 h-16 rounded-full mx-auto mb-5 flex items-center justify-center" style="background:rgba(52,211,153,.15);border:1px solid rgba(52,211,153,.3)">
            <i data-lucide="check" class="w-8 h-8 text-green-400"></i>
          </div>
          <h2 class="font-display text-3xl text-white font-light mb-2">Pengaduan Terkirim!</h2>
          <p class="text-white/50 mb-5">Terima kasih telah menyampaikan pengaduan Anda.</p>
          <div class="inline-flex items-center gap-3 px-6 py-3 rounded-2xl mb-6" style="background:rgba(212,170,58,.1);border:1px solid rgba(212,170,58,.3)">
            <i data-lucide="ticket" class="w-5 h-5 text-gold-400"></i>
            <div class="text-left">
              <div class="text-[10px] text-white/35 uppercase tracking-widest">Nomor Tiket Anda</div>
              <div class="text-gold-300 font-mono font-medium"><?= e($ticketNo) ?></div>
            </div>
          </div>
          <p class="text-white/35 text-sm mb-6">Simpan nomor tiket ini untuk melacak status pengaduan Anda.</p>
          <a href="pengaduan.php" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-sm glass text-white/70 hover:text-white hover:bg-white/12 transition-all">
            <i data-lucide="plus" class="w-4 h-4"></i> Buat Pengaduan Baru
          </a>
        </div>
        <?php else: ?>
        <form method="POST" enctype="multipart/form-data" class="glass rounded-3xl p-8 space-y-5">
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

          <?php if ($error): ?>
          <div class="px-4 py-3 rounded-xl flex items-center gap-2 text-sm" style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#fca5a5">
            <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i><?= e($error) ?>
          </div>
          <?php endif; ?>

          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40">Nama Lengkap *</label>
              <input type="text" name="name" value="<?= e($_POST['name']??'') ?>" required placeholder="Nama Anda" class="w-full px-4 py-3 rounded-xl text-sm" style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);color:#fff">
            </div>
            <div>
              <label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40">Nomor HP</label>
              <input type="tel" name="phone" value="<?= e($_POST['phone']??'') ?>" placeholder="08xx-xxxx-xxxx" class="w-full px-4 py-3 rounded-xl text-sm" style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);color:#fff">
            </div>
          </div>

          <div>
            <label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40">Email</label>
            <input type="email" name="email" value="<?= e($_POST['email']??'') ?>" placeholder="email@anda.com" class="w-full px-4 py-3 rounded-xl text-sm" style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);color:#fff">
          </div>

          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40">Kategori</label>
              <select name="category" class="w-full px-4 py-3 rounded-xl text-sm" style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);color:#fff">
                <?php foreach(['fasilitas'=>'Fasilitas','pembelajaran'=>'Pembelajaran','administrasi'=>'Administrasi','keamanan'=>'Keamanan','lainnya'=>'Lainnya'] as $v=>$l): ?>
                <option value="<?= $v ?>" <?= ($_POST['category']??'')===$v?'selected':'' ?> style="background:#1a1a1a"><?= $l ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40">Subjek *</label>
              <input type="text" name="subject" value="<?= e($_POST['subject']??'') ?>" required placeholder="Topik pengaduan singkat" class="w-full px-4 py-3 rounded-xl text-sm" style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);color:#fff">
            </div>
          </div>

          <div>
            <label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40">Isi Pengaduan *</label>
            <textarea name="message" required rows="5" placeholder="Uraikan pengaduan Anda secara detail…" class="w-full px-4 py-3 rounded-xl text-sm resize-none" style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);color:#fff"><?= e($_POST['message']??'') ?></textarea>
          </div>

          <div>
            <label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40">Lampiran (opsional, max 5MB)</label>
            <input type="file" name="attachment" accept="image/*,.pdf" class="w-full px-4 py-3 rounded-xl text-sm text-white/60" style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12)">
            <p class="text-white/25 text-xs mt-1">Format: JPG, PNG, PDF</p>
          </div>

          <button type="submit" class="w-full py-4 rounded-2xl font-medium text-sm text-black transition-all hover:scale-[1.01] hover:shadow-[0_10px_40px_rgba(212,170,58,.35)] flex items-center justify-center gap-2" style="background:linear-gradient(135deg,#d4aa3a,#e8c860)">
            <i data-lucide="send" class="w-4 h-4"></i> Kirim Pengaduan
          </button>
        </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</main>

<?php require_once ROOT_PATH . '../../includes/footer.php'; ?>
