<?php
require_once 'includes/config.php';

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle GET request (quick add from product listing)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $product_id = (int)$_GET['id'];
    
    // Check if product exists
    $conn = connectDB();
    $sql = "SELECT id, name, price, stock FROM products WHERE id = $product_id";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $product = $result->fetch_assoc();
        
        // Check if product is in stock
        if ($product['stock'] <= 0) {
            $_SESSION['error_message'] = "Sorry, this product is out of stock.";
            $conn->close();
            redirect(SITE_URL . "/shop.php");
        }
        
        // Check if product is already in cart
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $product_id) {
                $item['quantity'] += 1;
                $found = true;
                break;
            }
        }
        
        // If not in cart, add it
        if (!$found) {
            $_SESSION['cart'][] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => 1
            ];
        }
        
        $_SESSION['success_message'] = "Product added to cart.";
        $conn->close();
        redirect(SITE_URL . "/cart.php");
    } else {
        $_SESSION['error_message'] = "Product not found.";
        $conn->close();
        redirect(SITE_URL . "/shop.php");
    }
}

// Handle POST request (add from product detail page)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    
    // Validate quantity
    if ($quantity <= 0) {
        $_SESSION['error_message'] = "Invalid quantity.";
        redirect(SITE_URL . "/product.php?id=$product_id");
    }
    
    // Check if product exists
    $conn = connectDB();
    $sql = "SELECT id, name, price, stock FROM products WHERE id = $product_id";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $product = $result->fetch_assoc();
        
        // Check if requested quantity is available
        if ($product['stock'] < $quantity) {
            $_SESSION['error_message'] = "Sorry, only {$product['stock']} items available in stock.";
            $conn->close();
            redirect(SITE_URL . "/product.php?id=$product_id");
        }
        
        // Check if product is already in cart
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $product_id) {
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }
        
        // If not in cart, add it
        if (!$found) {
            $_SESSION['cart'][] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => $quantity
            ];
        }
        
        $_SESSION['success_message'] = "Product added to cart.";
        $conn->close();
        redirect(SITE_URL . "/cart.php");
    } else {
        $_SESSION['error_message'] = "Product not found.";
        $conn->close();
        redirect(SITE_URL . "/shop.php");
    }
}

// If we get here, something went wrong
$_SESSION['error_message'] = "Invalid request.";
redirect(SITE_URL . "/shop.php");
?> 