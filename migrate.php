<?php
require_once 'config.php';

try {
    // Add role column to users table if it doesn't exist
    $pdo->exec("ALTER TABLE users ADD COLUMN role ENUM('admin', 'user') DEFAULT 'user' AFTER password");
    echo "Added 'role' column to users table.\n";

    // Update existing admin user to have the 'admin' role
    $pdo->exec("UPDATE users SET role = 'admin' WHERE username = 'admin'");
    echo "Updated 'admin' user role to 'admin'.\n";

    // Insert a test user
    $testUserHash = password_hash('user123', PASSWORD_DEFAULT);
    $pdo->exec("INSERT INTO users (username, password, role) VALUES ('testuser', '$testUserHash', 'user') ON DUPLICATE KEY UPDATE role = 'user'");
    echo "Ensured testuser exists.\n";
} catch (PDOException $e) {
    echo "Error updating database: " . $e->getMessage() . "\n";
    // Ignore error if column already exists
}
?>