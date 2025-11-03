<?php
header('Content-Type: application/json');
require '../db_connect.php'; 

if (!isset($pdo) || $pdo === null) {
    http_response_code(503);
    echo json_encode(['success' => false, 'message' => 'Database connection error.']);
    exit();
}

$response = ['success' => false, 'blogs' => []];

try {
    $stmt = $pdo->query("
        SELECT 
            b.id, 
            b.title, 
            LEFT(b.content, 200) AS summary, 
            b.created_at,
            u.username as author_name
        FROM blogposts b
        LEFT JOIN users u ON b.user_id = u.id
        ORDER BY b.created_at DESC
    ");
    
    $blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format the dates and ensure author name is set
    foreach ($blogs as &$blog) {
        $blog['author_name'] = $blog['author_name'] ?? 'Unknown Author';
        $blog['formatted_date'] = date('F j, Y', strtotime($blog['created_at']));
    }
    unset($blog);

    $response['success'] = true;
    $response['blogs'] = $blogs;

} catch (\PDOException $e) {
    http_response_code(500);
    error_log("Get All Blogs Error: " . $e->getMessage()); 
    $response['message'] = 'Could not fetch posts due to a database error.';
}

echo json_encode($response);
exit();
?>