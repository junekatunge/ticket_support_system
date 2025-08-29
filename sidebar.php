<?php
// Get current page name for active navigation highlighting
$current_page = basename($_SERVER['PHP_SELF']);

// Handle incomplete User object safely
$userName = 'User';
$userRole = 'user';
$userInitials = 'U';

if (isset($user)) {
    // Handle both complete and incomplete User objects
    try {
        $userName = (is_object($user) && isset($user->name) && $user->name) ? $user->name : 'User';
        $userRole = (is_object($user) && isset($user->role) && $user->role) ? $user->role : 'user';
        
        if ($userName && $userName !== 'User' && strlen($userName) >= 2) {
            $userInitials = strtoupper(substr($userName, 0, 2));
        } else if ($userName && $userName !== 'User' && strlen($userName) >= 1) {
            $userInitials = strtoupper(substr($userName, 0, 1));
        }
    } catch (Exception $e) {
        // Fallback to defaults if any error occurs
        $userName = 'User';
        $userRole = 'user';
        $userInitials = 'U';
    }
}
?>

<style>
  :root { 
    --sidebar-w: 280px;
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
  .sidebar { 
    background: linear-gradient(180deg, var(--treasury-brown) 0%, var(--treasury-dark) 100%);
    color: #e2e8f0;
    padding: 1.5rem 1.25rem 1.5rem 1.5rem;
    box-shadow: 2px 0 15px rgba(139, 69, 19, 0.3);
    border-right: 1px solid var(--treasury-tan);
    height: 100vh;
    overflow-y: auto;
    width: var(--sidebar-w);
    flex-shrink: 0;
  }
  .brand { 
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: var(--treasury-tan);
    text-decoration: none;
    font-weight: 700;
    margin-bottom: 2rem;
    padding: 0.5rem;
  }
  .brand .logo { 
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--treasury-tan) 0%, var(--treasury-gold) 100%);
    border-radius: 10px;
    color: var(--treasury-brown);
    box-shadow: 0 2px 8px rgba(210, 180, 140, 0.4);
    padding: 4px;
  }
  .nav-section { margin-top: 1rem; }
  .nav-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--treasury-tan);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 1.5rem 0 0.75rem 0.75rem;
  }
  .side-link { 
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    border-radius: 10px;
    color: #cbd5e1;
    text-decoration: none;
    transition: all 0.15s ease;
    font-weight: 500;
    margin-bottom: 0.25rem;
    position: relative;
  }
  
  .side-link i {
    color: var(--treasury-tan);
    transition: color 0.15s ease;
  }
  .side-link:hover { 
    background: rgba(210, 180, 140, 0.15);
    color: var(--treasury-tan);
    transform: translateX(2px);
  }
  
  .side-link:hover i {
    color: var(--treasury-gold);
  }
  .side-link.active { 
    background: linear-gradient(135deg, var(--treasury-tan), var(--treasury-gold));
    color: var(--treasury-brown);
    box-shadow: 0 2px 8px rgba(210, 180, 140, 0.4);
    font-weight: 600;
  }
  
  .side-link.active i {
    color: var(--treasury-brown);
  }
  .side-link.active::before {
    content: '';
    position: absolute;
    left: -1.25rem;
    top: 50%;
    transform: translateY(-50%);
    width: 3px;
    height: 20px;
    background: var(--treasury-tan);
    border-radius: 2px;
  }
  .user-profile-section {
    margin-top: 2rem;
    padding: 1rem;
    background: linear-gradient(135deg, rgba(210, 180, 140, 0.1), rgba(201, 169, 110, 0.05));
    border-radius: 12px;
    border: 1px solid var(--treasury-tan);
  }
  
  @media (max-width: 992px) { 
    :root { --sidebar-w: 70px; }
    .brand div { display: none; }
    .nav-label { display: none; }
    .side-link span { display: none; }
    .side-link { justify-content: center; padding: 0.75rem; }
    .user-profile-section { 
      padding: 0.5rem;
      text-align: center;
    }
    .user-profile-section .d-flex { flex-direction: column; }
    .user-profile-section .btn { margin-top: 0.5rem; }
    .user-profile-section div:last-child { display: none; }
  }
</style>

