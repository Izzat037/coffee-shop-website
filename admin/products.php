<?php
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
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $productId = (int)$_GET['delete'];
    
    // Get product image before deletion to remove the file
    $sql = "SELECT image FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        
        // Delete the product from database
        $deleteSql = "DELETE FROM products WHERE id = ?";
        $deleteStmt = $conn->prepare($deleteSql);
        $deleteStmt->bind_param("i", $productId);
        
        if ($deleteStmt->execute()) {
            // Delete image file if exists
            if (!empty($product['image'])) {
                $imagePath = "../uploads/products/" . $product['image'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            $_SESSION['success_message'] = "Product deleted successfully!";
        } else {
            $_SESSION['error_message'] = "Error deleting product: " . $conn->error;
        }
    } else {
        $_SESSION['error_message'] = "Product not found.";
    }
    
    // Redirect to avoid resubmission on refresh
    redirect(SITE_URL . '/admin/products.php');
}

// Set up pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$recordsPerPage = 10;
$offset = ($page - 1) * $recordsPerPage;

// Get the total number of products
$countSql = "SELECT COUNT(*) as total FROM products";
$countResult = $conn->query($countSql);
$totalRecords = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);

// Get search and filter values
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$featured = isset($_GET['featured']) ? $_GET['featured'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';

// Build the query
$sql = "SELECT * FROM products WHERE 1=1";
$params = [];
$types = "";

// Add search condition if search term is provided
if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR description LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "ss";
}

// Add category filter if selected
if (!empty($category)) {
    $sql .= " AND category = ?";
    $params[] = $category;
    $types .= "s";
}

// Add featured filter if selected
if ($featured !== '') {
    $sql .= " AND is_featured = ?";
    $params[] = $featured;
    $types .= "i";
}

// Add sorting
switch ($sort) {
    case 'name_desc':
        $sql .= " ORDER BY name DESC";
        break;
    case 'price_asc':
        $sql .= " ORDER BY price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY price DESC";
        break;
    case 'stock_asc':
        $sql .= " ORDER BY stock ASC";
        break;
    case 'stock_desc':
        $sql .= " ORDER BY stock DESC";
        break;
    case 'newest':
        $sql .= " ORDER BY id DESC";
        break;
    case 'oldest':
        $sql .= " ORDER BY id ASC";
        break;
    default:
        $sql .= " ORDER BY name ASC";
}

// Add pagination limit
$sql .= " LIMIT ?, ?";
$params[] = $offset;
$params[] = $recordsPerPage;
$types .= "ii";

// Prepare and execute the query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Get all categories for filter dropdown
$categorySql = "SELECT DISTINCT category FROM products ORDER BY category";
$categoryResult = $conn->query($categorySql);
$categories = [];

if ($categoryResult && $categoryResult->num_rows > 0) {
    while ($row = $categoryResult->fetch_assoc()) {
        $categories[] = $row['category'];
    }
}

