<?php
session_start();
if (!isset($_SESSION['logged-in']) || $_SESSION['logged-in'] == false) {
    header('Location: ./index.php');
    exit();
}

if (!isset($_GET['id']) || strlen($_GET['id']) < 1 || !ctype_digit($_GET['id'])) {
    echo '<script> history.back()</script>';
    exit();
}

require_once './src/requester.php';
require_once './src/team.php';
require_once './src/ticket.php';
require_once './src/ticket-event.php';
require_once './src/team-member.php';
require_once './src/comment.php';

$err = '';
$msg = '';
$ticket = Ticket::find($_GET['id']);
//print_r($ticket->team_member);die();

$teams = Team::findAll();

$events = Event::findByTicket($ticket->id);

$comments = Comment::findByTicket($ticket->id);

if (isset($_POST['submit'])) {

    $team = $_POST["team_member"];
    // print_r($team);die();
    $id = $_GET['id'];
    //print_r($id);die();

    try {

        $ticket = new Ticket([

            'team_member' => $team,
            'title' => $ticket->title,
            'body' => $ticket->body,
            'requester' => $ticket->requester,
            'team' => $ticket->team,
            'status' => $ticket->status,
            'priority' => $ticket->priority,
        ]);

        $updateTicket = $ticket->update($id);
        // print_r($updateTicket);die();

        $msg = "Ticket assigned successfully";

    } catch (Exception $e) {

        $err = "Failed to assigned ticket";

    }
}

if (isset($_POST['comment'])) {

    $body = $_POST["body"];

    try {
        $comment = new Comment([
            'ticket-id' => $ticket->id,
            'team-member' => $ticket->team_member,
            'body' => $body,

        ]);
        $comment->save();
        //  print_r($cv);die();
        $msg = "Successfully comment on the ticket";

    } catch (Exception $e) {
        var_dump($e);
        $err = "Failed to comment on the ticket";
    }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Details - Helpdesk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --treasury-brown: #8B4513;
            --treasury-tan: #D2B48C;
            --treasury-light: #f8f9fc;
        }
        body {
            background: var(--treasury-light);
        }
        .app-shell { display: flex; height: 100vh; }
        .content {
            padding: calc(60px + 1rem) 1.25rem 2rem;
            height: 100vh;
            overflow-y: auto;
            flex: 1;
        }
        .ticket-header {
            background: linear-gradient(135deg, var(--treasury-brown) 0%, var(--treasury-tan) 100%);
            color: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 12px rgba(139, 69, 19, 0.15);
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
        }
        .card-header {
            background: linear-gradient(135deg, rgba(139, 69, 19, 0.05) 0%, rgba(210, 180, 140, 0.05) 100%);
            border-bottom: 2px solid rgba(139, 69, 19, 0.1);
            padding: 1rem 1.5rem;
            font-weight: 600;
        }
        .comment-item {
            border-left: 3px solid var(--treasury-tan);
            background: #fff;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 8px;
            transition: all 0.2s;
        }
        .comment-item:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateX(5px);
        }
        .event-item {
            border-left: 3px solid var(--treasury-brown);
            background: #fff;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 8px;
        }
        .btn-treasury {
            background: linear-gradient(135deg, var(--treasury-brown) 0%, var(--treasury-tan) 100%);
            color: white;
            border: none;
        }
        .btn-treasury:hover {
            opacity: 0.9;
            color: white;
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
                    <h1 class="h3 mb-1">
                        <i class="fas fa-ticket-alt me-2" style="color: var(--treasury-brown);"></i>Ticket Details
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Ticket #<?php echo $ticket->id; ?></li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <?php include './includes/create-ticket-button.php'; ?>
                </div>
            </div>

            <!-- Ticket Header Card -->
            <div class="ticket-header">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <?php echo $ticket->displayStatusBadge()?>
                        <h4 class="mt-2 mb-1"><?php echo htmlspecialchars($ticket->title)?></h4>
                        <small class="opacity-75">
                            <i class="fas fa-clock me-1"></i>
                            <?php $date = new DateTime($ticket->created_at ?? '' );?>
                            Created: <?php echo $date->format('d M Y, H:i:s')?>
                        </small>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-light text-dark">Priority: <?php echo ucfirst($ticket->priority ?? 'Medium'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Assignment Form -->
            <div class="card mb-3">
                <div class="card-header">
                    <i class="fas fa-user-cog me-2"></i>Ticket Assignment
                </div>
            <div class="card-body">
                <form method="post">
                    <div class="col-lg-8 col-md-8 col-sm-12 offset-lg-2 offset-md-2">
                    <?php if(strlen($err) > 1) :?>
                <div class="alert alert-danger text-center my-3" role="alert"> <strong>Failed! </strong> <?php echo $err;?></div>
                <?php endif?>

                <?php if(strlen($msg) > 1) :?>
                <div class="alert alert-success text-center my-3" role="alert"> <strong>Success! </strong> <?php echo $msg;?></div>
                <?php endif?>
                        <div class="form-group row">
                            <label for="team" class="col-sm-3 col-form-label">Team</label>
                            <div class="col-sm-8">
                                <select class="form-control" id="team-dropdown"
                                    onchange="getTeamMember(event.target.value)">
                                    <option>--select--</option>
                                    <?php foreach($teams as $team):?>
                                    <option <?php echo $team->id == $ticket->team ? 'selected' : null?> value="<?php echo $team->id?>"><?php echo $team->name?></option>
                                    <?php endforeach?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="assigned" class="col-sm-3 col-form-label">Assigned</label>
                            <div class="col-sm-8">
                                <select class="form-control"  name="team_member" id="team-member-dropdown">
                                    <option>--select--</option>
                                </select>
                            </div>
                        </div>
                        <div class="text-center">
                            <button class="btn btn-treasury" type="submit" name="submit">
                                <i class="fas fa-save me-2"></i>Assign Ticket
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Change Status Card -->
        <div class="card mb-3">
            <div class="card-header">
                <i class="fas fa-exchange-alt me-2"></i>Change Ticket Status
            </div>
            <div class="card-body">
                <form id="formData" method="POST">
                    <div class="row">
                        <div class="col-md-6 offset-md-3">
                            <input type="hidden" name="id" value="<?php echo $ticket->id ?>">
                            <div class="mb-3">
                                <label for="status" class="form-label">Select New Status</label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="">--Select Status--</option>
                                    <option value="open">Open</option>
                                    <option value="pending">Pending</option>
                                    <option value="closed">Closed</option>
                                    <option value="solved">Solved</option>
                                </select>
                            </div>
                            <div class="text-center">
                                <button type="submit" name="submit" class="btn btn-treasury">
                                    <i class="fas fa-check-circle me-2"></i>Update Status
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
                <div id="msg" class="mt-3"></div>
            </div>
        </div>

        <!-- Add Comment Card -->
        <div class="card mb-3">
            <div class="card-header">
                <i class="fas fa-comment-dots me-2"></i>Add Comment
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-8 offset-md-2">
                            <div class="mb-3">
                                <label for="body" class="form-label">Your Comment</label>
                                <textarea class="form-control" name="body" id="body" rows="4" required placeholder="Enter your comment here..."></textarea>
                            </div>
                            <div class="text-center">
                                <button type="submit" name="comment" class="btn btn-treasury">
                                    <i class="fas fa-paper-plane me-2"></i>Post Comment
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Comments Section -->
        <div class="card mb-3">
            <div class="card-header">
                <i class="fas fa-comments me-2"></i>Comments (<?php echo count($comments); ?>)
            </div>
            <div class="card-body">
                <?php if(empty($comments)): ?>
                    <p class="text-muted text-center py-4">No comments yet. Be the first to comment!</p>
                <?php else: ?>
                    <?php foreach($comments as $c):?>
                    <div class="comment-item">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="mb-0">
                                <i class="fas fa-user-circle me-2" style="color: var(--treasury-brown);"></i>
                                <?php echo htmlspecialchars(TeamMember::getName($c->team_member))?>
                            </h6>
                            <?php $d = new DateTime($c->created_at)?>
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>
                                <?php echo $d->format('d M Y, H:i:s')?>
                            </small>
                        </div>
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($c->body))?></p>
                    </div>
                    <?php endforeach?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Events Section -->
        <div class="card mb-3">
            <div class="card-header">
                <i class="fas fa-history me-2"></i>Activity Timeline (<?php echo count($events); ?>)
            </div>
            <div class="card-body">
                <?php if(empty($events)): ?>
                    <p class="text-muted text-center py-4">No activity recorded yet.</p>
                <?php else: ?>
                    <?php foreach($events as $e):?>
                    <div class="event-item">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="mb-0">
                                <i class="fas fa-user-cog me-2" style="color: var(--treasury-brown);"></i>
                                <?php echo htmlspecialchars(TeamMember::getName($e->user))?>
                            </h6>
                            <?php $d = new DateTime($e->created_at)?>
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>
                                <?php echo $d->format('d M Y, H:i:s')?>
                            </small>
                        </div>
                        <p class="mb-0"><?php echo htmlspecialchars($e->body)?></p>
                    </div>
                    <?php endforeach?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center text-muted py-3">
            <small>© <?php echo date('Y'); ?> ICT Helpdesk. All rights reserved.</small>
        </div>
    </div>
    </section>
