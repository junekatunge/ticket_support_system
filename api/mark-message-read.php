<?php
session_start();
require_once __DIR__ . '/../src/database.php';
require_once __DIR__ . '/../src/message.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logged-in']) || $_SESSION['logged-in'] == false) {
    echo json_encode(['success' => false]);
    exit();
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false]);
    exit();
}

$messageId = intval($_GET['id']);
$result = Message::markAsRead($messageId);

echo json_encode(['success' => $result]);
