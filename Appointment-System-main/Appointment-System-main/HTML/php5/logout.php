<?php
session_start();
session_unset();  // Clear all session variables
session_destroy(); // Destroy the session
header("Location: user_login.php"); // Redirect to login page
exit();
?>
