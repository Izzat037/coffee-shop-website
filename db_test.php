<?php
// Database connection test
require_once 'includes/config.php';

echo "<h1>Database Connection Test</h1>";

try {
    $conn = connectDB();
    echo "<p style='color: green;'>Connection successful!</p>";
    
    // Check if products table exists
    $result = $conn->query("SHOW TABLES LIKE 'products'");
    if ($result->num_rows > 0) {
        echo "<p style='color: green;'>Products table exists.</p>";
        
        // Check product count
        $countResult = $conn->query("SELECT COUNT(*) as total FROM products");
        $total = $countResult->fetch_assoc()['total'];
        echo "<p>Total products in database: {$total}</p>";
        
        // Show category data
        $categoryResult = $conn->query("SELECT DISTINCT category FROM products");
        if ($categoryResult->num_rows > 0) {
            echo "<p>Categories in database:</p><ul>";
            while ($row = $categoryResult->fetch_assoc()) {
                echo "<li>" . htmlspecialchars($row['category']) . "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color: orange;'>No categories found.</p>";
        }
    } else {
        echo "<p style='color: red;'>Products table does not exist!</p>";
    }
    
    $conn->close();
} catch (Exception $e) {
    echo "<p style='color: red;'>Connection failed: " . $e->getMessage() . "</p>";
}

// Session check
echo "<h2>Session Check</h2>";
echo "<p>Session status: " . session_status() . "</p>";
echo "<pre>SESSION data: " . print_r($_SESSION, true) . "</pre>";

// Admin check
echo "<h2>Admin Check</h2>";
if (isAdmin()) {
    echo "<p style='color: green;'>User is logged in as admin.</p>";
} else {
    echo "<p style='color: red;'>User is NOT logged in as admin.</p>";
    echo "<p>To be an admin, your session needs to have 'user_role' set to 'admin'.</p>";
}
?> 