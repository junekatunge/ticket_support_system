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
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $notifications = Notification::getRecentNotifications(null, $limit);
    
    // Format notifications for JSON response
    $formattedNotifications = [];
    foreach ($notifications as $notification) {
        $formattedNotifications[] = [
            'id' => $notification['id'],
            'subject' => $notification['subject'],
            'message' => $notification['notification_message'],
            'type' => $notification['notification_type'],
            'requester_name' => $notification['requester_name'] ?? 'Unknown',
            'time_ago' => Notification::timeAgo($notification['created_at']),
            'badge_color' => Notification::getBadgeColor($notification['notification_type']),
            'priority' => $notification['priority'],
            'status' => $notification['status'],
            'created_at' => $notification['created_at']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'notifications' => $formattedNotifications,
        'count' => count($formattedNotifications)
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>