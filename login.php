<?php
$pageTitle = "Login";
require_once 'includes/header.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(SITE_URL . '/index.php');
}

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;
    
    // Validate inputs
    if (empty($email) || empty($password)) {
        $_SESSION['error_message'] = "Please enter both email and password.";
    } else {
        $conn = connectDB();
        
        // Get user by email
        $sql = "SELECT * FROM users WHERE email = '$email'";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                // Set remember me cookie if requested
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $expires = time() + (30 * 24 * 60 * 60); // 30 days
                    
                    // Store token in database
                    $sql = "UPDATE users SET remember_token = '$token' WHERE id = " . $user['id'];
                    $conn->query($sql);
                    
                    // Set cookie
                    setcookie('remember_token', $token, $expires, '/');
                    setcookie('remember_user', $user['id'], $expires, '/');
                }
                
                $_SESSION['success_message'] = "Welcome back, " . $user['name'] . "!";
                
                // Redirect to intended page or home
                $redirect = isset($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : SITE_URL . '/index.php';
                unset($_SESSION['redirect_after_login']);
                
                $conn->close();
                redirect($redirect);
            } else {
                $_SESSION['error_message'] = "Invalid email or password.";
            }
        } else {
            $_SESSION['error_message'] = "Invalid email or password.";
        }
        
        $conn->close();
    }
}
?>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card auth-form">
                    <div class="card-body">
                        <h1 class="card-title text-center mb-4">Login</h1>
                        
                        <form action="<?php echo SITE_URL; ?>/login.php" method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                                <div class="invalid-feedback">
                                    Please enter a valid email address.
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div class="invalid-feedback">
                                    Please enter your password.
                                </div>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>
                            
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary btn-lg">Login</button>
                            </div>
                            
                            <div class="text-center">
                                <p class="mb-0">Don't have an account? <a href="<?php echo SITE_URL; ?>/register.php">Register</a></p>
                                <p class="mt-2"><a href="<?php echo SITE_URL; ?>/forgot-password.php">Forgot Password?</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?> 