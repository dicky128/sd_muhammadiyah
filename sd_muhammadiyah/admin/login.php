<?php
require_once __DIR__ . '/../includes/config.php';

// Already logged in → redirect
if (!empty($_SESSION['admin_id'])) {
    header('Location: index.php'); exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Token keamanan tidak valid. Refresh halaman dan coba lagi.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$username || !$password) {
            $error = 'Username dan password wajib diisi.';
        } else {
            try {
                $stmt = db()->prepare(
                    "SELECT id,username,password,full_name,role,is_active
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

                    db()->prepare("UPDATE admin_users SET last_login=NOW() WHERE id=?")
                       ->execute([$user['id']]);

                    header('Location: index.php'); exit;
                } else {
                    $error = 'Username atau password salah, atau akun dinonaktifkan.';
                }
            } catch (Exception $e) {
                $error = 'Terjadi kesalahan sistem. Silakan coba lagi.';
            }
        }
    }
}
$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Login Admin — <?= e(APP_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/light3d.css">
<style>
  * { font-family: 'Plus Jakarta Sans', sans-serif; }
  h1, h2 { font-family: 'Playfair Display', serif; }

  /* Input light for this page */
  .input-login {
    width: 100%;
    padding: .875rem 1rem .875rem 3rem;
    border-radius: 12px;
    background: rgba(255,255,255,.85);
    border: 1.5px solid rgba(212,170,58,.2);
    color: #1a1228;
    font-size: .9rem;
    transition: all .2s;
    font-family: 'Plus Jakarta Sans', sans-serif;
  }
  .input-login:focus {
    outline: none;
    border-color: #f472b6;
    box-shadow: 0 0 0 3px rgba(244,114,182,.12);
    background: #fff;
  }
  .input-login::placeholder { color: #9ca3af; }

  @keyframes fadeUp { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:none} }
  @keyframes float-y { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-16px)} }
  .float-y { animation: float-y 6s ease-in-out infinite; }
</style>
</head>

<body class="light-mode min-h-screen flex items-center justify-center px-4"
      style="background: linear-gradient(135deg, #fdf2f8 0%, #fef9e7 50%, #f0f9ff 100%)">

<!-- Background orbs -->
<div class="orb orb-pink w-80 h-80" style="position:fixed;top:-10%;right:-5%;opacity:.5"></div>
<div class="orb orb-gold w-56 h-56" style="position:fixed;bottom:5%;left:-5%;opacity:.4"></div>
<div class="orb orb-sky  w-64 h-64" style="position:fixed;top:40%;right:15%;opacity:.35;animation-delay:2s"></div>

<!-- Grid lines -->
<div class="grid-lines" style="position:fixed;inset:0;pointer-events:none;opacity:.5"></div>

<!-- Floating decorations -->
<div class="float-y" style="position:fixed;top:18%;left:8%;animation-delay:.5s">
  <div style="width:40px;height:40px;border-radius:10px;border:2px solid rgba(244,114,182,.3);transform:rotate(15deg)"></div>
</div>
<div class="float-y" style="position:fixed;bottom:22%;right:8%;animation-delay:2s">
  <div style="width:24px;height:24px;border-radius:50%;border:2px solid rgba(212,170,58,.4)"></div>
</div>

