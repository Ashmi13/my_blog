<?php
/**
 * Handles user login, authentication, and session creation.
 */
require 'db_connect.php'; 

header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $response['message'] = 'Method Not Allowed.';
    echo json_encode($response);
    exit();
}

// Get the data sent from the login form
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? ''; 

if (empty($email) || empty($password)) {
    http_response_code(400);
    $response['message'] = 'Email and password are required.';
    echo json_encode($response);
    exit();
}

try {
    // Debug information
    error_log("Login attempt for email: " . $email);
    
    // Check if we have a valid PDO connection
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new \Exception('Database connection not established');
    }
    
    // First, check if the users table exists
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($tableCheck->rowCount() === 0) {
        throw new \Exception('Users table does not exist');
    }
    
    // 1. Find the user by email
    $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE email = :email");
    if (!$stmt) {
        throw new \Exception('Failed to prepare statement');
    }
    
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // 2. Verify the password hash
        // FIX 2: Check against $user['password'] instead of $user['password_hash']
        if (password_verify($password, $user['password'])) {
            
            // 3. SUCCESS: Set Session Variables
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['user_id'] = $user['id'];
            // FIX 3: Use the correct column name 'username' for the session variable
            $_SESSION['user_name'] = $user['username']; 
            
            $response['success'] = true;
            $response['message'] = 'Login successful!';
            $response['redirect'] = 'dashboard.html';
            
        } else {
            // Password incorrect
            $response['message'] = 'Login Failed: Invalid email or password.';
        }
    } else {
        // User not found
        $response['message'] = 'Login Failed: Invalid email or password.';
    }

} catch (\PDOException $e) {
    http_response_code(500);
    error_log("Login PDO Error: " . $e->getMessage());
    error_log("Error code: " . $e->getCode());
    $response['message'] = 'Database Error: ' . $e->getMessage();
} catch (\Exception $e) {
    http_response_code(500);
    error_log("Login General Error: " . $e->getMessage());
    $response['message'] = 'Server Error: ' . $e->getMessage();
}

echo json_encode($response);
?>