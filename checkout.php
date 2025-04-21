<?php
$pageTitle = "Checkout";
require_once 'includes/header.php';

// Check if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    $_SESSION['error_message'] = "Your cart is empty. Please add some products before checkout.";
    redirect(SITE_URL . '/shop.php');
}

// Calculate cart total
$cartTotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $cartTotal += $item['price'] * $item['quantity'];
}

// Handle checkout form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate form data
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $city = sanitize($_POST['city']);
    $zip = sanitize($_POST['zip']);
    $payment_method = sanitize($_POST['payment_method']);
    
    // Simple validation
    $errors = [];
    if (empty($name)) $errors[] = "Name is required.";
    if (empty($email)) $errors[] = "Email is required.";
    if (empty($phone)) $errors[] = "Phone is required.";
    if (empty($address)) $errors[] = "Address is required.";
    if (empty($city)) $errors[] = "City is required.";
    if (empty($zip)) $errors[] = "ZIP code is required.";
    
    if (empty($errors)) {
        $conn = connectDB();
        
        // Check if items are still in stock
        $productIds = array_column($_SESSION['cart'], 'id');
        $productIdsStr = implode(',', $productIds);
        $sql = "SELECT id, name, stock FROM products WHERE id IN ($productIdsStr)";
        $result = $conn->query($sql);
        $stocks = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $stocks[$row['id']] = [
                    'name' => $row['name'],
                    'stock' => $row['stock']
                ];
            }
        }
        
        $stockErrors = [];
        foreach ($_SESSION['cart'] as $item) {
            $id = $item['id'];
            if (isset($stocks[$id]) && $item['quantity'] > $stocks[$id]['stock']) {
                $stockErrors[] = "Not enough stock for {$stocks[$id]['name']}. Available: {$stocks[$id]['stock']}.";
            }
        }
        
        if (!empty($stockErrors)) {
            foreach ($stockErrors as $error) {
                $_SESSION['error_message'] = $error;
            }
            $conn->close();
            redirect(SITE_URL . '/cart.php');
        }
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Create order
            $shipping_address = "$address, $city, $zip";
            
            // Handle user_id for SQL insertion
            $user_id_value = "NULL"; // Default to NULL for guest checkout
            if (isLoggedIn()) {
                if (isset($_SESSION['user_id'])) {
                    $user_id_value = $_SESSION['user_id']; // Use actual user_id if available
                }
                // Note: We don't create orders with admin_id
            }
            
            $sql = "INSERT INTO orders (user_id, total_amount, status, shipping_address, payment_method) 
                    VALUES ($user_id_value, $cartTotal, 'pending', '$shipping_address', '$payment_method')";
            $conn->query($sql);
            $order_id = $conn->insert_id;
            
            // Create order items
            foreach ($_SESSION['cart'] as $item) {
                $product_id = $item['id'];
                $quantity = $item['quantity'];
                $price = $item['price'];
                
                $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                        VALUES ($order_id, $product_id, $quantity, $price)";
                $conn->query($sql);
                
                // Update product stock
                $sql = "UPDATE products SET stock = stock - $quantity WHERE id = $product_id";
                $conn->query($sql);
            }
            
            // Commit transaction
            $conn->commit();
            
            // Clear cart
            $_SESSION['cart'] = [];
            
            // Set success message
            $_SESSION['success_message'] = "Your order has been placed successfully! Order #$order_id";
            
            // Redirect to order confirmation
            redirect(SITE_URL . "/order-confirmation.php?id=$order_id");
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $_SESSION['error_message'] = "An error occurred while processing your order: " . $e->getMessage();
            redirect(SITE_URL . '/checkout.php');
        }
        
        $conn->close();
    } else {
        $_SESSION['error_message'] = implode("<br>", $errors);
    }
}

// Get user data if logged in
$userData = [
    'name' => '',
    'email' => '',
    'phone' => '',
    'address' => ''
];

if (isLoggedIn()) {
    $conn = connectDB();
    
    // Check which type of user is logged in and get the appropriate user ID
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $sql = "SELECT name, email, phone, address FROM users WHERE id = $user_id";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $userData = $result->fetch_assoc();
        }
    } elseif (isset($_SESSION['admin_id'])) {
        // Admin user - could fetch admin data if needed
        $admin_id = $_SESSION['admin_id'];
        $sql = "SELECT username as name, 'admin@example.com' as email, '' as phone, '' as address FROM admin WHERE id = $admin_id";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $userData = $result->fetch_assoc();
        }
    }
    
    $conn->close();
}
?>

<section class="py-5">
    <div class="container">
        <h1 class="mb-4">Checkout</h1>
        
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Shipping Information</h5>
                    </div>
                    <div class="card-body">
                        <form action="<?php echo SITE_URL; ?>/checkout.php" method="POST" class="needs-validation" novalidate>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo $userData['name']; ?>" required>
                                    <div class="invalid-feedback">
                                        Please enter your full name.
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo $userData['email']; ?>" required>
                                    <div class="invalid-feedback">
                                        Please enter a valid email.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo $userData['phone']; ?>" required>
                                <div class="invalid-feedback">
                                    Please enter your phone number.
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" class="form-control" id="address" name="address" value="<?php echo $userData['address']; ?>" required>
                                <div class="invalid-feedback">
                                    Please enter your address.
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" class="form-control" id="city" name="city" required>
                                    <div class="invalid-feedback">
                                        Please enter your city.
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="zip" class="form-label">ZIP Code</label>
                                    <input type="text" class="form-control" id="zip" name="zip" required>
                                    <div class="invalid-feedback">
                                        Please enter your ZIP code.
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <h5 class="mb-3">Payment Method</h5>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="payment_method" id="cod" value="COD" checked>
                                <label class="form-check-label" for="cod">
                                    Cash on Delivery (COD)
                                </label>
                            </div>
                            
                            <hr class="my-4">
                            
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="<?php echo SITE_URL; ?>/terms.php">terms and conditions</a>
                                </label>
                                <div class="invalid-feedback">
                                    You must agree to the terms and conditions.
                                </div>
                            </div>
                            
                            <button class="btn btn-primary btn-lg w-100" type="submit">Place Order</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span>
                                    <?php echo $item['name']; ?> x <?php echo $item['quantity']; ?>
                                </span>
                                <span>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                            </div>
                        <?php endforeach; ?>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <span>$<?php echo number_format($cartTotal, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping</span>
                            <span>Free</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold">Total</span>
                            <span class="fw-bold">$<?php echo number_format($cartTotal, 2); ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Need Help?</h5>
                        <p class="card-text">If you have any questions about your order, feel free to contact our customer service.</p>
                        <a href="<?php echo SITE_URL; ?>/contact.php" class="btn btn-outline-primary">Contact Us</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?> 