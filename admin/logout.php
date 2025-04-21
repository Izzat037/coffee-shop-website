<?php
// Include configuration file
require_once '../includes/config.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Clear all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Set a success message
$_SESSION['success_message'] = "You have been successfully logged out.";

// Redirect to login page
redirect(SITE_URL . '/admin/index.php'); 