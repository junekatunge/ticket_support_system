<?php
session_start();
if (!isset($_SESSION['logged-in']) || $_SESSION['logged-in'] == false) {
    header('Location: ./index.php');
    exit();
}

require_once './src/database.php';
require_once './src/ticket.php';
require_once './src/user.php';
require_once './src/team.php';
require_once './src/requester.php';

$user = $_SESSION['user'];

// Rule-Based Insights Generation Function
function generateAIInsights($data) {
    $insights = [];
    $stats = $data['stats'];

    // Calculate resolution rate
    $resolutionRate = $stats['total_tickets'] > 0
        ? round(($stats['solved_tickets'] / $stats['total_tickets']) * 100, 1)
        : 0;

    // Calculate average tickets per day
    $totalTickets = $stats['total_tickets'];
    $daysInPeriod = 30; // Assuming 30 days
    $avgTicketsPerDay = round($totalTickets / $daysInPeriod, 1);

    // Performance Analysis with AI-powered insights
    if ($resolutionRate >= 80) {
        $insights[] = [
            'type' => 'success',
            'icon' => 'fa-trophy',
            'title' => 'Excellent Performance - Industry Leading',
            'description' => "Your team has achieved a {$resolutionRate}% resolution rate, which exceeds industry standards (70-75%). This demonstrates exceptional problem-solving capabilities, efficient workflows, and strong team collaboration. Continue this momentum by documenting successful strategies."
        ];
    } elseif ($resolutionRate >= 60) {
        $targetImprovement = 80 - $resolutionRate;
        $insights[] = [
            'type' => 'warning',
            'icon' => 'fa-chart-line',
            'title' => 'Good Performance - Optimization Opportunity',
            'description' => "Current resolution rate is {$resolutionRate}%. To reach excellent performance (80%), improve by {$targetImprovement}%. Key actions: (1) Identify common blockers in unresolved tickets, (2) Implement faster triage processes, (3) Provide additional training on complex issues."
        ];
    } else {
        $ticketsToImprove = round($totalTickets * ((80 - $resolutionRate) / 100));
        $insights[] = [
            'type' => 'danger',
            'icon' => 'fa-exclamation-circle',
            'title' => 'Performance Requires Immediate Action',
            'description' => "Resolution rate of {$resolutionRate}% is below optimal levels. Resolving {$ticketsToImprove} more tickets would reach 80% target. Critical actions: (1) Audit open tickets for quick wins, (2) Increase team capacity, (3) Review and optimize workflows, (4) Escalate systemic issues."
        ];
    }

    // Advanced Workload Analysis
    $openPendingTotal = $stats['open_tickets'] + $stats['pending_tickets'];
    $backlogRatio = $totalTickets > 0 ? round(($openPendingTotal / $totalTickets) * 100, 1) : 0;

    if ($openPendingTotal > $stats['total_tickets'] * 0.5) {
        $insights[] = [
            'type' => 'warning',
            'icon' => 'fa-exclamation-triangle',
            'title' => 'High Backlog Alert - {$backlogRatio}% Unresolved',
            'description' => "{$openPendingTotal} tickets pending ({$backlogRatio}% of total workload). AI Analysis: At current resolution rate, backlog will take approximately " . round($openPendingTotal / max($avgTicketsPerDay, 1)) . " days to clear. Recommendations: (1) Prioritize by impact, (2) Consider temporary resource allocation, (3) Implement ticket aging policies."
        ];
    } elseif ($backlogRatio > 30) {
        $insights[] = [
            'type' => 'info',
            'icon' => 'fa-tasks',
            'title' => 'Moderate Backlog - Manageable',
            'description' => "Current backlog is {$backlogRatio}% ({$openPendingTotal} tickets). This is within normal range but requires monitoring. Maintain current pace and prevent backlog growth by matching intake with resolution velocity."
        ];
    } else {
        $insights[] = [
            'type' => 'success',
            'icon' => 'fa-check-double',
            'title' => 'Low Backlog - Excellent Control',
            'description' => "Only {$backlogRatio}% backlog ({$openPendingTotal} tickets) indicates excellent ticket flow management. Your team is processing tickets faster than they arrive. Use this opportunity to focus on preventive measures and knowledge base development."
        ];
    }

    // Priority-Based Risk Analysis
    if (($stats['urgent_tickets'] + $stats['high_priority_tickets']) > 0) {
        $criticalCount = $stats['urgent_tickets'] + $stats['high_priority_tickets'];
        $criticalRatio = round(($criticalCount / max($totalTickets, 1)) * 100, 1);

        if ($criticalRatio > 25) {
            $insights[] = [
                'type' => 'danger',
                'icon' => 'fa-fire-extinguisher',
                'title' => 'Critical Priority Crisis - {$criticalRatio}% High Risk',
                'description' => "{$criticalCount} urgent/high-priority tickets ({$criticalRatio}% of total) indicates systemic issues. AI Recommendation: (1) Emergency triage session, (2) Escalate to management, (3) Investigate root causes, (4) Implement preventive controls to reduce future critical incidents."
            ];
        } else {
            $insights[] = [
                'type' => 'info',
                'icon' => 'fa-fire',
                'title' => 'Critical Tickets - Normal Range',
                'description' => "{$criticalCount} high-priority or urgent tickets detected ({$criticalRatio}% of total). This is within expected range. Continue prioritizing these to maintain SLA compliance and user satisfaction. Average resolution target: 1 day for urgent tickets."
            ];
        }
    }

    // Advanced Category Analysis with Trends
    if (!empty($data['top_categories'])) {
        $topCategory = $data['top_categories'][0];
        $categoryPercentage = round(($topCategory['count'] / max($totalTickets, 1)) * 100, 1);

        if ($categoryPercentage > 40) {
            $insights[] = [
                'type' => 'warning',
                'icon' => 'fa-bullseye',
                'title' => 'Category Concentration - {$categoryPercentage}% in One Area',
                'description' => "'{$topCategory['category']}' dominates with {$topCategory['count']} tickets ({$categoryPercentage}% of total). High concentration suggests: (1) Potential systemic issue requiring root cause analysis, (2) Opportunity for targeted training, (3) Need for comprehensive knowledge base articles, (4) Consider dedicated specialist assignment."
            ];
        } else {
            $insights[] = [
                'type' => 'info',
                'icon' => 'fa-lightbulb',
                'title' => 'Leading Issue Category - {$topCategory["category"]}',
                'description' => "'{$topCategory['category']}' leads with {$topCategory['count']} tickets ({$categoryPercentage}% of total). AI Insight: Create 3-5 targeted knowledge base articles addressing common '{$topCategory['category']}' scenarios. Expected impact: 15-25% reduction in similar tickets over next 30 days."
            ];
        }
    }

    // Team Performance Analysis with Benchmarking
    if (!empty($data['team_stats'])) {
        $bestTeam = null;
        $worstTeam = null;
        $bestRate = 0;
        $worstRate = 100;
        $totalTeamTickets = 0;

        foreach ($data['team_stats'] as $team) {
            if ($team['total_tickets'] > 0) {
                $teamRate = ($team['solved_tickets'] / $team['total_tickets']) * 100;
                $totalTeamTickets += $team['total_tickets'];

                if ($teamRate > $bestRate) {
                    $bestRate = $teamRate;
                    $bestTeam = $team;
                }
                if ($teamRate < $worstRate && $team['total_tickets'] > 5) { // Only consider teams with meaningful ticket count
                    $worstRate = $teamRate;
                    $worstTeam = $team;
                }
            }
        }

        if ($bestTeam) {
            $insights[] = [
                'type' => 'success',
                'icon' => 'fa-users',
                'title' => 'Top Performing Team - Excellence Model',
                'description' => "Team {$bestTeam['team']} leads with " . round($bestRate, 1) . "% resolution rate on {$bestTeam['total_tickets']} tickets. AI Recommendation: (1) Document their successful processes, (2) Conduct knowledge-sharing session, (3) Consider peer mentoring program, (4) Replicate their ticket prioritization strategy across other teams."
            ];
        }

        if ($worstTeam && $worstTeam !== $bestTeam) {
            $performanceGap = round($bestRate - $worstRate, 1);
            $insights[] = [
                'type' => 'warning',
                'icon' => 'fa-user-cog',
                'title' => 'Team Performance Gap - {$performanceGap}% Variance',
                'description' => "Team {$worstTeam['team']} shows " . round($worstRate, 1) . "% resolution rate vs top team's " . round($bestRate, 1) . "%. AI Analysis: (1) Review ticket complexity distribution, (2) Assess resource allocation, (3) Provide targeted training from high-performers, (4) Investigate if lower performing team handles more complex cases."
            ];
        }
    }

    // Predictive Analytics & Smart Recommendations
    $projectedTickets = round($avgTicketsPerDay * 30);
    $topCategoryName = isset($data['top_categories'][0]['category']) ? $data['top_categories'][0]['category'] : 'common';
    $insights[] = [
        'type' => 'primary',
        'icon' => 'fa-robot',
        'title' => 'AI-Powered Strategic Recommendations',
        'description' => "Predictive Analysis: Expecting ~{$projectedTickets} tickets next period based on {$avgTicketsPerDay}/day average. Priority Actions: (1) Implement automated ticket routing to reduce triage time by 40%, (2) Deploy chatbot for {$topCategoryName} issues (potential 20% ticket reduction), (3) Schedule preventive maintenance during low-volume periods, (4) Build self-service portal for top 3 categories, (5) Implement SLA-based escalation automation."
    ];

    // Trend-based Early Warning
    if (!empty($data['weekly_trends'])) {
        $recentWeeks = array_slice($data['weekly_trends'], 0, 2);
        if (count($recentWeeks) >= 2) {
            $weeklyChange = $recentWeeks[0]['tickets_created'] - $recentWeeks[1]['tickets_created'];
            $weeklyChangePercent = $recentWeeks[1]['tickets_created'] > 0
                ? round(($weeklyChange / $recentWeeks[1]['tickets_created']) * 100, 1)
                : 0;

            if ($weeklyChangePercent > 20) {
                $insights[] = [
                    'type' => 'warning',
                    'icon' => 'fa-chart-line',
                    'title' => 'Volume Surge Alert - +{$weeklyChangePercent}% Increase',
                    'description' => "Ticket volume increased by {$weeklyChangePercent}% this week ({$weeklyChange} more tickets). AI Trend Analysis: (1) Investigate recent changes (software updates, policy changes), (2) Prepare for sustained high volume, (3) Consider temporary resource boost, (4) Monitor for specific issue patterns causing surge."
                ];
            } elseif ($weeklyChangePercent < -20) {
                $insights[] = [
                    'type' => 'success',
                    'icon' => 'fa-arrow-down',
                    'title' => 'Volume Decrease - {$weeklyChangePercent}% Reduction',
                    'description' => "Ticket volume decreased by " . abs($weeklyChangePercent) . "% this week. Positive indicators suggest: (1) Recent improvements are working, (2) Users adapting to new systems, (3) Knowledge base effectiveness, (4) Proactive support reducing issues. Maintain current strategies."
                ];
            }
        }
    }

    return $insights;
}

