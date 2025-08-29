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

// Get the current user - handle incomplete class object
$userId = null;
if (is_object($user)) {
    $userId = isset($user->id) ? $user->id : null;
}

if (!$userId) {
    // Session is corrupted, redirect to login
    session_destroy();
    header('Location: ./index.php?error=session_expired');
    exit();
}

$currentUser = User::find($userId);
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $room = trim($_POST['room'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate required fields
    if (empty($name) || empty($email)) {
        $error_message = 'Name and email are required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } elseif (strlen($name) < 2) {
        $error_message = 'Name must be at least 2 characters long.';
    } elseif (strlen($name) > 100) {
        $error_message = 'Name cannot be longer than 100 characters.';
    } else {
        // Check if email already exists for another user (only if email is being changed)
        if ($email !== $currentUser->email) {
            $emailCheck = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $emailCheck->bind_param("si", $email, $currentUser->id);
            $emailCheck->execute();
            $emailResult = $emailCheck->get_result();
            
            if ($emailResult->num_rows > 0) {
                $error_message = 'Email address is already taken by another user.';
            }
        }
        
        if (empty($error_message)) {
        // Check if password change is requested
        if (!empty($new_password)) {
            if (empty($current_password)) {
                $error_message = 'Current password is required to set a new password.';
            } elseif (!password_verify($current_password, $currentUser->password)) {
                $error_message = 'Current password is incorrect.';
            } elseif ($new_password !== $confirm_password) {
                $error_message = 'New passwords do not match.';
            } elseif (strlen($new_password) < 6) {
                $error_message = 'New password must be at least 6 characters long.';
            } else {
                // Update with new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE users SET name = ?, email = ?, room = ?, password = ?, last_password = ? WHERE id = ?");
                $stmt->bind_param("sssssi", $name, $email, $room, $hashed_password, $currentUser->password, $currentUser->id);
            }
        } else {
            // Update without password change
            $stmt = $db->prepare("UPDATE users SET name = ?, email = ?, room = ? WHERE id = ?");
            $stmt->bind_param("sssi", $name, $email, $room, $currentUser->id);
        }

        if (empty($error_message)) {
            if ($stmt->execute()) {
                $success_message = 'Profile updated successfully!';
                // Update session user data
                $_SESSION['user'] = User::find($currentUser->id);
                $user = $_SESSION['user'];
                $currentUser = $user;
            } else {
                $error_message = 'Error updating profile. Please try again.';
            }
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
    <title>My Profile - Helpdesk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex">
        <?php include 'sidebar.php'; ?>
        
        <div style="flex: 1; padding: 2rem; width: 100%;">
            <div class="container-fluid" style="max-width: none; padding: 0;">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-circle me-2"></i>My Profile
        </h1>
    </div>

    <div class="row">
        <div class="col-12">
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

            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Profile Information</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?= htmlspecialchars($currentUser->name) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($currentUser->email) ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="room" class="form-label">Room/Location</label>
                                <input type="text" class="form-control" id="room" name="room" 
                                       value="<?= htmlspecialchars($currentUser->room ?? '') ?>" 
                                       placeholder="e.g., Room 101, Building A">
                            </div>
                            <div class="col-md-6">
                                <label for="role" class="form-label">Role</label>
                                <input type="text" class="form-control" id="role" 
                                       value="<?= htmlspecialchars(ucfirst($currentUser->role)) ?>" readonly>
                                <div class="form-text">Role cannot be changed from your profile.</div>
                            </div>
                        </div>

                        <hr class="my-4">
                        
                        <h6 class="mb-3 text-primary">Change Password (Optional)</h6>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password">
                                <div class="form-text">Required only if changing password</div>
                            </div>
                            <div class="col-md-4">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password">
                                <div class="form-text">Minimum 6 characters</div>
                            </div>
                            <div class="col-md-4">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="bg-light p-3 rounded">
                                    <h6 class="mb-2">Account Information</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <small class="text-muted">Account Created:</small><br>
                                            <strong><?= $currentUser->created_at ? date('M d, Y \a\t g:i A', strtotime($currentUser->created_at)) : 'N/A' ?></strong>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted">Last Updated:</small><br>
                                            <strong><?= $currentUser->updated_at ? date('M d, Y \a\t g:i A', strtotime($currentUser->updated_at)) : 'N/A' ?></strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Enhanced form validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const nameField = document.getElementById('name');
    const emailField = document.getElementById('email');
    const newPasswordField = document.getElementById('new_password');
    const confirmPasswordField = document.getElementById('confirm_password');
    const currentPasswordField = document.getElementById('current_password');
    
    // Name validation
    nameField.addEventListener('input', function() {
        if (this.value.length < 2 && this.value.length > 0) {
            this.setCustomValidity('Name must be at least 2 characters long');
            this.classList.add('is-invalid');
        } else if (this.value.length > 100) {
            this.setCustomValidity('Name cannot be longer than 100 characters');
            this.classList.add('is-invalid');
        } else {
            this.setCustomValidity('');
            this.classList.remove('is-invalid');
        }
    });
    
    // Email validation
    emailField.addEventListener('input', function() {
        if (this.value && !this.value.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
            this.setCustomValidity('Please enter a valid email address');
            this.classList.add('is-invalid');
        } else {
            this.setCustomValidity('');
            this.classList.remove('is-invalid');
        }
    });
    
    // Password confirmation validation
    function validatePasswords() {
        const newPassword = newPasswordField.value;
        const confirmPassword = confirmPasswordField.value;
        
        if (newPassword && confirmPassword && newPassword !== confirmPassword) {
            confirmPasswordField.setCustomValidity('Passwords do not match');
            confirmPasswordField.classList.add('is-invalid');
        } else {
            confirmPasswordField.setCustomValidity('');
            confirmPasswordField.classList.remove('is-invalid');
        }
    }
    
    newPasswordField.addEventListener('input', function() {
        if (this.value.length > 0) {
            currentPasswordField.required = true;
            currentPasswordField.parentElement.classList.add('required-field');
            if (this.value.length < 6) {
                this.setCustomValidity('Password must be at least 6 characters long');
                this.classList.add('is-invalid');
            } else {
                this.setCustomValidity('');
                this.classList.remove('is-invalid');
            }
        } else {
            currentPasswordField.required = false;
            currentPasswordField.parentElement.classList.remove('required-field');
            this.setCustomValidity('');
            this.classList.remove('is-invalid');
        }
        validatePasswords();
    });
    
    confirmPasswordField.addEventListener('input', validatePasswords);
    
    // Form submission validation
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Check required fields
        if (!nameField.value.trim() || !emailField.value.trim()) {
            isValid = false;
            alert('Please fill in all required fields.');
        }
        
        // Check password fields if password change is attempted
        if (newPasswordField.value && !currentPasswordField.value) {
            isValid = false;
            alert('Please enter your current password to change your password.');
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
});
</script>

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

  .required-field label::after {
    content: ' *';
    color: var(--treasury-burgundy);
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
  
  .form-control:focus {
    border-color: var(--treasury-tan);
    box-shadow: 0 0 0 0.2rem rgba(210, 180, 140, 0.25);
  }
  
  .card {
    box-shadow: 0 2px 8px rgba(30, 58, 95, 0.08);
  }
  
  .card-header {
    background: linear-gradient(135deg, var(--treasury-light) 0%, #ffffff 100%);
    border-bottom: 2px solid var(--treasury-tan);
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
  
  .fa-user-circle, .fa-save {
    color: var(--treasury-brown);
  }
  
  .fa-check-circle {
    color: var(--treasury-green);
  }
  
  .fa-exclamation-triangle {
    color: var(--treasury-burgundy);
  }
</style>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>