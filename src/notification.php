<?php
class Notification {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Create a new notification
     */
    public static function create($userId, $type, $title, $message, $relatedId = null) {
        $db = Database::getInstance();

        try {
            $stmt = $db->prepare("INSERT INTO notifications (user_id, type, title, message, related_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("isssi", $userId, $type, $title, $message, $relatedId);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Create notification error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Notify all admins
     */
    public static function notifyAdmins($type, $title, $message, $relatedId = null) {
        $db = Database::getInstance();

        try {
            $result = $db->query("SELECT id FROM users WHERE role = 'admin'");
            while ($row = $result->fetch_assoc()) {
                self::create($row['id'], $type, $title, $message, $relatedId);
            }
            return true;
        } catch (Exception $e) {
            error_log("Notify admins error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get unread notification count for a user
     */
    public static function getUnreadCount($userId = null) {
        $db = Database::getInstance();

        if (!$userId && isset($_SESSION['user'])) {
            $userId = $_SESSION['user']->id ?? null;
        }

        if (!$userId) return 0;

        try {
            // Count from notifications table
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            $notificationCount = $row['count'] ?? 0;

            // Also count urgent/high priority tickets for backward compatibility
            $stmt = $db->prepare("
                SELECT COUNT(*) as count FROM ticket
                WHERE (priority = 'high' OR priority = 'urgent')
                AND status IN ('open', 'pending')
                AND (team_member = ? OR team_member IS NULL)
            ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            $ticketCount = $row['count'] ?? 0;

            return $notificationCount + $ticketCount;
        } catch (Exception $e) {
            error_log("Notification count error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get recent notifications for a user
     */
    public static function getRecentNotifications($userId = null, $limit = 10) {
        $db = Database::getInstance();

        if (!$userId && isset($_SESSION['user'])) {
            $userId = $_SESSION['user']->id ?? null;
        }

        if (!$userId) return [];

        try {
            $notifications = [];

            // Get system notifications from notifications table
            $stmt = $db->prepare("
                SELECT
                    id,
                    type as notification_type,
                    title as subject,
                    message as notification_message,
                    related_id,
                    is_read,
                    created_at,
                    'system' as source
                FROM notifications
                WHERE user_id = ?
                ORDER BY created_at DESC
                LIMIT ?
            ");
            $stmt->bind_param("ii", $userId, $limit);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $notifications[] = $row;
            }

            // Get ticket-based notifications for backward compatibility
            $remaining = $limit - count($notifications);
            if ($remaining > 0) {
                $stmt = $db->prepare("
                    SELECT
                        t.id,
                        t.title as subject,
                        r.name as requester_name,
                        CASE
                            WHEN t.priority = 'urgent' THEN 'Urgent ticket requires immediate attention'
                            WHEN t.priority = 'high' THEN 'High priority ticket needs attention'
                            WHEN TIMESTAMPDIFF(HOUR, t.created_at, NOW()) > 72 THEN 'Ticket is overdue'
                            ELSE 'New ticket assigned'
                        END as notification_message,
                        CASE
                            WHEN t.priority = 'urgent' THEN 'urgent'
                            WHEN t.priority = 'high' THEN 'high'
                            WHEN TIMESTAMPDIFF(HOUR, t.created_at, NOW()) > 72 THEN 'overdue'
                            ELSE 'normal'
                        END as notification_type,
                        t.created_at,
                        'ticket' as source
                    FROM ticket t
                    LEFT JOIN requester r ON t.requester = r.id
                    WHERE (
                        (t.priority IN ('high', 'urgent') AND t.status IN ('open', 'pending'))
                        OR TIMESTAMPDIFF(HOUR, t.created_at, NOW()) > 72
                    )
                    AND (CAST(t.team_member AS UNSIGNED) = ? OR t.team_member IS NULL OR t.team_member = '')
                    ORDER BY
                        FIELD(t.priority, 'urgent', 'high', 'medium', 'low'),
                        t.created_at DESC
                    LIMIT ?
                ");
                $stmt->bind_param("ii", $userId, $remaining);
                $stmt->execute();
                $result = $stmt->get_result();

                while ($row = $result->fetch_assoc()) {
                    $notifications[] = $row;
                }
            }

            // Sort all notifications by created_at
            usort($notifications, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });

            return array_slice($notifications, 0, $limit);
        } catch (Exception $e) {
            error_log("Recent notifications error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get notification badge color based on type
     */
    public static function getBadgeColor($type) {
        switch ($type) {
            case 'urgent': return '#dc3545'; // Red
            case 'high': return '#fd7e14'; // Orange  
            case 'overdue': return '#6f42c1'; // Purple
            default: return '#007bff'; // Blue
        }
    }
    
    /**
     * Get time ago string
     */
    public static function timeAgo($datetime) {
        $time = time() - strtotime($datetime);
        
        if ($time < 60) return 'Just now';
        if ($time < 3600) return floor($time/60) . 'm ago';
        if ($time < 86400) return floor($time/3600) . 'h ago';
        if ($time < 2592000) return floor($time/86400) . 'd ago';
        
        return date('M j', strtotime($datetime));
    }
}
?>