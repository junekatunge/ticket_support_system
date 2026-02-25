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

// Check if team ID is provided
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header('Location: team.php');
    exit();
}

$db = Database::getInstance();
$teamId = intval($_GET['id']);
$success_message = '';
$error_message = '';

// Fetch team details
$currentTeam = Team::find($teamId);
if (!$currentTeam) {
    header('Location: team.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // Validation
    if (empty($name)) {
        $error_message = 'Team name is required.';
    } elseif (strlen($name) < 2) {
        $error_message = 'Team name must be at least 2 characters long.';
    } elseif (strlen($name) > 100) {
        $error_message = 'Team name cannot exceed 100 characters.';
    } else {
        // Check if team name already exists (excluding current team)
        $checkStmt = $db->prepare("SELECT id FROM team WHERE name = ? AND id != ?");
        $checkStmt->bind_param("si", $name, $teamId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = 'A team with this name already exists.';
        } else {
            try {
                // Update team
                $stmt = $db->prepare("UPDATE team SET name = ?, updated_at = NOW() WHERE id = ?");
                $stmt->bind_param("si", $name, $teamId);

                if ($stmt->execute()) {
                    $success_message = "Team '{$name}' updated successfully!";
                    // Refresh team data
                    $currentTeam = Team::find($teamId);
                } else {
                    $error_message = 'Failed to update team. Please try again.';
                }
            } catch (Exception $e) {
                $error_message = 'Error updating team: ' . $e->getMessage();
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
    <title>Edit Team - Helpdesk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --bg-soft: #f8f9fc;
            --treasury-navy: #1e3a5f;
            --treasury-gold: #c9a96e;
            --treasury-brown: #8B4513;
            --treasury-tan: #D2B48C;
            --treasury-dark: #2c3e50;
            --treasury-light: #f8f9fc;
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
        .form-control:focus {
            border-color: var(--treasury-tan);
            box-shadow: 0 0 0 0.2rem rgba(210, 180, 140, 0.25);
        }
    </style>
</head>
<body>
    <div class="app-shell">
        <?php include 'sidebar.php'; ?>

        <section class="content content-with-navbar">
            <?php include 'navbar.php'; ?>

            <div class="container-fluid mt-3">
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-1">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="team.php">Teams</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Edit Team</li>
                            </ol>
                        </nav>
                        <h1 class="h3 mb-1" style="color: var(--treasury-navy);">
                            <i class="fas fa-edit me-2"></i>Edit Team
                        </h1>
                        <p class="mb-0 text-muted">Update team information</p>
                    </div>
                    <div>
                        <a href="team.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Teams
                        </a>
                    </div>
                </div>

                <!-- Success/Error Messages -->
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success_message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error_message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Edit Team Form -->
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="card form-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-users me-2"></i>Team Information
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="" id="editTeamForm">
                                    <div class="mb-4">
                                        <label for="name" class="form-label fw-bold">
                                            Team Name <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control form-control-lg"
                                               id="name" name="name"
                                               value="<?= htmlspecialchars($currentTeam->name ?? '') ?>"
                                               placeholder="Enter team name"
                                               required minlength="2" maxlength="100">
                                        <div class="form-text">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Enter a unique name for this team (2-100 characters)
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="description" class="form-label fw-bold">
                                            Description <span class="text-muted">(Optional)</span>
                                        </label>
                                        <textarea class="form-control" id="description" name="description"
                                                  rows="3" placeholder="Enter team description">
                                                  </textarea>
                                        <div class="form-text">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Provide a brief description of the team's role
                                        </div>
                                    </div>

                                    <hr class="my-4">

                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="form-text">
                                            <i class="fas fa-asterisk text-danger me-1" style="font-size: 8px;"></i>
                                            Required fields
                                        </div>
                                        <div>
                                            <a href="team.php" class="btn btn-light me-2">Cancel</a>
                                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                                <i class="fas fa-save me-2"></i>Update Team
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Show success popup if team was updated
            <?php if (!empty($success_message)): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Team Updated!',
                    text: '<?= addslashes($success_message) ?>',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#8B4513',
                    timer: 3000,
                    timerProgressBar: true
                });
            <?php endif; ?>

            // Show error popup if there was an error
            <?php if (!empty($error_message)): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: '<?= addslashes($error_message) ?>',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#8B4513'
                });
            <?php endif; ?>

            const form = document.getElementById('editTeamForm');
            const submitBtn = document.getElementById('submitBtn');
            const nameInput = document.getElementById('name');

            form.addEventListener('submit', function(e) {
                if (nameInput.value.trim().length < 2) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Invalid Input',
                        text: 'Team name must be at least 2 characters long.',
                        confirmButtonColor: '#8B4513'
                    });
                    nameInput.focus();
                    return;
                }

                // Show loading state
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating Team...';
                submitBtn.disabled = true;
            });

            // Real-time validation
            nameInput.addEventListener('input', function() {
                if (this.value.trim().length < 2) {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                } else {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                }
            });
        });
    </script>
</body>
</html>
