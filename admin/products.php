<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../includes/config.php';
require_once 'admin_header.php';

// Check if user is admin
if (!isAdmin()) {
    $_SESSION['error_message'] = "You do not have permission to access the admin panel.";
    redirect(SITE_URL . '/admin/index.php');
}

// Initialize database connection
$conn = connectDB();

// Handle product deletion
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $product_id = $_GET['id'];
    
    // Get product image before deleting
    $imgSql = "SELECT image FROM products WHERE id = ?";
    $stmt = $conn->prepare($imgSql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $image = $row['image'];
        
        // Delete the product
        $deleteSql = "DELETE FROM products WHERE id = ?";
        $stmt = $conn->prepare($deleteSql);
        $stmt->bind_param("i", $product_id);
        
        if ($stmt->execute()) {
            // Delete product image if it exists and is not the default image
            if ($image && $image != 'default.jpg' && file_exists("../uploads/products/" . $image)) {
                unlink("../uploads/products/" . $image);
            }
            
            $_SESSION['success_message'] = "Product deleted successfully!";
        } else {
            $_SESSION['error_message'] = "Error deleting product: " . $conn->error;
        }
    } else {
        $_SESSION['error_message'] = "Product not found.";
    }
    
    // Redirect to remove the action from URL (to prevent accidental refreshes)
    redirect(SITE_URL . '/admin/products.php');
}

// Determine sort order
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'id_desc';
$sortColumn = 'id';
$sortDirection = 'DESC';

switch ($sort) {
    case 'name_asc':
        $sortColumn = 'name';
        $sortDirection = 'ASC';
        break;
    case 'name_desc':
        $sortColumn = 'name';
        $sortDirection = 'DESC';
        break;
    case 'price_asc':
        $sortColumn = 'price';
        $sortDirection = 'ASC';
        break;
    case 'price_desc':
        $sortColumn = 'price';
        $sortDirection = 'DESC';
        break;
    case 'stock_asc':
        $sortColumn = 'stock';
        $sortDirection = 'ASC';
        break;
    case 'stock_desc':
        $sortColumn = 'stock';
        $sortDirection = 'DESC';
        break;
    case 'id_asc':
        $sortColumn = 'id';
        $sortDirection = 'ASC';
        break;
    default:
        $sortColumn = 'id';
        $sortDirection = 'DESC';
}

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$searchCondition = '';
$params = [];
$types = '';

if (!empty($search)) {
    $searchCondition = "WHERE name LIKE ? OR description LIKE ?";
    $searchParam = "%" . $search . "%";
    $params = [$searchParam, $searchParam];
    $types = "ss";
}

