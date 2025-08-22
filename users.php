<?php
// ===== Debug switches =====
define('APP_DEBUG', true); // set to false in production

if (APP_DEBUG) {
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
}

// ---- Helpers ----
function e($v) { return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
function pretty_date($v) {
  if (empty($v)) return '—';
  try { $dt = new DateTime($v); return $dt->format('d M Y, H:i'); }
  catch (Exception $e) { return '—'; }
}

// ---- Load model safely ----
$users = [];
$srcPath = __DIR__ . '/src/user.php';

if (!file_exists($srcPath)) {
  $fatalMessage = "Missing file: ./src/user.php";
} else {
  require_once $srcPath;
  if (!class_exists('User')) {
    $fatalMessage = "Class User not found in ./src/user.php";
  } elseif (!is_callable(['User', 'findAll'])) {
    $fatalMessage = "Static method User::findAll() not found.";
  } else {
    try {
      $users = User::findAll(); // your data access
    } catch (Throwable $t) {
      $fatalMessage = "Error calling User::findAll(): " . ($t->getMessage());
    }
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Helpdesk · Users</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- DataTables (Bootstrap 5 integration) -->
  <link href="https://cdn.datatables.net/v/bs5/dt-2.0.7/r-3.0.3/datatables.min.css" rel="stylesheet"/>
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>

  <style>
    :root { --sidebar-w: 260px; --bg-soft: #f3f5f8; }
    html, body { height: 100%; }
    body { background: var(--bg-soft); }
    .app-shell { display: grid; grid-template-columns: var(--sidebar-w) 1fr; min-height: 100vh; }
    .sidebar { background: #1f2937; color:#cbd5e1; padding: 1.25rem 1rem; }
    .brand { display:flex; align-items:center; gap:.6rem; color:#e2e8f0; text-decoration:none; font-weight:700; margin-bottom:1rem; }
    .brand .logo { width:28px; height:28px; display:grid; place-items:center; background:#111827; border-radius:.75rem; color:#60a5fa; }
    .nav-section { margin-top:.75rem; }
    .side-link { display:flex; align-items:center; gap:.7rem; padding:.6rem .75rem; border-radius:.6rem; color:#cbd5e1; text-decoration:none; transition:background .12s, color .12s; font-weight:500; }
    .side-link:hover { background:#111827; color:#fff; }
    .side-link.active { background:#0b1220; color:#fff; }
    .content { padding: 1rem 1.25rem 2rem; }
    .topbar { display:flex; justify-content:flex-end; gap:1rem; padding:.5rem 0 1rem; }
    .page-header { background: linear-gradient(135deg, #0d6efd, #6f42c1); color:#fff; border-radius:1.2rem; padding:1.5rem; margin-bottom:1.25rem; box-shadow:0 10px 20px rgba(13,110,253,.12); }
    .card { border:0; border-radius:1rem; box-shadow:0 6px 18px rgba(0,0,0,.06); }
    .table > :not(caption) > * > * { vertical-align: middle; }
    .dataTables_filter input { border-radius:.8rem !important; }
    .badge-role { text-transform: capitalize; }
    .badge-admin { background:#ffe3e6; color:#c1121f; border:1px solid #ffc2c8; }
    .badge-member { background:#e9ecef; color:#495057; border:1px solid #dee2e6; }
    .app-footer { color:#6c757d; }
    @media (max-width: 992px) { :root { --sidebar-w: 86px; } .brand span { display:none; } .side-link span { display:none; } }
    pre.debug { white-space:pre-wrap; background:#fff; border:1px dashed #ced4da; border-radius:.5rem; padding:.75rem; }
  </style>
</head>
<body>

<div class="app-shell">
  <!-- SIDEBAR -->
  <aside class="sidebar">
    <a href="#" class="brand"><span class="logo"><i class="fa-solid fa-life-ring"></i></span><span>Helpdesk</span></a>
    <nav class="nav-section">
      <a class="side-link" href="./dashboard.php"><i class="fa-solid fa-gauge"></i><span>Dashboard</span></a>
      <a class="side-link" href="./open.php"><i class="fa-regular fa-folder-open"></i><span>Open</span></a>
      <a class="side-link" href="./tickets-solved.php"><i class="fa-regular fa-circle-check"></i><span>Solved</span></a>
      <a class="side-link" href="./tickets-closed.php"><i class="fa-solid fa-xmark-circle"></i><span>Closed</span></a>
      <a class="side-link" href="./tickets-pending.php"><i class="fa-regular fa-clock"></i><span>Pending</span></a>
      <a class="side-link" href="./tickets-unassigned.php"><i class="fa-regular fa-circle"></i><span>Unassigned</span></a>
      <hr class="border-secondary my-3">
      <a class="side-link" href="./mytickets.php"><i class="fa-regular fa-ticket"></i><span>My tickets</span></a>
      <a class="side-link" href="./team.php"><i class="fa-solid fa-users"></i><span>Teams</span></a>
      <a class="side-link active" href="./users.php"><i class="fa-solid fa-user-group"></i><span>Users</span></a>
      <div class="mt-4 p-2 rounded-3" style="background:#0b1220;">
        <div class="d-flex align-items-center gap-2">
          <i class="fa-solid fa-people-group text-primary"></i>
          <strong class="text-primary">Admin</strong>
          <a class="btn btn-outline-light btn-sm ms-auto" href="./index.php"><i class="fa-solid fa-right-from-bracket me-1"></i>Logout</a>
        </div>
      </div>
    </nav>
  </aside>

  <!-- MAIN -->
  <section class="content">
    <div class="topbar">
      <div class="d-flex align-items-center gap-2 text-secondary">
        <i class="fa-regular fa-circle-user fa-lg"></i><span>John Doe</span>
      </div>
    </div>

    <div class="page-header">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
          <h2 class="mb-1">Users</h2>
          <small>Users / Overview</small>
        </div>
        <a class="btn btn-light fw-semibold shadow-sm" href="./newuser.php"><i class="fa fa-plus me-2"></i>Create New User</a>
      </div>
    </div>

    <?php if (isset($fatalMessage)): ?>
      <div class="alert alert-danger rounded-3">
        <strong>Cannot load users.</strong><br><?= e($fatalMessage) ?>
      </div>
      <?php if (APP_DEBUG): ?>
        <pre class="debug small">
Tips:
• Check path: <?= e($srcPath) . "\n" ?>
• Confirm class "User" and static method "findAll()" exist.
• If you recently renamed "room" → "phone", update the column in the table output.
        </pre>
      <?php endif; ?>
    <?php endif; ?>

    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table id="usersTable" class="table table-striped table-hover align-middle w-100">
            <thead class="small text-uppercase text-secondary">
              <tr>
                <th>Name</th>
                <th>Role</th>
                <th>Email</th>
                <th>Room</th>
                <th>Created</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($fatalMessage) && !empty($users)): ?>
                <?php foreach ($users as $u): ?>
                  <tr>
                    <td><?= e($u->name) ?></td>
                    <td>
                      <?php
                        $role = strtolower($u->role ?? 'member');
                        $cls  = in_array($role, ['admin', 'administrator']) ? 'badge-admin' : 'badge-member';
                      ?>
                      <span class="badge rounded-pill badge-role <?= $cls ?>"><?= e($u->role ?: 'Member') ?></span>
                    </td>
                    <td>
                      <?php if (!empty($u->email)): ?>
                        <a class="text-decoration-none" href="mailto:<?= e($u->email) ?>"><?= e($u->email) ?></a>
                      <?php else: ?>
                        —
                      <?php endif; ?>
                    </td>
                    <td><?= e($u->room ?? '—') ?: '—' ?></td>
                    <td><?= pretty_date($u->created_at ?? null) ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
              <!-- NOTE: No colspan row here. If there are no users, we leave tbody empty
                   and DataTables will show 'emptyTable' message configured below. -->
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <p class="text-center mt-4 app-footer">Copyright © The National Treasury</p>
  </section>
</div>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/v/bs5/dt-2.0.7/r-3.0.3/datatables.min.js"></script>
<script>
  $(function () {
    $('#usersTable').DataTable({
      responsive: true,
      pageLength: 10,
      lengthChange: false,
      order: [[0, 'asc']],
      language: {
        searchPlaceholder: 'Search users…',
        search: '',
        emptyTable: 'No users found.'
      }
    });
  });
</script>
</body>
</html>
