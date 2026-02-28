<?php

class Database {
    private $host = 'localhost';
    private $db_name = 'pilkasis';
    private $db_user = 'root';
    private $db_pass = '';
    private $conn;

    public function connect() {
        try {
            $this->conn = new PDO(
                'mysql:host=' . $this->host . ';dbname=' . $this->db_name,
                $this->db_user,
                $this->db_pass
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $this->conn;
        } catch (PDOException $e) {
            echo 'Database Error: ' . $e->getMessage();
            return null;
        }
    }
}
