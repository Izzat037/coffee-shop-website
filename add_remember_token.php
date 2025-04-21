<?php
// Script to add remember_token column to users table

// Include configuration to get database connection details
require_once 'includes/config.php';

// Display header
echo "<!DOCTYPE html>
<html>
<head>
    <title>Add Remember Token Column</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .container { max-width: 800px; margin: 0 auto; }
        .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; }
        .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; }
        .info { color: blue; background: #e3f2fd; padding: 10px; border-radius: 5px; }
        code { background: #f5f5f5; padding: 2px 5px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Database Update Tool</h1>";

try {
    // Connect to database
    $conn = connectDB();
    
    // Step 1: Check if column exists
    echo "<h2>Step 1: Checking if column exists</h2>";
    $checkColumn = $conn->query("SHOW COLUMNS FROM users LIKE 'remember_token'");
    
    if ($checkColumn->num_rows > 0) {
        echo "<p class='info'>The remember_token column already exists in the users table.</p>";
    } else {
        echo "<p class='info'>The remember_token column does not exist in the users table. Adding it now...</p>";
        
        // Step 2: Add the column
        echo "<h2>Step 2: Adding column</h2>";
        
        if ($conn->query("ALTER TABLE users ADD COLUMN remember_token VARCHAR(255) NULL")) {
            echo "<p class='success'>Successfully added remember_token column to users table!</p>";
        } else {
            echo "<p class='error'>Error adding column: " . $conn->error . "</p>";
        }
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
}

// Display footer with next steps
echo "
        <h2>Next Steps</h2>
        <p>You can now navigate back to:</p>
        <ul>
            <li><a href='" . SITE_URL . "/login.php'>Login Page</a></li>
            <li><a href='" . SITE_URL . "/index.php'>Home Page</a></li>
        </ul>
        <p>The Remember Me functionality should now work correctly.</p>
    </div>
</body>
</html>"; 