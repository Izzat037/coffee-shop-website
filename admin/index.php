<?php
require_once '../includes/config.php';

// Redirect to dashboard if already logged in as admin
if (isLoggedIn() && isAdmin()) {
    redirect(SITE_URL . '/admin/dashboard.php');
}

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    // Validate inputs
    if (empty($username) || empty($password)) {
        $_SESSION['error_message'] = "Please enter both username and password.";
    } else {
        $conn = connectDB();
        
        // Get user by username
        $sql = "SELECT * FROM admin WHERE username = '$username'";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $admin['password'])) {
                // Set session variables
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['user_role'] = 'admin'; // To use the same isAdmin() function
                
                $_SESSION['success_message'] = "Welcome to the admin panel, " . $admin['username'] . "!";
                
                $conn->close();
                redirect(SITE_URL . '/admin/dashboard.php');
            } else {
                $_SESSION['error_message'] = "Invalid username or password.";
            }
        } else {
            $_SESSION['error_message'] = "Invalid username or password.";
        }
        
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Tree Smoker</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/admin.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <img src="<?php echo SITE_URL; ?>/assets/images/logo.png" alt="Tree Smoker Logo" height="70">
                            <h2 class="mt-3">Admin Login</h2>
                        </div>
                        
                        <?php if(isset($_SESSION['error_message'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <?php echo $_SESSION['error_message']; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <?php unset($_SESSION['error_message']); ?>
                        <?php endif; ?>

                        <?php if(isset($_SESSION['success_message'])): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <?php echo $_SESSION['success_message']; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <?php unset($_SESSION['success_message']); ?>
                        <?php endif; ?>
                        
                        <form action="<?php echo SITE_URL; ?>/admin/index.php" method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="username" name="username" required>
                                </div>
                                <div class="invalid-feedback">
                                    Please enter your username.
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <div class="invalid-feedback">
                                    Please enter your password.
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">Login</button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-4">
                            <a href="<?php echo SITE_URL; ?>" class="text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i> Back to Website
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function() {
            'use strict';
            
            var forms = document.querySelectorAll('.needs-validation');
            
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
</body>
</html> 