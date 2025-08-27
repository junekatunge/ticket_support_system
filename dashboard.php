<?php
session_start();
if (!isset($_SESSION['logged-in']) || $_SESSION['logged-in'] == false) {
    header('Location: ./index.php');
    exit();
}
$user = $_SESSION['user'];
require_once './src/Database.php';
require_once './src/ticket.php';
require_once './src/requester.php';
require_once './src/team.php';

$db = Database::getInstance();
$ticket = new Ticket();
$allTicket = $ticket::findAll();
$requester = new Requester();
$team = new Team();

// Calculate statistics
$totalTickets = count($allTicket);
$openTickets = count(array_filter($allTicket, function($t) { return $t->status == 'open'; }));
$pendingTickets = count(array_filter($allTicket, function($t) { return $t->status == 'pending'; }));
$solvedTickets = count(array_filter($allTicket, function($t) { return $t->status == 'solved'; }));
$closedTickets = count(array_filter($allTicket, function($t) { return $t->status == 'closed'; }));

if (isset($_GET['del'])) {
    $id = $_GET['del'];
    try {
        $ticket->delete($id);
        echo '<script>alert("Ticket deleted successfully");window.location = "./dashboard.php"</script>';
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Dashboard - Helpdesk</title>
  
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
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
  .status-pending {
    background-color: #fef3c7;
    color: #92400e;
  }
  .status-solved {
    background-color: #dbeafe;
    color: #1e40af;
  }
  .status-closed {
    background-color: #f3f4f6;
    color: #374151;
  }
  .search-box {
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    padding: 8px 16px;
  }
  .search-box:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
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
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
  }
  
  .dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white !important;
    box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
  }
  
  .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }
  
  .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
    background: #fff;
    color: #6b7280;
    transform: none;
  }
  
  .dataTables_wrapper .dataTables_info {
    color: #6b7280;
    font-size: 0.875rem;
    font-weight: 500;
    padding-top: 0.5rem;
  }
  
  .dataTables_wrapper .dataTables_length {
    margin-bottom: 1rem;
  }
  
  .dataTables_wrapper .dataTables_length select {
    border-radius: 6px;
    border: 1px solid #e5e7eb;
    padding: 4px 8px;
    margin: 0 8px;
    display: inline-block;
  }
  
  .dataTables_wrapper .row {
    margin-top: 1rem;
  }
  
  .dataTables_wrapper .dataTables_paginate {
    margin-top: 1rem;
    display: flex;
    justify-content: center;
  }
  
  /* Custom pagination layout */
  .pagination-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 1.5rem;
    padding: 1rem 2rem;
    background: #f9fafb;
    border-radius: 12px;
    flex-wrap: nowrap;
  }
  
  .pagination-wrapper .dataTables_length {
    margin: 0;
    display: flex;
    align-items: center;
    white-space: nowrap;
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
    flex-shrink: 0;
  }
  
  .pagination-wrapper .dataTables_paginate {
    margin: 0;
    display: flex;
    align-items: center;
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
    .filter-btn {
      margin-bottom: 0.5rem;
    }
    .stat-card {
      margin-bottom: 1rem;
    }
    .table-responsive {
      font-size: 0.875rem;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
      padding: 6px 10px;
      font-size: 0.875rem;
    }
    .pagination-wrapper {
      flex-direction: column;
      gap: 1rem;
    }
    .pagination-wrapper .dataTables_length,
    .pagination-wrapper .dataTables_info {
      margin: 0.5rem 0;
    }
  }
</style>

</head>
<body>
<?php include 'navbar.php'; ?>

