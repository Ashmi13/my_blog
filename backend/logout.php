<?php
/**
 * Handles user logout.
 */
session_start();

// Destroy all session data
session_unset();
session_destroy();

// Redirect back to the home page (index.html) inside the 'frontend' folder
header('Location: ../frontend/index.html');

// Exit immediately after sending the header
exit();
