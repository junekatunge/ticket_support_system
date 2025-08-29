<?php
require_once './src/Database.php';
require_once './src/team.php';
require_once './src/user.php';

session_start();
if (!isset($_SESSION['logged-in']) || $_SESSION['logged-in'] == false) {
    header('Location: ./index.php');
    exit();
}
$user = $_SESSION['user'];

$db = Database::getInstance();
$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priority = $_POST['priority'] ?? 'medium';
    $status = 'active'; // Default status
    
    // Validation
    if (empty($name)) {
        $error_message = 'Team name is required.';
    } elseif (strlen($name) < 2) {
        $error_message = 'Team name must be at least 2 characters long.';
    } elseif (strlen($name) > 100) {
        $error_message = 'Team name cannot exceed 100 characters.';
    } else {
        // Check if team name already exists
        $checkStmt = $db->prepare("SELECT id FROM teams WHERE name = ?");
        $checkStmt->bind_param("s", $name);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows > 0) {
            $error_message = 'A team with this name already exists.';
        } else {
            try {
                // Create team using direct database insertion
                $stmt = $db->prepare("INSERT INTO teams (name, description, priority, status, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->bind_param("ssss", $name, $description, $priority, $status);
                
                if ($stmt->execute()) {
                    $success_message = "Team '{$name}' created successfully!";
                    // Clear form data
                    $name = $description = '';
                    $priority = 'medium';
                } else {
                    $error_message = 'Failed to create team. Please try again.';
                }
            } catch (Exception $e) {
                $error_message = 'Error creating team: ' . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Team - Helpdesk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root { 
            --bg-soft: #f8f9fc;
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
        html, body { height: 100%; }
        body { background: var(--treasury-light); }
        .app-shell { display: flex; height: 100vh; }
        .content { 
            padding: calc(60px + 1rem) 1.25rem 2rem;
            height: 100vh;
            overflow-y: auto;
            flex: 1;
        }
        .form-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(30, 58, 95, 0.08);
        }
        .priority-option {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .priority-option:hover {
            border-color: var(--treasury-tan);
        }
        .priority-option.selected {
            border-color: var(--treasury-tan);
            background-color: rgba(210, 180, 140, 0.1);
        }
        .priority-high { border-color: var(--treasury-burgundy) !important; background-color: rgba(114, 47, 55, 0.1) !important; }
        .priority-medium { border-color: var(--treasury-amber) !important; background-color: rgba(184, 134, 11, 0.1) !important; }
        .priority-low { border-color: var(--treasury-green) !important; background-color: rgba(45, 90, 61, 0.1) !important; }
        
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
        
        .card-header {
            background: linear-gradient(135deg, var(--treasury-light) 0%, #ffffff 100%);
            border-bottom: 2px solid var(--treasury-tan);
        }
        
        .btn-outline-secondary {
            color: var(--treasury-navy);
            border-color: var(--treasury-navy);
        }
        
        .btn-outline-secondary:hover {
            background-color: var(--treasury-navy);
            border-color: var(--treasury-navy);
        }
        
        .form-control:focus {
            border-color: var(--treasury-tan);
            box-shadow: 0 0 0 0.2rem rgba(210, 180, 140, 0.25);
        }
        
        .text-primary {
            color: var(--treasury-navy) !important;
        }
        
        .text-info {
            color: var(--treasury-blue) !important;
        }
        
        .text-warning {
            color: var(--treasury-amber) !important;
        }
        
        .breadcrumb-item.active {
            color: var(--treasury-navy);
        }
        
        .breadcrumb-item a {
            color: var(--treasury-blue);
            text-decoration: none;
        }
        
        .breadcrumb-item a:hover {
            color: var(--treasury-navy);
        }
        
        .fa-plus-circle.text-primary, .fa-users {
            color: var(--treasury-brown) !important;
        }
        
        .fa-arrow-left {
            color: var(--treasury-navy);
        }
        
        .fa-check-circle {
            color: var(--treasury-green);
        }
        
        .fa-exclamation-triangle, .fa-asterisk.text-danger {
            color: var(--treasury-burgundy) !important;
        }
        
        .fa-arrow-up.text-danger {
            color: var(--treasury-burgundy) !important;
        }
        
        .fa-minus.text-warning {
            color: var(--treasury-amber) !important;
        }
        
        .fa-arrow-down.text-success {
            color: var(--treasury-green) !important;
        }
        
        .fa-info-circle.text-info, .fa-lightbulb.text-warning {
            color: var(--treasury-tan) !important;
        }
        
        .fa-spinner {
            color: var(--treasury-brown);
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
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-1">
                                <li class="breadcrumb-item"><a href="team.php">Teams</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Create New Team</li>
                            </ol>
                        </nav>
                        <h1 class="h3 mb-1 text-gray-800">
                            <i class="fas fa-plus-circle me-2 text-primary"></i>Create New Team
                        </h1>
                        <p class="mb-0 text-muted">Build a new support team to organize your helpdesk efficiently</p>
                    </div>
                    <div>
                        <a href="team.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Teams
                        </a>
                    </div>
                </div>

                <!-- Success/Error Messages -->
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

                <!-- Create Team Form -->
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="card form-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-users me-2"></i>Team Information
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="" id="createTeamForm">
                                    <div class="row mb-3">
                                        <div class="col-md-8">
                                            <label for="name" class="form-label">Team Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="name" name="name" 
                                                   value="<?= htmlspecialchars($name ?? '') ?>" 
                                                   placeholder="e.g., IT Support Team" required maxlength="100">
                                            <div class="form-text">Choose a clear, descriptive name for your team</div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Team Priority</label>
                                            <div class="priority-selector">
                                                <div class="priority-option <?= ($priority ?? 'medium') === 'high' ? 'selected priority-high' : '' ?>" 
                                                     data-priority="high">
                                                    <input type="radio" name="priority" value="high" 
                                                           <?= ($priority ?? 'medium') === 'high' ? 'checked' : '' ?> class="d-none">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-arrow-up text-danger me-2"></i>
                                                        <div>
                                                            <div class="fw-bold">High</div>
                                                            <small class="text-muted">Critical issues</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="priority-option mt-2 <?= ($priority ?? 'medium') === 'medium' ? 'selected priority-medium' : '' ?>" 
                                                     data-priority="medium">
                                                    <input type="radio" name="priority" value="medium" 
                                                           <?= ($priority ?? 'medium') === 'medium' ? 'checked' : '' ?> class="d-none">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-minus text-warning me-2"></i>
                                                        <div>
                                                            <div class="fw-bold">Medium</div>
                                                            <small class="text-muted">Standard support</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="priority-option mt-2 <?= ($priority ?? 'medium') === 'low' ? 'selected priority-low' : '' ?>" 
                                                     data-priority="low">
                                                    <input type="radio" name="priority" value="low" 
                                                           <?= ($priority ?? 'medium') === 'low' ? 'checked' : '' ?> class="d-none">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-arrow-down text-success me-2"></i>
                                                        <div>
                                                            <div class="fw-bold">Low</div>
                                                            <small class="text-muted">General inquiries</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="4" 
                                                  placeholder="Describe the team's role and responsibilities..."><?= htmlspecialchars($description ?? '') ?></textarea>
                                        <div class="form-text">Optional: Add details about what this team handles</div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card bg-light">
                                                <div class="card-body">
                                                    <h6 class="card-title">
                                                        <i class="fas fa-info-circle text-info me-2"></i>Next Steps
                                                    </h6>
                                                    <ul class="mb-0 small">
                                                        <li>Team will be created with active status</li>
                                                        <li>You can add members after creation</li>
                                                        <li>Set up team permissions and workflows</li>
                                                        <li>Configure team-specific settings</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card bg-light">
                                                <div class="card-body">
                                                    <h6 class="card-title">
                                                        <i class="fas fa-lightbulb text-warning me-2"></i>Tips
                                                    </h6>
                                                    <ul class="mb-0 small">
                                                        <li>Use clear, specific team names</li>
                                                        <li>Set priority based on ticket urgency</li>
                                                        <li>Add detailed descriptions for clarity</li>
                                                        <li>Consider team size for workload balance</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <hr>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="form-text">
                                            <i class="fas fa-asterisk text-danger me-1" style="font-size: 8px;"></i>
                                            Required fields
                                        </div>
                                        <div>
                                            <a href="team.php" class="btn btn-light me-2">Cancel</a>
                                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                                <i class="fas fa-plus-circle me-2"></i>Create Team
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Priority selection
            const priorityOptions = document.querySelectorAll('.priority-option');
            priorityOptions.forEach(option => {
                option.addEventListener('click', function() {
                    // Remove selected class from all options
                    priorityOptions.forEach(opt => {
                        opt.classList.remove('selected', 'priority-high', 'priority-medium', 'priority-low');
                    });
                    
                    // Add selected class and priority color to clicked option
                    const priority = this.dataset.priority;
                    this.classList.add('selected', `priority-${priority}`);
                    
                    // Update radio button
                    this.querySelector('input[type="radio"]').checked = true;
                });
            });

            // Form validation
            const form = document.getElementById('createTeamForm');
            const submitBtn = document.getElementById('submitBtn');
            const nameInput = document.getElementById('name');

            form.addEventListener('submit', function(e) {
                if (!nameInput.value.trim()) {
                    e.preventDefault();
                    alert('Please enter a team name.');
                    nameInput.focus();
                    return;
                }

                // Show loading state
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating Team...';
                submitBtn.disabled = true;
            });

            // Real-time validation
            nameInput.addEventListener('input', function() {
                const value = this.value.trim();
                if (value.length === 0) {
                    this.classList.add('is-invalid');
                } else if (value.length < 2) {
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                }
            });
        });
    </script>
</body>
</html>