// Filter by category
$category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;
if ($category_id > 0) {
    $searchCondition = $searchCondition ? $searchCondition . " AND category_id = ?" : "WHERE category_id = ?";
    $params[] = $category_id;
    $types .= "i";
}

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Count total products for pagination
$countSql = "SELECT COUNT(*) as total FROM products " . $searchCondition;
if (!empty($params)) {
    $stmt = $conn->prepare($countSql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $countResult = $stmt->get_result();
    $totalProducts = $countResult->fetch_assoc()['total'];
} else {
    $countResult = $conn->query($countSql);
    $totalProducts = $countResult->fetch_assoc()['total'];
}

$totalPages = ceil($totalProducts / $perPage);

// Get products with pagination and sorting
$sql = "SELECT p.*, c.name as category_name 
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        $searchCondition
        ORDER BY $sortColumn $sortDirection
        LIMIT ?, ?";

$stmt = $conn->prepare($sql);
$limitTypes = $types . "ii";
$limitParams = array_merge($params, [$offset, $perPage]);
$stmt->bind_param($limitTypes, ...$limitParams);
$stmt->execute();
$result = $stmt->get_result();

// Get all categories for filter dropdown
$categoriesSql = "SELECT * FROM categories ORDER BY name ASC";
$categoriesResult = $conn->query($categoriesSql);
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Products Management</h1>
    <a href="<?php echo SITE_URL; ?>/admin/add-product.php" class="btn btn-primary">
        <i class="fas fa-plus fa-sm text-white-50 me-1"></i> Add New Product
    </a>
</div>

<!-- Search and Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?php echo SITE_URL; ?>/admin/products.php" class="row align-items-end">
            <div class="col-md-4 mb-3">
                <label for="search" class="form-label">Search Products</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="Search by name or description" value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-3 mb-3">
                <label for="category" class="form-label">Filter by Category</label>
                <select name="category" id="category" class="form-select">
                    <option value="">All Categories</option>
                    <?php if ($categoriesResult && $categoriesResult->num_rows > 0): ?>
                        <?php while ($category = $categoriesResult->fetch_assoc()): ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo ($category_id == $category['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label for="sort" class="form-label">Sort By</label>
                <select name="sort" id="sort" class="form-select">
                    <option value="id_desc" <?php echo ($sort == 'id_desc') ? 'selected' : ''; ?>>Newest First</option>
                    <option value="id_asc" <?php echo ($sort == 'id_asc') ? 'selected' : ''; ?>>Oldest First</option>
                    <option value="name_asc" <?php echo ($sort == 'name_asc') ? 'selected' : ''; ?>>Name (A-Z)</option>
                    <option value="name_desc" <?php echo ($sort == 'name_desc') ? 'selected' : ''; ?>>Name (Z-A)</option>
                    <option value="price_asc" <?php echo ($sort == 'price_asc') ? 'selected' : ''; ?>>Price (Low to High)</option>
                    <option value="price_desc" <?php echo ($sort == 'price_desc') ? 'selected' : ''; ?>>Price (High to Low)</option>
                    <option value="stock_asc" <?php echo ($sort == 'stock_asc') ? 'selected' : ''; ?>>Stock (Low to High)</option>
                    <option value="stock_desc" <?php echo ($sort == 'stock_desc') ? 'selected' : ''; ?>>Stock (High to Low)</option>
                </select>
            </div>
            <div class="col-md-2 mb-3">
                <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
            </div>
        </form>
    </div>
</div>

<!-- Products Table -->
<div class="card mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Products List</h6>
    </div>
    <div class="card-body">
        <?php if ($result && $result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($product = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $product['id']; ?></td>
                                <td>
                                    <img src="<?php echo SITE_URL; ?>/uploads/products/<?php echo $product['image'] ?: 'default.jpg'; ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                         class="img-thumbnail" 
                                         style="width: 50px; height: 50px; object-fit: cover;">
                                </td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></td>
                                <td>$<?php echo number_format($product['price'], 2); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $product['stock'] <= 0 ? 'danger' : ($product['stock'] < 5 ? 'warning' : 'success'); ?>">
                                        <?php echo $product['stock']; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?php echo SITE_URL; ?>/admin/edit-product.php?id=<?php echo $product['id']; ?>" 
                                       class="btn btn-primary btn-sm mb-1">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="<?php echo SITE_URL; ?>/product.php?id=<?php echo $product['id']; ?>" 
                                       class="btn btn-info btn-sm mb-1" 
                                       target="_blank">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="<?php echo SITE_URL; ?>/admin/products.php?action=delete&id=<?php echo $product['id']; ?>" 
                                       class="btn btn-danger btn-sm mb-1 delete-product">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center mt-4">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo SITE_URL; ?>/admin/products.php?page=<?php echo ($page - 1); ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_id; ?>&sort=<?php echo $sort; ?>">
                                    Previous
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                <a class="page-link" href="<?php echo SITE_URL; ?>/admin/products.php?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_id; ?>&sort=<?php echo $sort; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo SITE_URL; ?>/admin/products.php?page=<?php echo ($page + 1); ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_id; ?>&sort=<?php echo $sort; ?>">
                                    Next
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="alert alert-info text-center">
                No products found. <?php echo !empty($search) || $category_id > 0 ? 'Try a different search or filter.' : ''; ?>
            </div>
            <div class="text-center mt-3">
                <a href="<?php echo SITE_URL; ?>/admin/add-product.php" class="btn btn-primary">
                    <i class="fas fa-plus fa-sm text-white-50 me-1"></i> Add Your First Product
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Confirm before deleting a product
    const deleteLinks = document.querySelectorAll('.delete-product');
    
    deleteLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });
});
</script>

<?php require_once 'admin_footer.php'; ?> 