<?php
require_once './src/Database.php';
require_once './src/notification.php';

echo "Testing notification system...<br>";

// Test creating a notification directly
$result = Notification::create(1, 'system', 'Test Notification', 'This is a test notification');
echo "Direct create result: " . ($result ? 'SUCCESS' : 'FAILED') . "<br>";

// Test notifying admins
$result2 = Notification::notifyAdmins('user_created', 'Test Admin Notification', 'This is a test admin notification');
echo "Notify admins result: " . ($result2 ? 'SUCCESS' : 'FAILED') . "<br>";

// Check if notifications were created
$db = Database::getInstance();
$result3 = $db->query("SELECT * FROM notifications ORDER BY id DESC LIMIT 5");
echo "<br>Recent notifications:<br>";
while ($row = $result3->fetch_assoc()) {
    echo "ID: {$row['id']}, User: {$row['user_id']}, Title: {$row['title']}, Message: {$row['message']}<br>";
}
