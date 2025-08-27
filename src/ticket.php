<?php

class Ticket
{
    public ?int $id = null;
    public ?string $title = null;
    public ?string $body = null;
    public ?int $requester = null;
    public ?int $team = null;
    public ?int $team_member = null;
    public ?string $status = null;
    public ?string $priority = null;
    public ?string $rating = null;
    public ?string $building = null;
    public ?string $department = null;
    public ?string $room = null;
    public ?string $category = null;
    public ?string $additional_info = null;
    public ?string $created_at = null;
    public ?string $updated_at = null;
    public ?string $deleted_at = null;

    private mysqli $db;


    public function __construct($data = null)
    {
        $this->title = isset($data['title']) ? $data['title'] : null;
        $this->body = isset($data['body']) ? $data['body'] : null;
        $this->requester = isset($data['requester']) ? $data['requester'] : null;
        $this->team = isset($data['team']) ? $data['team'] : null;
        $this->team_member = isset($data['team_member']) ? $data['team_member'] : null;
        $this->status = isset($data['status']) ? $data['status'] : 'open';
        $this->priority = isset($data['priority']) ? $data['priority'] : 'low';
        $this->rating = isset($data['rating']) ? $data['rating'] : 0;

        $this->building = isset($data['building']) ? $data['building'] : null;
        $this->department = isset($data['department']) ? $data['department'] : null;
        $this->room = isset($data['room']) ? $data['room'] : null;
        $this->category = isset($data['category']) ? $data['category'] : null;
        $this->additional_info = isset($data['additional_info']) ? $data['additional_info'] : null;

        $this->db = Database::getInstance();

        return $this;
    }

    public function save(): Ticket
    {
        $sql = "INSERT INTO ticket (title, body, requester, team, team_member, status, priority, rating, building, department, room, category, additional_info, created_at)
                VALUES (
                    '$this->title',
                    '$this->body',
                    '$this->requester',
                    '$this->team',
                    '$this->team_member',
                    '$this->status',
                    '$this->priority',
                    '$this->rating',
                    '$this->building',
                    '$this->department',
                    '$this->room',
                    '$this->category',
                    '$this->additional_info',
                    NOW()
                )";

        if ($this->db->query($sql) === false) {
    echo "<pre>SQL Query:\n$sql\n\nError:\n" . $this->db->error . "</pre>";
    exit;
}


        $id = $this->db->insert_id;
        return self::find($id);
    }

    public static function find($id): Ticket
    {
        $sql = "SELECT * FROM ticket WHERE id = '$id'";
        $self = new static;
        $res = $self->db->query($sql);
        if ($res->num_rows < 1) {
            return false;
        }

        $self->populateObject($res->fetch_object());
        return $self;
    }

    public static function findAll(): array
    {
        $sql = "SELECT * FROM ticket ORDER BY created_at DESC, id DESC";
        $tickets = [];
        $self = new static;
        $res = $self->db->query($sql);

        if ($res->num_rows < 1) {
            return new static;
        }

        while ($row = $res->fetch_object()) {
            $ticket = new static;
            $ticket->populateObject($row);
            $tickets[] = $ticket;
        }

        return $tickets;
    }

    public static function findByStatus($status): array
    {
        $sql = "SELECT * FROM ticket WHERE status = '$status' ORDER BY created_at DESC, id DESC";
        $self = new static;
        $tickets = [];
        $res = $self->db->query($sql);

        while ($row = $res->fetch_object()) {
            $ticket = new static;
            $ticket->populateObject($row);
            $tickets[] = $ticket;
        }

        return $tickets;
    }

    public static function changeStatus($id, $status): bool
    {
        $self = new static;
        $sql = "UPDATE ticket SET status = '$status' WHERE id = '$id'";
        return $self->db->query($sql);
    }

    public static function delete($id): bool
    {
        $sql = "DELETE FROM ticket WHERE id = '$id'";
        $self = new static;
        return $self->db->query($sql);
    }

    public static function setRating($id, $rating): bool
    {
        $sql = "UPDATE ticket SET rating = '$rating' WHERE id = '$id'";
        $self = new static;
        return $self->db->query($sql);
    }

    public static function setPriority($id, $priority): bool
    {
        $sql = "UPDATE ticket SET priority = '$priority' WHERE id = '$id'";
        $self = new static;
        return $self->db->query($sql);
    }

    public function displayStatusBadge(): string
    {
        $badgeType = '';
        if ($this->status == 'open') {
            $badgeType = 'danger';
        } else if ($this->status == 'pending') {
            $badgeType = 'warning';
        } else if ($this->status == 'solved') {
            $badgeType = 'success';
        } else if ($this->status == 'closed') {
            $badgeType = 'info';
        }

        return '<div class="badge badge-' . $badgeType . '" role="badge"> ' . ucfirst($this->status) . '</div>';
    }

    public function populateObject($object): void
{
    foreach ($object as $key => $property) {
        switch ($key) {
            case 'id':
            case 'requester':
            case 'team':
            case 'team_member':
                $this->$key = is_numeric($property) ? (int)$property : null;
                break;

            case 'rating':
                $this->$key = is_numeric($property) ? (float)$property : null;
                break;

            default:
                $this->$key = $property;
        }
    }
}


    public function update($id): Ticket
    {
        $sql = "UPDATE ticket SET 
                    team_member = '$this->team_member',
                    title = '$this->title',
                    body = '$this->body',
                    requester = '$this->requester',
                    team = '$this->team',
                    status = '$this->status',
                    priority = '$this->priority',
                    building = '$this->building',
                    department = '$this->department',
                    room = '$this->room',
                    category = '$this->category',
                    additional_info = '$this->additional_info'
                WHERE id = '$id'";

        if ($this->db->query($sql) === false) {
            throw new Exception($this->db->error);
        }

        return self::find($id);
    }

    public function unassigned()
    {
        $sql = "SELECT * FROM ticket WHERE team_member = '' ORDER BY created_at DESC, id DESC";
        $self = new static;
        $tickets = [];
        $res = $self->db->query($sql);

        while ($row = $res->fetch_object()) {
            $tickets[] = $row;
        }

        return $tickets;
    }

    public static function findByMember($member)
    {
        $sql = "SELECT * FROM ticket WHERE team_member = '$member' ORDER BY created_at DESC, id DESC";
        $self = new static;
        $tickets = [];
        $res = $self->db->query($sql);

        while ($row = $res->fetch_object()) {
            $ticket = new static;
            $ticket->populateObject($row);
            $tickets[] = $ticket;
        }

        return $tickets;
    }

    public static function findByTeam($team_id)
    {
        $sql = "SELECT * FROM ticket WHERE team = '$team_id' ORDER BY created_at DESC, id DESC";
        $self = new static;
        $tickets = [];
        $res = $self->db->query($sql);

        while ($row = $res->fetch_object()) {
            $ticket = new static;
            $ticket->populateObject($row);
            $tickets[] = $ticket;
        }

        return $tickets;
    }
}
