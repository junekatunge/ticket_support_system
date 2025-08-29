<?php
session_start();
if (!isset($_SESSION['logged-in']) || $_SESSION['logged-in'] == false) {
    header('Location: signin.php');
    exit();
}

require_once 'src/database.php';
require_once 'src/user.php';

$user = $_SESSION['user'];
$db = Database::getInstance();

if (!isset($_GET['format']) || !isset($_GET['type'])) {
    header('Location: reports.php');
    exit();
}

$format = $_GET['format'];
$reportType = $_GET['type'];

// Get filters
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
$status = $_GET['status'] ?? '';
$priority = $_GET['priority'] ?? '';
$team = $_GET['team'] ?? '';

// Build WHERE clause for filtering
$whereClause = "WHERE 1=1";
$params = [];

if (!empty($dateFrom)) {
    $whereClause .= " AND DATE(t.created_at) >= ?";
    $params[] = $dateFrom;
}
if (!empty($dateTo)) {
    $whereClause .= " AND DATE(t.created_at) <= ?";
    $params[] = $dateTo;
}
if (!empty($status)) {
    $whereClause .= " AND t.status = ?";
    $params[] = $status;
}
if (!empty($priority)) {
    $whereClause .= " AND t.priority = ?";
    $params[] = $priority;
}
if (!empty($team)) {
    $whereClause .= " AND t.team = ?";
    $params[] = $team;
}

// Generate report data based on type
function getReportData($reportType, $whereClause, $params) {
    global $db;
    
    switch ($reportType) {
        case 'overview':
            $stmt = $db->prepare("SELECT COUNT(*) as total_tickets, SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open_tickets, SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_tickets, SUM(CASE WHEN status = 'solved' THEN 1 ELSE 0 END) as solved_tickets, SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed_tickets FROM ticket WHERE 1=1");
            $stmt->execute();
            return ['summary' => $stmt->get_result()->fetch_assoc()];
            
        case 'tickets':
            $stmt = $db->prepare("SELECT t.id, t.title, t.status, t.priority, t.created_at, t.team, r.name as requester_name FROM ticket t LEFT JOIN requester r ON t.requester = r.id ORDER BY t.created_at DESC LIMIT 100");
            $stmt->execute();
            return ['tickets' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC)];
            
        case 'performance':
            $stmt = $db->prepare("SELECT team, COUNT(*) as total_tickets, SUM(CASE WHEN status = 'solved' OR status = 'closed' THEN 1 ELSE 0 END) as resolved_tickets FROM ticket GROUP BY team");
            $stmt->execute();
            return ['performance' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC)];
            
        case 'trends':
            $stmt = $db->prepare("SELECT DATE(created_at) as date, COUNT(*) as tickets_created FROM ticket GROUP BY DATE(created_at) ORDER BY date DESC LIMIT 30");
            $stmt->execute();
            return ['trends' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC)];
            
        default:
            return [];
    }
}

$reportData = getReportData($reportType, $whereClause, $params);

// Handle different export formats
switch ($format) {
    case 'pdf':
        exportToPDF($reportData, $reportType, $dateFrom, $dateTo);
        break;
    case 'docx':
        exportToDOCX($reportData, $reportType, $dateFrom, $dateTo);
        break;
    case 'excel':
        exportToExcel($reportData, $reportType, $dateFrom, $dateTo);
        break;
    default:
        header('Location: reports.php');
        exit();
}

