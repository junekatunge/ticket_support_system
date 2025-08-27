<?php
session_start();
if (!isset($_SESSION['logged-in']) || $_SESSION['logged-in'] == false) {
    header('Location: ./index.php');
    exit();
}
$user = $_SESSION['user'];
require_once './src/ticket.php';
require_once './src/requester.php';
require_once './src/team.php';
require_once './src/user.php';

$tickets = Ticket::findByMember($user->id);

// Calculate personal performance metrics
$totalMyTickets = count($tickets);
$activeTickets = count(array_filter($tickets, function($t) { return in_array($t->status, ['open', 'pending']); }));
$solvedTickets = count(array_filter($tickets, function($t) { return $t->status == 'solved'; }));
$todayWork = count(array_filter($tickets, function($t) { 
    return isset($t->updated_at) && date('Y-m-d', strtotime($t->updated_at)) == date('Y-m-d'); 
}));

// Calculate response and resolution times
$avgResponseTime = 0;
$avgResolutionTime = 0;
$responseTimes = [];
$resolutionTimes = [];

foreach($tickets as $t) {
    // Response time (first update after creation)
    if(isset($t->updated_at) && $t->updated_at != $t->created_at) {
        $created = strtotime($t->created_at);
        $responded = strtotime($t->updated_at);
        $responseTimes[] = ($responded - $created) / 3600; // in hours
    }
    
    // Resolution time for solved tickets
    if($t->status == 'solved' && isset($t->solved_at)) {
        $created = strtotime($t->created_at);
        $solved = strtotime($t->solved_at);
        $resolutionTimes[] = ($solved - $created) / 3600; // in hours
    }
}

$avgResponseTime = !empty($responseTimes) ? round(array_sum($responseTimes) / count($responseTimes), 1) : 0;
$avgResolutionTime = !empty($resolutionTimes) ? round(array_sum($resolutionTimes) / count($resolutionTimes), 1) : 0;

// Calculate productivity score (simple scoring system)
$productivityScore = min(100, ($solvedTickets * 10) + (($totalMyTickets > 0) ? (($solvedTickets / $totalMyTickets) * 50) : 0));

?>

<style>
  .stat-card {
    border-radius: 12px;
    border: none;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: transform 0.2s, box-shadow 0.2s;
  }
  .stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
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
  .ticket-table {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
  }
  .ticket-table thead {
    background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
    color: white;
  }
  .ticket-table th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
    padding: 1rem 0.75rem;
  }
  .ticket-table tbody tr {
    transition: all 0.2s;
  }
  .ticket-table tbody tr:hover {
    background-color: #ecfdf5;
    transform: scale(1.01);
  }
  .status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
  }
  .status-open {
    background-color: #dcfce7;
    color: #166534;
  }
  .status-pending {
    background-color: #fef3c7;
    color: #92400e;
  }
  .status-solved {
    background-color: #dbeafe;
    color: #1e40af;
  }
  .priority-high {
    background-color: #fee2e2;
    color: #991b1b;
  }
  .priority-medium {
    background-color: #fef3c7;
    color: #92400e;
  }
  .priority-low {
    background-color: #dbeafe;
    color: #1e40af;
  }
  .search-box {
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    padding: 8px 16px;
  }
  .search-box:focus {
    outline: none;
    border-color: #06b6d4;
    box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.1);
  }
  .filter-btn {
    border-radius: 6px;
    padding: 6px 16px;
    font-weight: 500;
    transition: all 0.2s;
  }
  .filter-btn:hover {
    transform: translateY(-2px);
  }
  .dropdown-menu {
    border: none;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    border-radius: 8px;
  }
  .dropdown-item:hover {
    background-color: #f3f4f6;
  }
  
  /* Pagination Styling */
  .dataTables_wrapper .dataTables_paginate .paginate_button {
    border: none;
    border-radius: 8px;
    padding: 8px 14px;
    margin: 0 3px;
    background: #fff;
    color: #6b7280;
    font-weight: 500;
    transition: all 0.2s;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  }
  
  .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
    color: white !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(6, 182, 212, 0.3);
  }
  
  .dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
    color: white !important;
    box-shadow: 0 4px 8px rgba(6, 182, 212, 0.3);
  }
  
  .pagination-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 1.5rem;
    padding: 1rem 2rem;
    background: #f9fafb;
    border-radius: 12px;
  }
  
  .pagination-wrapper .dataTables_length label {
    display: flex;
    align-items: center;
    margin: 0;
    font-weight: 500;
    color: #6b7280;
  }
  
  .pagination-wrapper .dataTables_info {
    margin: 0 1rem;
    white-space: nowrap;
    color: #6b7280;
    font-weight: 500;
  }
  
  /* Personal indicator */
  .personal-indicator {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    margin-right: 6px;
    background-color: #06b6d4;
  }
  
  /* Productivity score */
  .productivity-circle {
    position: relative;
    width: 80px;
    height: 80px;
  }
  
  .productivity-circle svg {
    transform: rotate(-90deg);
  }
  
  .productivity-circle .score-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-weight: bold;
    font-size: 0.875rem;
    color: #06b6d4;
  }
  
  /* Quick actions */
  .quick-actions {
    background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
    color: white;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
    box-shadow: 0 4px 12px rgba(6, 182, 212, 0.2);
  }
  
  /* Performance chart */
  .performance-card {
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
  }
  
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
  
  @media (max-width: 768px) {
    .stat-card {
      margin-bottom: 1rem;
    }
    .pagination-wrapper {
      flex-direction: column;
      gap: 1rem;
    }
    .quick-actions {
      text-align: center;
    }
  }
