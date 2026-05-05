<?php
require_once __DIR__ . '/includes/config.php';

// ---- Fetch dynamic data ----
try {
    $profile     = db()->query("SELECT * FROM school_profile LIMIT 1")->fetch() ?: [];
    $heroTitle   = setting('hero_title', 'Sekolah Dasar Islam Unggulan');
    $heroSub     = setting('hero_subtitle', 'Membentuk Generasi Cerdas Berakhlak Mulia');
    $statStudents = setting('stats_students', '380');
    $statTeachers = setting('stats_teachers', '24');
    $statYears    = setting('stats_years', '62');
    $statEkskul   = setting('stats_ekskul', '12');

    $announcements = db()->query(
        "SELECT * FROM announcements 
         WHERE is_published = 1 
         ORDER BY is_pinned DESC, published_at DESC 
         LIMIT 4"
    )->fetchAll();

    $achievements = db()->query(
        "SELECT * FROM student_achievements ORDER BY year DESC LIMIT 3"
    )->fetchAll();

    $facilities = db()->query(
        "SELECT * FROM facilities WHERE `condition` = 'baik' ORDER BY sort_order LIMIT 6"
    )->fetchAll();

    $ekskuls = db()->query(
        "SELECT * FROM extracurricular WHERE is_active = 1 ORDER BY sort_order LIMIT 6"
    )->fetchAll();
} catch (Exception $e) {
    // Graceful fallback — site works even without DB during dev
    $profile = $announcements = $achievements = $facilities = $ekskuls = [];
    $heroTitle = 'Sekolah Dasar Islam Unggulan';
    $heroSub   = 'Membentuk Generasi Cerdas Berakhlak Mulia';
    $statStudents = '380'; $statTeachers = '24'; $statYears = '62'; $statEkskul = '12';
}

$heroImage = !empty($profile['hero_image']) ? UPLOAD_URL . $profile['hero_image'] 
           : 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?w=1920&q=80';
