<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['logged-in']) || $_SESSION['logged-in'] == false) {
    header('Location: ./index.php');
    exit();
}
require_once './src/team.php';
require_once './src/ticket.php';
require_once './src/user.php';

$teams = Team::findAll();

// Calculate team performance metrics
$teamMetrics = [];
$totalTeams = count($teams);
$totalMembers = 0;
$activeTeams = 0;

foreach($teams as $team) {
    $memberCount = Team::getMemberCount($team->id);
    $totalMembers += $memberCount;
    
    // Get team tickets (simplified - in reality would need proper relation)
    $teamTickets = Ticket::findByTeam($team->id) ?? [];
    $activeTickets = count(array_filter($teamTickets, function($t) { return in_array($t->status, ['open', 'pending']); }));
    $solvedTickets = count(array_filter($teamTickets, function($t) { return $t->status == 'solved'; }));
    $totalTickets = count($teamTickets);
    
    // Calculate team performance score
    $performanceScore = $totalTickets > 0 ? round(($solvedTickets / $totalTickets) * 100) : 0;
    
    // Team workload calculation
    $workload = $memberCount > 0 ? round($activeTickets / $memberCount, 1) : 0;
    
    $teamMetrics[$team->id] = [
        'name' => $team->name,
        'memberCount' => $memberCount,
        'totalTickets' => $totalTickets,
        'activeTickets' => $activeTickets,
        'solvedTickets' => $solvedTickets,
        'performanceScore' => $performanceScore,
        'workload' => $workload,
        'created_at' => $team->created_at
    ];
    
    if($memberCount > 0) {
        $activeTeams++;
    }
}

