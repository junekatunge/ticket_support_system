<?php
session_start();
require_once __DIR__ . '/../src/database.php';
require_once __DIR__ . '/../src/message.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logged-in']) || $_SESSION['logged-in'] == false) {
    echo json_encode(['success' => false, 'count' => 0]);
    exit();
}

$count = Message::getUnreadCount();

echo json_encode([
    'success' => true,
    'count' => $count
]);
