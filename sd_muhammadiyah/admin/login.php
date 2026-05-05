<?php
require_once __DIR__ . '/../includes/config.php';

// Redirect if already logged in
if (!empty($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Invalid CSRF token.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username && $password) {
            $stmt = db()->prepare(
                "SELECT id, username, password, full_name, role, is_active 
                 FROM admin_users WHERE username = ? LIMIT 1"
            );
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && $user['is_active'] && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['admin_id']       = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin_name']     = $user['full_name'];
                $_SESSION['admin_role']     = $user['role'];

                db()->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?")
                   ->execute([$user['id']]);

                header('Location: index.php');
                exit;
            } else {
                $error = 'Username atau password salah.';
            }
        } else {
            $error = 'Semua field wajib diisi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login — SD Muhammadiyah 1 Gentasari</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;1,300&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: { extend: {
        fontFamily: { display: ['"Cormorant Garamond"','serif'], body:['"DM Sans"','sans-serif'] }
      }}
    }
  </script>
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
  <style>
    body { font-family: 'DM Sans', sans-serif; }
    .glass { background:rgba(255,255,255,0.07); backdrop-filter:blur(20px) saturate(1.8); -webkit-backdrop-filter:blur(20px) saturate(1.8); border:1px solid rgba(255,255,255,0.13); }
    .input-glass { background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.12); color:#fff; transition:all .25s; }
    .input-glass:focus { outline:none; background:rgba(255,255,255,0.1); border-color:rgba(212,170,58,0.6); box-shadow:0 0 0 3px rgba(212,170,58,0.1); }
    .input-glass::placeholder { color:rgba(255,255,255,0.3); }
    .text-gold-shimmer {
      background:linear-gradient(135deg,#f0d898,#d4aa3a,#f0d898);
      background-size:200% 100%;
      -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
      animation:shimmer 4s linear infinite;
    }
    @keyframes shimmer { to { background-position:-200% 0; } }
  </style>
</head>
<body class="min-h-screen bg-black flex items-center justify-center relative overflow-hidden">

  <!-- Background -->
  <div class="absolute inset-0"
       style="background-image:linear-gradient(135deg,rgba(0,0,0,0.95) 0%,rgba(0,0,0,0.85) 100%), url('https://images.unsplash.com/photo-1580582932707-520aed937b7b?w=1920&q=60'); background-size:cover; background-position:center;">
  </div>
  <div class="absolute top-1/4 right-1/4 w-96 h-96 rounded-full bg-yellow-500/5 blur-[120px]"></div>
  <div class="absolute bottom-1/4 left-1/4 w-64 h-64 rounded-full bg-blue-500/5 blur-[80px]"></div>

  <!-- Login Card -->
  <div class="relative w-full max-w-md mx-4">
    <div class="glass rounded-3xl p-8 lg:p-10">
      <!-- Logo -->
      <div class="text-center mb-8">
        <div class="w-16 h-16 mx-auto glass rounded-2xl flex items-center justify-center mb-4" style="border:1px solid rgba(212,170,58,0.3)">
          <span class="font-display text-3xl font-light" style="color:#d4aa3a">ص</span>
        </div>
        <h1 class="font-display text-3xl font-light text-white mb-1">Admin Panel</h1>
        <p class="text-white/35 text-xs tracking-widest uppercase">SD Muhammadiyah 1 Gentasari</p>
      </div>

      <!-- Error message -->
      <?php if ($error): ?>
      <div class="mb-5 px-4 py-3 rounded-xl bg-red-500/10 border border-red-500/25 text-red-300 text-sm flex items-center gap-2">
        <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
        <?= e($error) ?>
      </div>
      <?php endif; ?>

      <form method="POST" action="">
        <?= csrf_field() ?>

        <div class="space-y-4">
          <div>
            <label class="block text-white/50 text-xs tracking-widest uppercase mb-2">Username</label>
            <div class="relative">
              <i data-lucide="user" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-white/30 pointer-events-none"></i>
              <input type="text" name="username" required autocomplete="username"
                     value="<?= e($_POST['username'] ?? '') ?>"
                     placeholder="Masukkan username"
                     class="input-glass w-full pl-11 pr-4 py-3.5 rounded-xl text-sm font-light">
            </div>
          </div>

          <div>
            <label class="block text-white/50 text-xs tracking-widest uppercase mb-2">Password</label>
            <div class="relative">
              <i data-lucide="lock" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-white/30 pointer-events-none"></i>
              <input type="password" name="password" id="password" required autocomplete="current-password"
                     placeholder="••••••••"
                     class="input-glass w-full pl-11 pr-11 py-3.5 rounded-xl text-sm font-light">
              <button type="button" id="toggle-pw" class="absolute right-4 top-1/2 -translate-y-1/2 text-white/30 hover:text-white/60 transition-colors">
                <i data-lucide="eye" class="w-4 h-4" id="eye-icon"></i>
              </button>
            </div>
          </div>
        </div>

        <button type="submit"
                class="mt-8 w-full py-4 rounded-2xl font-medium text-sm tracking-wide text-black
                       transition-all duration-300 hover:scale-[1.02] hover:shadow-[0_10px_40px_rgba(212,170,58,0.4)]"
                style="background:linear-gradient(135deg,#d4aa3a,#e8c860)">
          Masuk ke Dashboard
        </button>
      </form>

      <div class="mt-6 text-center">
        <a href="../index.php" class="text-white/25 hover:text-white/50 transition-colors text-xs flex items-center justify-center gap-1.5">
          <i data-lucide="arrow-left" class="w-3 h-3"></i> Kembali ke Situs
        </a>
      </div>
    </div>
  </div>

  <script>
    lucide.createIcons();

    // Toggle password visibility
    const togglePw = document.getElementById('toggle-pw');
    const pwInput  = document.getElementById('password');
    const eyeIcon  = document.getElementById('eye-icon');
    togglePw.addEventListener('click', () => {
      if (pwInput.type === 'password') {
        pwInput.type = 'text';
        eyeIcon.setAttribute('data-lucide', 'eye-off');
      } else {
        pwInput.type = 'password';
        eyeIcon.setAttribute('data-lucide', 'eye');
      }
      lucide.createIcons();
    });

    <?php if ($error): ?>
    Swal.fire({
      toast: true, position: 'top-end', timer: 3500, timerProgressBar: true,
      icon: 'error', title: '<?= addslashes($error) ?>',
      showConfirmButton: false,
      background: 'rgba(20,20,20,0.95)',
      color: '#fff',
    });
    <?php endif; ?>
  </script>
</body>
</html>
