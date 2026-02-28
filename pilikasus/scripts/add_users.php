<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../src/User.php';

$database = new Database();
$conn = $database->connect();

if (!$conn) {
    die("Database connection failed!");
}

// Data user baru
$new_users = [
    ['username' => 'andi', 'password' => 'ceper123', 'role' => 'voter'],
    ['username' => 'dewi', 'password' => 'ceper123', 'role' => 'voter']
];

try {
    $user = new User($conn);
    
    foreach ($new_users as $new_user) {
        // Cek apakah user sudah ada
        $query = "SELECT id FROM users WHERE username = :username";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':username', $new_user['username']);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            echo "⚠ User '{$new_user['username']}' sudah ada!\n";
        } else {
            $user->username = $new_user['username'];
            $user->password = $new_user['password'];
            $user->role = $new_user['role'];
            
            if ($user->create()) {
                echo "✓ User '{$new_user['username']}' berhasil ditambahkan!\n";
            } else {
                echo "✗ Gagal menambahkan user '{$new_user['username']}'\n";
            }
        }
    }
    
    echo "\n📝 Daftar semua users:\n";
    $all_users = $user->getAll();
    foreach ($all_users as $u) {
        echo "  - {$u['username']} ({$u['role']})\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
