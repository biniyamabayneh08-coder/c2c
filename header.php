<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>C2C Marketplace</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="logo"><i class="fa-solid fa-store"></i> C2C Market</a>
    
    <form action="index.php" method="GET" class="search-bar">
        <input type="text" name="search" placeholder="Search items, parts, tools..." 
               value="<?php echo isset($_GET['search']) ? e($_GET['search']) : ''; ?>">
        <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
    </form>

    <div class="nav-links">
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="dashboard.php"><i class="fa-solid fa-user-gear"></i> Dashboard</a>
            <a href="add_product.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Sell</a>
            <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i></a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php" class="btn btn-primary">Sign Up</a>
        <?php endif; ?>
    </div>
</nav>
<div class="container">