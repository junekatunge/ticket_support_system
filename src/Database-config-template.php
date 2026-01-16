<?php
/**
 * DATABASE CONFIGURATION TEMPLATE
 *
 * INSTRUCTIONS:
 * 1. After uploading to hosting, rename this file from Database-config-template.php to Database.php
 * 2. OR edit the existing Database.php file
 * 3. Replace the placeholders below with your actual database credentials from your hosting provider
 */

class Database {
    private static $connection = null;

    private function __construct() {
        // ⚠️ REPLACE THESE VALUES WITH YOUR HOSTING DATABASE CREDENTIALS ⚠️

        $host = 'localhost';                    // Usually 'localhost' for most hosting
        $username = 'YOUR_DATABASE_USERNAME';   // From hosting control panel
        $password = 'YOUR_DATABASE_PASSWORD';   // From hosting control panel
        $database = 'YOUR_DATABASE_NAME';       // From hosting control panel

        // Create connection
        self::$connection = new mysqli($host, $username, $password, $database);

        // Check connection
        if (self::$connection->connect_error) {
            die("Connection failed: " . self::$connection->connect_error);
        }
    }

    public static function getInstance() {
        if (self::$connection === null) {
            new Database();
        }
        return self::$connection;
    }
}
?>
