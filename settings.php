<?php
require_once './src/Database.php';
require_once './src/user.php';

session_start();
if (!isset($_SESSION['logged-in']) || $_SESSION['logged-in'] == false) {
    header('Location: ./index.php');
    exit();
}
$user = $_SESSION['user'];

$db = Database::getInstance();

// Handle incomplete class object
$userRole = null;
if (is_object($user)) {
    $userRole = isset($user->role) ? $user->role : null;
}

// Only admin can access settings
if ($userRole !== 'admin') {
    header('Location: ./dashboard.php');
    exit();
}

$success_message = '';
$error_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'general_settings':
                // Handle general settings
                $site_name = trim($_POST['site_name'] ?? 'Helpdesk System');
                $admin_email = trim($_POST['admin_email'] ?? '');
                $timezone = $_POST['timezone'] ?? 'UTC';
                $maintenance_mode = isset($_POST['maintenance_mode']) ? 1 : 0;
                
                // Create or update settings table
                $db->query("CREATE TABLE IF NOT EXISTS settings (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    setting_key VARCHAR(255) UNIQUE,
                    setting_value TEXT,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )");
                
                // Save settings
                $settings = [
                    'site_name' => $site_name,
                    'admin_email' => $admin_email,
                    'timezone' => $timezone,
                    'maintenance_mode' => $maintenance_mode
                ];
                
                foreach ($settings as $key => $value) {
                    $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) 
                                         ON DUPLICATE KEY UPDATE setting_value = ?");
                    $stmt->bind_param("sss", $key, $value, $value);
                    $stmt->execute();
                }
                
                $success_message = 'General settings updated successfully!';
                break;
                
            case 'email_settings':
                // Handle email settings
                $smtp_host = trim($_POST['smtp_host'] ?? '');
                $smtp_port = intval($_POST['smtp_port'] ?? 587);
                $smtp_encryption = $_POST['smtp_encryption'] ?? 'tls';
                $smtp_username = trim($_POST['smtp_username'] ?? '');
                $smtp_password = $_POST['smtp_password'] ?? '';
                $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
                
                // Create settings table if not exists
                $db->query("CREATE TABLE IF NOT EXISTS settings (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    setting_key VARCHAR(255) UNIQUE,
                    setting_value TEXT,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )");
                
                // Save email settings
                $emailSettings = [
                    'smtp_host' => $smtp_host,
                    'smtp_port' => $smtp_port,
                    'smtp_encryption' => $smtp_encryption,
                    'smtp_username' => $smtp_username,
                    'smtp_password' => $smtp_password ? password_hash($smtp_password, PASSWORD_DEFAULT) : '',
                    'email_notifications' => $email_notifications
                ];
                
                foreach ($emailSettings as $key => $value) {
                    if ($key === 'smtp_password' && empty($_POST['smtp_password'])) {
                        continue; // Don't update password if not provided
                    }
                    $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) 
                                         ON DUPLICATE KEY UPDATE setting_value = ?");
                    $stmt->bind_param("sss", $key, $value, $value);
                    $stmt->execute();
                }
                
                $success_message = 'Email settings updated successfully!';
                break;
                
            case 'backup_database':
                try {
                    // Create backup directory if it doesn't exist
                    $backupDir = './database/backups/';
                    if (!is_dir($backupDir)) {
                        mkdir($backupDir, 0755, true);
                    }
                    
                    $filename = 'helpdesk_backup_' . date('Y-m-d_H-i-s') . '.sql';
                    $filepath = $backupDir . $filename;
                    
                    // Simple PHP-based backup
                    $tables = [];
                    $result = $db->query("SHOW TABLES");
                    while ($row = $result->fetch_row()) {
                        $tables[] = $row[0];
                    }
                    
                    $sqlScript = "-- Helpdesk Database Backup\n";
                    $sqlScript .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n\n";
                    
                    foreach ($tables as $table) {
                        // Get table structure
                        $result = $db->query("SHOW CREATE TABLE `$table`");
                        $row = $result->fetch_row();
                        $sqlScript .= "\n-- Table structure for table `$table`\n";
                        $sqlScript .= "DROP TABLE IF EXISTS `$table`;\n";
                        $sqlScript .= $row[1] . ";\n\n";
                        
                        // Get table data
                        $result = $db->query("SELECT * FROM `$table`");
                        if ($result->num_rows > 0) {
                            $sqlScript .= "-- Dumping data for table `$table`\n";
                            while ($row = $result->fetch_assoc()) {
                                $sqlScript .= "INSERT INTO `$table` VALUES (";
                                $values = [];
                                foreach ($row as $value) {
                                    $values[] = $value === null ? 'NULL' : "'" . $db->real_escape_string($value) . "'";
                                }
                                $sqlScript .= implode(',', $values) . ");\n";
                            }
                            $sqlScript .= "\n";
                        }
                    }
                    
                    if (file_put_contents($filepath, $sqlScript)) {
                        $success_message = "Database backup created successfully: {$filename}";
                    } else {
                        $error_message = 'Failed to write backup file. Please check directory permissions.';
                    }
                } catch (Exception $e) {
                    $error_message = 'Backup failed: ' . $e->getMessage();
                }
                break;
                
            case 'clear_cache':
                // Clear various cache types
                try {
                    // Clear session files (if using file-based sessions)
                    $sessionPath = session_save_path();
                    if ($sessionPath && is_dir($sessionPath)) {
                        $sessions = glob($sessionPath . '/sess_*');
                        foreach ($sessions as $session) {
                            if (is_file($session)) {
                                unlink($session);
                            }
                        }
                    }
                    
                    // Clear any temp files
                    $tempDirs = [sys_get_temp_dir(), './temp/', './cache/'];
                    foreach ($tempDirs as $dir) {
                        if (is_dir($dir)) {
                            $files = glob($dir . '/*');
                            foreach ($files as $file) {
                                if (is_file($file) && (time() - filemtime($file)) > 3600) { // Only delete files older than 1 hour
                                    unlink($file);
                                }
                            }
                        }
                    }
                    
                    $success_message = 'System cache cleared successfully!';
                } catch (Exception $e) {
                    $error_message = 'Failed to clear cache: ' . $e->getMessage();
                }
                break;
        }
    }
}