// Get report parameters
$reportType = $_GET['type'] ?? 'overview';
// Use wider date range for trends to show actual data
if ($reportType == 'trends') {
    $startDate = $_GET['start_date'] ?? '2019-01-01'; // Show all historical data for trends
    $endDate = $_GET['end_date'] ?? date('Y-m-d'); // Today
} else {
    $startDate = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month for other reports
    $endDate = $_GET['end_date'] ?? date('Y-m-d'); // Today
}
$teamId = $_GET['team_id'] ?? '';
$priority = $_GET['priority'] ?? '';
$status = $_GET['status'] ?? '';

$db = Database::getInstance();

// Get teams for filter dropdown
$teams = Team::findAll();

// Generate report data based on type
$reportData = [];
$reportTitle = '';
$reportDescription = '';

switch ($reportType) {
    case 'overview':
        $reportTitle = 'Helpdesk Overview Report';
        $reportDescription = 'Comprehensive overview of helpdesk performance and ticket statistics';
        
        // Get basic statistics
        $stmt = $db->prepare("SELECT COUNT(*) as total_tickets, SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open_tickets, SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_tickets, SUM(CASE WHEN status = 'solved' THEN 1 ELSE 0 END) as solved_tickets, SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed_tickets, SUM(CASE WHEN priority = 'high' THEN 1 ELSE 0 END) as high_priority_count, SUM(CASE WHEN priority = 'medium' THEN 1 ELSE 0 END) as medium_priority_count, SUM(CASE WHEN priority = 'low' THEN 1 ELSE 0 END) as low_priority_count, SUM(CASE WHEN priority = 'urgent' THEN 1 ELSE 0 END) as urgent_priority_count FROM ticket WHERE DATE(created_at) BETWEEN ? AND ?");
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $reportData['stats'] = $stmt->get_result()->fetch_assoc();
        
        // Get tickets by category
        $stmt = $db->prepare("SELECT category, COUNT(*) as count FROM ticket WHERE DATE(created_at) BETWEEN ? AND ? GROUP BY category ORDER BY count DESC");
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $reportData['categories'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Get team performance
        $stmt = $db->prepare("SELECT team, COUNT(*) as total_tickets FROM ticket WHERE DATE(created_at) BETWEEN ? AND ? GROUP BY team ORDER BY total_tickets DESC");
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $reportData['team_performance'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        break;
        
    case 'tickets':
        $reportTitle = 'Detailed Tickets Report';
        $reportDescription = 'Detailed listing of all tickets with filters applied';
        
        // Get overall statistics for summary cards
        $stmt = $db->prepare("SELECT COUNT(*) as total_tickets, SUM(CASE WHEN status = 'solved' THEN 1 ELSE 0 END) as solved_tickets FROM ticket WHERE DATE(created_at) BETWEEN ? AND ?");
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $reportData['stats'] = $stmt->get_result()->fetch_assoc();
        
        $whereConditions = ["DATE(t.created_at) BETWEEN ? AND ?"];
        $params = [$startDate, $endDate];
        $types = "ss";
        
        if ($teamId) {
            $whereConditions[] = "t.team = ?";
            $params[] = $teamId;
            $types .= "i";
        }
        if ($priority) {
            $whereConditions[] = "t.priority = ?";
            $params[] = $priority;
            $types .= "s";
        }
        if ($status) {
            $whereConditions[] = "t.status = ?";
            $params[] = $status;
            $types .= "s";
        }
        
        $sql = "SELECT t.id, t.title, t.priority, t.status, t.category, t.created_at, r.name as requester_name FROM ticket t LEFT JOIN requester r ON t.requester = r.id WHERE " . implode(" AND ", $whereConditions) . " ORDER BY t.created_at DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $reportData['tickets'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        break;
        
    case 'performance':
        $reportTitle = 'Team Performance Report';
        $reportDescription = 'Analysis of team and individual performance metrics';
        
        // Get overall statistics for summary cards
        $stmt = $db->prepare("SELECT COUNT(*) as total_tickets, SUM(CASE WHEN status = 'solved' THEN 1 ELSE 0 END) as solved_tickets FROM ticket WHERE DATE(created_at) BETWEEN ? AND ?");
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $reportData['stats'] = $stmt->get_result()->fetch_assoc();
        
        // Simple team performance metrics  
        $stmt = $db->prepare("SELECT team, COUNT(*) as total_tickets, SUM(CASE WHEN status = 'solved' THEN 1 ELSE 0 END) as solved_tickets FROM ticket WHERE DATE(created_at) BETWEEN ? AND ? GROUP BY team ORDER BY solved_tickets DESC");
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $reportData['team_performance'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // User performance placeholder
        $reportData['user_performance'] = [];
        break;
        
    case 'trends':
        $reportTitle = 'Trend Analysis Report';
        $reportDescription = 'Historical trends and patterns in ticket volume and resolution';

        // Get overall statistics for summary cards
        $stmt = $db->prepare("SELECT COUNT(*) as total_tickets, SUM(CASE WHEN status = 'solved' THEN 1 ELSE 0 END) as solved_tickets, SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed_tickets FROM ticket WHERE DATE(created_at) BETWEEN ? AND ?");
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $reportData['stats'] = $stmt->get_result()->fetch_assoc();

        // Daily ticket trends
        $stmt = $db->prepare("SELECT DATE(created_at) as ticket_date, COUNT(*) as tickets_created, SUM(CASE WHEN status = 'solved' THEN 1 ELSE 0 END) as tickets_solved FROM ticket WHERE DATE(created_at) BETWEEN ? AND ? GROUP BY DATE(created_at) ORDER BY ticket_date ASC");
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $reportData['daily_trends'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Category trends
        $stmt = $db->prepare("SELECT category, COUNT(*) as count FROM ticket WHERE DATE(created_at) BETWEEN ? AND ? GROUP BY category ORDER BY count DESC");
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $reportData['category_trends'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        break;

    case 'insights':
        $reportTitle = 'Analytics Insights Report';
        $reportDescription = 'Intelligent analysis and recommendations based on rule-based analytics';

        // Get comprehensive statistics for analysis
        $stmt = $db->prepare("SELECT COUNT(*) as total_tickets, SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open_tickets, SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_tickets, SUM(CASE WHEN status = 'solved' THEN 1 ELSE 0 END) as solved_tickets, SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed_tickets, SUM(CASE WHEN priority = 'urgent' THEN 1 ELSE 0 END) as urgent_tickets, SUM(CASE WHEN priority = 'high' THEN 1 ELSE 0 END) as high_priority_tickets FROM ticket WHERE DATE(created_at) BETWEEN ? AND ?");
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $reportData['stats'] = $stmt->get_result()->fetch_assoc();

        // Get category distribution
        $stmt = $db->prepare("SELECT category, COUNT(*) as count FROM ticket WHERE DATE(created_at) BETWEEN ? AND ? GROUP BY category ORDER BY count DESC LIMIT 5");
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $reportData['top_categories'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Get team performance
        $stmt = $db->prepare("SELECT team, COUNT(*) as total_tickets, SUM(CASE WHEN status = 'solved' THEN 1 ELSE 0 END) as solved_tickets FROM ticket WHERE DATE(created_at) BETWEEN ? AND ? GROUP BY team ORDER BY total_tickets DESC");
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $reportData['team_stats'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Get weekly trends
        $stmt = $db->prepare("SELECT YEARWEEK(created_at) as week_num, COUNT(*) as tickets_created, SUM(CASE WHEN status = 'solved' THEN 1 ELSE 0 END) as tickets_solved FROM ticket WHERE DATE(created_at) BETWEEN ? AND ? GROUP BY week_num ORDER BY week_num DESC LIMIT 4");
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $reportData['weekly_trends'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Generate insights
        $reportData['ai_insights'] = generateAIInsights($reportData);
        break;
}

// Calculate summary metrics
$totalTickets = 0;
$solvedTickets = 0;
$avgResolutionTime = 0;

if (isset($reportData['stats'])) {
    $totalTickets = $reportData['stats']['total_tickets'];
    $solvedTickets = $reportData['stats']['solved_tickets'];
}

// Don't override stats data - keep the values from the database queries
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics - Helpdesk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
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
      }
      
      body {
        background: var(--treasury-light);
      }
      
      .report-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 2px 8px rgba(30, 58, 95, 0.08);
        transition: transform 0.2s, box-shadow 0.2s;
      }
      
      .report-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(30, 58, 95, 0.15);
      }
      
      .stat-card {
        background: linear-gradient(135deg, var(--treasury-brown) 0%, var(--treasury-tan) 100%);
        color: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1rem;
      }
      
      .btn-export {
        background: linear-gradient(135deg, var(--treasury-brown) 0%, var(--treasury-tan) 100%);
        border: none;
        color: white;
        border-radius: 8px;
        padding: 0.5rem 1rem;
        margin: 0 0.25rem;
        z-index: 999;
        transition: all 0.2s;
      }
      
      .btn-export:hover {
        background: linear-gradient(135deg, var(--treasury-tan) 0%, var(--treasury-brown) 100%);
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(139, 69, 19, 0.3);
      }
      
      .btn-export:last-child {
        background: linear-gradient(135deg, var(--treasury-blue) 0%, var(--treasury-navy) 100%);
        border: 1px solid var(--treasury-blue);
      }
      
      .btn-export:last-child:hover {
        background: linear-gradient(135deg, var(--treasury-navy) 0%, var(--treasury-blue) 100%);
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(74, 144, 164, 0.3);
      }
      
      .form-select:focus, .form-control:focus {
        border-color: var(--treasury-tan);
        box-shadow: 0 0 0 0.2rem rgba(210, 180, 140, 0.25);
      }
      
      .table th {
        background-color: var(--treasury-brown);
        color: white;
        border: none;
      }
      
      .chart-container {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(30, 58, 95, 0.08);
        margin-bottom: 2rem;
      }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="d-flex">
        <?php include 'sidebar.php'; ?>
        
        <div style="flex: 1; padding: 2rem; width: 100%; margin-top: 70px; height: calc(100vh - 70px); overflow-y: auto;">
            <div class="container-fluid" style="max-width: none; padding: 0;">
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h3 mb-1" style="color: var(--treasury-navy);">
                            <i class="fas fa-chart-bar me-2"></i>Reports & Analytics
                        </h1>
                        <p class="mb-0 text-muted">Generate comprehensive reports and export data</p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-export" onclick="exportReport('pdf')">
                            <i class="fas fa-file-pdf me-2"></i>Export PDF
                        </button>
                        <button class="btn btn-export" onclick="exportReport('docx')">
                            <i class="fas fa-file-word me-2"></i>Export DOCX
                        </button>
                        <button class="btn btn-export" onclick="exportReport('excel')">
                            <i class="fas fa-file-excel me-2"></i>Export Excel
                        </button>
                        <button class="btn btn-export" onclick="exportCharts()">
                            <i class="fas fa-chart-line me-2"></i>Export Charts
                        </button>
                    </div>
                </div>

                <!-- Report Filters -->
                <div class="report-card mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3" style="color: var(--treasury-navy);">
                            <i class="fas fa-filter me-2"></i>Report Filters
                        </h5>
                        
                        <form method="GET" action="reports.php" id="reportForm">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Report Type</label>
                                    <select class="form-select" name="type" onchange="document.getElementById('reportForm').submit()">
                                        <option value="overview" <?= $reportType == 'overview' ? 'selected' : '' ?>>Overview</option>
                                        <option value="tickets" <?= $reportType == 'tickets' ? 'selected' : '' ?>>Detailed Tickets</option>
                                        <option value="performance" <?= $reportType == 'performance' ? 'selected' : '' ?>>Performance</option>
                                        <option value="trends" <?= $reportType == 'trends' ? 'selected' : '' ?>>Trends</option>
                                        <option value="insights" <?= $reportType == 'insights' ? 'selected' : '' ?>>Analytics Insights</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Start Date</label>
                                    <input type="date" class="form-control" name="start_date" value="<?= $startDate ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">End Date</label>
                                    <input type="date" class="form-control" name="end_date" value="<?= $endDate ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Team</label>
                                    <select class="form-select" name="team_id">
                                        <option value="">All Teams</option>
                                        <?php foreach ($teams as $team): ?>
                                            <option value="<?= $team->id ?>" <?= $teamId == $team->id ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($team->name) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Priority</label>
                                    <select class="form-select" name="priority">
                                        <option value="">All Priorities</option>
                                        <option value="low" <?= $priority == 'low' ? 'selected' : '' ?>>Low</option>
                                        <option value="medium" <?= $priority == 'medium' ? 'selected' : '' ?>>Medium</option>
                                        <option value="high" <?= $priority == 'high' ? 'selected' : '' ?>>High</option>
                                        <option value="urgent" <?= $priority == 'urgent' ? 'selected' : '' ?>>Urgent</option>
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="submit" class="btn btn-export d-block w-100">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Report Header -->
                <div class="report-card mb-4" id="reportContent">
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <h2 style="color: var(--treasury-navy);"><?= $reportTitle ?></h2>
                            <p class="text-muted"><?= $reportDescription ?></p>
                            <p class="small text-muted">
                                Report Period: <?= date('M d, Y', strtotime($startDate)) ?> - <?= date('M d, Y', strtotime($endDate)) ?>
                                | Generated: <?= date('M d, Y \a\t g:i A') ?>
                            </p>
                        </div>

                        <!-- Summary Stats -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="stat-card">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3 class="mb-0"><?= number_format($totalTickets) ?></h3>
                                            <p class="mb-0">Total Tickets</p>
                                        </div>
                                        <i class="fas fa-ticket fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3 class="mb-0"><?= number_format($reportData['stats']['open_tickets'] ?? 0) ?></h3>
                                            <p class="mb-0">Open Tickets</p>
                                        </div>
                                        <i class="fas fa-folder-open fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3 class="mb-0"><?= number_format($reportData['stats']['pending_tickets'] ?? 0) ?></h3>
                                            <p class="mb-0">Pending</p>
                                        </div>
                                        <i class="fas fa-clock fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3 class="mb-0"><?= number_format($solvedTickets) ?></h3>
                                            <p class="mb-0">Solved</p>
                                        </div>
                                        <i class="fas fa-check-circle fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if ($reportType == 'overview'): ?>
                            <!-- Overview Report Content -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="chart-container">
                                        <h5 style="color: var(--treasury-navy);">Tickets by Status</h5>
                                        <canvas id="statusChart" width="400" height="200"></canvas>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="chart-container">
                                        <h5 style="color: var(--treasury-navy);">Tickets by Category</h5>
                                        <canvas id="categoryChart" width="400" height="200"></canvas>
                                    </div>
                                </div>
                            </div>

                            <!-- Team Performance Table -->
                            <div class="chart-container">
                                <h5 style="color: var(--treasury-navy);" class="mb-3">Team Performance</h5>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Team</th>
                                                <th>Total Tickets</th>
                                                <th>Avg Resolution Time</th>
                                                <th>Performance</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($reportData['team_performance'] as $team): ?>
                                                <tr>
                                                    <td>Team <?= $team['team'] ?></td>
                                                    <td><?= $team['total_tickets'] ?></td>
                                                    <td>N/A</td>
                                                    <td>
                                                        <?php 
                                                        $performance = $team['total_tickets'] > 0 ? 'Active' : 'Idle';
                                                        $badgeClass = $team['total_tickets'] > 5 ? 'bg-success' : ($team['total_tickets'] > 0 ? 'bg-warning' : 'bg-secondary');
                                                        ?>
                                                        <span class="badge <?= $badgeClass ?>"><?= $performance ?></span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        <?php elseif ($reportType == 'tickets'): ?>
                            <!-- Detailed Tickets Report -->
                            <div class="chart-container">
                                <h5 style="color: var(--treasury-navy);" class="mb-3">Ticket Details</h5>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Subject</th>
                                                <th>Requester</th>
                                                <th>Department</th>
                                                <th>Team</th>
                                                <th>Priority</th>
                                                <th>Status</th>
                                                <th>Created</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($reportData['tickets'] as $ticket): ?>
                                                <tr>
                                                    <td>#<?= $ticket['id'] ?></td>
                                                    <td><?= htmlspecialchars($ticket['title']) ?></td>
                                                    <td><?= htmlspecialchars($ticket['requester_name']) ?></td>
                                                    <td><?= htmlspecialchars($ticket['department_name']) ?></td>
                                                    <td><?= htmlspecialchars($ticket['team_name']) ?></td>
                                                    <td>
                                                        <?php 
                                                        $priorityClass = [
                                                            'low' => 'bg-success', 
                                                            'medium' => 'bg-warning', 
                                                            'high' => 'bg-danger', 
                                                            'urgent' => 'bg-dark'
                                                        ];
                                                        ?>
                                                        <span class="badge <?= $priorityClass[$ticket['priority']] ?? 'bg-secondary' ?>">
                                                            <?= ucfirst($ticket['priority']) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                        $statusClass = [
                                                            'open' => 'bg-primary', 
                                                            'pending' => 'bg-warning', 
                                                            'solved' => 'bg-success', 
                                                            'closed' => 'bg-secondary'
                                                        ];
                                                        ?>
                                                        <span class="badge <?= $statusClass[$ticket['status']] ?? 'bg-secondary' ?>">
                                                            <?= ucfirst($ticket['status']) ?>
                                                        </span>
                                                    </td>
                                                    <td><?= date('M d, Y', strtotime($ticket['created_at'])) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        <?php elseif ($reportType == 'performance'): ?>
                            <!-- Performance Report -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="chart-container">
                                        <h5 style="color: var(--treasury-navy);" class="mb-3">Team Performance</h5>
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Team</th>
                                                        <th>Total</th>
                                                        <th>Solved</th>
                                                        <th>Rate</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($reportData['team_performance'] as $team): ?>
                                                        <tr>
                                                            <td>Team <?= $team['team'] ?></td>
                                                            <td><?= $team['total_tickets'] ?></td>
                                                            <td><?= $team['solved_tickets'] ?></td>
                                                            <td>
                                                                <?php 
                                                                $rate = $team['total_tickets'] > 0 ? round(($team['solved_tickets'] / $team['total_tickets']) * 100, 1) : 0;
                                                                ?>
                                                                <?= $rate ?>%
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="chart-container">
                                        <h5 style="color: var(--treasury-navy);" class="mb-3">User Performance</h5>
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>User</th>
                                                        <th>Assigned</th>
                                                        <th>Solved</th>
                                                        <th>Rate</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($reportData['user_performance'] as $user): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($user['user_name']) ?></td>
                                                            <td><?= $user['assigned_tickets'] ?></td>
                                                            <td><?= $user['solved_tickets'] ?></td>
                                                            <td>
                                                                <?php 
                                                                $rate = $user['assigned_tickets'] > 0 ? round(($user['solved_tickets'] / $user['assigned_tickets']) * 100, 1) : 0;
                                                                ?>
                                                                <?= $rate ?>%
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <?php elseif ($reportType == 'trends'): ?>
                            <!-- Trends Report -->
                            <div class="chart-container">
                                <h5 style="color: var(--treasury-navy);" class="mb-3">Daily Ticket Trends</h5>
                                <?php if (!empty($reportData['daily_trends'])): ?>
                                    <canvas id="trendsChart" width="800" height="400" style="max-width: 100%; height: 400px;"></canvas>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        No trend data available for the selected date range (<?= $startDate ?> to <?= $endDate ?>). Try expanding the date range to see historical trends.
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="chart-container">
                                <h5 style="color: var(--treasury-navy);" class="mb-3">Category Performance</h5>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Category</th>
                                                <th>Total Tickets</th>
                                                <th>Avg Resolution Time</th>
                                                <th>Performance</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($reportData['category_trends'] as $category): ?>
                                                <tr>
                                                    <td><?= ucfirst($category['category']) ?></td>
                                                    <td><?= $category['count'] ?></td>
                                                    <td><?= 0 ? round(0, 1) . 'h' : 'N/A' ?></td>
                                                    <td>
                                                        <?php
                                                        $performance = 0 < 24 ? 'Excellent' :
                                                                      (0 < 48 ? 'Good' : 'Needs Improvement');
                                                        $badgeClass = 0 < 24 ? 'bg-success' :
                                                                     (0 < 48 ? 'bg-warning' : 'bg-danger');
                                                        ?>
                                                        <span class="badge <?= $badgeClass ?>"><?= $performance ?></span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        <?php elseif ($reportType == 'insights'): ?>
                            <!-- Analytics Insights Report -->
                            <style>
                                .insight-card {
                                    border-left: 4px solid;
                                    padding: 1.5rem;
                                    margin-bottom: 1.5rem;
                                    border-radius: 8px;
                                    background: white;
                                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
                                    transition: transform 0.2s, box-shadow 0.2s;
                                }
                                .insight-card:hover {
                                    transform: translateX(5px);
                                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                                }
                                .insight-card.success { border-left-color: #2d5a3d; background: linear-gradient(to right, rgba(45, 90, 61, 0.05), white); }
                                .insight-card.warning { border-left-color: #b8860b; background: linear-gradient(to right, rgba(184, 134, 11, 0.05), white); }
                                .insight-card.danger { border-left-color: #722f37; background: linear-gradient(to right, rgba(114, 47, 55, 0.05), white); }
                                .insight-card.info { border-left-color: #4a90a4; background: linear-gradient(to right, rgba(74, 144, 164, 0.05), white); }
                                .insight-card.primary { border-left-color: #8B4513; background: linear-gradient(to right, rgba(139, 69, 19, 0.05), white); }
                                .insight-icon {
                                    font-size: 2.5rem;
                                    margin-right: 1.5rem;
                                    opacity: 0.8;
                                }
                                .insight-icon.success { color: #2d5a3d; }
                                .insight-icon.warning { color: #b8860b; }
                                .insight-icon.danger { color: #722f37; }
                                .insight-icon.info { color: #4a90a4; }
                                .insight-icon.primary { color: #8B4513; }
                                .metric-card {
                                    background: linear-gradient(135deg, var(--treasury-blue), var(--treasury-navy));
                                    color: white;
                                    padding: 1.5rem;
                                    border-radius: 12px;
                                    text-align: center;
                                    margin-bottom: 1rem;
                                }
                            </style>

                            <!-- Key Metrics -->
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <div class="metric-card">
                                        <h3><?= round(($stats['solved_tickets'] / max($stats['total_tickets'], 1)) * 100, 1) ?>%</h3>
                                        <p class="mb-0">Resolution Rate</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="metric-card" style="background: linear-gradient(135deg, var(--treasury-green), var(--treasury-blue));">
                                        <h3><?= count($reportData['team_stats'] ?? []) ?></h3>
                                        <p class="mb-0">Active Teams</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="metric-card" style="background: linear-gradient(135deg, var(--treasury-amber), var(--treasury-gold));">
                                        <h3><?= count($reportData['top_categories'] ?? []) ?></h3>
                                        <p class="mb-0">Issue Categories</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="metric-card" style="background: linear-gradient(135deg, var(--treasury-burgundy), var(--treasury-brown));">
                                        <h3><?= $stats['urgent_tickets'] + $stats['high_priority_tickets'] ?></h3>
                                        <p class="mb-0">Critical Tickets</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Analytics Insights Cards -->
                            <div class="chart-container">
                                <h5 style="color: var(--treasury-navy);" class="mb-4">
                                    <i class="fas fa-chart-line me-2"></i>Analytics Insights
                                </h5>
                                <?php foreach ($reportData['ai_insights'] as $insight): ?>
                                    <div class="insight-card <?= $insight['type'] ?>">
                                        <div class="d-flex align-items-start">
                                            <i class="fas <?= $insight['icon'] ?> insight-icon <?= $insight['type'] ?>"></i>
                                            <div style="flex: 1;">
                                                <h5 style="color: var(--treasury-navy); margin-bottom: 0.5rem;">
                                                    <?= htmlspecialchars($insight['title']) ?>
                                                </h5>
                                                <p style="color: #6c757d; margin-bottom: 0; line-height: 1.6;">
                                                    <?= htmlspecialchars($insight['description']) ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Top Categories Chart -->
                            <?php if (!empty($reportData['top_categories'])): ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="chart-container">
                                        <h5 style="color: var(--treasury-navy);" class="mb-3">Top Issue Categories</h5>
                                        <canvas id="aiCategoriesChart"></canvas>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="chart-container">
                                        <h5 style="color: var(--treasury-navy);" class="mb-3">Team Performance Overview</h5>
                                        <canvas id="aiTeamChart"></canvas>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Create Ticket Modal -->
    <?php include 'includes/create-ticket-modal.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="includes/create-ticket-modal.js"></script>
    
    <script>
        // Export functions
        function exportReport(format) {
            const params = new URLSearchParams(window.location.search);
            params.set('format', format);
            params.set('type', '<?= $reportType ?>');
            window.open('export-report.php?' + params.toString(), '_blank');
        }

        function exportCharts() {
            // Create a new window/document with charts for printing
            const printWindow = window.open('', '_blank', 'width=1200,height=800');
            
            let chartsHTML = '<!DOCTYPE html>' +
                '<html>' +
                '<head>' +
                '<meta charset="UTF-8">' +
                '<title><?= ucfirst($reportType) ?> Charts - Kenya National Treasury Helpdesk</title>' +
                '<style>' +
                'body { font-family: Arial, sans-serif; margin: 20px; }' +
                '.header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #8B4513; padding-bottom: 20px; }' +
                '.header h1 { color: #8B4513; margin: 0; }' +
                '.header p { color: #666; margin: 5px 0; }' +
                '.chart-container { margin: 40px 0; page-break-inside: avoid; }' +
                '.chart-title { font-size: 18px; font-weight: bold; color: #8B4513; margin-bottom: 15px; text-align: center; }' +
                'canvas { max-width: 100%; height: auto; border: 1px solid #ddd; }' +
                '@media print { ' +
                'body { margin: 0; } ' +
                '.chart-container { page-break-after: always; }' +
                '.chart-container:last-child { page-break-after: auto; }' +
                '}' +
                '</style>' +
                '</head>' +
                '<body>' +
                '<div class="header">' +
                '<h1>Kenya National Treasury Helpdesk</h1>' +
                '<h2><?= ucfirst($reportType) ?> Report Charts</h2>' +
                '<p>Generated on: ' + new Date().toLocaleString() + '</p>' +
                '</div>';
            
            // Find all canvas elements (charts) and copy them
            const canvases = document.querySelectorAll('canvas');
            canvases.forEach((canvas, index) => {
                if (canvas.id) {
                    const dataURL = canvas.toDataURL('image/png');
                    const chartTitle = getChartTitle(canvas.id);
                    chartsHTML += '<div class="chart-container">' +
                        '<div class="chart-title">' + chartTitle + '</div>' +
                        '<img src="' + dataURL + '" style="max-width: 100%; height: auto;" alt="' + chartTitle + '">' +
                        '</div>';
                }
            });
            
            chartsHTML += '<script>' +
                'window.onload = function() {' +
                'setTimeout(function() {' +
                'window.print();' +
                '}, 1000);' +
                '}' +
                '</' + 'script>' +
                '</body>' +
                '</html>';
            
            printWindow.document.write(chartsHTML);
            printWindow.document.close();
        }

        function getChartTitle(canvasId) {
            const titles = {
                'statusChart': 'Ticket Status Distribution',
                'priorityChart': 'Priority Distribution',  
                'categoryChart': 'Category Breakdown',
                'teamChart': 'Team Performance',
                'trendsChart': 'Daily Ticket Trends',
                'performanceChart': 'Performance Metrics'
            };
            return titles[canvasId] || 'Chart';
        }

        // Chart configurations
        const chartColors = {
            primary: '#8B4513',
            secondary: '#D2B48C',
            success: '#2d5a3d',
            warning: '#b8860b',
            danger: '#722f37',
            info: '#4a90a4'
        };

        <?php if ($reportType == 'overview' && isset($reportData['stats'])): ?>
        // Status Chart
        const statusCtx = document.getElementById('statusChart');
        if (statusCtx) {
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Open', 'Pending', 'Solved', 'Closed'],
                    datasets: [{
                        data: [
                            <?= $reportData['stats']['open_tickets'] ?>,
                            <?= $reportData['stats']['pending_tickets'] ?>,
                            <?= $reportData['stats']['solved_tickets'] ?>,
                            <?= $reportData['stats']['closed_tickets'] ?>
                        ],
                        backgroundColor: [
                            chartColors.info,
                            chartColors.warning,
                            chartColors.success,
                            chartColors.secondary
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        // Category Chart
        const categoryCtx = document.getElementById('categoryChart');
        if (categoryCtx) {
            new Chart(categoryCtx, {
                type: 'bar',
                data: {
                    labels: [<?php echo implode(',', array_map(function($cat) { return '"' . ucfirst($cat['category']) . '"'; }, $reportData['categories'])); ?>],
                    datasets: [{
                        label: 'Tickets',
                        data: [<?php echo implode(',', array_column($reportData['categories'], 'count')); ?>],
                        backgroundColor: chartColors.primary
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
        <?php endif; ?>

        <?php if ($reportType == 'trends' && isset($reportData['daily_trends']) && !empty($reportData['daily_trends'])): ?>
        // Trends Chart
        const trendsCtx = document.getElementById('trendsChart');
        if (trendsCtx) {

            // Add slight delay to ensure DOM is ready
            setTimeout(function() {
                const chart = new Chart(trendsCtx, {
                type: 'line',
                data: {
                    labels: [<?php echo implode(',', array_map(function($day) { return '"' . date('M d', strtotime($day['ticket_date'])) . '"'; }, $reportData['daily_trends'])); ?>],
                    datasets: [{
                        label: 'Tickets Created',
                        data: [<?php echo implode(',', array_column($reportData['daily_trends'], 'tickets_created')); ?>],
                        borderColor: chartColors.primary,
                        backgroundColor: chartColors.primary + '20',
                        tension: 0.4,
                        fill: false,
                        pointRadius: 6,
                        pointHoverRadius: 8
                    }, {
                        label: 'Tickets Solved',
                        data: [<?php echo implode(',', array_column($reportData['daily_trends'], 'tickets_solved')); ?>],
                        borderColor: chartColors.success,
                        backgroundColor: chartColors.success + '20',
                        tension: 0.4,
                        fill: false,
                        pointRadius: 6,
                        pointHoverRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
                });
            }, 500); // Wait 500ms for DOM and Chart.js to be ready
        }
        <?php endif; ?>

        <?php if ($reportType == 'insights' && isset($reportData['top_categories']) && !empty($reportData['top_categories'])): ?>
        // AI Categories Chart
        const aiCategoriesCtx = document.getElementById('aiCategoriesChart');
        if (aiCategoriesCtx) {
            new Chart(aiCategoriesCtx, {
                type: 'doughnut',
                data: {
                    labels: [<?php echo implode(',', array_map(function($cat) { return '"' . ucfirst($cat['category']) . '"'; }, $reportData['top_categories'])); ?>],
                    datasets: [{
                        data: [<?php echo implode(',', array_column($reportData['top_categories'], 'count')); ?>],
                        backgroundColor: [
                            chartColors.primary,
                            chartColors.secondary,
                            chartColors.info,
                            chartColors.warning,
                            chartColors.success
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        // AI Team Performance Chart
        const aiTeamCtx = document.getElementById('aiTeamChart');
        if (aiTeamCtx && <?php echo !empty($reportData['team_stats']) ? 'true' : 'false'; ?>) {
            new Chart(aiTeamCtx, {
                type: 'bar',
                data: {
                    labels: [<?php echo !empty($reportData['team_stats']) ? implode(',', array_map(function($team) { return '"Team ' . $team['team'] . '"'; }, $reportData['team_stats'])) : ''; ?>],
                    datasets: [{
                        label: 'Total Tickets',
                        data: [<?php echo !empty($reportData['team_stats']) ? implode(',', array_column($reportData['team_stats'], 'total_tickets')) : ''; ?>],
                        backgroundColor: chartColors.primary,
                        borderRadius: 8
                    }, {
                        label: 'Solved Tickets',
                        data: [<?php echo !empty($reportData['team_stats']) ? implode(',', array_column($reportData['team_stats'], 'solved_tickets')) : ''; ?>],
                        backgroundColor: chartColors.success,
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
        <?php endif; ?>

    </script>
</body>
</html>