<!-- SIDEBAR -->
<aside class="sidebar">
  <a href="dashboard.php" class="brand">
    <span class="logo"><img src="images/tnt.logo" alt="TNT Logo" style="width: 100%; height: 100%; object-fit: contain;"></span>
    <div>
      <div style="font-size: 1.1rem;">Helpdesk</div>
      <div style="font-size: 0.7rem; color: #64748b; font-weight: 400;">Support System</div>
    </div>
  </a>
  <nav class="nav-section">
    <!-- Overview Section -->
    <div class="nav-label">Overview</div>
    <a class="side-link <?= ($current_page == 'dashboard.php') ? 'active' : '' ?>" href="dashboard.php">
      <i class="fas fa-chart-line"></i><span>Dashboard</span>
    </a>
    
    <!-- Tickets Section -->
    <div class="nav-label">Tickets</div>
    <a class="side-link <?= ($current_page == 'open.php') ? 'active' : '' ?>" href="open.php">
      <i class="fas fa-folder-open"></i><span>Open Tickets</span>
    </a>
    <a class="side-link <?= ($current_page == 'pending.php') ? 'active' : '' ?>" href="pending.php">
      <i class="fas fa-clock"></i><span>Pending</span>
    </a>
    <a class="side-link <?= ($current_page == 'solved.php') ? 'active' : '' ?>" href="solved.php">
      <i class="fas fa-check-circle"></i><span>Solved</span>
    </a>
    <a class="side-link <?= ($current_page == 'closed.php') ? 'active' : '' ?>" href="closed.php">
      <i class="fas fa-times-circle"></i><span>Closed</span>
    </a>
    <a class="side-link <?= ($current_page == 'unassigned.php') ? 'active' : '' ?>" href="unassigned.php">
      <i class="fas fa-exclamation-triangle"></i><span>Unassigned</span>
    </a>
    
    <!-- Personal Section -->
    <div class="nav-label">Personal</div>
    <a class="side-link <?= ($current_page == 'mytickets.php') ? 'active' : '' ?>" href="mytickets.php">
      <i class="fas fa-user-circle"></i><span>My Tickets</span>
    </a>
    <a class="side-link <?= ($current_page == 'profile.php') ? 'active' : '' ?>" href="profile.php">
      <i class="fas fa-user-edit"></i><span>My Profile</span>
    </a>
    
    <!-- Management Section -->
    <div class="nav-label">Management</div>
    <a class="side-link <?= ($current_page == 'team.php') ? 'active' : '' ?>" href="team.php">
      <i class="fas fa-users"></i><span>Teams</span>
    </a>
    <a class="side-link <?= ($current_page == 'users.php') ? 'active' : '' ?>" href="users.php">
      <i class="fas fa-user-cog"></i><span>Users</span>
    </a>
    <?php if ($userRole == 'admin'): ?>
    <a class="side-link <?= ($current_page == 'settings.php') ? 'active' : '' ?>" href="settings.php">
      <i class="fas fa-cog"></i><span>Settings</span>
    </a>
    <?php endif; ?>
    
    <!-- Reports Section -->
    <div class="nav-label">Reports</div>
    <a class="side-link <?= ($current_page == 'reports.php') ? 'active' : '' ?>" href="reports.php">
      <i class="fas fa-chart-bar"></i><span>Analytics</span>
    </a>
    
    <!-- User Profile Section -->
    <div class="user-profile-section">
      <div class="d-flex align-items-center mb-2">
        <div style="width: 32px; height: 32px; background: linear-gradient(135deg, var(--treasury-tan), var(--treasury-gold)); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--treasury-brown); font-weight: 600; margin-right: 0.75rem;">
          <?= $userInitials ?>
        </div>
        <div>
          <div style="font-weight: 600; font-size: 0.9rem; color: var(--treasury-tan);"><?= htmlspecialchars($userName) ?></div>
          <div style="font-size: 0.75rem; color: #cbd5e1;"><?= htmlspecialchars(ucfirst($userRole)) ?></div>
        </div>
      </div>
      <a class="btn btn-outline-primary btn-sm w-100" href="signout.php" style="border-radius: 8px; font-size: 0.8rem;">
        <i class="fas fa-sign-out-alt me-1"></i>Sign Out
      </a>
    </div>
  </nav>
</aside>