<!-- Login Card -->
<div class="relative z-10 w-full max-w-md" style="opacity:0;animation:fadeUp .8s .1s ease forwards">

  <!-- Branding above card -->
  <div class="text-center mb-8">
    <a href="<?= APP_URL ?>/index.php" class="inline-flex flex-col items-center gap-3 group">
      <div class="w-16 h-16 rounded-2xl flex items-center justify-center transition-transform group-hover:scale-110"
           style="background:linear-gradient(135deg,#fbcfe8,#fef3c7);border:2px solid rgba(244,114,182,.3);box-shadow:0 8px 30px rgba(244,114,182,.2)">
        <span style="font-family:'Playfair Display',serif;color:#be185d;font-weight:700;font-size:1.6rem">ص</span>
      </div>
      <div>
        <div class="font-display font-bold text-gray-800 text-lg leading-tight">SD Muhammadiyah 1</div>
        <div class="text-xs tracking-widest uppercase font-semibold text-pink-400 mt-0.5">Gentasari · Cilacap</div>
      </div>
    </a>
  </div>

  <!-- Card -->
  <div class="glass-card rounded-3xl p-8 lg:p-10"
       style="box-shadow:0 20px 60px rgba(244,114,182,.18),0 8px 20px rgba(0,0,0,.06)">

    <!-- Card inner shimmer -->
    <div style="position:absolute;inset:0;background:linear-gradient(135deg,rgba(255,255,255,.6) 0%,transparent 60%);border-radius:inherit;pointer-events:none"></div>

    <div class="relative z-10">
      <h1 class="font-display font-bold text-gray-800 text-2xl mb-1">Panel Admin</h1>
      <p class="text-gray-400 text-sm mb-7">Masuk ke dashboard pengelolaan situs</p>

      <!-- Error banner -->
      <?php if ($error): ?>
      <div class="flex items-center gap-3 px-4 py-3 rounded-2xl mb-5 text-sm"
           style="background:rgba(239,68,68,.07);border:1px solid rgba(239,68,68,.2);color:#dc2626">
        <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
        <span><?= e($error) ?></span>
      </div>
      <?php endif; ?>

      <!-- Form -->
      <form method="POST" action="" class="space-y-4">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

        <!-- Username -->
        <div>
          <label class="block text-xs font-bold tracking-widest uppercase text-gray-400 mb-2">Username</label>
          <div class="relative">
            <i data-lucide="user" class="absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none w-4 h-4 text-pink-400"></i>
            <input type="text" name="username" required autocomplete="username"
                   value="<?= e($_POST['username'] ?? '') ?>"
                   placeholder="Masukkan username"
                   class="input-login">
          </div>
        </div>

        <!-- Password -->
        <div>
          <label class="block text-xs font-bold tracking-widest uppercase text-gray-400 mb-2">Password</label>
          <div class="relative">
            <i data-lucide="lock" class="absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none w-4 h-4 text-pink-400"></i>
            <input type="password" name="password" id="pw-input" required autocomplete="current-password"
                   placeholder="••••••••"
                   class="input-login" style="padding-right:3rem">
            <button type="button" id="toggle-pw"
                    class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-pink-500 transition-colors">
              <i data-lucide="eye" id="pw-eye" class="w-4 h-4"></i>
            </button>
          </div>
        </div>

        <!-- Submit -->
        <button type="submit"
                class="w-full btn-primary-light justify-center !rounded-2xl !py-4 !text-base mt-2">
          <i data-lucide="log-in" class="w-4 h-4"></i>
          Masuk ke Dashboard
        </button>
      </form>

      <!-- Back to site -->
      <div class="mt-6 text-center">
        <a href="<?= APP_URL ?>/index.php"
           class="inline-flex items-center gap-2 text-sm text-gray-400 hover:text-pink-500 transition-colors font-medium">
          <i data-lucide="arrow-left" class="w-4 h-4"></i>
          Kembali ke Situs
        </a>
      </div>
    </div>
  </div>

  <!-- Security note -->
  <p class="text-center text-xs text-gray-400 mt-5 flex items-center justify-center gap-1.5">
    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-green-400"></i>
    Koneksi aman · Session terenkripsi
  </p>
</div>

<script>
lucide.createIcons();

// Toggle password visibility
document.getElementById('toggle-pw').addEventListener('click', function() {
  const inp = document.getElementById('pw-input');
  const ico = document.getElementById('pw-eye');
  if (inp.type === 'password') {
    inp.type = 'text';
    ico.setAttribute('data-lucide', 'eye-off');
  } else {
    inp.type = 'password';
    ico.setAttribute('data-lucide', 'eye');
  }
  lucide.createIcons();
});

<?php if ($error): ?>
// SweetAlert toast for error
Swal.fire({
  toast: true, position: 'top-end',
  icon: 'error',
  title: '<?= addslashes(e($error)) ?>',
  showConfirmButton: false,
  timer: 4000, timerProgressBar: true,
  background: '#fff',
  color: '#1a1228',
  customClass: { popup: 'rounded-2xl' }
});
<?php endif; ?>

<?php if (isset($_GET['logout'])): ?>
Swal.fire({
  toast: true, position: 'top-end',
  icon: 'success',
  title: 'Berhasil logout. Sampai jumpa!',
  showConfirmButton: false,
  timer: 3000, timerProgressBar: true,
  background: '#fff',
  color: '#1a1228',
  customClass: { popup: 'rounded-2xl' }
});
<?php endif; ?>
</script>
</body>
</html>