<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Render PostgreSQL Credentials
$host = 'dpg-da5eisjm8hqs73cdv5dg-a';
$db   = 'bini_tcgq';
$user = 'bini_tcgq_user';
$pass = 'xjFToVf2Az0J5qNKxms10lUaAYZbHPXs';
$port = '5432'; // Default PostgreSQL port

$dsn = "pgsql:host=$host;port=$port;dbname=$db";

try {
    // Connect to PostgreSQL using PDO
    $conn = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

if (!is_dir(__DIR__ . '/uploads')) { 
    mkdir(__DIR__ . '/uploads', 0777, true); 
}

function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>