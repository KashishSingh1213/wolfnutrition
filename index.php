<?php
// index.php
require_once __DIR__ . '/includes/header.php';

// Fetch all active categories
try {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE is_active = 1 ORDER BY display_order ASC");
    $stmt->execute();
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}

// Fetch products for tabbed grid
$products_by_category = [];
foreach ($categories as $cat) {
    $stmt = $pdo->prepare("
        SELECT p.*, MIN(pv.price) as max_mrp, MIN(pv.sale_price) as min_price, pv.id as default_variant_id
        FROM products p
        JOIN product_variants pv ON p.id = pv.product_id
        WHERE p.category_id = ? AND p.is_active = 1 AND pv.is_default = 1
        GROUP BY p.id
    ");
    $stmt->execute([$cat['id']]);
    $products_by_category[$cat['slug']] = $stmt->fetchAll();
}

// Fetch bundle info (Combo Pack)
try {
    $stmt = $pdo->prepare("SELECT * FROM bundles WHERE status = 1 LIMIT 1");
    $stmt->execute();
    $bundle = $stmt->fetch();
} catch (PDOException $e) {
    $bundle = null;
}

// Fetch certificates
$certs = get_certificates();

// Fetch latest blog posts
try {
    $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE status = 1 ORDER BY published_at DESC LIMIT 3");
    $stmt->execute();
    $blogs = $stmt->fetchAll();
} catch (PDOException $e) {
    $blogs = [];
}
?>

    <!-- Hero Banner Slider -->
    <section class="hero-slider">
        <!-- Slide 1: Wolftox (Fuel Your Strength) -->
        <div class="hero-slide active">
            <a href="product.php?slug=wolftox-liver-support-detox">
                <img src="assets/images/hero1.png" alt="WolfTox Liver Support & Detox Banners">
            </a>
        </div>
        <!-- Slide 2: Wolfpack (Unleash the Beast) -->
        <div class="hero-slide">
            <a href="product.php?slug=wolfpack-unleash-the-alpha-within">
                <img src="assets/images/hero2.png" alt="Wolfpack Stamina & Strength Banners">
            </a>
        </div>
        <!-- Slide 3: Wolfpack (Natural Ingredients) -->
        <div class="hero-slide">
            <a href="product.php?slug=wolfpack-unleash-the-alpha-within">
                <img src="assets/images/hero3.png" alt="Wolfpack Performance & Vitality Banners">
            </a>
        </div>
        <!-- Slide 4: Wolftox (Healthy Liver) -->
        <div class="hero-slide">
            <a href="product.php?slug=wolftox-liver-support-detox">
                <img src="assets/images/hero4.png" alt="WolfTox Detox & Immunity Banners">
            </a>
        </div>
    </section>

    <!-- Category Tile Grid -->
    <section class="container" style="margin-top: 60px;">
        <div class="section-header">
            <h2>Shop By Health Need</h2>
            <p>Select a specialized category to find your targeted wellness solution</p>
        </div>
        <div class="category-tiles-grid">
            <a href="category.php?slug=vitality" class="category-tile">
                <img src="assets/images/products/wolfpack.png" alt="Men's Vitality Category">
            </a>
            <a href="category.php?slug=liver-detox" class="category-tile">
                <img src="assets/images/products/wolftox.png" alt="Liver & Detox Category">
            </a>
            <a href="category.php?slug=all" class="category-tile">
                <img src="assets/images/products/wolfpack_wolftox_combo.png" alt="Shop All Category">
            </a>
        </div>
    </section>

    <!-- Tabbed Product Grid -->
    <section class="container" style="margin-bottom: 60px;">
        <div class="section-header">
            <h2>Range of Products</h2>
            <p>Raw, natural, and scientifically balanced formulations</p>
        </div>

        <!-- Tabs Navigation -->
        <div class="tabs-container">
            <?php foreach ($categories as $index => $cat): ?>
                <button class="tab-btn <?php echo $index === 0 ? 'active' : ''; ?>" data-target="cat-<?php echo $cat['slug']; ?>">
                    <?php echo htmlspecialchars($cat['name']); ?>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- Tab Contents -->
        <?php foreach ($categories as $index => $cat): ?>
            <div id="cat-<?php echo $cat['slug']; ?>" class="tab-pane <?php echo $index === 0 ? 'active' : ''; ?>">
                <div class="product-grid">
                    <?php 
                    $prods = isset($products_by_category[$cat['slug']]) ? $products_by_category[$cat['slug']] : [];
                    if (!empty($prods)):
                        foreach ($prods as $prod):
                            // Calculate discount percent
                            $discount_pct = 0;
                            if ($prod['max_mrp'] > 0) {
                                $discount_pct = round((($prod['max_mrp'] - $prod['min_price']) / $prod['max_mrp']) * 100);
                            }
                            
                            // Check average rating
                            $stmt_r = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(id) as cnt FROM reviews WHERE product_id = ? AND is_approved = 1");
                            $stmt_r->execute([$prod['id']]);
                            $rating_info = $stmt_r->fetch();
                            $avg_r = $rating_info['avg_rating'] ? round($rating_info['avg_rating'], 1) : 5.0;
                            $cnt_r = $rating_info['cnt'];
                    ?>
                        <div class="product-card glass-card">
                            <?php if ($discount_pct > 0): ?>
                                <span class="badge-discount">-<?php echo $discount_pct; ?>% OFF</span>
                            <?php endif; ?>
                            
                            <div class="product-card-image">
                                <img src="<?php echo htmlspecialchars($prod['image_url']); ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>">
                            </div>
                            
                            <div class="product-card-info">
                                <a href="product.php?slug=<?php echo $prod['slug']; ?>">
                                    <h3 class="product-card-title"><?php echo htmlspecialchars($prod['name']); ?></h3>
                                </a>
                                
                                <div class="product-card-rating">
                                    <?php for($i=1; $i<=5; $i++): ?>
                                        <i class="<?php echo $i <= round($avg_r) ? 'fas' : 'far'; ?> fa-star"></i>
                                    <?php endfor; ?>
                                    <span>(<?php echo $cnt_r; ?>)</span>
                                </div>
                                
                                <div class="product-card-prices">
                                    <span class="price-sale">₹<?php echo number_format($prod['min_price'], 2); ?></span>
                                    <span class="price-regular">MRP ₹<?php echo number_format($prod['max_mrp'], 2); ?></span>
                                </div>
                                
                                <div class="product-card-action">
                                    <!-- Hover Quick Add / Select Options button -->
                                    <button class="btn-gold quick-add-btn" style="width: 100%;" 
                                            data-product-id="<?php echo $prod['id']; ?>" 
                                            data-variant-id="<?php echo $prod['default_variant_id']; ?>">
                                        <i class="fas fa-shopping-cart"></i> Quick Add
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php 
                        endforeach;
                    else:
                    ?>
                        <p style="text-align: center; grid-column: 1/-1;">No products found in this category.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </section>

    <!-- Build Your Wellness Stack Combo Section -->
    <?php if ($bundle): ?>
    <section class="bundle-builder-section">
        <div class="container">
            <div class="section-header">
                <h2>Build Your Wellness Stack</h2>
                <p>Maximize your performance. Combined power for peak testosterone & total liver detox</p>
            </div>
            
            <div class="bundle-builder-grid">
                <!-- Product A: Wolfpack -->
                <div class="bundle-card glass-card">
                    <img src="assets/images/products/wolfpack.png" alt="Wolfpack Supplement">
                    <h3>WOLFPACK Vitality</h3>
                    <p style="color:var(--gold-muted); margin-bottom:15px; font-weight:600;">60 capsules supply</p>
                    <select disabled>
                        <option>60 Veggie Capsules (₹1,999.00)</option>
                    </select>
                </div>
                
                <div class="bundle-connector">+</div>
                
                <!-- Product B: Wolftox -->
                <div class="bundle-card glass-card">
                    <img src="assets/images/products/wolftox.png" alt="Wolftox Supplement">
                    <h3>WOLFTOX Liver Support</h3>
                    <p style="color:var(--gold-muted); margin-bottom:15px; font-weight:600;">60 capsules supply</p>
                    <select disabled>
                        <option>60 Veggie Capsules (₹999.00)</option>
                    </select>
                </div>
                
                <div class="bundle-connector">=</div>
                
                <!-- Combo Price and Checkout Block -->
                <div class="bundle-results">
                    <h3>Wolf Stack Combo</h3>
                    <span class="bundle-save-badge">🔥 SAVE ₹299 (10% COMBO DISCOUNT)</span>
                    <div class="bundle-price-box">
                        <span style="text-decoration:line-through; font-size:1.1rem; color:var(--text-muted);">Regular ₹2,998</span>
                        <div style="font-size:2rem; font-weight:800; color:var(--gold-primary); margin-top:5px;">
                            ₹<?php echo number_format($bundle['combo_price'], 2); ?>
                        </div>
                    </div>
                    <p style="font-size:0.85rem; color:var(--text-secondary); margin-bottom:20px;">
                        Full 30-day program containing both premium formulas. Formulated for synergy.
                    </p>
                    <button class="btn-gold" id="add-bundle-btn" data-bundle-id="<?php echo $bundle['id']; ?>" style="width:100%;">
                        <i class="fas fa-cubes"></i> Add Stack to Cart
                    </button>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Trust Strip Section -->
    <section class="container">
        <div class="trust-strip">
            <div class="trust-item">
                <svg viewBox="0 0 24 24"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                <h4>FSSAI Certified</h4>
                <p>License No. 22126022000063</p>
            </div>
            <div class="trust-item">
                <svg viewBox="0 0 24 24"><path d="M12 3c-1.2 0-2.4.4-3.4 1.1C6.7 3.5 4.5 3 2 3v13c2.5 0 4.7.5 6.6 1.2 1-.7 2.2-1.2 3.4-1.2s2.4.5 3.4 1.2c1.9-.7 4.1-1.2 6.6-1.2V3c-2.5 0-4.7.5-6.6 1.2C14.4 3.4 13.2 3 12 3zm0 11.5c-1.1 0-2.2.3-3 .9V6.1c.8-.6 1.9-.9 3-.9s2.2.3 3 .9v8.3c-.8-.6-1.9-.9-3-.9z"/></svg>
                <h4>100% Ayurvedic</h4>
                <p>Pure Himalayan botanicals & extracts</p>
            </div>
            <div class="trust-item">
                <svg viewBox="0 0 24 24"><path d="M12 2L2 22h20L12 2zm0 3.99L19.53 19H4.47L12 5.99zM13 16h-2v2h2v-2zm0-6h-2v4h2v-4z"/></svg>
                <h4>Veggie Capsules</h4>
                <p>100% clean veggie caps, zero fillers</p>
            </div>
        </div>
    </section>

    <!-- Brand Teaser / About Teaser -->
    <section class="container" style="margin: 60px auto;">
        <div class="glass-card" style="display:grid; grid-template-columns: 1fr 1fr; align-items:center; border-radius:12px; overflow:hidden;">
            <div style="padding: 40px;">
                <h2 style="font-size:2.2rem; text-transform:uppercase; margin-bottom:15px; background:var(--gold-gradient); -webkit-background-clip:text; -webkit-text-fill-color:transparent;">
                    Our Brand Philosophy
                </h2>
                <p style="margin-bottom:20px; font-size:1.05rem;">
                    At Wolf Nutrition, we bridge the gap between time-tested Ayurvedic wisdom and the rigorous performance demands of modern life. We source the highest grade Shilajit, Ashwagandha, Kutki and Gokshura to formulate active wellness stacks for those who refuse to settle for average.
                </p>
                <a href="about.php" class="btn-outline-gold">Know More About Our Story</a>
            </div>
            <div style="height:100%; min-height:300px; background-image:url('assets/images/logo.png'); background-size:contain; background-position:center; background-repeat:no-repeat; background-color:#000; padding:20px;"></div>
        </div>
    </section>

    <!-- Certificates Grid Gallery -->
    <?php if (!empty($certs)): ?>
    <section class="container" style="margin-bottom: 60px;">
        <div class="section-header">
            <h2>Quality Certificates</h2>
            <p>Our quality and safety registrations for wholesale distribution</p>
        </div>
        <div class="cert-gallery">
            <?php foreach ($certs as $cert): ?>
                <div class="cert-item">
                    <a href="certificates.php">
                        <img src="<?php echo htmlspecialchars($cert['image_url']); ?>" alt="<?php echo htmlspecialchars($cert['title']); ?>">
                    </a>
                    <h4><?php echo htmlspecialchars($cert['title']); ?></h4>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Blog Preview Grid -->
    <?php if (!empty($blogs)): ?>
    <section class="container" style="margin-bottom: 60px;">
        <div class="section-header">
            <h2>The Wellness Pack Blog</h2>
            <p>Scientific insights, biohacking strategies and Ayurvedic guides</p>
        </div>
        <div class="blog-grid">
            <?php foreach ($blogs as $blog): ?>
                <div class="blog-card">
                    <div class="blog-card-image">
                        <img src="<?php echo htmlspecialchars($blog['cover_image'] ? $blog['cover_image'] : 'assets/images/blog/default.png'); ?>" alt="<?php echo htmlspecialchars($blog['title']); ?>">
                        <span class="blog-card-badge"><?php echo htmlspecialchars($blog['category_tag']); ?></span>
                    </div>
                    <div class="blog-card-content">
                        <div class="blog-card-date"><?php echo date('M d, Y', strtotime($blog['published_at'])); ?></div>
                        <a href="blog-post.php?slug=<?php echo $blog['slug']; ?>">
                            <h3 class="blog-card-title"><?php echo htmlspecialchars($blog['title']); ?></h3>
                        </a>
                        <p class="blog-card-excerpt">
                            <?php 
                            $text = strip_tags($blog['body']);
                            echo htmlspecialchars(strlen($text) > 100 ? substr($text, 0, 97) . '...' : $text); 
                            ?>
                        </p>
                        <a href="blog-post.php?slug=<?php echo $blog['slug']; ?>" class="blog-card-link">
                            Read Article <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
