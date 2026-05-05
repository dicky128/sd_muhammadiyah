<?php
// admin/pages/users.php — Admin User Management (superadmin only)
require_once __DIR__ . '/../includes/auth.php';

// Superadmin guard
if (!is_superadmin()) {
    http_response_code(403);
    echo '<div style="background:#0a0a0a;color:#fff;display:flex;height:100vh;align-items:center;justify-content:center;font-family:sans-serif"><div style="text-align:center"><p style="font-size:2rem;color:rgba(255,255,255,.2)">403</p><p style="color:rgba(255,255,255,.4);margin-top:8px">Akses ditolak. Hanya Superadmin.</p><a href="../index.php" style="color:#d4aa3a;font-size:.85rem;margin-top:16px;display:block">← Dashboard</a></div></div>';
    exit;
}

$activeSidebar = 'users';
$pageTitle     = 'Manajemen User';
$pageSubtitle  = 'Kelola akun admin & editor CMS';

// ── AJAX handler ──────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    if (!verify_csrf()) { echo json_encode(['ok'=>false,'msg'=>'Token tidak valid.']); exit; }
    $action = $_POST['action'] ?? '';

    // GET single
    if ($action === 'get') {
        $stmt = db()->prepare("SELECT id,username,full_name,email,role,is_active,last_login,created_at FROM admin_users WHERE id=?");
        $stmt->execute([(int)$_POST['id']]);
        echo json_encode($stmt->fetch()); exit;
    }

    // TOGGLE active
    if ($action === 'toggle_active') {
        $id = (int)$_POST['id'];
        if ($id === (int)$_SESSION['admin_id']) { echo json_encode(['ok'=>false,'msg'=>'Tidak dapat menonaktifkan akun Anda sendiri.']); exit; }
        db()->prepare("UPDATE admin_users SET is_active = NOT is_active WHERE id=?")->execute([$id]);
        $r = db()->prepare("SELECT is_active FROM admin_users WHERE id=?"); $r->execute([$id]);
        echo json_encode(['ok'=>true,'status'=>(int)$r->fetch()['is_active']]); exit;
    }

    // DELETE
    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        if ($id === (int)$_SESSION['admin_id']) { echo json_encode(['ok'=>false,'msg'=>'Tidak dapat menghapus akun Anda sendiri.']); exit; }
        db()->prepare("DELETE FROM admin_users WHERE id=?")->execute([$id]);
        echo json_encode(['ok'=>true,'msg'=>'User berhasil dihapus.']); exit;
    }

    // CREATE
    if ($action === 'create') {
        $username  = trim($_POST['username'] ?? '');
        $fullName  = trim($_POST['full_name'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $role      = $_POST['role'] ?? 'editor';
        $password  = $_POST['password'] ?? '';
        $isActive  = isset($_POST['is_active']) ? 1 : 0;

        if (!$username || !$fullName || !$email || !$password) { echo json_encode(['ok'=>false,'msg'=>'Semua field wajib diisi.']); exit; }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { echo json_encode(['ok'=>false,'msg'=>'Format email tidak valid.']); exit; }
        if (strlen($password) < 8) { echo json_encode(['ok'=>false,'msg'=>'Password minimal 8 karakter.']); exit; }

        // Check unique
        $ck = db()->prepare("SELECT id FROM admin_users WHERE username=? OR email=?");
        $ck->execute([$username, $email]);
        if ($ck->fetch()) { echo json_encode(['ok'=>false,'msg'=>'Username atau email sudah digunakan.']); exit; }

        try {
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost'=>12]);
            db()->prepare("INSERT INTO admin_users (username,password,full_name,email,role,is_active) VALUES (?,?,?,?,?,?)")
               ->execute([$username,$hash,$fullName,$email,$role,$isActive]);
            echo json_encode(['ok'=>true,'msg'=>"User '$username' berhasil ditambahkan."]); exit;
        } catch (Exception $e) { echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]); exit; }
    }

    // UPDATE
    if ($action === 'update') {
        $id       = (int)$_POST['id'];
        $username = trim($_POST['username'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $role     = $_POST['role'] ?? 'editor';
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $newPw    = $_POST['new_password'] ?? '';

        if (!$username || !$fullName || !$email) { echo json_encode(['ok'=>false,'msg'=>'Nama, username, dan email wajib diisi.']); exit; }

        // Prevent self-demotion
        if ($id === (int)$_SESSION['admin_id'] && $role !== 'superadmin') {
            echo json_encode(['ok'=>false,'msg'=>'Anda tidak dapat mengubah role akun sendiri.']); exit;
        }

        // Check duplicate
        $ck = db()->prepare("SELECT id FROM admin_users WHERE (username=? OR email=?) AND id!=?");
        $ck->execute([$username,$email,$id]);
        if ($ck->fetch()) { echo json_encode(['ok'=>false,'msg'=>'Username atau email sudah digunakan user lain.']); exit; }

        try {
            if ($newPw) {
                if (strlen($newPw) < 8) { echo json_encode(['ok'=>false,'msg'=>'Password baru minimal 8 karakter.']); exit; }
                $hash = password_hash($newPw, PASSWORD_BCRYPT, ['cost'=>12]);
                db()->prepare("UPDATE admin_users SET username=?,full_name=?,email=?,role=?,is_active=?,password=? WHERE id=?")
                   ->execute([$username,$fullName,$email,$role,$isActive,$hash,$id]);
            } else {
                db()->prepare("UPDATE admin_users SET username=?,full_name=?,email=?,role=?,is_active=? WHERE id=?")
                   ->execute([$username,$fullName,$email,$role,$isActive,$id]);
            }
            echo json_encode(['ok'=>true,'msg'=>'Data user berhasil diperbarui.']); exit;
        } catch (Exception $e) { echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]); exit; }
    }

    echo json_encode(['ok'=>false,'msg'=>'Unknown action']); exit;
}

