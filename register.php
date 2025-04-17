<?php
$pageTitle = "Register";
require_once 'includes/header.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(SITE_URL . '/index.php');
}

// Process registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate inputs
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Name is required.";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }
    
    // Check if email already exists
    if (empty($errors)) {
        $conn = connectDB();
        $sql = "SELECT id FROM users WHERE email = '$email'";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $errors[] = "Email address is already registered.";
        }
        
        if (empty($errors)) {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user into database
            $sql = "INSERT INTO users (name, email, phone, password, role) VALUES ('$name', '$email', '$phone', '$hashed_password', 'customer')";
            
            if ($conn->query($sql)) {
                $_SESSION['success_message'] = "Registration successful! You can now login.";
                redirect(SITE_URL . '/login.php');
            } else {
                $errors[] = "Error creating account: " . $conn->error;
            }
        }
        
        $conn->close();
    }
    
    if (!empty($errors)) {
        $_SESSION['error_message'] = implode("<br>", $errors);
    }
}
?>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card auth-form">
                    <div class="card-body">
                        <h1 class="card-title text-center mb-4">Create Account</h1>
                        
                        <form action="<?php echo SITE_URL; ?>/register.php" method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($_POST['name']) ? $_POST['name'] : ''; ?>" required>
                                <div class="invalid-feedback">
                                    Please enter your full name.
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>" required>
                                <div class="invalid-feedback">
                                    Please enter a valid email address.
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo isset($_POST['phone']) ? $_POST['phone'] : ''; ?>">
                                <div class="form-text">Optional, but recommended for order updates.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div class="invalid-feedback">
                                    Please enter a password (minimum 6 characters).
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                <div class="invalid-feedback">
                                    Please confirm your password.
                                </div>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="agree_terms" required>
                                <label class="form-check-label" for="agree_terms">
                                    I agree to the <a href="<?php echo SITE_URL; ?>/terms.php">Terms and Conditions</a>
                                </label>
                                <div class="invalid-feedback">
                                    You must agree to the terms and conditions.
                                </div>
                            </div>
                            
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary btn-lg">Register</button>
                            </div>
                            
                            <div class="text-center">
                                <p class="mb-0">Already have an account? <a href="<?php echo SITE_URL; ?>/login.php">Login</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?> 