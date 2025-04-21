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

// Get total products count
$productSql = "SELECT COUNT(*) as total FROM products";
$productResult = $conn->query($productSql);
$totalProducts = $productResult && $productResult->num_rows > 0 ? $productResult->fetch_assoc()['total'] : 0;

// Get total users count
$userSql = "SELECT COUNT(*) as total FROM users WHERE role = 'customer'";
$userResult = $conn->query($userSql);
$totalUsers = $userResult && $userResult->num_rows > 0 ? $userResult->fetch_assoc()['total'] : 0;

// Get total orders count
$orderSql = "SELECT COUNT(*) as total FROM orders";
$orderResult = $conn->query($orderSql);
$totalOrders = $orderResult && $orderResult->num_rows > 0 ? $orderResult->fetch_assoc()['total'] : 0;

// Get total revenue
$revenueSql = "SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'";
$revenueResult = $conn->query($revenueSql);
$totalRevenue = $revenueResult && $revenueResult->num_rows > 0 ? $revenueResult->fetch_assoc()['total'] : 0;
$totalRevenue = $totalRevenue ?: 0;

// Get low stock products (less than 5 items)
$lowStockSql = "SELECT * FROM products WHERE stock < 5 ORDER BY stock ASC LIMIT 5";
$lowStockResult = $conn->query($lowStockSql);
$lowStockProducts = [];

if ($lowStockResult && $lowStockResult->num_rows > 0) {
    while ($row = $lowStockResult->fetch_assoc()) {
        $lowStockProducts[] = $row;
    }
}

// Get recent orders
$recentOrdersSql = "SELECT o.*, u.email 
                   FROM orders o 
                   LEFT JOIN users u ON o.user_id = u.id 
                   ORDER BY o.created_at DESC 
                   LIMIT 5";
$recentOrdersResult = $conn->query($recentOrdersSql);
$recentOrders = [];

if ($recentOrdersResult && $recentOrdersResult->num_rows > 0) {
    while ($row = $recentOrdersResult->fetch_assoc()) {
        $recentOrders[] = $row;
    }
}

// Get monthly orders for chart
$monthlySql = "SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COUNT(*) as order_count,
                SUM(total_amount) as total_amount
              FROM orders
              WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
              GROUP BY month
              ORDER BY month ASC";
$monthlyResult = $conn->query($monthlySql);
$monthlyData = [];

if ($monthlyResult && $monthlyResult->num_rows > 0) {
    while ($row = $monthlyResult->fetch_assoc()) {
        $monthlyData[] = $row;
    }
}

// Close connection
$conn->close();
?>

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
        <div>
            <a href="<?php echo SITE_URL; ?>/admin/products.php" class="btn btn-primary btn-sm">
                <i class="fas fa-box fa-sm text-white-50 me-1"></i> View Products
            </a>
            <a href="<?php echo SITE_URL; ?>/admin/add-product.php" class="btn btn-success btn-sm ms-2">
                <i class="fas fa-plus fa-sm text-white-50 me-1"></i> Add Product
            </a>
        </div>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Total Products Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-5 border-primary h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                Total Products</div>
                            <div class="h5 mb-0 fw-bold text-gray-800"><?php echo $totalProducts; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-box fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Customers Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-5 border-success h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">
                                Total Customers</div>
                            <div class="h5 mb-0 fw-bold text-gray-800"><?php echo $totalUsers; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Orders Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-5 border-info h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-info text-uppercase mb-1">
                                Total Orders</div>
                            <div class="h5 mb-0 fw-bold text-gray-800"><?php echo $totalOrders; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Revenue Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-5 border-warning h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">
                                Total Revenue</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">$<?php echo number_format($totalRevenue, 2); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Recent Orders -->
        <div class="col-xl-8 col-lg-7">
            <div class="card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Orders</h6>
                    <a href="<?php echo SITE_URL; ?>/admin/orders.php" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recentOrders)): ?>
                        <p class="text-center">No recent orders found.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentOrders as $order): ?>
                                        <tr>
                                            <td>#<?php echo $order['id']; ?></td>
                                            <td><?php echo htmlspecialchars($order['email'] ?? 'Guest'); ?></td>
                                            <td><?php echo date("M d, Y", strtotime($order['created_at'])); ?></td>
                                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $order['status'] == 'completed' ? 'success' : 
                                                        ($order['status'] == 'processing' ? 'primary' : 
                                                            ($order['status'] == 'cancelled' ? 'danger' : 'secondary')); 
                                                ?>">
                                                    <?php echo ucfirst($order['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?php echo SITE_URL; ?>/admin/view-order.php?id=<?php echo $order['id']; ?>" class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Low Stock Products -->
        <div class="col-xl-4 col-lg-5">
            <div class="card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Low Stock Products</h6>
                    <a href="<?php echo SITE_URL; ?>/admin/products.php?sort=stock_asc" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($lowStockProducts)): ?>
                        <p class="text-center">No low stock products found.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Stock</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($lowStockProducts as $product): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $product['stock'] <= 0 ? 'danger' : 'warning'; ?>">
                                                    <?php echo $product['stock']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?php echo SITE_URL; ?>/admin/edit-product.php?id=<?php echo $product['id']; ?>" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Users Info Card -->
    <div class="card mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Admin Information</h6>
        </div>
        <div class="card-body">
            <p>You have 2 admin users set up in the system. You can add or manage administrators from the <a href="<?php echo SITE_URL; ?>/admin/setup.php">Admin Users</a> page.</p>
            
            <div class="alert alert-info mb-0">
                <i class="fas fa-info-circle me-2"></i> For help or support, please contact the system administrator.
            </div>
        </div>
    </div>
</div>

<?php require_once 'admin_footer.php'; ?> 