<div class="app-shell">
  <?php include 'sidebar.php'; ?>
  
  <section class="content content-with-navbar">

  <div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row mb-4 mt-4">
      <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stat-card">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                <i class="fas fa-ticket"></i>
              </div>
              <div>
                <div class="text-muted small">Total Tickets</div>
                <div class="h4 mb-0"><?php echo $totalTickets; ?></div>
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
                <i class="fas fa-folder-open"></i>
              </div>
              <div>
                <div class="text-muted small">Open Tickets</div>
                <div class="h4 mb-0"><?php echo $openTickets; ?></div>
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
                <i class="fas fa-clock"></i>
              </div>
              <div>
                <div class="text-muted small">Pending</div>
                <div class="h4 mb-0"><?php echo $pendingTickets; ?></div>
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
                <i class="fas fa-check-circle"></i>
              </div>
              <div>
                <div class="text-muted small">Solved</div>
                <div class="h4 mb-0"><?php echo $solvedTickets; ?></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create New Ticket Button -->
    <div class="text-end mb-2" style="margin-top: -2rem;">
      <?php include './includes/create-ticket-button.php'; ?>
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
                     id="searchInput" placeholder="Search tickets...">
            </div>
          </div>
          <div class="col-md-8 text-end">
            <div class="btn-group" role="group">
              <button type="button" class="btn btn-outline-secondary filter-btn" data-filter="all">All</button>
              <button type="button" class="btn btn-outline-success filter-btn" data-filter="open">Open</button>
              <button type="button" class="btn btn-outline-warning filter-btn" data-filter="pending">Pending</button>
              <button type="button" class="btn btn-outline-info filter-btn" data-filter="solved">Solved</button>
              <button type="button" class="btn btn-outline-secondary filter-btn" data-filter="closed">Closed</button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Tickets Table -->
    <div class="card ticket-table">
      <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0 fw-bold">Recent Tickets</h5>
      </div>
      <div class="card-body pt-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle" id="dataTable">
            <thead>
              <tr>
                <th class="border-0">Ticket ID</th>
                <th class="border-0">Subject</th>
                <th class="border-0">Status</th>
                <th class="border-0">Requester</th>
                <th class="border-0">Team</th>
                <th class="border-0">Priority</th>
                <th class="border-0">Created</th>
                <th class="border-0 text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($allTicket as $ticket):?>
              <tr data-status="<?php echo $ticket->status ?? 'open'; ?>">
                <td class="fw-semibold">#<?php echo str_pad($ticket->id, 5, '0', STR_PAD_LEFT); ?></td>
                <td>
                  <a href="./ticket-details.php?id=<?php echo $ticket->id?>" 
                     class="text-decoration-none text-dark fw-medium">
                    <?php echo htmlspecialchars($ticket->title)?>
                  </a>
                </td>
                <td>
                  <span class="status-badge status-<?php echo $ticket->status ?? 'open'; ?>">
                    <?php echo ucfirst($ticket->status ?? 'open'); ?>
                  </span>
                </td>
                <td>
                  <div class="d-flex align-items-center">
                    <div class="avatar-sm rounded-circle bg-secondary bg-opacity-10 text-secondary me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                      <i class="fas fa-user" style="font-size: 14px;"></i>
                    </div>
                    <span><?php echo htmlspecialchars($requester::find($ticket->requester)->name)?></span>
                  </div>
                </td>
                <td>
                  <span class="badge bg-primary bg-opacity-10 text-primary">
                    <?php echo htmlspecialchars($team::find($ticket->team)->name);?>
                  </span>
                </td>
                <td>
                  <?php 
                    $priority = $ticket->priority ?? 'medium';
                    $priorityClass = $priority == 'high' ? 'danger' : ($priority == 'low' ? 'success' : 'warning');
                  ?>
                  <span class="badge bg-<?php echo $priorityClass; ?> bg-opacity-10 text-<?php echo $priorityClass; ?>">
                    <i class="fas fa-flag me-1"></i><?php echo ucfirst($priority); ?>
                  </span>
                </td>
                <?php $date = new DateTime($ticket->created_at)?>
                <td data-sort="<?php echo $date->getTimestamp()?>">
                  <small class="text-muted">
                    <i class="fas fa-calendar me-1"></i>
                    <?php echo $date->format('M d, Y')?>
                  </small>
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
                        <a class="dropdown-item" href="./ticket-details.php?id=<?php echo $ticket->id?>">
                          <i class="fas fa-edit me-2 text-warning"></i>Edit
                        </a>
                      </li>
                      <li><hr class="dropdown-divider"></li>
                      <li>
                        <a class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete this ticket?')"
                          href="?del=<?php echo $ticket->id; ?>">
                          <i class="fas fa-trash me-2"></i>Delete
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
  <!-- /.container-fluid -->