// ── Fetch users ───────────────────────────────────────────────────────────
try {
    $users = db()->query("SELECT id,username,full_name,email,role,is_active,last_login,created_at FROM admin_users ORDER BY role,created_at")->fetchAll();
} catch (Exception $e) { $users = []; }

$csrf = csrf_token();
$roleColors = [
    'superadmin' => 'text-red-300 bg-red-500/15 border-red-500/30',
    'admin'      => 'text-gold-300 bg-yellow-500/15 border-yellow-500/30',
    'editor'     => 'text-blue-300 bg-blue-500/15 border-blue-500/30',
];

require_once __DIR__ . '/../includes/admin_head.php';
?>

<div class="mb-6 flex items-center justify-between">
  <p class="text-white/40 text-sm"><?= count($users) ?> user terdaftar</p>
  <button onclick="openModal('create')"
          class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm text-black font-medium hover:scale-105 transition-all"
          style="background:linear-gradient(135deg,#d4aa3a,#e8c860)">
    <i data-lucide="user-plus" style="width:15px;height:15px"></i> Tambah User
  </button>
</div>

<!-- Users grid -->
<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
  <?php foreach ($users as $u):
    $isSelf = ((int)$u['id'] === (int)$_SESSION['admin_id']);
  ?>
  <div class="glass rounded-2xl p-5 group hover:bg-white/[.08] transition-all">
    <div class="flex items-start justify-between mb-4">
      <!-- Avatar -->
      <div class="w-11 h-11 rounded-xl flex items-center justify-center text-base font-medium flex-shrink-0"
           style="background:rgba(212,170,58,.12);border:1px solid rgba(212,170,58,.2);color:#d4aa3a">
        <?= strtoupper(substr($u['full_name'],0,1)) ?>
      </div>
      <div class="flex items-center gap-2">
        <?php if ($isSelf): ?>
        <span class="text-[10px] px-2 py-0.5 rounded-full" style="background:rgba(212,170,58,.15);color:#f0d898;border:1px solid rgba(212,170,58,.3)">Anda</span>
        <?php endif; ?>
        <!-- Active toggle -->
        <button onclick="toggleActive(<?= $u['id'] ?>, this)"
                class="text-[10px] px-2.5 py-1 rounded-full border transition-all"
                style="<?= $u['is_active'] ? 'background:rgba(52,211,153,.12);border-color:rgba(52,211,153,.3);color:#6ee7b7' : 'background:rgba(255,255,255,.05);border-color:rgba(255,255,255,.12);color:rgba(255,255,255,.35)' ?>"
                <?= $isSelf ? 'disabled style="opacity:.4;cursor:not-allowed"' : '' ?>>
          <?= $u['is_active'] ? 'Aktif' : 'Nonaktif' ?>
        </button>
      </div>
    </div>

    <h3 class="text-white/90 font-medium text-sm mb-0.5"><?= e($u['full_name']) ?></h3>
    <p class="text-white/40 text-xs mb-1">@<?= e($u['username']) ?></p>
    <p class="text-white/30 text-xs mb-3"><?= e($u['email']) ?></p>

    <div class="flex items-center justify-between">
      <span class="text-[11px] px-2.5 py-1 rounded-full border capitalize <?= $roleColors[$u['role']] ?? 'text-white/40 bg-white/5 border-white/15' ?>">
        <?= e($u['role']) ?>
      </span>
      <div class="flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
        <button onclick="openModal('edit', <?= $u['id'] ?>)"
                class="w-7 h-7 glass rounded-lg flex items-center justify-center hover:bg-blue-500/20 transition-all">
          <i data-lucide="pencil" style="width:12px;height:12px;color:#93c5fd"></i>
        </button>
        <?php if (!$isSelf): ?>
        <button onclick="deleteUser(<?= $u['id'] ?>, '<?= addslashes(e($u['full_name'])) ?>')"
                class="w-7 h-7 glass rounded-lg flex items-center justify-center hover:bg-red-500/20 transition-all">
          <i data-lucide="trash-2" style="width:12px;height:12px;color:#f87171"></i>
        </button>
        <?php endif; ?>
      </div>
    </div>

    <?php if ($u['last_login']): ?>
    <p class="text-white/20 text-[10px] mt-3 flex items-center gap-1">
      <i data-lucide="clock" style="width:10px;height:10px"></i>
      Login terakhir: <?= date('d M Y H:i', strtotime($u['last_login'])) ?>
    </p>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>

  <!-- Add card shortcut -->
  <button onclick="openModal('create')"
          class="glass rounded-2xl p-5 border-dashed text-white/25 hover:text-white/50 hover:bg-white/[.04] transition-all flex flex-col items-center justify-center gap-3 min-h-[180px]"
          style="border:2px dashed rgba(255,255,255,.1)">
    <i data-lucide="plus" style="width:24px;height:24px"></i>
    <span class="text-sm">Tambah User Baru</span>
  </button>
