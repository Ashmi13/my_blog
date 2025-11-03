<?php
// Returns current session info (for debugging only). Remove after use.
require '../db_connect.php';
header('Content-Type: application/json');
$response = ['success' => false, 'message' => '', 'session' => null];

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    $response['success'] = true;
    $response['message'] = 'Session active';
    $response['session'] = [
        'user_id' => $_SESSION['user_id'],
        'user_name' => $_SESSION['user_name'] ?? null
    ];
} else {
    $response['success'] = false;
    $response['message'] = 'No active session';
}

echo json_encode($response);
?>