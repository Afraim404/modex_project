-- MODEX Database
-- 1. Remove the first 2 lines (CREATE DATABASE + USE) before importing in phpMyAdmin
-- 2. Select your database in phpMyAdmin first, then import

CREATE DATABASE IF NOT EXISTS modex_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE modex_db;

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    category VARCHAR(100),
    images TEXT,
    specifications TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(20) UNIQUE NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    customer_email VARCHAR(255),
    delivery_address TEXT NOT NULL,
    district VARCHAR(100) NOT NULL,
    division VARCHAR(100),
    is_inside_dhaka TINYINT(1) DEFAULT 0,
    delivery_charge DECIMAL(10,2) NOT NULL,
    special_note TEXT,
    status ENUM('pending','confirmed','processing','shipped','delivered','cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(255) NOT NULL,
    review TEXT NOT NULL,
    rating INT DEFAULT 5,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default admin: username=admin  password=modex2024
INSERT INTO admin_users (username, password) VALUES
('admin', '$2y$10$vtesFHArD.4y3sugS0dC7.yDzDAsjQC7PRplsk.GHyr/mNAo9L7Xa');

INSERT INTO products (name, description, price, category, images, specifications) VALUES
('MODEX Alpha Stand', 'Premium 3D printed stand with modern industrial design. Built with high-strength PLA for maximum durability.', 850.00, 'Stands', '[]', '{"Material":"High-strength PLA","Height":"15cm","Base":"12x12cm","Weight":"320g","Colors":"Black, White, Gray"}'),
('MODEX Gear Holder', 'Compact gear holder for everyday use. Perfect for desks and workspaces with clean minimal profile.', 650.00, 'Holders', '[]', '{"Material":"PETG","Height":"10cm","Capacity":"5 slots","Weight":"180g","Colors":"Black, White"}'),
('MODEX Wall Mount', 'Space-saving wall mount with sleek minimal profile. Holds up to 2kg.', 550.00, 'Mounts', '[]', '{"Material":"ABS","Dimensions":"20x8cm","Max Load":"2kg","Weight":"150g","Colors":"White, Black"}');

INSERT INTO testimonials (customer_name, review, rating) VALUES
('Rahim Ahmed', 'Excellent quality! The 3D print is clean and sturdy. Delivered on time to Dhaka.', 5),
('Nusrat Jahan', 'Very happy with my MODEX stand. Looks great on my desk. Will order again!', 5),
('Karim Hossain', 'Great product, fast delivery. The finish quality is top-notch.', 5);
