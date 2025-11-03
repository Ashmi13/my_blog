<?php
/**
 * Handles user registration, creates a secure password hash, and stores the user in the database.
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

// Get the data sent from the registration form
// FIX 1: Changed 'name' input to 'username' input to match the database column
$username = trim($_POST['name'] ?? ''); 
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm'] ?? '';

// FIX 2: Check for $username instead of $name
if (empty($username) || empty($email) || empty($password) || empty($confirm)) {
    http_response_code(400);
    $response['message'] = 'All fields are required.';
    echo json_encode($response);
    exit();
}

if ($password !== $confirm) {
    http_response_code(400);
    $response['message'] = 'Passwords do not match.';
    echo json_encode($response);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    $response['message'] = 'Invalid email format.';
    echo json_encode($response);
    exit();
}

try {
    // 1. Check if user already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    if ($stmt->fetchColumn() > 0) {
        http_response_code(409); // Conflict
        $response['message'] = 'Registration Failed: An account with this email already exists.';
        echo json_encode($response);
        exit();
    }

    // 2. Hash the password securely
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // 3. Insert the new user
    // FIX 3: Changed column 'name' to 'username' and 'password_hash' to 'password'
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password, created_at) 
        VALUES (:username, :email, :password, NOW())
    ");
    
    $stmt->execute([
        ':username' => $username, // FIX 4: Bind $username variable
        ':email' => $email,
        ':password' => $password_hash // FIX 5: Bind to the 'password' column
    ]);

    $response['success'] = true;
    $response['message'] = 'Registration successful! You can now log in.';

} catch (\PDOException $e) {
    http_response_code(500); // Internal Server Error
    error_log("Registration Error: " . $e->getMessage()); 
    $response['message'] = 'Registration Failed: A server error occurred.';
}

echo json_encode($response);
?>