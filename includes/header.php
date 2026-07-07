<?php
// includes/header.php
require_once __DIR__ . '/functions.php';

$announcements = get_announcements();
$cart_count = get_cart_count();
$active_page = basename($_SERVER['PHP_SELF']);

// Dynamic SEO & Metatag Engine
$seo_title = "Wolf Nutrition | Premium Ayurvedic Performance & Vitality Stacks";
$seo_desc = "Wolf Nutrition merges ancient Ayurvedic wisdom with modern sports science. Buy certified Shilajit, Ashwagandha, and Kutki stacks for stamina and liver support.";
$seo_keywords = "wolf nutrition, ayurvedic stamina gainer, shilajit capsules india, liver support kutki, wolftox liver detox, test boost ayurvedic";
$canonical_url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

if ($active_page === 'product.php' && isset($_GET['slug'])) {
    $prod_slug = $_GET['slug'];
    $stmt = $pdo->prepare("SELECT name, short_description FROM products WHERE slug = ?");
    $stmt->execute([$prod_slug]);
    $prod_seo = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($prod_seo) {
        $seo_title = htmlspecialchars($prod_seo['name']) . " | Buy Online | Wolf Nutrition";
        $seo_desc = htmlspecialchars(strip_tags($prod_seo['short_description']));
        $seo_keywords = htmlspecialchars(strtolower($prod_seo['name'])) . ", buy online, ayurvedic premium performance";
    }
} elseif ($active_page === 'category.php' && isset($_GET['slug'])) {
    $cat_slug = $_GET['slug'];
    if ($cat_slug === 'vitality') {
        $seo_title = "Ayurvedic Performance & Vitality Supplements | Wolf Nutrition";
        $seo_desc = "Shop premium Ayurvedic vitality capsules containing pure Himalayan Shilajit, Ashwagandha, Gokshura, and Safed Musli extracts.";
    } elseif ($cat_slug === 'liver-detox') {
        $seo_title = "Liver Support & Detox Stacks | Wolf Nutrition";
        $seo_desc = "Protect your liver enzymes and cleanse toxins. Shop Kutki, Milk Thistle, and Kalmegh Ayurvedic liver support capsules.";
    }
} elseif ($active_page === 'about.php') {
    $seo_title = "Our Brand Story & Philosophy | Wolf Nutrition";
    $seo_desc = "Discover how Wolf Nutrition bridges the gap between ancient Ayurvedic botanicals and the rigorous demands of modern active life.";
} elseif ($active_page === 'contact.php') {
    $seo_title = "Contact Us & Expert Support | Wolf Nutrition";
    $seo_desc = "Get in touch with Wolf Nutrition. Ask questions about your stacks, shipping, or book your free dietitian Ayurvedic call.";
} elseif ($active_page === 'blog-post.php' && isset($_GET['slug'])) {
    $blog_slug = $_GET['slug'];
    $stmt_blog = $pdo->prepare("SELECT title, body, cover_image, category_tag, excerpt FROM blog_posts WHERE slug = ? AND status = 1");
    $stmt_blog->execute([$blog_slug]);
    $blog_seo = $stmt_blog->fetch(PDO::FETCH_ASSOC);
    if ($blog_seo) {
        $seo_title = htmlspecialchars($blog_seo['title']) . " | Wolf Nutrition Blog";
        $blog_excerpt = !empty($blog_seo['excerpt']) ? $blog_seo['excerpt'] : strip_tags($blog_seo['body']);
        $seo_desc = htmlspecialchars(substr($blog_excerpt, 0, 160));
        $seo_keywords = htmlspecialchars($blog_seo['category_tag']) . ", wolf nutrition, ayurvedic, wellness, blog";
        $blog_og_image = !empty($blog_seo['cover_image']) ? $blog_seo['cover_image'] : 'assets/images/logo.png';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- SEO Meta Tags -->
    <title><?php echo $seo_title; ?></title>
    <meta name="description" content="<?php echo $seo_desc; ?>">
    <meta name="keywords" content="<?php echo $seo_keywords; ?>">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?php echo $canonical_url; ?>">

    <!-- Open Graph / Facebook / Instagram -->
    <meta property="og:type" content="article">
    <meta property="og:url" content="<?php echo $canonical_url; ?>">
    <meta property="og:title" content="<?php echo $seo_title; ?>">
    <meta property="og:description" content="<?php echo $seo_desc; ?>">
    <meta property="og:image" content="http://<?php echo $_SERVER['HTTP_HOST']; ?>/wolfnutrition/<?php echo isset($blog_og_image) ? $blog_og_image : 'assets/images/logo.png'; ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?php echo $canonical_url; ?>">
    <meta property="twitter:title" content="<?php echo $seo_title; ?>">
    <meta property="twitter:description" content="<?php echo $seo_desc; ?>">
    <meta property="twitter:image" content="http://<?php echo $_SERVER['HTTP_HOST']; ?>/wolfnutrition/<?php echo isset($blog_og_image) ? $blog_og_image : 'assets/images/logo.png'; ?>">

    <!-- JSON-LD Structured Data for Search Engine rich snippets -->
    <script type="application/ld+json">
    <?php
    if ($active_page === 'product.php' && isset($prod_seo)) {
        echo json_encode([
            "@context" => "https://schema.org/",
            "@type" => "Product",
            "name" => $prod_seo['name'],
            "image" => "http://" . $_SERVER['HTTP_HOST'] . "/wolfnutrition/assets/images/logo.png",
            "description" => $seo_desc,
            "brand" => [
                "@type" => "Brand",
                "name" => "Wolf Nutrition"
            ],
            "offers" => [
                "@type" => "AggregateOffer",
                "url" => $canonical_url,
                "priceCurrency" => "INR",
                "lowPrice" => "899.00",
                "highPrice" => "1999.00",
                "offerCount" => "1"
            ]
        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    } elseif ($active_page === 'blog-post.php' && isset($blog_seo)) {
        echo json_encode([
            "@context" => "https://schema.org",
            "@type" => "BlogPosting",
            "headline" => $blog_seo['title'],
            "description" => $seo_desc,
            "image" => !empty($blog_seo['cover_image']) ? "http://" . $_SERVER['HTTP_HOST'] . "/wolfnutrition/" . $blog_seo['cover_image'] : "http://" . $_SERVER['HTTP_HOST'] . "/wolfnutrition/assets/images/logo.png",
            "author" => [
                "@type" => "Organization",
                "name" => "Wolf Nutrition"
            ],
            "publisher" => [
                "@type" => "Organization",
                "name" => "Wolf Nutrition",
                "logo" => [
                    "@type" => "ImageObject",
                    "url" => "http://" . $_SERVER['HTTP_HOST'] . "/wolfnutrition/assets/images/logo.png"
                ]
            ],
            "datePublished" => date('c', strtotime($post['published_at'])),
            "mainEntityOfPage" => $canonical_url
        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    } else {
        echo json_encode([
            "@context" => "https://schema.org",
            "@type" => "Organization",
            "name" => "Wolf Nutrition",
            "url" => "http://" . $_SERVER['HTTP_HOST'] . "/wolfnutrition",
            "logo" => "http://" . $_SERVER['HTTP_HOST'] . "/wolfnutrition/assets/images/logo.png",
            "sameAs" => [
                "https://facebook.com/wolfnutrition",
                "https://instagram.com/wolfnutrition"
            ]
        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }
    ?>
    </script>

    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Razorpay Checkout SDK -->
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <!-- Header CSS -->
    <link rel="stylesheet" href="assets/css/header-clean.css">
    <!-- Main Style CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<script>
// Mobile Menu
document.addEventListener('DOMContentLoaded', function() {
    var menuBtn = document.getElementById('mobileMenuBtn');
    var mobileNav = document.getElementById('mobileNav');
    var overlay = document.getElementById('mobileOverlay');
    var closeBtn = document.getElementById('mobileCloseBtn');

    function openMenu() {
        mobileNav.classList.add('open');
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    function closeMenu() {
        mobileNav.classList.remove('open');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (menuBtn) menuBtn.addEventListener('click', openMenu);
    if (closeBtn) closeBtn.addEventListener('click', closeMenu);
    if (overlay) overlay.addEventListener('click', closeMenu);

    // Close on link click
    var mobileLinks = document.querySelectorAll('.mobile-nav-links a');
    mobileLinks.forEach(function(link) {
        link.addEventListener('click', closeMenu);
    });
});
</script>

    <!-- Premium Announcement Bar -->
    <div class="announcement-bar">
        <div class="announcement-item active">
            <a href="#"><i class="fas fa-truck-fast" style="color:var(--gold-primary);"></i> FREE Shipping on all prepaid orders — Limited time only!</a>
        </div>
        <div class="announcement-item">
            <a href="#"><i class="fas fa-tags" style="color:var(--gold-primary);"></i> Wolfpack Combo Offer: Buy 2 products together, Save 10% automatically!</a>
        </div>
        <div class="announcement-item">
            <a href="#"><i class="fas fa-leaf" style="color:var(--gold-primary);"></i> 100% Ayurvedic Sourced | FSSAI Certified Wholesaler | Veggie Capsules</a>
        </div>
    </div>

    <!-- Premium Navbar -->
    <header id="mainHeader">
        <div class="container header-container">
            <!-- Logo -->
            <a href="index.php" class="logo">
                <img src="assets/images/logo.png" alt="Wolf Nutrition">
                <div class="logo-text">WOLF <span>NUTRITION</span></div>
            </a>

            <!-- Navigation (Desktop) -->
            <nav class="desktop-nav">
                <ul>
                    <li class="<?php echo $active_page === 'index.php' ? 'active' : ''; ?>">
                        <a href="index.php">Home</a>
                    </li>
                    <li class="<?php echo ($active_page === 'category.php' && isset($_GET['slug']) && $_GET['slug'] === 'vitality') ? 'active' : ''; ?>">
                        <a href="category.php?slug=vitality">Supplements</a>
                    </li>
                    <li class="<?php echo ($active_page === 'category.php' && isset($_GET['slug']) && $_GET['slug'] === 'liver-detox') ? 'active' : ''; ?>">
                        <a href="category.php?slug=liver-detox">Liver & Detox</a>
                    </li>
                    <li class="<?php echo $active_page === 'about.php' ? 'active' : ''; ?>">
                        <a href="about.php">About Us</a>
                    </li>
                    <li class="<?php echo $active_page === 'contact.php' ? 'active' : ''; ?>">
                        <a href="contact.php">Contact</a>
                    </li>
                </ul>
            </nav>

            <!-- Right Actions -->
            <div class="header-actions">
                <button class="header-icon search-trigger" aria-label="Search">
                    <i class="fas fa-search"></i>
                    <span class="icon-label">Search</span>
                </button>
                <a href="<?php echo is_logged_in() ? 'my-account.php' : 'login.php'; ?>" class="header-icon" aria-label="Account">
                    <i class="fas fa-user-circle"></i>
                    <?php if (is_logged_in()): ?>
                        <span class="logged-dot"></span>
                    <?php endif; ?>
                    <span class="icon-label"><?php echo is_logged_in() ? 'Account' : 'Login'; ?></span>
                </a>
                <button class="header-icon cart-drawer-trigger" aria-label="Cart">
                    <i class="fas fa-shopping-bag"></i>
                    <span class="cart-badge" style="<?php echo $cart_count > 0 ? 'display:flex;' : 'display:none;'; ?>">
                        <?php echo $cart_count; ?>
                    </span>
                    <span class="icon-label">Cart</span>
                </button>
                <!-- Mobile Menu Toggle -->
                <button class="mobile-menu-toggle" id="mobileMenuBtn" aria-label="Menu">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
    </header>

    <!-- Mobile Menu -->
    <div class="mobile-menu-overlay" id="mobileOverlay"></div>
    <nav class="mobile-nav" id="mobileNav">
        <div class="mobile-nav-header">
            <a href="index.php" class="mobile-nav-logo">
                <img src="assets/images/logo.png" alt="Wolf Nutrition">
                <span>WOLF NUTRITION</span>
            </a>
            <button class="mobile-nav-close" id="mobileCloseBtn">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <ul class="mobile-nav-links">
            <li class="<?php echo $active_page === 'index.php' ? 'active' : ''; ?>">
                <a href="index.php"><i class="fas fa-home"></i> Home</a>
            </li>
            <li class="<?php echo ($active_page === 'category.php' && isset($_GET['slug']) && $_GET['slug'] === 'vitality') ? 'active' : ''; ?>">
                <a href="category.php?slug=vitality"><i class="fas fa-capsules"></i> Supplements</a>
            </li>
            <li class="<?php echo ($active_page === 'category.php' && isset($_GET['slug']) && $_GET['slug'] === 'liver-detox') ? 'active' : ''; ?>">
                <a href="category.php?slug=liver-detox"><i class="fas fa-shield-halved"></i> Liver & Detox</a>
            </li>
            <li class="<?php echo $active_page === 'about.php' ? 'active' : ''; ?>">
                <a href="about.php"><i class="fas fa-info-circle"></i> About Us</a>
            </li>
            <li class="<?php echo $active_page === 'contact.php' ? 'active' : ''; ?>">
                <a href="contact.php"><i class="fas fa-envelope"></i> Contact</a>
            </li>
        </ul>
        <div class="mobile-nav-footer">
            <a href="<?php echo is_logged_in() ? 'my-account.php' : 'login.php'; ?>" class="btn-gold" style="width:100%; justify-content:center; padding:12px; text-decoration:none;">
                <i class="fas fa-user-circle"></i> <?php echo is_logged_in() ? 'My Account' : 'Login / Register'; ?>
            </a>
        </div>
    </nav>

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
