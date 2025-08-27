<?php
session_start();
if (!isset($_SESSION['logged-in']) || $_SESSION['logged-in'] == false) {
    header('Location: ./index.php');
    exit();
}
    require_once './src/ticket.php';
    require_once './src/requester.php';
    require_once './src/team.php';
    require_once './src/user.php';

    $ticket = new Ticket();
    $allTicket = $ticket->unassigned();
   
    $requester = new Requester();
    $team = new Team();
    $user = new User();
    
    // Get all teams and users for assignment dropdowns
    $allTeams = $team::findAll();
    $allUsers = $user::findAll();

    // Calculate statistics for unassigned tickets
    $totalUnassigned = count($allTicket);
    $highPriority = count(array_filter($allTicket, function($t) { return ($t->priority ?? 'medium') == 'high'; }));
    $critical = count(array_filter($allTicket, function($t) { 
        return ($t->priority ?? 'medium') == 'high' && strtotime($t->created_at) < strtotime('-24 hours'); 
    }));
    $todayUnassigned = count(array_filter($allTicket, function($t) { 
        return date('Y-m-d', strtotime($t->created_at)) == date('Y-m-d'); 
    }));
    
    // Calculate average unassigned time
    $unassignedTimes = [];
    foreach($allTicket as $t) {
        $created = strtotime($t->created_at);
        $now = time();
        $unassignedTimes[] = ($now - $created) / 3600; // in hours
    }
    $avgUnassignedTime = !empty($unassignedTimes) ? round(array_sum($unassignedTimes) / count($unassignedTimes), 1) : 0;
    
    // Count by team for workload distribution
    $teamWorkload = [];
    foreach($allTicket as $t) {
        $teamName = $team::find($t->team)->name ?? 'Unknown';
        if(!isset($teamWorkload[$teamName])) {
            $teamWorkload[$teamName] = 0;
        }
        $teamWorkload[$teamName]++;
    }
    arsort($teamWorkload);
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
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
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
    background-color: #f3f4f6;
    transform: scale(1.01);
  }
  .ticket-table tbody tr.selected {
    background-color: #ede9fe !important;
    border-left: 4px solid #8b5cf6;
  }
  .status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
  }
  .status-unassigned {
    background-color: #f3e8ff;
    color: #7c3aed;
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
  .assignment-needed {
    background-color: #dc2626;
    color: white;
    animation: pulse 2s infinite;
  }
  @keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
  }
  .search-box {
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    padding: 8px 16px;
  }
  .search-box:focus {
    outline: none;
    border-color: #8b5cf6;
    box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
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
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    color: white !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(139, 92, 246, 0.3);
  }
  
  .dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    color: white !important;
    box-shadow: 0 4px 8px rgba(139, 92, 246, 0.3);
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
  
  /* Assignment indicator */
  .assignment-indicator {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    margin-right: 6px;
    background-color: #8b5cf6;
  }
  
  /* Critical alert */
  .assignment-alert {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    color: white;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
  }
  
  /* Bulk actions */
  .bulk-actions {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    color: white;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
    box-shadow: 0 4px 12px rgba(139, 92, 246, 0.2);
    display: none;
  }
  
  .bulk-actions.show {
    display: block;
  }
  
  /* Workload chart */
  .workload-card {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
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
    .bulk-actions {
      text-align: center;
    }
  }
