<?php
// Navbar component for ICT Helpdesk
// Get current page name for active navigation
$current_page = basename($_SERVER['PHP_SELF']);

// Include user session data if not already included
if (!isset($user) && isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
}

// Include notification system
require_once __DIR__ . '/src/database.php';
require_once __DIR__ . '/src/notification.php';

// Get notification count
$notificationCount = Notification::getUnreadCount();
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

/* Sticky Navbar Styles */
.navbar-main {
    position: fixed;
    top: 0;
    left: var(--sidebar-w, 280px);
    right: 0;
    z-index: 1020;
    background: linear-gradient(135deg, var(--treasury-light) 0%, #ffffff 100%);
    height: 60px;
    box-shadow: 0 2px 8px rgba(30, 58, 95, 0.15), 0 1px 3px rgba(30, 58, 95, 0.08);
    border-bottom: 2px solid var(--treasury-gold);
    transition: left 0.3s ease;
}

.navbar-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 100%;
    padding: 0 1.5rem;
    max-width: 100%;
}

.navbar-brand {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 700;
    font-size: 1.1rem;
    color: var(--treasury-navy);
    text-decoration: none;
}

.navbar-brand:hover {
    color: var(--treasury-gold);
    text-decoration: none;
}

.navbar-brand .brand-logo {
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, var(--treasury-tan) 0%, var(--treasury-brown) 100%);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--treasury-light);
    font-size: 14px;
    box-shadow: 0 2px 4px rgba(210, 180, 140, 0.3);
}

.navbar-search {
    flex: 1;
    max-width: 400px;
    margin: 0 2rem;
}

.search-input {
    width: 100%;
    border: 1px solid #e2e8f0;
    border-radius: 25px;
    padding: 8px 16px 8px 40px;
    font-size: 0.9rem;
    background: #f8fafc;
    transition: all 0.2s ease;
    outline: none;
}

.search-input:focus {
    background: #ffffff;
    border-color: var(--treasury-tan);
    box-shadow: 0 0 0 3px rgba(210, 180, 140, 0.15);
}

.search-wrapper {
    position: relative;
}

.search-wrapper .search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 14px;
}

.navbar-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.navbar-notifications {
    position: relative;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--treasury-brown);
    text-decoration: none;
    transition: all 0.2s ease;
}

.navbar-notifications:hover {
    background: linear-gradient(135deg, var(--treasury-tan) 0%, var(--treasury-brown) 100%);
    color: var(--treasury-light);
    border-color: transparent;
    text-decoration: none;
}

.navbar-notifications .notification-badge {
    position: absolute;
    top: -2px;
    right: -2px;
    background: #ef4444;
    color: white;
    border-radius: 50%;
    width: 16px;
    height: 16px;
    font-size: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.navbar-user {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.5rem;
    border-radius: 12px;
    transition: all 0.2s ease;
    text-decoration: none;
    color: var(--treasury-navy);
    position: relative;
}

.navbar-user:hover {
    background: rgba(210, 180, 140, 0.1);
    color: var(--treasury-navy);
    text-decoration: none;
}

.user-avatar {
    width: 36px;
    height: 36px;
    background: linear-gradient(135deg, var(--treasury-tan) 0%, var(--treasury-brown) 100%);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--treasury-light);
    font-weight: 600;
    font-size: 14px;
    box-shadow: 0 2px 4px rgba(210, 180, 140, 0.3);
}

.user-info {
    display: flex;
    flex-direction: column;
}

.user-name {
    font-weight: 600;
    font-size: 0.9rem;
    line-height: 1.2;
}

.user-role {
    font-size: 0.75rem;
    color: var(--treasury-blue);
    text-transform: capitalize;
}

.logout-btn {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 0.5rem 0.75rem;
    color: #64748b;
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s ease;
}

.logout-btn:hover {
    background: var(--treasury-burgundy);
    border-color: var(--treasury-burgundy);
    color: white;
    text-decoration: none;
}

