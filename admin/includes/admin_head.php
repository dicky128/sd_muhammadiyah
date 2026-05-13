<?php
// admin/includes/admin_head.php
// Set before including: $pageTitle (string), $activeSidebar (string)
$pageTitle = $pageTitle ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?> — Admin CMS SD Muhammadiyah 1</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config={theme:{extend:{fontFamily:{display:['"Cormorant Garamond"','serif'],body:['"DM Sans"','sans-serif']}}}}</script>
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
  <style>
    *{font-family:'DM Sans',sans-serif}h1,h2,.font-display{font-family:'Cormorant Garamond',serif}
    body{background:#0a0a0a;color:#fff}
    .glass{background:rgba(255,255,255,.06);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,.1)}
    .input-g{background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);color:#fff;border-radius:10px;padding:10px 14px;font-size:.85rem;width:100%;transition:all .25s}
    .input-g:focus{outline:none;background:rgba(255,255,255,.1);border-color:rgba(212,170,58,.6);box-shadow:0 0 0 3px rgba(212,170,58,.1)}
    .input-g::placeholder{color:rgba(255,255,255,.3)}.input-g option{background:#1a1a1a}
    #modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:100;backdrop-filter:blur(8px)}
    #modal-overlay.open{display:flex;align-items:flex-start;justify-content:center;padding:40px 16px;overflow-y:auto}
    .modal-box{background:#111;border:1px solid rgba(255,255,255,.1);border-radius:24px;width:100%;max-width:680px;animation:modalIn .3s cubic-bezier(.16,1,.3,1)}
    @keyframes modalIn{from{opacity:0;transform:translateY(-16px) scale(.97)}to{opacity:1;transform:none}}
    ::-webkit-scrollbar{width:4px}::-webkit-scrollbar-track{background:#111}::-webkit-scrollbar-thumb{background:#333;border-radius:4px}
    .badge-count{font-size:.65rem;padding:2px 7px;border-radius:99px;background:rgba(239,68,68,.2);color:#fca5a5}
  </style>
  <script>
    function showToast(icon,title){Swal.fire({toast:true,position:'top-end',icon,title,showConfirmButton:false,timer:3000,timerProgressBar:true,background:'rgba(15,15,15,.97)',color:'#fff',customClass:{popup:'rounded-2xl'}})}
  </script>
</head>
<body>
<div class="flex h-screen overflow-hidden">
<?php require_once __DIR__ . '/sidebar.php'; ?>
<main class="flex-1 flex flex-col overflow-hidden">
  <!-- Topbar -->
  <div class="px-8 py-4 border-b border-white/[.07] flex items-center justify-between flex-shrink-0" style="background:rgba(10,10,10,.9);backdrop-filter:blur(16px)">
    <div>
      <h1 style="font-family:'Cormorant Garamond',serif;font-size:1.5rem;font-weight:300"><?= e($pageTitle) ?></h1>
      <?php if (!empty($pageSubtitle)): ?><p style="font-size:.75rem;color:rgba(255,255,255,.35)"><?= e($pageSubtitle) ?></p><?php endif; ?>
    </div>
    <div class="flex items-center gap-3">
      <?php if (!empty($topbarAction)): echo $topbarAction; endif; ?>
      <a href="<?= APP_URL ?>/index.php" target="_blank" class="flex items-center gap-2 px-4 py-2 glass rounded-xl text-xs text-white/50 hover:text-white hover:bg-white/10 transition-all">
        <i data-lucide="external-link" style="width:14px;height:14px"></i> Lihat Situs
      </a>
    </div>
  </div>
  <div class="flex-1 overflow-y-auto p-8">
