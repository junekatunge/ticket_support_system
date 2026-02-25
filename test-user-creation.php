<?php
session_start();

// Set up a fake admin session for testing
if (!isset($_SESSION['user'])) {
    $_SESSION['logged-in'] = true;
    $_SESSION['user'] = (object)[
        'id' => 1,
        'name' => 'John Doe',
        'email' => 'johndoe@helpdesk.com',
        'role' => 'admin'
    ];
}

require_once './src/Database.php';
require_once './src/user.php';
require_once './src/notification.php';
require './src/helper-functions.php';

echo "<h1>User Creation & Notification Test</h1>";
echo "<hr>";

// Check if admin exists
$db = Database::getInstance();
$adminResult = $db->query("SELECT id, name, email FROM users WHERE role = 'admin'");
echo "<h2>Admin Users:</h2>";
while ($row = $adminResult->fetch_assoc()) {
    echo "ID: {$row['id']}, Name: {$row['name']}, Email: {$row['email']}<br>";
}
echo "<hr>";

// Try to create a test user
$testName = "Test User " . time();
$testEmail = "test" . time() . "@example.com";
$testPhone = "0700000000";
$testPassword = "password123";

echo "<h2>Creating Test User:</h2>";
echo "Name: $testName<br>";
echo "Email: $testEmail<br>";
echo "Phone: $testPhone<br>";
echo "Role: member<br>";
echo "<hr>";

try {
    $newUser = new User([
        'name' => $testName,
        'email' => $testEmail,
        'phone' => $testPhone,
        'password' => password_hash($testPassword, PASSWORD_DEFAULT),
        'role' => 'member',
        'last_password' => password_hash($testPassword, PASSWORD_DEFAULT)
    ]);

    $savedUser = $newUser->save();
    echo "<p style='color: green;'>✓ User created successfully! ID: {$savedUser->id}</p>";

    // Try to send notification
    echo "<h2>Sending Notification to Admins:</h2>";

    try {
        $notificationSent = Notification::notifyAdmins(
            'user_created',
            'New User Created',
            "A new user '{$testName}' has been created with role: member",
            $savedUser->id ?? null
        );

        if ($notificationSent) {
            echo "<p style='color: green;'>✓ Notification sent successfully!</p>";
        } else {
            echo "<p style='color: red;'>✗ Notification::notifyAdmins() returned false</p>";
        }
    } catch (Exception $notifException) {
        echo "<p style='color: red;'>✗ Notification exception: " . $notifException->getMessage() . "</p>";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Unable to create user: " . $e->getMessage() . "</p>";
}

echo "<hr>";

// Check notifications in database
echo "<h2>Notifications in Database:</h2>";
$notifResult = $db->query("SELECT * FROM notifications ORDER BY created_at DESC LIMIT 10");
if ($notifResult->num_rows > 0) {
    while ($row = $notifResult->fetch_assoc()) {
        echo "ID: {$row['id']}, User ID: {$row['user_id']}, Type: {$row['type']}, Title: {$row['title']}<br>";
    }
} else {
    echo "No notifications found.<br>";
}

echo "<hr>";
echo "<a href='newuser.php'>Go to New User Form</a> | ";
echo "<a href='dashboard.php'>Go to Dashboard</a>";
?>
