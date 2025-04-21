<?php
require_once '../includes/config.php';

// Check if admin is logged in
if (!isAdmin()) {
    $_SESSION['error_message'] = "You must be logged in as an admin to access this page.";
    redirect(SITE_URL . '/admin/index.php');
}

// Get admin info
$admin_username = $_SESSION['admin_username'] ?? ($_SESSION['user_name'] ?? 'Admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Tree Smoker</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fc;
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .sidebar {
            min-height: 100vh;
            background-color: #4e73df;
            background-image: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
            background-size: cover;
            position: fixed;
            z-index: 1;
            width: 16.66667%;
        }
        
        .sidebar-brand {
            height: 4.375rem;
            padding: 1rem 0;
            text-decoration: none;
            font-size: 1.2rem;
            font-weight: 800;
            color: white;
            text-align: center;
        }
        
        .sidebar hr {
            margin: 0 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.15);
        }
        
        .sidebar .nav-item {
            margin-bottom: 0.25rem;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1rem;
            font-weight: 700;
            font-size: 0.9rem;
        }
        
        .sidebar .nav-link:hover, 
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar .nav-link i {
            margin-right: 0.5rem;
            width: 1.25rem;
            text-align: center;
        }
        
        .content-wrapper {
            margin-left: 16.66667%;
            width: 83.33333%;
        }
        
        .navbar {
            background-color: white;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .navbar-light .navbar-brand {
            color: #4e73df;
            font-weight: 700;
        }
        
        .card {
            margin-bottom: 1.5rem;
            border: none;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
            padding: 0.75rem 1.25rem;
        }
        
        .card-header h6 {
            margin-bottom: 0;
            color: #4e73df;
            font-weight: 700;
        }
        
        .card-body {
            padding: 1.25rem;
        }
        
        .btn-primary {
            background-color: #4e73df;
            border-color: #4e73df;
        }
        
        .btn-primary:hover {
            background-color: #2e59d9;
            border-color: #2653d4;
        }
        
        .text-primary {
            color: #4e73df !important;
        }
        
        .bg-primary {
            background-color: #4e73df !important;
        }
        
        .bg-success {
            background-color: #1cc88a !important;
        }
        
        .bg-info {
            background-color: #36b9cc !important;
        }
        
        .bg-warning {
            background-color: #f6c23e !important;
        }
        
        .table thead th {
            font-weight: 700;
            color: #5a5c69;
        }
        
        .topbar-divider {
            width: 0;
            border-right: 1px solid #e3e6f0;
            height: 2rem;
            margin: auto 1rem;
        }
        
        .user-dropdown {
            cursor: pointer;
        }
        
        .content-area {
            padding: 1.5rem;
            min-height: calc(100vh - 60px);
        }
        
        @media (max-width: 991.98px) {
            .sidebar {
                width: 100%;
                position: relative;
                min-height: auto;
            }
            
            .content-wrapper {
                margin-left: 0;
                width: 100%;
            }
        }
    </style>
</head>
<body id="page-top">
    <div id="wrapper">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-brand d-flex align-items-center justify-content-center">
                <i class="fas fa-coffee me-2"></i>
                <span>TreeSmoker</span>
            </div>
            <hr class="sidebar-divider">
            <div class="nav flex-column">
                <div class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/admin/dashboard.php">
                        <i class="fas fa-fw fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/admin/products.php">
                        <i class="fas fa-fw fa-box"></i>
                        <span>Products</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['add-product.php', 'edit-product.php']) ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/admin/add-product.php">
                        <i class="fas fa-fw fa-plus-circle"></i>
                        <span>Add Product</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>/admin/orders.php">
                        <i class="fas fa-fw fa-shopping-cart"></i>
                        <span>Orders</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>/admin/customers.php">
                        <i class="fas fa-fw fa-users"></i>
                        <span>Customers</span>
                    </a>
                </div>
                <hr class="sidebar-divider">
                <div class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'setup.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/admin/setup.php">
                        <i class="fas fa-fw fa-user-shield"></i>
                        <span>Admin Users</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>/admin/settings.php">
                        <i class="fas fa-fw fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </div>
                <hr class="sidebar-divider">
                <div class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>/" target="_blank">
                        <i class="fas fa-fw fa-home"></i>
                        <span>View Site</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                        <i class="fas fa-fw fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <!-- Topbar -->
            <nav class="navbar navbar-expand navbar-light bg-white mb-4 shadow">
                <div class="container-fluid">
                    <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target=".sidebar">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="navbar-nav ms-auto">
                            <div class="topbar-divider d-none d-sm-block"></div>
                            <li class="nav-item dropdown no-arrow">
                                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span class="d-none d-lg-inline text-gray-600 small me-2"><?php echo htmlspecialchars($admin_username); ?></span>
                                    <i class="fas fa-user-circle fa-fw"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userDropdown">
                                    <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/profile.php"><i class="fas fa-user fa-sm fa-fw me-2 text-gray-400"></i> Profile</a></li>
                                    <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/settings.php"><i class="fas fa-cogs fa-sm fa-fw me-2 text-gray-400"></i> Settings</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal"><i class="fas fa-sign-out-alt fa-sm fa-fw me-2 text-gray-400"></i> Logout</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
            
            <!-- Main Content -->
            <div class="container-fluid content-area">
                <?php if(isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php echo $_SESSION['success_message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>

                <?php if(isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?php echo $_SESSION['error_message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>
                
                <div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 