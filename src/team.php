<?php
require_once __DIR__ . '/database.php';

class Team
{
    public ?int $id = null;
    public string $name = '';
    public ?string $description = null;
    public ?string $created_at = null;
    public ?string $updated_at = null;

    private mysqli $db;

    public function __construct($data = null)
{
    $this->db = Database::getInstance();

    if (is_array($data)) {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}


    public static function findAll(): array
{
    $db = Database::getInstance();
    $result = $db->query("SELECT * FROM team ORDER BY created_at DESC");

    $teams = [];

    while ($row = $result->fetch_assoc()) {
        $teams[] = new Team($row);  // use constructor
    }

    return $teams;
}


    public static function getMemberCount($teamId): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM team_member WHERE team_id = ?");
        if (!$stmt) return 0;

        $stmt->bind_param("i", $teamId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return (int)$row['total'];
        }
        return 0;
    }

    public function save(): Team
    {
        $stmt = $this->db->prepare("INSERT INTO team (name, created_at) VALUES (?, NOW())");
        $stmt->bind_param("s", $this->name);
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        $id = $this->db->insert_id;
        return self::find($id);
    }

    public static function find($id): ?Team
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM team WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        return $data ? new Team($data) : null;
    }
}
