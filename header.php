<?php 
session_start();
if (!isset($_SESSION['logged-in']) || $_SESSION['logged-in'] == false) {
    header('Location: ./index.php');
    exit();
}
$user = $_SESSION['user'];
require_once './src/Database.php';
$db = Database::getInstance();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Helpdesk</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

  <style>
    body {
      background-color: #f4f6f9;
    }
    .sidebar {
      width: 250px;
      height: 100vh;
      background-color: #1e293b;
      padding-top: 20px;
      position: fixed;
    }
    .sidebar .nav-link {
      color: #cbd5e1;
      padding: 10px 20px;
      font-weight: 500;
    }
    .sidebar .nav-link.active, .sidebar .nav-link:hover {
      background-color: #0f172a;
      color: #fff;
      border-left: 4px solid #2563eb;
    }
    .sidebar .nav-link i {
      margin-right: 10px;
    }
    .sidebar .admin-box {
      margin-top: auto;
      padding: 15px 20px;
      background-color: #0f172a;
    }
    .sidebar .admin-box a {
      color: #3b82f6;
      font-weight: bold;
    }
    .topbar {
      margin-left: 250px;
      background-color: #fff;
      padding: 10px 20px;
      height: 60px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .content-wrapper {
      margin-left: 250px;
      padding: 30px;
    }
  </style>
</head>

<body>
  <!-- Sidebar -->
  <div class="sidebar d-flex flex-column">
    <div class="px-3 mb-4 text-white fs-4 fw-bold">
      <i class="fas fa-life-ring me-2"></i>Helpdesk
    </div>
    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">
      <i class="fas fa-gauge-high"></i> Dashboard
    </a>
    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'open.php' ? 'active' : '' ?>" href="open.php">
      <i class="fas fa-folder-open"></i> Open
    </a>
    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'solved.php' ? 'active' : '' ?>" href="solved.php">
      <i class="fas fa-circle-check"></i> Solved
    </a>
    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'closed.php' ? 'active' : '' ?>" href="closed.php">
      <i class="fas fa-circle-xmark"></i> Closed
    </a>
    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'pending.php' ? 'active' : '' ?>" href="pending.php">
      <i class="fas fa-clock"></i> Pending
    </a>
    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'unassigned.php' ? 'active' : '' ?>" href="unassigned.php">
      <i class="fas fa-circle"></i> Unassigned
    </a>
    <hr class="border-light mx-3" />
    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'mytickets.php' ? 'active' : '' ?>" href="mytickets.php">
      <i class="fas fa-ticket"></i> My tickets
    </a>

    <?php if ($user->role == 'admin'): ?>
      <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'team.php' ? 'active' : '' ?>" href="team.php">
        <i class="fas fa-users"></i> Teams
      </a>
      <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : '' ?>" href="users.php">
        <i class="fas fa-user-friends"></i> Users
      </a>
    <?php endif; ?>

    <div class="admin-box mt-auto d-flex justify-content-between align-items-center">
      <a href="#" class="text-decoration-none"><i class="fas fa-user-group"></i> Admin</a>
      <a href="logout.php" class="btn btn-outline-light btn-sm"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
  </div>

  <!-- Topbar -->
  <div class="topbar d-flex justify-content-end align-items-center">
    <div class="text-muted me-3">
      <i class="fas fa-user-circle me-1"></i> <?= htmlspecialchars($user->name) ?>
    </div>
  </div>

  <!-- Begin Page Content -->
  <div class="content-wrapper">
