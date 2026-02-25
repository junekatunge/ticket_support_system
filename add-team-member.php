<?php
session_start();
if (!isset($_SESSION['logged-in']) || $_SESSION['logged-in'] == false) {
    header('Location: ./index.php');
    exit();
}
$user = $_SESSION['user'];

if (!isset($_GET['team-id']) || strlen($_GET['team-id']) < 1 || !ctype_digit($_GET['team-id'])) {
    echo '<script>history.back()</script>';
    exit();
}

require_once './src/Database.php';
require_once './src/user.php';
require_once './src/team.php';
require_once './src/team-member.php';

$db = Database::getInstance();
$teamId = intval($_GET['team-id']);
$currentTeam = Team::find($teamId);

if (!$currentTeam) {
    echo '<script>alert("Team not found"); window.location = "team.php";</script>';
    exit();
}

$users = new User();
$allusers = $users::findAll();

$err = '';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $userId = $_POST['id'];

    if ($userId == 'none' || empty($userId)) {
        $err = "Please select a user";
    } else {
        try {
            $team_mem = new TeamMember([
                'user' => $userId,
                'team' => $teamId
            ]);

            $saveteam = $team_mem->save();
            $msg = "Member added successfully to team!";
        } catch (Exception $e) {
            $err = "Failed to add member: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Team Member - Helpdesk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --bg-soft: #f8f9fc;
            --treasury-navy: #1e3a5f;
            --treasury-gold: #c9a96e;
            --treasury-brown: #8B4513;
            --treasury-tan: #D2B48C;
        }
        html, body { height: 100%; }
        body { background: var(--bg-soft); }
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
        .form-control:focus, .form-select:focus {
            border-color: var(--treasury-tan);
            box-shadow: 0 0 0 0.2rem rgba(210, 180, 140, 0.25);
        }
        .breadcrumb-item.active {
            color: var(--treasury-navy);
        }
        .breadcrumb-item a {
            color: var(--treasury-blue);
            text-decoration: none;
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
                                <li class="breadcrumb-item active" aria-current="page">Add Member</li>
                            </ol>
                        </nav>
                        <h1 class="h3 mb-1" style="color: var(--treasury-navy);">
                            <i class="fas fa-user-plus me-2"></i>Add Team Member
                        </h1>
                        <p class="mb-0 text-muted">Add a new member to <strong><?= htmlspecialchars($currentTeam->name) ?></strong></p>
                    </div>
                    <div>
                        <a href="team.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Teams
                        </a>
                    </div>
                </div>

                <!-- Success/Error Messages -->
                <?php if (!empty($msg)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($msg) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($err)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($err) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Add Member Form -->
                <div class="row justify-content-center">
                    <div class="col-lg-6 col-md-8">
                        <div class="card form-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-users me-2"></i>Select User
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="" id="addMemberForm">
                                    <div class="mb-4">
                                        <label for="id" class="form-label fw-bold">
                                            User <span class="text-danger">*</span>
                                        </label>
                                        <select name="id" id="id" class="form-select form-select-lg" required>
                                            <option value="none">-- Select User --</option>
                                            <?php foreach ($allusers as $u): ?>
                                                <option value="<?= $u->id ?>">
                                                    <?= htmlspecialchars($u->name) ?> (<?= htmlspecialchars($u->email) ?>) - <?= ucfirst($u->role) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-text">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Select a user to add to this team
                                        </div>
                                    </div>

                                    <div class="alert alert-info">
                                        <i class="fas fa-lightbulb me-2"></i>
                                        <strong>Note:</strong> The selected user will be added to the team and can start receiving assigned tickets.
                                    </div>

                                    <hr>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="form-text">
                                            <i class="fas fa-asterisk text-danger me-1" style="font-size: 8px;"></i>
                                            Required fields
                                        </div>
                                        <div>
                                            <a href="team.php" class="btn btn-light me-2">Cancel</a>
                                            <button type="submit" name="submit" class="btn btn-primary" id="submitBtn">
                                                <i class="fas fa-user-plus me-2"></i>Add Member
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Current Team Members -->
                        <div class="card form-card mt-3">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-users me-2"></i>Current Members
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php
                                $memberCount = Team::getMemberCount($teamId);
                                if ($memberCount > 0):
                                ?>
                                    <p class="mb-0">
                                        <i class="fas fa-info-circle text-primary me-2"></i>
                                        This team currently has <strong><?= $memberCount ?></strong> member<?= $memberCount > 1 ? 's' : '' ?>.
                                    </p>
                                <?php else: ?>
                                    <p class="mb-0 text-muted">
                                        <i class="fas fa-info-circle me-2"></i>
                                        This team has no members yet. Be the first to add one!
                                    </p>
                                <?php endif; ?>
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
            // Show success popup if member was added
            <?php if (!empty($msg)): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Member Added!',
                    text: '<?= addslashes($msg) ?>',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#8B4513',
                    timer: 3000,
                    timerProgressBar: true
                });
            <?php endif; ?>

            // Show error popup if there was an error
            <?php if (!empty($err)): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: '<?= addslashes($err) ?>',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#8B4513'
                });
            <?php endif; ?>

            const form = document.getElementById('addMemberForm');
            const submitBtn = document.getElementById('submitBtn');
            const userSelect = document.getElementById('id');

            form.addEventListener('submit', function(e) {
                if (userSelect.value === 'none' || !userSelect.value) {
                    e.preventDefault();
                    alert('Please select a user to add to the team.');
                    userSelect.focus();
                    return;
                }

                // Show loading state
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Adding Member...';
                submitBtn.disabled = true;
            });

            // Real-time validation
            userSelect.addEventListener('change', function() {
                if (this.value === 'none' || !this.value) {
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
