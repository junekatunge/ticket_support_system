<?php
// Navbar component for ICT Helpdesk
// Get current page name for active navigation
$current_page = basename($_SERVER['PHP_SELF']);

// Include user session data if not already included
if (!isset($user) && isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
}
?>

<style>
/* Sticky Navbar Styles */
.navbar-main {
    position: fixed;
    top: 0;
    left: var(--sidebar-w, 280px);
    right: 0;
    z-index: 1020;
    background: #ffffff;
    height: 60px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.04);
    border-bottom: 1px solid #e2e8f0;
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
    color: #1e293b;
    text-decoration: none;
}

.navbar-brand:hover {
    color: #3b82f6;
    text-decoration: none;
}

.navbar-brand .brand-logo {
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 14px;
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
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
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
    color: #64748b;
    text-decoration: none;
    transition: all 0.2s ease;
}

.navbar-notifications:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
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
    color: #1e293b;
    position: relative;
}

.navbar-user:hover {
    background: #f1f5f9;
    color: #1e293b;
    text-decoration: none;
}

.user-avatar {
    width: 36px;
    height: 36px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 14px;
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
    color: #64748b;
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
    background: #ef4444;
    border-color: #ef4444;
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
</style>

<nav class="navbar-main">
    <div class="navbar-content">
        <!-- Page Title Only -->
        <div class="navbar-brand">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 4px; height: 28px; background: linear-gradient(180deg, #667eea, #764ba2); border-radius: 2px;"></div>
                <div>
                    <h5 style="margin: 0; font-size: 1.25rem; font-weight: 700; background: linear-gradient(135deg, #667eea, #764ba2); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; letter-spacing: -0.025em;">
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
                        }
                        echo $page_name;
                        ?>
                    </h5>
                    <p style="margin: 0; font-size: 0.75rem; color: #94a3b8; font-weight: 500;">
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
            <a href="#" class="navbar-notifications" title="Notifications">
                <i class="fas fa-bell"></i>
                <span class="notification-badge">3</span>
            </a>
            
            <!-- User Profile -->
            <div class="dropdown">
                <a href="#" class="navbar-user" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="user-avatar">
                        <?php 
                        $initials = 'U';
                        if (isset($user->name)) {
                            $names = explode(' ', trim($user->name));
                            $initials = strtoupper(substr($names[0], 0, 1));
                            if (count($names) > 1) {
                                $initials .= strtoupper(substr(end($names), 0, 1));
                            }
                        }
                        echo $initials;
                        ?>
                    </div>
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($user->name ?? 'User'); ?></div>
                        <div class="user-role"><?php echo htmlspecialchars($user->role ?? 'member'); ?></div>
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