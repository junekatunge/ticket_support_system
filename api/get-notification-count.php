<?php
header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['logged-in']) || $_SESSION['logged-in'] == false) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

require_once '../src/database.php';
require_once '../src/notification.php';

try {
    $count = Notification::getUnreadCount();
    
    echo json_encode([
        'success' => true,
        'count' => $count
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>