$logo      = !empty($profile['logo']) ? UPLOAD_URL . $profile['logo'] : null;
$siteName  = $profile['school_name'] ?? APP_NAME;
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($siteName) ?> — Official Website</title>
  <meta name="description" content="<?= e($heroSub) ?>">

  <!-- Fonts: Cormorant Garamond (display) + DM Sans (body) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            display: ['"Cormorant Garamond"', 'Georgia', 'serif'],
            body:    ['"DM Sans"', 'system-ui', 'sans-serif'],
          },
          colors: {
            gold: {
              300: '#f0d898',
              400: '#e8c860',
              500: '#d4aa3a',
              600: '#b8921e',
            },
            glass: {
              DEFAULT: 'rgba(255,255,255,0.07)',
              hover:   'rgba(255,255,255,0.13)',
              border:  'rgba(255,255,255,0.15)',
              strong:  'rgba(255,255,255,0.18)',
            }
          },
          backdropBlur: { xs: '2px' },
          animation: {
            'fade-up':   'fadeUp 0.7s ease forwards',
            'fade-in':   'fadeIn 0.6s ease forwards',
            'slide-down':'slideDown 0.35s cubic-bezier(.16,1,.3,1) forwards',
            'float':     'float 6s ease-in-out infinite',
            'pulse-slow':'pulse 4s cubic-bezier(0.4,0,0.6,1) infinite',
          },
          keyframes: {
            fadeUp:    { from:{ opacity:'0', transform:'translateY(28px)' }, to:{ opacity:'1', transform:'translateY(0)' } },
            fadeIn:    { from:{ opacity:'0' }, to:{ opacity:'1' } },
            slideDown: { from:{ opacity:'0', transform:'translateY(-10px)' }, to:{ opacity:'1', transform:'translateY(0)' } },
            float:     { '0%,100%':{ transform:'translateY(0)' }, '50%':{ transform:'translateY(-14px)' } },
          }
        }
      }
    }
  </script>

  <!-- Lucide Icons -->
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

  <style>
    :root { --gold: #d4aa3a; --glass: rgba(255,255,255,0.07); }

    * { font-family: 'DM Sans', sans-serif; }
    h1, h2, .font-display { font-family: 'Cormorant Garamond', Georgia, serif; }

    /* ── Scrollbar ─────────────────────────────────────── */
    ::-webkit-scrollbar { width: 5px; }
    ::-webkit-scrollbar-track { background: #0a0a0a; }
    ::-webkit-scrollbar-thumb { background: var(--gold); border-radius: 99px; }

    /* ── Glass utility ─────────────────────────────────── */
    .glass {
      background: rgba(255,255,255,0.07);
      backdrop-filter: blur(16px) saturate(1.8);
      -webkit-backdrop-filter: blur(16px) saturate(1.8);
      border: 1px solid rgba(255,255,255,0.13);
    }
    .glass-strong {
      background: rgba(255,255,255,0.14);
      backdrop-filter: blur(24px) saturate(2);
      -webkit-backdrop-filter: blur(24px) saturate(2);
      border: 1px solid rgba(255,255,255,0.22);
    }
    .glass-dark {
      background: rgba(0,0,0,0.35);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1px solid rgba(255,255,255,0.08);
    }

    /* ── Hero Parallax Background ──────────────────────── */
    .hero-bg {
      background-image: 
        linear-gradient(135deg, rgba(0,0,0,0.72) 0%, rgba(0,0,0,0.45) 50%, rgba(0,0,0,0.68) 100%),
        url('<?= $heroImage ?>');
      background-size: cover;
      background-position: center 30%;
      background-attachment: fixed;
    }

    /* ── Gold shimmer text ─────────────────────────────── */
    .text-gold-shimmer {
      background: linear-gradient(135deg, #f0d898 0%, #d4aa3a 40%, #f0d898 70%, #b8921e 100%);
      background-size: 200% 100%;
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      animation: shimmer 4s linear infinite;
    }
    @keyframes shimmer { to { background-position: -200% 0; } }

    /* ── Divider ornament ──────────────────────────────── */
    .ornament-divider::before,
    .ornament-divider::after {
      content: '';
      flex: 1;
      height: 1px;
      background: linear-gradient(90deg, transparent, rgba(212,170,58,0.5), transparent);
    }

    /* ── Nav link ──────────────────────────────────────── */
    .nav-link {
      position: relative;
      font-size: 0.8rem;
      letter-spacing: 0.1em;
      font-weight: 400;
      text-transform: uppercase;
      color: rgba(255,255,255,0.82);
      transition: color 0.25s;
      padding: 0.25rem 0;
    }
    .nav-link::after {
      content: '';
      position: absolute;
      bottom: -2px; left: 0; right: 0;
      height: 1px;
      background: var(--gold);
      transform: scaleX(0);
      transform-origin: center;
      transition: transform 0.3s cubic-bezier(.16,1,.3,1);
    }
    .nav-link:hover { color: #fff; }
    .nav-link:hover::after,
    .nav-link.active::after { transform: scaleX(1); }
    .nav-link.active { color: #f0d898; }

    /* ── Dropdown ──────────────────────────────────────── */
    .dropdown-menu {
      opacity: 0;
      visibility: hidden;
      transform: translateY(6px);
      transition: all 0.22s cubic-bezier(.16,1,.3,1);
    }
    .dropdown:hover .dropdown-menu,
    .dropdown:focus-within .dropdown-menu {
      opacity: 1;
      visibility: visible;
      transform: translateY(0);
    }

    /* ── Stat counter card ─────────────────────────────── */
    .stat-card {
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .stat-card:hover {
      transform: translateY(-4px) scale(1.02);
      box-shadow: 0 20px 60px rgba(212,170,58,0.15);
    }

    /* ── Section entry animation ───────────────────────── */
    .reveal { opacity: 0; transform: translateY(30px); transition: opacity 0.7s ease, transform 0.7s ease; }
    .reveal.visible { opacity: 1; transform: translateY(0); }

    /* ── Mobile menu ───────────────────────────────────── */
    #mobile-menu {
      transform: translateX(100%);
      transition: transform 0.38s cubic-bezier(.16,1,.3,1);
    }
    #mobile-menu.open { transform: translateX(0); }

    /* ── Announcement badge ────────────────────────────── */
    .badge-new {
      animation: badgePulse 2s ease-in-out infinite;
    }
    @keyframes badgePulse {
      0%,100% { box-shadow: 0 0 0 0 rgba(212,170,58,0.5); }
      50%      { box-shadow: 0 0 0 6px rgba(212,170,58,0); }
    }

    /* ── Announcement card hover ───────────────────────── */
    .ann-card { transition: transform 0.3s ease, box-shadow 0.3s ease, background 0.3s ease; }
    .ann-card:hover { transform: translateX(6px); background: rgba(255,255,255,0.13) !important; }
  </style>
</head>
<body class="bg-black text-white overflow-x-hidden">

<!-- ============================================================
     NAVBAR
============================================================ -->
<nav id="navbar" class="fixed top-0 left-0 right-0 z-50 transition-all duration-500">
  <div class="glass border-b border-white/[0.08]" id="navbar-inner">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16 md:h-20">

        <!-- Logo & Brand -->
        <a href="index.php" class="flex items-center gap-3 group flex-shrink-0">
          <?php if ($logo): ?>
            <img src="<?= e($logo) ?>" alt="Logo" class="w-10 h-10 object-contain transition-transform group-hover:scale-110">
          <?php else: ?>
            <div class="w-10 h-10 rounded-xl glass-strong flex items-center justify-center transition-all group-hover:scale-110 group-hover:border-gold-400/40">
              <span class="text-gold-400 font-display font-bold text-lg leading-none">ص</span>
            </div>
          <?php endif; ?>
          <div class="leading-tight">
            <div class="text-white font-medium text-sm tracking-wide">SD Muhammadiyah 1</div>
            <div class="text-gold-400 text-[10px] tracking-[0.18em] uppercase font-light">Gentasari · Cilacap</div>
          </div>
        </a>

        <!-- Desktop Navigation -->
        <div class="hidden lg:flex items-center gap-8">

          <a href="index.php" class="nav-link active">Beranda</a>

          <!-- Dropdown: Profil -->
          <div class="dropdown relative">
            <button class="nav-link flex items-center gap-1">
              Profil
              <i data-lucide="chevron-down" class="w-3 h-3 opacity-60 mt-0.5 transition-transform duration-200 group-hover:rotate-180"></i>
            </button>
            <div class="dropdown-menu absolute top-full left-1/2 -translate-x-1/2 mt-3 w-48">
              <div class="glass-dark rounded-2xl overflow-hidden py-1 shadow-2xl">
                <a href="pages/profile/sekolah.php" class="flex items-center gap-3 px-4 py-3 text-sm text-white/80 hover:text-white hover:bg-white/10 transition-all">
                  <i data-lucide="building-2" class="w-4 h-4 text-gold-400"></i> Profil Sekolah
                </a>
                <a href="pages/profile/guru-staff.php" class="flex items-center gap-3 px-4 py-3 text-sm text-white/80 hover:text-white hover:bg-white/10 transition-all">
                  <i data-lucide="users" class="w-4 h-4 text-gold-400"></i> Guru &amp; Staff
                </a>
                <a href="pages/profile/siswa.php" class="flex items-center gap-3 px-4 py-3 text-sm text-white/80 hover:text-white hover:bg-white/10 transition-all">
                  <i data-lucide="graduation-cap" class="w-4 h-4 text-gold-400"></i> Siswa
                </a>
              </div>
            </div>
          </div>

          <!-- Dropdown: Media -->
          <div class="dropdown relative">
            <button class="nav-link flex items-center gap-1">
              Media
              <i data-lucide="chevron-down" class="w-3 h-3 opacity-60 mt-0.5"></i>
            </button>
            <div class="dropdown-menu absolute top-full left-1/2 -translate-x-1/2 mt-3 w-44">
              <div class="glass-dark rounded-2xl overflow-hidden py-1 shadow-2xl">
                <a href="pages/media/galeri.php" class="flex items-center gap-3 px-4 py-3 text-sm text-white/80 hover:text-white hover:bg-white/10 transition-all">
                  <i data-lucide="image" class="w-4 h-4 text-gold-400"></i> Galeri Foto
                </a>
                <a href="pages/media/fasilitas.php" class="flex items-center gap-3 px-4 py-3 text-sm text-white/80 hover:text-white hover:bg-white/10 transition-all">
                  <i data-lucide="layout-grid" class="w-4 h-4 text-gold-400"></i> Fasilitas
                </a>
              </div>
            </div>
          </div>

          <!-- Dropdown: Aktivitas -->
          <div class="dropdown relative">
            <button class="nav-link flex items-center gap-1">
              Aktivitas
              <i data-lucide="chevron-down" class="w-3 h-3 opacity-60 mt-0.5"></i>
            </button>
            <div class="dropdown-menu absolute top-full left-1/2 -translate-x-1/2 mt-3 w-48">
              <div class="glass-dark rounded-2xl overflow-hidden py-1 shadow-2xl">
                <a href="pages/aktivitas/ekskul.php" class="flex items-center gap-3 px-4 py-3 text-sm text-white/80 hover:text-white hover:bg-white/10 transition-all">
                  <i data-lucide="sparkles" class="w-4 h-4 text-gold-400"></i> Ekstrakurikuler
                </a>
                <a href="pages/aktivitas/pengumuman.php" class="flex items-center gap-3 px-4 py-3 text-sm text-white/80 hover:text-white hover:bg-white/10 transition-all">
                  <i data-lucide="bell" class="w-4 h-4 text-gold-400"></i> Pengumuman
                </a>
              </div>
            </div>
          </div>

          <!-- Dropdown: Interaksi -->
          <div class="dropdown relative">
            <button class="nav-link flex items-center gap-1">
              Interaksi
              <i data-lucide="chevron-down" class="w-3 h-3 opacity-60 mt-0.5"></i>
            </button>
            <div class="dropdown-menu absolute top-full left-1/2 -translate-x-1/2 mt-3 w-44">
              <div class="glass-dark rounded-2xl overflow-hidden py-1 shadow-2xl">
                <a href="pages/interaksi/pengaduan.php" class="flex items-center gap-3 px-4 py-3 text-sm text-white/80 hover:text-white hover:bg-white/10 transition-all">
                  <i data-lucide="message-square-warning" class="w-4 h-4 text-gold-400"></i> Pengaduan
                </a>
                <a href="pages/interaksi/kontak.php" class="flex items-center gap-3 px-4 py-3 text-sm text-white/80 hover:text-white hover:bg-white/10 transition-all">
                  <i data-lucide="mail" class="w-4 h-4 text-gold-400"></i> Kontak
                </a>
              </div>
            </div>
          </div>

          <!-- CTA -->
          <a href="pages/interaksi/pengaduan.php"
             class="ml-2 px-5 py-2.5 rounded-full text-xs tracking-widest uppercase font-medium
                    border border-gold-400/60 text-gold-300 
                    hover:bg-gold-500/20 hover:border-gold-300 hover:text-white
                    transition-all duration-300 hover:shadow-[0_0_20px_rgba(212,170,58,0.3)]">
            Pengaduan
          </a>
        </div>

        <!-- Hamburger (mobile) -->
        <button id="hamburger" aria-label="Menu" 
                class="lg:hidden w-10 h-10 rounded-xl glass flex items-center justify-center transition-all hover:bg-white/15">
          <i data-lucide="menu" class="w-5 h-5" id="ham-icon"></i>
        </button>
      </div>
    </div>
  </div>
</nav>

<!-- Mobile Slide Menu -->
<div id="mobile-menu" class="fixed inset-y-0 right-0 w-80 z-[60] glass-dark shadow-2xl flex flex-col">
  <div class="flex items-center justify-between px-6 py-5 border-b border-white/10">
    <span class="font-display text-xl text-gold-300">Menu</span>
    <button id="close-menu" class="w-9 h-9 rounded-xl glass flex items-center justify-center hover:bg-white/15 transition-all">
      <i data-lucide="x" class="w-4 h-4"></i>
    </button>
  </div>
  <nav class="flex-1 overflow-y-auto px-4 py-6 space-y-1">
    <a href="index.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-white bg-white/10 text-sm font-medium">
      <i data-lucide="home" class="w-4 h-4 text-gold-400"></i> Beranda
    </a>
    <!-- Profile Group -->
    <div class="px-4 py-2 text-[10px] tracking-widest uppercase text-white/30 font-light mt-2">Profil</div>
    <a href="pages/profile/sekolah.php"    class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/75 hover:text-white hover:bg-white/10 transition-all text-sm"><i data-lucide="building-2" class="w-4 h-4 text-gold-400/70"></i> Profil Sekolah</a>
    <a href="pages/profile/guru-staff.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/75 hover:text-white hover:bg-white/10 transition-all text-sm"><i data-lucide="users" class="w-4 h-4 text-gold-400/70"></i> Guru &amp; Staff</a>
    <a href="pages/profile/siswa.php"      class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/75 hover:text-white hover:bg-white/10 transition-all text-sm"><i data-lucide="graduation-cap" class="w-4 h-4 text-gold-400/70"></i> Siswa</a>
    <!-- Media Group -->
    <div class="px-4 py-2 text-[10px] tracking-widest uppercase text-white/30 font-light mt-2">Media</div>
    <a href="pages/media/galeri.php"    class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/75 hover:text-white hover:bg-white/10 transition-all text-sm"><i data-lucide="image" class="w-4 h-4 text-gold-400/70"></i> Galeri Foto</a>
    <a href="pages/media/fasilitas.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/75 hover:text-white hover:bg-white/10 transition-all text-sm"><i data-lucide="layout-grid" class="w-4 h-4 text-gold-400/70"></i> Fasilitas</a>
    <!-- Aktivitas Group -->
    <div class="px-4 py-2 text-[10px] tracking-widest uppercase text-white/30 font-light mt-2">Aktivitas</div>
    <a href="pages/aktivitas/ekskul.php"       class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/75 hover:text-white hover:bg-white/10 transition-all text-sm"><i data-lucide="sparkles" class="w-4 h-4 text-gold-400/70"></i> Ekstrakurikuler</a>
    <a href="pages/aktivitas/pengumuman.php"   class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/75 hover:text-white hover:bg-white/10 transition-all text-sm"><i data-lucide="bell" class="w-4 h-4 text-gold-400/70"></i> Pengumuman</a>
    <!-- Interaksi Group -->
    <div class="px-4 py-2 text-[10px] tracking-widest uppercase text-white/30 font-light mt-2">Interaksi</div>
    <a href="pages/interaksi/pengaduan.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/75 hover:text-white hover:bg-white/10 transition-all text-sm"><i data-lucide="message-square-warning" class="w-4 h-4 text-gold-400/70"></i> Pengaduan</a>
    <a href="pages/interaksi/kontak.php"    class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/75 hover:text-white hover:bg-white/10 transition-all text-sm"><i data-lucide="mail" class="w-4 h-4 text-gold-400/70"></i> Kontak Kami</a>
  </nav>
  <div class="px-4 pb-6">
    <a href="admin/login.php" class="block text-center py-3 rounded-xl border border-gold-400/50 text-gold-300 text-sm tracking-widest uppercase hover:bg-gold-500/20 transition-all">
      Panel Admin
    </a>
  </div>
</div>
<div id="menu-overlay" class="fixed inset-0 bg-black/50 z-[55] hidden backdrop-blur-sm"></div>

<!-- ============================================================
     HERO SECTION
============================================================ -->
<section class="hero-bg relative min-h-screen flex flex-col" id="hero">

  <!-- Ambient glow orbs -->
  <div class="absolute inset-0 overflow-hidden pointer-events-none">
    <div class="absolute -top-40 -right-40 w-96 h-96 rounded-full bg-gold-500/10 blur-[100px] animate-pulse-slow"></div>
    <div class="absolute bottom-20 -left-20 w-80 h-80 rounded-full bg-blue-500/8 blur-[80px] animate-pulse-slow" style="animation-delay:2s"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full bg-white/[0.02] blur-[120px]"></div>
  </div>

  <!-- Geometric accents -->
  <div class="absolute top-32 right-12 w-px h-32 bg-gradient-to-b from-transparent via-gold-400/40 to-transparent hidden lg:block"></div>
  <div class="absolute top-32 right-12 w-32 h-px bg-gradient-to-r from-transparent via-gold-400/40 to-transparent hidden lg:block"></div>
  <div class="absolute bottom-40 left-12 w-px h-32 bg-gradient-to-b from-transparent via-white/20 to-transparent hidden lg:block"></div>

  <!-- Main hero content -->
  <div class="flex-1 flex items-center relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full pt-28 pb-16">
      <div class="max-w-3xl">

        <!-- Eyebrow -->
        <div class="flex items-center gap-3 mb-6 animate-fade-up" style="animation-delay:.1s">
          <div class="h-px w-12 bg-gold-400/70"></div>
          <span class="text-gold-300 text-xs tracking-[0.25em] uppercase font-light">Akreditasi A · Est. <?= e($profile['tahun_berdiri'] ?? '1962') ?></span>
          <div class="h-px w-6 bg-gold-400/40"></div>
        </div>

        <!-- Main heading -->
        <h1 class="font-display text-5xl sm:text-6xl lg:text-7xl xl:text-8xl font-light leading-[1.05] mb-4 animate-fade-up" style="animation-delay:.2s">
          <span class="text-white"><?= e($heroTitle) ?></span>
        </h1>
        <h1 class="font-display text-5xl sm:text-6xl lg:text-7xl xl:text-8xl font-light leading-[1.05] mb-8 animate-fade-up" style="animation-delay:.3s">
          <em class="text-gold-shimmer not-italic">Terpercaya.</em>
        </h1>

        <!-- Sub-copy -->
        <p class="text-white/60 text-base sm:text-lg font-light leading-relaxed max-w-xl mb-10 animate-fade-up" style="animation-delay:.4s">
          <?= e($heroSub) ?> — di bawah naungan <span class="text-white/85">Persyarikatan Muhammadiyah</span> sejak <?= e($profile['tahun_berdiri'] ?? '1962') ?>.
        </p>

        <!-- CTAs -->
        <div class="flex flex-wrap gap-4 animate-fade-up" style="animation-delay:.5s">
          <a href="pages/profile/sekolah.php"
             class="group inline-flex items-center gap-2.5 px-8 py-4 rounded-2xl
                    bg-gold-500 hover:bg-gold-400 text-black font-medium text-sm tracking-wide
                    transition-all duration-300 hover:scale-105 hover:shadow-[0_10px_40px_rgba(212,170,58,0.4)]">
            Kenali Kami
            <i data-lucide="arrow-right" class="w-4 h-4 transition-transform group-hover:translate-x-1"></i>
          </a>
          <a href="pages/interaksi/kontak.php"
             class="group inline-flex items-center gap-2.5 px-8 py-4 rounded-2xl
                    glass border-white/20 text-white/85 hover:text-white font-light text-sm tracking-wide
                    transition-all duration-300 hover:bg-white/15 hover:scale-105">
            Hubungi Kami
            <i data-lucide="send" class="w-4 h-4 opacity-60 group-hover:opacity-100 transition-all group-hover:translate-x-1"></i>
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- STATS BAR -->
  <div class="relative w-full pb-0 animate-fade-up" style="animation-delay:.65s">
    <div class="glass border-t border-white/10">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 lg:grid-cols-4 divide-y lg:divide-y-0 lg:divide-x divide-white/10">

          <?php
          $stats = [
            ['value' => $statStudents, 'label' => 'Siswa Aktif',    'icon' => 'users',         'suffix' => '+'],
            ['value' => $statTeachers, 'label' => 'Tenaga Pendidik','icon' => 'graduation-cap', 'suffix' => ''],
            ['value' => $statYears,    'label' => 'Tahun Berdiri',  'icon' => 'calendar',       'suffix' => 'Thn'],
            ['value' => $statEkskul,   'label' => 'Ekstrakurikuler','icon' => 'sparkles',        'suffix' => ''],
          ];
          foreach ($stats as $i => $s): ?>
          <div class="stat-card flex items-center gap-4 px-6 py-6 group cursor-default <?= $i < 2 ? 'col-span-1' : 'col-span-1' ?>">
            <div class="w-11 h-11 rounded-xl glass-strong flex items-center justify-center flex-shrink-0 group-hover:border-gold-400/40 transition-all">
              <i data-lucide="<?= e($s['icon']) ?>" class="w-5 h-5 text-gold-400"></i>
            </div>
            <div>
              <div class="font-display text-2xl lg:text-3xl font-light text-white leading-none">
                <?= e($s['value']) ?><span class="text-gold-400 text-lg ml-0.5"><?= e($s['suffix']) ?></span>
              </div>
              <div class="text-white/45 text-xs tracking-wide mt-1"><?= e($s['label']) ?></div>
            </div>
          </div>
          <?php endforeach; ?>

        </div>
      </div>
    </div>
  </div>

  <!-- Scroll indicator -->
  <div class="absolute bottom-28 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 opacity-40 animate-float">
    <span class="text-[10px] tracking-[0.2em] uppercase text-white/60">Scroll</span>
    <i data-lucide="chevrons-down" class="w-4 h-4 text-white/60"></i>
  </div>
</section>

<!-- ============================================================
     VISI & MISI SECTION
============================================================ -->
<?php if (!empty($profile['visi']) || !empty($profile['misi'])): ?>
<section class="relative bg-gradient-to-b from-black via-zinc-950 to-black py-28 overflow-hidden">

  <div class="absolute inset-0 pointer-events-none">
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-px h-32 bg-gradient-to-b from-gold-400/40 to-transparent"></div>
  </div>

  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Section header -->
    <div class="text-center mb-20 reveal">
      <div class="flex items-center justify-center gap-4 ornament-divider mb-5">
        <span class="text-gold-400 text-xs tracking-[0.3em] uppercase font-light px-4">Visi & Misi</span>
      </div>
      <h2 class="font-display text-4xl lg:text-5xl font-light text-white">Landasan <em class="text-gold-shimmer not-italic">Kami</em></h2>
    </div>

    <div class="grid lg:grid-cols-2 gap-8">
      <!-- Visi -->
      <div class="glass rounded-3xl p-8 lg:p-10 reveal group hover:bg-white/10 transition-all duration-500 hover:-translate-y-1">
        <div class="flex items-start gap-5">
          <div class="w-14 h-14 rounded-2xl glass-strong flex items-center justify-center flex-shrink-0 group-hover:border-gold-400/40 transition-all">
            <i data-lucide="eye" class="w-6 h-6 text-gold-400"></i>
          </div>
          <div>
            <h3 class="font-display text-2xl text-white font-light mb-3">Visi</h3>
            <p class="text-white/60 font-light leading-relaxed text-sm"><?= nl2br(e($profile['visi'])) ?></p>
          </div>
        </div>
      </div>
      <!-- Misi -->
      <div class="glass rounded-3xl p-8 lg:p-10 reveal group hover:bg-white/10 transition-all duration-500 hover:-translate-y-1">
        <div class="flex items-start gap-5">
          <div class="w-14 h-14 rounded-2xl glass-strong flex items-center justify-center flex-shrink-0 group-hover:border-gold-400/40 transition-all">
            <i data-lucide="target" class="w-6 h-6 text-gold-400"></i>
          </div>
          <div>
            <h3 class="font-display text-2xl text-white font-light mb-3">Misi</h3>
            <p class="text-white/60 font-light leading-relaxed text-sm"><?= nl2br(e($profile['misi'])) ?></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ============================================================
     ANNOUNCEMENTS SECTION
============================================================ -->
<section class="relative bg-zinc-950 py-28">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="flex items-end justify-between mb-16 reveal">
      <div>
        <div class="flex items-center gap-3 mb-3">
          <div class="h-px w-8 bg-gold-400/60"></div>
          <span class="text-gold-400 text-xs tracking-[0.25em] uppercase font-light">Info Terkini</span>
        </div>
        <h2 class="font-display text-4xl lg:text-5xl font-light text-white">Pengumuman</h2>
      </div>
      <a href="pages/aktivitas/pengumuman.php"
         class="hidden sm:flex items-center gap-2 text-sm text-white/50 hover:text-gold-300 transition-all group">
        Lihat Semua
        <i data-lucide="arrow-right" class="w-4 h-4 transition-transform group-hover:translate-x-1"></i>
      </a>
    </div>

    <?php if (empty($announcements)): ?>
    <div class="glass rounded-3xl p-12 text-center text-white/30 reveal">
      <i data-lucide="bell-off" class="w-10 h-10 mx-auto mb-3 opacity-40"></i>
      <p class="text-sm">Belum ada pengumuman saat ini.</p>
    </div>
    <?php else: ?>
    <div class="space-y-4">
      <?php foreach ($announcements as $i => $ann):
        $isNew = isNew($ann['published_at']);
        $catColor = match($ann['category']) {
          'penting'   => 'text-red-300 bg-red-500/15 border-red-500/30',
          'akademik'  => 'text-blue-300 bg-blue-500/15 border-blue-500/30',
          'kegiatan'  => 'text-green-300 bg-green-500/15 border-green-500/30',
          default     => 'text-white/50 bg-white/5 border-white/15',
        };
      ?>
      <a href="pages/aktivitas/pengumuman.php?id=<?= $ann['id'] ?>"
         class="ann-card glass rounded-2xl p-6 flex items-center gap-5 group reveal block"
         style="animation-delay:<?= $i * 0.08 ?>s">

        <!-- Index number -->
        <div class="font-display text-3xl text-white/10 font-light w-10 text-center flex-shrink-0 group-hover:text-gold-400/30 transition-colors">
          <?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?>
        </div>

        <!-- Gold line accent -->
        <div class="w-px self-stretch bg-gradient-to-b from-gold-400/60 via-gold-400/20 to-transparent flex-shrink-0"></div>

        <div class="flex-1 min-w-0">
          <div class="flex flex-wrap items-center gap-2 mb-2">
            <span class="text-[10px] px-2.5 py-0.5 rounded-full border font-light tracking-wide <?= $catColor ?>">
              <?= ucfirst(e($ann['category'])) ?>
            </span>
            <?php if ($isNew): ?>
            <span class="badge-new text-[10px] px-2.5 py-0.5 rounded-full bg-gold-500/20 border border-gold-400/40 text-gold-300 font-medium tracking-wide">
              ✦ Terbaru
            </span>
            <?php endif; ?>
            <?php if ($ann['is_pinned']): ?>
            <span class="text-[10px] px-2.5 py-0.5 rounded-full bg-white/5 border border-white/15 text-white/40 tracking-wide">
              📌 Disematkan
            </span>
            <?php endif; ?>
          </div>
          <h3 class="text-white/85 font-medium text-sm group-hover:text-white transition-colors line-clamp-1">
            <?= e($ann['title']) ?>
          </h3>
        </div>

        <div class="flex-shrink-0 text-right">
          <div class="text-white/30 text-xs"><?= date('d M Y', strtotime($ann['published_at'])) ?></div>
          <i data-lucide="chevron-right" class="w-4 h-4 text-white/20 group-hover:text-gold-400 transition-all mt-1 ml-auto group-hover:translate-x-1"></i>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- ============================================================
     FACILITIES SECTION
============================================================ -->
<?php if (!empty($facilities)): ?>
<section class="relative bg-gradient-to-b from-zinc-950 to-black py-28">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="text-center mb-16 reveal">
      <div class="flex items-center justify-center gap-4 ornament-divider mb-5">
        <span class="text-gold-400 text-xs tracking-[0.3em] uppercase font-light px-4">Sarana & Prasarana</span>
      </div>
      <h2 class="font-display text-4xl lg:text-5xl font-light text-white">Fasilitas <em class="text-gold-shimmer not-italic">Unggulan</em></h2>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
      <?php foreach ($facilities as $i => $f): ?>
      <div class="glass rounded-2xl p-5 text-center group hover:bg-white/12 hover:-translate-y-1.5 transition-all duration-300 reveal"
           style="animation-delay:<?= $i * 0.06 ?>s">
        <div class="w-12 h-12 rounded-xl glass-strong mx-auto flex items-center justify-center mb-3 group-hover:border-gold-400/40 transition-all">
          <i data-lucide="<?= e($f['icon'] ?? 'square') ?>" class="w-5 h-5 text-gold-400"></i>
        </div>
        <div class="font-display text-2xl text-white font-light"><?= e($f['count']) ?></div>
        <div class="text-white/50 text-xs mt-1"><?= e($f['name']) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ============================================================
     CTA BAND
============================================================ -->
<section class="relative py-24 overflow-hidden">
  <div class="absolute inset-0"
       style="background: linear-gradient(135deg, rgba(212,170,58,0.08) 0%, rgba(0,0,0,0.98) 40%, rgba(212,170,58,0.05) 100%);">
  </div>
  <div class="absolute inset-0 border-y border-gold-400/10"></div>

  <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center reveal">
    <div class="font-display text-3xl sm:text-4xl lg:text-5xl font-light text-white mb-6 leading-snug">
      Bersama kami, <em class="text-gold-shimmer not-italic">buah hati Anda</em><br>
      akan tumbuh menjadi yang terbaik.
    </div>
    <p class="text-white/45 font-light mb-10 text-base">Hubungi kami untuk informasi pendaftaran atau kunjungi sekolah kami secara langsung.</p>
    <div class="flex flex-wrap justify-center gap-4">
      <a href="pages/interaksi/kontak.php"
         class="inline-flex items-center gap-2.5 px-8 py-4 rounded-2xl bg-gold-500 hover:bg-gold-400 text-black font-medium text-sm tracking-wide transition-all duration-300 hover:scale-105 hover:shadow-[0_10px_40px_rgba(212,170,58,0.35)]">
        <i data-lucide="phone-call" class="w-4 h-4"></i>
        Hubungi Sekarang
      </a>
      <a href="pages/aktivitas/pengumuman.php"
         class="inline-flex items-center gap-2.5 px-8 py-4 rounded-2xl glass border-white/20 text-white/85 hover:text-white font-light text-sm tracking-wide transition-all duration-300 hover:bg-white/12 hover:scale-105">
        <i data-lucide="newspaper" class="w-4 h-4 opacity-60"></i>
        Info Terkini
      </a>
    </div>
  </div>
</section>

<!-- ============================================================
     FOOTER
============================================================ -->
<footer class="bg-black border-t border-white/[0.06]">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-10">
      <!-- Brand -->
      <div class="lg:col-span-2">
        <div class="flex items-center gap-3 mb-5">
          <div class="w-10 h-10 rounded-xl glass-strong flex items-center justify-center">
            <span class="text-gold-400 font-display font-bold text-lg">ص</span>
          </div>
          <div>
            <div class="text-white font-medium text-sm">SD Muhammadiyah 1</div>
            <div class="text-gold-400 text-[10px] tracking-widest uppercase font-light">Gentasari · Cilacap</div>
          </div>
        </div>
        <p class="text-white/35 font-light text-sm leading-relaxed max-w-xs">
          <?= e($profile['visi'] ? substr($profile['visi'], 0, 100) . '…' : 'Menjadi sekolah Islam unggulan yang membentuk generasi cerdas berkarakter.') ?>
        </p>
        <?php if (!empty($profile['instagram']) || !empty($profile['facebook'])): ?>
        <div class="flex gap-3 mt-5">
          <?php if (!empty($profile['instagram'])): ?>
          <a href="<?= e($profile['instagram']) ?>" target="_blank"
             class="w-9 h-9 glass rounded-xl flex items-center justify-center hover:bg-white/15 transition-all hover:scale-110">
            <i data-lucide="instagram" class="w-4 h-4 text-white/60"></i>
          </a>
          <?php endif; ?>
          <?php if (!empty($profile['facebook'])): ?>
          <a href="<?= e($profile['facebook']) ?>" target="_blank"
             class="w-9 h-9 glass rounded-xl flex items-center justify-center hover:bg-white/15 transition-all hover:scale-110">
            <i data-lucide="facebook" class="w-4 h-4 text-white/60"></i>
          </a>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- Quick links -->
      <div>
        <h4 class="text-white/80 text-xs tracking-[0.2em] uppercase mb-5 font-medium">Navigasi</h4>
        <ul class="space-y-3 text-sm">
          <?php foreach ([
            ['pages/profile/sekolah.php','Profil Sekolah'],
            ['pages/profile/guru-staff.php','Guru & Staff'],
            ['pages/media/galeri.php','Galeri'],
            ['pages/aktivitas/pengumuman.php','Pengumuman'],
            ['pages/interaksi/kontak.php','Kontak'],
          ] as $link): ?>
          <li><a href="<?= $link[0] ?>" class="text-white/35 hover:text-gold-300 transition-colors flex items-center gap-2">
            <i data-lucide="chevron-right" class="w-3 h-3"></i><?= e($link[1]) ?>
          </a></li>
          <?php endforeach; ?>
        </ul>
      </div>

      <!-- Contact -->
      <div>
        <h4 class="text-white/80 text-xs tracking-[0.2em] uppercase mb-5 font-medium">Kontak</h4>
        <ul class="space-y-4 text-sm">
          <?php if (!empty($profile['address'])): ?>
          <li class="flex gap-3 text-white/35">
            <i data-lucide="map-pin" class="w-4 h-4 text-gold-400/60 flex-shrink-0 mt-0.5"></i>
            <span class="leading-relaxed"><?= e($profile['address']) ?>, <?= e($profile['village'] ?? '') ?>, <?= e($profile['city'] ?? '') ?></span>
          </li>
          <?php endif; ?>
          <?php if (!empty($profile['phone'])): ?>
          <li class="flex gap-3 text-white/35">
            <i data-lucide="phone" class="w-4 h-4 text-gold-400/60 flex-shrink-0"></i>
            <span><?= e($profile['phone']) ?></span>
          </li>
          <?php endif; ?>
          <?php if (!empty($profile['email'])): ?>
          <li class="flex gap-3 text-white/35">
            <i data-lucide="mail" class="w-4 h-4 text-gold-400/60 flex-shrink-0"></i>
            <span><?= e($profile['email']) ?></span>
          </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>

    <div class="mt-14 pt-8 border-t border-white/[0.06] flex flex-col sm:flex-row items-center justify-between gap-4">
      <p class="text-white/20 text-xs">
        &copy; <?= date('Y') ?> <?= e($siteName) ?>. All rights reserved.
      </p>
      <a href="admin/login.php" class="text-white/15 hover:text-white/40 transition-colors text-xs flex items-center gap-1.5">
        <i data-lucide="lock" class="w-3 h-3"></i> Admin Panel
      </a>
    </div>
  </div>
</footer>

<!-- ============================================================
     SCRIPTS
============================================================ -->
<script>
  // ── Init Lucide icons ──────────────────────────────────────
  lucide.createIcons();

  // ── Navbar scroll effect ──────────────────────────────────
  const navbar = document.getElementById('navbar');
  const navInner = document.getElementById('navbar-inner');
  window.addEventListener('scroll', () => {
    if (window.scrollY > 40) {
      navInner.style.background = 'rgba(0,0,0,0.85)';
      navInner.style.backdropFilter = 'blur(24px) saturate(2)';
    } else {
      navInner.style.background = '';
      navInner.style.backdropFilter = '';
    }
  }, { passive: true });

  // ── Mobile menu ────────────────────────────────────────────
  const hamburger   = document.getElementById('hamburger');
  const closeMenu   = document.getElementById('close-menu');
  const mobileMenu  = document.getElementById('mobile-menu');
  const menuOverlay = document.getElementById('menu-overlay');

  function openMenu() {
    mobileMenu.classList.add('open');
    menuOverlay.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
  }
  function closeMenuFn() {
    mobileMenu.classList.remove('open');
    menuOverlay.classList.add('hidden');
    document.body.style.overflow = '';
  }
  hamburger.addEventListener('click', openMenu);
  closeMenu.addEventListener('click', closeMenuFn);
  menuOverlay.addEventListener('click', closeMenuFn);

  // ── Scroll reveal ──────────────────────────────────────────
  const revealEls = document.querySelectorAll('.reveal');
  const observer  = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
  revealEls.forEach(el => observer.observe(el));

  // ── Re-init icons after dynamic content ───────────────────
  document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
</script>

</body>
</html>
