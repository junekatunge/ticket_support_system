<?php
if (!class_exists('Database')) {
    class Database {
        private static $instance = null;
        private static $host = 'localhost';
        private static $user = 'root';
        private static $password = '';
        private static $db = 'helpdesk_core_php';

        public static function getInstance() {
            if (self::$instance === null) {
                self::$instance = new mysqli(self::$host, self::$user, self::$password, self::$db);

                if (self::$instance->connect_error) {
                    die("Database connection failed: " . self::$instance->connect_error);
                }
            }

            return self::$instance;
        }
    }
}
