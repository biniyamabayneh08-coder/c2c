<?php
require 'db.php';

try {
    // 1. Create categories table
    $conn->exec("CREATE TABLE IF NOT EXISTS categories (
        id SERIAL PRIMARY KEY,
        name VARCHAR(255) NOT NULL
    )");

    // 2. Create products table
    $conn->exec("CREATE TABLE IF NOT EXISTS products (
        id SERIAL PRIMARY KEY,
        category_id INT REFERENCES categories(id),
        title VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10, 2) NOT NULL,
        item_condition VARCHAR(50),
        delivery_type VARCHAR(50),
        status VARCHAR(50) DEFAULT 'Available',
        image VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 3. Insert default categories if the table is empty
    $stmt = $conn->query("SELECT COUNT(*) FROM categories");
    if ($stmt->fetchColumn() == 0) {
        $conn->exec("INSERT INTO categories (name) VALUES ('Electronics'), ('Vehicles'), ('Clothing'), ('Home & Garden'), ('Spare Parts')");
    }

    echo "<h2 style='color: green;'>Database tables created successfully!</h2>";
    echo "<p><a href='index.php'>Click here to go to your C2C Marketplace Home</a></p>";

} catch (PDOException $e) {
    echo "<h2 style='color: red;'>Error setting up database:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>