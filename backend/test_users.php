<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'db_connect.php';

try {
    echo "<h2>Database Connection Test</h2>";
    
    // Test database connection
    echo "Checking database connection...<br>";
    if ($pdo instanceof PDO) {
        echo "✅ Database connection successful!<br><br>";
    }
    
    // Check users table
    echo "Checking users table...<br>";
    $result = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($result->rowCount() > 0) {
        echo "✅ Users table exists!<br>";
        
        // Show table structure
        echo "<br><strong>Users Table Structure:</strong><br>";
        $structure = $pdo->query("DESCRIBE users");
        echo "<pre>";
        while ($row = $structure->fetch(PDO::FETCH_ASSOC)) {
            print_r($row);
        }
        echo "</pre>";
        
        // Show number of users
        $count = $pdo->query("SELECT COUNT(*) as total FROM users")->fetch();
        echo "<br>Total users in database: " . $count['total'] . "<br>";
        
        // Show user list (without passwords)
        $users = $pdo->query("SELECT id, username, email, created_at FROM users");
        if ($users->rowCount() > 0) {
            echo "<br><strong>Registered Users:</strong><br>";
            echo "<pre>";
            while ($user = $users->fetch(PDO::FETCH_ASSOC)) {
                print_r($user);
            }
            echo "</pre>";
        } else {
            echo "<br>❌ No users registered yet.<br>";
        }
    } else {
        echo "❌ Users table does not exist! Creating it now...<br>";
        
        // Create users table
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        $pdo->exec($sql);
        echo "✅ Users table created successfully!<br>";
    }
    
} catch (Exception $e) {
    echo "<br>❌ Error: " . $e->getMessage() . "<br>";
    echo "Error Code: " . $e->getCode() . "<br>";
}
?>