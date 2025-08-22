<?php

class Requester
{
    public ?int $id = null;
    public string $name = '';
    public string $email = '';
    public ?string $phone = null;
    public ?string $building = null;
    public ?string $department = null;
    public ?string $room = null;
    public ?string $created_at = null;
    public ?string $updated_at = null;

    private mysqli $db;

    public function __construct($data = null)
    {
        $this->db = Database::getInstance();

        if ($data) {
            $this->name = $data['name'] ?? '';
            $this->email = $data['email'] ?? '';
            $this->phone = $data['phone'] ?? null;
            $this->building = $data['building'] ?? null;
            $this->department = $data['department'] ?? null;
            $this->room = $data['room'] ?? null;
        }
    }

    public function save(): Requester
    {
        $sql = "INSERT INTO requester (name, email, phone, building, department, room)
                VALUES ('$this->name', '$this->email', '$this->phone', '$this->building', '$this->department', '$this->room')";

        if ($this->db->query($sql) === false) {
            throw new Exception($this->db->error);
        }

        $id = $this->db->insert_id;
        return self::find($id);
    }

    public static function find($id): ?Requester
    {
        $self = new static;
        $sql = "SELECT * FROM requester WHERE id = '$id'";
        $res = $self->db->query($sql);
        
        if (!$res || $res->num_rows < 1) {
            return null; // Instead of throwing an exception
        }

        $self->populateObject($res->fetch_object());
        return $self;
    }


    public static function findAll(): array
    {
        $sql = "SELECT * FROM requester ORDER BY id DESC";
        $requesters = [];
        $self = new static;
        $res = $self->db->query($sql);

        while ($row = $res->fetch_object()) {
            $requester = new static;
            $requester->populateObject($row);
            $requesters[] = $requester;
        }

        return $requesters;
    }

    public static function findByColumn($data): array
    {
        $field = key($data);
        $value = $data[$field];

        $sql = "SELECT * FROM requester WHERE $field LIKE '%$value%' ORDER BY id DESC";
        $requesters = [];
        $self = new static;
        $res = $self->db->query($sql);

        while ($row = $res->fetch_object()) {
            $requester = new static;
            $requester->populateObject($row);
            $requesters[] = $requester;
        }

        return $requesters;
    }

    public static function delete($id): bool
    {
        $sql = "DELETE FROM requester WHERE id = '$id'";
        $self = new static;
        return $self->db->query($sql);
    }

    public function populateObject($object): void
    {
        foreach ($object as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}
