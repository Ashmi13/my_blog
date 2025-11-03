<?php
require 'backend/db_connect.php';

try {
    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
    echo "Users table created successfully! <br>";
    
    // Show table structure
    $result = $pdo->query("DESCRIBE users");
    echo "<h3>Table Structure:</h3>";
    echo "<pre>";
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
    echo "</pre>";
    
    echo "<p>Now you can register a new user account.</p>";
    echo "<p><a href='index.html#register'>Click here to register</a></p>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
    echo "Error Code: " . $e->getCode() . "<br>";
}
?>