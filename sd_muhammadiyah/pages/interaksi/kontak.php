<?php
require_once __DIR__ . '/../../includes/config.php';
$pageTitle  = 'Kontak Kami';
$activePage = 'kontak';

$success = ''; $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $name    = trim($_POST['name']    ?? '');
    $email   = trim($_POST['email']   ?? '');
    $phone   = trim($_POST['phone']   ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!$name || !$email || !$subject || !$message) {
        $error = 'Nama, email, subjek, dan pesan wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } else {
        try {
            db()->prepare("INSERT INTO contact_messages (name,email,phone,subject,message) VALUES (?,?,?,?,?)")
               ->execute([$name,$email,$phone,$subject,$message]);
            $success = true;
        } catch(Exception $e){ $error = 'Gagal mengirim pesan. Silakan coba lagi.'; }
    }
}

require_once ROOT_PATH . '../../includes/header.php';
try { $p = $profileData ?? db()->query("SELECT * FROM school_profile LIMIT 1")->fetch() ?: []; }
catch(Exception $e){ $p=[]; }
?>

<main class="pt-20 min-h-screen bg-gradient-to-b from-black via-zinc-950 to-black">
  <section class="page-hero py-24">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center gap-3 mb-4 reveal"><div class="h-px w-10 bg-gold-400/70"></div><span class="text-gold-300 text-xs tracking-[.25em] uppercase">Hubungi Kami</span></div>
      <h1 class="font-display text-5xl lg:text-6xl font-light text-white reveal">Kontak &amp; <em class="text-gold-shimmer not-italic">Lokasi</em></h1>
    </div>
  </section>

  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="grid lg:grid-cols-5 gap-10">

      <!-- Contact Info -->
      <div class="lg:col-span-2 space-y-6">
        <?php foreach ([
          ['map-pin','Alamat',$p['address']??'-'.', '.($p['village']??'').', '.($p['district']??'').', '.($p['city']??'')],
          ['phone','Telepon',$p['phone']??'-'],
          ['mail','Email',$p['email']??'-'],
          ['clock','Jam Operasional','Senin – Jumat: 07.00 – 16.00 WIB'],
        ] as $info): ?>
        <div class="glass rounded-2xl p-5 flex items-start gap-4 hover:bg-white/10 transition-all reveal">
          <div class="w-10 h-10 rounded-xl flex-shrink-0 flex items-center justify-center" style="background:rgba(212,170,58,.1)"><i data-lucide="<?= $info[0] ?>" class="w-4 h-4 text-gold-400"></i></div>
          <div><div class="text-xs text-white/35 uppercase tracking-widest mb-1"><?= $info[1] ?></div><p class="text-white/75 text-sm"><?= e($info[2]) ?></p></div>
        </div>
        <?php endforeach; ?>

        <!-- Social links -->
        <?php if (!empty($p['instagram']) || !empty($p['facebook']) || !empty($p['youtube'])): ?>
        <div class="glass rounded-2xl p-5 reveal">
          <div class="text-xs text-white/35 uppercase tracking-widest mb-3">Media Sosial</div>
          <div class="flex gap-3">
            <?php if (!empty($p['instagram'])): ?><a href="<?= e($p['instagram']) ?>" target="_blank" class="flex items-center gap-2 glass px-3 py-2 rounded-xl text-xs text-white/60 hover:text-white hover:bg-white/12 transition-all"><i data-lucide="instagram" class="w-4 h-4"></i> Instagram</a><?php endif; ?>
            <?php if (!empty($p['facebook'])): ?><a href="<?= e($p['facebook']) ?>" target="_blank" class="flex items-center gap-2 glass px-3 py-2 rounded-xl text-xs text-white/60 hover:text-white hover:bg-white/12 transition-all"><i data-lucide="facebook" class="w-4 h-4"></i> Facebook</a><?php endif; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <!-- Form -->
      <div class="lg:col-span-3">
        <?php if ($success): ?>
        <div class="glass rounded-3xl p-12 text-center">
          <div class="w-16 h-16 rounded-full mx-auto mb-5 flex items-center justify-center" style="background:rgba(52,211,153,.15);border:1px solid rgba(52,211,153,.3)">
            <i data-lucide="check" class="w-8 h-8 text-green-400"></i>
          </div>
          <h2 class="font-display text-3xl text-white font-light mb-2">Pesan Terkirim!</h2>
          <p class="text-white/50 mb-6">Terima kasih telah menghubungi kami. Tim kami akan segera merespons pesan Anda.</p>
          <a href="kontak.php" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl glass text-white/70 hover:text-white text-sm transition-all">Kirim Pesan Lain</a>
        </div>
        <?php else: ?>
        <form method="POST" class="glass rounded-3xl p-8 space-y-5">
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
          <h2 class="font-display text-2xl text-white font-light">Kirim Pesan</h2>

          <?php if ($error): ?>
          <div class="px-4 py-3 rounded-xl text-sm flex items-center gap-2" style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#fca5a5">
            <i data-lucide="alert-circle" class="w-4 h-4"></i><?= e($error) ?>
          </div>
          <?php endif; ?>

          <div class="grid sm:grid-cols-2 gap-4">
            <div><label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40">Nama *</label><input type="text" name="name" value="<?= e($_POST['name']??'') ?>" required placeholder="Nama lengkap" class="w-full px-4 py-3 rounded-xl text-sm" style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);color:#fff"></div>
            <div><label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40">Email *</label><input type="email" name="email" value="<?= e($_POST['email']??'') ?>" required placeholder="email@anda.com" class="w-full px-4 py-3 rounded-xl text-sm" style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);color:#fff"></div>
          </div>
          <div class="grid sm:grid-cols-2 gap-4">
            <div><label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40">Telepon</label><input type="tel" name="phone" value="<?= e($_POST['phone']??'') ?>" placeholder="08xx" class="w-full px-4 py-3 rounded-xl text-sm" style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);color:#fff"></div>
            <div><label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40">Subjek *</label><input type="text" name="subject" value="<?= e($_POST['subject']??'') ?>" required placeholder="Perihal pesan" class="w-full px-4 py-3 rounded-xl text-sm" style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);color:#fff"></div>
          </div>
          <div><label class="block text-xs tracking-widest uppercase mb-1.5 text-white/40">Pesan *</label><textarea name="message" required rows="5" placeholder="Tulis pesan Anda di sini…" class="w-full px-4 py-3 rounded-xl text-sm resize-none" style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);color:#fff"><?= e($_POST['message']??'') ?></textarea></div>

          <button type="submit" class="w-full py-4 rounded-2xl font-medium text-sm text-black transition-all hover:scale-[1.01] hover:shadow-[0_10px_40px_rgba(212,170,58,.35)] flex items-center justify-center gap-2" style="background:linear-gradient(135deg,#d4aa3a,#e8c860)">
            <i data-lucide="send" class="w-4 h-4"></i> Kirim Pesan
          </button>
        </form>
        <?php endif; ?>
      </div>
    </div>

    <!-- Google Maps embed -->
    <?php if (!empty($p['maps_embed'])): ?>
    <div class="mt-12 rounded-3xl overflow-hidden reveal" style="height:380px"><?= $p['maps_embed'] ?></div>
    <?php endif; ?>
  </div>
</main>

<?php require_once ROOT_PATH . '../../includes/footer.php'; ?>
