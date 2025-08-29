<?php
class TeamMember{
    
    public $id = null;
    
    public $user = '';

    public $team = '';


    public function __construct($data = null)
{
    $this->user = $data['user'] ?? null;
    $this->team = $data['team_id'] ?? null;
    $this->db = Database::getInstance();
}


     //this function returns Teammember obj
     public function save(): TeamMember
     {
        $stmt = $this->db->prepare("INSERT INTO team_member (`user`, `team_id`) VALUES (?, ?)");

         if (!$stmt) {
             throw new Exception("Prepare failed: " . $this->db->error);
         }
     
         $stmt->bind_param("ii", $this->user, $this->team);
         if (!$stmt->execute()) {
             throw new Exception("Execute failed: " . $stmt->error);
         }
     
         $id = $this->db->insert_id;
         $stmt->close();
     
         return self::find($id);
     }
     

    public static function find($id) : TeamMember
    {
        $sql ="SELECT * FROM team_member WHERE id = '$id'";
        $self = new static; //ceate an obj, u dont need to create the obj 
        $res = $self->db->query($sql);
        if($res->num_rows < 1) return false;
        $self->populateObject($res->fetch_object());
        return $self;
    }

    public static function findByTeam($id) : array
    {
        $sql = "SELECT * FROM team_member WHERE team_id = '$id' ORDER BY id DESC";
        $members = [];
        $self = new static;
        $res = $self->db->query($sql);
        
        if($res->num_rows < 1) return [];

        while($row = $res->fetch_object()){
            $member = new static;
            $member->populateObject($row);
            $members[] = $member;
        }

        return $members;
    }

    public static function findAll() : array
    {
        $sql = "SELECT * FROM team_member ORDER BY id DESC";
        $members = [];
        $self = new static;
        $res = $self->db->query($sql);
        
        if($res->num_rows < 1) return new static;

        while($row = $res->fetch_object()){
            $member = new static;
            $member->populateObject($row);
            $members[] = $member;
        }

        return $members;
    } 

    public static function getName($id) : string 
    {
        $sql = "SELECT * FROM users WHERE id = '$id'";
        //print_r($sql);die;
        $self = new static;
        $res = $self->db->query($sql);
        return $res->fetch_object()->name;
    }

    public function populateObject($object) : void 
    {

        foreach($object as $key => $property){
            $this->$key = $property;
        }
    }



    
}