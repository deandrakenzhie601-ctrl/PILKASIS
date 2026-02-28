<?php

class Vote {
    private $conn;
    private $table = 'votes';

    public $id;
    public $user_id;
    public $candidate_id;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function hasUserVoted($user_id) {
        $query = "SELECT id FROM " . $this->table . " WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function getUserVote($user_id) {
        $query = "SELECT * FROM " . $this->table . " WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (user_id, candidate_id) 
                  VALUES (:user_id, :candidate_id)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':candidate_id', $this->candidate_id);

        return $stmt->execute();
    }

    public function getResults() {
        $query = "SELECT c.id, c.name, c.party, COUNT(v.id) as total_votes 
                  FROM candidates c 
                  LEFT JOIN " . $this->table . " v ON c.id = v.candidate_id 
                  GROUP BY c.id, c.name, c.party 
                  ORDER BY total_votes DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