</style>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Unassigned Tickets - Helpdesk</title>
  
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
        <h1 class="h3 mb-1 text-gray-800">Unassigned Tickets</h1>
        <p class="mb-0 text-muted">Support requests awaiting assignment to agents</p>
      </div>
      <div class="d-flex gap-2">
        <button class="btn btn-primary" onclick="toggleBulkMode()">
          <i class="fas fa-tasks me-2"></i>Bulk Assign
        </button>
        <button class="btn btn-success">
          <i class="fas fa-user-plus me-2"></i>Auto Assign
        </button>
      </div>
    </div>

    <!-- Critical Assignment Alert -->
    <?php if($critical > 0): ?>
    <div class="assignment-alert">
      <div class="d-flex align-items-center">
        <i class="fas fa-exclamation-triangle me-3" style="font-size: 24px;"></i>
        <div>
          <h6 class="mb-1 fw-bold">🚨 Critical Assignment Required!</h6>
          <p class="mb-0"><?php echo $critical; ?> high-priority tickets have been unassigned for over 24 hours and need immediate assignment.</p>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Bulk Actions Panel -->
    <div class="bulk-actions" id="bulkActionsPanel">
      <div class="row align-items-center">
        <div class="col-md-3">
          <span id="selectedCount">0 tickets selected</span>
        </div>
        <div class="col-md-3">
          <select class="form-select" id="bulkTeamSelect">
            <option value="">Select Team...</option>
            <?php foreach($allTeams as $teamOption): ?>
              <option value="<?php echo $teamOption->id; ?>"><?php echo htmlspecialchars($teamOption->name); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <select class="form-select" id="bulkAgentSelect">
            <option value="">Select Agent...</option>
            <?php foreach($allUsers as $userOption): ?>
              <option value="<?php echo $userOption->id; ?>"><?php echo htmlspecialchars($userOption->name); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <button class="btn btn-light me-2" onclick="assignSelected()">
            <i class="fas fa-check me-1"></i>Assign
          </button>
          <button class="btn btn-outline-light" onclick="clearSelection()">
            <i class="fas fa-times me-1"></i>Cancel
          </button>
        </div>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
      <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stat-card">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="stat-icon bg-purple bg-opacity-10 text-purple me-3" style="color: #8b5cf6;">
                <i class="fas fa-user-slash"></i>
              </div>
              <div>
                <div class="text-muted small">Total Unassigned</div>
                <div class="h4 mb-0"><?php echo $totalUnassigned; ?></div>
                <small class="text-warning">
                  <i class="fas fa-user-plus"></i> Need assignment
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
              <div class="stat-icon bg-danger bg-opacity-10 text-danger me-3">
                <i class="fas fa-fire"></i>
              </div>
              <div>
                <div class="text-muted small">High Priority</div>
                <div class="h4 mb-0"><?php echo $highPriority; ?></div>
                <small class="text-danger">
                  <i class="fas fa-arrow-up"></i> Urgent assignment
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
                <i class="fas fa-clock"></i>
              </div>
              <div>
                <div class="text-muted small">Avg Wait Time</div>
                <div class="h4 mb-0"><?php echo $avgUnassignedTime; ?>h</div>
                <small class="text-muted">
                  Since creation
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
                <i class="fas fa-calendar-plus"></i>
              </div>
              <div>
                <div class="text-muted small">Today's New</div>
                <div class="h4 mb-0"><?php echo $todayUnassigned; ?></div>
                <small class="text-success">
                  Need processing
                </small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Team Workload Overview -->
    <?php if(!empty($teamWorkload)): ?>
    <div class="workload-card">
      <h6 class="fw-bold mb-3"><i class="fas fa-chart-bar me-2"></i>Team Workload Distribution</h6>
      <div class="row">
        <?php foreach(array_slice($teamWorkload, 0, 4) as $teamName => $count): ?>
        <div class="col-md-3 mb-2">
          <div class="d-flex justify-content-between align-items-center p-2 bg-white rounded">
            <span class="fw-medium"><?php echo htmlspecialchars($teamName); ?></span>
            <span class="badge bg-primary"><?php echo $count; ?> tickets</span>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

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
                     id="searchInput" placeholder="Search unassigned tickets...">
            </div>
          </div>
          <div class="col-md-8 text-end">
            <div class="btn-group" role="group">
              <button type="button" class="btn btn-outline-purple filter-btn active" data-filter="all" style="border-color: #8b5cf6; color: #8b5cf6;">
                <i class="fas fa-user-slash"></i> All Unassigned
              </button>
              <button type="button" class="btn btn-outline-danger filter-btn" data-filter="high">
                <i class="fas fa-fire"></i> High Priority
              </button>
              <button type="button" class="btn btn-outline-warning filter-btn" data-filter="critical">
                <i class="fas fa-exclamation-triangle"></i> Critical
              </button>
              <button type="button" class="btn btn-outline-info filter-btn" data-filter="today">
                <i class="fas fa-calendar-day"></i> Today's
              </button>
              <button type="button" class="btn btn-outline-success filter-btn" data-filter="team">
                <i class="fas fa-users"></i> By Team
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
            <i class="fas fa-inbox me-2" style="color: #8b5cf6;"></i>Unassigned Tickets Queue
          </h5>
          <div class="d-flex align-items-center gap-3">
            <span class="badge bg-purple bg-opacity-10" style="color: #8b5cf6;">
              <i class="fas fa-user-slash me-1"></i>
              <?php echo $totalUnassigned; ?> Awaiting Assignment
            </span>
            <?php if($critical > 0): ?>
            <span class="badge bg-danger text-white">
              <i class="fas fa-exclamation me-1"></i>
              <?php echo $critical; ?> Critical
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
                <th class="border-0">
                  <input type="checkbox" id="selectAll" class="form-check-input" style="display: none;">
                  <span id="selectAllLabel">Ticket ID</span>
                </th>
                <th class="border-0">Subject</th>
                <th class="border-0">Priority</th>
                <th class="border-0">Requester</th>
                <th class="border-0">Team</th>
                <th class="border-0">Wait Time</th>
                <th class="border-0">Status</th>
                <th class="border-0">Assignment</th>
                <th class="border-0 text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($allTicket as $ticket):?>
              <?php
                $created = strtotime($ticket->created_at);
                $now = time();
                $waitHours = round(($now - $created) / 3600, 1);
                $waitDays = round($waitHours / 24, 1);
                $priority = $ticket->priority ?? 'medium';
                
                // Determine assignment urgency
                $isCritical = $priority == 'high' && $waitHours > 24;
              ?>
              <tr data-priority="<?php echo $priority; ?>" 
                  data-wait-hours="<?php echo $waitHours; ?>"
                  data-team-id="<?php echo $ticket->team; ?>"
                  data-created="<?php echo $ticket->created_at; ?>"
                  data-ticket-id="<?php echo $ticket->id; ?>">
                <td class="fw-semibold">
                  <input type="checkbox" class="form-check-input ticket-checkbox me-2" value="<?php echo $ticket->id; ?>" style="display: none;">
                  <span class="assignment-indicator"></span>
                  #<?php echo str_pad($ticket->id, 5, '0', STR_PAD_LEFT); ?>
                </td>
                <td>
                  <a href="./ticket-details.php?id=<?php echo $ticket->id?>" 
                     class="text-decoration-none text-dark fw-medium">
                    <i class="fas fa-user-slash me-2" style="color: #8b5cf6;"></i>
                    <?php echo htmlspecialchars($ticket->title)?>
                  </a>
                  <?php if($isCritical): ?>
                    <div class="small mt-1">
                      <span class="badge assignment-needed">
                        <i class="fas fa-exclamation me-1"></i>ASSIGN NOW
                      </span>
                    </div>
                  <?php endif; ?>
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
                    <div class="avatar-sm rounded-circle bg-purple bg-opacity-10 text-purple me-2 d-flex align-items-center justify-content-center" 
                         style="width: 32px; height: 32px; color: #8b5cf6;">
                      <i class="fas fa-user" style="font-size: 14px;"></i>
                    </div>
                    <span><?php echo htmlspecialchars($requester::find($ticket->requester)->name)?></span>
                  </div>
                </td>
                <td>
                  <span class="badge bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-users me-1"></i>
                    <?php echo htmlspecialchars($team::find($ticket->team)->name);?>
                  </span>
                </td>
                <td>
                  <div class="d-flex flex-column">
                    <span class="fw-medium <?php echo $waitHours > 24 ? 'text-danger' : ($waitHours > 8 ? 'text-warning' : 'text-success'); ?>">
                      <?php 
                        if($waitHours < 24) {
                          echo $waitHours . ' hours';
                        } else {
                          echo $waitDays . ' days';
                        }
                      ?>
                    </span>
                    <small class="text-muted">
                      since <?php echo date('M d', $created); ?>
                    </small>
                  </div>
                </td>
                <td>
                  <span class="status-badge status-unassigned">
                    <i class="fas fa-user-slash me-1"></i>UNASSIGNED
                  </span>
                </td>
                <td>
                  <div class="d-flex align-items-center">
                    <i class="fas fa-user-times me-2 text-muted"></i>
                    <span class="text-muted">No Agent</span>
                  </div>
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
                      <li>
                        <a class="dropdown-item" href="#" onclick="quickAssign(<?php echo $ticket->id; ?>)">
                          <i class="fas fa-user-plus me-2 text-success"></i>Quick Assign
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item" href="#" onclick="assignToMe(<?php echo $ticket->id; ?>)">
                          <i class="fas fa-hand-point-right me-2 text-primary"></i>Assign to Me
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item" href="#">
                          <i class="fas fa-robot me-2 text-info"></i>Auto Assign
                        </a>
                      </li>
                      <li><hr class="dropdown-divider"></li>
                      <li>
                        <a class="dropdown-item" href="#">
                          <i class="fas fa-edit me-2 text-warning"></i>Update Priority
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
var bulkMode = false;
var selectedTickets = [];

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
                "info": "Showing _START_ to _END_ of _TOTAL_ unassigned tickets",
                "paginate": {
                    "first": "First",
                    "last": "Last",
                    "next": " Next →",
                    "previous": "← Previous "
                }
            },
            "dom": 'rt<"pagination-wrapper"lip>',
            "order": [[5, 'desc'], [2, 'desc']], // Sort by wait time, then priority
            "responsive": true,
            "lengthMenu": [[5, 10, 25, 50], [5, 10, 25, 50]]
        });
    }
    
    // Custom search functionality
    $('#searchInput').on('keyup', function() {
        table.search(this.value).draw();
    });
    
    // Advanced filter functionality
    $('.filter-btn').on('click', function() {
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');
        
        var filterValue = $(this).data('filter');
        
        table.rows().every(function() {
            var node = $(this.node());
            var priority = node.data('priority');
            var waitHours = parseFloat(node.data('wait-hours'));
            var created = new Date(node.data('created'));
            var today = new Date();
            var show = false;
            
            switch(filterValue) {
                case 'all':
                    show = true;
                    break;
                case 'high':
                    show = priority === 'high';
                    break;
                case 'critical':
                    show = priority === 'high' && waitHours > 24;
                    break;
                case 'today':
                    show = created.toDateString() === today.toDateString();
                    break;
                case 'team':
                    // Could implement team-specific filtering
                    show = true;
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
    
    // Checkbox selection
    $(document).on('change', '.ticket-checkbox', function() {
        var ticketId = $(this).val();
        var row = $(this).closest('tr');
        
        if($(this).is(':checked')) {
            selectedTickets.push(ticketId);
            row.addClass('selected');
        } else {
            selectedTickets = selectedTickets.filter(id => id !== ticketId);
            row.removeClass('selected');
        }
        
        updateSelectedCount();
    });
    
    // Select all functionality
    $('#selectAll').on('change', function() {
        var isChecked = $(this).is(':checked');
        $('.ticket-checkbox:visible').prop('checked', isChecked).trigger('change');
    });
    
    // Animation on load
    $('.stat-card').each(function(index) {
        $(this).css('opacity', '0').delay(index * 100).animate({
            opacity: 1
        }, 500);
    });
    
    // Load create ticket modal functionality
    $.getScript('./includes/create-ticket-modal.js');
});

function toggleBulkMode() {
    bulkMode = !bulkMode;
    
    if(bulkMode) {
        $('#bulkActionsPanel').addClass('show');
        $('.ticket-checkbox, #selectAll').show();
        $('#selectAllLabel').text('Select All');
        $('button:contains("Bulk Assign")').html('<i class="fas fa-times me-2"></i>Exit Bulk');
    } else {
        $('#bulkActionsPanel').removeClass('show');
        $('.ticket-checkbox, #selectAll').hide();
        $('#selectAllLabel').text('Ticket ID');
        $('button:contains("Exit Bulk")').html('<i class="fas fa-tasks me-2"></i>Bulk Assign');
        clearSelection();
    }
}

function updateSelectedCount() {
    $('#selectedCount').text(selectedTickets.length + ' tickets selected');
}

function clearSelection() {
    selectedTickets = [];
    $('.ticket-checkbox').prop('checked', false);
    $('#selectAll').prop('checked', false);
    $('tr').removeClass('selected');
    updateSelectedCount();
}

function assignSelected() {
    if(selectedTickets.length === 0) {
        alert('Please select tickets to assign');
        return;
    }
    
    var team = $('#bulkTeamSelect').val();
    var agent = $('#bulkAgentSelect').val();
    
    if(!team && !agent) {
        alert('Please select a team or agent');
        return;
    }
    
    // Here you would implement the actual assignment logic
    alert('Assigning ' + selectedTickets.length + ' tickets...');
    // After successful assignment, reload the page or update the UI
}

function quickAssign(ticketId) {
    // Open a modal or dropdown for quick assignment
    alert('Quick assign ticket #' + ticketId);
}

function assignToMe(ticketId) {
    // Assign ticket to current user
    alert('Assigning ticket #' + ticketId + ' to you');
}
</script>

<!-- Include Create Ticket Modal -->
<?php include './includes/create-ticket-modal.php'; ?>

</body>
</html>