<?php
// category.php
require_once __DIR__ . '/includes/header.php';

$cat_slug = isset($_GET['slug']) ? trim($_GET['slug']) : 'all';
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'newest';
$in_stock = isset($_GET['in_stock']) ? (int)$_GET['in_stock'] : 0;
$min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 5000;

// Fetch Category info
$category = null;
if ($cat_slug !== 'all') {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ? AND is_active = 1");
    $stmt->execute([$cat_slug]);
    $category = $stmt->fetch();
    if (!$category) {
        // Fallback to all
        $cat_slug = 'all';
    }
}

// Build Query
$sql = "
    SELECT p.*, MIN(pv.price) as max_mrp, MIN(pv.sale_price) as min_price, pv.id as default_variant_id, SUM(pv.stock_qty) as total_stock
    FROM products p
    JOIN product_variants pv ON p.id = pv.product_id
    WHERE p.is_active = 1
";
$params = [];

if ($category) {
    $sql .= " AND p.category_id = ? ";
    $params[] = $category['id'];
}

// Group products
$sql .= " GROUP BY p.id HAVING min_price >= ? AND min_price <= ? ";
$params[] = $min_price;
$params[] = $max_price;

if ($in_stock) {
    $sql .= " AND total_stock > 0 ";
}

// Sorting logic
switch ($sort) {
    case 'price-low':
        $sql .= " ORDER BY min_price ASC ";
        break;
    case 'price-high':
        $sql .= " ORDER BY min_price DESC ";
        break;
    case 'popularity':
        // sort by count of reviews
        $sql .= " ORDER BY (SELECT COUNT(r.id) FROM reviews r WHERE r.product_id = p.id AND r.is_approved = 1) DESC ";
        break;
    case 'newest':
    default:
        $sql .= " ORDER BY p.created_at DESC ";
        break;
}

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $products = [];
}
?>