// Overall statistics
$avgTeamSize = $totalTeams > 0 ? round($totalMembers / $totalTeams, 1) : 0;
$topPerformer = !empty($teamMetrics) ? array_reduce($teamMetrics, function($carry, $item) {
    return ($carry === null || $item['performanceScore'] > $carry['performanceScore']) ? $item : $carry;
}) : null;
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
  .team-card {
    border-radius: 10px;
    border: none;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: transform 0.2s, box-shadow 0.2s;
    height: 100%;
  }
  .team-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
  }
  .team-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px 10px 0 0;
    padding: 1rem;
  }
  .performance-bar {
    width: 100%;
    height: 8px;
    background-color: #e5e7eb;
    border-radius: 4px;
    overflow: hidden;
  }
  .performance-fill {
    height: 100%;
    border-radius: 4px;
    transition: width 1s ease-in-out;
  }
  .workload-indicator {
    display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    margin-right: 8px;
  }
  .workload-light {
    background-color: #10b981;
  }
  .workload-moderate {
    background-color: #f59e0b;
  }
  .workload-heavy {
    background-color: #ef4444;
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
  .team-badge {
    padding: 4px 8px;
    border-radius: 15px;
    font-size: 0.75rem;
    font-weight: 600;
  }
  .badge-small {
    background-color: #fef3c7;
    color: #92400e;
  }
  .badge-medium {
    background-color: #dbeafe;
    color: #1e40af;
  }
  .badge-large {
    background-color: #dcfce7;
    color: #166534;
  }
  .create-team-card {
    border: 2px dashed #d1d5db;
    background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
    border-radius: 10px;
    transition: all 0.2s;
    cursor: pointer;
    height: 100%;
    min-height: 200px;
  }
  .create-team-card:hover {
    border-color: #667eea;
    background: linear-gradient(135deg, #ede9fe 0%, #e0e7ff 100%);
    transform: translateY(-2px);
  }
  .team-actions {
    position: absolute;
    top: 1rem;
    right: 1rem;
  }
  .dropdown-menu {
    border: none;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    border-radius: 8px;
  }
  .dropdown-item:hover {
    background-color: #f3f4f6;
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
    .team-card {
      margin-bottom: 1rem;
    }
  }
</style>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Teams - Helpdesk</title>
  
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

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
        <h1 class="h3 mb-1 text-gray-800">Teams Management</h1>
        <p class="mb-0 text-muted">Organize and manage your support teams efficiently</p>
      </div>
      <div class="d-flex gap-2">
        <?php include './includes/create-ticket-button.php'; ?>
        <button class="btn btn-info">
          <i class="fas fa-chart-bar me-2"></i>Team Analytics
        </button>
        <a href="newteam.php" class="btn btn-outline-primary">
          <i class="fas fa-plus-circle me-2"></i>Create Team
        </a>
      </div>
    </div>

    <!-- Team Statistics Cards -->
    <div class="row mb-4">
      <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stat-card">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                <i class="fas fa-users"></i>
              </div>
              <div>
                <div class="text-muted small">Total Teams</div>
                <div class="h4 mb-0"><?php echo $totalTeams; ?></div>
                <small class="text-success">
                  <i class="fas fa-check-circle"></i> <?php echo $activeTeams; ?> active
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
                <i class="fas fa-user-friends"></i>
              </div>
              <div>
                <div class="text-muted small">Total Members</div>
                <div class="h4 mb-0"><?php echo $totalMembers; ?></div>
                <small class="text-muted">
                  Avg: <?php echo $avgTeamSize; ?> per team
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
                <i class="fas fa-trophy"></i>
              </div>
              <div>
                <div class="text-muted small">Top Performer</div>
                <div class="h6 mb-0"><?php echo $topPerformer ? htmlspecialchars($topPerformer['name']) : 'N/A'; ?></div>
                <small class="text-warning">
                  <?php echo $topPerformer ? $topPerformer['performanceScore'] . '% score' : 'No data'; ?>
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
                <i class="fas fa-tasks"></i>
              </div>
              <div>
                <div class="text-muted small">Avg Workload</div>
                <div class="h4 mb-0">
                  <?php 
                    $avgWorkload = !empty($teamMetrics) ? round(array_sum(array_column($teamMetrics, 'workload')) / count($teamMetrics), 1) : 0;
                    echo $avgWorkload;
                  ?>
                </div>
                <small class="text-muted">
                  tickets per member
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
                     id="searchInput" placeholder="Search teams...">
            </div>
          </div>
          <div class="col-md-8 text-end">
            <div class="btn-group" role="group">
              <button type="button" class="btn btn-outline-primary filter-btn active" data-filter="all">
                <i class="fas fa-users"></i> All Teams
              </button>
              <button type="button" class="btn btn-outline-success filter-btn" data-filter="active">
                <i class="fas fa-check-circle"></i> Active
              </button>
              <button type="button" class="btn btn-outline-warning filter-btn" data-filter="small">
                <i class="fas fa-user"></i> Small (1-3)
              </button>
              <button type="button" class="btn btn-outline-info filter-btn" data-filter="medium">
                <i class="fas fa-users"></i> Medium (4-8)
              </button>
              <button type="button" class="btn btn-outline-secondary filter-btn" data-filter="large">
                <i class="fas fa-user-friends"></i> Large (9+)
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Teams Grid -->
    <div class="row" id="teamsContainer">
      <!-- Create New Team Card -->
      <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
        <div class="create-team-card d-flex flex-column align-items-center justify-content-center" onclick="window.location.href='newteam.php'">
          <div class="text-center">
            <i class="fas fa-plus-circle" style="font-size: 2.5rem; color: #9ca3af; margin-bottom: 0.75rem;"></i>
            <h6 class="text-muted mb-1">Create New Team</h6>
            <p class="text-muted" style="font-size: 0.75rem;">Build a new support team</p>
          </div>
        </div>
      </div>

      <!-- Team Cards -->
      <?php foreach($teamMetrics as $teamId => $metrics): ?>
      <div class="col-xl-3 col-lg-4 col-md-6 mb-3 team-item" 
           data-team-size="<?php echo $metrics['memberCount']; ?>"
           data-team-name="<?php echo strtolower($metrics['name']); ?>">
        <div class="card team-card position-relative">
          <!-- Team Actions Dropdown -->
          <div class="team-actions">
            <div class="dropdown">
              <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                <i class="fas fa-ellipsis-v"></i>
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li>
                  <a class="dropdown-item" href="add-team-member.php?team-id=<?php echo $teamId; ?>">
                    <i class="fas fa-user-plus me-2 text-success"></i>Add Member
                  </a>
                </li>
                <li>
                  <a class="dropdown-item" href="editteam.php?id=<?php echo $teamId; ?>">
                    <i class="fas fa-edit me-2 text-primary"></i>Edit Team
                  </a>
                </li>
                <li>
                  <a class="dropdown-item" href="#">
                    <i class="fas fa-chart-line me-2 text-info"></i>View Analytics
                  </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <a class="dropdown-item text-danger" href="deleteteam.php?id=<?php echo $teamId; ?>" onclick="return confirm('Are you sure you want to delete this team?');">
                    <i class="fas fa-trash me-2"></i>Delete Team
                  </a>
                </li>
              </ul>
            </div>
          </div>

          <!-- Team Header -->
          <div class="team-header">
            <div class="d-flex align-items-center">
              <div class="me-2">
                <div class="bg-white bg-opacity-20 rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                  <i class="fas fa-users text-white" style="font-size: 16px;"></i>
                </div>
              </div>
              <div>
                <h6 class="mb-0 text-white fw-bold"><?php echo htmlspecialchars($metrics['name']); ?></h6>
                <div class="d-flex align-items-center mt-1">
                  <?php
                    $sizeClass = '';
                    if($metrics['memberCount'] <= 3) {
                      $sizeClass = 'badge-small';
                    } elseif($metrics['memberCount'] <= 8) {
                      $sizeClass = 'badge-medium';
                    } else {
                      $sizeClass = 'badge-large';
                    }
                  ?>
                  <span class="team-badge <?php echo $sizeClass; ?>" style="font-size: 0.65rem;">
                    <?php echo $metrics['memberCount']; ?> member<?php echo $metrics['memberCount'] != 1 ? 's' : ''; ?>
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Team Stats -->
          <div class="card-body" style="padding: 0.75rem;">
            <div class="row text-center mb-2">
              <div class="col-4">
                <div class="h6 mb-1 text-primary"><?php echo $metrics['totalTickets']; ?></div>
                <div style="font-size: 0.7rem;" class="text-muted">Total</div>
              </div>
              <div class="col-4">
                <div class="h6 mb-1 text-warning"><?php echo $metrics['activeTickets']; ?></div>
                <div style="font-size: 0.7rem;" class="text-muted">Active</div>
              </div>
              <div class="col-4">
                <div class="h6 mb-1 text-success"><?php echo $metrics['solvedTickets']; ?></div>
                <div style="font-size: 0.7rem;" class="text-muted">Solved</div>
              </div>
            </div>

            <!-- Workload Indicator -->
            <div class="d-flex justify-content-between align-items-center">
              <div class="d-flex align-items-center">
                <?php
                  $workloadClass = '';
                  if($metrics['workload'] <= 3) {
                    $workloadClass = 'workload-light';
                    $workloadText = 'Light';
                  } elseif($metrics['workload'] <= 6) {
                    $workloadClass = 'workload-moderate';
                    $workloadText = 'Moderate';
                  } else {
                    $workloadClass = 'workload-heavy';
                    $workloadText = 'Heavy';
                  }
                ?>
                <span class="workload-indicator <?php echo $workloadClass; ?>" style="width: 8px; height: 8px;"></span>
                <span style="font-size: 0.7rem;" class="text-muted"><?php echo $workloadText; ?></span>
              </div>
              <span style="font-size: 0.7rem;" class="fw-medium"><?php echo $metrics['workload']; ?>/member</span>
            </div>

            <!-- Team Created Date -->
            <div class="mt-2 pt-2 border-top">
              <small class="text-muted" style="font-size: 0.65rem;">
                <i class="fas fa-calendar me-1"></i>
                <?php echo date('M d, Y', strtotime($metrics['created_at'])); ?>
              </small>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Footer Section -->
    <div class="footer-section">
      <div class="footer-content text-center">
        © <?php echo date('Y'); ?> ICT Helpdesk. All rights reserved.
      </div>
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
<script src="js/sb-admin.min.js"></script>

<!-- Custom JavaScript -->
<script>
$(document).ready(function() {
    // Search functionality
    $('#searchInput').on('keyup', function() {
        var searchTerm = $(this).val().toLowerCase();
        $('.team-item').each(function() {
            var teamName = $(this).data('team-name');
            if(teamName.includes(searchTerm) || searchTerm === '') {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    // Filter functionality
    $('.filter-btn').on('click', function() {
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');
        
        var filterValue = $(this).data('filter');
        
        $('.team-item').each(function() {
            var teamSize = parseInt($(this).data('team-size'));
            var show = false;
            
            switch(filterValue) {
                case 'all':
                    show = true;
                    break;
                case 'active':
                    show = teamSize > 0;
                    break;
                case 'small':
                    show = teamSize >= 1 && teamSize <= 3;
                    break;
                case 'medium':
                    show = teamSize >= 4 && teamSize <= 8;
                    break;
                case 'large':
                    show = teamSize >= 9;
                    break;
            }
            
            if(show) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    // Animation on load
    $('.stat-card').each(function(index) {
        $(this).css('opacity', '0').delay(index * 100).animate({
            opacity: 1
        }, 500);
    });
    
    // Animate team cards
    $('.team-card').each(function(index) {
        $(this).css('opacity', '0').delay((index + 4) * 150).animate({
            opacity: 1
        }, 600);
    });
    
    // Animate performance bars on load
    setTimeout(function() {
        $('.performance-fill').each(function() {
            var width = $(this).css('width');
            $(this).css('width', '0%').animate({
                width: width
            }, 1500);
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