</div>
  </div>
  </section>
  
</div>

<!-- Create Ticket Modal -->
<style>
  .modal-content {
    border: none;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
  }
  
  .icon-box {
    width: 42px;
    height: 42px;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #667eea;
    font-size: 18px;
    flex-shrink: 0;
  }
  
  .priority-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    margin-left: 8px;
  }
  
  .section-divider {
    border: none;
    height: 1px;
    background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
    margin: 1.5rem 0;
  }
</style>

<?php include './includes/create-ticket-modal.php'; ?>

<script>
function updatePriorityBadge(select) {
  // Optional: Add visual feedback when priority is selected
  if(select.value) {
    select.style.borderColor = '#667eea';
    select.style.background = 'white';
  }
}

</script>

<!-- Logout Modal-->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
  aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
      <div class="modal-footer">
        <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
        <a class="btn btn-primary" href="./index.php">Logout</a>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap core JavaScript-->
<script src="vendor/jquery/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Core plugin JavaScript-->
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>

<!-- Page level plugin JavaScript-->
<script src="vendor/chart.js/Chart.min.js"></script>
<script src="vendor/datatables/jquery.dataTables.js"></script>
<script src="vendor/datatables/dataTables.bootstrap4.js"></script>

<!-- Custom scripts for all pages-->
<script src="js/sb-admin.min.js"></script>

<!-- Note: Removed datatables-demo.js to prevent double initialization -->
<!-- Custom JavaScript for search and filter -->
<script>
$(document).ready(function() {
    // Check if DataTable is already initialized
    if ($.fn.DataTable.isDataTable('#dataTable')) {
        var table = $('#dataTable').DataTable();
    } else {
        // Initialize DataTable with custom options
        var table = $('#dataTable').DataTable({
            "pageLength": 10,
            "language": {
                "search": "",
                "searchPlaceholder": "Search tickets...",
                "lengthMenu": "Show _MENU_ entries",
                "info": "Showing _START_ to _END_ of _TOTAL_ tickets",
                "paginate": {
                    "first": "First",
                    "last": "Last",
                    "next": " Next →",
                    "previous": "← Previous "
                }
            },
            "dom": 'rt<"pagination-wrapper"lip>', // Custom layout with pagination wrapper
            "order": [[6, 'desc']], // Sort by created date descending (timestamp)
            "columnDefs": [
                {
                    "targets": 6, // Created date column
                    "type": "num", // Treat as numeric for proper timestamp sorting
                    "orderData": [6] // Use data-sort attribute for sorting
                }
            ],
            "responsive": true,
            "lengthMenu": [[5, 10, 25, 50], [5, 10, 25, 50]]
        });
    }
    
    // Custom search functionality
    $('#searchInput').on('keyup', function() {
        table.search(this.value).draw();
    });
    
    // Filter buttons functionality
    $('.filter-btn').on('click', function() {
        $('.filter-btn').removeClass('active btn-primary').addClass('btn-outline-secondary');
        $(this).removeClass('btn-outline-secondary').addClass('active btn-primary');
        
        var filterValue = $(this).data('filter');
        
        if (filterValue === 'all') {
            table.column(2).search('').draw();
        } else {
            table.column(2).search('^' + filterValue + '$', true, false).draw();
        }
    });
    
    // Set 'All' filter as active by default
    $('.filter-btn[data-filter="all"]').removeClass('btn-outline-secondary').addClass('active btn-primary');
    
    // Add animation on page load
    $('.stat-card').each(function(index) {
        $(this).css('opacity', '0').delay(index * 100).animate({
            opacity: 1
        }, 500);
    });
    
    // Tooltips initialization
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Load create ticket modal functionality
    $.getScript('./includes/create-ticket-modal.js');
});

</script>

</body>

</html>