</style>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>My Tickets - Helpdesk</title>
  
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <!-- DataTables -->
  <link href="https://cdn.datatables.net/v/bs5/dt-2.0.7/r-3.0.3/datatables.min.css" rel="stylesheet"/>

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
  </style>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="app-shell">
  <?php include 'sidebar.php'; ?>
  
  <section class="content content-with-navbar">
  <div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h1 class="h3 mb-1 text-gray-800">My Tickets</h1>
        <p class="mb-0 text-muted">Your personal workload and performance dashboard</p>
      </div>
      <div class="d-flex gap-2">
        <button class="btn btn-info">
          <i class="fas fa-chart-line me-2"></i>My Analytics
        </button>
        <?php include './includes/create-ticket-button.php'; ?>
      </div>
    </div>

    <!-- Quick Actions Panel -->
    <div class="quick-actions">
      <div class="row align-items-center">
        <div class="col-md-8">
          <h6 class="mb-1 fw-bold">👋 Welcome back, <?php echo htmlspecialchars($user->name ?? 'Agent'); ?>!</h6>
          <p class="mb-0 opacity-90">You have <?php echo $activeTickets; ?> active tickets and <?php echo $todayWork; ?> tickets updated today.</p>
        </div>
        <div class="col-md-4 text-end">
          <div class="d-flex gap-2 justify-content-end">
            <button class="btn btn-light btn-sm">
              <i class="fas fa-play me-1"></i>Start Work
            </button>
            <button class="btn btn-outline-light btn-sm">
              <i class="fas fa-pause me-1"></i>Take Break
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
      <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stat-card">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                <i class="fas fa-briefcase"></i>
              </div>
              <div>
                <div class="text-muted small">My Total Tickets</div>
                <div class="h4 mb-0"><?php echo $totalMyTickets; ?></div>
                <small class="text-info">
                  <i class="fas fa-user"></i> Personal workload
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
                <i class="fas fa-tasks"></i>
              </div>
              <div>
                <div class="text-muted small">Active Tickets</div>
                <div class="h4 mb-0"><?php echo $activeTickets; ?></div>
                <small class="text-warning">
                  <i class="fas fa-hourglass-half"></i> In progress
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
                <i class="fas fa-check-circle"></i>
              </div>
              <div>
                <div class="text-muted small">Solved</div>
                <div class="h4 mb-0"><?php echo $solvedTickets; ?></div>
                <small class="text-success">
                  <i class="fas fa-trophy"></i> Completed
                </small>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stat-card">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <div class="text-muted small">Performance</div>
                <div class="h4 mb-0"><?php echo round($productivityScore); ?>%</div>
                <small class="text-primary">
                  <i class="fas fa-chart-line"></i> Score
                </small>
              </div>
              <div class="productivity-circle">
                <svg width="60" height="60">
                  <circle cx="30" cy="30" r="25" fill="none" stroke="#e5e7eb" stroke-width="4"/>
                  <circle cx="30" cy="30" r="25" fill="none" stroke="#06b6d4" stroke-width="4"
                          stroke-dasharray="<?php echo ($productivityScore/100)*157; ?>, 157"/>
                </svg>
                <div class="score-text"><?php echo round($productivityScore); ?>%</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Performance Insights -->
    <div class="performance-card">
      <h6 class="fw-bold mb-3"><i class="fas fa-analytics me-2"></i>Performance Insights</h6>
      <div class="row">
        <div class="col-md-4 mb-2">
          <div class="d-flex justify-content-between align-items-center p-2 bg-white rounded">
            <span class="fw-medium">Avg Response Time</span>
            <span class="badge bg-info"><?php echo $avgResponseTime; ?> hours</span>
          </div>
        </div>
        <div class="col-md-4 mb-2">
          <div class="d-flex justify-content-between align-items-center p-2 bg-white rounded">
            <span class="fw-medium">Avg Resolution Time</span>
            <span class="badge bg-success"><?php echo $avgResolutionTime; ?> hours</span>
          </div>
        </div>
        <div class="col-md-4 mb-2">
          <div class="d-flex justify-content-between align-items-center p-2 bg-white rounded">
            <span class="fw-medium">Today's Updates</span>
            <span class="badge bg-primary"><?php echo $todayWork; ?> tickets</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="card mb-3">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-md-4">
            <div class="input-group">
              <span class="input-group-text bg-white border-end-0">
                <i class="fas fa-search text-muted"></i>
              </span>
              <input type="text" class="form-control border-start-0 search-box" 
                     id="searchInput" placeholder="Search my tickets...">
            </div>
          </div>
          <div class="col-md-8 text-end">
            <div class="btn-group" role="group">
              <button type="button" class="btn btn-outline-info filter-btn active" data-filter="all">
                <i class="fas fa-briefcase"></i> All My Tickets
              </button>
              <button type="button" class="btn btn-outline-success filter-btn" data-filter="open">
                <i class="fas fa-folder-open"></i> Open
              </button>
              <button type="button" class="btn btn-outline-warning filter-btn" data-filter="pending">
                <i class="fas fa-clock"></i> Pending
              </button>
              <button type="button" class="btn btn-outline-primary filter-btn" data-filter="solved">
                <i class="fas fa-check-circle"></i> Solved
              </button>
              <button type="button" class="btn btn-outline-secondary filter-btn" data-filter="today">
                <i class="fas fa-calendar-day"></i> Today's Work
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tickets Table -->
    <div class="card ticket-table">
      <div class="card-header bg-white border-0 py-3">
        <div class="d-flex justify-content-between align-items-center">
          <h5 class="mb-0 fw-bold">
            <i class="fas fa-user-tie me-2 text-info"></i>My Personal Queue
          </h5>
          <div class="d-flex align-items-center gap-3">
            <span class="badge bg-info bg-opacity-10 text-info">
              <i class="fas fa-user-check me-1"></i>
              Assigned to <?php echo htmlspecialchars($user->name ?? 'You'); ?>
            </span>
            <?php if($activeTickets > 0): ?>
            <span class="badge bg-warning text-white">
              <i class="fas fa-exclamation me-1"></i>
              <?php echo $activeTickets; ?> Active
            </span>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <div class="card-body pt-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle" id="dataTable">
            <thead>
              <tr>
                <th class="border-0">Ticket ID</th>
                <th class="border-0">Subject</th>
                <th class="border-0">Priority</th>
                <th class="border-0">Requester</th>
                <th class="border-0">Status</th>
                <th class="border-0">Last Update</th>
                <th class="border-0">Age</th>
                <th class="border-0 text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($tickets as $ticket):?>
              <?php
                $created = strtotime($ticket->created_at);
                $now = time();
                $ageHours = round(($now - $created) / 3600, 1);
                $ageDays = round($ageHours / 24, 1);
                $priority = $ticket->priority ?? 'medium';
              ?>
              <tr data-status="<?php echo $ticket->status; ?>" 
                  data-priority="<?php echo $priority; ?>"
                  data-updated="<?php echo $ticket->updated_at ?? $ticket->created_at; ?>">
                <td class="fw-semibold">
                  <span class="personal-indicator"></span>
                  #<?php echo str_pad($ticket->id, 5, '0', STR_PAD_LEFT); ?>
                </td>
                <td>
                  <a href="./ticket-details.php?id=<?php echo $ticket->id?>" 
                     class="text-decoration-none text-dark fw-medium">
                    <i class="fas fa-ticket me-2 text-info"></i>
                    <?php echo htmlspecialchars($ticket->title)?>
                  </a>
                </td>
                <td>
                  <?php 
                    $priorityIcon = $priority == 'high' ? 'exclamation-circle' : ($priority == 'low' ? 'arrow-down' : 'minus-circle');
                  ?>
                  <span class="badge priority-<?php echo $priority; ?>">
                    <i class="fas fa-<?php echo $priorityIcon; ?> me-1"></i><?php echo ucfirst($priority); ?>
                  </span>
                </td>
                <td>
                  <div class="d-flex align-items-center">
                    <div class="avatar-sm rounded-circle bg-info bg-opacity-10 text-info me-2 d-flex align-items-center justify-content-center" 
                         style="width: 32px; height: 32px;">
                      <i class="fas fa-user" style="font-size: 14px;"></i>
                    </div>
                    <span><?php echo htmlspecialchars(Requester::find($ticket->requester)->name ?? 'Unknown')?></span>
                  </div>
                </td>
                <td>
                  <?php
                    $statusClass = '';
                    $statusIcon = '';
                    switch($ticket->status) {
                      case 'open':
                        $statusClass = 'status-open';
                        $statusIcon = 'folder-open';
                        break;
                      case 'pending':
                        $statusClass = 'status-pending';
                        $statusIcon = 'clock';
                        break;
                      case 'solved':
                        $statusClass = 'status-solved';
                        $statusIcon = 'check-circle';
                        break;
                      default:
                        $statusClass = 'status-open';
                        $statusIcon = 'folder-open';
                    }
                  ?>
                  <span class="status-badge <?php echo $statusClass; ?>">
                    <i class="fas fa-<?php echo $statusIcon; ?> me-1"></i><?php echo strtoupper($ticket->status); ?>
                  </span>
                </td>
                <td>
                  <?php 
                    $lastUpdate = new DateTime($ticket->updated_at ?? $ticket->created_at);
                    $now = new DateTime();
                    $interval = $lastUpdate->diff($now);
                    
                    if($interval->days == 0) {
                      if($interval->h == 0) {
                        $timeAgo = $interval->i . ' min ago';
                      } else {
                        $timeAgo = $interval->h . 'h ago';
                      }
                    } else {
                      $timeAgo = $interval->days . ' days ago';
                    }
                  ?>
                  <small class="text-muted">
                    <i class="fas fa-sync me-1"></i>
                    <?php echo $timeAgo; ?>
                  </small>
                </td>
                <td>
                  <span class="fw-medium <?php echo $ageHours > 72 ? 'text-danger' : ($ageHours > 24 ? 'text-warning' : 'text-success'); ?>">
                    <?php 
                      if($ageHours < 24) {
                        echo $ageHours . 'h';
                      } else {
                        echo $ageDays . 'd';
                      }
                    ?>
                  </span>
                </td>
                <td class="text-center">
                  <div class="dropdown">
                    <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                      <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                      <li>
                        <a class="dropdown-item" href="./ticket-details.php?id=<?php echo $ticket->id?>">
                          <i class="fas fa-eye me-2 text-info"></i>View Details
                        </a>
                      </li>
                      <?php if($ticket->status != 'solved'): ?>
                      <li>
                        <a class="dropdown-item" href="#">
                          <i class="fas fa-play me-2 text-success"></i>Start Working
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item" href="#">
                          <i class="fas fa-comment me-2 text-primary"></i>Add Update
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item" href="#">
                          <i class="fas fa-check-circle me-2 text-success"></i>Mark as Solved
                        </a>
                      </li>
                      <?php endif; ?>
                      <li><hr class="dropdown-divider"></li>
                      <li>
                        <a class="dropdown-item" href="#">
                          <i class="fas fa-user-plus me-2 text-warning"></i>Transfer
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item text-danger" href="#">
                          <i class="fas fa-level-up-alt me-2"></i>Escalate
                        </a>
                      </li>
                    </ul>
                  </div>
                </td>
              </tr>
              <?php endforeach?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Footer Section -->
    <div class="footer-section">
      <div class="footer-content text-center">
        © <?php echo date('Y'); ?> ICT Helpdesk. All rights reserved.
      </div>
    </div>

  </div>
  </div>
  </section>
