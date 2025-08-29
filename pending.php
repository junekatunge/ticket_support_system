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
    $allTicket = $ticket::findByStatus('pending');

    $requester = new Requester();
    $team = new Team();
    $user = new User();

    // Calculate statistics for pending tickets
    $totalPending = count($allTicket);
    $highPriority = count(array_filter($allTicket, function($t) { return ($t->priority ?? 'medium') == 'high'; }));
    $overdue = count(array_filter($allTicket, function($t) { 
        return strtotime($t->created_at) < strtotime('-72 hours'); 
    }));
    $todayPending = count(array_filter($allTicket, function($t) { 
        return date('Y-m-d', strtotime($t->created_at)) == date('Y-m-d'); 
    }));
    
    // Calculate average wait time for pending tickets
    $waitTimes = [];
    foreach($allTicket as $t) {
        $created = strtotime($t->created_at);
        $now = time();
        $waitTimes[] = ($now - $created) / 3600; // in hours
    }
    $avgWaitTime = !empty($waitTimes) ? round(array_sum($waitTimes) / count($waitTimes), 1) : 0;
    
    // Count escalation needed (high priority + overdue)
    $escalationNeeded = count(array_filter($allTicket, function($t) { 
        return ($t->priority ?? 'medium') == 'high' && strtotime($t->created_at) < strtotime('-24 hours'); 
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
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
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
    background-color: #fef3c7;
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
  .status-pending {
    background-color: #fef3c7;
    color: #92400e;
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
  .urgency-critical {
    background-color: #dc2626;
    color: white;
    animation: pulse 2s infinite;
  }
  .urgency-high {
    background-color: #f59e0b;
    color: white;
  }
  .urgency-normal {
    background-color: #10b981;
    color: white;
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
    border-color: #f59e0b;
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
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
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(245, 158, 11, 0.3);
  }
  
  .dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white !important;
    box-shadow: 0 4px 8px rgba(245, 158, 11, 0.3);
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
  
  /* Wait time indicator */
  .wait-indicator {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    margin-right: 6px;
  }
  .wait-normal {
    background-color: #10b981;
  }
  .wait-attention {
    background-color: #f59e0b;
  }
  .wait-critical {
    background-color: #ef4444;
  }
  
  /* Escalation alert */
  .escalation-alert {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
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
  <title>Pending Tickets - Helpdesk</title>
  
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
        <h1 class="h3 mb-1 text-gray-800">Pending Tickets</h1>
        <p class="mb-0 text-muted">Support requests awaiting action or response</p>
      </div>
      <div class="d-flex gap-2">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTicketModal" style="background: linear-gradient(135deg, #8B4513 0%, #D2B48C 100%); border: none; box-shadow: 0 2px 4px rgba(139, 69, 19, 0.3);">
          <i class="fas fa-plus me-2"></i>Create New Ticket
        </button>
        <button class="btn btn-warning">
          <i class="fas fa-bell me-2"></i>Set Reminders
        </button>
        <button class="btn btn-success">
          <i class="fas fa-rocket me-2"></i>Bulk Actions
        </button>
      </div>
    </div>

    <!-- Escalation Alert -->
    <?php if($escalationNeeded > 0): ?>
    <div class="escalation-alert">
      <div class="d-flex align-items-center">
        <i class="fas fa-exclamation-triangle me-3" style="font-size: 24px;"></i>
        <div>
          <h6 class="mb-1 fw-bold">⚠️ Escalation Required!</h6>
          <p class="mb-0"><?php echo $escalationNeeded; ?> high-priority tickets have been pending for over 24 hours and need immediate attention.</p>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row mb-4">
      <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stat-card">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                <i class="fas fa-clock"></i>
              </div>
              <div>
                <div class="text-muted small">Total Pending</div>
                <div class="h4 mb-0"><?php echo $totalPending; ?></div>
                <small class="text-warning">
                  <i class="fas fa-hourglass-half"></i> Awaiting action
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
                  <i class="fas fa-arrow-up"></i> Urgent attention
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
                <i class="fas fa-stopwatch"></i>
              </div>
              <div>
                <div class="text-muted small">Avg Wait Time</div>
                <div class="h4 mb-0"><?php echo $avgWaitTime; ?>h</div>
                <small class="text-muted">
                  Current average
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
              <div class="stat-icon bg-secondary bg-opacity-10 text-secondary me-3">
                <i class="fas fa-exclamation-circle"></i>
              </div>
              <div>
                <div class="text-muted small">Overdue (>72h)</div>
                <div class="h4 mb-0"><?php echo $overdue; ?></div>
                <small class="text-secondary">
                  Need attention
                </small>
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
                     id="searchInput" placeholder="Search pending tickets...">
            </div>
          </div>
          <div class="col-md-8 text-end">
            <div class="btn-group" role="group">
              <button type="button" class="btn btn-outline-warning filter-btn active" data-filter="all">
                <i class="fas fa-clock"></i> All Pending
              </button>
              <button type="button" class="btn btn-outline-danger filter-btn" data-filter="high">
                <i class="fas fa-fire"></i> High Priority
              </button>
              <button type="button" class="btn btn-outline-secondary filter-btn" data-filter="overdue">
                <i class="fas fa-exclamation-triangle"></i> Overdue
              </button>
              <button type="button" class="btn btn-outline-info filter-btn" data-filter="today">
                <i class="fas fa-calendar-day"></i> Today's
              </button>
              <button type="button" class="btn btn-outline-success filter-btn" data-filter="escalation">
                <i class="fas fa-level-up-alt"></i> Escalation
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
            <i class="fas fa-hourglass-half me-2 text-warning"></i>Pending Tickets Queue
          </h5>
          <div class="d-flex align-items-center gap-3">
            <span class="badge bg-warning bg-opacity-10 text-warning">
              <i class="fas fa-clock me-1"></i>
              <?php echo $totalPending; ?> Tickets Waiting
            </span>
            <?php if($escalationNeeded > 0): ?>
            <span class="badge bg-danger text-white">
              <i class="fas fa-exclamation me-1"></i>
              <?php echo $escalationNeeded; ?> Need Escalation
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
                <th class="border-0">Team</th>
                <th class="border-0">Wait Time</th>
                <th class="border-0">Status</th>
                <th class="border-0">Urgency</th>
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
                
                // Determine urgency based on priority and wait time
                if($priority == 'high' && $waitHours > 24) {
                  $urgencyClass = 'urgency-critical';
                  $urgencyText = 'CRITICAL';
                  $urgencyIcon = 'exclamation-triangle';
                } elseif($priority == 'high' || $waitHours > 72) {
                  $urgencyClass = 'urgency-high';
                  $urgencyText = 'HIGH';
                  $urgencyIcon = 'fire';
                } else {
                  $urgencyClass = 'urgency-normal';
                  $urgencyText = 'NORMAL';
                  $urgencyIcon = 'clock';
                }
                
                // Wait time indicator
                if($waitHours < 24) {
                  $waitClass = 'wait-normal';
                } elseif($waitHours < 72) {
                  $waitClass = 'wait-attention';
                } else {
                  $waitClass = 'wait-critical';
                }
              ?>
              <tr data-priority="<?php echo $priority; ?>" 
                  data-wait-hours="<?php echo $waitHours; ?>"
                  data-created="<?php echo $ticket->created_at; ?>">
                <td class="fw-semibold">
                  <span class="wait-indicator <?php echo $waitClass; ?>"></span>
                  #<?php echo str_pad($ticket->id, 5, '0', STR_PAD_LEFT); ?>
                </td>
                <td>
                  <a href="./ticket-details.php?id=<?php echo $ticket->id?>" 
                     class="text-decoration-none text-dark fw-medium">
                    <i class="fas fa-hourglass-half me-2 text-warning"></i>
                    <?php echo htmlspecialchars($ticket->title)?>
                  </a>
                  <?php if($priority == 'high' && $waitHours > 24): ?>
                    <div class="small mt-1">
                      <span class="badge bg-danger text-white">
                        <i class="fas fa-bolt me-1"></i>ESCALATE NOW
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
                    <div class="avatar-sm rounded-circle bg-warning bg-opacity-10 text-warning me-2 d-flex align-items-center justify-content-center" 
                         style="width: 32px; height: 32px;">
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
                    <span class="fw-medium">
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
                  <span class="status-badge status-pending">
                    <i class="fas fa-pause-circle me-1"></i>PENDING
                  </span>
                </td>
                <td>
                  <span class="badge <?php echo $urgencyClass; ?>">
                    <i class="fas fa-<?php echo $urgencyIcon; ?> me-1"></i><?php echo $urgencyText; ?>
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
                      <li>
                        <a class="dropdown-item" href="#">
                          <i class="fas fa-play me-2 text-success"></i>Resume Work
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item" href="#">
                          <i class="fas fa-user-plus me-2 text-primary"></i>Assign Agent
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item" href="#">
                          <i class="fas fa-level-up-alt me-2 text-warning"></i>Escalate
                        </a>
                      </li>
                      <li><hr class="dropdown-divider"></li>
                      <li>
                        <a class="dropdown-item" href="#">
                          <i class="fas fa-comments me-2 text-info"></i>Add Update
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item text-success" href="#">
                          <i class="fas fa-check-circle me-2"></i>Mark as Solved
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

<!-- Include Create Ticket Modal -->
<?php include 'includes/create-ticket-modal.php'; ?>

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
                "info": "Showing _START_ to _END_ of _TOTAL_ pending tickets",
                "paginate": {
                    "first": "First",
                    "last": "Last",
                    "next": " Next →",
                    "previous": "← Previous "
                }
            },
            "dom": 'rt<"pagination-wrapper"lip>',
            "order": [[7, 'desc'], [5, 'desc']], // Sort by urgency, then wait time
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
                case 'overdue':
                    show = waitHours > 72;
                    break;
                case 'today':
                    show = created.toDateString() === today.toDateString();
                    break;
                case 'escalation':
                    show = priority === 'high' && waitHours > 24;
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
    
    // Highlight critical rows
    $('tbody tr').each(function() {
        var waitHours = parseFloat($(this).data('wait-hours'));
        var priority = $(this).data('priority');
        
        if(priority === 'high' && waitHours > 24) {
            $(this).addClass('table-warning');
        }
    });
    
    // Auto-refresh page every 5 minutes for real-time updates
    setInterval(function() {
        if(!document.hidden) {
            location.reload();
        }
    }, 300000); // 5 minutes
    
    // Load create ticket modal functionality
    $.getScript('./includes/create-ticket-modal.js');
});
</script>

<!-- Include Create Ticket Modal -->
<?php include './includes/create-ticket-modal.php'; ?>

</body>
</html>