</div>

<!-- ── Modal ──────────────────────────────────────────────────────────── -->
<div id="modal-overlay" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-5"
     style="background:rgba(0,0,0,.75);backdrop-filter:blur(8px)">
  <div id="modal-box" style="background:#111;border:1px solid rgba(255,255,255,.1);border-radius:24px;width:100%;max-width:560px;animation:modalIn .3s cubic-bezier(.16,1,.3,1)">
    <div class="flex items-center justify-between px-7 py-5 border-b border-white/[.08]">
      <h2 id="modal-title" class="font-display text-2xl text-white font-light">Tambah User</h2>
      <button onclick="closeModal()" class="w-9 h-9 glass rounded-xl flex items-center justify-center hover:bg-white/15 transition-all">
        <i data-lucide="x" style="width:15px;height:15px"></i>
      </button>
    </div>

    <form id="user-form" class="px-7 py-6 space-y-4">
      <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
      <input type="hidden" name="action" id="f-action" value="create">
      <input type="hidden" name="id" id="f-id" value="">

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-[10px] tracking-widest uppercase mb-1.5 text-white/35">Nama Lengkap *</label>
          <input type="text" name="full_name" id="f-name" required placeholder="Nama lengkap" class="input-g">
        </div>
        <div>
          <label class="block text-[10px] tracking-widest uppercase mb-1.5 text-white/35">Username *</label>
          <input type="text" name="username" id="f-username" required placeholder="username" class="input-g" autocomplete="off">
        </div>
      </div>

      <div>
        <label class="block text-[10px] tracking-widest uppercase mb-1.5 text-white/35">Email *</label>
        <input type="email" name="email" id="f-email" required placeholder="email@sekolah.sch.id" class="input-g" autocomplete="off">
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-[10px] tracking-widest uppercase mb-1.5 text-white/35">Role</label>
          <select name="role" id="f-role" class="input-g">
            <option value="editor">Editor</option>
            <option value="admin">Admin</option>
            <option value="superadmin">Superadmin</option>
          </select>
        </div>
        <div class="flex items-end pb-1">
          <label class="flex items-center gap-2.5 cursor-pointer select-none">
            <input type="checkbox" name="is_active" id="f-active" checked class="w-4 h-4 accent-yellow-500">
            <span class="text-sm text-white/65">Aktifkan akun</span>
          </label>
        </div>
      </div>

      <!-- Password fields -->
      <div id="pw-section">
        <label class="block text-[10px] tracking-widest uppercase mb-1.5 text-white/35" id="pw-label">Password *</label>
        <div class="relative">
          <input type="password" name="password" id="f-pw" placeholder="Min. 8 karakter" class="input-g pr-10" autocomplete="new-password">
          <button type="button" onclick="togglePw('f-pw','eye-pw')" class="absolute right-3 top-1/2 -translate-y-1/2 text-white/30 hover:text-white/60 transition-colors">
            <i data-lucide="eye" id="eye-pw" style="width:15px;height:15px"></i>
          </button>
        </div>
      </div>

      <div id="new-pw-section" class="hidden">
        <label class="block text-[10px] tracking-widets uppercase mb-1.5 text-white/35">Password Baru <span class="normal-case text-white/25">(kosongkan jika tidak diubah)</span></label>
        <div class="relative">
          <input type="password" name="new_password" id="f-npw" placeholder="Kosongkan jika tidak diubah" class="input-g pr-10" autocomplete="new-password">
          <button type="button" onclick="togglePw('f-npw','eye-npw')" class="absolute right-3 top-1/2 -translate-y-1/2 text-white/30 hover:text-white/60 transition-colors">
            <i data-lucide="eye" id="eye-npw" style="width:15px;height:15px"></i>
          </button>
        </div>
      </div>

      <div class="flex items-center justify-end gap-3 pt-3 border-t border-white/[.07]">
        <button type="button" onclick="closeModal()"
                class="px-5 py-2.5 glass rounded-xl text-sm text-white/55 hover:text-white hover:bg-white/10 transition-all">
          Batal
        </button>
        <button type="submit" id="submit-btn"
                class="px-7 py-2.5 rounded-xl text-sm font-medium text-black flex items-center gap-2 transition-all hover:scale-105"
                style="background:linear-gradient(135deg,#d4aa3a,#e8c860)">
          <i data-lucide="save" style="width:14px;height:14px"></i>
          <span id="submit-label">Simpan</span>
        </button>
      </div>
    </form>
  </div>
