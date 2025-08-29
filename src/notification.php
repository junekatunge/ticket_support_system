<?php
class Notification {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
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
            // Count urgent/high priority tickets
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
            
            return $row['count'] ?? 0;
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
            // Get recent high priority and overdue tickets
            $stmt = $db->prepare("
                SELECT 
                    t.*,
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
                    END as notification_type
                FROM ticket t
                LEFT JOIN requesters r ON t.requester_id = r.id
                WHERE (
                    (t.priority IN ('high', 'urgent') AND t.status IN ('open', 'pending'))
                    OR TIMESTAMPDIFF(HOUR, t.created_at, NOW()) > 72
                )
                AND (t.team_member = ? OR t.team_member IS NULL)
                ORDER BY 
                    FIELD(t.priority, 'urgent', 'high', 'medium', 'low'),
                    t.created_at DESC
                LIMIT ?
            ");
            $stmt->bind_param("ii", $userId, $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $notifications = [];
            while ($row = $result->fetch_assoc()) {
                $notifications[] = $row;
            }
            
            return $notifications;
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