$conn->close();
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Product Management</h1>
        <a href="<?php echo SITE_URL; ?>/admin/add-product.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Add New Product
        </a>
    </div>

    <!-- Display success message if any -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?php 
                echo $_SESSION['success_message']; 
                unset($_SESSION['success_message']);
            ?>
        </div>
    <?php endif; ?>

    <!-- Display error message if any -->
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger">
            <?php 
                echo $_SESSION['error_message']; 
                unset($_SESSION['error_message']);
            ?>
        </div>
    <?php endif; ?>

    <!-- Product List Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">All Products</h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                    <div class="dropdown-header">Export Options:</div>
                    <a class="dropdown-item" href="#"><i class="fas fa-file-csv fa-sm fa-fw mr-2 text-gray-400"></i> Export CSV</a>
                    <a class="dropdown-item" href="#"><i class="fas fa-file-excel fa-sm fa-fw mr-2 text-gray-400"></i> Export Excel</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#"><i class="fas fa-print fa-sm fa-fw mr-2 text-gray-400"></i> Print List</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Search and Filters -->
            <form method="GET" action="<?php echo SITE_URL; ?>/admin/products.php" class="mb-4">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <input type="text" class="form-control" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-2 mb-2">
                        <select class="form-control" name="category">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat; ?>" <?php echo ($category === $cat) ? 'selected' : ''; ?>>
                                    <?php echo ucfirst($cat); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <select class="form-control" name="featured">
                            <option value="">All Products</option>
                            <option value="1" <?php echo ($featured === '1') ? 'selected' : ''; ?>>Featured Only</option>
                            <option value="0" <?php echo ($featured === '0') ? 'selected' : ''; ?>>Not Featured</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <select class="form-control" name="sort">
                            <option value="name_asc" <?php echo ($sort === 'name_asc') ? 'selected' : ''; ?>>Name (A-Z)</option>
                            <option value="name_desc" <?php echo ($sort === 'name_desc') ? 'selected' : ''; ?>>Name (Z-A)</option>
                            <option value="price_asc" <?php echo ($sort === 'price_asc') ? 'selected' : ''; ?>>Price (Low-High)</option>
                            <option value="price_desc" <?php echo ($sort === 'price_desc') ? 'selected' : ''; ?>>Price (High-Low)</option>
                            <option value="stock_asc" <?php echo ($sort === 'stock_asc') ? 'selected' : ''; ?>>Stock (Low-High)</option>
                            <option value="stock_desc" <?php echo ($sort === 'stock_desc') ? 'selected' : ''; ?>>Stock (High-Low)</option>
                            <option value="newest" <?php echo ($sort === 'newest') ? 'selected' : ''; ?>>Newest First</option>
                            <option value="oldest" <?php echo ($sort === 'oldest') ? 'selected' : ''; ?>>Oldest First</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="<?php echo SITE_URL; ?>/admin/products.php" class="btn btn-secondary">Reset</a>
                    </div>
                </div>
            </form>

            <!-- Products Table -->
            <div class="table-responsive">
                <table class="table table-bordered" id="productsTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Featured</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($product = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $product['id']; ?></td>
                                    <td>
                                        <?php if (!empty($product['image'])): ?>
                                            <img src="<?php echo SITE_URL; ?>/uploads/products/<?php echo $product['image']; ?>" 
                                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                                 class="img-thumbnail" style="max-height: 50px;">
                                        <?php else: ?>
                                            <span class="text-muted">No image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td><?php echo htmlspecialchars($product['category']); ?></td>
                                    <td>$<?php echo number_format($product['price'], 2); ?></td>
                                    <td><?php echo $product['stock']; ?></td>
                                    <td>
                                        <?php if ($product['is_featured'] == 1): ?>
                                            <span class="badge badge-success">Featured</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">No</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo SITE_URL; ?>/admin/edit-product.php?id=<?php echo $product['id']; ?>" 
                                           class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="#" class="btn btn-danger btn-sm delete-product" 
                                           data-id="<?php echo $product['id']; ?>" 
                                           data-name="<?php echo htmlspecialchars($product['name']); ?>">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">No products found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo SITE_URL; ?>/admin/products.php?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&featured=<?php echo urlencode($featured); ?>&sort=<?php echo urlencode($sort); ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                <a class="page-link" href="<?php echo SITE_URL; ?>/admin/products.php?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&featured=<?php echo urlencode($featured); ?>&sort=<?php echo urlencode($sort); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo SITE_URL; ?>/admin/products.php?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&featured=<?php echo urlencode($featured); ?>&sort=<?php echo urlencode($sort); ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete the product: <strong id="product-name"></strong>?
                <p class="text-danger mt-2">This action cannot be undone!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <a href="#" class="btn btn-danger" id="confirm-delete">Delete</a>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Delete product confirmation
        $('.delete-product').click(function(e) {
            e.preventDefault();
            
            const productId = $(this).data('id');
            const productName = $(this).data('name');
            
            $('#product-name').text(productName);
            $('#confirm-delete').attr('href', '<?php echo SITE_URL; ?>/admin/products.php?delete=' + productId);
            
            $('#deleteModal').modal('show');
        });
    });
</script>

<?php require_once 'admin_footer.php'; ?> 