</div>

<script>
lucide.createIcons();
function showToast(i,t){Swal.fire({toast:true,position:'top-end',icon:i,title:t,showConfirmButton:false,timer:3000,timerProgressBar:true,background:'rgba(15,15,15,.97)',color:'#fff',customClass:{popup:'rounded-2xl'}})}

function openModal(mode, id = null) {
  document.getElementById('modal-overlay').classList.remove('hidden');
  document.body.style.overflow = 'hidden';
  if (mode === 'create') {
    document.getElementById('modal-title').textContent  = 'Tambah User Baru';
    document.getElementById('submit-label').textContent = 'Simpan';
    document.getElementById('f-action').value           = 'create';
    document.getElementById('f-id').value              = '';
    document.getElementById('user-form').reset();
    document.getElementById('pw-section').classList.remove('hidden');
    document.getElementById('new-pw-section').classList.add('hidden');
    document.getElementById('f-pw').required = true;
    document.getElementById('f-active').checked = true;
  } else {
    fetchUser(id);
  }
}

async function fetchUser(id) {
  const res  = await fetch('users.php', { method:'POST', body: new URLSearchParams({action:'get',id,csrf_token:'<?= $csrf ?>'}) });
  const data = await res.json();
  if (!data || !data.id) { showToast('error','Gagal memuat data user.'); return; }

  document.getElementById('modal-title').textContent  = 'Edit User';
  document.getElementById('submit-label').textContent = 'Perbarui';
  document.getElementById('f-action').value           = 'update';
  document.getElementById('f-id').value              = data.id;
  document.getElementById('f-name').value            = data.full_name   || '';
  document.getElementById('f-username').value        = data.username    || '';
  document.getElementById('f-email').value           = data.email       || '';
  document.getElementById('f-role').value            = data.role        || 'editor';
  document.getElementById('f-active').checked        = !!parseInt(data.is_active);
  document.getElementById('pw-section').classList.add('hidden');
  document.getElementById('new-pw-section').classList.remove('hidden');
  document.getElementById('f-pw').required = false;

  document.getElementById('modal-overlay').classList.remove('hidden');
  document.body.style.overflow = 'hidden';
  lucide.createIcons();
}

