<?php
/**
 * Database connection script. 
 * CRITICAL: Must be included at the top of every PHP file that interacts with sessions or the database.
 */

// 1. Start the session first thing. All APIs rely on this for user authentication.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. Define Database Credentials (ADJUST THESE TO YOUR LOCAL SETUP)
$host = 'localhost';
$db   = 'blog'; // Changed to match your existing database name
$user = 'root'; 
$pass = '';      // Using default XAMPP password (empty)
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    // CRITICAL FIX: Add a timeout of 5 seconds to prevent connection instability on XAMPP
    PDO::ATTR_TIMEOUT              => 5, 
    PDO::ATTR_ERRMODE              => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE   => PDO::FETCH_ASSOC,       // Fetch results as associative arrays
    PDO::ATTR_EMULATE_PREPARES     => false,                  // Use native prepared statements
];

try {
    // 3. Establish the connection
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Test the connection
    $pdo->query("SELECT 1");
    
} catch (\PDOException $e) {
    // 4. Handle connection failure gracefully
    
    // Log the detailed error internally
    error_log("Database Connection Error Details: " . $e->getMessage());
    error_log("DSN: " . $dsn);
    error_log("User: " . $user);
    
    // Send a detailed JSON error response
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed. Error: ' . $e->getMessage(),
        'details' => [
            'host' => $host,
            'database' => $db,
            'user' => $user
        ]
    ]);
    exit(); // Stop all execution immediately
}
// If execution reaches here, $pdo is a valid connection object.
?>
