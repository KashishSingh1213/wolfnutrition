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

    <!-- 90-Day Challenge Transformation Section -->
    <section class="transformation-challenge-section">
        <style>
            .transformation-challenge-section {
                padding: 100px 0;
                background-color: #080C10;
                background: radial-gradient(circle at 10% 30%, rgba(212, 175, 55, 0.03) 0%, rgba(8, 12, 16, 0) 60%);
            }
            .challenge-grid {
                display: grid;
                grid-template-columns: 0.95fr 1.05fr;
                gap: 70px;
                align-items: center;
            }
            .challenge-image-box {
                position: relative;
                border-radius: 28px;
                overflow: hidden;
                box-shadow: 0 35px 75px -15px rgba(8,12,16,0.9), 0 0 30px rgba(212,175,55,0.08);
                border: 1px solid rgba(212, 175, 55, 0.15);
            }
            .challenge-image-box img {
                width: 100%;
                display: block;
                object-fit: cover;
                height: 540px;
                transition: transform 0.6s cubic-bezier(0.25, 0.8, 0.25, 1);
            }
            .challenge-image-box:hover img {
                transform: scale(1.03);
            }
            .challenge-accent-circle {
                position: absolute;
                width: 250px;
                height: 250px;
                background: radial-gradient(circle, rgba(212, 175, 55, 0.15) 0%, rgba(8,12,16,0) 75%);
                bottom: -50px;
                right: -50px;
                z-index: 1;
                pointer-events: none;
            }
            .challenge-content h2 {
                font-size: 2.8rem;
                text-transform: uppercase;
                margin-bottom: 25px;
                line-height: 1.15;
                font-family: var(--font-heading);
                color: #fff;
                font-weight: 800;
            }
            .challenge-content h2 span {
                background: var(--gold-gradient);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }
            .challenge-content p.main-desc {
                font-size: 1.05rem;
                color: var(--text-secondary);
                line-height: 1.6;
                margin-bottom: 35px;
            }
            .challenge-features-row {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 25px;
                margin-bottom: 40px;
            }
            .challenge-feat-card {
                background: rgba(255,255,255,0.01);
                border: 1px solid rgba(212,175,55,0.1);
                border-radius: 18px;
                padding: 24px;
                transition: all 0.3s ease;
            }
            .challenge-feat-card:hover {
                border-color: var(--gold-primary);
                box-shadow: 0 10px 25px rgba(8,12,16,0.4);
                transform: translateY(-3px);
            }
            .challenge-feat-icon {
                width: 48px;
                height: 48px;
                background: rgba(212, 175, 55, 0.08);
                border: 1px solid rgba(212, 175, 55, 0.2);
                border-radius: 50%;
                display: flex;
                justify-content: center;
                align-items: center;
                color: var(--gold-primary);
                font-size: 1.15rem;
                margin-bottom: 15px;
                box-shadow: var(--gold-glow);
            }
            .challenge-feat-card h4 {
                font-size: 1.1rem;
                color: #fff;
                margin-bottom: 8px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                font-family: var(--font-heading);
            }
            .challenge-feat-card p {
                font-size: 0.88rem;
                color: var(--text-muted);
                line-height: 1.45;
            }

            @media (max-width: 1024px) {
                .challenge-grid {
                    grid-template-columns: 1fr !important;
                    gap: 50px;
                }
                .challenge-image-box img {
                    height: 450px;
                }
            }
            @media (max-width: 480px) {
                .challenge-features-row {
                    grid-template-columns: 1fr !important;
                    gap: 15px;
                }
                .challenge-content h2 {
                    font-size: 2.1rem;
                }
            }
        </style>
        
        <div class="container">
            <div class="challenge-grid">
                
                <!-- Left Column: Visual campaign image -->
                <div class="challenge-image-box">
                    <div class="challenge-accent-circle"></div>
                    <img src="assets/images/fitness_90days.png" alt="90-Day Challenge athlete holding Wolfpack bottle">
                </div>
                
                <!-- Right Column: Campaign content & features -->
                <div class="challenge-content">
                    <h2>Change your life in the next<br><span>90 days of Practice</span></h2>
                    <p class="main-desc">
                        Ayurveda is not a temporary shortcut. It is a daily discipline. In 90 days of consistent nutrition stacks and physical activity, your body undergoes complete cellular rejuvenation and peak performance enhancement.
                    </p>
                    
                    <div class="challenge-features-row">
                        <!-- Feat 1 -->
                        <div class="challenge-feat-card">
                            <div class="challenge-feat-icon">
                                <i class="fas fa-leaf"></i>
                            </div>
                            <h4>Personalized Nutrition Stack</h4>
                            <p>Tailored Shilajit & Ashwagandha dosage guidelines to optimize T-levels and clean liver toxins daily.</p>
                        </div>
                        
                        <!-- Feat 2 -->
                        <div class="challenge-feat-card">
                            <div class="challenge-feat-icon">
                                <i class="fas fa-dumbbell"></i>
                            </div>
                            <h4>Personalized Exercise Regimen</h4>
                            <p>Combine peak vitality with high-intensity strength training guides to build lean, raw muscle.</p>
                        </div>
                    </div>
                    
                    <div style="display:flex; gap:15px; flex-wrap:wrap; align-items:center;">
                        <a href="https://wa.me/919876543210?text=Hi%20Wolf%20Nutrition,%20I%20would%20like%20to%20start%20my%2090-day%20personalized%20challenge%20program%20please." target="_blank" class="btn-gold" style="padding:15px 36px; font-weight:700; font-size:0.95rem; border-radius:30px;">Start Your 90-Day Challenge</a>
                        <a href="about.php" class="btn-outline-gold" style="padding:14px 36px; font-weight:700; font-size:0.95rem; border-radius:30px;">Learn Regimen Story</a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Wolfpack Digital Experience / Why Shop With Us -->
    <section class="digital-experience-section">
        <style>
            .digital-experience-section {
                padding: 100px 0;
                background: radial-gradient(circle at 75% 50%, rgba(212, 175, 55, 0.05) 0%, rgba(8,12,16, 0) 70%);
                overflow: hidden;
            }
            .digital-experience-grid {
                display: grid;
                grid-template-columns: 1.25fr 1fr;
                gap: 80px;
                align-items: center;
            }
            /* Photo-Real CSS Phone Mockup Wrapper */
            .phone-mockup-wrapper {
                position: relative;
                width: 440px;
                margin: 0 auto;
                display: flex;
                justify-content: center;
                align-items: center;
            }
            .phone-backdrop-accent {
                position: absolute;
                bottom: -20px;
                left: -10px;
                width: 280px;
                height: 280px;
                background: var(--gold-gradient);
                opacity: 0.08;
                border-radius: 30px;
                z-index: 1;
            }
            /* Real physical side buttons */
            .phone-phys-btn {
                position: absolute;
                width: 4px;
                background: linear-gradient(to bottom, #121212, #121212);
                box-shadow: -2px 4px 10px rgba(8,12,16,0.6);
                z-index: 2;
            }
            .phone-phys-btn.vol-up {
                top: 150px;
                left: 36px;
                height: 55px;
                border-radius: 3px 0 0 3px;
            }
            .phone-phys-btn.vol-down {
                top: 220px;
                left: 36px;
                height: 55px;
                border-radius: 3px 0 0 3px;
            }
            .phone-phys-btn.power {
                top: 180px;
                right: 36px;
                height: 75px;
                border-radius: 0 3px 3px 0;
                box-shadow: 2px 4px 10px rgba(8,12,16,0.6);
            }
            /* Titanium Casing */
            .phone-mockup {
                width: 360px;
                height: 700px;
                background: #080C10;
                border: 6px solid #121212; /* Titanium Outer shell */
                padding: 10px; /* Inner bezel spacing */
                border-radius: 50px;
                box-shadow: 0 45px 85px -20px rgba(8,12,16,0.95), 0 0 35px rgba(212,175,55,0.08);
                position: relative;
                overflow: hidden;
                display: flex;
                flex-direction: column;
                z-index: 5;
            }
            /* OLED Inner Screen Bezel */
            .phone-screen-container {
                flex: 1;
                background-color: #080C10;
                border-radius: 38px;
                position: relative;
                overflow: hidden;
                display: flex;
                flex-direction: column;
                border: 1px solid rgba(255,255,255,0.02);
            }
            /* High Gloss Glass Reflection Overlay */
            .phone-glass-reflection {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: linear-gradient(135deg, rgba(255,255,255,0.08) 0%, rgba(255,255,255,0.03) 45%, rgba(255,255,255,0) 46%);
                pointer-events: none;
                z-index: 9;
            }
            /* Dynamic Speaker & Camera Notch */
            .phone-notch {
                position: absolute;
                top: 15px;
                left: 50%;
                transform: translateX(-50%);
                width: 115px;
                height: 26px;
                background-color: #000;
                border-radius: 14px;
                z-index: 10;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                box-shadow: inset 0 2px 4px rgba(255,255,255,0.05);
            }
            .phone-camera-lens {
                width: 6px;
                height: 6px;
                background: radial-gradient(circle at 35% 35%, rgba(212,175,55,0.5) 0%, #080C10 85%);
                border-radius: 50%;
                box-shadow: 0 0 2px rgba(255,255,255,0.4);
            }
            .phone-speaker-mesh {
                width: 32px;
                height: 3px;
                background: #121212;
                border-radius: 2px;
                box-shadow: inset 0 1px 2px rgba(8,12,16,0.8);
            }
            .phone-screen {
                flex: 1;
                display: flex;
                flex-direction: column;
                padding: 45px 18px 18px 18px;
            }
            /* Mock App UI */
            .mock-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 15px;
                border-bottom: 1px solid rgba(255,255,255,0.04);
                padding-bottom: 8px;
            }
            .mock-logo {
                font-size: 0.9rem;
                font-weight: 800;
                color: #fff;
                font-family: var(--font-heading);
                letter-spacing: 0.5px;
            }
            .mock-logo span {
                color: var(--gold-primary);
            }
            .mock-carousel {
                background: rgba(255,255,255,0.02);
                border: 1px solid rgba(255,255,255,0.05);
                border-radius: 14px;
                padding: 12px;
                margin-bottom: 15px;
                text-align: left;
            }
            .mock-carousel h5 {
                font-size: 0.8rem;
                color: var(--gold-primary);
                margin-bottom: 4px;
            }
            .mock-carousel p {
                font-size: 0.65rem;
                color: var(--text-secondary);
                line-height: 1.3;
            }
            .mock-carousel-dots {
                display: flex;
                gap: 4px;
                margin-top: 8px;
            }
            .mock-dot {
                width: 5px;
                height: 5px;
                border-radius: 50%;
                background: rgba(255,255,255,0.2);
            }
            .mock-dot.active {
                background: var(--gold-primary);
                width: 12px;
                border-radius: 3px;
            }
            .mock-categories-label {
                font-size: 0.75rem;
                font-weight: 700;
                color: #fff;
                text-align: left;
                margin-bottom: 10px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .mock-categories-row {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 8px;
            }
            .mock-cat-card {
                background: rgba(255,255,255,0.02);
                border: 1px solid rgba(212,175,55,0.1);
                border-radius: 10px;
                padding: 8px;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
            }
            .mock-cat-card img {
                height: 40px;
                object-fit: contain;
                margin-bottom: 5px;
            }
            .mock-cat-card span {
                font-size: 0.55rem;
                color: var(--text-secondary);
                font-weight: 600;
                text-align: center;
            }

            /* 3D Overlapping Floating Cards */
            .floating-card-tr {
                position: absolute;
                top: 80px;
                right: -95px;
                width: 230px;
                background: rgba(18,18,18, 0.95);
                backdrop-filter: blur(20px);
                border: 1px solid var(--gold-primary);
                border-radius: 16px;
                padding: 16px;
                box-shadow: 0 30px 60px rgba(8,12,16,0.85);
                z-index: 15;
                text-align: left;
                animation: floatCardTR 5s ease-in-out infinite;
                border-color: #FFD700;
            }
            .floating-card-tr .ultimate-badge {
                display: inline-block;
                background: rgba(212,175,55,0.15);
                color: var(--gold-primary);
                font-size: 0.6rem;
                font-weight: 800;
                padding: 2px 8px;
                border-radius: 20px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-bottom: 10px;
                border: 1px solid rgba(212,175,55,0.2);
            }
            .floating-card-tr .prod-box {
                display: flex;
                gap: 12px;
                align-items: center;
                margin-bottom: 12px;
            }
            .floating-card-tr .prod-box img {
                width: 60px;
                height: 60px;
                object-fit: contain;
                filter: drop-shadow(0 8px 12px rgba(8,12,16,0.3));
            }
            .floating-card-tr .prod-title {
                font-size: 0.8rem;
                font-weight: 700;
                color: #fff;
                line-height: 1.3;
            }
            .floating-card-tr .rating {
                color: var(--gold-light);
                font-size: 0.65rem;
                margin-top: 3px;
                display: flex;
                align-items: center;
                gap: 2px;
            }
            .floating-card-tr .price-box {
                display: flex;
                align-items: baseline;
                gap: 6px;
                margin-bottom: 10px;
            }
            .floating-card-tr .price-sale {
                font-size: 1.1rem;
                font-weight: 800;
                color: var(--gold-primary);
            }
            .floating-card-tr .price-mrp {
                font-size: 0.75rem;
                text-decoration: line-through;
                color: var(--text-muted);
            }

            .floating-card-bl {
                position: absolute;
                bottom: 80px;
                left: -95px;
                width: 240px;
                background: rgba(18,18,18, 0.95);
                backdrop-filter: blur(20px);
                border: 1px solid rgba(212, 175, 55, 0.3);
                border-radius: 16px;
                padding: 16px;
                box-shadow: 0 30px 60px rgba(8,12,16,0.85);
                z-index: 15;
                text-align: left;
                display: flex;
                flex-direction: column;
                gap: 10px;
                animation: floatCardBL 6s ease-in-out infinite;
            }
            .floating-card-bl .header-text {
                font-size: 0.75rem;
                font-weight: 700;
                color: var(--gold-primary);
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .floating-card-bl .body-text {
                font-size: 0.8rem;
                font-weight: 600;
                color: #fff;
                line-height: 1.4;
            }
            .floating-card-bl .avatar-box {
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 5px;
            }
            .floating-card-bl .avatar-box img {
                width: 36px;
                height: 36px;
                border-radius: 50%;
                border: 1.5px solid var(--gold-primary);
                object-fit: cover;
            }
            .floating-card-bl .avatar-box .info h5 {
                font-size: 0.75rem;
                color: #fff;
                font-weight: 700;
            }
            .floating-card-bl .avatar-box .info p {
                font-size: 0.6rem;
                color: var(--text-muted);
            }

            @keyframes floatCardTR {
                0%, 100% { transform: translateY(0) rotate(1deg); }
                50% { transform: translateY(-8px) rotate(-1deg); }
            }
            @keyframes floatCardBL {
                0%, 100% { transform: translateY(0) rotate(-1deg); }
                50% { transform: translateY(-10px) rotate(1deg); }
            }

            /* Benefit Cards in Right Column */
            .benefit-list {
                display: flex;
                flex-direction: column;
                gap: 35px;
                margin: 40px 0;
            }
            .benefit-item {
                display: flex;
                gap: 25px;
                align-items: flex-start;
            }
            .benefit-icon-wrapper {
                width: 58px;
                height: 58px;
                background: rgba(212, 175, 55, 0.06);
                border: 1px solid rgba(212, 175, 55, 0.2);
                border-radius: 14px;
                display: flex;
                justify-content: center;
                align-items: center;
                color: var(--gold-primary);
                font-size: 1.4rem;
                flex-shrink: 0;
                box-shadow: var(--gold-glow);
                transition: all 0.3s;
            }
            .benefit-item:hover .benefit-icon-wrapper {
                background: rgba(212, 175, 55, 0.12);
                border-color: var(--gold-primary);
                transform: scale(1.1);
                box-shadow: var(--gold-glow-hover);
            }
            .benefit-text h4 {
                font-size: 1.25rem;
                color: #fff;
                margin-bottom: 6px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .benefit-text p {
                font-size: 0.95rem;
                color: var(--text-secondary);
                line-height: 1.6;
            }

            @media (max-width: 1024px) {
                .digital-experience-grid {
                    grid-template-columns: 1fr !important;
                    gap: 80px;
                }
                .phone-mockup-wrapper {
                    margin-bottom: 40px;
                }
                .floating-card-tr {
                    right: -10px;
                }
                .floating-card-bl {
                    left: -10px;
                }
            }
            @media (max-width: 480px) {
                .phone-mockup-wrapper {
                    width: 320px;
                }
                .phone-mockup {
                    width: 290px;
                    height: 550px;
                    border-width: 8px;
                }
                .floating-card-tr {
                    width: 170px;
                    right: -5px;
                    top: 60px;
                    padding: 10px;
                }
                .floating-card-bl {
                    width: 180px;
                    left: -5px;
                    bottom: 40px;
                    padding: 10px;
                }
                .floating-card-tr .prod-box img {
                    width: 45px;
                    height: 45px;
                }
                .floating-card-tr .price-sale {
                    font-size: 0.95rem;
                }
            }
        </style>

        <div class="container">
            <div class="digital-experience-grid">
                
                <!-- Left Column: Interactive Phone Mockup with 3D overlapping cards -->
                <div class="phone-mockup-wrapper">
                    <!-- Asymmetric gold contrast block -->
                    <div class="phone-backdrop-accent"></div>
                    
                    <!-- Physical side buttons -->
                    <div class="phone-phys-btn vol-up"></div>
                    <div class="phone-phys-btn vol-down"></div>
                    <div class="phone-phys-btn power"></div>
                    
                    <!-- Phone body mockup -->
                    <div class="phone-mockup">
                        <!-- OLED display container -->
                        <div class="phone-screen-container">
                            <!-- Gloss reflection overlay -->
                            <div class="phone-glass-reflection"></div>
                            
                            <!-- Notch speaker/camera -->
                            <div class="phone-notch">
                                <div class="phone-speaker-mesh"></div>
                                <div class="phone-camera-lens"></div>
                            </div>
                            
                            <div class="phone-screen" style="display:flex; flex-direction:column; padding:35px 0 0 0;">
                                <!-- Mock Top Status Bar -->
                                <div class="mock-status-bar" style="display:flex; justify-content:space-between; align-items:center; padding: 2px 20px; font-size: 0.62rem; color: rgba(255,255,255,0.3); font-weight:700; font-family:sans-serif;">
                                    <span>09:41</span>
                                    <div style="display:flex; gap:6px; align-items:center;">
                                        <i class="fas fa-signal"></i>
                                        <i class="fas fa-wifi"></i>
                                        <i class="fas fa-battery-full" style="font-size:0.75rem;"></i>
                                    </div>
                                </div>

                                <!-- Mock App Header -->
                                <div class="mock-header" style="display:flex; justify-content:space-between; align-items:center; padding: 12px 18px 8px 18px; border-bottom:1px solid rgba(255,255,255,0.03); margin-bottom: 0;">
                                    <i class="fas fa-bars" style="color:var(--gold-primary); font-size:0.85rem; cursor:pointer;"></i>
                                    <div class="mock-logo" style="font-size:0.9rem; font-weight:800; color:#fff; font-family:var(--font-heading); letter-spacing:0.5px;">WOLF <span>NUTRITION</span></div>
                                    <div style="position:relative; cursor:pointer;">
                                        <i class="fas fa-shopping-bag" style="color:#fff; font-size:0.85rem;"></i>
                                        <span style="position:absolute; top:-6px; right:-6px; background:var(--gold-primary); color:#080C10; font-size:0.5rem; width:12px; height:12px; border-radius:50%; display:flex; justify-content:center; align-items:center; font-weight:800; font-family:sans-serif;">2</span>
                                    </div>
                                </div>
                                
                                <!-- Large Product Carousel Banner (Spotlight behind floating bottle) -->
                                <div class="mock-carousel-banner" style="height:210px; background:radial-gradient(circle at 50% 50%, rgba(212, 175, 55, 0.15) 0%, rgba(8,12,16,0) 80%), rgba(255,255,255,0.01); border:1px solid rgba(255,255,255,0.03); border-radius:18px; margin: 15px 15px 12px 15px; position:relative; overflow:hidden; display:flex; flex-direction:column; justify-content:center; align-items:center; text-align:center;">
                                    <span style="position:absolute; top:12px; left:12px; background:var(--gold-gradient); color:#000; font-size:0.55rem; font-weight:800; padding:2px 8px; border-radius:10px; text-transform:uppercase; letter-spacing:0.5px; font-family:var(--font-heading);">Best Seller</span>
                                    <img src="assets/images/products/wolfpack.png" alt="Wolfpack Mock Product" style="height:120px; object-fit:contain; filter:drop-shadow(0 15px 25px rgba(8,12,16,0.6)); animation:phoneProductFloat 4s ease-in-out infinite;">
                                    <h4 style="font-size:0.85rem; font-weight:700; color:#fff; margin-top:8px; font-family:var(--font-heading);">WOLFPACK Vitality</h4>
                                    <!-- Dots -->
                                    <div class="mock-carousel-dots" style="position:absolute; bottom:10px; display:flex; gap:4px;">
                                        <div class="mock-dot active" style="width:12px; height:4px; border-radius:2px; background:var(--gold-primary);"></div>
                                        <div class="mock-dot" style="width:4px; height:4px; border-radius:50%; background:rgba(255,255,255,0.2);"></div>
                                        <div class="mock-dot" style="width:4px; height:4px; border-radius:50%; background:rgba(255,255,255,0.2);"></div>
                                    </div>
                                </div>
                                
                                <!-- Mock Categories Row (Horizontal scrollable circle items) -->
                                <div class="mock-categories-label" style="font-size:0.7rem; font-weight:800; color:#fff; text-align:left; margin:5px 18px 10px 18px; text-transform:uppercase; letter-spacing:0.5px;">Shop Range</div>
                                <div class="mock-categories-row" style="display:flex; justify-content:space-between; padding:0 18px; gap:10px;">
                                    <!-- Item 1: Vitality -->
                                    <div class="mock-category-item" style="display:flex; flex-direction:column; align-items:center; gap:6px; cursor:pointer;" onclick="location.href='category.php?slug=vitality'">
                                        <div class="mock-category-circle" style="width:50px; height:50px; background:rgba(255,255,255,0.02); border:1px solid rgba(212,175,55,0.15); border-radius:50%; display:flex; justify-content:center; align-items:center; padding:6px; transition:border-color 0.2s;">
                                            <img src="assets/images/products/wolfpack.png" alt="Vitality Icon" style="width:100%; height:100%; object-fit:contain; filter:drop-shadow(0 4px 6px rgba(8,12,16,0.3));">
                                        </div>
                                        <span style="font-size:0.55rem; color:var(--text-secondary); font-weight:600;">Vitality</span>
                                    </div>
                                    <!-- Item 2: Detox -->
                                    <div class="mock-category-item" style="display:flex; flex-direction:column; align-items:center; gap:6px; cursor:pointer;" onclick="location.href='category.php?slug=liver-detox'">
                                        <div class="mock-category-circle" style="width:50px; height:50px; background:rgba(255,255,255,0.02); border:1px solid rgba(212,175,55,0.15); border-radius:50%; display:flex; justify-content:center; align-items:center; padding:6px; transition:border-color 0.2s;">
                                            <img src="assets/images/products/wolftox.png" alt="Detox Icon" style="width:100%; height:100%; object-fit:contain; filter:drop-shadow(0 4px 6px rgba(8,12,16,0.3));">
                                        </div>
                                        <span style="font-size:0.55rem; color:var(--text-secondary); font-weight:600;">Liver Detox</span>
                                    </div>
                                    <!-- Item 3: Combos -->
                                    <div class="mock-category-item" style="display:flex; flex-direction:column; align-items:center; gap:6px; cursor:pointer;" onclick="location.href='category.php?slug=all'">
                                        <div class="mock-category-circle" style="width:50px; height:50px; background:rgba(255,255,255,0.02); border:1px solid rgba(212,175,55,0.15); border-radius:50%; display:flex; justify-content:center; align-items:center; padding:6px; transition:border-color 0.2s;">
                                            <img src="assets/images/products/wolfpack_wolftox_combo.png" alt="Combos Icon" style="width:100%; height:100%; object-fit:contain; filter:drop-shadow(0 4px 6px rgba(8,12,16,0.3));">
                                        </div>
                                        <span style="font-size:0.55rem; color:var(--text-secondary); font-weight:600;">Combos</span>
                                    </div>
                                </div>
                                
                                <!-- Mock App Bottom Tab Bar -->
                                <div class="mock-tab-bar" style="display:grid; grid-template-columns:repeat(4, 1fr); padding: 12px 10px; border-top:1px solid rgba(255,255,255,0.03); background:#080C10; margin-top:auto; text-align:center; z-index:10;">
                                    <div style="display:flex; flex-direction:column; align-items:center; gap:4px; color:var(--gold-primary); cursor:pointer;" onclick="location.href='index.php'">
                                        <i class="fas fa-home" style="font-size:0.8rem;"></i>
                                        <span style="font-size:0.5rem; font-weight:700;">Home</span>
                                    </div>
                                    <div style="display:flex; flex-direction:column; align-items:center; gap:4px; color:rgba(255,255,255,0.4); cursor:pointer;" onclick="location.href='category.php?slug=all'">
                                        <i class="fas fa-capsules" style="font-size:0.8rem;"></i>
                                        <span style="font-size:0.5rem; font-weight:700;">Shop</span>
                                    </div>
                                    <div style="display:flex; flex-direction:column; align-items:center; gap:4px; color:rgba(255,255,255,0.4); cursor:pointer;" onclick="location.href='https://wa.me/919876543210'">
                                        <i class="fas fa-user-doctor" style="font-size:0.8rem;"></i>
                                        <span style="font-size:0.5rem; font-weight:700;">Consult</span>
                                    </div>
                                    <div style="display:flex; flex-direction:column; align-items:center; gap:4px; color:rgba(255,255,255,0.4); cursor:pointer;" onclick="location.href='my-account.php'">
                                        <i class="fas fa-user" style="font-size:0.8rem;"></i>
                                        <span style="font-size:0.5rem; font-weight:700;">Profile</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3D Overlapping Card 1: Ultimate Offer Card (Top-Right) -->
                    <div class="floating-card-tr">
                        <span class="ultimate-badge">Ultimate Offer</span>
                        <div class="prod-box">
                            <img src="assets/images/products/wolftox.png" alt="Wolftox">
                            <div class="info">
                                <div class="prod-title">WOLFTOX Detox</div>
                                <div class="rating">
                                    <i class="fas fa-star"></i> 4.9 <span>(24)</span>
                                </div>
                            </div>
                        </div>
                        <div class="price-box">
                            <span class="price-sale">₹899</span>
                            <span class="price-mrp">₹999</span>
                        </div>
                        <button class="btn-gold" style="width:100%; padding:8px; font-size:0.75rem; font-weight:700; border-radius:8px;" onclick="location.href='product.php?slug=wolftox-liver-support-detox'">Add to cart</button>
                    </div>

                    <!-- 3D Overlapping Card 2: Doctor Consultation Card (Bottom-Left) -->
                    <div class="floating-card-bl">
                        <div class="header-text">Goal-focused Plans</div>
                        <div class="body-text">Get your first wellness call free!</div>
                        <div class="avatar-box">
                            <img src="assets/images/dietitian_avatar.png" alt="Dietitian Avatar">
                            <div class="info">
                                <h5>Shalini Sen</h5>
                                <p>Certified Dietitian</p>
                            </div>
                        </div>
                        <a href="https://wa.me/919876543210?text=Hi%20Wolf%20Nutrition,%20I%20would%20like%20to%20book%20a%20free%20dietitian%20consultation%20please." target="_blank" class="btn-outline-gold" style="width:100%; text-align:center; padding:8px; font-size:0.75rem; font-weight:700; border-radius:8px; display:block;">Consult Free</a>
                    </div>
                </div>

                <!-- Right Column: Brand Value Coordinates -->
                <div>
                    <h4 style="color:var(--gold-primary); font-size:0.95rem; text-transform:uppercase; letter-spacing:1.5px; margin-bottom:10px; font-weight:700;">Designed for High Performance</h4>
                    <h2 style="font-size:2.8rem; text-transform:uppercase; margin-bottom:25px; line-height:1.15; font-weight:800; font-family:var(--font-heading);">Unleash the Power of Pure Wellness</h2>
                    <p style="font-size:1.05rem; color:var(--text-secondary); line-height:1.6;">
                        Experience the gold standard in wellness. We combine ancient Ayurvedic secrets with modern sports science to deliver active daily stacks that fuel your strength and detox your liver.
                    </p>

                    <div class="benefit-list">
                        <!-- Benefit 1 -->
                        <div class="benefit-item">
                            <div class="benefit-icon-wrapper">
                                <i class="fas fa-flask"></i>
                            </div>
                            <div class="benefit-text">
                                <h4>100% Transparent Formulations</h4>
                                <p>No hidden ingredients, zero fillers, and absolutely no proprietary blends. We publish the full disclosure of every premium extract on our label.</p>
                            </div>
                        </div>

                        <!-- Benefit 2 -->
                        <div class="benefit-item">
                            <div class="benefit-icon-wrapper">
                                <i class="fas fa-user-doctor"></i>
                            </div>
                            <div class="benefit-text">
                                <h4>Free Certified Expert Guidance</h4>
                                <p>Confused about what stack is right for you? Consult 1-on-1 with our certified health coaches and dietitians for a personalized regimen.</p>
                            </div>
                        </div>

                        <!-- Benefit 3 -->
                        <div class="benefit-item">
                            <div class="benefit-icon-wrapper">
                                <i class="fas fa-truck-fast"></i>
                            </div>
                            <div class="benefit-text">
                                <h4>Prepaid Rewards & Fast Delivery</h4>
                                <p>Get free express shipping and additional prepaid cashbacks/discounts on all orders. Packed and shipped securely from our certified warehouse.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Action CTA Buttons -->
                    <div style="display:flex; gap:20px; align-items:center; flex-wrap:wrap; margin-top:20px;">
                        <a href="category.php?slug=all" class="btn-gold" style="padding:16px 36px; font-size:0.95rem; font-weight:700;">Explore Products</a>
                        <a href="https://wa.me/919876543210?text=Hi%20Wolf%20Nutrition,%20I%20would%20like%20to%20book%20a%20free%20dietitian%20consultation%20please." target="_blank" class="btn-outline-gold" style="padding:15px 36px; font-size:0.95rem; font-weight:700; display:inline-flex; align-items:center; gap:8px;">
                            <i class="fab fa-whatsapp"></i> Chat with Dietitian
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>

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

    <!-- Wolfpack loyalty and expert assistance hub -->
    <section class="loyalty-assistance-section">
        <style>
            .loyalty-assistance-section {
                padding: 60px 0;
                background: linear-gradient(180deg, rgba(8,12,16, 0) 0%, rgba(212, 175, 55, 0.02) 50%, rgba(8,12,16, 0) 100%);
            }
            .loyalty-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 30px;
            }
            .loyalty-left-col {
                display: flex;
                flex-direction: column;
                gap: 30px;
            }
            .loyalty-card {
                background: #121212;
                border: 1px solid rgba(212, 175, 55, 0.1);
                border-radius: 24px;
                overflow: hidden;
                position: relative;
                transition: all 0.3s ease;
            }
            .loyalty-card:hover {
                border-color: var(--gold-primary);
                box-shadow: 0 15px 35px rgba(8,12,16,0.5), 0 0 15px rgba(212,175,55,0.05);
                transform: translateY(-4px);
            }
            .loyalty-elite-card {
                background: linear-gradient(135deg, #121212 0%, #080C10 100%);
                padding: 35px;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                min-height: 220px;
            }
            .loyalty-elite-card .elite-badge {
                align-self: flex-start;
                background: var(--gold-gradient);
                color: #080C10;
                font-size: 0.75rem;
                font-weight: 800;
                padding: 4px 12px;
                border-radius: 20px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-bottom: 15px;
            }
            .loyalty-elite-card h3 {
                font-size: 1.8rem;
                color: #fff;
                margin-bottom: 10px;
                text-transform: uppercase;
                font-family: var(--font-heading);
            }
            .loyalty-elite-card p {
                color: var(--text-secondary);
                font-size: 0.95rem;
                line-height: 1.5;
                margin-bottom: 20px;
                max-width: 80%;
            }
            .elite-bolt-icon {
                position: absolute;
                right: 30px;
                bottom: 20px;
                font-size: 8rem;
                color: var(--gold-primary);
                opacity: 0.05;
                transform: rotate(15deg);
                pointer-events: none;
            }

            /* Refer Card & Assistance Card Grid Split */
            .loyalty-split-card {
                padding: 30px;
                display: grid;
                grid-template-columns: 1.2fr 1fr;
                align-items: center;
                min-height: 220px;
            }
            .loyalty-split-card.tall {
                min-height: 470px;
                height: 100%;
            }
            .loyalty-card-text {
                z-index: 2;
            }
            .loyalty-card-text h3 {
                font-size: 1.8rem;
                color: #fff;
                margin-bottom: 12px;
                text-transform: uppercase;
                font-family: var(--font-heading);
            }
            .loyalty-card-text p {
                color: var(--text-secondary);
                font-size: 0.95rem;
                line-height: 1.5;
                margin-bottom: 20px;
            }
            .loyalty-card-image-box {
                position: relative;
                width: 100%;
                height: 100%;
                display: flex;
                justify-content: center;
                align-items: flex-end;
            }
            .loyalty-card-image-box img {
                max-height: 220px;
                object-fit: contain;
                z-index: 2;
            }
            .loyalty-split-card.tall .loyalty-card-image-box img {
                max-height: 440px;
                object-fit: contain;
                width: 110%;
                transform: translateX(10px);
            }
            .loyalty-image-backdrop {
                position: absolute;
                width: 160px;
                height: 160px;
                background: radial-gradient(circle, rgba(212, 175, 55, 0.2) 0%, rgba(8,12,16,0) 70%);
                border-radius: 50%;
                bottom: 20px;
                z-index: 1;
            }
            .loyalty-split-card.tall .loyalty-image-backdrop {
                width: 250px;
                height: 250px;
                bottom: 60px;
            }

            @media (max-width: 900px) {
                .loyalty-grid {
                    grid-template-columns: 1fr !important;
                }
                .loyalty-split-card.tall {
                    min-height: auto;
                }
                .loyalty-split-card {
                    grid-template-columns: 1fr !important;
                    text-align: center;
                    padding: 30px 20px;
                }
                .loyalty-card-image-box {
                    margin-top: 20px;
                }
                .loyalty-split-card.tall .loyalty-card-image-box img {
                    width: 100%;
                    transform: none;
                }
            }
        </style>
        
        <div class="container">
            <div class="loyalty-grid">
                
                <!-- Left Column (Elite & Refer banners) -->
                <div class="loyalty-left-col">
                    
                    <!-- Elite Card -->
                    <div class="loyalty-card loyalty-elite-card">
                        <i class="fas fa-bolt-lightning elite-bolt-icon"></i>
                        <div>
                            <span class="elite-badge"><i class="fas fa-crown"></i> Elite Member</span>
                            <h3>Wolfpack VIP Club</h3>
                            <p>Become a VIP member. Earn 2x loyalty points on every stack and access private formulations before general release.</p>
                        </div>
                        <a href="my-account.php" class="btn-gold" style="align-self: flex-start; padding: 12px 28px; font-weight:700;">Join VIP Pack</a>
                    </div>
                    
                    <!-- Refer Card -->
                    <div class="loyalty-card loyalty-split-card">
                        <div class="loyalty-card-text">
                            <h3>Refer & Earn</h3>
                            <p>Bring a brother to the pack. They get ₹150 off on their first order, and you get ₹150 coupon rewards instantly!</p>
                            <a href="my-account.php" style="color:var(--gold-primary); font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:8px;">
                                Invite Friends <i class="fas fa-arrow-right-long"></i>
                            </a>
                        </div>
                        <div class="loyalty-card-image-box">
                            <div class="loyalty-image-backdrop"></div>
                            <img src="assets/images/athletic_guy.png" alt="Happy pack member">
                        </div>
                    </div>
                    
                </div>
                
                <!-- Right Column (Tall Doctor Banner) -->
                <div class="loyalty-card loyalty-split-card tall">
                    <div class="loyalty-card-text" style="display:flex; flex-direction:column; justify-content:center;">
                        <h4 style="color:var(--gold-primary); font-size:0.9rem; text-transform:uppercase; letter-spacing:1.5px; margin-bottom:10px; font-weight:700;">Expert Guidance</h4>
                        <h3>Instant Ayurvedic Consultation</h3>
                        <p style="margin-bottom:25px;">Confused about Shilajit dosage or liver detox timing? Consult 1-on-1 with our certified Ayurvedic nutritionists for a custom regimen.</p>
                        
                        <a href="https://wa.me/919876543210?text=Hi%20Wolf%20Nutrition,%20I%20would%20like%20to%20book%20a%20free%20dietitian%20consultation%20please." target="_blank" class="btn-gold" style="align-self: flex-start; padding: 15px 35px; font-weight:700; display:inline-flex; align-items:center; gap:8px;">
                            <i class="fab fa-whatsapp"></i> Book Appointment
                        </a>
                        
                        <span style="font-size: 0.8rem; color: var(--text-muted); margin-top: 15px; display:block;">*Get your customized nutrition & lifestyle plan</span>
                    </div>
                    <div class="loyalty-card-image-box">
                        <div class="loyalty-image-backdrop"></div>
                        <img src="assets/images/ayurvedic_doctor.png" alt="Ayurvedic Practitioner">
                    </div>
                </div>
                
            </div>
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