/* Responsive Design */
@media (max-width: 992px) {
    .navbar-main {
        left: 70px;
    }
    
    .navbar-search {
        max-width: 200px;
        margin: 0 1rem;
    }
    
    .user-info {
        display: none;
    }
    
    .navbar-brand span {
        display: none;
    }
}

@media (max-width: 768px) {
    .navbar-main {
        left: 0;
    }
    
    .navbar-search {
        display: none;
    }
    
    .navbar-content {
        padding: 0 1rem;
    }
    
    .navbar-actions {
        gap: 0.5rem;
    }
}

/* Adjust content area for navbar */
.content-with-navbar {
    padding-top: 60px !important;
    margin-top: 0 !important;
}

/* Mobile sidebar overlay fix */
@media (max-width: 768px) {
    .navbar-main {
        position: fixed;
        left: 0;
        right: 0;
    }
    
    .content-with-navbar {
        padding-top: 60px !important;
    }
}

/* Notification Dropdown Styles */
.notifications-dropdown {
    border: none;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(30, 58, 95, 0.15);
}

.notification-item {
    padding: 12px 16px;
    border-bottom: 1px solid #f1f3f4;
    transition: background-color 0.2s ease;
}

.notification-item:hover {
    background-color: rgba(210, 180, 140, 0.05);
}

.notification-item:last-child {
    border-bottom: none;
}

.notification-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 14px;
}

.notification-title {
    font-weight: 600;
    font-size: 0.875rem;
    color: var(--treasury-navy);
    margin-bottom: 2px;
}

.notification-subtitle {
    font-size: 0.8rem;
    color: var(--treasury-blue);
    margin-bottom: 2px;
}

.notification-meta {
    font-size: 0.75rem;
    color: #6c757d;
}

.notification-badge {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}
</style>

