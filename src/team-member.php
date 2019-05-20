<?php
class TeamMember{
    
    public $id = null;
    
    public $user = '';

    public $team = '';


    public function __construct($data = null) {
        $this->user = $data['name'];
        $this->team = $data['team-id'];
        
        $this->db = Database::getInstance();

        return $this;
    }

    public function save(){
        $sql = "INSERT INTO team_member (user, team)
                VALUES ('$this->name', '$this->teamId');
        ";
        if($this->db->query($sql) === false) {
            throw new Exception($this->db->error);
        }
        $id = $this->db->insert_id;
        return self::find($id);

    }

    public static function find($id){
        $sql ="SELECT * FROM team_member WHERE id = '$id'";
        $self = new static;
        $res = $self->db->query($sql);
        if($res->num_rows < 1) return false;
        $self->populateObject($res->fetch_object());
        return $self;
    }

    public static function findByTeam($id){
        $sql = "SELECT * FROM team_member WHERE id = '$id' ORDER BY id DESC";
        $tickets = [];
        $self = new static;
        $res = $self->db->query($sql);
        
        if($res->num_rows < 1) return new static;

        while($row = $res->fetch_object()){
            $ticket = new static;
            $ticket->populateObject($row);
            $tickets[] = $ticket;
        }

        return $tickets;
    }

    public static function findAll(){
        $sql = "SELECT * FROM team_member ORDER BY id DESC";
        $tickets = [];
        $self = new static;
        $res = $self->db->query($sql);
        
        if($res->num_rows < 1) return new static;

        while($row = $res->fetch_object()){
            $ticket = new static;
            $ticket->populateObject($row);
            $tickets[] = $ticket;
        }

        return $tickets;
    } 

    public static function getName($id){
        $sql = "SELECT name FROM users WHERE id = '$id'";
        $self = new static;
        $res = $self->db->query($sql);
        return $res->fetch_object()->name;
    }

    public function populateObject($object){

        foreach($object as $key => $property){
            $this->$key = $property;
        }
    }



    
}