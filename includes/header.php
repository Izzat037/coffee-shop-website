<?php require_once 'includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <!-- Favicon -->
    <link rel="icon" href="<?php echo SITE_URL; ?>/assets/images/favicon.ico" type="image/x-icon">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Rye&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark">
            <div class="container">
                <a class="navbar-brand" href="<?php echo SITE_URL; ?>">
                    <img src="<?php echo SITE_URL; ?>/assets/images/logo.png" alt="Tree Smoker Logo" height="50">
                    <span>Tree Smoker</span>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/shop.php">Shop</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                Categories
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/shop.php?category=beans">Coffee Beans</a></li>
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/shop.php?category=tools">Tools</a></li>
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/shop.php?category=merchandise">Merchandise</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/contact.php">Contact</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/cart.php">
                                <i class="fas fa-shopping-cart"></i> 
                                <?php if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                                    <span class="badge bg-success"><?php echo count($_SESSION['cart']); ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <?php if(isLoggedIn()): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-user"></i> <?php echo $_SESSION['user_name']; ?>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/profile.php">My Profile</a></li>
                                    <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/orders.php">My Orders</a></li>
                                    <?php if(isAdmin()): ?>
                                        <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin">Admin Panel</a></li>
                                    <?php endif; ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/logout.php">Logout</a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo SITE_URL; ?>/login.php">Login</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo SITE_URL; ?>/register.php">Register</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    <main class="container py-4">
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