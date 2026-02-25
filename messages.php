<?php
session_start();
if (!isset($_SESSION['logged-in']) || $_SESSION['logged-in'] == false) {
    header('Location: ./index.php');
    exit();
}
$user = $_SESSION['user'];
require_once './src/Database.php';
require_once './src/message.php';

$db = Database::getInstance();

// Handle message actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['send_message'])) {
        $receiverIdRaw = $_POST['receiver_id'];
        $subject = $_POST['subject'];
        $body = $_POST['body'];

        // Parse receiver ID (format: "user_1" or "requester_5")
        $parts = explode('_', $receiverIdRaw);
        $receiverType = $parts[0]; // 'user' or 'requester'
        $receiverId = intval($parts[1]);

        if (Message::send($receiverId, $subject, $body, $receiverType)) {
            $success_message = "Message sent successfully!";
        } else {
            $error_message = "Failed to send message.";
        }
    } elseif (isset($_POST['delete_message'])) {
        $messageId = $_POST['message_id'];
        if (Message::delete($messageId)) {
            $success_message = "Message deleted successfully!";
        } else {
            $error_message = "Failed to delete message.";
        }
    } elseif (isset($_POST['mark_read'])) {
        $messageId = $_POST['message_id'];
        Message::markAsRead($messageId);
    }
}

// Get messages based on view (inbox/sent)
$view = isset($_GET['view']) ? $_GET['view'] : 'inbox';
$messages = ($view === 'sent') ? Message::getSent() : Message::getInbox();
$unreadCount = Message::getUnreadCount();
$allUsers = Message::getAllUsers();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Messages - Helpdesk</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

  <style>
    :root {
      --bg-soft: #f8fafc;
      --treasury-brown: #8B4513;
      --treasury-tan: #D2B48C;
      --treasury-navy: #1e3a5f;
      --treasury-gold: #c9a96e;
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

    .message-card {
      border-radius: 12px;
      border: 1px solid #e2e8f0;
      transition: all 0.2s;
      cursor: pointer;
      background: white;
    }

    .message-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(139, 69, 19, 0.15);
      border-color: var(--treasury-tan);
    }

    .message-card.unread {
      background: rgba(210, 180, 140, 0.05);
      border-left: 4px solid var(--treasury-brown);
      font-weight: 600;
    }

    .view-btn {
      border-radius: 8px;
      padding: 0.5rem 1.5rem;
      font-weight: 500;
      border: 1px solid #e2e8f0;
      background: white;
      transition: all 0.2s;
    }

    .view-btn:hover {
      background: var(--treasury-tan);
      color: white;
      border-color: var(--treasury-tan);
    }

    .view-btn.active {
      background: linear-gradient(135deg, var(--treasury-brown), var(--treasury-tan));
      color: white;
      border-color: transparent;
    }

    .compose-btn {
      background: linear-gradient(135deg, var(--treasury-brown), var(--treasury-tan));
      color: white;
      border: none;
      border-radius: 10px;
      padding: 0.75rem 1.5rem;
      font-weight: 600;
      box-shadow: 0 4px 12px rgba(139, 69, 19, 0.3);
      transition: all 0.2s;
    }

    .compose-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(139, 69, 19, 0.4);
      color: white;
    }

    .message-avatar {
      width: 48px;
      height: 48px;
      border-radius: 10px;
      background: linear-gradient(135deg, var(--treasury-tan), var(--treasury-brown));
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: 600;
      font-size: 18px;
    }

    .badge-unread {
      background: #ef4444;
      color: white;
      border-radius: 12px;
      padding: 2px 8px;
      font-size: 11px;
      font-weight: 600;
    }

    .modal-content {
      border-radius: 16px;
      border: none;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }

    .modal-header {
      background: linear-gradient(135deg, var(--treasury-brown), var(--treasury-tan));
      color: white;
      border-radius: 16px 16px 0 0;
      border: none;
    }

    .empty-state {
      padding: 4rem 2rem;
      text-align: center;
      color: #64748b;
    }

    .empty-state i {
      font-size: 4rem;
      opacity: 0.3;
      margin-bottom: 1rem;
    }
  </style>
