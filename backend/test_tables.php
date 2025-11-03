<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'db_connect.php';

try {
    echo "<h2>Database Tables Check</h2>";
    
    // Test database connection
    echo "Checking database connection...<br>";
    if ($pdo instanceof PDO) {
        echo "✅ Database connection successful!<br><br>";
    }
    
    // Check users table
    echo "<h3>Checking Users Table:</h3>";
    $result = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($result->rowCount() > 0) {
        echo "✅ Users table exists!<br>";
        // Show table structure
        $structure = $pdo->query("DESCRIBE users");
        echo "<pre>";
        while ($row = $structure->fetch(PDO::FETCH_ASSOC)) {
            print_r($row);
        }
        echo "</pre>";
    } else {
        echo "❌ Users table does not exist!<br>";
    }
    
    // Check blogposts table
    echo "<h3>Checking Blogposts Table:</h3>";
    $result = $pdo->query("SHOW TABLES LIKE 'blogposts'");
    if ($result->rowCount() > 0) {
        echo "✅ Blogposts table exists!<br>";
        // Show table structure
        $structure = $pdo->query("DESCRIBE blogposts");
        echo "<pre>";
        while ($row = $structure->fetch(PDO::FETCH_ASSOC)) {
            print_r($row);
        }
        echo "</pre>";
    } else {
        echo "❌ Blogposts table does not exist!<br>";
    }
    
    // Check foreign key
    echo "<h3>Checking Foreign Key:</h3>";
    $fk = $pdo->query("SELECT * FROM information_schema.KEY_COLUMN_USAGE 
                       WHERE REFERENCED_TABLE_NAME = 'users' 
                       AND TABLE_NAME = 'blogposts'");
    if ($fk->rowCount() > 0) {
        echo "✅ Foreign key exists between users and blogposts tables!<br>";
    } else {
        echo "❌ Foreign key missing!<br>";
    }
    
} catch (Exception $e) {
    echo "<br>❌ Error: " . $e->getMessage() . "<br>";
    echo "Error Code: " . $e->getCode() . "<br>";
}

echo "<br><p>To create or recreate the tables:</p>";
echo "1. Open phpMyAdmin<br>";
echo "2. Select the 'blog' database<br>";
echo "3. Go to the 'SQL' tab<br>";
echo "4. Copy and paste the contents of database_setup.sql<br>";
echo "5. Click 'Go' to execute the SQL commands<br>";
?>