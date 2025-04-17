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

// Check if product ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "Invalid product ID.";
    redirect(SITE_URL . '/admin/products.php');
}

$productId = (int)$_GET['id'];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    $category = $_POST['category'] ?? '';
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    // Validate form data
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Product name is required";
    }
    
    if (empty($description)) {
        $errors[] = "Product description is required";
    }
    
    if (!is_numeric($price) || $price <= 0) {
        $errors[] = "Product price must be a positive number";
    }
    
    if (!is_numeric($stock) || $stock < 0) {
        $errors[] = "Product stock must be a non-negative number";
    }
    
    if (empty($category)) {
        $errors[] = "Product category is required";
    }
    
    // Get current product data to check if image exists
    $currentProductSql = "SELECT image FROM products WHERE id = ?";
    $stmt = $conn->prepare($currentProductSql);
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $currentProduct = $result->fetch_assoc();
    $currentImage = $currentProduct['image'] ?? '';
    
    // Handle image upload
    $image = $currentImage; // Keep current image by default
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = $_FILES['image']['type'];
        
        if (!in_array($fileType, $allowedTypes)) {
            $errors[] = "Invalid image format. Only JPEG, PNG, and GIF are allowed.";
        } else {
            // Create uploads directory if it doesn't exist
            $uploadDir = "../uploads/products/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Generate unique filename
            $image = time() . '_' . basename($_FILES['image']['name']);
            $targetFile = $uploadDir . $image;
            
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $errors[] = "Failed to upload image. Please try again.";
                $image = $currentImage; // Keep current image if upload fails
            } else {
                // Delete old image if exists
                if (!empty($currentImage) && file_exists($uploadDir . $currentImage)) {
                    unlink($uploadDir . $currentImage);
                }
            }
        }
    }
    
    // Handle image removal
    if (isset($_POST['remove_image']) && $_POST['remove_image'] == 1) {
        // Delete old image if exists
        if (!empty($currentImage)) {
            $uploadDir = "../uploads/products/";
            if (file_exists($uploadDir . $currentImage)) {
                unlink($uploadDir . $currentImage);
            }
            $image = ''; // Clear image
        }
    }
    
    // If no errors, update product in database
    if (empty($errors)) {
        $sql = "UPDATE products SET 
                name = ?, 
                description = ?, 
                price = ?, 
                stock = ?, 
                category = ?, 
                image = ?, 
                is_featured = ? 
                WHERE id = ?";
                
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssddssii", $name, $description, $price, $stock, $category, $image, $is_featured, $productId);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Product updated successfully!";
            redirect(SITE_URL . '/admin/products.php');
        } else {
            $_SESSION['error_message'] = "Error updating product: " . $conn->error;
        }
    } else {
        $_SESSION['error_message'] = implode("<br>", $errors);
    }
}

// Get product data
$sql = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = "Product not found.";
    redirect(SITE_URL . '/admin/products.php');
}

$product = $result->fetch_assoc();

// Get existing categories for dropdown
$categorySql = "SELECT DISTINCT category FROM products ORDER BY category";
$categoryResult = $conn->query($categorySql);
$categories = [];

if ($categoryResult && $categoryResult->num_rows > 0) {
    while ($row = $categoryResult->fetch_assoc()) {
        $categories[] = $row['category'];
    }
}

// Add current product category if not in the list
if (!in_array($product['category'], $categories)) {
    $categories[] = $product['category'];
}

$conn->close();
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Product</h1>
        <a href="<?php echo SITE_URL; ?>/admin/products.php" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Products
        </a>
    </div>

    <!-- Display error message if any -->
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger">
            <?php 
                echo $_SESSION['error_message']; 
                unset($_SESSION['error_message']);
            ?>
        </div>
    <?php endif; ?>

    <!-- Edit Product Form -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Product Information</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="<?php echo SITE_URL; ?>/admin/edit-product.php?id=<?php echo $productId; ?>" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name">Product Name *</label>
                        <input type="text" class="form-control" id="name" name="name" required 
                               value="<?php echo htmlspecialchars($product['name']); ?>">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="category">Category *</label>
                        <div class="input-group">
                            <select class="form-control" id="category" name="category" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat; ?>" <?php echo ($product['category'] === $cat) ? 'selected' : ''; ?>>
                                        <?php echo ucfirst($cat); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" id="newCategoryBtn">
                                    New
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="price">Price ($) *</label>
                        <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required
                               value="<?php echo htmlspecialchars($product['price']); ?>">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="stock">Stock *</label>
                        <input type="number" class="form-control" id="stock" name="stock" min="0" required
                               value="<?php echo htmlspecialchars($product['stock']); ?>">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="description">Description *</label>
                        <textarea class="form-control" id="description" name="description" rows="5" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="image">Product Image</label>
                        <?php if (!empty($product['image'])): ?>
                            <div class="mb-2">
                                <img src="<?php echo SITE_URL; ?>/uploads/products/<?php echo $product['image']; ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                     class="img-thumbnail" style="max-height: 150px;">
                                <div class="form-check mt-2">
                                    <input type="checkbox" class="form-check-input" id="remove_image" name="remove_image" value="1">
                                    <label class="form-check-label" for="remove_image">Remove current image</label>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="image" name="image">
                            <label class="custom-file-label" for="image">Choose new image</label>
                        </div>
                        <small class="form-text text-muted">Recommended size: 800x800 pixels. Max file size: 2MB.</small>
                    </div>

                    <div class="col-md-12 mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="is_featured" name="is_featured" 
                                  <?php echo ($product['is_featured'] == 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_featured">Featured Product</label>
                        </div>
                        <small class="form-text text-muted">Featured products appear on the homepage.</small>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Update Product</button>
                <a href="<?php echo SITE_URL; ?>/admin/products.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

<!-- New Category Modal -->
<div class="modal fade" id="newCategoryModal" tabindex="-1" role="dialog" aria-labelledby="newCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newCategoryModalLabel">Add New Category</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="newCategory">Category Name</label>
                    <input type="text" class="form-control" id="newCategory">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveCategory">Save</button>
            </div>
        </div>
    </div>
</div>

<script>
    // New Category Modal
    $(document).ready(function() {
        $('#newCategoryBtn').click(function() {
            $('#newCategoryModal').modal('show');
        });
        
        $('#saveCategory').click(function() {
            const newCategory = $('#newCategory').val().trim();
            
            if (newCategory) {
                // Add new option to select
                $('#category').append(new Option(newCategory, newCategory, true, true));
                $('#newCategoryModal').modal('hide');
                $('#newCategory').val('');
            }
        });
        
        // Update filename on file select
        $('.custom-file-input').on('change', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').html(fileName);
        });
        
        // Handle remove image checkbox
        $('#remove_image').change(function() {
            if ($(this).is(':checked')) {
                $('#image').prop('disabled', true);
            } else {
                $('#image').prop('disabled', false);
            }
        });
    });
</script>

<?php require_once 'admin_footer.php'; ?> 