</head>
<body>
  <div class="app-shell">
    <!-- SIDEBAR -->
    <?php include 'sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <div class="content">
      <!-- NAVBAR -->
      <?php include 'navbar.php'; ?>

      <!-- Messages Section -->
      <div class="container-fluid mt-3">
        <?php if (isset($success_message)): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h4 class="mb-1" style="font-weight: 700; color: var(--treasury-navy);">
              <i class="fas fa-envelope me-2"></i>Messages
            </h4>
            <p class="text-muted mb-0">Communicate with your team</p>
          </div>
          <button class="compose-btn" data-bs-toggle="modal" data-bs-target="#composeModal">
            <i class="fas fa-plus me-2"></i>Compose Message
          </button>
        </div>

        <!-- View Toggle -->
        <div class="mb-4">
          <div class="btn-group" role="group">
            <a href="?view=inbox" class="view-btn <?= $view === 'inbox' ? 'active' : '' ?>">
              <i class="fas fa-inbox me-2"></i>Inbox
              <?php if ($unreadCount > 0): ?>
                <span class="badge-unread ms-2"><?= $unreadCount ?></span>
              <?php endif; ?>
            </a>
            <a href="?view=sent" class="view-btn <?= $view === 'sent' ? 'active' : '' ?>">
              <i class="fas fa-paper-plane me-2"></i>Sent
            </a>
          </div>
        </div>

        <!-- Messages List -->
        <div class="row">
          <div class="col-12">
            <?php if (empty($messages)): ?>
              <div class="empty-state">
                <i class="fas fa-envelope-open"></i>
                <h5>No messages yet</h5>
                <p class="text-muted">Start a conversation by composing a new message</p>
              </div>
            <?php else: ?>
              <?php foreach ($messages as $message): ?>
                <div class="message-card p-3 mb-3 <?= $view === 'inbox' && !$message['is_read'] ? 'unread' : '' ?>"
                     data-bs-toggle="modal"
                     data-bs-target="#viewMessageModal"
                     onclick="viewMessage(<?= $message['id'] ?>)">
                  <div class="d-flex align-items-start">
                    <div class="message-avatar me-3">
                      <?php
                      $name = $view === 'inbox' ? ($message['sender_name'] ?? 'Unknown') : ($message['receiver_name'] ?? 'Unknown');
                      $names = explode(' ', trim($name));
                      $initials = strtoupper(substr($names[0], 0, 1));
                      if (count($names) > 1) {
                        $initials .= strtoupper(substr(end($names), 0, 1));
                      }
                      echo $initials;
                      ?>
                    </div>
                    <div class="flex-grow-1">
                      <div class="d-flex justify-content-between align-items-start mb-1">
                        <div>
                          <h6 class="mb-0" style="font-weight: 600; color: var(--treasury-navy);">
                            <?= htmlspecialchars($name) ?>
                            <?php if ($view === 'inbox' && !$message['is_read']): ?>
                              <span class="badge-unread ms-2">NEW</span>
                            <?php endif; ?>
                          </h6>
                          <small class="text-muted">
                            <?= htmlspecialchars($message['sender_email'] ?? $message['receiver_email'] ?? '') ?>
                          </small>
                        </div>
                        <small class="text-muted">
                          <i class="fas fa-clock me-1"></i><?= Message::timeAgo($message['created_at']) ?>
                        </small>
                      </div>
                      <h6 class="mb-1" style="color: var(--treasury-brown);">
                        <?= htmlspecialchars($message['subject']) ?>
                      </h6>
                      <p class="mb-0 text-muted" style="font-size: 0.9rem;">
                        <?= htmlspecialchars(substr($message['body'], 0, 100)) ?>
                        <?= strlen($message['body']) > 100 ? '...' : '' ?>
                      </p>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Compose Message Modal -->
  <div class="modal fade" id="composeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Compose Message</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <form method="POST">
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label fw-bold">To</label>
              <select class="form-select" name="receiver_id" required>
                <option value="">Select recipient...</option>
                <?php
                // Separate users into team members and requesters
                $teamMembers = array_filter($allUsers, function($u) { return $u['user_type'] === 'member'; });
                $requesters = array_filter($allUsers, function($u) { return $u['user_type'] === 'requester'; });
                ?>

                <?php if (!empty($teamMembers)): ?>
                  <optgroup label="Team Members">
                    <?php foreach ($teamMembers as $u): ?>
                      <option value="user_<?= $u['id'] ?>">
                        <?= htmlspecialchars($u['name']) ?> (<?= htmlspecialchars($u['email']) ?>) - <?= ucfirst($u['role']) ?>
                      </option>
                    <?php endforeach; ?>
                  </optgroup>
                <?php endif; ?>

                <?php if (!empty($requesters)): ?>
                  <optgroup label="Requesters">
                    <?php foreach ($requesters as $u): ?>
                      <option value="requester_<?= $u['id'] ?>">
                        <?= htmlspecialchars($u['name']) ?> (<?= htmlspecialchars($u['email']) ?>)
                      </option>
                    <?php endforeach; ?>
                  </optgroup>
                <?php endif; ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Subject</label>
              <input type="text" class="form-control" name="subject" required>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Message</label>
              <textarea class="form-control" name="body" rows="6" required></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="send_message" class="btn" style="background: linear-gradient(135deg, var(--treasury-brown), var(--treasury-tan)); color: white;">
              <i class="fas fa-paper-plane me-2"></i>Send Message
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- View Message Modal -->
  <div class="modal fade" id="viewMessageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-envelope-open me-2"></i>Message Details</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="messageContent">
          <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <form method="POST" style="display: inline;" id="deleteForm">
            <input type="hidden" name="message_id" id="deleteMessageId">
            <button type="submit" name="delete_message" class="btn btn-danger">
              <i class="fas fa-trash me-2"></i>Delete
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    function viewMessage(messageId) {
      const content = document.getElementById('messageContent');
      const deleteId = document.getElementById('deleteMessageId');

      content.innerHTML = `
        <div class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
      `;

      deleteId.value = messageId;

      // Fetch message details via AJAX
      fetch('api/get-message.php?id=' + messageId)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const msg = data.message;
            content.innerHTML = `
              <div class="mb-3 pb-3 border-bottom">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <h6 class="mb-1" style="color: var(--treasury-navy);">From: ${msg.sender_name}</h6>
                    <small class="text-muted">${msg.sender_email}</small>
                  </div>
                  <small class="text-muted">
                    <i class="fas fa-clock me-1"></i>${msg.time_ago}
                  </small>
                </div>
              </div>
              <h5 class="mb-3" style="color: var(--treasury-brown);">${msg.subject}</h5>
              <div style="white-space: pre-wrap; line-height: 1.6;">${msg.body}</div>
            `;

            // Mark as read
            if (!msg.is_read) {
              fetch('api/mark-message-read.php?id=' + messageId);
            }
          } else {
            content.innerHTML = '<div class="alert alert-danger">Failed to load message</div>';
          }
        })
        .catch(error => {
          content.innerHTML = '<div class="alert alert-danger">Error loading message</div>';
        });
    }
  </script>
</body>
</html>
