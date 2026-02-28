<?php

require_once __DIR__ . '/../config/Database.php';

$database = new Database();
$conn = $database->connect();

if (!$conn) {
    die("Database connection failed!");
}

try {
    // Update candidates
    $updates = [
        1 => 'Hendrik',
        2 => 'Cecep',
        3 => 'Tintin'
    ];

    foreach ($updates as $id => $name) {
        $query = "UPDATE candidates SET name = :name WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        echo "✓ Candidate $id updated to: $name\n";
    }

    echo "\n📋 Daftar candidates terbaru:\n";
    $query = "SELECT id, name, party FROM candidates ORDER BY id";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($candidates as $c) {
        echo "  #{$c['id']} - {$c['name']} ({$c['party']})\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