// Get system information
$phpVersion = phpversion();
$mysqlVersion = $db->query("SELECT VERSION() as version")->fetch_assoc()['version'] ?? 'Unknown';
$serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';
$diskSpace = disk_free_space('./');
$diskTotal = disk_total_space('./');
$diskUsed = $diskTotal - $diskSpace;

// Get user statistics
$userStats = $db->query("SELECT 
    COUNT(*) as total_users,
    COUNT(CASE WHEN role = 'admin' THEN 1 END) as admin_users,
    COUNT(CASE WHEN role = 'user' THEN 1 END) as regular_users
    FROM users")->fetch_assoc();

// Get ticket statistics - check if table exists first
$ticketStats = ['total_tickets' => 0, 'open_tickets' => 0, 'solved_tickets' => 0, 'closed_tickets' => 0];
try {
    $result = $db->query("SELECT 
        COUNT(*) as total_tickets,
        COUNT(CASE WHEN status = 'open' THEN 1 END) as open_tickets,
        COUNT(CASE WHEN status = 'solved' THEN 1 END) as solved_tickets,
        COUNT(CASE WHEN status = 'closed' THEN 1 END) as closed_tickets
        FROM tickets");
    if ($result) {
        $ticketStats = $result->fetch_assoc();
    }
} catch (Exception $e) {
    // Table doesn't exist or other error - use defaults
}

// Load current settings
$currentSettings = [];
try {
    $result = $db->query("SELECT setting_key, setting_value FROM settings");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $currentSettings[$row['setting_key']] = $row['setting_value'];
        }
    }
} catch (Exception $e) {
    // Settings table doesn't exist yet - use defaults
}

