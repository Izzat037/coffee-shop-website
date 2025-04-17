<?php
$pageTitle = "My Profile";
require_once 'includes/header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['error_message'] = "Please login to view your profile.";
    $_SESSION['redirect_after_login'] = SITE_URL . '/profile.php';
    redirect(SITE_URL . '/login.php');
}

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Get user data from database
$conn = connectDB();
$sql = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($sql);

if (!$result || $result->num_rows === 0) {
    $_SESSION['error_message'] = "User not found.";
    redirect(SITE_URL . '/index.php');
}

$user = $result->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        // Get form data
        $name = sanitize($_POST['name']);
        $email = sanitize($_POST['email']);
        $phone = sanitize($_POST['phone']);
        $address = sanitize($_POST['address']);
        
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
        
        // Check if email already exists (if changed)
        if ($email !== $user['email']) {
            $sql = "SELECT id FROM users WHERE email = '$email' AND id != $user_id";
            $result = $conn->query($sql);
            
            if ($result && $result->num_rows > 0) {
                $errors[] = "Email address is already registered.";
            }
        }
        
        if (empty($errors)) {
            // Update user in database
            $sql = "UPDATE users SET name = '$name', email = '$email', phone = '$phone', address = '$address' WHERE id = $user_id";
            
            if ($conn->query($sql)) {
                // Update session data
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                
                $_SESSION['success_message'] = "Profile updated successfully.";
                redirect(SITE_URL . '/profile.php');
            } else {
                $errors[] = "Error updating profile: " . $conn->error;
            }
        }
        
        if (!empty($errors)) {
            $_SESSION['error_message'] = implode("<br>", $errors);
        }
    } elseif (isset($_POST['change_password'])) {
        // Get form data
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validate inputs
        $errors = [];
        
        if (empty($current_password)) {
            $errors[] = "Current password is required.";
        }
        
        if (empty($new_password)) {
            $errors[] = "New password is required.";
        } elseif (strlen($new_password) < 6) {
            $errors[] = "New password must be at least 6 characters long.";
        }
        
        if ($new_password !== $confirm_password) {
            $errors[] = "New passwords do not match.";
        }
        
        // Verify current password
        if (empty($errors) && !password_verify($current_password, $user['password'])) {
            $errors[] = "Current password is incorrect.";
        }
        
        if (empty($errors)) {
            // Hash new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password in database
            $sql = "UPDATE users SET password = '$hashed_password' WHERE id = $user_id";
            
            if ($conn->query($sql)) {
                $_SESSION['success_message'] = "Password changed successfully.";
                redirect(SITE_URL . '/profile.php');
            } else {
                $errors[] = "Error changing password: " . $conn->error;
            }
        }
        
        if (!empty($errors)) {
            $_SESSION['error_message'] = implode("<br>", $errors);
        }
    }
}

// Get recent orders
$sql = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5";
$result = $conn->query($sql);
$recentOrders = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $recentOrders[] = $row;
    }
}

$conn->close();
?>

<section class="py-5">
    <div class="container">
        <h1 class="mb-4">My Profile</h1>
        
        <div class="row">
            <!-- Sidebar Navigation -->
            <div class="col-md-3">
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Account Navigation</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="#profile-info" class="list-group-item list-group-item-action active" data-bs-toggle="tab">Profile Information</a>
                        <a href="#change-password" class="list-group-item list-group-item-action" data-bs-toggle="tab">Change Password</a>
                        <a href="#recent-orders" class="list-group-item list-group-item-action" data-bs-toggle="tab">Recent Orders</a>
                        <a href="<?php echo SITE_URL; ?>/orders.php" class="list-group-item list-group-item-action">View All Orders</a>
                        <?php if (isAdmin()): ?>
                            <a href="<?php echo SITE_URL; ?>/admin" class="list-group-item list-group-item-action">Admin Panel</a>
                        <?php endif; ?>
                        <a href="<?php echo SITE_URL; ?>/logout.php" class="list-group-item list-group-item-action text-danger">Logout</a>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9">
                <div class="tab-content">
                    <!-- Profile Information Tab -->
                    <div class="tab-pane fade show active" id="profile-info">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Profile Information</h5>
                            </div>
                            <div class="card-body">
                                <form action="<?php echo SITE_URL; ?>/profile.php" method="POST" class="needs-validation" novalidate>
                                    <input type="hidden" name="update_profile" value="1">
                                    
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Full Name</label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?php echo $user['name']; ?>" required>
                                        <div class="invalid-feedback">
                                            Please enter your full name.
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                                        <div class="invalid-feedback">
                                            Please enter a valid email address.
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo $user['phone']; ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="address" class="form-label">Address</label>
                                        <textarea class="form-control" id="address" name="address" rows="3"><?php echo $user['address']; ?></textarea>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">Update Profile</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Change Password Tab -->
                    <div class="tab-pane fade" id="change-password">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Change Password</h5>
                            </div>
                            <div class="card-body">
                                <form action="<?php echo SITE_URL; ?>/profile.php" method="POST" class="needs-validation" novalidate>
                                    <input type="hidden" name="change_password" value="1">
                                    
                                    <div class="mb-3">
                                        <label for="current_password" class="form-label">Current Password</label>
                                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                                        <div class="invalid-feedback">
                                            Please enter your current password.
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="new_password" class="form-label">New Password</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                                        <div class="invalid-feedback">
                                            Please enter a new password (minimum 6 characters).
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                        <div class="invalid-feedback">
                                            Please confirm your new password.
                                        </div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">Change Password</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Orders Tab -->
                    <div class="tab-pane fade" id="recent-orders">
                        <div class="card">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Recent Orders</h5>
                                <a href="<?php echo SITE_URL; ?>/orders.php" class="btn btn-sm btn-outline-primary">View All</a>
                            </div>
                            <div class="card-body">
                                <?php if (empty($recentOrders)): ?>
                                    <div class="alert alert-info">
                                        You haven't placed any orders yet. <a href="<?php echo SITE_URL; ?>/shop.php">Start shopping</a>.
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Order #</th>
                                                    <th>Date</th>
                                                    <th>Status</th>
                                                    <th>Total</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($recentOrders as $order): ?>
                                                    <tr>
                                                        <td>#<?php echo $order['id']; ?></td>
                                                        <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                                        <td>
                                                            <span class="badge bg-<?php 
                                                                echo ($order['status'] === 'pending') ? 'warning' : 
                                                                    (($order['status'] === 'shipped') ? 'primary' : 
                                                                        (($order['status'] === 'delivered') ? 'success' : 'danger')); 
                                                            ?>">
                                                                <?php echo ucfirst($order['status']); ?>
                                                            </span>
                                                        </td>
                                                        <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                                        <td>
                                                            <a href="<?php echo SITE_URL; ?>/order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                                View
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?> 