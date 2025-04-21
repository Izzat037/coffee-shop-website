<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../includes/config.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Display session data
echo "<h2>Session Data</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Check if user is logged in
echo "<h2>Login Status</h2>";
echo "Is Logged In: " . (isLoggedIn() ? "Yes" : "No") . "<br>";
echo "Is Admin: " . (isAdmin() ? "Yes" : "No") . "<br>";

// Check database connection
echo "<h2>Database Connection</h2>";
try {
    $conn = connectDB();
    echo "Database Connection: Successful<br>";
    
    // Try to get admin users
    $sql = "SELECT * FROM admin";
    $result = $conn->query($sql);
    
    if ($result) {
        echo "Admin Users Found: " . $result->num_rows . "<br><br>";
        
        if ($result->num_rows > 0) {
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>ID</th><th>Username</th><th>Created</th></tr>";
            
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                echo "<td>" . $row['created_at'] . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        }
    } else {
        echo "Error querying admin table: " . $conn->error;
    }
    
    $conn->close();
} catch (Exception $e) {
    echo "Database Connection Error: " . $e->getMessage();
}

// Navigation links
echo "<h2>Navigation</h2>";
echo "<ul>";
echo "<li><a href='" . SITE_URL . "/admin/index.php'>Login Page</a></li>";
echo "<li><a href='" . SITE_URL . "/admin/dashboard.php'>Dashboard</a></li>";
echo "</ul>";
?> 