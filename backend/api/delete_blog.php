<?php
/**
 * Deletes a blog post after checking if the logged-in user is the owner.
 */

// CRITICAL FIX 1: Corrected relative paths. 
// Assuming this file is in 'backend/api/', includes are in '../' (backend/)
require_once '../db_connect.php'; // Provides $pdo connection
require_once '../auth_check.php'; // Performs auth check and provides $current_user_id

header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit();
}

// CRITICAL FIX 2: Read raw JSON input from the fetch request body
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

// Get the 'id' from the decoded JSON data
$blog_id = $data['id'] ?? null;

if (empty($blog_id) || !is_numeric($blog_id)) {
    http_response_code(400); // Bad Request
    $response['message'] = 'Deletion Failed: Invalid blog ID provided.';
    echo json_encode($response);
    exit();
}

try {
    // 1. Prepare SQL statement: DELETE the post but ONLY if it belongs to the current user
    // CRITICAL FIX 3: Ensure your blog table is named 'blogPosts' (or 'blogposts')
    $stmt = $pdo->prepare("DELETE FROM blogPosts WHERE id = ? AND user_id = ?");
    
    // 2. Execute with the blog ID and the current user ID
    $stmt->execute([$blog_id, $current_user_id]);
    
    // 3. Check if any row was affected
    if ($stmt->rowCount() > 0) {
        $response['success'] = true;
        $response['message'] = 'Blog post deleted successfully.';
    } else {
        // This means the ID was valid but didn't belong to the user, or the ID didn't exist
        http_response_code(403); // Forbidden
        $response['message'] = 'Action forbidden or post not found. Cannot delete.';
    }

} catch (\PDOException $e) {
    http_response_code(500);
    $response['message'] = 'A database error occurred during deletion.';
    error_log("Blog Deletion Error: " . $e->getMessage()); 
}

echo json_encode($response);
exit();