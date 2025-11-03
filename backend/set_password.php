<?php
// One-off script to set a new password for a user by email.
// Usage (in browser): http://localhost/MyBlog/set_password.php?email=you@example.com&password=NewPass123
// IMPORTANT: Delete this file after use for security.

error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'db_connect.php';

$email = $_GET['email'] ?? '';
$newpass = $_GET['password'] ?? '';

if (!$email || !$newpass) {
    echo "Usage: set_password.php?email=you@example.com&password=NewPass123";
    exit;
}

try {
    $hash = password_hash($newpass, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE email = :email");
    $stmt->execute([':password' => $hash, ':email' => $email]);
    if ($stmt->rowCount() > 0) {
        echo "Password updated successfully for: " . htmlspecialchars($email);
    } else {
        echo "No user updated. Check the email exists: " . htmlspecialchars($email);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>