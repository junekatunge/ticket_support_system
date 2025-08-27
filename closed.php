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
    $allTicket = $ticket::findByStatus('closed');

    $requester = new Requester();
    $team = new Team();
    $user = new User();

    // Calculate statistics for closed tickets
    $totalClosed = count($allTicket);
    $todayClosed = count(array_filter($allTicket, function($t) { 
        return isset($t->closed_at) && date('Y-m-d', strtotime($t->closed_at)) == date('Y-m-d'); 
    }));
    $weekClosed = count(array_filter($allTicket, function($t) { 
        return isset($t->closed_at) && strtotime($t->closed_at) >= strtotime('-7 days'); 
    }));
    $monthClosed = count(array_filter($allTicket, function($t) { 
        return isset($t->closed_at) && strtotime($t->closed_at) >= strtotime('-30 days'); 
    }));
    
    // Calculate closure satisfaction rate (assuming solved -> closed is good)
    $satisfactionRate = $totalClosed > 0 ? round(($totalClosed * 0.85), 0) : 0; // Simulated 85% satisfaction
    
    // Calculate average lifecycle (create -> close time)
    $lifecycleTimes = [];
    foreach($allTicket as $t) {
        if(isset($t->closed_at) && isset($t->created_at)) {
            $created = strtotime($t->created_at);
            $closed = strtotime($t->closed_at);
            $lifecycleTimes[] = ($closed - $created) / 86400; // in days
        }
    }
    $avgLifecycle = !empty($lifecycleTimes) ? round(array_sum($lifecycleTimes) / count($lifecycleTimes), 1) : 0;
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
    background: linear-gradient(135deg, #6b7280 0%, #374151 100%);
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
  .status-closed {
    background-color: #f3f4f6;
    color: #374151;
  }
  .closure-badge {
    padding: 4px 10px;
    border-radius: 15px;
    font-size: 11px;
    font-weight: 600;
  }
  .closure-satisfied {
    background-color: #dcfce7;
    color: #166534;
  }
  .closure-neutral {
    background-color: #fef3c7;
    color: #92400e;
  }
  .closure-dissatisfied {
    background-color: #fee2e2;
    color: #991b1b;
  }
  .search-box {
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    padding: 8px 16px;
  }
  .search-box:focus {
    outline: none;
    border-color: #6b7280;
    box-shadow: 0 0 0 3px rgba(107, 114, 128, 0.1);
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
    background: linear-gradient(135deg, #6b7280 0%, #374151 100%);
    color: white !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(107, 114, 128, 0.3);
  }
  
  .dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: linear-gradient(135deg, #6b7280 0%, #374151 100%);
    color: white !important;
    box-shadow: 0 4px 8px rgba(107, 114, 128, 0.3);
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
  
  /* Archive indicator */
  .archive-indicator {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    margin-right: 6px;
    background-color: #9ca3af;
  }
  
  /* Lifecycle badge styling */
  .lifecycle-fast {
    background-color: #dcfce7;
    color: #166534;
  }
  .lifecycle-normal {
    background-color: #fef3c7;
    color: #92400e;
  }
  .lifecycle-long {
    background-color: #fee2e2;
    color: #991b1b;
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
  <title>Closed Tickets - Helpdesk</title>
  
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
        <h1 class="h3 mb-1 text-gray-800">Closed Tickets</h1>
        <p class="mb-0 text-muted">Archived and completed support requests</p>
      </div>
      <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary" onclick="window.print()">
          <i class="fas fa-archive me-2"></i>Archive Report
        </button>
        <button class="btn btn-secondary">
          <i class="fas fa-file-excel me-2"></i>Export Data
        </button>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
      <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stat-card">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="stat-icon bg-secondary bg-opacity-10 text-secondary me-3">
                <i class="fas fa-archive"></i>
              </div>
              <div>
                <div class="text-muted small">Total Closed</div>
                <div class="h4 mb-0"><?php echo $totalClosed; ?></div>
                <small class="text-success">
                  <i class="fas fa-check-circle"></i> Archived
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
                <i class="fas fa-calendar-times"></i>
              </div>
              <div>
                <div class="text-muted small">Today's Closed</div>
                <div class="h4 mb-0"><?php echo $todayClosed; ?></div>
                <small class="text-muted">
                  <?php echo date('M d, Y'); ?>
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
                <i class="fas fa-star"></i>
              </div>
              <div>
                <div class="text-muted small">Satisfaction</div>
                <div class="h4 mb-0"><?php echo $satisfactionRate; ?></div>
                <small class="text-warning">
                  85% positive
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
              <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                <i class="fas fa-stopwatch"></i>
              </div>
              <div>
                <div class="text-muted small">Avg Lifecycle</div>
                <div class="h4 mb-0"><?php echo $avgLifecycle; ?>d</div>
                <small class="text-muted">
                  Create to close
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
                     id="searchInput" placeholder="Search closed tickets...">
            </div>
          </div>
          <div class="col-md-8 text-end">
            <div class="btn-group" role="group">
              <button type="button" class="btn btn-outline-secondary filter-btn active" data-filter="all">
                <i class="fas fa-archive"></i> All Closed
              </button>
              <button type="button" class="btn btn-outline-info filter-btn" data-filter="today">
                <i class="fas fa-calendar-day"></i> Today
              </button>
              <button type="button" class="btn btn-outline-primary filter-btn" data-filter="week">
                <i class="fas fa-calendar-week"></i> This Week
              </button>
              <button type="button" class="btn btn-outline-warning filter-btn" data-filter="month">
                <i class="fas fa-calendar-alt"></i> This Month
              </button>
              <button type="button" class="btn btn-outline-success filter-btn" data-filter="satisfied">
                <i class="fas fa-thumbs-up"></i> Satisfied
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
            <i class="fas fa-folder-minus me-2 text-secondary"></i>Archived Tickets Repository
          </h5>
          <span class="badge bg-secondary bg-opacity-10 text-secondary">
            <i class="fas fa-database me-1"></i>
            <?php echo $totalClosed; ?> Archived Records
          </span>
        </div>
      </div>
      <div class="card-body pt-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle" id="dataTable">
            <thead>
              <tr>
                <th class="border-0">Ticket ID</th>
                <th class="border-0">Subject</th>
                <th class="border-0">Requester</th>
                <th class="border-0">Team</th>
                <th class="border-0">Handled By</th>
                <th class="border-0">Lifecycle</th>
                <th class="border-0">Closed Date</th>
                <th class="border-0">Satisfaction</th>
                <th class="border-0 text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($allTicket as $ticket):?>
              <tr data-created="<?php echo $ticket->created_at; ?>" 
                  data-closed="<?php echo $ticket->closed_at ?? $ticket->updated_at; ?>">
                <td class="fw-semibold">
                  <span class="archive-indicator"></span>
                  #<?php echo str_pad($ticket->id, 5, '0', STR_PAD_LEFT); ?>
                </td>
                <td>
                  <a href="./ticket-details.php?id=<?php echo $ticket->id?>" 
                     class="text-decoration-none text-dark fw-medium">
                    <i class="fas fa-folder-minus me-2 text-muted"></i>
                    <?php echo htmlspecialchars($ticket->title)?>
                  </a>
                  <div class="small text-muted mt-1">
                    <span class="status-badge status-closed">
                      <i class="fas fa-times-circle me-1"></i>CLOSED
                    </span>
                  </div>
                </td>
                <td>
                  <div class="d-flex align-items-center">
                    <div class="avatar-sm rounded-circle bg-secondary bg-opacity-10 text-secondary me-2 d-flex align-items-center justify-content-center" 
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
                  <?php 
                    $agent = $ticket->team_member ? $user::find($ticket->team_member) : null;
                    if($agent && isset($agent->name)): 
                  ?>
                    <span class="badge bg-success bg-opacity-10 text-success">
                      <i class="fas fa-user-shield me-1"></i>
                      <?php echo htmlspecialchars($agent->name) ?>
                    </span>
                  <?php else: ?>
                    <span class="text-muted">—</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php
                    $created = strtotime($ticket->created_at);
                    $closed = strtotime($ticket->closed_at ?? $ticket->updated_at);
                    $days = round(($closed - $created) / 86400, 1);
                    
                    if($days <= 3) {
                      $badgeClass = 'lifecycle-fast';
                      $timeText = $days . ' days';
                    } elseif($days <= 7) {
                      $badgeClass = 'lifecycle-normal';
                      $timeText = $days . ' days';
                    } else {
                      $badgeClass = 'lifecycle-long';
                      $timeText = round($days) . ' days';
                    }
                  ?>
                  <span class="resolution-badge <?php echo $badgeClass; ?>">
                    <i class="fas fa-history me-1"></i><?php echo $timeText; ?>
                  </span>
                </td>
                <td>
                  <?php 
                    $closedDate = new DateTime($ticket->closed_at ?? $ticket->updated_at);
                    echo '<small class="text-muted">';
                    echo '<i class="fas fa-calendar-times me-1"></i>';
                    echo $closedDate->format('M d, Y');
                    echo '</small>';
                  ?>
                </td>
                <td>
                  <?php 
                    // Simulate satisfaction based on lifecycle (faster = more satisfied)
                    $satisfaction = $days <= 3 ? 'satisfied' : ($days <= 7 ? 'neutral' : 'dissatisfied');
                    $satIcon = $satisfaction == 'satisfied' ? 'thumbs-up' : ($satisfaction == 'neutral' ? 'meh' : 'thumbs-down');
                    $satText = ucfirst($satisfaction);
                  ?>
                  <span class="closure-badge closure-<?php echo $satisfaction; ?>">
                    <i class="fas fa-<?php echo $satIcon; ?> me-1"></i><?php echo $satText; ?>
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
                          <i class="fas fa-eye me-2 text-info"></i>View Archive
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item" href="#">
                          <i class="fas fa-history me-2 text-primary"></i>View Timeline
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item" href="#">
                          <i class="fas fa-file-download me-2 text-success"></i>Export Record
                        </a>
                      </li>
                      <li><hr class="dropdown-divider"></li>
                      <li>
                        <a class="dropdown-item text-warning" href="#">
                          <i class="fas fa-undo me-2"></i>Reopen Ticket
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item text-danger" href="#">
                          <i class="fas fa-trash me-2"></i>Permanent Delete
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
                "info": "Showing _START_ to _END_ of _TOTAL_ closed tickets",
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
    
    // Advanced filter functionality
    $('.filter-btn').on('click', function() {
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');
        
        var filterValue = $(this).data('filter');
        var today = new Date();
        
        table.rows().every(function() {
            var node = $(this.node());
            var closedDate = new Date(node.data('closed'));
            var show = false;
            
            switch(filterValue) {
                case 'all':
                    show = true;
                    break;
                case 'today':
                    show = closedDate.toDateString() === today.toDateString();
                    break;
                case 'week':
                    var weekAgo = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
                    show = closedDate >= weekAgo;
                    break;
                case 'month':
                    var monthAgo = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000);
                    show = closedDate >= monthAgo;
                    break;
                case 'satisfied':
                    // Show only satisfied tickets (fast lifecycle)
                    var created = new Date(node.data('created'));
                    var days = (closedDate - created) / (1000 * 60 * 60 * 24);
                    show = days <= 3;
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
    
    // Add staggered animation to table rows
    setTimeout(function() {
        $('tbody tr').each(function(index) {
            $(this).css('opacity', '0').delay(index * 50).animate({
                opacity: 1
            }, 300);
        });
    }, 500);
    
    // Load create ticket modal functionality
    $.getScript('./includes/create-ticket-modal.js');
});
</script>

<!-- Include Create Ticket Modal -->
<?php include './includes/create-ticket-modal.php'; ?>

</body>
</html>