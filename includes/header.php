<?php
/**
 * ARMAS Shared Header
 * Include at the top of every page
 */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo isset($page_title) ? $page_title . ' — ARMAS' : 'ARMAS — Assistance and Repatriation Management and Action System'; ?>
    </title>
    <meta name="description" content="ARMAS - Protecting Every Filipino, Every Mile Away">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@400;500;600&family=JetBrains+Mono&display=swap"
        rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- ARMAS CSS -->
    <link rel="stylesheet" href="/armas/assets/css/style.css">
    <?php if (isset($use_dashboard_css) && $use_dashboard_css): ?>
        <link rel="stylesheet" href="/armas/assets/css/dashboard.css">
    <?php endif; ?>
    <link rel="stylesheet" href="/armas/assets/css/responsive.css">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/armas/assets/img/armas.png">
</head>

<body>
    <?php if (!isset($hide_navbar)): ?>
        <!-- Navbar -->
        <nav class="navbar">
            <div class="navbar-container">
                <a href="<?php echo isset($is_portal) ? '/armas/pages/landing.php' : '/armas/pages/landing.php'; ?>"
                    class="navbar-brand">
                    <img src="/armas/assets/img/armas.png" alt="ARMAS Shield" class="navbar-logo">
                    <div class="navbar-brand-text">
                        <span class="logo-text">ARMAS</span>
                        <span class="brand-subtitle">Assistance &amp; Repatriation</span>
                    </div>
                </a>

                <?php if (!isset($hide_nav_menu)): ?>
                    <button class="hamburger" id="hamburger" onclick="toggleMobileMenu()">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>

                    <ul class="nav-menu" id="nav-menu">
                        <?php if (!isset($_SESSION['user_id'])): ?>
                            <li><a href="/armas/pages/landing.php">Home</a></li>
                            <li><a href="/armas/pages/landing.php#about">About</a></li>
                            <li><a href="/armas/pages/landing.php#services">Services</a></li>
                            <li><a href="/armas/pages/landing.php#contact">Contact</a></li>
                            <li><a href="/armas/pages/login.php" class="btn btn-outline">Login</a></li>
                            <li><a href="/armas/pages/register.php" class="btn btn-primary">Register as OFW</a></li>
                        <?php else: ?>
                            <li><a href="<?php echo get_dashboard_link(); ?>">Dashboard</a></li>
                            <li><a href="/armas/pages/logout.php" class="btn btn-outline">Logout</a></li>
                        <?php endif; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </nav>
    <?php endif; ?>

    <!-- Flash Messages -->
    <?php display_flash(); ?>

    <!-- Main Content -->
    <?php if (!isset($hide_navbar)): ?>
        <main class="main-content">
        <?php endif; ?>