<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'db_connect.php';

try {
    // Test basic connection
    $test = $pdo->query("SELECT 1");
    echo "Basic connection successful!<br>";
    
    // Test users table
    $test = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($test->rowCount() > 0) {
        echo "Users table exists!<br>";
        
        // Test user count
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Number of users in database: " . $count['count'] . "<br>";
    } else {
        echo "Users table does not exist!<br>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
    echo "Error Code: " . $e->getCode() . "<br>";
}
?>