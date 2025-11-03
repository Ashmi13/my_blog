<?php
// Include the database connection script (which must start session_start() and set $pdo)
require '../db_connect.php'; 

header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

// Ensure PHP warnings/notices don't break JSON output in responses
ini_set('display_errors', '0');
error_reporting(E_ALL);

// Ensure $pdo is set and not null
if (!isset($pdo) || $pdo === null) {
    http_response_code(503);
    $response['message'] = 'Service Unavailable: Database connection failed.';
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $response['message'] = 'Method Not Allowed.';
    echo json_encode($response);
    $pdo = null; 
    exit();
}

// 1. Authentication Check: Ensure a user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403); // Forbidden
    $response['message'] = 'Authentication required. Please log in.';
    echo json_encode($response);
    $pdo = null; 
    exit();
}
$user_id = $_SESSION['user_id'];

// --- TEMPORARY SESSION DEBUGGING LOG ---
error_log("Session Check Success: User ID retrieved is " . $user_id);
// ---------------------------------------

// CRITICAL FIX: Close session write lock immediately after reading user_id 
session_write_close(); 


// 2. Read JSON input from the request body
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if ($data === null) {
    http_response_code(400);
    $response['message'] = 'Invalid JSON input.';
    echo json_encode($response);
    $pdo = null; 
    exit();
}

// 3. Validation from JSON data
$title = trim($data['title'] ?? '');
$content = trim($data['content'] ?? '');
$tags = trim($data['tags'] ?? ''); 

if (empty($title) || empty($content)) {
    http_response_code(400);
    $response['message'] = 'Title and content cannot be empty.';
    echo json_encode($response);
    $pdo = null; 
    exit();
}

try {
    // 4. Insert new blog post
    // Define the SQL query string
    $sql_query = "
        INSERT INTO blogposts (user_id, title, content, tags, created_at) 
        VALUES (:user_id, :title, :content, :tags, NOW())
    ";

    $stmt = $pdo->prepare($sql_query);
    
    // Define the parameters array
    $params = [
        ':user_id' => $user_id,
        ':title' => $title,
        ':content' => $content,
        ':tags' => $tags
    ];

    $stmt->execute($params);

    // Get the ID of the newly created post and return it.
    $new_post_id = $pdo->lastInsertId();

    $response['success'] = true;
    $response['message'] = 'Blog post published successfully!';
    $response['id'] = $new_post_id; 

} catch (\PDOException $e) {
    http_response_code(500);
    
    // --- DEBUGGING CRITICAL LOGGING ---
    $log_message = "SQL Error during blog creation:\n";
    $log_message .= "User ID: $user_id\n";
    $log_message .= "SQL Query: " . $sql_query . "\n"; // Log the full query text
    $log_message .= "Parameters: " . print_r($params, true) . "\n"; // Log the values attempted to be bound
    $log_message .= "Error Message: " . $e->getMessage() . "\n";
    $log_message .= "SQL State: " . $e->getCode() . "\n";
    
    error_log($log_message);
    // --- END CRITICAL LOGGING ---

    $response['message'] = 'A server error occurred while publishing the post.';
}

// Clear any accidental output (notices, HTML) that could break JSON parsing on the client
if (ob_get_level()) {
    while (ob_get_level()) {
        ob_end_clean();
    }
}

// Ensure we send only the JSON
header('Content-Type: application/json');
echo json_encode($response);

// Explicitly close the connection at the very end of the script execution.
$pdo = null; 
?>

