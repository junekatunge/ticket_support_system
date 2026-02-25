<?php
session_start();
require_once __DIR__ . '/../src/database.php';
require_once __DIR__ . '/../src/message.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logged-in']) || $_SESSION['logged-in'] == false) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'Message ID required']);
    exit();
}

$messageId = intval($_GET['id']);
$message = Message::getMessageById($messageId);

if ($message) {
    $message['time_ago'] = Message::timeAgo($message['created_at']);
    echo json_encode([
        'success' => true,
        'message' => $message
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Message not found']);
}
