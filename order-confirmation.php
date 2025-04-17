<?php
$pageTitle = "Order Confirmation";
require_once 'includes/header.php';

// Check if order ID is provided
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id <= 0) {
    $_SESSION['error_message'] = "Invalid order ID.";
    redirect(SITE_URL . '/index.php');
}

// Get order details
$conn = connectDB();
$sql = "SELECT * FROM orders WHERE id = $order_id";
$result = $conn->query($sql);

if (!$result || $result->num_rows === 0) {
    $_SESSION['error_message'] = "Order not found.";
    redirect(SITE_URL . '/index.php');
}

$order = $result->fetch_assoc();

// Check if user is authorized to view this order
if (isLoggedIn() && $order['user_id'] != $_SESSION['user_id'] && !isAdmin()) {
    $_SESSION['error_message'] = "You are not authorized to view this order.";
    redirect(SITE_URL . '/index.php');
}

// Get order items
$sql = "SELECT oi.*, p.name, p.image_url FROM order_items oi 
        LEFT JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = $order_id";
$result = $conn->query($sql);
$orderItems = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orderItems[] = $row;
    }
}

$conn->close();
?>

<section class="py-5">
    <div class="container">
        <div class="card">
            <div class="card-body">
                <div class="text-center mb-4">
                    <i class="fas fa-check-circle text-success fa-5x mb-3"></i>
                    <h1>Thank You for Your Order!</h1>
                    <p class="lead">Your order has been placed successfully and is being processed.</p>
                </div>
                
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="card mb-4">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Order Details</h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Order Number:</strong></p>
                                        <p>#<?php echo $order_id; ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Order Date:</strong></p>
                                        <p><?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?></p>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Order Status:</strong></p>
                                        <p>
                                            <span class="badge bg-<?php 
                                                echo ($order['status'] === 'pending') ? 'warning' : 
                                                    (($order['status'] === 'shipped') ? 'primary' : 
                                                        (($order['status'] === 'delivered') ? 'success' : 'danger')); 
                                            ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Payment Method:</strong></p>
                                        <p><?php echo $order['payment_method']; ?></p>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <p class="mb-1"><strong>Shipping Address:</strong></p>
                                    <p><?php echo $order['shipping_address']; ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Order Items</h5>
                            </div>
                            <div class="card-body">
                                <?php foreach ($orderItems as $item): ?>
                                    <div class="row mb-3 py-2 border-bottom">
                                        <div class="col-md-2">
                                            <img src="<?php echo SITE_URL . '/' . ($item['image_url'] ?? 'assets/images/placeholder.jpg'); ?>" alt="<?php echo $item['name']; ?>" class="img-fluid rounded">
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="mb-1"><?php echo $item['name']; ?></h6>
                                            <p class="mb-0 small text-muted">Quantity: <?php echo $item['quantity']; ?></p>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <p class="mb-0">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                
                                <div class="d-flex justify-content-between mt-4">
                                    <span class="fw-bold">Total</span>
                                    <span class="fw-bold">$<?php echo number_format($order['total_amount'], 2); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <p>A confirmation email has been sent to your email address.</p>
                            <div class="mt-4">
                                <a href="<?php echo SITE_URL; ?>/shop.php" class="btn btn-primary me-2">Continue Shopping</a>
                                <?php if (isLoggedIn()): ?>
                                    <a href="<?php echo SITE_URL; ?>/orders.php" class="btn btn-outline-primary">View All Orders</a>
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