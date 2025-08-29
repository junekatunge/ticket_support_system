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

$confirmed = isset($_POST['confirmed']) && $_POST['confirmed'] === 'yes';

if ($confirmed) {
    // Log the signout action
    $logMessage = "User {$user->name} (ID: {$user->id}) signed out at " . date('Y-m-d H:i:s');
    error_log($logMessage);
    
    // Clear session data
    session_start();
    $_SESSION = array(); // Clear all session data
    
    // Delete the session cookie if it exists
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Clear any remember me cookies if they exist
    $cookiesToClear = ['remember_token', 'user_id', 'auth_token'];
    foreach ($cookiesToClear as $cookie) {
        if (isset($_COOKIE[$cookie])) {
            setcookie($cookie, '', time() - 3600, '/');
        }
    }
    
    // Destroy the session
    session_destroy();
    
    // Redirect to login with logout message
    header('Location: ./index.php?message=logged_out&user=' . urlencode($user->name));
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Out - Helpdesk</title>
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
      
      body {
        background: var(--treasury-light);
      }
      
      .card {
        box-shadow: 0 4px 12px rgba(30, 58, 95, 0.15);
      }
      
      .text-warning {
        color: var(--treasury-tan) !important;
      }
      
      .btn-danger {
        background: linear-gradient(135deg, var(--treasury-burgundy) 0%, #8b3842 100%);
        border: none;
        box-shadow: 0 2px 4px rgba(114, 47, 55, 0.3);
      }
      
      .btn-danger:hover {
        background: linear-gradient(135deg, #8b3842 0%, var(--treasury-burgundy) 100%);
        box-shadow: 0 4px 8px rgba(114, 47, 55, 0.4);
        transform: translateY(-1px);
      }
      
      .btn-outline-secondary {
        color: var(--treasury-navy);
        border-color: var(--treasury-navy);
      }
      
      .btn-outline-secondary:hover {
        background-color: var(--treasury-navy);
        border-color: var(--treasury-navy);
      }
      
      .alert-info {
        background-color: rgba(74, 144, 164, 0.1);
        border-color: var(--treasury-blue);
        color: var(--treasury-navy);
      }
      
      .text-primary {
        color: var(--treasury-navy) !important;
      }
      
      .bg-light {
        background-color: rgba(201, 169, 110, 0.1) !important;
      }
      
      .fa-sign-out-alt.text-warning {
        color: var(--treasury-tan) !important;
      }
      
      .fa-info-circle, .fa-bolt {
        color: var(--treasury-blue);
      }
      
      .fa-arrow-left {
        color: var(--treasury-navy);
      }
      
      .fa-user-circle, .fa-shield-alt, .fa-clock, .fa-ticket, .fa-user-cog {
        color: var(--treasury-brown);
      }
    </style>
</head>
<body>
    <div class="d-flex">
        <?php include 'sidebar.php'; ?>
        
        <div style="flex: 1; padding: 2rem; width: 100%;">
            <div class="container-fluid" style="max-width: none; padding: 0;">
    <div class="row justify-content-center align-items-center" style="min-height: 60vh;">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5 text-center">
                    <div class="mb-4">
                        <i class="fas fa-sign-out-alt text-warning" style="font-size: 4rem;"></i>
                    </div>
                    
                    <h3 class="card-title mb-3 text-dark">Sign Out</h3>
                    
                    <p class="text-muted mb-4">
                        Are you sure you want to sign out of your account?<br>
                        <strong><?= htmlspecialchars($user->name) ?></strong>
                    </p>
                    
                    <div class="mb-3">
                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>What happens when you sign out:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Your session will be completely cleared</li>
                                <li>All cookies will be removed</li>
                                <li>You'll need to sign in again to access the system</li>
                            </ul>
                        </div>
                    </div>
                    
                    <form method="POST" action="">
                        <div class="d-grid gap-2">
                            <button type="submit" name="confirmed" value="yes" class="btn btn-danger">
                                <i class="fas fa-sign-out-alt me-2"></i>Yes, Sign Me Out
                            </button>
                            
                            <a href="dashboard.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                    
                    <div class="mt-4 pt-3 border-top">
                        <div class="row text-center">
                            <div class="col-4">
                                <small class="text-muted">
                                    <i class="fas fa-user-circle d-block mb-1"></i>
                                    Profile
                                </small>
                            </div>
                            <div class="col-4">
                                <small class="text-muted">
                                    <i class="fas fa-shield-alt d-block mb-1"></i>
                                    Secure
                                </small>
                            </div>
                            <div class="col-4">
                                <small class="text-muted">
                                    <i class="fas fa-clock d-block mb-1"></i>
                                    Session
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <small class="text-muted">
                            Last login: <?= date('M d, Y \a\t g:i A') ?>
                        </small>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions Card -->
            <div class="card shadow-sm border-0 mt-3">
                <div class="card-body p-3">
                    <h6 class="card-title mb-3 text-center">
                        <i class="fas fa-bolt me-1"></i>Before you go...
                    </h6>
                    
                    <div class="row text-center">
                        <div class="col-6">
                            <a href="mytickets.php" class="text-decoration-none">
                                <div class="p-2 bg-light rounded">
                                    <i class="fas fa-ticket text-primary d-block mb-1"></i>
                                    <small>My Tickets</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="profile.php" class="text-decoration-none">
                                <div class="p-2 bg-light rounded">
                                    <i class="fas fa-user-cog text-info d-block mb-1"></i>
                                    <small>Profile</small>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border-radius: 15px;
}

.card-body {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 15px;
}

.btn {
    border-radius: 10px;
    padding: 12px 24px;
    font-weight: 500;
}

.btn-danger {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    border: none;
}

.btn-danger:hover {
    background: linear-gradient(135deg, #c82333 0%, #bd2130 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
}

.btn-outline-secondary {
    border-color: #6c757d;
    color: #6c757d;
}

.btn-outline-secondary:hover {
    background-color: #6c757d;
    transform: translateY(-1px);
}

.bg-light {
    background-color: #f8f9fa !important;
    transition: all 0.3s ease;
}

.bg-light:hover {
    background-color: #e9ecef !important;
    transform: translateY(-1px);
}

.text-muted {
    color: #6c757d !important;
}

@media (max-width: 768px) {
    .container-fluid {
        padding: 15px;
    }
    
    .card-body {
        padding: 2rem !important;
    }
}
</style>

<script>
// Enhanced signout functionality
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const signoutBtn = document.querySelector('button[name="confirmed"]');
    const cancelBtn = document.querySelector('a[href="dashboard.php"]');
    
    // Add confirmation dialog for extra security
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const confirmModal = confirm('⚠️ FINAL CONFIRMATION\n\nThis will immediately:\n• End your current session\n• Clear all stored data\n• Redirect you to the login page\n\nAre you absolutely sure you want to sign out?');
        
        if (confirmModal) {
            signoutBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Signing out...';
            signoutBtn.disabled = true;
            cancelBtn.style.display = 'none';
            
            // Add a small delay to show the loading state
            setTimeout(() => {
                form.submit();
            }, 1000);
        }
    });
    
    // Auto-focus on the cancel button initially (safer default)
    setTimeout(function() {
        cancelBtn.focus();
    }, 500);
    
    // Add keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            window.location.href = 'dashboard.php';
        } else if (e.key === 'Enter' && e.shiftKey) {
            signoutBtn.click();
        }
    });
    
    // Add visual feedback for keyboard shortcuts
    const shortcutInfo = document.createElement('div');
    shortcutInfo.className = 'text-muted text-center mt-2';
    shortcutInfo.innerHTML = '<small><kbd>Esc</kbd> to cancel • <kbd>Shift+Enter</kbd> to confirm</small>';
    form.appendChild(shortcutInfo);
});
</script>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>