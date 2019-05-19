<?php
  include './header.php';
  if(!isset($_GET['id']) || strlen($_GET['id']) < 1 || !ctype_digit($_GET['id'])){
    echo '<script> history.back()</script>';
    exit();
  }

  require_once './src/requester.php';
  require_once './src/team.php';
  require_once './src/ticket.php';

  $ticket = Ticket::find($_GET['id']);

  $teams = Team::findAll();

?>
<div id="content-wrapper">

    <div class="container-fluid">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="#">Dashboard</a>
            </li>
            <li class="breadcrumb-item active">Ticket details</li>
        </ol>
        <div class="card mb-3">
            <div class="card-header">
                <div class="row mx-auto">
                    <div>
                        <?php echo $ticket->displayStatusBadge()?>
                        <small class="text-info ml-2"><?php echo $ticket->title?> <span class="text-muted">
                                <?php $date = new DateTime($ticket->created_at);?>
                                <?php echo $date->format('d-m-Y H:i:s')?>
                            </span></small>
                    </div>
                    <div class="ml-auto">
                        <span class="badge badge-primary">Primary</span>
                        <span class="badge badge-danger">Primary</span>
                    </div>
                </div>

            </div>
            <div class="card-body">
                <form method="">
                    <div class="col-lg-8 col-md-8 col-sm-12 offset-lg-2 offset-md-2">
                        <div class="form-group row">
                            <label for="team" class="col-sm-3 col-form-label">Team</label>
                            <div class="col-sm-8">
                                <select class="form-control" id="team-dropdown" onchange="getTeamMember(event.target.value)">
                                    <option>--select--</option>
                                    <?php foreach($teams as $team):?>
                                    <option value="<?php echo $team->id?>"><?php echo $team->name?></option>
                                    <?php endforeach?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="assigned" class="col-sm-3 col-form-label">Assigned</label>
                            <div class="col-sm-8">
                                <select class="form-control" id="team-member-dropdown">
                                    <option>--select--</option>
                                </select>
                            </div>
                        </div>
                        <div class="text-center">
                            <a class="btn btn-primary my-3" href="#">Assign</a>
                        </div>
                    </div>

                </form>

            </div>
        </div>
        <div class="form-group row col-lg-8 offset-lg-2 col-md-8 col-sm-12 offset-md-2">
            <label for="team" class="col-sm-12 col-lg-3 col-md-3 col-form-label">Comment</label>
            <div class="col-sm-8">
                <textarea class="form-control"></textarea>
            </div>
        </div>
    </div>
    <footer class="sticky-footer">
        <div class="container my-auto">
            <div class="copyright text-center my-auto">
                <span>Copyright © Your Website 2019</span>
            </div>
        </div>
    </footer>

</div>

<?php include './footer.php'?>