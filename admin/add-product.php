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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    $name = trim($_POST['name']);
    $price = trim($_POST['price']);
    $stock = trim($_POST['stock']);
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    $errors = [];
    
    // Validate name
    if (empty($name)) {
        $errors[] = "Product name is required.";
    }
    
    // Validate price
    if (empty($price) || !is_numeric($price) || $price <= 0) {
        $errors[] = "Valid price is required.";
    }
    
    // Validate stock
    if (empty($stock) || !is_numeric($stock) || $stock < 0) {
        $errors[] = "Valid stock quantity is required.";
    }
    
    // Validate category
    if (empty($category)) {
        $errors[] = "Category is required.";
    }
    
    // Handle image upload
    $imageName = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        // Validate file type
        if (!in_array($_FILES['image']['type'], $allowedTypes)) {
            $errors[] = "Invalid image format. Allowed formats: JPG, PNG, GIF, WEBP.";
        }
        
        // Validate file size
        if ($_FILES['image']['size'] > $maxSize) {
            $errors[] = "Image size exceeds 5MB limit.";
        }
        
        if (empty($errors)) {
            // Create uploads directory if it doesn't exist
            $uploadDir = "../uploads/products/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Generate unique filename
            $imageName = uniqid() . '_' . basename($_FILES['image']['name']);
            $targetFile = $uploadDir . $imageName;
            
            // Upload file
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $errors[] = "Failed to upload image.";
                $imageName = '';
            }
        }
    }
    
    // If no errors, insert the product
    if (empty($errors)) {
        $sql = "INSERT INTO products (name, description, price, stock, category, image, is_featured) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdssi", $name, $description, $price, $stock, $category, $imageName, $is_featured);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Product added successfully!";
            redirect(SITE_URL . '/admin/products.php');
        } else {
            $errors[] = "Error adding product: " . $conn->error;
            
            // Delete uploaded image if insert fails
            if (!empty($imageName)) {
                unlink("../uploads/products/" . $imageName);
            }
        }
    }
}

// Get all existing categories for dropdown
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
        <h1 class="h3 mb-0 text-gray-800">Add New Product</h1>
        <a href="<?php echo SITE_URL; ?>/admin/products.php" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Products
        </a>
    </div>

    <!-- Display errors if any -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Product Form Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Product Details</h6>
        </div>
        <div class="card-body">
            <form action="<?php echo SITE_URL; ?>/admin/add-product.php" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Product Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required
                                   value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="category">Category <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <select class="form-control" id="category" name="category" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat; ?>" <?php echo (isset($category) && $category === $cat) ? 'selected' : ''; ?>>
                                            <?php echo ucfirst($cat); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" data-toggle="modal" data-target="#newCategoryModal">
                                        <i class="fas fa-plus"></i> New
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="price">Price ($) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0.01" class="form-control" id="price" name="price" required
                                           value="<?php echo isset($price) ? htmlspecialchars($price) : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="stock">Stock Quantity <span class="text-danger">*</span></label>
                                    <input type="number" min="0" class="form-control" id="stock" name="stock" required
                                           value="<?php echo isset($stock) ? htmlspecialchars($stock) : '0'; ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="is_featured" name="is_featured" 
                                       <?php echo (isset($is_featured) && $is_featured == 1) ? 'checked' : ''; ?>>
                                <label class="custom-control-label" for="is_featured">Featured Product</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="5"><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="image">Product Image</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="image" name="image" accept="image/*" onchange="updateFileName()">
                                <label class="custom-file-label" for="image" id="image-label">Choose file</label>
                            </div>
                            <small class="form-text text-muted">Max file size: 5MB. Allowed formats: JPG, PNG, GIF, WEBP.</small>
                        </div>
                        
                        <div class="mt-3" id="image-preview-container" style="display: none;">
                            <p>Image Preview:</p>
                            <img id="image-preview" src="#" alt="Preview" class="img-thumbnail" style="max-height: 200px;">
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Add Product</button>
                    <a href="<?php echo SITE_URL; ?>/admin/products.php" class="btn btn-secondary">Cancel</a>
                </div>
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
                    <label for="new_category">Category Name</label>
                    <input type="text" class="form-control" id="new_category" placeholder="Enter new category name">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="add_category">Add Category</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Image preview functionality
    function updateFileName() {
        const input = document.getElementById('image');
        const label = document.getElementById('image-label');
        const preview = document.getElementById('image-preview');
        const previewContainer = document.getElementById('image-preview-container');
        
        if (input.files && input.files[0]) {
            label.textContent = input.files[0].name;
            
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                previewContainer.style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            label.textContent = 'Choose file';
            previewContainer.style.display = 'none';
        }
    }
    
    // Add new category
    $(document).ready(function() {
        $('#add_category').click(function() {
            const newCategory = $('#new_category').val().trim();
            
            if (newCategory) {
                // Add the new category to the dropdown
                const option = new Option(newCategory, newCategory, true, true);
                $('#category').append(option).trigger('change');
                
                // Close the modal
                $('#newCategoryModal').modal('hide');
                
                // Clear the input field
                $('#new_category').val('');
            }
        });
    });
</script>

<?php require_once 'admin_footer.php'; ?> 