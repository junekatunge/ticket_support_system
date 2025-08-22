<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/src/database.php';
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/src/ticket.php';
require_once __DIR__ . '/src/requester.php';
require_once __DIR__ . '/src/team.php';
require_once __DIR__ . '/src/user.php';

$ticket = new Ticket();
$allTicket = Ticket::findByStatus('open');

$requester = new Requester();
$team = new Team();
$user = new User();
?>

<div id="content-wrapper">
  <div class="container-fluid">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
      <li class="breadcrumb-item active">Open Tickets</li>
    </ol>

    <div class="card mb-3">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered table-sm" id="dataTable" width="100%" cellspacing="0">
            <thead>
              <tr>
                <th>Subject</th>
                <th>Requester</th>
                <th>Team</th>
                <th>Agent</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($allTicket as $ticket): ?>
              <tr>
                <td>
                  <a href="./ticket-details.php?id=<?= htmlspecialchars($ticket->id) ?>">
                    <?= htmlspecialchars($ticket->title) ?>
                  </a>
                </td>

                <td>
                  <?= htmlspecialchars(($requester::find($ticket->requester)->name ?? '—')) ?>
                </td>

                <td>
                  <?= htmlspecialchars(($team::find($ticket->team)->name ?? '—')) ?>
                </td>

                <td>
  <?php
    $agent = $ticket->team_member ? $user::find((int)$ticket->team_member) : null;
    echo htmlspecialchars($agent->name ?? '—');
  ?>
</td>


                <td>
                  <button class="btn btn-danger"><?= htmlspecialchars($ticket->status) ?></button>
                </td>

                <td>
                  <?php
                    try {
                      $date = new DateTime($ticket->created_at);
                      echo $date->format('d-m-Y H:i:s');
                    } catch (Exception $e) {
                      echo '—';
                    }
                  ?>
                </td>

                <td width="100px">
                  <div class="btn-group" role="group" aria-label="Actions">
                    <button type="button" class="btn btn-outline-primary dropdown-toggle" data-toggle="dropdown"
                      aria-haspopup="true" aria-expanded="false">Action</button>
                    <div class="dropdown-menu">
                      <a class="dropdown-item" href="#">View</a>
                      <a class="dropdown-item" href="#">Update</a>
                      <a class="dropdown-item" href="#">Delete</a>
                    </div>
                  </div>
                </td>
              </tr>
              <?php endforeach ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>

  <!-- Sticky Footer -->
  <footer class="sticky-footer">
    <div class="container my-auto">
      <div class="copyright text-center my-auto">
        <span>Copyright © The National Treasury</span>
      </div>
    </div>
  </footer>
</div>

<!-- Scroll to Top Button-->
<a class="scroll-to-top rounded" href="#page-top">
  <i class="fas fa-angle-up"></i>
</a>

<!-- Logout Modal-->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
  aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Ready to Leave?</h5>
        <button class="close" type="button" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
      <div class="modal-footer">
        <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
        <a class="btn btn-primary" href="./index.php">Logout</a>
      </div>
    </div>
  </div>
</div>

<!-- JavaScript includes -->
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="vendor/chart.js/Chart.min.js"></script>
<script src="vendor/datatables/jquery.dataTables.js"></script>
<script src="vendor/datatables/dataTables.bootstrap4.js"></script>
<script src="js/sb-admin.min.js"></script>
<script src="js/demo/datatables-demo.js"></script>
<script src="js/demo/chart-area-demo.js"></script>

</body>
</html>
