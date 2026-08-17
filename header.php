<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>C2C Market | Buy & Sell Easily</title>

    <!-- CSS -->
    <link rel="stylesheet" href="style.css">

    <!-- Font Awesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

<!-- ================= NAVBAR ================= -->
<header class="navbar">

    <!-- Logo -->
    <a href="index.php" class="logo">
        <span class="logo-icon">
            <i class="fa-solid fa-store"></i>
        </span>
        <span>C2C <strong>Market</strong></span>
    </a>

    <!-- Search -->
    <form action="index.php" method="GET" class="search-bar">

        <i class="fa-solid fa-magnifying-glass search-icon"></i>

        <input
            type="text"
            name="search"
            placeholder="Search products, phones, cars, clothes..."
            value="<?php echo isset($_GET['search']) ? e($_GET['search']) : ''; ?>"
            aria-label="Search products"
        >

        <button type="submit" aria-label="Search">
            Search
        </button>

    </form>

    <!-- Navigation -->
    <nav class="nav-links">

        <?php if (isset($_SESSION['user_id'])): ?>

            <!-- Favorites -->
            <a href="favorites.php" class="nav-icon" title="Favorites">
                <i class="fa-regular fa-heart"></i>
                <span>Favorites</span>
            </a>

            <!-- Messages -->
            <a href="messages.php" class="nav-icon" title="Messages">
                <i class="fa-regular fa-comment"></i>
                <span>Messages</span>
            </a>

            <!-- Dashboard -->
            <a href="dashboard.php" class="nav-icon">
                <i class="fa-regular fa-user"></i>
                <span>Account</span>
            </a>

            <!-- Sell Button -->
            <a href="add_product.php" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i>
                Sell Item
            </a>

            <!-- Logout -->
            <a href="logout.php" class="logout-btn" title="Logout">
                <i class="fa-solid fa-right-from-bracket"></i>
            </a>

        <?php else: ?>

            <!-- Login -->
            <a href="login.php" class="login-link">
                Login
            </a>

            <!-- Register -->
            <a href="register.php" class="btn btn-primary">
                <i class="fa-solid fa-user-plus"></i>
                Sign Up
            </a>

        <?php endif; ?>

    </nav>

    <!-- Mobile Menu -->
    <button class="mobile-menu-btn" aria-label="Open menu">
        <i class="fa-solid fa-bars"></i>
    </button>

</header>


<!-- ================= MAIN CONTENT ================= -->
<main class="container"></main>