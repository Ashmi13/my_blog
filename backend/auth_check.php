<?php
/**
 * Authentication and Authorization Check
 * CRITICAL: Must be included at the top of ALL authenticated API files.
 */

// 1. Include the DB connection file first. This also starts the session.
// NOTE: db_connect.php must be in the same directory (backend/)
require_once 'db_connect.php'; 

// --- 1. Authentication Check ---

// Check if the user ID is set in the session.
// This relies on login.php setting $_SESSION['user_id'].
if (!isset($_SESSION['user_id'])) {
    // Unauthorized access attempt (for AJAX calls, return 403 JSON)
    if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
        http_response_code(403); 
        echo json_encode(['success' => false, 'message' => 'Authentication failed. Please log in.']);
        exit();
    }
    
    // For direct browser access (dashboard.html)
    header('Location: ../index.html#login');
    exit(); // Stop execution of the current script
}

// Global variables for convenience in other scripts
$current_user_id = $_SESSION['user_id'];
// NOTE: Using 'user_name' to match login.php.
$current_username = $_SESSION['user_name'];


// --- 2. Authorization Logic (Used for Blog Management) ---

/**
 * Ensures the logged-in user owns the specified blog post ID.
 * This function uses the global $pdo object established in db_connect.php.
 */
function check_blog_ownership($blog_id) {
    global $pdo, $current_user_id; // Use the global connection and user ID

    // Look up the blog post's user_id
    // CRITICAL: Ensure 'blogPosts' table name is correct.
    $stmt = $pdo->prepare("SELECT user_id FROM blogPosts WHERE id = ?");
    $stmt->execute([$blog_id]);
    $post = $stmt->fetch();

    // Check if the post exists OR if the post's user_id matches the session user_id
    if (!$post || $post['user_id'] != $current_user_id) {
        // Unauthorized access attempt: Respond with 403 Forbidden status
        http_response_code(403); 
        echo json_encode(['success' => false, 'message' => 'You are not authorized to perform this action.']);
        exit();
    }
    // If we reach here, ownership is confirmed
    return true; 
}
?>