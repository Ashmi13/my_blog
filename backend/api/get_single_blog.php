<?php
/**
 * API to fetch a single blog post by ID, including the author's name.
 * Used by:
 * 1. index.html (to display the full post content)
 * 2. dashboard.html (to populate the "Edit Existing Post" form)
 */
header('Content-Type: application/json');
require '../db_connect.php'; 

// Check if $pdo connection object is available (ensuring db_connect succeeded)
if (!isset($pdo)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit();
}

$response = ['success' => false, 'post' => null, 'message' => ''];

try {
    // 1. Validate the input ID from the URL query string
    $blog_id = isset($_GET['id']) ? $_GET['id'] : null;

    if (!$blog_id || !is_numeric($blog_id)) {
        http_response_code(400); // Bad Request
        $response['message'] = 'Invalid or missing post ID.';
        echo json_encode($response);
        exit();
    }

    // 2. Prepare the robust SQL query with JOIN
    // 🎯 CRITICAL FIX ATTEMPT: We are removing 'b.tags' from the SELECT list.
    // If the error disappears, it means your 'blogposts' table does not have a 'tags' column.
    $stmt = $pdo->prepare("
        SELECT 
            b.id, 
            b.title, 
            b.content, 
            b.created_at,
            b.user_id,
            u.username AS author_name 
        FROM blogposts b
        JOIN users u ON b.user_id = u.id
        WHERE b.id = :id
    ");

    $stmt->execute([':id' => $blog_id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($post) {
        // If 'tags' was missing, the post data will still be returned successfully,
        // but without the tags field. We can add an empty tags field for compatibility 
        // with the frontend form, which might expect it.
        if (!isset($post['tags'])) {
            $post['tags'] = '';
        }
        
        // Post found
        $response['success'] = true;
        $response['post'] = $post;
    } else {
        // Post not found
        http_response_code(404); // Not Found
        $response['message'] = 'Post not found.';
    }

} catch (\PDOException $e) {
    // This logs the precise SQL error, which is often the key to the fix
    http_response_code(500); // Internal Server Error
    error_log("Get Single Blog Error: " . $e->getMessage()); 
    // Send a generic message to the frontend
    $response['message'] = 'A server error prevented the post from loading.';
}

echo json_encode($response);
exit();
?>
