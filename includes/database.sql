-- Tree Smoker E-Commerce Database

-- Create database
CREATE DATABASE IF NOT EXISTS tree_smoker;
USE tree_smoker;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    address TEXT,
    phone VARCHAR(20),
    role ENUM('customer', 'admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category ENUM('beans', 'tools', 'merchandise') NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    description TEXT,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    shipping_address TEXT NOT NULL,
    payment_method VARCHAR(50) DEFAULT 'COD',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- Reviews table
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    product_id INT,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    is_approved BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- Admin table (optional, can use users table instead)
CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin
INSERT INTO admin (username, password) VALUES ('admin', '$2y$10$8KQT50O7KRGgXS5TlqhbCuLKedSEcW.mxe3/adDKQNpmZfxZkgKAC'); -- Default password: admin123

-- Insert some sample products
INSERT INTO products (name, category, price, stock, description, image_url) VALUES
('Morning Blaze Blend', 'beans', 14.99, 50, 'A smooth medium-roast coffee with hints of chocolate and citrus. Perfect for starting your day with a relaxed vibe.', 'assets/images/morning-blaze.jpg'),
('High Altitude Dark Roast', 'beans', 16.99, 40, 'Bold and smoky dark roast from high-altitude farms. Strong but smooth finish.', 'assets/images/high-altitude.jpg'),
('Chill Ceramic Mug', 'merchandise', 12.99, 30, 'Handcrafted ceramic mug with the Tree Smoker logo. 12oz capacity.', 'assets/images/chill-mug.jpg'),
('Premium Grinder Set', 'tools', 29.99, 15, 'Stainless steel manual coffee grinder with adjustable coarseness settings.', 'assets/images/premium-grinder.jpg'),
('Leaf Logo T-Shirt', 'merchandise', 24.99, 25, 'Soft cotton t-shirt with embroidered Tree Smoker leaf logo. Available in sizes S-XL.', 'assets/images/leaf-tshirt.jpg'); 