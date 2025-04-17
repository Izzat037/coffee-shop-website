<?php
require_once 'includes/header.php';

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    $_SESSION['error_message'] = "Invalid product ID.";
    redirect(SITE_URL . '/shop.php');
}

// Connect to database
$conn = connectDB();

// Get product details
$sql = "SELECT * FROM products WHERE id = $product_id";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $product = $result->fetch_assoc();
    $pageTitle = $product['name'];
} else {
    $_SESSION['error_message'] = "Product not found.";
    redirect(SITE_URL . '/shop.php');
}

// Get related products
$sql = "SELECT * FROM products WHERE category = '{$product['category']}' AND id != $product_id ORDER BY RAND() LIMIT 4";
$result = $conn->query($sql);
$relatedProducts = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $relatedProducts[] = $row;
    }
}

// Get reviews for this product
$sql = "SELECT r.*, u.name as user_name FROM reviews r 
        JOIN users u ON r.user_id = u.id 
        WHERE r.product_id = $product_id AND r.is_approved = 1 
        ORDER BY r.created_at DESC";
$result = $conn->query($sql);
$reviews = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $reviews[] = $row;
    }
}

// Calculate average rating
$avgRating = 0;
$reviewCount = count($reviews);
if ($reviewCount > 0) {
    $totalRating = 0;
    foreach ($reviews as $review) {
        $totalRating += $review['rating'];
    }
    $avgRating = round($totalRating / $reviewCount, 1);
}

// Check if user has already reviewed this product
$userHasReviewed = false;
if (isLoggedIn()) {
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT id FROM reviews WHERE user_id = $user_id AND product_id = $product_id";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $userHasReviewed = true;
    }
}

// Handle review form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating']) && isLoggedIn() && !$userHasReviewed) {
    $rating = (int)$_POST['rating'];
    $comment = isset($_POST['comment']) ? sanitize($_POST['comment']) : '';
    $user_id = $_SESSION['user_id'];
    
    // Validate rating
    if ($rating < 1 || $rating > 5) {
        $_SESSION['error_message'] = "Invalid rating. Please rate between 1 and 5 stars.";
    } else {
        // Insert review
        $sql = "INSERT INTO reviews (user_id, product_id, rating, comment) VALUES ($user_id, $product_id, $rating, '$comment')";
        if ($conn->query($sql)) {
            $_SESSION['success_message'] = "Your review has been submitted and is pending approval.";
            redirect(SITE_URL . "/product.php?id=$product_id");
        } else {
            $_SESSION['error_message'] = "Error submitting review: " . $conn->error;
        }
    }
}

$conn->close();
?>

<!-- Product Details Section -->
<section class="py-5">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/shop.php">Shop</a></li>
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/shop.php?category=<?php echo $product['category']; ?>"><?php echo ucfirst($product['category']); ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo $product['name']; ?></li>
            </ol>
        </nav>
        
        <div class="row">
            <div class="col-md-6">
                <img src="<?php echo SITE_URL . '/' . $product['image_url']; ?>" alt="<?php echo $product['name']; ?>" class="img-fluid rounded product-detail-img">
            </div>
            <div class="col-md-6 product-details">
                <h1><?php echo $product['name']; ?></h1>
                
                <?php if ($reviewCount > 0): ?>
                    <div class="d-flex align-items-center mb-3">
                        <div class="star-rating me-2">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?php if ($i <= $avgRating): ?>
                                    <i class="fas fa-star"></i>
                                <?php elseif ($i - 0.5 <= $avgRating): ?>
                                    <i class="fas fa-star-half-alt"></i>
                                <?php else: ?>
                                    <i class="far fa-star"></i>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                        <span><?php echo $avgRating; ?> (<?php echo $reviewCount; ?> reviews)</span>
                    </div>
                <?php endif; ?>
                
                <p class="price">$<?php echo number_format($product['price'], 2); ?></p>
                <p class="mb-4"><?php echo $product['description']; ?></p>
                
                <form action="add-to-cart.php" method="POST">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <div class="quantity-selector">
                            <button type="button" class="decrement">-</button>
                            <input type="number" name="quantity" id="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" class="form-control">
                            <button type="button" class="increment">+</button>
                        </div>
                    </div>
                    
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary btn-lg">Add to Cart</button>
                    </div>
                    
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <span class="badge <?php echo $product['stock'] > 0 ? 'bg-success' : 'bg-danger'; ?>">
                                <?php echo $product['stock'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                            </span>
                        </div>
                        <div>
                            <span class="text-muted">Category: </span>
                            <a href="<?php echo SITE_URL; ?>/shop.php?category=<?php echo $product['category']; ?>"><?php echo ucfirst($product['category']); ?></a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Product Reviews -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="mb-4">Reviews</h2>
        
        <div class="row">
            <div class="col-md-8">
                <?php if (empty($reviews)): ?>
                    <div class="alert alert-info">
                        No reviews yet. Be the first to review this product!
                    </div>
                <?php else: ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-card mb-3">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="star-rating mb-2">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fa<?php echo $i <= $review['rating'] ? 's' : 'r'; ?> fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <p class="review-user"><?php echo $review['user_name']; ?></p>
                                </div>
                                <div>
                                    <p class="review-date"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></p>
                                </div>
                            </div>
                            <p><?php echo $review['comment']; ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <?php if (isLoggedIn() && !$userHasReviewed): ?>
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">Leave a Review</h5>
                        </div>
                        <div class="card-body">
                            <form action="<?php echo SITE_URL; ?>/product.php?id=<?php echo $product_id; ?>" method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Rating</label>
                                    <div class="rating-container">
                                        <?php for ($i = 5; $i >= 1; $i--): ?>
                                            <input type="radio" name="rating" value="<?php echo $i; ?>" id="rating-<?php echo $i; ?>" class="rating-input" required>
                                            <label for="rating-<?php echo $i; ?>" class="rating-label"><i class="fas fa-star"></i></label>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="comment" class="form-label">Your Review</label>
                                    <textarea name="comment" id="comment" class="form-control" rows="4"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Submit Review</button>
                            </form>
                        </div>
                    </div>
                <?php elseif (!isLoggedIn()): ?>
                    <div class="alert alert-info mt-4">
                        Please <a href="<?php echo SITE_URL; ?>/login.php">login</a> to leave a review.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Related Products -->
<section class="py-5">
    <div class="container">
        <h2 class="mb-4">Related Products</h2>
        
        <div class="row">
            <?php if (empty($relatedProducts)): ?>
                <div class="col-12">
                    <div class="alert alert-info">No related products found.</div>
                </div>
            <?php else: ?>
                <?php foreach ($relatedProducts as $relatedProduct): ?>
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="product-card">
                            <img src="<?php echo SITE_URL . '/' . $relatedProduct['image_url']; ?>" alt="<?php echo $relatedProduct['name']; ?>" class="product-img">
                            <div class="card-body">
                                <span class="category-badge">
                                    <?php echo ucfirst($relatedProduct['category']); ?>
                                </span>
                                <h5 class="card-title"><?php echo $relatedProduct['name']; ?></h5>
                                <div class="price">$<?php echo number_format($relatedProduct['price'], 2); ?></div>
                                <div class="d-grid">
                                    <a href="product.php?id=<?php echo $relatedProduct['id']; ?>" class="btn btn-primary">View Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?> 