<?php
session_start();

// Destroy all session data
session_unset();
session_destroy();

// Redirect back to the home page (index.html)
header('Location: ../index.html');
exit();
?>