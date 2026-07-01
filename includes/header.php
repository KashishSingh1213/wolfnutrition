<?php
// includes/header.php
require_once __DIR__ . '/functions.php';

$announcements = get_announcements();
$cart_count = get_cart_count();
$active_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wolf Nutrition | Premium Ayurvedic Performance & Vitality</title>
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Main Style CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- Rotating Announcement Bar -->
    <div class="announcement-bar">
        <?php if (!empty($announcements)): ?>
            <?php foreach ($announcements as $index => $ann): ?>
                <div class="announcement-item <?php echo $index === 0 ? 'active' : ''; ?>">
                    <a href="<?php echo htmlspecialchars($ann['link'] ? $ann['link'] : '#'); ?>">
                        <?php echo htmlspecialchars($ann['message']); ?>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="announcement-item active">
                <a href="#">🌿 100% Ayurvedic Sourced | FSSAI Certified Wholesaler | Veggie Capsules</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Sticky Header -->
    <header>
        <div class="container header-container">
            <!-- Brand Logo -->
            <a href="index.php" class="logo">
                <img src="assets/images/logo.png" alt="Wolf Nutrition Logo">
                <div class="logo-text">WOLF <span>NUTRITION</span></div>
            </a>

            <!-- Navigation Links -->
            <nav>
                <ul>
                    <li class="<?php echo $active_page === 'index.php' ? 'active' : ''; ?>"><a href="index.php">Home</a></li>
                    <li class="<?php echo ($active_page === 'category.php' && isset($_GET['slug']) && $_GET['slug'] === 'vitality') ? 'active' : ''; ?>"><a href="category.php?slug=vitality">Supplements</a></li>
                    <li class="<?php echo ($active_page === 'category.php' && isset($_GET['slug']) && $_GET['slug'] === 'liver-detox') ? 'active' : ''; ?>"><a href="category.php?slug=liver-detox">Liver & Detox</a></li>
                    <li class="<?php echo $active_page === 'about.php' ? 'active' : ''; ?>"><a href="about.php">About Us</a></li>
                    <li class="<?php echo $active_page === 'contact.php' ? 'active' : ''; ?>"><a href="contact.php">Contact Us</a></li>
                </ul>
            </nav>

            <!-- Actions Icons -->
            <div class="header-actions">
                <!-- Search Icon -->
                <button class="header-icon search-trigger" aria-label="Search Products">
                    <i class="fas fa-search"></i>
                </button>

                <!-- My Account Icon -->
                <a href="<?php echo is_logged_in() ? 'my-account.php' : 'login.php'; ?>" class="header-icon" aria-label="My Account">
                    <i class="fas fa-user-circle"></i>
                </a>

                <!-- Cart Icon -->
                <button class="header-icon cart-drawer-trigger" aria-label="Open Cart">
                    <i class="fas fa-shopping-bag"></i>
                    <span class="cart-badge" style="<?php echo $cart_count > 0 ? 'display:flex;' : 'display:none;'; ?>">
                        <?php echo $cart_count; ?>
                    </span>
                </button>
            </div>
        </div>
    </header>

    <!-- Search Overlay -->
    <div class="search-overlay">
        <div class="search-close"><i class="fas fa-times"></i></div>
        <div class="search-input-wrapper">
            <input type="text" id="search-input" placeholder="What are you looking for..." autocomplete="off">
        </div>
        
        <div class="search-popular-searches">
            <h4>Popular Searches</h4>
            <div class="search-tags">
                <a href="product.php?slug=wolfpack-unleash-the-alpha-within" class="search-tag">Wolfpack Vitality</a>
                <a href="product.php?slug=wolftox-liver-support-detox" class="search-tag">Wolftox Detox</a>
                <a href="category.php?slug=vitality" class="search-tag">Shilajit</a>
                <a href="category.php?slug=liver-detox" class="search-tag">Liver Support</a>
            </div>
        </div>

        <!-- Search Live Results -->
        <div class="search-live-results"></div>
    </div>

    <!-- Slide-out Cart Drawer Backdrop -->
    <div class="cart-drawer-backdrop"></div>

    <!-- Slide-out Cart Drawer -->
    <div class="cart-drawer">
        <div class="cart-drawer-header">
            <h3>Your Pack Cart</h3>
            <div class="cart-drawer-close"><i class="fas fa-times"></i></div>
        </div>
        
        <!-- Cart Drawer Items -->
        <div class="cart-drawer-items">
            <!-- Populated via AJAX / main.js -->
        </div>

        <div class="cart-drawer-footer">
            <div class="cart-drawer-totals">
                <span>Subtotal:</span>
                <span id="cart-drawer-total-val">₹0</span>
            </div>
            
            <div class="cart-drawer-actions">
                <a href="cart.php" class="btn btn-outline-gold" style="width:100%;">View Full Cart</a>
                <a href="checkout.php" class="btn btn-gold" style="width:100%;">Proceed to Checkout</a>
            </div>
        </div>
    </div>
