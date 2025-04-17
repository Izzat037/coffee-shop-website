<?php
require_once 'includes/config.php';

// Clear session data
$_SESSION = array();

// Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Clear remember me cookie
setcookie('remember_token', '', time() - 3600, '/');
setcookie('remember_user', '', time() - 3600, '/');

// Destroy the session
session_destroy();

// Start new session for flash message
session_start();
$_SESSION['success_message'] = "You have been successfully logged out.";

// Redirect to home page
redirect(SITE_URL . '/index.php');
?> 