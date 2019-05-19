<?php
class Requester{
    
    public $id = null;
    
    public $name = '';

    public $email = '';

    public $phone = '';

    private $db = null;


    public function __construct($data = null) {
        $this->name = $data['name'];
        $this->email = $data['email'];
        $this->phone = $data['phone'];
        
        $this->db = Database::getInstance();

        return $this;
    }

    public function save(){
        $sql = "INSERT INTO requester (name, email, phone)
                VALUES ('$this->name', '$this->email', '$this->phone');
        ";
        if($this->db->query($sql) === false) {
            throw new Exception($this->db->error);
        }
        $id = $this->db->insert_id;
        return self::find($id);

    }

    public static function find($id){
        $sql ="SELECT * FROM requester WHERE id = '$id'";
        $self = new static;
        $res = $self->db->query($sql);
        if($res->num_rows < 1) return false;
        $self->populateObject($res->fetch_object());
        return $self;
    }

    public static function findAll(){
        $sql = "SELECT * FROM requester ORDER BY id DESC";
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