function exportToPDF($data, $reportType, $dateFrom, $dateTo) {
    $html = generateHTMLReport($data, $reportType, $dateFrom, $dateTo);
    
    // Output as HTML file that browsers can save as PDF
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $reportType . '_report_' . date('Y-m-d') . '.html"');
    
    echo '<!DOCTYPE html><html><head>';
    echo '<meta charset="UTF-8">';
    echo '<title>' . ucfirst($reportType) . ' Report</title>';
    echo '<style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
        .header { background: #8B4513; color: white; padding: 15px; text-align: center; margin-bottom: 20px; border-radius: 8px; }
        .summary { margin: 20px 0; }
        .metric { display: inline-block; margin: 10px; padding: 15px; border: 2px solid #D2B48C; text-align: center; border-radius: 8px; background: #f9f9f9; }
        .metric h3 { margin: 0; color: #8B4513; }
        .metric p { margin: 5px 0 0 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #D2B48C; color: #8B4513; font-weight: bold; }
        @media print { body { margin: 0; } }
    </style>';
    echo '</head><body onload="window.print()">';
    echo $html;
    echo '</body></html>';
}

function exportToDOCX($data, $reportType, $dateFrom, $dateTo) {
    $html = generateHTMLReport($data, $reportType, $dateFrom, $dateTo);
    
    // Output as RTF format that Word can open
    header('Content-Type: application/rtf');
    header('Content-Disposition: attachment; filename="' . $reportType . '_report_' . date('Y-m-d') . '.rtf"');
    
    echo '{\\rtf1\\ansi\\deff0 {\\fonttbl {\\f0 Times New Roman;}}';
    echo '{\\colortbl;\\red139\\green69\\blue19;\\red210\\green180\\blue140;}';
    echo '\\f0\\fs24';
    
    // Simple RTF header
    echo '\\par\\pard\\qc\\cf1\\b\\fs32 KENYA NATIONAL TREASURY HELPDESK\\par';
    echo '\\cf1\\b\\fs28 ' . strtoupper($reportType) . ' REPORT\\par\\par';
    echo '\\cf0\\b0\\fs20 Generated on: ' . date('Y-m-d H:i:s') . '\\par\\par';
    
    // Add data based on report type
    switch ($reportType) {
        case 'overview':
            if (isset($data['summary'])) {
                $summary = $data['summary'];
                echo '\\b OVERVIEW STATISTICS\\b0\\par';
                echo 'Total Tickets: ' . $summary['total_tickets'] . '\\par';
                echo 'Open Tickets: ' . $summary['open_tickets'] . '\\par';
                echo 'Pending Tickets: ' . $summary['pending_tickets'] . '\\par';
                echo 'Solved Tickets: ' . $summary['solved_tickets'] . '\\par';
                echo 'Closed Tickets: ' . $summary['closed_tickets'] . '\\par';
            }
            break;
        case 'tickets':
            echo '\\b TICKETS LIST\\b0\\par\\par';
            if (isset($data['tickets'])) {
                foreach (array_slice($data['tickets'], 0, 20) as $ticket) {
                    echo 'ID: ' . $ticket['id'] . ' | Title: ' . $ticket['title'] . ' | Status: ' . $ticket['status'] . '\\par';
                }
            }
            break;
    }
    
    echo '}';
}

function exportToExcel($data, $reportType, $dateFrom, $dateTo) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $reportType . '_report_' . date('Y-m-d') . '.xls"');
    
    echo '<html><head><meta charset="UTF-8"></head><body>';
    echo '<table border="1">';
    
    switch ($reportType) {
        case 'overview':
            echo '<tr><th colspan="2">Kenya National Treasury Helpdesk - Overview Report</th></tr>';
            echo '<tr><th>Metric</th><th>Value</th></tr>';
            if (isset($data['summary'])) {
                $summary = $data['summary'];
                echo '<tr><td>Total Tickets</td><td>' . $summary['total_tickets'] . '</td></tr>';
                echo '<tr><td>Open Tickets</td><td>' . $summary['open_tickets'] . '</td></tr>';
                echo '<tr><td>Pending Tickets</td><td>' . $summary['pending_tickets'] . '</td></tr>';
                echo '<tr><td>Solved Tickets</td><td>' . $summary['solved_tickets'] . '</td></tr>';
                echo '<tr><td>Closed Tickets</td><td>' . $summary['closed_tickets'] . '</td></tr>';
                echo '<tr><td>High Priority</td><td>' . ($summary['high_priority_count'] ?? 0) . '</td></tr>';
                echo '<tr><td>Medium Priority</td><td>' . ($summary['medium_priority_count'] ?? 0) . '</td></tr>';
                echo '<tr><td>Low Priority</td><td>' . ($summary['low_priority_count'] ?? 0) . '</td></tr>';
            }
            break;
            
        case 'tickets':
            echo '<tr><th>ID</th><th>Title</th><th>Status</th><th>Priority</th><th>Team</th><th>Requester</th><th>Created</th></tr>';
            if (isset($data['tickets'])) {
                foreach ($data['tickets'] as $ticket) {
                    echo '<tr>';
                    echo '<td>' . $ticket['id'] . '</td>';
                    echo '<td>' . htmlspecialchars($ticket['title']) . '</td>';
                    echo '<td>' . ucfirst($ticket['status']) . '</td>';
                    echo '<td>' . ucfirst($ticket['priority']) . '</td>';
                    echo '<td>Team ' . ($ticket['team'] ?? 'N/A') . '</td>';
                    echo '<td>' . htmlspecialchars($ticket['requester_name'] ?? 'Unknown') . '</td>';
                    echo '<td>' . date('Y-m-d H:i', strtotime($ticket['created_at'])) . '</td>';
                    echo '</tr>';
                }
            }
            break;
            
        case 'performance':
            echo '<tr><th>Team</th><th>Total Tickets</th><th>Resolved Tickets</th><th>Avg Resolution Time (Hours)</th></tr>';
            if (isset($data['performance'])) {
                foreach ($data['performance'] as $perf) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($perf['team']) . '</td>';
                    echo '<td>' . $perf['total_tickets'] . '</td>';
                    echo '<td>' . $perf['resolved_tickets'] . '</td>';
                    echo '<td>' . number_format($perf['avg_resolution_time'] ?? 0, 2) . '</td>';
                    echo '</tr>';
                }
            }
            break;
            
        case 'trends':
            echo '<tr><th>Date</th><th>Tickets Created</th><th>Tickets Resolved</th></tr>';
            if (isset($data['trends'])) {
                foreach ($data['trends'] as $trend) {
                    echo '<tr>';
                    echo '<td>' . $trend['date'] . '</td>';
                    echo '<td>' . $trend['tickets_created'] . '</td>';
                    echo '<td>' . $trend['tickets_resolved'] . '</td>';
                    echo '</tr>';
                }
            }
            break;
    }
    
    echo '</table>';
    echo '</body></html>';
}

