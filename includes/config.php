<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tree_smoker');

// Application configuration
define('SITE_NAME', 'Tree Smoker');
define('SITE_URL', 'http://localhost/TreeSmoker');
define('UPLOAD_DIR', 'assets/images/products/');

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Connect to the database
function connectDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check the connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

// Function to sanitize input data
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) || isset($_SESSION['admin_id']);
}

// Function to check if user is admin
function isAdmin() {
    return (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') || isset($_SESSION['admin_id']);
}

// Function to redirect to a page
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Function to display error message
function displayError($message) {
    return "<div class='alert alert-danger'>{$message}</div>";
}

// Function to display success message
function displaySuccess($message) {
    return "<div class='alert alert-success'>{$message}</div>";
}
?> 