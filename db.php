<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli('dpg-da5eisjm8hqs73cdv5dg-a', 'bini_tcgq_user', 'xjFToVf2Az0J5qNKxms10lUaAYZbHPXs', 'bini_tcgq');
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