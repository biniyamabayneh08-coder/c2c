<?php
// Ensure session starts on every page automatically
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $databaseUrl = getenv('DATABASE_URL');
    $host = getenv('C2C_DB_HOST');
    $isPostgres = (bool) $databaseUrl || (bool) $host;

    if ($databaseUrl) {
        $parts = parse_url($databaseUrl);
        if (!$parts || empty($parts['host']) || empty($parts['path']) || !isset($parts['user'], $parts['pass'])) {
            throw new RuntimeException('DATABASE_URL is not a valid PostgreSQL connection URL.');
        }

        parse_str($parts['query'] ?? '', $query);
        $sslmode = $query['sslmode'] ?? 'require';
        $dsn = 'pgsql:host=' . $parts['host']
            . ';port=' . ($parts['port'] ?? 5432)
            . ';dbname=' . ltrim($parts['path'], '/')
            . ';sslmode=' . $sslmode;
        $dbUser = rawurldecode($parts['user']);
        $dbPassword = rawurldecode($parts['pass']);
    } elseif ($isPostgres) {
        $dbName = getenv('C2C_DB_NAME');
        $dbUser = getenv('C2C_DB_USER');
        $dbPassword = getenv('C2C_DB_PASSWORD');
        if (!$dbName || !$dbUser || !$dbPassword) {
            throw new RuntimeException('Set C2C_DB_NAME, C2C_DB_USER, and C2C_DB_PASSWORD with C2C_DB_HOST.');
        }
        $sslmode = getenv('C2C_DB_SSLMODE') ?: 'require';
        $dsn = 'pgsql:host=' . $host . ';port=' . (getenv('C2C_DB_PORT') ?: '5432')
            . ';dbname=' . $dbName . ';sslmode=' . $sslmode;
    } else {
        $databasePath = getenv('C2C_DB_PATH') ?: __DIR__ . '/marketplace.sqlite';
        $dsn = 'sqlite:' . $databasePath;
        $dbUser = null;
        $dbPassword = null;
    }

    $conn = new PDO($dsn, $dbUser, $dbPassword, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    if (!$isPostgres) {
        $conn->exec('PRAGMA foreign_keys = ON');
    }

    $idPrimaryKey = $isPostgres ? 'SERIAL PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT';

    $schema = "
        CREATE TABLE IF NOT EXISTS users (
            id $idPrimaryKey,
            name VARCHAR(50) NOT NULL,
            username VARCHAR(50) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS categories (
            id $idPrimaryKey,
            name VARCHAR(100) NOT NULL
        );

        CREATE TABLE IF NOT EXISTS products (
            id $idPrimaryKey,
            seller_id INT NOT NULL,
            category_id INT NOT NULL,
            title VARCHAR(150) NOT NULL,
            description TEXT,
            price DECIMAL(10, 2) NOT NULL,
            stock_quantity INT NOT NULL DEFAULT 1,
            status VARCHAR(50) DEFAULT 'Available',
            item_condition VARCHAR(50) DEFAULT 'Used',
            delivery_type VARCHAR(100) DEFAULT 'Postal Service',
            payment_methods VARCHAR(255) DEFAULT 'Cash',
            image VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (seller_id) REFERENCES users(id),
            FOREIGN KEY (category_id) REFERENCES categories(id)
        );

        CREATE TABLE IF NOT EXISTS transactions (
            id $idPrimaryKey,
            product_id INT NOT NULL,
            buyer_id INT NOT NULL,
            seller_id INT NOT NULL,
            total_amount DECIMAL(10, 2) NOT NULL,
            commission_amount DECIMAL(10, 2) NOT NULL,
            seller_payout DECIMAL(10, 2) NOT NULL,
            payment_status VARCHAR(50) DEFAULT 'unpaid',
            delivery_status VARCHAR(50) DEFAULT 'waiting_for_shipping',
            transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id),
            FOREIGN KEY (buyer_id) REFERENCES users(id),
            FOREIGN KEY (seller_id) REFERENCES users(id)
        );

        CREATE TABLE IF NOT EXISTS payments (
            id $idPrimaryKey,
            transaction_id INT NOT NULL,
            buyer_id INT NOT NULL,
            payment_reference VARCHAR(100) NOT NULL,
            provider VARCHAR(50) DEFAULT 'manual',
            amount DECIMAL(10, 2) NOT NULL,
            currency VARCHAR(10) DEFAULT 'ETB',
            status VARCHAR(50) DEFAULT 'pending',
            paid_at TIMESTAMP NULL,
            FOREIGN KEY (transaction_id) REFERENCES transactions(id),
            FOREIGN KEY (buyer_id) REFERENCES users(id)
        );

        CREATE TABLE IF NOT EXISTS seller_payouts (
            id $idPrimaryKey,
            transaction_id INT NOT NULL,
            seller_id INT NOT NULL,
            amount DECIMAL(10, 2) NOT NULL,
            marketplace_fee DECIMAL(10, 2) NOT NULL,
            status VARCHAR(50) DEFAULT 'waiting',
            FOREIGN KEY (transaction_id) REFERENCES transactions(id),
            FOREIGN KEY (seller_id) REFERENCES users(id)
        );

        CREATE TABLE IF NOT EXISTS likes (
            user_id INT NOT NULL,
            product_id INT NOT NULL,
            action VARCHAR(20) NOT NULL,
            PRIMARY KEY (user_id, product_id),
            FOREIGN KEY (user_id) REFERENCES users(id),
            FOREIGN KEY (product_id) REFERENCES products(id)
        );

        CREATE TABLE IF NOT EXISTS comments (
            id $idPrimaryKey,
            user_id INT NOT NULL,
            product_id INT NOT NULL,
            comment TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id),
            FOREIGN KEY (product_id) REFERENCES products(id)
        );
        ";
    $conn->exec($schema);

    foreach (['Electronics', 'Vehicles & Spare Parts', 'Home & Office', 'Fashion & Clothing'] as $category) {
        $stmt = $conn->prepare('INSERT INTO categories (name) SELECT ? WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = ?)');
        $stmt->execute([$category, $category]);
    }
} catch (PDOException $e) {
    die("Database Connection / Setup Failed: " . $e->getMessage());
}

if (!is_dir(__DIR__ . '/uploads')) { 
    mkdir(__DIR__ . '/uploads', 0777, true); 
}

if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }
}
?>