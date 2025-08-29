<?php
session_start();
if (!isset($_SESSION['logged-in']) || $_SESSION['logged-in'] == false) {
    header('Location: ./index.php');
    exit();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/src/database.php';
require_once __DIR__ . '/src/ticket.php';
require_once __DIR__ . '/src/requester.php';
require_once __DIR__ . '/src/team.php';
require_once __DIR__ . '/src/user.php';

$ticket = new Ticket();
$allTicket = Ticket::findByStatus('open');

$requester = new Requester();
$team = new Team();
$user = new User();

// Calculate statistics
$totalOpen = count($allTicket);
$highPriority = count(array_filter($allTicket, function($t) { return ($t->priority ?? 'medium') == 'high'; }));
$unassigned = count(array_filter($allTicket, function($t) { return empty($t->team_member); }));
$todayTickets = count(array_filter($allTicket, function($t) { 
    return date('Y-m-d', strtotime($t->created_at)) == date('Y-m-d'); 
}));
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
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
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
    background-color: #f8f9fa;
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
    border-color: #22c55e;
    box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
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
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    color: white !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(34, 197, 94, 0.3);
  }
  
  .dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    color: white !important;
    box-shadow: 0 4px 8px rgba(34, 197, 94, 0.3);
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
  }
</style>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Open Tickets - Helpdesk</title>
  
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
        <h1 class="h3 mb-1 text-gray-800">Open Tickets</h1>
        <p class="mb-0 text-muted">Manage and resolve active support requests</p>
      </div>
      <?php include './includes/create-ticket-button.php'; ?>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
      <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stat-card">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                <i class="fas fa-folder-open"></i>
              </div>
              <div>
                <div class="text-muted small">Total Open</div>
                <div class="h4 mb-0"><?php echo $totalOpen; ?></div>
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
                <i class="fas fa-exclamation-triangle"></i>
              </div>
              <div>
                <div class="text-muted small">High Priority</div>
                <div class="h4 mb-0"><?php echo $highPriority; ?></div>
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
                <i class="fas fa-user-slash"></i>
              </div>
              <div>
                <div class="text-muted small">Unassigned</div>
                <div class="h4 mb-0"><?php echo $unassigned; ?></div>
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
                <i class="fas fa-calendar-day"></i>
              </div>
              <div>
                <div class="text-muted small">Today's Tickets</div>
                <div class="h4 mb-0"><?php echo $todayTickets; ?></div>
              </div>
            </div>
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
                     id="searchInput" placeholder="Search open tickets...">
            </div>
          </div>
          <div class="col-md-8 text-end">
            <div class="btn-group" role="group">
              <button type="button" class="btn btn-outline-success filter-btn active" data-filter="all">All Open</button>
              <button type="button" class="btn btn-outline-danger filter-btn" data-filter="high">High Priority</button>
              <button type="button" class="btn btn-outline-warning filter-btn" data-filter="medium">Medium Priority</button>
              <button type="button" class="btn btn-outline-info filter-btn" data-filter="low">Low Priority</button>
              <button type="button" class="btn btn-outline-secondary filter-btn" data-filter="unassigned">Unassigned</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tickets Table -->
    <div class="card ticket-table">
      <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0 fw-bold">
          <i class="fas fa-ticket me-2 text-success"></i>Open Tickets Queue
        </h5>
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
                <th class="border-0">Team</th>
                <th class="border-0">Assigned To</th>
                <th class="border-0">Created</th>
                <th class="border-0 text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($allTicket as $ticket): ?>
              <tr data-priority="<?php echo $ticket->priority ?? 'medium'; ?>" 
                  data-assigned="<?php echo empty($ticket->team_member) ? 'unassigned' : 'assigned'; ?>">
                <td class="fw-semibold">#<?php echo str_pad($ticket->id, 5, '0', STR_PAD_LEFT); ?></td>
                <td>
                  <a href="./ticket-details.php?id=<?= htmlspecialchars($ticket->id) ?>"
                     class="text-decoration-none text-dark fw-medium">
                    <i class="fas fa-ticket-alt me-2 text-muted"></i>
                    <?= htmlspecialchars($ticket->title) ?>
                  </a>
                </td>
                <td>
                  <?php 
                    $priority = $ticket->priority ?? 'medium';
                    $priorityClass = $priority == 'high' ? 'danger' : ($priority == 'low' ? 'info' : 'warning');
                    $priorityIcon = $priority == 'high' ? 'exclamation-circle' : ($priority == 'low' ? 'arrow-down' : 'minus-circle');
                  ?>
                  <span class="badge priority-<?php echo $priority; ?>">
                    <i class="fas fa-<?php echo $priorityIcon; ?> me-1"></i><?php echo ucfirst($priority); ?>
                  </span>
                </td>
                <td>
                  <div class="d-flex align-items-center">
                    <div class="avatar-sm rounded-circle bg-success bg-opacity-10 text-success me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                      <i class="fas fa-user" style="font-size: 14px;"></i>
                    </div>
                    <span><?= htmlspecialchars(($requester::find($ticket->requester)->name ?? '—')) ?></span>
                  </div>
                </td>
                <td>
                  <span class="badge bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-users me-1"></i>
                    <?= htmlspecialchars(($team::find($ticket->team)->name ?? '—')) ?>
                  </span>
                </td>
                <td>
                  <?php
                    $agent = $ticket->team_member ? $user::find((int)$ticket->team_member) : null;
                    if ($agent) {
                      echo '<span class="badge bg-success bg-opacity-10 text-success">';
                      echo '<i class="fas fa-user-check me-1"></i>';
                      echo htmlspecialchars($agent->name);
                      echo '</span>';
                    } else {
                      echo '<span class="badge bg-secondary bg-opacity-10 text-secondary">';
                      echo '<i class="fas fa-user-times me-1"></i>Unassigned';
                      echo '</span>';
                    }
                  ?>
                </td>
                <td>
                  <?php
                    try {
                      $date = new DateTime($ticket->created_at);
                      $now = new DateTime();
                      $interval = $date->diff($now);
                      
                      if ($interval->days == 0) {
                        if ($interval->h == 0) {
                          $timeAgo = $interval->i . ' min ago';
                        } else {
                          $timeAgo = $interval->h . ' hours ago';
                        }
                      } else {
                        $timeAgo = $interval->days . ' days ago';
                      }
                      
                      echo '<small class="text-muted">';
                      echo '<i class="fas fa-clock me-1"></i>';
                      echo $timeAgo;
                      echo '</small>';
                    } catch (Exception $e) {
                      echo '—';
                    }
                  ?>
                </td>
                <td class="text-center">
                  <div class="dropdown">
                    <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                      <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                      <li>
                        <a class="dropdown-item" href="./ticket-details.php?id=<?= htmlspecialchars($ticket->id) ?>">
                          <i class="fas fa-eye me-2 text-info"></i>View Details
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item" href="./ticket-details.php?id=<?= htmlspecialchars($ticket->id) ?>">
                          <i class="fas fa-user-plus me-2 text-success"></i>Assign Agent
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item" href="./ticket-details.php?id=<?= htmlspecialchars($ticket->id) ?>">
                          <i class="fas fa-edit me-2 text-warning"></i>Update Status
                        </a>
                      </li>
                      <li><hr class="dropdown-divider"></li>
                      <li>
                        <a class="dropdown-item" href="./ticket-details.php?id=<?= htmlspecialchars($ticket->id) ?>">
                          <i class="fas fa-check-circle me-2 text-success"></i>Mark as Solved
                        </a>
                      </li>
                    </ul>
                  </div>
                </td>
              </tr>
              <?php endforeach ?>
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
                "info": "Showing _START_ to _END_ of _TOTAL_ open tickets",
                "paginate": {
                    "first": "First",
                    "last": "Last",
                    "next": " Next →",
                    "previous": "← Previous "
                }
            },
            "dom": 'rt<"pagination-wrapper"lip>',
            "order": [[6, 'desc']],
            "responsive": true,
            "lengthMenu": [[5, 10, 25, 50], [5, 10, 25, 50]]
        });
    }
    
    // Custom search functionality
    $('#searchInput').on('keyup', function() {
        table.search(this.value).draw();
    });
    
    // Priority filter functionality
    $('.filter-btn').on('click', function() {
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');
        
        var filterValue = $(this).data('filter');
        
        if (filterValue === 'all') {
            table.column(2).search('').draw();
            table.rows().every(function() {
                $(this.node()).show();
            });
        } else if (filterValue === 'unassigned') {
            table.rows().every(function() {
                var data = $(this.node()).data('assigned');
                if (data === 'unassigned') {
                    $(this.node()).show();
                } else {
                    $(this.node()).hide();
                }
            });
            table.draw();
        } else {
            table.rows().every(function() {
                var data = $(this.node()).data('priority');
                if (data === filterValue) {
                    $(this.node()).show();
                } else {
                    $(this.node()).hide();
                }
            });
            table.draw();
        }
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
</script>

<!-- Include Create Ticket Modal -->
<?php include './includes/create-ticket-modal.php'; ?>

</body>
</html>