<?php
require_once __DIR__ . '/database.php';

class User {

    public ?int $id = null;
    public string $name = '';
    public string $email = '';
    public ?string $room = null;
    public string $password = '';
    public string $role = '';
    public ?string $avatar = null;
    public ?string $last_password = null; 
    // public ?string $phone = null;
    public ?string $created_at = null;
    public ?string $updated_at = null;

    private mysqli $db;

    public function __construct($data = null) {
        $this->db = Database::getInstance(); // always initialize DB

        if ($data) {
            $this->name = $data['name'] ?? '';
            $this->email = $data['email'] ?? '';
            $this->room = $data['room'] ?? '';
            $this->password = $data['password'] ?? '';
            $this->role = $data['role'] ?? '';
            $this->last_password = $data['password'] ?? '';
        }
    }

    public function save(): User {
        $stmt = $this->db->prepare(
            "INSERT INTO users (name, email, room, password, role, last_password) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("ssssss", 
            $this->name, 
            $this->email, 
            $this->room, 
            $this->password, 
            $this->role, 
            $this->last_password
        );

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        $id = $this->db->insert_id;
        return self::find($id);
    }

    public static function find(int $id): ?User {
        $self = new static();
        $stmt = $self->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($row = $res->fetch_object()) {
            $self->populateObject($row);
            return $self;
        }
        return null;
    }

    public static function findAll(): array {
        $self = new static();
        $res = $self->db->query("SELECT * FROM users ORDER BY id DESC");
        $users = [];

        while ($row = $res->fetch_object()) {
            $user = new static();
            $user->populateObject($row);
            $users[] = $user;
        }
        return $users;
    }

    public function populateObject($object): void {
        foreach ($object as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}
