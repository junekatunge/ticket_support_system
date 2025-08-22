<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once './header.php';
require_once './src/team.php';

$teams = Team::findAll();
?>

<!-- Inline table styles to override dark themes -->
<style>
  .table {
    background-color: #ffffff !important;
    color: #000 !important;
  }

  .table-striped tbody tr:nth-of-type(odd) {
    background-color: #f8f9fa !important;
  }

  .badge-count {
    background-color: #d1ecf1;
    color: #0c5460;
    font-weight: 500;
    padding: 0.25em 0.6em;
    font-size: 0.85rem;
    border-radius: 0.6rem;
  }

  .table th {
    font-weight: bold;
  }

  .card {
    border-radius: 12px;
  }
</style>

<div class="container-fluid py-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4 px-4 py-3 rounded"
       style="background: linear-gradient(to right, #3b82f6, #6366f1); color: white;">
    <div>
      <h2 class="mb-0">Teams</h2>
      <small>Teams / Overview</small>
    </div>
    <a href="newteam.php" class="btn btn-light btn-lg fw-bold shadow-sm">
      <i class="fas fa-users me-2"></i> Create New Team
    </a>
  </div>

  <!-- Team List Table -->
  <div class="card shadow-sm border-0 bg-white">
    <div class="card-body p-4">
      <div class="table-responsive">
      <table class="table table-striped table-hover align-middle" style="background-color: white;">

          <thead>
            <tr>
              <th style="width: 40%;">Team Name</th>
              <th style="width: 30%;">Created At</th>
              <th style="width: 20%;">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($teams)): ?>
              <tr><td colspan="3" class="text-center text-muted">No teams found.</td></tr>
            <?php else: ?>
              <?php foreach ($teams as $team): ?>
                <?php
                  try {
                    $date = new DateTime($team->created_at ?? 'now');
                    $formatted = $date->format('d M Y, H:i');
                  } catch (Exception $e) {
                    $formatted = '—';
                  }

                  $count = Team::getMemberCount($team->id);
                ?>
                <tr>
                  <td>
                    <strong><?= htmlspecialchars($team->name) ?></strong>
                    <span class="badge-count ms-2"><?= $count ?> member<?= $count != 1 ? 's' : '' ?></span>
                  </td>
                  <td><?= $formatted ?></td>
                  <td>
                    <div class="dropdown">
                      <button class="btn btn-outline-primary btn-sm dropdown-toggle"
                              data-toggle="dropdown"
                              aria-haspopup="true"
                              aria-expanded="false">
                        Manage
                      </button>
                      <ul class="dropdown-menu">
                        <li>
                          <a class="dropdown-item" href="add-team-member.php?team-id=<?= $team->id ?>">
                            <i class="fas fa-user-plus me-2"></i> Add Member
                          </a>
                        </li>
                        <li>
                          <a class="dropdown-item" href="editteam.php?id=<?= $team->id ?>">
                            <i class="fas fa-edit me-2"></i> Edit
                          </a>
                        </li>
                        <li>
                          <a class="dropdown-item text-danger" href="deleteteam.php?id=<?= $team->id ?>" onclick="return confirm('Are you sure you want to delete this team?');">
                            <i class="fas fa-trash me-2"></i> Delete
                          </a>
                        </li>
                      </ul>
                    </div>
                  </td>
                </tr>
              <?php endforeach ?>
            <?php endif ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require_once './footer.php'; ?>
