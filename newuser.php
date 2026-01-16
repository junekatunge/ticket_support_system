<?php
session_start();
if (!isset($_SESSION['logged-in']) || $_SESSION['logged-in'] == false) {
    header('Location: ./index.php');
    exit();
}
$user = $_SESSION['user'];

require_once './src/Database.php';
require_once './src/user.php';
require_once './src/notification.php';
require './src/helper-functions.php';

$db = Database::getInstance();
$err = '';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_pass = $_POST['confirm-password'];
    $role = $_POST['role'] ?? 'member';

    if (strlen($name) < 1) {
        $err = "Please enter user name";
    } elseif (strlen($email) < 1) {
        $err = "Please enter email";
    } elseif (!isValidEmail($email)) {
        $err = "Please enter a valid email";
    } else {
        // Check if email already exists
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $err = "Email address already exists. Please use a different email.";
        }
    }

    if (empty($err) && strlen($phone) < 1) {
        $err = "Please enter phone number";
    }
    if (empty($err) && strlen($password) < 1) {
        $err = "Please enter a password";
    }
    if (empty($err) && strlen($password) < 8) {
        $err = "Password should be at least 8 characters";
    }
    if (empty($err) && $password != $confirm_pass) {
        $err = "Passwords do not match";
    }

    if (empty($err)) {
        try {
            $newUser = new User([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role' => $role,
                'last_password' => password_hash($password, PASSWORD_DEFAULT)
            ]);

            $savedUser = $newUser->save();

            // Notify all admins about the new user
            try {
                $notificationSent = Notification::notifyAdmins(
                    'user_created',
                    'New User Created',
                    "A new user '{$name}' has been created with role: " . ucfirst($role),
                    $savedUser->id ?? null
                );

                if ($notificationSent) {
                    error_log("Notification sent successfully for new user: {$name}");
                    $msg = "User created successfully! All administrators have been notified.";
                } else {
                    error_log("Failed to send notification for new user: {$name}");
                    $msg = "User created successfully! (Warning: Could not send notification to admins)";
                }
            } catch (Exception $notifException) {
                error_log("Notification exception: " . $notifException->getMessage());
                $msg = "User created successfully! (Warning: Notification error - " . $notifException->getMessage() . ")";
            }

            // Clear form
            $name = $email = $phone = '';
            $role = 'member';
        } catch (Exception $e) {
            $err = "Unable to create user: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New User - Helpdesk</title>
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
        .role-option {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .role-option:hover {
            border-color: var(--treasury-tan);
        }
        .role-option.selected {
            border-color: var(--treasury-tan);
            background-color: rgba(210, 180, 140, 0.1);
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
                                <li class="breadcrumb-item"><a href="users.php">Users</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Create New User</li>
                            </ol>
                        </nav>
                        <h1 class="h3 mb-1" style="color: var(--treasury-navy);">
                            <i class="fas fa-user-plus me-2"></i>Create New User
                        </h1>
                        <p class="mb-0 text-muted">Add a new team member to the helpdesk system</p>
                    </div>
                    <div>
                        <a href="users.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Users
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

                <!-- Create User Form -->
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="card form-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-user me-2"></i>User Information
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="" id="createUserForm">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="name" class="form-label fw-bold">
                                                Full Name <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="name" name="name"
                                                   value="<?= htmlspecialchars($name ?? '') ?>"
                                                   placeholder="e.g., John Doe" required>
                                            <div class="form-text">Enter the user's full name</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="email" class="form-label fw-bold">
                                                Email Address <span class="text-danger">*</span>
                                            </label>
                                            <input type="email" class="form-control" id="email" name="email"
                                                   value="<?= htmlspecialchars($email ?? '') ?>"
                                                   placeholder="johndoe@example.com" required>
                                            <div class="form-text">User's email for login and notifications</div>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="phone" class="form-label fw-bold">
                                                Phone Number <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="phone" name="phone"
                                                   value="<?= htmlspecialchars($phone ?? '') ?>"
                                                   placeholder="e.g., 0712345678" required>
                                            <div class="form-text">Contact phone number</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">
                                                Role <span class="text-danger">*</span>
                                            </label>
                                            <div class="role-selector">
                                                <div class="role-option mb-2 <?= ($role ?? 'member') === 'admin' ? 'selected' : '' ?>"
                                                     data-role="admin">
                                                    <input type="radio" name="role" value="admin"
                                                           <?= ($role ?? 'member') === 'admin' ? 'checked' : '' ?> class="d-none">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-shield-alt me-2" style="color: var(--treasury-brown);"></i>
                                                        <div>
                                                            <div class="fw-bold">Administrator</div>
                                                            <small class="text-muted">Full system access</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="role-option <?= ($role ?? 'member') === 'member' ? 'selected' : '' ?>"
                                                     data-role="member">
                                                    <input type="radio" name="role" value="member"
                                                           <?= ($role ?? 'member') === 'member' ? 'checked' : '' ?> class="d-none">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-user me-2" style="color: var(--treasury-tan);"></i>
                                                        <div>
                                                            <div class="fw-bold">Team Member</div>
                                                            <small class="text-muted">Standard user access</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="password" class="form-label fw-bold">
                                                Password <span class="text-danger">*</span>
                                            </label>
                                            <input type="password" class="form-control" id="password" name="password"
                                                   placeholder="Min. 8 characters" required minlength="8">
                                            <div class="form-text">Minimum 8 characters</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="confirm-password" class="form-label fw-bold">
                                                Confirm Password <span class="text-danger">*</span>
                                            </label>
                                            <input type="password" class="form-control" id="confirm-password"
                                                   name="confirm-password" placeholder="Re-enter password" required minlength="8">
                                            <div class="form-text">Must match the password</div>
                                        </div>
                                    </div>

                                    <hr>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="form-text">
                                            <i class="fas fa-asterisk text-danger me-1" style="font-size: 8px;"></i>
                                            Required fields
                                        </div>
                                        <div>
                                            <a href="users.php" class="btn btn-light me-2">Cancel</a>
                                            <button type="submit" name="submit" class="btn btn-primary" id="submitBtn">
                                                <i class="fas fa-user-plus me-2"></i>Create User
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
            // Show success popup if user was created
            <?php if (!empty($msg)): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'User Created!',
                    html: '<p><?= addslashes($msg) ?></p><p class="mt-2"><small>All administrators have been notified.</small></p>',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#8B4513',
                    timer: 4000,
                    timerProgressBar: true,
                    showClass: {
                        popup: 'animate__animated animate__fadeInDown'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__fadeOutUp'
                    }
                }).then(() => {
                    // Optional: Send browser notification if permission granted
                    if ("Notification" in window && Notification.permission === "granted") {
                        new Notification("New User Created", {
                            body: "<?= addslashes($msg) ?>",
                            icon: "/helpdesk-core-php/images/tnt.logo",
                            badge: "/helpdesk-core-php/images/tnt.logo"
                        });
                    }
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

            // Role selection
            const roleOptions = document.querySelectorAll('.role-option');
            roleOptions.forEach(option => {
                option.addEventListener('click', function() {
                    roleOptions.forEach(opt => opt.classList.remove('selected'));
                    this.classList.add('selected');
                    this.querySelector('input[type="radio"]').checked = true;
                });
            });

            // Form validation
            const form = document.getElementById('createUserForm');
            const submitBtn = document.getElementById('submitBtn');
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm-password');

            form.addEventListener('submit', function(e) {
                if (password.value !== confirmPassword.value) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Password Mismatch',
                        text: 'Passwords do not match!',
                        confirmButtonColor: '#8B4513'
                    });
                    confirmPassword.focus();
                    return;
                }

                if (password.value.length < 8) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Password Too Short',
                        text: 'Password must be at least 8 characters long!',
                        confirmButtonColor: '#8B4513'
                    });
                    password.focus();
                    return;
                }
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating User...';
                submitBtn.disabled = true;
            });

            // Password match validation
            confirmPassword.addEventListener('input', function() {
                if (this.value !== password.value) {
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
