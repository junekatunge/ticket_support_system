<?php
session_start();
if (!isset($_SESSION['logged-in']) || $_SESSION['logged-in'] == false) {
    header('Location: ./index.php');
    exit();
}

require_once './src/database.php';
require_once './src/notification.php';
require_once './src/user.php';

$user = $_SESSION['user'];

// Get all notifications
$notifications = Notification::getRecentNotifications(null, 50);
$notificationCount = Notification::getUnreadCount();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Helpdesk</title>
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
      }
      
      body {
        background: var(--treasury-light);
      }
      
      .notification-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 2px 8px rgba(30, 58, 95, 0.08);
        transition: transform 0.2s, box-shadow 0.2s;
        margin-bottom: 1rem;
      }
      
      .notification-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(30, 58, 95, 0.15);
      }
      
      .notification-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 16px;
      }
      
      .page-header {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 2px 8px rgba(30, 58, 95, 0.08);
      }
      
      .btn-primary {
        background: linear-gradient(135deg, var(--treasury-brown) 0%, var(--treasury-tan) 100%);
        border: none;
        box-shadow: 0 2px 4px rgba(139, 69, 19, 0.3);
      }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="d-flex">
        <?php include 'sidebar.php'; ?>
        
        <div style="flex: 1; padding: 2rem; width: 100%;">
            <div class="container-fluid" style="max-width: none; padding: 0;">
                <!-- Page Header -->
                <div class="page-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h3 mb-1 text-gray-800">
                                <i class="fas fa-bell me-2" style="color: var(--treasury-brown);"></i>Notifications
                            </h1>
                            <p class="mb-0 text-muted">Stay updated with important ticket alerts and system updates</p>
                        </div>
                        <div>
                            <span class="badge bg-primary" style="background: var(--treasury-brown) !important;">
                                <?= $notificationCount ?> Unread
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Notifications List -->
                <?php if (empty($notifications)): ?>
                    <div class="notification-card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-bell-slash text-muted mb-3" style="font-size: 3rem;"></i>
                            <h5 class="text-muted">No notifications</h5>
                            <p class="text-muted mb-0">You're all caught up! No new notifications at this time.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($notifications as $notification): ?>
                        <div class="notification-card">
                            <div class="card-body">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="notification-icon" style="background: <?= Notification::getBadgeColor($notification['notification_type']) ?>;">
                                            <?php if ($notification['notification_type'] === 'urgent'): ?>
                                                <i class="fas fa-exclamation"></i>
                                            <?php elseif ($notification['notification_type'] === 'high'): ?>
                                                <i class="fas fa-arrow-up"></i>
                                            <?php elseif ($notification['notification_type'] === 'overdue'): ?>
                                                <i class="fas fa-clock"></i>
                                            <?php else: ?>
                                                <i class="fas fa-ticket"></i>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="mb-0" style="color: var(--treasury-navy);">
                                                <?= htmlspecialchars($notification['notification_message']) ?>
                                            </h6>
                                            <small class="text-muted"><?= Notification::timeAgo($notification['created_at']) ?></small>
                                        </div>
                                        
                                        <p class="mb-2" style="color: var(--treasury-blue);">
                                            <strong>Ticket #<?= $notification['id'] ?>:</strong> 
                                            <?= htmlspecialchars($notification['subject']) ?>
                                        </p>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <small class="text-muted">
                                                    <i class="fas fa-user me-1"></i>
                                                    <?= htmlspecialchars($notification['requester_name'] ?? 'Unknown') ?>
                                                </small>
                                                <small class="text-muted ms-3">
                                                    <i class="fas fa-tag me-1"></i>
                                                    <?= ucfirst($notification['priority']) ?> Priority
                                                </small>
                                                <small class="text-muted ms-3">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    <?= ucfirst($notification['status']) ?>
                                                </small>
                                            </div>
                                            
                                            <a href="ticket-details.php?id=<?= $notification['id'] ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-eye me-1"></i>View Ticket
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>