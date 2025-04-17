<?php
$pageTitle = "Home";
require_once 'includes/header.php';

// Get featured products
$conn = connectDB();
$sql = "SELECT * FROM products ORDER BY RAND() LIMIT 4";
$result = $conn->query($sql);
$featuredProducts = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $featuredProducts[] = $row;
    }
}

// Get latest products
$sql = "SELECT * FROM products ORDER BY created_at DESC LIMIT 4";
$result = $conn->query($sql);
$latestProducts = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $latestProducts[] = $row;
    }
}

$conn->close();
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <h1>Welcome to Tree Smoker</h1>
        <p>Premium coffee beans and accessories for a relaxed brewing experience</p>
        <a href="shop.php" class="btn btn-success btn-lg">Shop Now</a>
    </div>
</section>

<!-- Featured Products -->
<section class="py-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2>Featured Products</h2>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="shop.php" class="btn btn-outline-primary">View All</a>
            </div>
        </div>
        
        <div class="row">
            <?php if (empty($featuredProducts)): ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        No featured products available at the moment. Please check back later.
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($featuredProducts as $product): ?>
                    <div class="col-md-3 col-sm-6">
                        <div class="product-card">
                            <img src="<?php echo SITE_URL . '/' . $product['image_url']; ?>" alt="<?php echo $product['name']; ?>" class="product-img">
                            <div class="card-body">
                                <span class="category-badge">
                                    <?php echo ucfirst($product['category']); ?>
                                </span>
                                <h5 class="card-title"><?php echo $product['name']; ?></h5>
                                <div class="price">$<?php echo number_format($product['price'], 2); ?></div>
                                <div class="d-grid">
                                    <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">View Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- About Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h2>About Tree Smoker</h2>
                <p>Tree Smoker is a boutique coffee brand dedicated to providing the highest quality beans and accessories for the discerning coffee enthusiast.</p>
                <p>Founded with a passion for exceptional coffee and a relaxed lifestyle, we source our beans from sustainable farms around the world and roast them to perfection to bring out their unique flavors.</p>
                <a href="about.php" class="btn btn-primary">Learn More</a>
            </div>
            <div class="col-md-6">
                <img src="assets/images/about.jpg" alt="About Tree Smoker" class="img-fluid rounded">
            </div>
        </div>
    </div>
</section>

<!-- Latest Products -->
<section class="py-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2>Latest Arrivals</h2>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="shop.php?sort=newest" class="btn btn-outline-primary">View All</a>
            </div>
        </div>
        
        <div class="row">
            <?php if (empty($latestProducts)): ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        No new products available at the moment. Please check back later.
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($latestProducts as $product): ?>
                    <div class="col-md-3 col-sm-6">
                        <div class="product-card">
                            <img src="<?php echo SITE_URL . '/' . $product['image_url']; ?>" alt="<?php echo $product['name']; ?>" class="product-img">
                            <div class="card-body">
                                <span class="category-badge">
                                    <?php echo ucfirst($product['category']); ?>
                                </span>
                                <h5 class="card-title"><?php echo $product['name']; ?></h5>
                                <div class="price">$<?php echo number_format($product['price'], 2); ?></div>
                                <div class="d-grid">
                                    <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">View Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">What Our Customers Say</h2>
        <div class="row">
            <div class="col-md-4">
                <div class="review-card">
                    <div class="star-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="review-text">"The Morning Blaze Blend is seriously the best coffee I've ever had. Perfect balance of flavors and the aroma is incredible."</p>
                    <p class="review-user">John D.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="review-card">
                    <div class="star-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </div>
                    <p class="review-text">"I love their ceramic mugs! High quality and keeps my coffee hot for a really long time. The designs are super cute too."</p>
                    <p class="review-user">Sarah M.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="review-card">
                    <div class="star-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="review-text">"The grinder I bought from Tree Smoker is a game changer. My morning coffee has never tasted better. Highly recommended!"</p>
                    <p class="review-user">Mike T.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?> 