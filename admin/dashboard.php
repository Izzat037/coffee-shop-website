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

// Get total products count
$productSql = "SELECT COUNT(*) as total FROM products";
$productResult = $conn->query($productSql);
$totalProducts = $productResult->fetch_assoc()['total'];

// Get total users count
$userSql = "SELECT COUNT(*) as total FROM users WHERE role = 'customer'";
$userResult = $conn->query($userSql);
$totalUsers = $userResult->fetch_assoc()['total'];

// Get total orders count
$orderSql = "SELECT COUNT(*) as total FROM orders";
$orderResult = $conn->query($orderSql);
$totalOrders = $orderResult->fetch_assoc()['total'];

// Get total revenue
$revenueSql = "SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'";
$revenueResult = $conn->query($revenueSql);
$totalRevenue = $revenueResult->fetch_assoc()['total'] ?? 0;

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
        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" id="printReport">
            <i class="fas fa-download fa-sm text-white-50"></i> Generate Report
        </a>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Total Products Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Products</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalProducts; ?></div>
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
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Customers</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalUsers; ?></div>
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
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Orders</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalOrders; ?></div>
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
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Revenue</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">$<?php echo number_format($totalRevenue, 2); ?></div>
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
        <!-- Monthly Sales Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Monthly Sales Overview</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                            <div class="dropdown-header">Chart Options:</div>
                            <a class="dropdown-item" href="#" id="viewOrdersChart">View Orders</a>
                            <a class="dropdown-item" href="#" id="viewRevenueChart">View Revenue</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="#" id="downloadChart">Download Chart</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="monthlySalesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Low Stock Products -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Low Stock Products</h6>
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
                                                <span class="badge badge-<?php echo $product['stock'] <= 0 ? 'danger' : 'warning'; ?>">
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
                    <div class="mt-3 text-center">
                        <a href="<?php echo SITE_URL; ?>/admin/products.php?sort=stock_asc" class="btn btn-secondary btn-sm">
                            View All Low Stock Products
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Recent Orders</h6>
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
                                    <td><?php echo htmlspecialchars($order['email']); ?></td>
                                    <td><?php echo date("M d, Y", strtotime($order['created_at'])); ?></td>
                                    <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td>
                                        <span class="badge badge-<?php 
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
            <div class="mt-3 text-center">
                <a href="<?php echo SITE_URL; ?>/admin/orders.php" class="btn btn-primary btn-sm">
                    View All Orders
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Set new default font family and font color to mimic Bootstrap's default styling
Chart.defaults.global.defaultFontFamily = 'Nunito';
Chart.defaults.global.defaultFontColor = '#858796';

// Prepare data for chart
const months = [];
const orderCounts = [];
const revenues = [];

<?php foreach ($monthlyData as $data): ?>
    months.push('<?php echo date("M Y", strtotime($data['month'] . "-01")); ?>');
    orderCounts.push(<?php echo $data['order_count']; ?>);
    revenues.push(<?php echo $data['total_amount']; ?>);
<?php endforeach; ?>

// Function to number format
function number_format(number, decimals, dec_point, thousands_sep) {
    number = (number + '').replace(',', '').replace(' ', '');
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function(n, prec) {
            var k = Math.pow(10, prec);
            return '' + Math.round(n * k) / k;
        };
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
}

// Initialize chart
let currentChartType = 'revenue';
let myChart;

function initChart(type) {
    const ctx = document.getElementById("monthlySalesChart");
    
    // Destroy existing chart if it exists
    if (myChart) {
        myChart.destroy();
    }
    
    const data = type === 'revenue' ? revenues : orderCounts;
    const label = type === 'revenue' ? 'Revenue ($)' : 'Orders';
    const color = type === 'revenue' ? '#4e73df' : '#1cc88a';
    
    myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: label,
                lineTension: 0.3,
                backgroundColor: "rgba(78, 115, 223, 0.05)",
                borderColor: color,
                pointRadius: 3,
                pointBackgroundColor: color,
                pointBorderColor: color,
                pointHoverRadius: 3,
                pointHoverBackgroundColor: color,
                pointHoverBorderColor: color,
                pointHitRadius: 10,
                pointBorderWidth: 2,
                data: data,
            }],
        },
        options: {
            maintainAspectRatio: false,
            layout: {
                padding: {
                    left: 10,
                    right: 25,
                    top: 25,
                    bottom: 0
                }
            },
            scales: {
                xAxes: [{
                    gridLines: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        maxTicksLimit: 7
                    }
                }],
                yAxes: [{
                    ticks: {
                        maxTicksLimit: 5,
                        padding: 10,
                        callback: function(value, index, values) {
                            return type === 'revenue' ? '$' + number_format(value) : number_format(value);
                        }
                    },
                    gridLines: {
                        color: "rgb(234, 236, 244)",
                        zeroLineColor: "rgb(234, 236, 244)",
                        drawBorder: false,
                        borderDash: [2],
                        zeroLineBorderDash: [2]
                    }
                }],
            },
            legend: {
                display: false
            },
            tooltips: {
                backgroundColor: "rgb(255,255,255)",
                bodyFontColor: "#858796",
                titleMarginBottom: 10,
                titleFontColor: '#6e707e',
                titleFontSize: 14,
                borderColor: '#dddfeb',
                borderWidth: 1,
                xPadding: 15,
                yPadding: 15,
                displayColors: false,
                intersect: false,
                mode: 'index',
                caretPadding: 10,
                callbacks: {
                    label: function(tooltipItem, chart) {
                        var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
                        return datasetLabel + ': ' + (type === 'revenue' ? '$' : '') + number_format(tooltipItem.yLabel);
                    }
                }
            }
        }
    });
}

// Initialize with revenue chart by default
$(document).ready(function() {
    initChart('revenue');
    
    // Switch chart type
    $('#viewOrdersChart').click(function(e) {
        e.preventDefault();
        currentChartType = 'orders';
        initChart('orders');
    });
    
    $('#viewRevenueChart').click(function(e) {
        e.preventDefault();
        currentChartType = 'revenue';
        initChart('revenue');
    });
    
    // Print report
    $('#printReport').click(function(e) {
        e.preventDefault();
        window.print();
    });
});
</script>

<?php require_once 'admin_footer.php'; ?> 