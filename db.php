<?php
session_start();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli('localhost', 'root', '', 'marketplace');
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

if (!is_dir(__DIR__ . '/uploads')) { 
    mkdir(__DIR__ . '/uploads', 0777, true); 
}

function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>