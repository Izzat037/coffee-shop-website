<?php
// This is a standalone setup page for inserting admin users
// Not linked from any other page for security reasons

// Include configuration
require_once '../includes/config.php';

// Define password hash cost
$options = ['cost' => 10];

// Initialize messages array
$messages = [];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = isset($_POST['username']) ? sanitize($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    
    // Validate form data
    $errors = [];
    
    if (empty($username)) {
        $errors[] = "Username is required";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match";
    }
    
    // If no errors, insert admin
    if (empty($errors)) {
        // Connect to database
        $conn = connectDB();
        
        // Check if username already exists
        $checkSql = "SELECT id FROM admin WHERE username = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("s", $username);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $messages[] = [
                'type' => 'danger',
                'text' => "Username '$username' already exists"
            ];
        } else {
            // Insert new admin
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT, $options);
            
            $sql = "INSERT INTO admin (username, password) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $username, $hashedPassword);
            
            if ($stmt->execute()) {
                $messages[] = [
                    'type' => 'success',
                    'text' => "Admin user '$username' created successfully"
                ];
            } else {
                $messages[] = [
                    'type' => 'danger',
                    'text' => "Error creating admin: " . $conn->error
                ];
            }
        }
        
        $conn->close();
    } else {
        // Show validation errors
        foreach ($errors as $error) {
            $messages[] = [
                'type' => 'danger',
                'text' => $error
            ];
        }
    }
}

// Get existing admin users
$conn = connectDB();
$sql = "SELECT id, username, created_at FROM admin ORDER BY id";
$result = $conn->query($sql);
$admins = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $admins[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Setup - Tree Smoker</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 2rem 0;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .card-header {
            background-color: #4e73df;
            color: white;
            border-radius: 10px 10px 0 0 !important;
        }
        .btn-primary {
            background-color: #4e73df;
            border-color: #4e73df;
        }
        .btn-primary:hover {
            background-color: #2e59d9;
            border-color: #2e59d9;
        }
        .table {
            margin-bottom: 0;
        }
        .admin-list {
            max-height: 300px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Display messages -->
                <?php foreach ($messages as $message): ?>
                    <div class="alert alert-<?php echo $message['type']; ?> alert-dismissible fade show">
                        <?php echo $message['text']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endforeach; ?>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-user-shield me-2"></i>Create Admin User</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div class="form-text">Password must be at least 6 characters long.</div>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Create Admin</button>
                        </form>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-users-cog me-2"></i>Existing Admin Users</h4>
                    </div>
                    <div class="card-body admin-list">
                        <?php if (empty($admins)): ?>
                            <p class="text-center">No admin users found.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Username</th>
                                            <th>Created Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($admins as $admin): ?>
                                            <tr>
                                                <td><?php echo $admin['id']; ?></td>
                                                <td><?php echo htmlspecialchars($admin['username']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($admin['created_at'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="mt-4 text-center">
                    <p class="text-danger">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        <strong>Important:</strong> This page should be deleted from the server after initial setup for security!
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 