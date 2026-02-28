<?php

class Candidate {
    private $conn;
    private $table = 'candidates';

    public $id;
    public $name;
    public $party;
    public $photo;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT c.*, COUNT(v.id) as vote_count 
                  FROM " . $this->table . " c 
                  LEFT JOIN votes v ON c.id = v.candidate_id 
                  GROUP BY c.id 
                  ORDER BY c.id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT c.*, COUNT(v.id) as vote_count 
                  FROM " . $this->table . " c 
                  LEFT JOIN votes v ON c.id = v.candidate_id 
                  WHERE c.id = :id 
                  GROUP BY c.id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (name, party, photo) 
                  VALUES (:name, :party, :photo)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':party', $this->party);
        $stmt->bindParam(':photo', $this->photo);

        return $stmt->execute();
    }
}
