<?php
// Set the content type to JSON
header('Content-Type: application/json');

session_start();

// 1. SECURITY CHECK: Must be logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit();
}

$current_user_id = $_SESSION['user_id'];
require '../db_connect.php'; // Path to db_connect.php

if (!isset($pdo) || $pdo === null) {
    http_response_code(503);
    echo json_encode(['success' => false, 'message' => 'Server configuration error (Database connection missing).']);
    exit();
}

// Get the JSON data sent from JavaScript
$data = json_decode(file_get_contents('php://input'), true);

$post_id = $data['id'] ?? null;
$title = $data['title'] ?? null;
$content = $data['content'] ?? null;
// CRITICAL FIX: Ensure tags are retrieved for updating
$tags = $data['tags'] ?? ''; // Set to empty string if not provided

if (empty($post_id) || empty($title) || empty($content)) {
    http_response_code(400); 
    echo json_encode(['success' => false, 'message' => 'Missing required fields (ID, Title, or Content).']);
    exit();
}

try {
    // CRITICAL FIX: Update the SQL to include the 'tags' field
    $stmt = $pdo->prepare("
        UPDATE blogposts 
        SET title = ?, content = ?, tags = ?, updated_at = NOW() 
        WHERE id = ? AND user_id = ?
    ");
    
    // Execute the statement, now passing the tags variable
    $success = $stmt->execute([$title, $content, $tags, $post_id, $current_user_id]);

    if ($success && $stmt->rowCount() > 0) {
        // SUCCESS PATH: Explicitly close the connection
        $pdo = null; 
        echo json_encode(['success' => true, 'message' => 'Post updated successfully!']);
        
    } else if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Update failed. Post not found or access denied.']);
    } else {
        // Fallback for execution failure
        echo json_encode(['success' => false, 'message' => 'Update execution failed.']);
    }

} catch (\PDOException $e) {
    http_response_code(500);
    error_log("Update Blog Error: " . $e->getMessage()); 
    echo json_encode(['success' => false, 'message' => 'Update Failed. A database error occurred during the update.']);
}

// Ensure connection is closed on error paths too
$pdo = null;
?>