function generateHTMLReport($data, $reportType, $dateFrom, $dateTo) {
    $html = '<div class="header">';
    $html .= '<h1>Kenya National Treasury Helpdesk</h1>';
    $html .= '<h2>' . ucfirst($reportType) . ' Report</h2>';
    if ($dateFrom || $dateTo) {
        $html .= '<p>Period: ' . ($dateFrom ?: 'Beginning') . ' to ' . ($dateTo ?: 'Present') . '</p>';
    }
    $html .= '<p>Generated on: ' . date('Y-m-d H:i:s') . '</p>';
    $html .= '</div>';
    
    switch ($reportType) {
        case 'overview':
            if (isset($data['summary'])) {
                $summary = $data['summary'];
                $html .= '<div class="summary">';
                $html .= '<div class="metric"><h3>' . $summary['total_tickets'] . '</h3><p>Total Tickets</p></div>';
                $html .= '<div class="metric"><h3>' . $summary['open_tickets'] . '</h3><p>Open</p></div>';
                $html .= '<div class="metric"><h3>' . $summary['pending_tickets'] . '</h3><p>Pending</p></div>';
                $html .= '<div class="metric"><h3>' . $summary['solved_tickets'] . '</h3><p>Solved</p></div>';
                $html .= '<div class="metric"><h3>' . $summary['closed_tickets'] . '</h3><p>Closed</p></div>';
                $html .= '</div>';
                
                $html .= '<h3>Priority Breakdown</h3>';
                $html .= '<div class="summary">';
                $html .= '<div class="metric"><h3>' . ($summary['high_priority_count'] ?? 0) . '</h3><p>High Priority</p></div>';
                $html .= '<div class="metric"><h3>' . ($summary['medium_priority_count'] ?? 0) . '</h3><p>Medium Priority</p></div>';
                $html .= '<div class="metric"><h3>' . ($summary['low_priority_count'] ?? 0) . '</h3><p>Low Priority</p></div>';
                $html .= '</div>';
            }
            break;
            
        case 'tickets':
            $html .= '<table>';
            $html .= '<tr><th>ID</th><th>Subject</th><th>Status</th><th>Priority</th><th>Team</th><th>Requester</th><th>Created</th></tr>';
            if (isset($data['tickets'])) {
                foreach ($data['tickets'] as $ticket) {
                    $html .= '<tr>';
                    $html .= '<td>' . $ticket['id'] . '</td>';
                    $html .= '<td>' . htmlspecialchars($ticket['title']) . '</td>';
                    $html .= '<td>' . ucfirst($ticket['status']) . '</td>';
                    $html .= '<td>' . ucfirst($ticket['priority']) . '</td>';
                    $html .= '<td>' . htmlspecialchars($ticket['team']) . '</td>';
                    $html .= '<td>' . htmlspecialchars($ticket['requester_name'] ?? 'Unknown') . '</td>';
                    $html .= '<td>' . date('Y-m-d H:i', strtotime($ticket['created_at'])) . '</td>';
                    $html .= '</tr>';
                }
            }
            $html .= '</table>';
            break;
    }
    
    return $html;
}
?>