<?php
$pageTitle = "Shop";
require_once 'includes/header.php';

// Get query parameters
$category = isset($_GET['category']) ? sanitize($_GET['category']) : '';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$sort = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'default';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12; // Products per page
$offset = ($page - 1) * $limit;

// Connect to database
$conn = connectDB();

// Build the query
$sql = "SELECT * FROM products WHERE 1=1";
$countSql = "SELECT COUNT(*) as total FROM products WHERE 1=1";

// Add category filter
if (!empty($category)) {
    $sql .= " AND category = '$category'";
    $countSql .= " AND category = '$category'";
}

// Add search filter
if (!empty($search)) {
    $sql .= " AND (name LIKE '%$search%' OR description LIKE '%$search%')";
    $countSql .= " AND (name LIKE '%$search%' OR description LIKE '%$search%')";
}

// Add sorting
switch ($sort) {
    case 'price_low':
        $sql .= " ORDER BY price ASC";
        break;
    case 'price_high':
        $sql .= " ORDER BY price DESC";
        break;
    case 'newest':
        $sql .= " ORDER BY created_at DESC";
        break;
    case 'name_az':
        $sql .= " ORDER BY name ASC";
        break;
    case 'name_za':
        $sql .= " ORDER BY name DESC";
        break;
    default:
        $sql .= " ORDER BY id ASC";
}

// Add pagination
$sql .= " LIMIT $limit OFFSET $offset";

// Get products
$result = $conn->query($sql);
$products = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// Get total count for pagination
$countResult = $conn->query($countSql);
$totalProducts = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalProducts / $limit);

$conn->close();

// Get category name for display
$categoryName = '';
if (!empty($category)) {
    $categoryName = ucfirst($category);
}
?>

<!-- Shop Header -->
<section class="py-3 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1><?php echo !empty($categoryName) ? $categoryName : 'All Products'; ?></h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Shop</li>
                        <?php if (!empty($categoryName)): ?>
                            <li class="breadcrumb-item active" aria-current="page"><?php echo $categoryName; ?></li>
                        <?php endif; ?>
                    </ol>
                </nav>
            </div>
            <div class="col-md-6">
                <form action="shop.php" method="GET" class="d-flex">
                    <?php if (!empty($category)): ?>
                        <input type="hidden" name="category" value="<?php echo $category; ?>">
                    <?php endif; ?>
                    <input type="text" name="search" class="form-control" placeholder="Search products..." value="<?php echo $search; ?>">
                    <button type="submit" class="btn btn-primary ms-2">Search</button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Filter & Sort Section -->
<section class="py-3">
    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Categories</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <a href="shop.php" class="text-decoration-none <?php echo empty($category) ? 'fw-bold' : ''; ?>">All Products</a>
                            </li>
                            <li class="list-group-item">
                                <a href="shop.php?category=beans" class="text-decoration-none <?php echo $category === 'beans' ? 'fw-bold' : ''; ?>">Coffee Beans</a>
                            </li>
                            <li class="list-group-item">
                                <a href="shop.php?category=tools" class="text-decoration-none <?php echo $category === 'tools' ? 'fw-bold' : ''; ?>">Tools</a>
                            </li>
                            <li class="list-group-item">
                                <a href="shop.php?category=merchandise" class="text-decoration-none <?php echo $category === 'merchandise' ? 'fw-bold' : ''; ?>">Merchandise</a>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Price Range</h5>
                    </div>
                    <div class="card-body">
                        <form action="shop.php" method="GET">
                            <?php if (!empty($category)): ?>
                                <input type="hidden" name="category" value="<?php echo $category; ?>">
                            <?php endif; ?>
                            <?php if (!empty($search)): ?>
                                <input type="hidden" name="search" value="<?php echo $search; ?>">
                            <?php endif; ?>
                            <div class="mb-3">
                                <label for="min_price" class="form-label">Min Price</label>
                                <input type="number" name="min_price" id="min_price" class="form-control" min="0" step="0.01">
                            </div>
                            <div class="mb-3">
                                <label for="max_price" class="form-label">Max Price</label>
                                <input type="number" name="max_price" id="max_price" class="form-control" min="0" step="0.01">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Apply Filter</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <p class="mb-0">Showing <?php echo count($products); ?> of <?php echo $totalProducts; ?> products</p>
                    <div class="d-flex align-items-center">
                        <label for="sort" class="me-2">Sort by:</label>
                        <select id="sort" class="form-select" onchange="window.location.href=this.value">
                            <?php 
                                $baseUrl = "shop.php?";
                                if (!empty($category)) $baseUrl .= "category=$category&";
                                if (!empty($search)) $baseUrl .= "search=$search&";
                            ?>
                            <option value="<?php echo $baseUrl; ?>sort=default" <?php echo $sort === 'default' ? 'selected' : ''; ?>>Default</option>
                            <option value="<?php echo $baseUrl; ?>sort=price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="<?php echo $baseUrl; ?>sort=price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="<?php echo $baseUrl; ?>sort=newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                            <option value="<?php echo $baseUrl; ?>sort=name_az" <?php echo $sort === 'name_az' ? 'selected' : ''; ?>>Name: A to Z</option>
                            <option value="<?php echo $baseUrl; ?>sort=name_za" <?php echo $sort === 'name_za' ? 'selected' : ''; ?>>Name: Z to A</option>
                        </select>
                    </div>
                </div>
                
                <?php if (empty($products)): ?>
                    <div class="alert alert-info">
                        No products found matching your criteria. Please try a different search or filter.
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($products as $product): ?>
                            <div class="col-md-4 col-sm-6 mb-4">
                                <div class="product-card">
                                    <img src="<?php echo SITE_URL . '/' . $product['image_url']; ?>" alt="<?php echo $product['name']; ?>" class="product-img">
                                    <div class="card-body">
                                        <span class="category-badge">
                                            <?php echo ucfirst($product['category']); ?>
                                        </span>
                                        <h5 class="card-title"><?php echo $product['name']; ?></h5>
                                        <div class="price">$<?php echo number_format($product['price'], 2); ?></div>
                                        <div class="d-grid gap-2">
                                            <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">View Details</a>
                                            <a href="add-to-cart.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-primary add-to-cart">
                                                <i class="fas fa-shopping-cart"></i> Add to Cart
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo $baseUrl; ?>sort=<?php echo $sort; ?>&page=<?php echo $page - 1; ?>">
                                            Previous
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="<?php echo $baseUrl; ?>sort=<?php echo $sort; ?>&page=<?php echo $i; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo $baseUrl; ?>sort=<?php echo $sort; ?>&page=<?php echo $page + 1; ?>">
                                            Next
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?> 