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
    $allTicket = $ticket::findByStatus('solved');

    $requester = new Requester();
    $team = new Team();
    $user = new User();

    // Calculate statistics
    $totalSolved = count($allTicket);
    $todaySolved = count(array_filter($allTicket, function($t) { 
        return isset($t->solved_at) && date('Y-m-d', strtotime($t->solved_at)) == date('Y-m-d'); 
    }));
    $weekSolved = count(array_filter($allTicket, function($t) { 
        return isset($t->solved_at) && strtotime($t->solved_at) >= strtotime('-7 days'); 
    }));
    $monthSolved = count(array_filter($allTicket, function($t) { 
        return isset($t->solved_at) && strtotime($t->solved_at) >= strtotime('-30 days'); 
    }));
    
    // Calculate average resolution time
    $resolutionTimes = [];
    foreach($allTicket as $t) {
        if(isset($t->solved_at) && isset($t->created_at)) {
            $created = strtotime($t->created_at);
            $solved = strtotime($t->solved_at);
            $resolutionTimes[] = ($solved - $created) / 3600; // in hours
        }
    }
    $avgResolutionTime = !empty($resolutionTimes) ? round(array_sum($resolutionTimes) / count($resolutionTimes), 1) : 0;
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
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
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
  .status-solved {
    background-color: #dbeafe;
    color: #1e40af;
  }
  .resolution-badge {
    padding: 4px 10px;
    border-radius: 15px;
    font-size: 11px;
    font-weight: 600;
  }
  .resolution-fast {
    background-color: #dcfce7;
    color: #166534;
  }
  .resolution-normal {
    background-color: #fef3c7;
    color: #92400e;
  }
  .resolution-slow {
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
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
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
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
  }
  
  .dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white !important;
    box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
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
  
  /* Performance indicator */
  .performance-indicator {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    margin-right: 6px;
  }
  .performance-good {
    background-color: #10b981;
  }
  .performance-average {
    background-color: #f59e0b;
  }
  .performance-poor {
    background-color: #ef4444;
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
  <title>Solved Tickets - Helpdesk</title>
  
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
        <h1 class="h3 mb-1 text-gray-800">Solved Tickets</h1>
        <p class="mb-0 text-muted">Successfully resolved support requests</p>
      </div>
      <div class="d-flex gap-2">
        <?php include './includes/create-ticket-button.php'; ?>
        <button class="btn btn-outline-primary" onclick="window.print()">
          <i class="fas fa-download me-2"></i>Export Report
        </button>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
      <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stat-card">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                <i class="fas fa-check-double"></i>
              </div>
              <div>
                <div class="text-muted small">Total Solved</div>
                <div class="h4 mb-0"><?php echo $totalSolved; ?></div>
                <small class="text-success">
                  <i class="fas fa-arrow-up"></i> All time
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
                <i class="fas fa-calendar-check"></i>
              </div>
              <div>
                <div class="text-muted small">Today's Resolved</div>
                <div class="h4 mb-0"><?php echo $todaySolved; ?></div>
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
              <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                <i class="fas fa-chart-line"></i>
              </div>
              <div>
                <div class="text-muted small">This Month</div>
                <div class="h4 mb-0"><?php echo $monthSolved; ?></div>
                <small class="text-info">
                  Last 30 days
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
                <i class="fas fa-hourglass-half"></i>
              </div>
              <div>
                <div class="text-muted small">Avg Resolution</div>
                <div class="h4 mb-0"><?php echo $avgResolutionTime; ?>h</div>
                <small class="text-muted">
                  Average time
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
                     id="searchInput" placeholder="Search solved tickets...">
            </div>
          </div>
          <div class="col-md-8 text-end">
            <div class="btn-group" role="group">
              <button type="button" class="btn btn-outline-primary filter-btn active" data-filter="all">
                <i class="fas fa-list"></i> All Solved
              </button>
              <button type="button" class="btn btn-outline-success filter-btn" data-filter="today">
                <i class="fas fa-calendar-day"></i> Today
              </button>
              <button type="button" class="btn btn-outline-info filter-btn" data-filter="week">
                <i class="fas fa-calendar-week"></i> This Week
              </button>
              <button type="button" class="btn btn-outline-warning filter-btn" data-filter="month">
                <i class="fas fa-calendar-alt"></i> This Month
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
            <i class="fas fa-check-circle me-2 text-primary"></i>Resolved Tickets History
          </h5>
          <span class="badge bg-primary bg-opacity-10 text-primary">
            <i class="fas fa-trophy me-1"></i>
            <?php echo $totalSolved; ?> Tickets Solved
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
                <th class="border-0">Solved By</th>
                <th class="border-0">Resolution Time</th>
                <th class="border-0">Solved Date</th>
                <th class="border-0 text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($allTicket as $ticket):?>
              <tr data-created="<?php echo $ticket->created_at; ?>" 
                  data-solved="<?php echo $ticket->solved_at ?? $ticket->updated_at; ?>">
                <td class="fw-semibold">
                  <span class="performance-indicator performance-good"></span>
                  #<?php echo str_pad($ticket->id, 5, '0', STR_PAD_LEFT); ?>
                </td>
                <td>
                  <a href="./ticket-details.php?id=<?php echo $ticket->id?>" 
                     class="text-decoration-none text-dark fw-medium">
                    <i class="fas fa-check-square me-2 text-success"></i>
                    <?php echo htmlspecialchars($ticket->title)?>
                  </a>
                </td>
                <td>
                  <div class="d-flex align-items-center">
                    <div class="avatar-sm rounded-circle bg-primary bg-opacity-10 text-primary me-2 d-flex align-items-center justify-content-center" 
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
                      <i class="fas fa-user-check me-1"></i>
                      <?php echo htmlspecialchars($agent->name) ?>
                    </span>
                  <?php else: ?>
                    <span class="text-muted">—</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php
                    $created = strtotime($ticket->created_at);
                    $solved = strtotime($ticket->solved_at ?? $ticket->updated_at);
                    $hours = round(($solved - $created) / 3600, 1);
                    
                    if($hours < 24) {
                      $badgeClass = 'resolution-fast';
                      $timeText = $hours . ' hours';
                    } elseif($hours < 72) {
                      $badgeClass = 'resolution-normal';
                      $timeText = round($hours/24, 1) . ' days';
                    } else {
                      $badgeClass = 'resolution-slow';
                      $timeText = round($hours/24) . ' days';
                    }
                  ?>
                  <span class="resolution-badge <?php echo $badgeClass; ?>">
                    <i class="fas fa-clock me-1"></i><?php echo $timeText; ?>
                  </span>
                </td>
                <td>
                  <?php 
                    $solvedDate = new DateTime($ticket->solved_at ?? $ticket->updated_at);
                    echo '<small class="text-muted">';
                    echo '<i class="fas fa-calendar-check me-1"></i>';
                    echo $solvedDate->format('M d, Y');
                    echo '</small>';
                  ?>
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
                          <i class="fas fa-file-alt me-2 text-primary"></i>View Solution
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item" href="#">
                          <i class="fas fa-star me-2 text-warning"></i>Rate Solution
                        </a>
                      </li>
                      <li><hr class="dropdown-divider"></li>
                      <li>
                        <a class="dropdown-item" href="#">
                          <i class="fas fa-redo me-2 text-danger"></i>Reopen Ticket
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
                "info": "Showing _START_ to _END_ of _TOTAL_ solved tickets",
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
    
    // Date range filter functionality
    $('.filter-btn').on('click', function() {
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');
        
        var filterValue = $(this).data('filter');
        var today = new Date();
        
        table.rows().every(function() {
            var node = $(this.node());
            var solvedDate = new Date(node.data('solved'));
            var show = false;
            
            switch(filterValue) {
                case 'all':
                    show = true;
                    break;
                case 'today':
                    show = solvedDate.toDateString() === today.toDateString();
                    break;
                case 'week':
                    var weekAgo = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
                    show = solvedDate >= weekAgo;
                    break;
                case 'month':
                    var monthAgo = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000);
                    show = solvedDate >= monthAgo;
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
    
    // Add performance indicators based on resolution time
    $('tbody tr').each(function() {
        var created = new Date($(this).data('created'));
        var solved = new Date($(this).data('solved'));
        var hours = (solved - created) / (1000 * 60 * 60);
        
        var indicator = $(this).find('.performance-indicator');
        if(hours < 24) {
            indicator.removeClass('performance-average performance-poor').addClass('performance-good');
        } else if(hours < 72) {
            indicator.removeClass('performance-good performance-poor').addClass('performance-average');
        } else {
            indicator.removeClass('performance-good performance-average').addClass('performance-poor');
        }
    });
    
    // Load create ticket modal functionality
    $.getScript('./includes/create-ticket-modal.js');
});
</script>

<!-- Include Create Ticket Modal -->
<?php include './includes/create-ticket-modal.php'; ?>

</body>
</html>