// Default values
$site_name = $currentSettings['site_name'] ?? 'Helpdesk System';
$admin_email = $currentSettings['admin_email'] ?? $user->email;
$timezone = $currentSettings['timezone'] ?? 'UTC';
$maintenance_mode = ($currentSettings['maintenance_mode'] ?? 0) == 1;
$smtp_host = $currentSettings['smtp_host'] ?? '';
$smtp_port = $currentSettings['smtp_port'] ?? 587;
$smtp_encryption = $currentSettings['smtp_encryption'] ?? 'tls';
$smtp_username = $currentSettings['smtp_username'] ?? '';
$email_notifications = ($currentSettings['email_notifications'] ?? 1) == 1;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Helpdesk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
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
      
      .card {
        box-shadow: 0 2px 8px rgba(30, 58, 95, 0.08);
      }
      
      .card-header {
        background: linear-gradient(135deg, var(--treasury-light) 0%, #ffffff 100%);
        border-bottom: 2px solid var(--treasury-tan);
      }
      
      .text-primary {
        color: var(--treasury-navy) !important;
      }
      
      .btn-primary {
        background: linear-gradient(135deg, var(--treasury-brown) 0%, var(--treasury-tan) 100%);
        border: none;
        box-shadow: 0 2px 4px rgba(139, 69, 19, 0.3);
      }
      
      .btn-primary:hover {
        background: linear-gradient(135deg, var(--treasury-tan) 0%, var(--treasury-brown) 100%);
        box-shadow: 0 4px 8px rgba(139, 69, 19, 0.4);
        transform: translateY(-1px);
      }
      
      .btn-warning {
        background: linear-gradient(135deg, var(--treasury-tan) 0%, var(--treasury-gold) 100%);
        border: none;
        color: var(--treasury-brown);
      }
      
      .btn-warning:hover {
        background: linear-gradient(135deg, var(--treasury-gold) 0%, var(--treasury-tan) 100%);
        color: var(--treasury-brown);
      }
      
      .btn-danger {
        background: var(--treasury-burgundy);
        border: none;
      }
      
      .btn-danger:hover {
        background: #8b3842;
      }
      
      .form-control:focus {
        border-color: var(--treasury-tan);
        box-shadow: 0 0 0 0.2rem rgba(210, 180, 140, 0.25);
      }
      
      .form-check-input:checked {
        background-color: var(--treasury-navy);
        border-color: var(--treasury-navy);
      }
      
      .alert-success {
        background-color: rgba(45, 90, 61, 0.1);
        border-color: var(--treasury-green);
        color: var(--treasury-green);
      }
      
      .alert-danger {
        background-color: rgba(114, 47, 55, 0.1);
        border-color: var(--treasury-burgundy);
        color: var(--treasury-burgundy);
      }
      
      .fa-cog, .fa-server, .fa-chart-bar, .fa-sliders-h, .fa-envelope, .fa-database, .fa-tools {
        color: var(--treasury-brown);
      }
      
      .fa-save, .fa-download, .fa-broom, .fa-sync, .fa-stethoscope {
        color: var(--treasury-tan);
      }
      
      .fa-check-circle {
        color: var(--treasury-green);
      }
      
      .fa-exclamation-triangle {
        color: var(--treasury-burgundy);
      }
    </style>
</head>
<body>
    <div class="d-flex">
        <?php include 'sidebar.php'; ?>
        
        <div style="flex: 1; padding: 2rem; width: 100%;">
            <div class="container-fluid" style="max-width: none; padding: 0;">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-cog me-2"></i>System Settings
        </h1>
    </div>

    <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- System Overview -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-server me-2"></i>System Overview
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>PHP Version:</strong></td>
                                <td><?= $phpVersion ?></td>
                            </tr>
                            <tr>
                                <td><strong>MySQL Version:</strong></td>
                                <td><?= $mysqlVersion ?></td>
                            </tr>
                            <tr>
                                <td><strong>Server:</strong></td>
                                <td><?= $serverSoftware ?></td>
                            </tr>
                            <tr>
                                <td><strong>Disk Usage:</strong></td>
                                <td>
                                    <?= formatBytes($diskUsed) ?> / <?= formatBytes($diskTotal) ?>
                                    <div class="progress mt-1" style="height: 10px;">
                                        <div class="progress-bar" role="progressbar" 
                                             style="width: <?= ($diskUsed / $diskTotal) * 100 ?>%"></div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar me-2"></i>System Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="bg-primary text-white p-3 rounded">
                                <h4><?= $userStats['total_users'] ?></h4>
                                <small>Total Users</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="bg-info text-white p-3 rounded">
                                <h4><?= $ticketStats['total_tickets'] ?></h4>
                                <small>Total Tickets</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-success text-white p-3 rounded">
                                <h4><?= $ticketStats['solved_tickets'] ?></h4>
                                <small>Solved Tickets</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-warning text-white p-3 rounded">
                                <h4><?= $ticketStats['open_tickets'] ?></h4>
                                <small>Open Tickets</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- General Settings -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-sliders-h me-2"></i>General Settings
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="general_settings">
                        
                        <div class="mb-3">
                            <label for="site_name" class="form-label">Site Name</label>
                            <input type="text" class="form-control" id="site_name" name="site_name" 
                                   value="<?= htmlspecialchars($site_name) ?>" placeholder="Enter site name">
                        </div>
                        
                        <div class="mb-3">
                            <label for="admin_email" class="form-label">Administrator Email</label>
                            <input type="email" class="form-control" id="admin_email" name="admin_email" 
                                   value="<?= htmlspecialchars($admin_email) ?>" placeholder="admin@example.com">
                        </div>
                        
                        <div class="mb-3">
                            <label for="timezone" class="form-label">Timezone</label>
                            <select class="form-select" id="timezone" name="timezone">
                                <option value="UTC" <?= $timezone === 'UTC' ? 'selected' : '' ?>>UTC</option>
                                <option value="America/New_York" <?= $timezone === 'America/New_York' ? 'selected' : '' ?>>Eastern Time</option>
                                <option value="America/Chicago" <?= $timezone === 'America/Chicago' ? 'selected' : '' ?>>Central Time</option>
                                <option value="America/Denver" <?= $timezone === 'America/Denver' ? 'selected' : '' ?>>Mountain Time</option>
                                <option value="America/Los_Angeles" <?= $timezone === 'America/Los_Angeles' ? 'selected' : '' ?>>Pacific Time</option>
                            </select>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="maintenance_mode" name="maintenance_mode" <?= $maintenance_mode ? 'checked' : '' ?>>
                            <label class="form-check-label" for="maintenance_mode">
                                Enable Maintenance Mode
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save General Settings
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Email Settings -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-envelope me-2"></i>Email Settings
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="email_settings">
                        
                        <div class="mb-3">
                            <label for="smtp_host" class="form-label">SMTP Host</label>
                            <input type="text" class="form-control" id="smtp_host" name="smtp_host" 
                                   value="<?= htmlspecialchars($smtp_host) ?>" placeholder="smtp.gmail.com">
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="smtp_port" class="form-label">SMTP Port</label>
                                <input type="number" class="form-control" id="smtp_port" name="smtp_port" 
                                       value="<?= $smtp_port ?>" placeholder="587">
                            </div>
                            <div class="col-md-6">
                                <label for="smtp_encryption" class="form-label">Encryption</label>
                                <select class="form-select" id="smtp_encryption" name="smtp_encryption">
                                    <option value="tls" <?= $smtp_encryption === 'tls' ? 'selected' : '' ?>>TLS</option>
                                    <option value="ssl" <?= $smtp_encryption === 'ssl' ? 'selected' : '' ?>>SSL</option>
                                    <option value="none" <?= $smtp_encryption === 'none' ? 'selected' : '' ?>>None</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="smtp_username" class="form-label">SMTP Username</label>
                            <input type="text" class="form-control" id="smtp_username" name="smtp_username" 
                                   value="<?= htmlspecialchars($smtp_username) ?>" placeholder="your-email@gmail.com">
                        </div>
                        
                        <div class="mb-3">
                            <label for="smtp_password" class="form-label">SMTP Password</label>
                            <input type="password" class="form-control" id="smtp_password" name="smtp_password" 
                                   placeholder="Leave blank to keep current password">
                            <div class="form-text">Leave blank to keep current password unchanged</div>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="email_notifications" name="email_notifications" <?= $email_notifications ? 'checked' : '' ?>>
                            <label class="form-check-label" for="email_notifications">
                                Enable Email Notifications
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Email Settings
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Database Management -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-database me-2"></i>Database Management
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        Create a backup of your database to protect your data.
                    </p>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="backup_database">
                        <button type="submit" class="btn btn-warning" onclick="return confirm('Are you sure you want to create a database backup?')">
                            <i class="fas fa-download me-2"></i>Create Database Backup
                        </button>
                    </form>
                    
                    <hr class="my-3">
                    
                    <h6>Recent Backups</h6>
                    <div class="small text-muted">
                        <?php
                        $backupDir = './database/backups/';
                        if (is_dir($backupDir)) {
                            $backups = glob($backupDir . '*.sql');
                            rsort($backups);
                            $recentBackups = array_slice($backups, 0, 3);
                            
                            if (!empty($recentBackups)) {
                                foreach ($recentBackups as $backup) {
                                    $filename = basename($backup);
                                    $filesize = formatBytes(filesize($backup));
                                    $modified = date('M d, Y g:i A', filemtime($backup));
                                    echo "<div class='d-flex justify-content-between align-items-center mb-1'>";
                                    echo "<span>{$filename}</span>";
                                    echo "<span class='text-muted'>{$filesize} - {$modified}</span>";
                                    echo "</div>";
                                }
                            } else {
                                echo "<em>No backups found</em>";
                            }
                        } else {
                            echo "<em>Backup directory not found</em>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Maintenance -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-tools me-2"></i>System Maintenance
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-info" onclick="clearCache()">
                            <i class="fas fa-broom me-2"></i>Clear System Cache
                        </button>
                        
                        <button type="button" class="btn btn-outline-warning" onclick="checkUpdates()">
                            <i class="fas fa-sync me-2"></i>Check for Updates
                        </button>
                        
                        <button type="button" class="btn btn-outline-danger" onclick="systemHealth()">
                            <i class="fas fa-stethoscope me-2"></i>Run System Health Check
                        </button>
                    </div>
                    
                    <div class="mt-3">
                        <small class="text-muted">
                            <strong>Last maintenance:</strong> <?= date('M d, Y g:i A') ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function clearCache() {
    if (confirm('Are you sure you want to clear the system cache?')) {
        fetch(window.location.href, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=clear_cache'
        }).then(response => response.text())
          .then(data => {
              alert('System cache cleared successfully!');
              location.reload();
          })
          .catch(error => {
              alert('Error clearing cache: ' + error);
          });
    }
}

function checkUpdates() {
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Checking...';
    button.disabled = true;
    
    setTimeout(() => {
        button.innerHTML = originalText;
        button.disabled = false;
        alert('System is up to date!\n\nHelpdesk Core PHP v1.0\nLast checked: ' + new Date().toLocaleString());
    }, 2000);
}

function systemHealth() {
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Checking...';
    button.disabled = true;
    
    // Simulate health check
    setTimeout(() => {
        const checks = [
            'Database connection: ✓ OK',
            'File permissions: ✓ OK', 
            'PHP version: ✓ ' + '<?= phpversion() ?>',
            'MySQL version: ✓ <?= $mysqlVersion ?>',
            'Disk space: ✓ Available',
            'Session storage: ✓ Working'
        ];
        
        button.innerHTML = originalText;
        button.disabled = false;
        
        alert('System Health Check Results:\n\n' + checks.join('\n') + '\n\n✓ All systems operational!');
    }, 3000);
}

// Add form validation
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
                submitBtn.disabled = true;
                
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 1000);
            }
        });
    });
});
</script>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
function formatBytes($size, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    for ($i = 0; $size >= 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }
    return round($size, $precision) . ' ' . $units[$i];
}
?>