</div>

<!-- Bootstrap 5 and other scripts -->
<script src="vendor/jquery/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="vendor/datatables/jquery.dataTables.js"></script>
<script src="vendor/datatables/dataTables.bootstrap4.js"></script>
<script src="js/sb-admin.min.js"></script>

<!-- Custom JavaScript -->
<script>
$(document).ready(function() {
    // Initialize DataTable
    var table;
    if ($.fn.DataTable.isDataTable('#dataTable')) {
        table = $('#dataTable').DataTable();
    } else {
        table = $('#dataTable').DataTable({
            "pageLength": 10,
            "language": {
                "search": "",
                "searchPlaceholder": "Search tickets...",
                "lengthMenu": "Show _MENU_ entries",
                "info": "Showing _START_ to _END_ of _TOTAL_ my tickets",
                "paginate": {
                    "first": "First",
                    "last": "Last",
                    "next": " Next →",
                    "previous": "← Previous "
                }
            },
            "dom": 'rt<"pagination-wrapper"lip>',
            "order": [[5, 'desc']], // Sort by last update
            "responsive": true,
            "lengthMenu": [[5, 10, 25, 50], [5, 10, 25, 50]]
        });
    }
    
    // Custom search functionality
    $('#searchInput').on('keyup', function() {
        table.search(this.value).draw();
    });
    
    // Filter functionality
    $('.filter-btn').on('click', function() {
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');
        
        var filterValue = $(this).data('filter');
        var today = new Date().toDateString();
        
        table.rows().every(function() {
            var node = $(this.node());
            var status = node.data('status');
            var updated = new Date(node.data('updated'));
            var show = false;
            
            switch(filterValue) {
                case 'all':
                    show = true;
                    break;
                case 'open':
                    show = status === 'open';
                    break;
                case 'pending':
                    show = status === 'pending';
                    break;
                case 'solved':
                    show = status === 'solved';
                    break;
                case 'today':
                    show = updated.toDateString() === today;
                    break;
            }
            
            if(show) {
                node.show();
            } else {
                node.hide();
            }
        });
        
        table.draw();
    });
    
    // Animation on load
    $('.stat-card').each(function(index) {
        $(this).css('opacity', '0').delay(index * 100).animate({
            opacity: 1
        }, 500);
    });
    
    // Productivity circle animation
    setTimeout(function() {
        $('.productivity-circle circle:last-child').css('stroke-dasharray', function() {
            var score = parseInt($('.score-text').text());
            return (score/100)*157 + ', 157';
        });
    }, 1000);
    
    // Load create ticket modal functionality
    $.getScript('./includes/create-ticket-modal.js');
});
</script>

<!-- Include Create Ticket Modal -->
<?php include './includes/create-ticket-modal.php'; ?>

</body>
</html>