<?php
// 1. Load Konfigurasi
require_once __DIR__ . '/../../includes/config.php';

// 2. Proteksi Fungsi e()
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}

// 3. Definisi Fungsi Render
function renderCard($t, $uploadUrl) { ?>
    <div class="glass rounded-2xl overflow-hidden group hover:-translate-y-1 hover:bg-white/10 transition-all duration-300 w-full max-w-[280px] mx-auto">
        <div class="h-48 overflow-hidden relative" style="background:rgba(212,170,58,.06)">
            <?php if (!empty($t['photo'])): ?>
                <img src="<?= $uploadUrl ?>teachers/<?= e($t['photo']) ?>" 
                     alt="<?= e($t['full_name']) ?>"
                     class="w-full h-full object-cover object-top group-hover:scale-105 transition-transform duration-500">
            <?php else: ?>
                <div class="w-full h-full flex items-center justify-center">
                    <span class="font-display text-5xl text-gold-400/40 font-light">
                        <?= strtoupper(substr($t['full_name'] ?? 'G', 0, 1)) ?>
                    </span>
                </div>
            <?php endif; ?>
        </div>
        <div class="p-5 text-center"> <!-- text-center untuk teks di dalam kartu -->
            <h3 class="font-display text-lg text-white font-light leading-tight"><?= e($t['full_name']) ?></h3>
            <?php if (!empty($t['subject'])): ?>
                <p class="text-gold-400 text-xs mt-1 tracking-wide uppercase"><?= e($t['subject']) ?></p>
            <?php endif; ?>
        </div>
    </div>
<?php }

$pageTitle  = 'Guru & Staff';
$activePage = 'guru';
require_once ROOT_PATH . 'includes/header.php';

// 4. Ambil Data
try {
    $teachers = db()->query("SELECT * FROM teachers WHERE is_active=1 AND type='guru' ORDER BY sort_order, full_name")->fetchAll(PDO::FETCH_ASSOC);
    $staff    = db()->query("SELECT * FROM teachers WHERE is_active=1 AND type='staff' ORDER BY sort_order, full_name")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $teachers = []; $staff = [];
}
?>

<!-- 
    Gunakan 'flex flex-col items-center justify-center' pada <main> 
    untuk memaksa semua konten berada di tengah secara vertikal & horizontal 
-->
<main class="min-h-screen pt-20 pb-20 bg-black flex flex-col items-center justify-center">
    
    <div class="w-full max-w-6xl mx-auto px-4 text-center">
        
        <!-- Hero Section Berada di Tengah -->
        <section class="mb-16">
            <div class="flex flex-col items-center justify-center mb-4">
                <div class="h-px w-10 bg-gold-400/70 mb-4"></div>
                <span class="text-gold-300 text-xs tracking-[.25em] uppercase">Sumber Daya Manusia</span>
            </div>
            <h1 class="font-display text-5xl lg:text-6xl font-light text-white">
                Guru & <em class="text-gold-shimmer not-italic">Staff</em>
            </h1>
            <p class="text-white/50 mt-4 font-light mx-auto max-w-md">
                Menampilkan <?= count($teachers) ?> Pendidik dan <?= count($staff) ?> Tenaga Kependidikan terbaik kami.
            </p>
        </section>

        <!-- Grid Guru (Tengah) -->
        <?php if (!empty($teachers)): ?>
            <div class="mb-20">
                <h2 class="font-display text-2xl text-white font-light mb-10 tracking-widest uppercase">Tenaga <span class="text-gold-400">Pengajar</span></h2>
                <div class="flex flex-wrap justify-center gap-8">
                    <?php foreach ($teachers as $t): ?>
                        <?php renderCard($t, UPLOAD_URL); ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Grid Staff (Tengah) -->
        <?php if (!empty($staff)): ?>
            <div class="mb-10">
                <h2 class="font-display text-2xl text-white font-light mb-10 tracking-widest uppercase">Tenaga <span class="text-gold-400">Staff</span></h2>
                <div class="flex flex-wrap justify-center gap-8">
                    <?php foreach ($staff as $s): ?>
                        <?php renderCard($s, UPLOAD_URL); ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Fallback jika kosong -->
        <?php if (empty($teachers) && empty($staff)): ?>
            <div class="glass rounded-3xl p-16 inline-block mx-auto">
                <p class="text-white/30 text-sm italic">Data belum tersedia.</p>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php require_once ROOT_PATH . 'includes/footer.php'; ?>