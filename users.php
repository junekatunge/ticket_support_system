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

// Calculate user statistics
$totalUsers = count($users);
$adminUsers = 0;
$memberUsers = 0;
$recentUsers = 0; // Users created in last 30 days
$activeUsers = 0; // Users with activity (simplified)

foreach($users as $user) {
    $role = strtolower($user->role ?? 'member');
    if(in_array($role, ['admin', 'administrator'])) {
        $adminUsers++;
    } else {
        $memberUsers++;
    }
    
    // Check if user was created in last 30 days
    if(!empty($user->created_at)) {
        $createdDate = new DateTime($user->created_at);
        $thirtyDaysAgo = new DateTime('-30 days');
        if($createdDate > $thirtyDaysAgo) {
            $recentUsers++;
        }
    }
    
    // For now, consider all users as active (you can add activity logic later)
    $activeUsers++;
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
    :root { --bg-soft: #f8fafc; }
    html, body { height: 100%; }
    body { background: var(--bg-soft); }
    .app-shell { display: flex; height: 100vh; }
    .content { 
      padding: calc(60px + 1rem) 1.25rem 2rem;
      height: 100vh;
      overflow-y: auto;
      flex: 1;
    }
    .page-header { background: linear-gradient(135deg, #28a745, #20c997); color:#fff; border-radius:1.2rem; padding:1.5rem; margin-bottom:1.25rem; box-shadow:0 10px 20px rgba(40,167,69,.15); }
    .card { border:0; border-radius:1rem; box-shadow:0 6px 18px rgba(0,0,0,.06); }
    .table > :not(caption) > * > * { vertical-align: middle; }
    .dataTables_filter input { border-radius:.8rem !important; }
    .badge-role { text-transform: capitalize; }
    .badge-admin { background:#fff3cd; color:#856404; border:1px solid #ffeaa7; }
    .badge-member { background:#d1ecf1; color:#0c5460; border:1px solid #b8daff; }
    .app-footer { color:#6c757d; }
    .action-buttons { display: flex; gap: 0.5rem; }
    .btn-action { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 6px; }
    
    /* User Statistics Cards */
    .stat-card {
      border-radius: 12px;
      border: none;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
      transition: transform 0.2s, box-shadow 0.2s;
      overflow: hidden;
    }
    .stat-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(0,0,0,0.12);
    }
    .stat-icon {
      width: 50px;
      height: 50px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
    }
    .user-avatar {
      width: 40px;
      height: 40px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
      font-size: 14px;
      color: white;
    }
    .search-controls {
      background: #f8f9fa;
      border-radius: 10px;
      padding: 1rem;
      margin-bottom: 1rem;
    }
    .filter-btn {
      border-radius: 6px;
      padding: 6px 16px;
      font-weight: 500;
      transition: all 0.2s;
      border: 1px solid #dee2e6;
    }
    .filter-btn:hover {
      transform: translateY(-1px);
    }
    .filter-btn.active {
      background: #28a745;
      border-color: #28a745;
      color: white;
    }
    
    pre.debug { white-space:pre-wrap; background:#fff; border:1px dashed #ced4da; border-radius:.5rem; padding:.75rem; }
    
    /* Footer/Copyright Styling */
    .footer-section {
      background: #f8f9fa;
      border-radius: 8px;
      padding: 1rem;
      margin-top: 2rem;
      border: 1px solid #e9ecef;
    }
    
    .footer-content {
      color: #6c757d;
      font-size: 0.875rem;
    }
  </style>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="app-shell">
  <?php include 'sidebar.php'; ?>

  <!-- MAIN -->
  <section class="content content-with-navbar">
    <div class="page-header">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
          <h2 class="mb-1"><i class="fas fa-user-group me-2"></i>User Management</h2>
          <p class="mb-0 opacity-75">Manage system users and their permissions</p>
        </div>
        <div class="d-flex gap-2">
          <button class="btn btn-light fw-semibold shadow-sm">
            <i class="fas fa-download me-2"></i>Export Users
          </button>
          <a class="btn btn-warning fw-semibold shadow-sm" href="./newuser.php">
            <i class="fa fa-plus me-2"></i>Create New User
          </a>
        </div>
      </div>
    </div>

    <!-- User Statistics Cards -->
    <div class="row mb-4">
      <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stat-card">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                <i class="fas fa-users"></i>
              </div>
              <div>
                <div class="text-muted small">Total Users</div>
                <div class="h4 mb-0"><?php echo $totalUsers; ?></div>
                <small class="text-success">
                  <i class="fas fa-arrow-up"></i> <?php echo $recentUsers; ?> recent
                </small>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stat-card">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                <i class="fas fa-user-shield"></i>
              </div>
              <div>
                <div class="text-muted small">Administrators</div>
                <div class="h4 mb-0"><?php echo $adminUsers; ?></div>
                <small class="text-muted">
                  <?php echo $totalUsers > 0 ? round(($adminUsers / $totalUsers) * 100) : 0; ?>% of total
                </small>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stat-card">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                <i class="fas fa-user-friends"></i>
              </div>
              <div>
                <div class="text-muted small">Team Members</div>
                <div class="h4 mb-0"><?php echo $memberUsers; ?></div>
                <small class="text-muted">
                  Support staff
                </small>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stat-card">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                <i class="fas fa-user-check"></i>
              </div>
              <div>
                <div class="text-muted small">Active Users</div>
                <div class="h4 mb-0"><?php echo $activeUsers; ?></div>
                <small class="text-success">
                  <i class="fas fa-circle" style="font-size: 8px;"></i> Online
                </small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Search and Filter Controls -->
    <div class="search-controls">
      <div class="row align-items-center">
        <div class="col-md-4">
          <div class="input-group">
            <span class="input-group-text bg-white border-end-0">
              <i class="fas fa-search text-muted"></i>
            </span>
            <input type="text" class="form-control border-start-0" id="searchUsers" placeholder="Search users...">
          </div>
        </div>
        <div class="col-md-8 text-end mt-2 mt-md-0">
          <div class="btn-group" role="group">
            <button type="button" class="btn filter-btn active" data-filter="all">
              <i class="fas fa-users"></i> All Users
            </button>
            <button type="button" class="btn filter-btn" data-filter="admin">
              <i class="fas fa-user-shield"></i> Admins
            </button>
            <button type="button" class="btn filter-btn" data-filter="member">
              <i class="fas fa-user"></i> Members
            </button>
            <button type="button" class="btn filter-btn" data-filter="recent">
              <i class="fas fa-clock"></i> Recent
            </button>
          </div>
        </div>
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
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($fatalMessage) && !empty($users)): ?>
                <?php foreach ($users as $u): ?>
                  <?php
                    // Generate avatar color based on name
                    $nameHash = md5($u->name ?? 'User');
                    $colors = ['#e91e63', '#9c27b0', '#673ab7', '#3f51b5', '#2196f3', '#00bcd4', '#009688', '#4caf50', '#ff9800', '#ff5722'];
                    $avatarColor = $colors[hexdec(substr($nameHash, 0, 1)) % count($colors)];
                    $initials = strtoupper(substr($u->name ?? 'U', 0, 1) . (strpos($u->name ?? '', ' ') ? substr($u->name, strpos($u->name, ' ') + 1, 1) : ''));
                    $role = strtolower($u->role ?? 'member');
                    $cls  = in_array($role, ['admin', 'administrator']) ? 'badge-admin' : 'badge-member';
                    $isRecent = false;
                    if(!empty($u->created_at)) {
                        $createdDate = new DateTime($u->created_at);
                        $thirtyDaysAgo = new DateTime('-30 days');
                        $isRecent = $createdDate > $thirtyDaysAgo;
                    }
                  ?>
                  <tr data-role="<?= $role ?>" data-recent="<?= $isRecent ? 'true' : 'false' ?>">
                    <td>
                      <div class="d-flex align-items-center">
                        <div class="user-avatar me-3" style="background-color: <?= $avatarColor ?>;">
                          <?= $initials ?>
                        </div>
                        <div>
                          <div class="fw-medium"><?= e($u->name) ?></div>
                          <?php if ($isRecent): ?>
                            <small class="text-success"><i class="fas fa-star" style="font-size: 10px;"></i> New user</small>
                          <?php endif; ?>
                        </div>
                      </div>
                    </td>
                    <td>
                      <span class="badge rounded-pill badge-role <?= $cls ?>">
                        <?php if(in_array($role, ['admin', 'administrator'])): ?>
                          <i class="fas fa-shield-alt me-1"></i>
                        <?php else: ?>
                          <i class="fas fa-user me-1"></i>
                        <?php endif; ?>
                        <?= e($u->role ?: 'Member') ?>
                      </span>
                    </td>
                    <td>
                      <?php if (!empty($u->email)): ?>
                        <div class="d-flex align-items-center">
                          <i class="fas fa-envelope text-muted me-2"></i>
                          <a class="text-decoration-none" href="mailto:<?= e($u->email) ?>"><?= e($u->email) ?></a>
                        </div>
                      <?php else: ?>
                        <span class="text-muted">—</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if (!empty($u->room)): ?>
                        <div class="d-flex align-items-center">
                          <i class="fas fa-map-marker-alt text-muted me-2"></i>
                          <?= e($u->room) ?>
                        </div>
                      <?php else: ?>
                        <span class="text-muted">—</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <div class="d-flex align-items-center">
                        <i class="fas fa-calendar text-muted me-2"></i>
                        <div>
                          <div><?= pretty_date($u->created_at ?? null) ?></div>
                          <?php if ($isRecent): ?>
                            <small class="text-success">Recently added</small>
                          <?php endif; ?>
                        </div>
                      </div>
                    </td>
                    <td class="text-center">
                      <div class="action-buttons justify-content-center">
                        <a href="edituser.php?id=<?= e($u->id ?? '') ?>" class="btn btn-sm btn-action btn-outline-primary" title="Edit user">
                          <i class="fas fa-edit fa-sm"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-action btn-outline-info" title="View profile">
                          <i class="fas fa-eye fa-sm"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-action btn-outline-danger" title="Delete user" data-bs-toggle="modal" data-bs-target="#deleteModal" data-userid="<?= e($u->id ?? '') ?>" data-username="<?= e($u->name) ?>">
                          <i class="fas fa-trash-alt fa-sm"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="deleteModalLabel">Confirm User Deletion</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Are you sure you want to delete user: <strong id="deleteUserName"></strong>?</p>
            <p class="text-danger">This action cannot be undone.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <form id="deleteUserForm" method="post" action="deleteuser.php">
              <input type="hidden" name="user_id" id="deleteUserId">
              <button type="submit" class="btn btn-danger">Delete User</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer Section -->
    <div class="footer-section">
      <div class="footer-content text-center">
        © <?php echo date('Y'); ?> ICT Helpdesk. All rights reserved.
      </div>
    </div>
  </section>
</div>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/v/bs5/dt-2.0.7/r-3.0.3/datatables.min.js"></script>
<script>
  $(function () {
    const table = $('#usersTable').DataTable({
      responsive: true,
      pageLength: 10,
      lengthChange: false,
      order: [[0, 'asc']],
      columnDefs: [
        { orderable: false, targets: 5 } // Disable sorting for Actions column
      ],
      language: {
        searchPlaceholder: 'Search users…',
        search: '',
        emptyTable: 'No users found.'
      }
    });

    // Custom search functionality
    $('#searchUsers').on('keyup', function() {
      table.search(this.value).draw();
    });

    // Filter functionality
    $('.filter-btn').on('click', function() {
      $('.filter-btn').removeClass('active');
      $(this).addClass('active');
      
      const filterValue = $(this).data('filter');
      
      if (filterValue === 'all') {
        table.column(1).search('').draw(); // Clear role filter
        $('tbody tr').show();
      } else if (filterValue === 'admin') {
        table.column(1).search('admin', true, false).draw();
      } else if (filterValue === 'member') {
        table.column(1).search('member', true, false).draw();
      } else if (filterValue === 'recent') {
        // Filter recent users using data attributes
        table.search('').draw(); // Clear search first
        $('tbody tr').hide();
        $('tbody tr[data-recent=\"true\"]').show();
      }
    });

    // Animation on page load
    $('.stat-card').each(function(index) {
      $(this).css('opacity', '0').delay(index * 100).animate({
        opacity: 1
      }, 500);
    });

    // Delete modal handler
    $('#deleteModal').on('show.bs.modal', function (event) {
      const button = $(event.relatedTarget);
      const userId = button.data('userid');
      const userName = button.data('username');
      
      const modal = $(this);
      modal.find('#deleteUserName').text(userName);
      modal.find('#deleteUserId').val(userId);
    });
      
    // Load create ticket modal functionality
    $.getScript('./includes/create-ticket-modal.js');
});
</script>
<!-- Include Create Ticket Modal -->
<?php include './includes/create-ticket-modal.php'; ?>

</body>
</html>