<nav class="navbar-main">
    <div class="navbar-content">
        <!-- Page Title Only -->
        <div class="navbar-brand">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 4px; height: 28px; background: linear-gradient(180deg, var(--treasury-tan), var(--treasury-brown)); border-radius: 2px;"></div>
                <div>
                    <h5 style="margin: 0; font-size: 1.25rem; font-weight: 700; background: linear-gradient(135deg, var(--treasury-brown), var(--treasury-tan)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; letter-spacing: -0.025em;">
                        <?php 
                        // Determine page name based on current page
                        $page_name = 'ICT Helpdesk';
                        switch($current_page) {
                            case 'dashboard.php': $page_name = 'Dashboard'; break;
                            case 'open.php': $page_name = 'Open Tickets'; break;
                            case 'pending.php': $page_name = 'Pending Tickets'; break;
                            case 'solved.php': $page_name = 'Solved Tickets'; break;
                            case 'closed.php': $page_name = 'Closed Tickets'; break;
                            case 'unassigned.php': $page_name = 'Unassigned Tickets'; break;
                            case 'mytickets.php': $page_name = 'My Tickets'; break;
                            case 'team.php': $page_name = 'Teams'; break;
                            case 'users.php': $page_name = 'Users'; break;
                            case 'reports.php': $page_name = 'Reports & Analytics'; break;
                        }
                        echo $page_name;
                        ?>
                    </h5>
                    <p style="margin: 0; font-size: 0.75rem; color: var(--treasury-blue); font-weight: 500;">
                        <?php echo date('l, F j, Y'); ?>
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Spacer -->
        <div style="flex: 1;"></div>
        
        <!-- Actions -->
        <div class="navbar-actions">
            <!-- Notifications -->
            <div class="dropdown">
                <a href="#" class="navbar-notifications" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
                    <i class="fas fa-bell"></i>
                    <?php if ($notificationCount > 0): ?>
                        <span class="notification-badge"><?= $notificationCount > 99 ? '99+' : $notificationCount ?></span>
                    <?php endif; ?>
                </a>
                
                <ul class="dropdown-menu dropdown-menu-end notifications-dropdown shadow-lg" style="width: 350px; max-height: 400px; overflow-y: auto;">
                    <li><h6 class="dropdown-header d-flex justify-content-between align-items-center">
                        <span>Notifications</span>
                        <small class="text-muted"><?= $notificationCount ?> unread</small>
                    </h6></li>
                    
                    <?php 
                    $notifications = Notification::getRecentNotifications();
                    if (empty($notifications)): 
                    ?>
                        <li><div class="dropdown-item-text text-center py-4">
                            <i class="fas fa-bell-slash text-muted mb-2" style="font-size: 2rem;"></i>
                            <p class="text-muted mb-0">No new notifications</p>
                        </div></li>
                    <?php else: ?>
                        <?php foreach ($notifications as $notification): ?>
                            <li>
                                <a class="dropdown-item notification-item" href="ticket-details.php?id=<?= $notification['id'] ?>">
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
                                            <div class="notification-title"><?= htmlspecialchars($notification['notification_message']) ?></div>
                                            <div class="notification-subtitle">
                                                #<?= $notification['id'] ?> - <?= htmlspecialchars($notification['subject']) ?>
                                            </div>
                                            <div class="notification-meta">
                                                <?= htmlspecialchars($notification['requester_name'] ?? 'Unknown') ?> • 
                                                <?= Notification::timeAgo($notification['created_at']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        <?php endforeach; ?>
                        
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-center text-primary" href="notifications.php">
                            <i class="fas fa-eye me-1"></i>View All Notifications
                        </a></li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <!-- User Profile -->
            <div class="dropdown">
                <a href="#" class="navbar-user" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="user-avatar">
                        <?php 
                        $initials = 'U';
                        $userName = 'User';
                        $userRole = 'member';
                        
                        if (isset($user) && is_object($user)) {
                            $userName = isset($user->name) && $user->name ? $user->name : 'User';
                            $userRole = isset($user->role) && $user->role ? $user->role : 'member';
                            
                            if ($userName && $userName !== 'User') {
                                $names = explode(' ', trim($userName));
                                $initials = strtoupper(substr($names[0], 0, 1));
                                if (count($names) > 1) {
                                    $initials .= strtoupper(substr(end($names), 0, 1));
                                }
                            }
                        }
                        echo $initials;
                        ?>
                    </div>
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($userName); ?></div>
                        <div class="user-role"><?php echo htmlspecialchars($userRole); ?></div>
                    </div>
                    <i class="fas fa-chevron-down" style="font-size: 12px; color: #94a3b8;"></i>
                </a>
                
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li><h6 class="dropdown-header">Account</h6></li>
                    <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profile</a></li>
                    <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Sign Out</a></li>
                </ul>
            </div>
            
            <!-- Quick Logout Button (visible on smaller screens) -->
            <a href="logout.php" class="logout-btn d-md-none">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>
</nav>

<!-- Initialize Notification System -->
<script>
// Simple notification functionality without external dependencies
document.addEventListener('DOMContentLoaded', function() {
    // Update notification count every 30 seconds
    function updateNotificationCount() {
        fetch('api/get-notification-count.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const badge = document.querySelector('.notification-badge');
                    const bellIcon = document.querySelector('.navbar-notifications i');
                    
                    if (data.count > 0) {
                        if (badge) {
                            badge.textContent = data.count > 99 ? '99+' : data.count;
                        } else if (bellIcon) {
                            const newBadge = document.createElement('span');
                            newBadge.className = 'notification-badge';
                            newBadge.textContent = data.count > 99 ? '99+' : data.count;
                            bellIcon.parentNode.appendChild(newBadge);
                        }
                    } else {
                        if (badge) {
                            badge.remove();
                        }
                    }
                }
            })
            .catch(error => console.log('Notification update error:', error));
    }
    
    // Update immediately and then every 30 seconds
    updateNotificationCount();
    setInterval(updateNotificationCount, 30000);
});
</script>