<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once './src/team.php';
require_once './src/ticket.php';
require_once './src/user.php';

session_start();
if (!isset($_SESSION['logged-in']) || $_SESSION['logged-in'] == false) {
    header('Location: ./index.php');
    exit();
}

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
  :root {
    --treasury-navy: #1e3a5f;
    --treasury-gold: #c9a96e;
    --treasury-green: #2d5a3d;
    --treasury-blue: #4a90a4;
    --treasury-amber: #b8860b;
    --treasury-burgundy: #722f37;
    --treasury-dark: #2c3e50;
    --treasury-light: #f8f9fc;
    --treasury-brown: #8B4513;
    --treasury-tan: #D2B48C;
    --kenya-red: #922529;
    --kenya-green: #008C51;
  }

  .stat-card {
    border-radius: 12px;
    border: none;
    box-shadow: 0 2px 8px rgba(30, 58, 95, 0.08);
    transition: transform 0.2s, box-shadow 0.2s;
  }
  .stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(30, 58, 95, 0.15);
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
    box-shadow: 0 2px 8px rgba(30, 58, 95, 0.08);
    transition: transform 0.2s, box-shadow 0.2s;
    height: 100%;
  }
  .team-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(30, 58, 95, 0.15);
  }
  .team-header {
    background: linear-gradient(135deg, var(--treasury-brown) 0%, var(--treasury-tan) 100%);
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
    background-color: var(--treasury-green);
  }
  .workload-moderate {
    background-color: var(--treasury-amber);
  }
  .workload-heavy {
    background-color: var(--treasury-burgundy);
  }
  .search-box {
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    padding: 8px 16px;
  }
  .search-box:focus {
    outline: none;
    border-color: var(--treasury-tan);
    box-shadow: 0 0 0 3px rgba(210, 180, 140, 0.1);
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
  
  .btn-outline-primary {
    color: var(--treasury-brown);
    border-color: var(--treasury-brown);
  }
  
  .btn-outline-primary:hover, .btn-outline-primary.active {
    background-color: var(--treasury-brown);
    border-color: var(--treasury-brown);
    color: white;
  }
  
  .btn-outline-success {
    color: var(--treasury-green);
    border-color: var(--treasury-green);
  }
  
  .btn-outline-success:hover {
    background-color: var(--treasury-green);
    border-color: var(--treasury-green);
  }
  
  .btn-outline-warning {
    color: var(--treasury-amber);
    border-color: var(--treasury-amber);
  }
  
  .btn-outline-warning:hover {
    background-color: var(--treasury-amber);
    border-color: var(--treasury-amber);
  }
  
  .btn-outline-info {
    color: var(--treasury-blue);
    border-color: var(--treasury-blue);
  }
  
  .btn-outline-info:hover {
    background-color: var(--treasury-blue);
    border-color: var(--treasury-blue);
  }
  
  .btn-outline-secondary {
    color: var(--treasury-tan);
    border-color: var(--treasury-tan);
  }
  
  .btn-outline-secondary:hover {
    background-color: var(--treasury-tan);
    border-color: var(--treasury-tan);
    color: var(--treasury-brown);
  }
  .team-badge {
    padding: 4px 8px;
    border-radius: 15px;
    font-size: 0.75rem;
    font-weight: 600;
  }
  .badge-small {
    background-color: #fff8dc;
    color: var(--treasury-amber);
  }
  .badge-medium {
    background-color: rgba(74, 144, 164, 0.1);
    color: var(--treasury-blue);
  }
  .badge-large {
    background-color: rgba(45, 90, 61, 0.1);
    color: var(--treasury-green);
  }
  .create-team-card {
    border: 2px dashed var(--treasury-tan);
    background: linear-gradient(135deg, var(--treasury-light) 0%, #f5f7fa 100%);
    border-radius: 10px;
    transition: all 0.2s;
    cursor: pointer;
    height: 100%;
    min-height: 200px;
  }
  .create-team-card:hover {
    border-color: var(--treasury-brown);
    background: linear-gradient(135deg, rgba(210, 180, 140, 0.1) 0%, rgba(139, 69, 19, 0.05) 100%);
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
    background: var(--treasury-light);
    border-radius: 8px;
    padding: 1rem;
    margin-top: 2rem;
    border: 1px solid rgba(201, 169, 110, 0.3);
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
    :root { --bg-soft: var(--treasury-light); }
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
            <i class="fas fa-plus-circle" style="font-size: 2.5rem; color: var(--treasury-tan); margin-bottom: 0.75rem;"></i>
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
    
    // Team Analytics button functionality
    $('.btn-info').on('click', function(e) {
        if ($(this).text().trim().includes('Team Analytics')) {
            e.preventDefault();
            
            // Create analytics modal content
            const analyticsData = {
                totalTeams: <?= $totalTeams ?>,
                totalMembers: <?= $totalMembers ?>,
                avgTeamSize: <?= $avgTeamSize ?>,
                activeTeams: <?= $activeTeams ?>
            };
            
            const modalContent = `
                <div class="modal fade" id="teamAnalyticsModal" tabindex="-1" role="dialog">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <i class="fas fa-chart-bar me-2"></i>Team Analytics Dashboard
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row mb-4">
                                    <div class="col-md-3">
                                        <div class="text-center p-3 bg-primary bg-opacity-10 rounded">
                                            <i class="fas fa-users fa-2x text-primary mb-2"></i>
                                            <h4>${analyticsData.totalTeams}</h4>
                                            <small class="text-muted">Total Teams</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center p-3 bg-success bg-opacity-10 rounded">
                                            <i class="fas fa-user-friends fa-2x text-success mb-2"></i>
                                            <h4>${analyticsData.totalMembers}</h4>
                                            <small class="text-muted">Total Members</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center p-3 bg-info bg-opacity-10 rounded">
                                            <i class="fas fa-chart-line fa-2x text-info mb-2"></i>
                                            <h4>${analyticsData.avgTeamSize}</h4>
                                            <small class="text-muted">Avg Team Size</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center p-3 bg-warning bg-opacity-10 rounded">
                                            <i class="fas fa-check-circle fa-2x text-warning mb-2"></i>
                                            <h4>${analyticsData.activeTeams}</h4>
                                            <small class="text-muted">Active Teams</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-12">
                                        <h6>Team Performance Overview</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Team</th>
                                                        <th>Members</th>
                                                        <th>Status</th>
                                                        <th>Performance</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="analyticsTableBody">
                                                    <!-- Team data will be populated here -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary">
                                    <i class="fas fa-download me-2"></i>Export Report
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Remove existing modal and add new one
            $('#teamAnalyticsModal').remove();
            $('body').append(modalContent);
            
            // Populate analytics table
            $('.team-item').each(function() {
                const teamName = $(this).find('.team-card h6').text();
                const memberCount = $(this).data('team-size');
                const status = memberCount > 0 ? 'Active' : 'Inactive';
                const performance = Math.floor(Math.random() * 100); // Random performance for demo
                
                $('#analyticsTableBody').append(`
                    <tr>
                        <td>${teamName}</td>
                        <td><span class="badge bg-light text-dark">${memberCount}</span></td>
                        <td><span class="badge bg-${status === 'Active' ? 'success' : 'secondary'}">${status}</span></td>
                        <td>
                            <div class="progress" style="height: 15px;">
                                <div class="progress-bar" style="width: ${performance}%">${performance}%</div>
                            </div>
                        </td>
                    </tr>
                `);
            });
            
            // Show modal
            $('#teamAnalyticsModal').modal('show');
        }
    });
    
    // Team dropdown actions functionality
    $(document).on('click', '.dropdown-item', function(e) {
        const href = $(this).attr('href');
        const text = $(this).text().trim();
        
        if (href === '#') {
            e.preventDefault();
            
            if (text.includes('View Analytics')) {
                // Individual team analytics
                const teamCard = $(this).closest('.team-item');
                const teamName = teamCard.find('.team-card h6').text();
                const memberCount = teamCard.data('team-size');
                
                alert(`Analytics for ${teamName}:\n\nMembers: ${memberCount}\nStatus: ${memberCount > 0 ? 'Active' : 'Inactive'}\nPerformance: ${Math.floor(Math.random() * 100)}%\n\n(Detailed analytics would show here in full implementation)`);
            }
        }
    });
    
    // Enhanced team card interactions
    $('.team-card').on('mouseenter', function() {
        $(this).css('transform', 'translateY(-2px)');
        $(this).css('box-shadow', '0 4px 15px rgba(0,0,0,0.1)');
    }).on('mouseleave', function() {
        $(this).css('transform', 'translateY(0)');
        $(this).css('box-shadow', '0 1px 3px rgba(0,0,0,0.1)');
    });
    
    // Load create ticket modal functionality
    $.getScript('./includes/create-ticket-modal.js');
});
</script>

<!-- Include Create Ticket Modal -->
<?php include './includes/create-ticket-modal.php'; ?>

</body>
</html>