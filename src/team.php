<?php
class Team{
    
    public $id = null;
    
    public $name = '';


    public function __construct($data = null) {
        $this->name = $data['name'];
        
        $this->db = Database::getInstance();

        return $this;
    }

    public function save(){
        $sql = "INSERT INTO team (name)
                VALUES ('$this->name');
        ";
        if($this->db->query($sql) === false) {
            throw new Exception($this->db->error);
        }
        $id = $this->db->insert_id;
        return self::find($id);

    }

    public static function find($id){
        $sql ="SELECT * FROM team WHERE id = '$id'";
        $self = new static;
        $res = $self->db->query($sql);
        if($res->num_rows < 1) return false;
        $self->populateObject($res->fetch_object());
        return $self;
    }

    public static function findAll(){
        $sql = "SELECT * FROM team ORDER BY id DESC";
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


    public function populateObject($object){

        foreach($object as $key => $property){
            $this->$key = $property;
        }
    }



    
}