<style>
    .category-hero {
        position: relative;
        padding: 50px 60px;
        border-radius: 12px;
        margin-top: 20px;
        margin-bottom: 40px;
        overflow: hidden;
        border: 1px solid rgba(212, 175, 55, 0.15);
        box-shadow: 0 15px 35px rgba(8,12,16,0.4);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 30px;
        min-height: 250px;
    }
    
    .hero-liver-detox {
        background: linear-gradient(135deg, #121212 0%, #080C10 60%, rgba(212,175,55,0.08) 100%);
        border-left: 5px solid var(--gold-primary);
    }
    .hero-vitality {
        background: linear-gradient(135deg, rgba(212,175,55,0.1) 0%, #080C10 60%, rgba(212,175,55,0.12) 100%);
        border-left: 5px solid var(--gold-primary);
    }
    .hero-all {
        background: linear-gradient(135deg, #080C10 0%, #121212 100%);
        border-left: 5px solid var(--gold-primary);
    }
    
    .cat-hero-text h1 {
        font-size: 2.6rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 10px;
        color: #fff;
        font-family: var(--font-heading);
        font-weight: 800;
    }
    .cat-hero-text p {
        font-size: 1.05rem;
        color: var(--text-secondary);
        max-width: 650px;
        line-height: 1.65;
        margin: 0;
    }
    .cat-hero-badge {
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 1.5px;
        background: rgba(255,255,255,0.06);
        border: 1px solid rgba(255,255,255,0.12);
        color: #fff;
        padding: 4px 12px;
        border-radius: 30px;
        text-transform: uppercase;
        display: inline-block;
        margin-bottom: 15px;
    }
    
    .cat-hero-visual {
        position: relative;
        width: 220px;
        height: 220px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .cat-hero-visual::before {
        content: '';
        position: absolute;
        width: 150px;
        height: 150px;
        border-radius: 50%;
        background: radial-gradient(circle, var(--gold-primary) 0%, transparent 70%);
        opacity: 0.15;
        filter: blur(25px);
        z-index: 1;
    }
    .hero-liver-detox .cat-hero-visual::before {
        background: radial-gradient(circle, var(--gold-primary) 0%, transparent 70%);
        opacity: 0.2;
    }
    .cat-hero-visual img {
        height: 100%;
        max-height: 200px;
        object-fit: contain;
        z-index: 2;
        filter: drop-shadow(0 15px 25px rgba(8,12,16,0.7));
        animation: catFloat 5s ease-in-out infinite;
    }
    
    @keyframes catFloat {
        0% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-8px) rotate(1.5deg); }
        100% { transform: translateY(0px) rotate(0deg); }
    }
    
    /* Benefits Grid */
    .cat-benefits-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 40px;
    }
    .benefit-item {
        background: rgba(255,255,255,0.01);
        border: 1px solid rgba(255,255,255,0.05);
        border-radius: 10px;
        padding: 18px 20px;
        display: flex;
        align-items: center;
        gap: 15px;
        box-shadow: 0 5px 15px rgba(8,12,16,0.15);
        transition: border-color 0.3s;
    }
    .benefit-item:hover {
        border-color: rgba(255,255,255,0.12);
    }
    .benefit-item i {
        font-size: 1.8rem;
    }
    .benefit-item div h5 {
        font-size: 0.95rem;
        color: #fff;
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-family: var(--font-heading);
        font-weight: 700;
    }
    .benefit-item div p {
        font-size: 0.8rem;
        color: var(--text-muted);
        margin: 0;
        line-height: 1.4;
    }

    @media (max-width: 1024px) {
        .cat-benefits-grid {
            grid-template-columns: 1fr;
            gap: 15px;
        }
        .category-hero {
            padding: 35px 30px;
            flex-direction: column;
            text-align: center;
        }
        .cat-hero-text p {
            max-width: 100%;
        }
    }
</style>

    <?php
    // Theme values
    $theme_class = 'hero-all';
    $benefit_theme_color = 'var(--gold-muted)';
    $hero_img = 'assets/images/products/wolfpack_wolftox_combo.png';
    $hero_badge = 'Wolf Nutrition Stacks';
    $hero_title = $category ? htmlspecialchars($category['name']) : 'All Supplements';
    $hero_desc = $category ? htmlspecialchars($category['description']) : 'Explore the full premium range of Wolf Nutrition wellness stacks.';

    if ($cat_slug === 'liver-detox') {
        $theme_class = 'hero-liver-detox';
        $benefit_theme_color = 'var(--gold-primary)';
        $hero_img = 'assets/images/products/wolftox.png';
        $hero_badge = 'Organic Cleansing';
    } elseif ($cat_slug === 'vitality') {
        $theme_class = 'hero-vitality';
        $benefit_theme_color = 'var(--gold-primary)';
        $hero_img = 'assets/images/products/wolfpack.png';
        $hero_badge = 'Active Performance';
    }
    ?>

    <div class="container" style="margin-top: 20px; margin-bottom: 60px;">
        
        <!-- Premium Custom Hero Banner -->
        <div class="category-hero <?php echo $theme_class; ?>">
            <div class="cat-hero-text">
                <span class="cat-hero-badge"><?php echo $hero_badge; ?></span>
                <h1><?php echo $hero_title; ?></h1>
                <p><?php echo $hero_desc; ?></p>
            </div>
            <div class="cat-hero-visual">
                <img src="<?php echo $hero_img; ?>" alt="<?php echo $hero_title; ?>">
            </div>
        </div>

        <!-- Custom Benefits List below Hero Banner -->
        <div class="cat-benefits-grid">
            <?php if ($cat_slug === 'liver-detox'): ?>
                <div class="benefit-item" style="border-left: 3px solid var(--gold-primary);">
                    <i class="fas fa-shield-halved" style="color:var(--gold-primary);"></i>
                    <div>
                        <h5>Hepatic Cleanse</h5>
                        <p>Optimizes detox enzymes, helping purge heavy metals and waste.</p>
                    </div>
                </div>
                <div class="benefit-item" style="border-left: 3px solid var(--gold-primary);">
                    <i class="fas fa-apple-whole" style="color:var(--gold-primary);"></i>
                    <div>
                        <h5>Digestive Booster</h5>
                        <p>Enhances bile secretion to break down lipids and ease bloating.</p>
                    </div>
                </div>
                <div class="benefit-item" style="border-left: 3px solid var(--gold-primary);">
                    <i class="fas fa-filter" style="color:var(--gold-primary);"></i>
                    <div>
                        <h5>Cellular Shield</h5>
                        <p>Protects liver tissue from modern toxins using Kutki and Kasani.</p>
                    </div>
                </div>
            <?php elseif ($cat_slug === 'vitality'): ?>
                <div class="benefit-item" style="border-left: 3px solid var(--gold-primary);">
                    <i class="fas fa-fire" style="color:var(--gold-primary);"></i>
                    <div>
                        <h5>T-Level Optimizer</h5>
                        <p>Sustains natural vitality levels using premium purified Shilajit.</p>
                    </div>
                </div>
                <div class="benefit-item" style="border-left: 3px solid var(--gold-primary);">
                    <i class="fas fa-bolt" style="color:var(--gold-primary);"></i>
                    <div>
                        <h5>Endurance Boost</h5>
                        <p>Maintains peak muscle oxygenation and daily cellular energy.</p>
                    </div>
                </div>
                <div class="benefit-item" style="border-left: 3px solid var(--gold-primary);">
                    <i class="fas fa-dumbbell" style="color:var(--gold-primary);"></i>
                    <div>
                        <h5>Adaptogenic Recovery</h5>
                        <p>Reduces cortisol to optimize stress recovery and focus metrics.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="benefit-item" style="border-left: 3px solid var(--gold-muted);">
                    <i class="fas fa-certificate" style="color:var(--gold-muted);"></i>
                    <div>
                        <h5>Certified Pure</h5>
                        <p>FSSAI, purity stamps, and GMP manufacturing laboratory seals.</p>
                    </div>
                </div>
                <div class="benefit-item" style="border-left: 3px solid var(--gold-muted);">
                    <i class="fas fa-leaf" style="color:var(--gold-muted);"></i>
                    <div>
                        <h5>100% Organic</h5>
                        <p>Compounded with raw extracts in clean vegan capsules.</p>
                    </div>
                </div>
                <div class="benefit-item" style="border-left: 3px solid var(--gold-muted);">
                    <i class="fas fa-flask" style="color:var(--gold-muted);"></i>
                    <div>
                        <h5>Validated Ratios</h5>
                        <p>Formulations matched to clinical research parameters.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div style="display:grid; grid-template-columns: 280px 1fr; gap:30px; align-items:start;">
            <!-- Filters Sidebar -->
            <aside class="glass-card" style="padding: 25px; border-radius: 8px;">
                <h3 style="font-size:1.2rem; text-transform:uppercase; margin-bottom:20px; border-bottom:1px solid var(--border-color); padding-bottom:10px;">
                    Filters
                </h3>
                
                <form action="category.php" method="GET">
                    <input type="hidden" name="slug" value="<?php echo htmlspecialchars($cat_slug); ?>">
                    
                    <!-- Sorting -->
                    <div class="form-group">
                        <label for="sort">Sort By</label>
                        <select name="sort" id="sort" class="form-control" onchange="this.form.submit()">
                            <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                            <option value="price-low" <?php echo $sort === 'price-low' ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price-high" <?php echo $sort === 'price-high' ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="popularity" <?php echo $sort === 'popularity' ? 'selected' : ''; ?>>Popularity</option>
                        </select>
                    </div>

                    <!-- In Stock Filter -->
                    <div class="form-group" style="margin: 25px 0;">
                        <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                            <input type="checkbox" name="in_stock" value="1" <?php echo $in_stock ? 'checked' : ''; ?> onchange="this.form.submit()" style="accent-color:var(--gold-primary); width:18px; height:18px;">
                            <span>In Stock Only</span>
                        </label>
                    </div>

                    <!-- Price Filter -->
                    <div class="form-group">
                        <label>Price Range (₹)</label>
                        <div style="display:flex; gap:10px; align-items:center;">
                            <input type="number" name="min_price" value="<?php echo $min_price; ?>" class="form-control" style="padding:5px;" placeholder="Min">
                            <span>to</span>
                            <input type="number" name="max_price" value="<?php echo $max_price; ?>" class="form-control" style="padding:5px;" placeholder="Max">
                        </div>
                    </div>

                    <button type="submit" class="btn-gold" style="width:100%; margin-top:20px; padding:10px;">Apply Filters</button>
                    <a href="category.php?slug=<?php echo htmlspecialchars($cat_slug); ?>" class="btn-outline-gold" style="width:100%; margin-top:10px; padding:10px; font-size:0.8rem; text-align:center;">Reset</a>
                </form>
            </aside>

            <!-- Product Grid -->
            <div>
                <?php if (!empty($products)): ?>
                    <div class="product-grid">
                        <?php foreach ($products as $prod): 
                            $discount_pct = 0;
                            if ($prod['max_mrp'] > 0) {
                                $discount_pct = round((($prod['max_mrp'] - $prod['min_price']) / $prod['max_mrp']) * 100);
                            }

                            // Fetch ratings count
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
                                
                                <?php if ($prod['total_stock'] <= 0): ?>
                                    <span class="badge-soldout">Sold Out</span>
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
                                        <?php if ($prod['total_stock'] > 0): ?>
                                            <button class="btn-gold quick-add-btn" style="width: 100%;" 
                                                    data-product-id="<?php echo $prod['id']; ?>" 
                                                    data-variant-id="<?php echo $prod['default_variant_id']; ?>">
                                                <i class="fas fa-shopping-cart"></i> Quick Add
                                            </button>
                                        <?php else: ?>
                                            <button class="btn-gold" style="width: 100%; background: rgba(255,255,255,0.2); border:none; cursor:not-allowed; box-shadow:none; color:rgba(255,255,255,0.4);" disabled>
                                                Out of Stock
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="glass-card" style="padding: 40px; text-align:center; border-radius:8px;">
                        <i class="fas fa-search" style="font-size:3rem; color:var(--text-muted); margin-bottom:15px;"></i>
                        <h3>No products match your filters</h3>
                        <p>Try resetting the price range filters or selecting a different sorting method.</p>
                        <a href="category.php?slug=<?php echo htmlspecialchars($cat_slug); ?>" class="btn-gold" style="margin-top:20px;">Clear All Filters</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
