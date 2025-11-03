<?php
/**
 * API to fetch all blog posts belonging to the logged-in user.
 * Used by: dashboard.html (to display the list of posts for editing/deleting).
 */
require '../db_connect.php'; 

header('Content-Type: application/json');
$response = ['success' => false, 'message' => '', 'blogs' => []];

// 1. Authentication Check: Ensure a user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403); // Forbidden
    $response['message'] = 'Authentication required. Please log in.';
    // Redirect to login page client-side (handled by script.js, but useful for API test)
    echo json_encode($response);
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['user_name'] ?? 'Author'; // Get username from session

try {
    // 2. Fetch the user's blog posts
    // We select only posts where the user_id matches the logged-in user's ID
    $stmt = $pdo->prepare("
        SELECT 
            id, 
            title, 
            content, 
            created_at, 
            user_id 
        FROM blogposts 
        WHERE user_id = :user_id
        ORDER BY created_at DESC
    ");

    $stmt->execute([':user_id' => $user_id]);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Success response
    $response['success'] = true;
    $response['blogs'] = $posts;
    $response['username'] = $username; // Send the username back for the welcome header

} catch (\PDOException $e) {
    http_response_code(500); // Internal Server Error
    error_log("Get User Blogs Error (User ID: $user_id): " . $e->getMessage());
    $response['message'] = 'A database error occurred while fetching your posts.';
}

echo json_encode($response);
?>