function closeModal() {
  document.getElementById('modal-overlay').classList.add('hidden');
  document.body.style.overflow = '';
}
document.getElementById('modal-overlay').addEventListener('click', e => {
  if (e.target === document.getElementById('modal-overlay')) closeModal();
});

function togglePw(inputId, iconId) {
  const inp = document.getElementById(inputId);
  const ico = document.getElementById(iconId);
  if (inp.type === 'password') { inp.type = 'text'; ico.setAttribute('data-lucide','eye-off'); }
  else { inp.type = 'password'; ico.setAttribute('data-lucide','eye'); }
  lucide.createIcons();
}

document.getElementById('user-form').addEventListener('submit', async e => {
  e.preventDefault();
  const btn = document.getElementById('submit-btn');
  btn.disabled = true; btn.querySelector('span').textContent = 'Menyimpan…';
  const res  = await fetch('users.php', { method:'POST', body: new FormData(e.target) });
  const data = await res.json();
  if (data.ok) {
    closeModal();
    showToast('success', data.msg);
    setTimeout(() => location.reload(), 1000);
  } else {
    showToast('error', data.msg || 'Terjadi kesalahan.');
  }
  btn.disabled = false;
  btn.querySelector('span').textContent = document.getElementById('f-action').value === 'create' ? 'Simpan' : 'Perbarui';
});

async function toggleActive(id, btn) {
  const res  = await fetch('users.php', { method:'POST', body: new URLSearchParams({action:'toggle_active',id,csrf_token:'<?= $csrf ?>'}) });
  const data = await res.json();
  if (data.ok) {
    const on = data.status === 1;
    btn.textContent  = on ? 'Aktif' : 'Nonaktif';
    btn.style.cssText = on
      ? 'background:rgba(52,211,153,.12);border:1px solid rgba(52,211,153,.3);color:#6ee7b7;border-radius:9999px;font-size:.625rem;padding:4px 10px;transition:all .2s'
      : 'background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.12);color:rgba(255,255,255,.35);border-radius:9999px;font-size:.625rem;padding:4px 10px;transition:all .2s';
    showToast('success', 'Status berhasil diubah.');
  } else {
    showToast('error', data.msg);
  }
}

function deleteUser(id, name) {
  Swal.fire({
    title: 'Hapus User?',
    html: `<span style="color:rgba(255,255,255,.6)">"${name}"</span><br><small style="color:rgba(255,255,255,.3)">Tindakan ini tidak dapat dibatalkan.</small>`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Ya, Hapus',
    cancelButtonText:  'Batal',
    confirmButtonColor: '#ef4444',
    background: '#111', color: '#fff',
    customClass: { popup:'rounded-2xl', confirmButton:'rounded-xl px-5 py-2', cancelButton:'rounded-xl px-5 py-2' }
  }).then(async r => {
    if (!r.isConfirmed) return;
    const res  = await fetch('users.php', { method:'POST', body: new URLSearchParams({action:'delete',id,csrf_token:'<?= $csrf ?>'}) });
    const data = await res.json();
    if (data.ok) { showToast('success', data.msg); setTimeout(() => location.reload(), 900); }
    else showToast('error', data.msg);
  });
}
</script>
</div></main></div></body></html>
