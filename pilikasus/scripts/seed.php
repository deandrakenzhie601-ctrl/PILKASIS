<?php

require_once __DIR__ . '/../config/Database.php';

$database = new Database();
$conn = $database->connect();

if (!$conn) {
    echo "Database connection failed!";
    exit;
}

try {
    // Create tables
    $conn->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('admin', 'voter') DEFAULT 'voter',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $conn->exec("
        CREATE TABLE IF NOT EXISTS candidates (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            party VARCHAR(100),
            photo VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $conn->exec("
        CREATE TABLE IF NOT EXISTS votes (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            candidate_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id),
            FOREIGN KEY (candidate_id) REFERENCES candidates(id),
            UNIQUE(user_id)
        )
    ");

    // Seed default accounts
    $admin_password = password_hash('admin123', PASSWORD_BCRYPT);
    $voter_password = password_hash('siswa123', PASSWORD_BCRYPT);

    $conn->exec("DELETE FROM users");
    $conn->exec("DELETE FROM candidates");
    $conn->exec("DELETE FROM votes");

    $conn->exec("
        INSERT INTO users (username, password, role) VALUES 
        ('admin', '$admin_password', 'admin'),
        ('siswa1', '$voter_password', 'voter'),
        ('siswa2', '$voter_password', 'voter'),
        ('siswa3', '$voter_password', 'voter')
    ");

    // Seed candidates
    $conn->exec("
        INSERT INTO candidates (name, party, photo) VALUES 
        ('Candidate 1', 'Party A', 'candidate1.jpg'),
        ('Candidate 2', 'Party B', 'candidate2.jpg'),
        ('Candidate 3', 'Party C', 'candidate3.jpg')
    ");

    echo "✓ Database tables created successfully!\n";
    echo "✓ Default users seeded:\n";
    echo "  - admin / admin123 (Admin)\n";
    echo "  - siswa1 / siswa123 (Voter)\n";
    echo "  - siswa2 / siswa123 (Voter)\n";
    echo "  - siswa3 / siswa123 (Voter)\n";
    echo "✓ Candidates seeded!\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
