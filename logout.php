<?php
session_start();

// Clear remember me cookies if they exist
if (isset($_COOKIE['remember_user'])) {
    setcookie('remember_user', '', time() - 3600, '/');
}
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Store logout message
$user_name = $_SESSION['first_name'] ?? 'User';

// Clear all session data
session_unset();
session_destroy();

// Start new session for the logout message
session_start();
$_SESSION['logout_message'] = "Goodbye, $user_name! You have been logged out successfully.";

// Redirect to login page or home page
$redirect_page = 'login.php';

// If there's a specific redirect request, use it
if (isset($_GET['redirect'])) {
    $redirect_page = $_GET['redirect'];
}

header("Location: $redirect_page");
exit();
?>