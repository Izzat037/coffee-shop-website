<?php
$pageTitle = "Shopping Cart";
require_once 'includes/header.php';

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle cart actions
if (isset($_GET['action'])) {
    // Remove item from cart
    if ($_GET['action'] === 'remove' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        foreach ($_SESSION['cart'] as $key => $item) {
            if ($item['id'] == $id) {
                unset($_SESSION['cart'][$key]);
                $_SESSION['cart'] = array_values($_SESSION['cart']); // Reindex array
                $_SESSION['success_message'] = "Item removed from cart.";
                redirect(SITE_URL . '/cart.php');
            }
        }
    }
    
    // Update quantity
    if ($_GET['action'] === 'update' && isset($_POST['quantities'])) {
        $quantities = $_POST['quantities'];
        
        // Fetch product stock information
        $conn = connectDB();
        $productIds = array_column($_SESSION['cart'], 'id');
        $productIdsStr = implode(',', $productIds);
        $sql = "SELECT id, stock FROM products WHERE id IN ($productIdsStr)";
        $result = $conn->query($sql);
        $stocks = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $stocks[$row['id']] = $row['stock'];
            }
        }
        
        // Update quantities
        foreach ($_SESSION['cart'] as $key => &$item) {
            $id = $item['id'];
            if (isset($quantities[$id])) {
                $newQuantity = (int)$quantities[$id];
                
                // Check stock
                if (isset($stocks[$id]) && $newQuantity > $stocks[$id]) {
                    $_SESSION['error_message'] = "Quantity for {$item['name']} exceeds available stock ({$stocks[$id]}).";
                    $conn->close();
                    redirect(SITE_URL . '/cart.php');
                }
                
                if ($newQuantity > 0) {
                    $item['quantity'] = $newQuantity;
                } else {
                    unset($_SESSION['cart'][$key]);
                }
            }
        }
        
        $_SESSION['cart'] = array_values($_SESSION['cart']); // Reindex array
        $_SESSION['success_message'] = "Cart updated successfully.";
        $conn->close();
        redirect(SITE_URL . '/cart.php');
    }
    
    // Clear cart
    if ($_GET['action'] === 'clear') {
        $_SESSION['cart'] = [];
        $_SESSION['success_message'] = "Cart cleared.";
        redirect(SITE_URL . '/cart.php');
    }
}

// Calculate cart total
$cartTotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $cartTotal += $item['price'] * $item['quantity'];
}
?>

<section class="py-5">
    <div class="container">
        <h1 class="mb-4">Your Shopping Cart</h1>
        
        <?php if (empty($_SESSION['cart'])): ?>
            <div class="alert alert-info">
                Your cart is empty. <a href="<?php echo SITE_URL; ?>/shop.php">Continue shopping</a>.
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-lg-8">
                    <form action="cart.php?action=update" method="POST">
                        <div class="card mb-4">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Cart Items (<?php echo count($_SESSION['cart']); ?>)</h5>
                            </div>
                            <div class="card-body">
                                <?php foreach ($_SESSION['cart'] as $item): ?>
                                    <div class="row cart-item py-3">
                                        <div class="col-md-2">
                                            <?php 
                                            $conn = connectDB();
                                            $id = $item['id'];
                                            $sql = "SELECT image_url FROM products WHERE id = $id";
                                            $result = $conn->query($sql);
                                            $imgUrl = $result && $result->num_rows > 0 ? $result->fetch_assoc()['image_url'] : 'assets/images/placeholder.jpg';
                                            $conn->close();
                                            ?>
                                            <img src="<?php echo SITE_URL . '/' . $imgUrl; ?>" alt="<?php echo $item['name']; ?>" class="img-fluid rounded">
                                        </div>
                                        <div class="col-md-4">
                                            <a href="product.php?id=<?php echo $item['id']; ?>" class="text-decoration-none">
                                                <h5 class="mb-1"><?php echo $item['name']; ?></h5>
                                            </a>
                                            <p class="mb-0 text-muted small">Unit Price: $<?php echo number_format($item['price'], 2); ?></p>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="quantity-<?php echo $item['id']; ?>" class="form-label">Quantity</label>
                                            <div class="quantity-selector mb-2">
                                                <button type="button" class="decrement">-</button>
                                                <input type="number" name="quantities[<?php echo $item['id']; ?>]" id="quantity-<?php echo $item['id']; ?>" class="form-control" value="<?php echo $item['quantity']; ?>" min="1">
                                                <button type="button" class="increment">+</button>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <p class="fw-bold mb-0">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                                        </div>
                                        <div class="col-md-1 text-end">
                                            <a href="cart.php?action=remove&id=<?php echo $item['id']; ?>" class="text-danger" onclick="return confirm('Are you sure you want to remove this item?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="card-footer bg-white d-flex justify-content-between">
                                <a href="cart.php?action=clear" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to clear your cart?');">
                                    <i class="fas fa-trash-alt me-2"></i>Clear Cart
                                </a>
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-sync-alt me-2"></i>Update Cart
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Order Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal</span>
                                <span>$<?php echo number_format($cartTotal, 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping</span>
                                <span>Free</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-4">
                                <span class="fw-bold">Total</span>
                                <span class="fw-bold">$<?php echo number_format($cartTotal, 2); ?></span>
                            </div>
                            <div class="d-grid">
                                <a href="<?php echo SITE_URL; ?>/checkout.php" class="btn btn-success btn-lg">
                                    Proceed to Checkout
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mt-3">
                        <div class="card-body">
                            <h5 class="card-title">Need Help?</h5>
                            <p class="card-text">Feel free to contact our customer service if you have any questions or concerns.</p>
                            <a href="<?php echo SITE_URL; ?>/contact.php" class="btn btn-outline-primary">Contact Us</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <h5>Continue Shopping</h5>
                <div class="row">
                    <div class="col-md-4">
                        <a href="<?php echo SITE_URL; ?>/shop.php?category=beans" class="btn btn-outline-secondary w-100 mb-2">
                            <i class="fas fa-coffee me-2"></i>Coffee Beans
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="<?php echo SITE_URL; ?>/shop.php?category=tools" class="btn btn-outline-secondary w-100 mb-2">
                            <i class="fas fa-tools me-2"></i>Coffee Tools
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="<?php echo SITE_URL; ?>/shop.php?category=merchandise" class="btn btn-outline-secondary w-100 mb-2">
                            <i class="fas fa-tshirt me-2"></i>Merchandise
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?> 