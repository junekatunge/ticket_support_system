<?php
// Get current page name for active navigation highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>

<style>
  :root { --sidebar-w: 280px; }
  .sidebar { 
    background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    color: #475569;
    padding: 1.5rem 1.25rem 1.5rem 1.5rem;
    box-shadow: 2px 0 10px rgba(0,0,0,0.05);
    border-right: 1px solid #e2e8f0;
    height: 100vh;
    overflow-y: auto;
    width: var(--sidebar-w);
    flex-shrink: 0;
  }
  .brand { 
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: #1e293b;
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
    background: white;
    border-radius: 10px;
    color: white;
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
    padding: 4px;
  }
  .nav-section { margin-top: 1rem; }
  .nav-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #94a3b8;
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
    color: #64748b;
    text-decoration: none;
    transition: all 0.15s ease;
    font-weight: 500;
    margin-bottom: 0.25rem;
    position: relative;
  }
  .side-link:hover { 
    background: #f1f5f9;
    color: #334155;
    transform: translateX(2px);
  }
  .side-link.active { 
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: white;
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
  }
  .side-link.active::before {
    content: '';
    position: absolute;
    left: -1.25rem;
    top: 50%;
    transform: translateY(-50%);
    width: 3px;
    height: 20px;
    background: #3b82f6;
    border-radius: 2px;
  }
  .user-profile-section {
    margin-top: 2rem;
    padding: 1rem;
    background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
    border-radius: 12px;
    border: 1px solid #e2e8f0;
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
    
    <!-- Management Section -->
    <div class="nav-label">Management</div>
    <a class="side-link <?= ($current_page == 'team.php') ? 'active' : '' ?>" href="team.php">
      <i class="fas fa-users"></i><span>Teams</span>
    </a>
    <a class="side-link <?= ($current_page == 'users.php') ? 'active' : '' ?>" href="users.php">
      <i class="fas fa-user-cog"></i><span>Users</span>
    </a>
    
    <!-- User Profile Section -->
    <div class="user-profile-section">
      <div class="d-flex align-items-center mb-2">
        <div style="width: 32px; height: 32px; background: linear-gradient(135deg, #3b82f6, #1d4ed8); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; margin-right: 0.75rem;">
          JD
        </div>
        <div>
          <div style="font-weight: 600; font-size: 0.9rem; color: #1e293b;">John Doe</div>
          <div style="font-size: 0.75rem; color: #64748b;">Administrator</div>
        </div>
      </div>
      <a class="btn btn-outline-primary btn-sm w-100" href="index.php" style="border-radius: 8px; font-size: 0.8rem;">
        <i class="fas fa-sign-out-alt me-1"></i>Sign Out
      </a>
    </div>
  </nav>
</aside>