</div>
<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="js/sb-admin.min.js"></script>

<script>
jQuery(document).ready(function($) {
    $('#formData').submit(function (e) {
        e.preventDefault();
        var formData = new FormData($(this)[0]);
        $('#msg').html(
            '<div class="alert alert-info text-center"><i class="fas fa-spinner fa-spin me-2"></i><strong>Processing...</strong></div>'
        );

        $.ajax({
            url: './src/update-ticket.php',
            type: 'post',
            dataType: 'text',
            data: formData,
            contentType: false,
            processData: false,
            success: function (res) {
                let result = JSON.parse(res);
                if (result.status == 200) {
                    $('#msg').html(
                        '<div class="alert alert-success text-center"><i class="fas fa-check-circle me-2"></i><strong>Success!</strong> ' +
                        result.msg + '</div>');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    $('#msg').html(
                        '<div class="alert alert-danger text-center"><i class="fas fa-times-circle me-2"></i><strong>Failed!</strong> ' +
                        result.msg + '</div>');
                }
            },
            error: function() {
                $('#msg').html(
                    '<div class="alert alert-danger text-center"><i class="fas fa-exclamation-triangle me-2"></i><strong>Error!</strong> Unable to update ticket status.</div>');
            }
        });
    });

    // Load create ticket modal functionality
    $.getScript('./includes/create-ticket-modal.js');
});
</script>

<!-- Include Create Ticket Modal -->
<?php include './includes/create-ticket-modal.php'; ?>

</body>
</html>