<?php
if (!class_exists('Message')) {
    require_once __DIR__ . '/database.php';
    require_once __DIR__ . '/Session.php';

    class Message {
        private $db;

        public function __construct() {
            $this->db = Database::getInstance();
        }

        /**
         * Get unread message count for current user
         */
        public static function getUnreadCount() {
            $db = Database::getInstance();

            if (!isset($_SESSION['user']) || !isset($_SESSION['user']->id)) {
                return 0;
            }

            $userId = $_SESSION['user']->id;

            $stmt = $db->prepare("SELECT COUNT(*) as count FROM messages WHERE receiver_id = ? AND is_read = 0");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            return $row['count'] ?? 0;
        }

        /**
         * Get all messages for current user (inbox)
         */
        public static function getInbox($limit = 50) {
            $db = Database::getInstance();

            if (!isset($_SESSION['user']) || !isset($_SESSION['user']->id)) {
                return [];
            }

            $userId = $_SESSION['user']->id;

            $stmt = $db->prepare("
                SELECT m.*,
                       u.name as sender_name,
                       u.email as sender_email,
                       u.role as sender_role
                FROM messages m
                LEFT JOIN users u ON m.sender_id = u.id
                WHERE m.receiver_id = ? AND m.receiver_type = 'user'
                ORDER BY m.created_at DESC
                LIMIT ?
            ");
            $stmt->bind_param("ii", $userId, $limit);
            $stmt->execute();
            $result = $stmt->get_result();

            $messages = [];
            while ($row = $result->fetch_assoc()) {
                $messages[] = $row;
            }

            return $messages;
        }

        /**
         * Get sent messages for current user
         */
        public static function getSent($limit = 50) {
            $db = Database::getInstance();

            if (!isset($_SESSION['user']) || !isset($_SESSION['user']->id)) {
                return [];
            }

            $userId = $_SESSION['user']->id;

            $stmt = $db->prepare("
                SELECT m.*,
                       CASE
                           WHEN m.receiver_type = 'user' THEN u.name
                           WHEN m.receiver_type = 'requester' THEN r.name
                       END as receiver_name,
                       CASE
                           WHEN m.receiver_type = 'user' THEN u.email
                           WHEN m.receiver_type = 'requester' THEN r.email
                       END as receiver_email,
                       CASE
                           WHEN m.receiver_type = 'user' THEN u.role
                           WHEN m.receiver_type = 'requester' THEN 'requester'
                       END as receiver_role
                FROM messages m
                LEFT JOIN users u ON m.receiver_id = u.id AND m.receiver_type = 'user'
                LEFT JOIN requester r ON m.receiver_id = r.id AND m.receiver_type = 'requester'
                WHERE m.sender_id = ?
                ORDER BY m.created_at DESC
                LIMIT ?
            ");
            $stmt->bind_param("ii", $userId, $limit);
            $stmt->execute();
            $result = $stmt->get_result();

            $messages = [];
            while ($row = $result->fetch_assoc()) {
                $messages[] = $row;
            }

            return $messages;
        }

        /**
         * Get a single message by ID
         */
        public static function getMessageById($messageId) {
            $db = Database::getInstance();

            if (!isset($_SESSION['user']) || !isset($_SESSION['user']->id)) {
                return null;
            }

            $userId = $_SESSION['user']->id;

            $stmt = $db->prepare("
                SELECT m.*,
                       sender.name as sender_name,
                       sender.email as sender_email,
                       sender.role as sender_role,
                       CASE
                           WHEN m.receiver_type = 'user' THEN u.name
                           WHEN m.receiver_type = 'requester' THEN r.name
                       END as receiver_name,
                       CASE
                           WHEN m.receiver_type = 'user' THEN u.email
                           WHEN m.receiver_type = 'requester' THEN r.email
                       END as receiver_email,
                       CASE
                           WHEN m.receiver_type = 'user' THEN u.role
                           WHEN m.receiver_type = 'requester' THEN 'requester'
                       END as receiver_role
                FROM messages m
                LEFT JOIN users sender ON m.sender_id = sender.id
                LEFT JOIN users u ON m.receiver_id = u.id AND m.receiver_type = 'user'
                LEFT JOIN requester r ON m.receiver_id = r.id AND m.receiver_type = 'requester'
                WHERE m.id = ? AND (m.sender_id = ? OR (m.receiver_id = ? AND m.receiver_type = 'user'))
            ");
            $stmt->bind_param("iii", $messageId, $userId, $userId);
            $stmt->execute();
            $result = $stmt->get_result();

            return $result->fetch_assoc();
        }

        /**
         * Send a new message
         */
        public static function send($receiverId, $subject, $body, $receiverType = 'user') {
            $db = Database::getInstance();

            if (!isset($_SESSION['user']) || !isset($_SESSION['user']->id)) {
                return false;
            }

            $senderId = $_SESSION['user']->id;

            $stmt = $db->prepare("INSERT INTO messages (sender_id, receiver_id, receiver_type, subject, body) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iisss", $senderId, $receiverId, $receiverType, $subject, $body);

            return $stmt->execute();
        }

        /**
         * Mark message as read
         */
        public static function markAsRead($messageId) {
            $db = Database::getInstance();

            if (!isset($_SESSION['user']) || !isset($_SESSION['user']->id)) {
                return false;
            }

            $userId = $_SESSION['user']->id;

            $stmt = $db->prepare("UPDATE messages SET is_read = 1 WHERE id = ? AND receiver_id = ?");
            $stmt->bind_param("ii", $messageId, $userId);

            return $stmt->execute();
        }

        /**
         * Delete a message
         */
        public static function delete($messageId) {
            $db = Database::getInstance();

            if (!isset($_SESSION['user']) || !isset($_SESSION['user']->id)) {
                return false;
            }

            $userId = $_SESSION['user']->id;

            $stmt = $db->prepare("DELETE FROM messages WHERE id = ? AND (sender_id = ? OR receiver_id = ?)");
            $stmt->bind_param("iii", $messageId, $userId, $userId);

            return $stmt->execute();
        }

        /**
         * Get all users for messaging (excluding current user)
         * Includes both team members and requesters
         */
        public static function getAllUsers() {
            $db = Database::getInstance();

            if (!isset($_SESSION['user']) || !isset($_SESSION['user']->id)) {
                return [];
            }

            $userId = $_SESSION['user']->id;

            // Get team members (users)
            $stmt = $db->prepare("SELECT id, name, email, role, 'member' as user_type FROM users WHERE id != ? ORDER BY name ASC");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();

            $users = [];
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }

            // Get requesters (non-members)
            $requesterQuery = "SELECT id, name, email, 'requester' as role, 'requester' as user_type FROM requester ORDER BY name ASC";
            $requesterResult = $db->query($requesterQuery);

            while ($row = $requesterResult->fetch_assoc()) {
                $users[] = $row;
            }

            return $users;
        }

        /**
         * Time ago helper function
         */
        public static function timeAgo($timestamp) {
            $time = strtotime($timestamp);
            $diff = time() - $time;

            if ($diff < 60) {
                return 'Just now';
            } elseif ($diff < 3600) {
                $mins = floor($diff / 60);
                return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
            } elseif ($diff < 86400) {
                $hours = floor($diff / 3600);
                return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
            } elseif ($diff < 604800) {
                $days = floor($diff / 86400);
                return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
            } else {
                return date('M j, Y', $time);
            }
        }
    }
}
